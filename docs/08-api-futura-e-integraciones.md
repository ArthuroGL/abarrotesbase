# ABARROTESBASE — Fase 8: API Futura e Integraciones

**Estado:** Propuesta para aprobación  
**Alcance:** Arquitectura de interoperabilidad, principios API, contratos conceptuales, seguridad e integración futura de catálogos. No expone endpoints productivos ni implementa conectores.

## 1. Qué estamos diseñando

Una frontera de integración estable para que ABARROTESBASE pueda, en el futuro, ofrecer API a aplicaciones autorizadas, recibir datos de proveedores y conectarse con periféricos o servicios externos sin introducir dependencias dentro del dominio de ventas, inventario o caja.

La API no será requisito para el MVP de interfaz Blade. El sistema interno utilizará casos de uso de aplicación; una API futura será otro adaptador que invoca esos mismos casos de uso, nunca una ruta alternativa que escriba tablas directamente.

## 2. Principios de interoperabilidad

1. **API versionada:** toda ruta pública tendrá un prefijo de versión, por ejemplo `/api/v1`.
2. **Contrato antes que endpoint:** solicitud, respuesta, errores, paginación, idempotencia y permisos se especifican y prueban antes de liberar.
3. **Mismo dominio, misma regla:** web, API, comandos e integraciones ejecutan los mismos casos de uso y Policies.
4. **Aislamiento por organización:** cada token, cliente o integración opera solo en su organización y alcance autorizados.
5. **Mínimo de datos:** no exponer datos personales, costos, márgenes o auditoría sin scope explícito.
6. **Asincronía para carga pesada:** importaciones, exportaciones y sincronizaciones se ejecutan en cola; no bloquean el POS.
7. **Idempotencia:** toda creación externa con impacto económico o de inventario exige una clave de idempotencia.
8. **Observabilidad:** solicitudes, webhooks y sincronizaciones dejan correlación, estado y error seguro.

## 3. Arquitectura de puertos y adaptadores

```text
Consumidor externo / archivo / proveedor / periférico
                        │
                        ▼
     Adaptador (REST, CSV/XLSX, webhook, SDK, dispositivo)
                        │ DTO canónico + autenticación
                        ▼
               Capa de aplicación ABARROTESBASE
                        │ casos de uso y Policies
                        ▼
       Dominio y PostgreSQL (fuente de verdad transaccional)
                        │
                        ▼
             Outbox → workers → eventos/webhooks/proyecciones
```

Los adaptadores no contienen reglas de precio, stock o autorización de negocio. Traducen formatos y delegan la decisión al núcleo.

## 4. API REST futura

### 4.1 Recursos candidatos de primera exposición

| Recurso | Operaciones futuras | Alcance mínimo |
|---|---|---|
| Productos | Consultar, buscar, alta/borrador controlada | `products:read`, `products:write` |
| Existencias | Consultar disponibilidad por sucursal | `inventory:read` |
| Precios | Consultar precio vigente | `prices:read` |
| Clientes | Consultar/crear/actualizar bajo consentimiento | `customers:read`, `customers:write` |
| Ventas | Crear borrador, consultar comprobante/estado; confirmación solo con diseño dedicado | `sales:read`, `sales:write` |
| Compras | Consultar/crear borrador y recepción autorizada | `purchases:read`, `purchases:write` |
| Reportes | Solicitar exportación y consultar estado | `reports:read`, `reports:export` |
| Catálogos externos | Ejecutar/supervisar importaciones autorizadas | `integrations:manage` |

No se expondrán inicialmente endpoints genéricos CRUD para movimientos de inventario, caja o auditoría. Son hechos sensibles que deben pasar por casos de uso específicos y permisos reforzados.

### 4.2 Convenciones de contrato

| Tema | Decisión |
|---|---|
| Formato | JSON UTF-8; fechas ISO 8601 con offset/UTC; importes y cantidades como cadenas decimales para preservar exactitud. |
| Identificadores | UUID externos; números de documento se muestran como referencia, no sustituyen el ID. |
| Colecciones | Envoltorio `data`, `meta`, `links`; paginación cursor para datos operativos grandes, page/size solo donde se justifique. |
| Filtros | Parámetros explícitos como `filter[branch_id]`, `filter[status]`, `from`, `to`; filtros permitidos documentados. |
| Ordenamiento | `sort=field,-field`; lista blanca de campos ordenables. |
| Campos | Respuesta mínima por defecto; expansión/selección de relaciones bajo lista blanca para evitar N+1 y exposición excesiva. |
| Errores | JSON problem details: código estable, título, detalle seguro, campo y `request_id`; sin stack traces. |
| Versionado | Versión mayor en URL; cambios aditivos preferidos; deprecación publicada con plazo. |
| Documentación | OpenAPI como fuente de contrato, ejemplos y colección de pruebas. |

### 4.3 Ejemplo conceptual de error

```json
{
  "type": "https://api.abarrotesbase.example/errors/insufficient-stock",
  "title": "Existencia insuficiente",
  "status": 422,
  "code": "INVENTORY_INSUFFICIENT_STOCK",
  "detail": "La cantidad solicitada no está disponible en la sucursal activa.",
  "instance": "request-id",
  "errors": [{"field": "lines[0].quantity", "code": "exceeds_available"}]
}
```

El ejemplo ilustra el contrato, no fija todavía una URL de producción.

## 5. Autenticación, autorización y protección de API

| Escenario | Mecanismo propuesto |
|---|---|
| Interfaz web propia | Sesión autenticada Laravel, CSRF y Policies. |
| Aplicación de confianza de una organización | Tokens personales/de servicio con expiración, scopes y revocación. |
| Integración de tercero con consentimiento | OAuth 2.1/OIDC cuando exista el caso comercial; no antes. |
| Comunicación servidor-a-servidor | Cliente de servicio con secreto rotado, IP allowlist opcional y scopes mínimos. |
| Webhook saliente | Firma HMAC, timestamp, ID de entrega, reintentos y endpoint de prueba. |

Controles obligatorios: TLS, rate limits por token/IP/recurso, validación de tamaño de solicitud, allowlists de campos, expiración/revocación, auditoría de uso, redacción de datos sensibles y reautenticación para acciones administrativas.

## 6. Idempotencia y concurrencia

Para `POST` que confirme venta, recepción, ajuste, pago o cualquier hecho con impacto, el consumidor enviará `Idempotency-Key` único. El sistema almacenará organización, endpoint, hash del cuerpo, estado/resultado y ventana de retención.

| Caso | Comportamiento |
|---|---|
| Misma clave y mismo cuerpo, completada | Devuelve la respuesta original; no duplica operación. |
| Misma clave y mismo cuerpo, en proceso | Devuelve estado de proceso o respuesta de reintento controlado. |
| Misma clave y cuerpo distinto | Rechaza con conflicto. |
| Actualización concurrente | Usa versión/ETag o control de versión del recurso; devuelve conflicto con datos para recargar. |

La idempotencia no reemplaza transacciones ni validaciones de dominio; las complementa para reintentos de red seguros.

## 7. Webhooks y eventos externos

### 7.1 Eventos candidatos

- `sale.confirmed`
- `sale.voided`
- `sale.returned`
- `inventory.changed`
- `inventory.low_stock`
- `purchase.receipt_confirmed`
- `cash.session_closed`
- `product.price_changed`
- `catalog.import_completed`

### 7.2 Contrato de entrega

Cada entrega incluye: `event_id` UUID, tipo, versión de esquema, organización, fecha de ocurrencia, identificador de recurso, payload mínimo, firma y cabeceras de correlación. El consumidor debe procesar idempotentemente por `event_id`.

```text
Evento de dominio confirmado
  → outbox transaccional
  → worker de entrega
  → firma HMAC + POST HTTPS
  → respuesta 2xx: entregado
  → fallo: reintento exponencial y registro
  → agotado: estado fallido, alerta y reintento manual autorizado
```

Los webhooks no transportarán contraseñas, tarjetas ni PII innecesaria. Para detalles sensibles, el consumidor autorizado consulta un recurso de API con su token y scope.

## 8. Integración de catálogos externos

### 8.1 Objetivo y límites

Reducir captura manual incorporando información de catálogos que el negocio tiene derecho a utilizar. No se hará scraping ni se consumirá una fuente sin términos, autorización o mecanismo compatible.

### 8.2 Puerto canónico

Cada adaptador implementará capacidades conceptuales:

| Capacidad | Entrada | Salida |
|---|---|---|
| Buscar | término, categoría, página | productos externos resumidos |
| Obtener detalle | identificador de proveedor | ficha canónica con especificaciones e imágenes permitidas |
| Importar archivo | archivo validado/metadatos | lote con resultados por fila |
| Sincronizar | cursor/fecha | cambios detectados y estado |
| Mapear | producto externo + reglas | propuesta de producto/variante interna |

El DTO canónico conservará proveedor, ID externo, código, nombre, descripción, categoría externa, marca, especificaciones, imágenes, unidades, precio sugerido si está autorizado, versión, hash, fecha de fuente y restricciones de uso.

### 8.3 Flujo de importación y revisión

```text
Fuente autorizada
  → validar credenciales/archivo y licencia
  → adaptar al DTO canónico
  → guardar lote externo sin publicar
  → detectar duplicados y proponer mapeo
  → revisión humana de producto, unidad, precio e impuesto
  → aprobar creación/actualización explícita
  → registrar auditoría y resultados
```

- Una importación no activa productos ni cambia precios automáticamente.
- La coincidencia de código, SKU o texto es una sugerencia; requiere política de confianza y revisión.
- Las imágenes se copian o referencian solo si la licencia/contrato lo permite.
- Credenciales, límites de API y términos del proveedor se almacenan/gestionan fuera de los logs y se revisan por integración.

### 8.4 Estados de lote de integración

`received → validating → normalized → awaiting_review → partially_approved/approved/rejected → applied/failed`

Cada lote guarda conteos, errores por fila, origen, versión, ejecutor, tiempos y enlaces a productos internos creados/actualizados.

## 9. Importación/exportación de archivos

| Operación | Formatos iniciales | Controles |
|---|---|---|
| Importar productos/proveedores | CSV UTF-8 y XLSX tras validación de cabeceras | Plantilla versionada, preview, validación por fila, lote reversible antes de aprobación. |
| Exportar reportes | CSV inicialmente; XLSX/PDF según aprobación posterior | Trabajo en cola, autorización, enlace temporal privado, auditoría. |
| Importar catálogo proveedor | Formato/documentación autorizados por proveedor | Adaptador específico, licencia, revisión humana, bitácora. |

Los archivos se analizan por tamaño, tipo real, extensión permitida y contenido antes de procesarlos. No se ejecutan macros ni se interpretan fórmulas de hoja de cálculo como comandos.

## 10. Periféricos y dispositivos

El POS web debe ser funcional sin dependencia obligatoria de dispositivos específicos. Los periféricos se integrarán por niveles:

| Dispositivo | Estrategia futura | Nota |
|---|---|---|
| Lector de código | Modo teclado/HID como primera opción | No requiere conector propietario. |
| Impresora de tickets | Impresión del navegador al inicio; adaptador local/servicio opcional si se requiere control avanzado | Validar modelos y sistema operativo antes de elegir. |
| Báscula | Entrada como teclado/serial mediante puente local autorizado, según hardware | La interfaz valida la cantidad; el dispositivo no es fuente de autoridad. |
| Terminal de pago | Integración certificada del proveedor en fase específica | No almacenar datos de tarjeta. |
| Cajón de dinero | Acoplado a impresora/controlador compatible | Su apertura no sustituye el movimiento de caja. |

## 11. Observabilidad y soporte de integraciones

Cada ejecución externa tendrá `correlation_id`/`request_id`, organización, integración, tipo de operación, actor, estado, duración, conteos y error sanitizado. Se dispondrá de una vista administrativa para reintentos autorizados, sin exponer secretos ni payloads sensibles completos.

Métricas operativas: entregas webhook exitosas/fallidas, antigüedad de cola, importaciones por estado, tasa de errores, última sincronización y límites de proveedor.

## 12. Decisiones pendientes

| Tema | Recomendación actual | Resolver antes de |
|---|---|---|
| Autenticación API pública | Tokens de servicio con scopes para primeras integraciones; OAuth al abrir terceros | Primera API externa |
| Estándar de API | REST + OpenAPI; evaluar GraphQL solo ante necesidades de composición probadas | Diseño de endpoints |
| Impresora | Browser print para MVP; validar hardware antes de un puente local | Implementación POS |
| Balanza | Definir marcas/protocolos con el negocio | Integración de granel con hardware |
| Catálogos | Solo proveedores con autorización formal/documentada | Primer adaptador |
| Facturación/pagos | Integración certificada y análisis legal/proveedor independiente | Incorporar esos módulos |

## 13. Puerta de salida de la Fase 8

Esta fase queda lista al aprobar:

- API versionada como adaptador de los mismos casos de uso internos.
- Contratos de seguridad, idempotencia, paginación, errores y webhooks.
- Integración de catálogos por puertos/adaptadores con revisión humana y uso autorizado.
- Estrategia segura para archivos, periféricos y observabilidad.

**Siguiente fase propuesta (solo tras aprobación):** Fase 9 — convenciones de código, estrategia de pruebas, estructura de proyecto y estándares de calidad. Posteriormente se cerrará el roadmap y se elaborará el plan concreto de inicialización del proyecto Laravel.
