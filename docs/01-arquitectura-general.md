# ABARROTESBASE — Fase 1: Diseño de Arquitectura General

**Estado:** Propuesta para aprobación  
**Alcance de esta fase:** Arquitectura general del sistema. No incluye código, migraciones, modelo entidad-relación detallado ni integración con terceros.

## 1. Qué estamos construyendo

ABARROTESBASE será un ERP + POS modular para comercios minoristas. El núcleo resolverá ventas, inventario, compras, caja y administración; los módulos complementarios (reportes, promociones, catálogos externos y API) se apoyarán en ese núcleo sin acoplarse a la interfaz ni a proveedores concretos.

La primera versión se diseñará como un **monolito modular Laravel**: una sola aplicación desplegable, con fronteras explícitas entre módulos y contratos internos estables. No se plantea una arquitectura de microservicios en esta etapa.

## 2. Por qué esta arquitectura

El negocio requiere consistencia inmediata entre venta, existencias y caja. Separar estos procesos en servicios distribuidos desde el inicio añadiría complejidad operativa, fallos parciales y costes de infraestructura sin aportar valor proporcional para una tienda o cadena pequeña.

El monolito modular permite:

- Transacciones PostgreSQL atómicas en los procesos críticos.
- Desarrollo y despliegue simples.
- Pruebas integrales más confiables.
- Evolución posterior de módulos con alta carga (por ejemplo, reportes o sincronización de catálogos) a procesos independientes, si los datos lo justifican.

### Alternativas evaluadas

| Opción | Ventajas | Desventajas | Decisión |
|---|---|---|---|
| Monolito tradicional por capas | Rápido al inicio | Tiende a mezclar dominios y a crecer de forma rígida | No recomendado |
| **Monolito modular** | Consistencia, menor operación, límites claros, evolución gradual | Requiere disciplina para conservar los límites | **Recomendado** |
| Microservicios desde el inicio | Escalamiento y despliegue independientes | Complejidad distribuida, observabilidad, consistencia eventual y más coste | Postergar |

## 3. Principios rectores

1. **Dominio primero.** Las reglas de venta, costo, inventario y caja no dependen de Blade, HTTP ni Eloquent.
2. **Módulos con alta cohesión.** Cada módulo posee sus casos de uso y reglas; las dependencias entre módulos son explícitas.
3. **Consistencia en operaciones financieras e inventario.** Una venta confirmada actualiza sus documentos, movimientos de inventario y efectos de caja en una misma transacción.
4. **Trazabilidad inmutable.** Los hechos operativos se corrigen mediante documentos de reversa o ajustes autorizados; no se alteran silenciosamente.
5. **Configuración antes que código.** Impuestos, promociones, unidades, sucursales y políticas comerciales serán configurables.
6. **Seguridad por defecto.** Autorización en el servidor, mínimo privilegio y bitácora de acciones sensibles.
7. **Preparado para múltiples sucursales.** El modelo incorporará organización y sucursal desde el inicio, aunque la primera instalación use una sola.

## 4. Vista de contexto

```text
Usuarios de tienda / Administradores
              │
              ▼
    Blade + Tailwind + JavaScript ES6
              │ HTTPS
              ▼
        Laravel 12 (monolito modular)
              │
  ┌───────────┼────────────────────────────────────┐
  │           │                                    │
  ▼           ▼                                    ▼
POS/Ventas  Inventario/Compras                 Administración
  │           │                                    │
  └───────────┴───── eventos de dominio ───────────┘
              │
              ▼
          PostgreSQL
              │
     ┌────────┴────────┐
     ▼                 ▼
 Auditoría       Trabajos en cola
                       │
                       ▼
     Catálogos externos / notificaciones / reportes pesados
```

## 5. Capas y regla de dependencias

Cada módulo seguirá una variante pragmática de Clean Architecture. La dependencia apunta hacia el dominio; las capas externas no introducen reglas de negocio.

```text
Interfaz (Blade, controladores, Form Requests, comandos)
                         │
Aplicación (casos de uso, DTO, transacciones, orquestación)
                         │
Dominio (entidades, value objects, enums, políticas y eventos)
                         ▲
Infraestructura (Eloquent, PostgreSQL, colas, archivos, proveedores)
```

### Responsabilidades

| Capa | Responsabilidad | No debe contener |
|---|---|---|
| Interfaz | Entrada/salida HTTP, renderizado, validación de forma | Reglas de precio, stock o permisos de negocio |
| Aplicación | Ejecutar un caso de uso, coordinar dependencias y transacciones | Consultas de presentación o detalles de framework dispersos |
| Dominio | Invariantes y lenguaje del negocio | HTTP, Blade, SQL o Eloquent acoplado |
| Infraestructura | Persistencia e integraciones concretas | Decisiones de negocio duplicadas |

**Decisión:** se usará Repository Pattern solamente para agregados o consultas cuyo contrato sea relevante al dominio y pueda necesitar otra implementación. Eloquent se empleará directamente en infraestructura y lecturas simples; crear repositorios genéricos para cada tabla agregaría abstracción sin valor.

## 6. Módulos y responsabilidades

| Módulo | Responsabilidad principal | Depende funcionalmente de |
|---|---|---|
| Organización y configuración | Empresa, sucursales, parámetros operativos | — |
| Identidad y acceso | Usuarios, roles, permisos, sesiones | Organización |
| Catálogos maestros | Productos, categorías, marcas, unidades, impuestos | Organización |
| Productos y precios | Variantes, códigos, listas e historial de precios | Catálogos maestros |
| Inventario | Existencias, movimientos, ajustes, transferencias y conteos | Productos, sucursales |
| Compras | Órdenes, recepción, costos y cuentas con proveedor | Proveedores, inventario, productos |
| Proveedores | Datos comerciales y condiciones de compra | Organización |
| POS y ventas | Carrito, cotización, venta, devoluciones y comprobantes internos | Productos, inventario, clientes, caja |
| Clientes | Datos, crédito y comportamiento comercial | Organización |
| Caja | Aperturas, cierres, ingresos, retiros y arqueos | Usuarios, sucursales, ventas, gastos |
| Gastos | Registro y autorización de egresos operativos | Caja, proveedores |
| Promociones y descuentos | Reglas comerciales, vigencias y autorizaciones | Productos, clientes, ventas |
| Reportes y dashboard | Proyecciones de lectura para operación y decisión | Eventos de todos los módulos |
| Auditoría | Bitácora de acciones y cambios sensibles | Todos los módulos |
| Integraciones de catálogo | Importaciones autorizadas, mapeos y sincronización | Productos, catálogos maestros |

Los módulos se comunican por contratos de aplicación y eventos de dominio; no deben consultar o modificar directamente tablas internas de otro módulo fuera de los casos de uso aprobados.

## 7. Agregados y límites transaccionales iniciales

Estos límites se detallarán en el diseño de base de datos, pero se fijan desde ahora para proteger consistencia:

| Agregado / documento | Invariantes clave |
|---|---|
| Producto | Identidad comercial, tipo de venta, unidades permitidas, variantes y códigos únicos por organización |
| Movimiento de inventario | Toda variación de existencias tiene causa, fecha, sucursal, responsable y documento origen |
| Compra / recepción | Una recepción confirmada incrementa inventario y actualiza el costo según la política definida |
| Venta | Una venta confirmada fija precios, descuentos, impuestos, pagos y líneas; no se edita, se devuelve o cancela bajo reglas |
| Sesión de caja | Una caja abierta pertenece a una sucursal, responsable y periodo; todo efectivo se concilia contra movimientos |
| Gasto | Un gasto autorizado genera el efecto correspondiente en caja y auditoría |

## 8. Decisiones críticas de dominio

### 8.1 Inventario como libro de movimientos

La fuente de verdad será un libro de movimientos de inventario, no un contador que se sobrescribe. Se podrán mantener saldos materializados por producto/sucursal para lectura rápida, pero deberán poder reconstruirse desde los movimientos.

**Impacto:** permite auditoría, correcciones controladas, devoluciones y reportes confiables de existencias.

### 8.2 Unidades y productos a granel

Las cantidades se almacenarán con precisión decimal configurable, nunca con `float`. Las unidades de venta y de inventario se relacionarán por factores de conversión explícitos. Por ejemplo, una caja puede equivaler a 12 piezas y el kilogramo a 1,000 gramos.

**Impacto:** una venta de 250 g descuenta correctamente del inventario base, y una caja puede comprarse/venderse también por piezas cuando la política del producto lo permita.

### 8.3 Dinero y costos

Los importes se almacenarán como `numeric/decimal` de PostgreSQL, con moneda ISO 4217 y precisión definida por configuración. Nunca se usarán flotantes para precios, descuentos, impuestos o costos.

**Impacto:** evita diferencias de redondeo en ventas, caja y utilidad.

### 8.4 Precio histórico y costo histórico

Una venta conservará la fotografía del precio, descuento, impuestos y costo aplicable en el momento de confirmación. Los cambios posteriores en listas de precios o costos no reescribirán el pasado.

**Impacto:** reportes de margen y auditoría consistentes.

### 8.5 Documentos reversables

Las cancelaciones, devoluciones, ajustes y correcciones producirán nuevos documentos o movimientos relacionados; no eliminarán el evento original una vez confirmado.

**Impacto:** trazabilidad real y menor riesgo de fraude o inconsistencias.

### 8.6 Multi-sucursal desde el inicio

Las entidades operativas se asociarán a organización y, cuando corresponda, a sucursal. Se evitará codificar el supuesto de una única tienda.

**Impacto:** habilita crecimiento sin rediseñar relaciones centrales.

## 9. Flujo técnico de una venta POS

1. El usuario autenticado abre una sesión de caja autorizada.
2. El POS identifica el producto por código, búsqueda o selección; consulta precio y disponibilidad aplicables.
3. El caso de uso valida permisos, promoción, descuento, unidad, stock y medios de pago.
4. En una transacción de base de datos se confirma la venta, se registran pagos, se generan movimientos de inventario y el efecto de caja.
5. Se publican eventos de dominio después de confirmar la transacción.
6. Listeners asíncronos actualizan proyecciones de dashboard, envían notificaciones o preparan impresión, sin comprometer la confirmación de la venta.

Para evitar sobreventa en concurrencia, la reserva/descuento de inventario aplicará bloqueo transaccional o actualización condicional en el saldo de la sucursal, definido en el diseño físico de base de datos.

## 10. Eventos, colas y procesos asíncronos

Los eventos de dominio son una integración interna, no un sustituto de las transacciones. Los procesos críticos se completan de forma síncrona; las tareas costosas o externas se encolan de forma fiable mediante un patrón de outbox, a definir en la fase de base de datos.

Eventos iniciales previstos:

- `SaleCompleted`
- `SaleVoided`
- `InventoryMovementRecorded`
- `PurchaseReceiptConfirmed`
- `CashSessionOpened`
- `CashSessionClosed`
- `ExpenseApproved`
- `PriceChanged`

Usos asíncronos: actualización de indicadores, alertas de mínimo, exportaciones, importación de catálogos autorizados y notificaciones. En el arranque se recomienda la cola de base de datos; Redis será una evolución cuando el volumen o la concurrencia lo requieran.

## 11. Integraciones futuras con catálogos externos

Se definirá un puerto por proveedor, por ejemplo `ExternalCatalogProvider`, con capacidades de búsqueda, detalle e importación. Cada proveedor tendrá un adaptador aislado que traduzca sus datos a un formato canónico interno.

```text
Proveedor / archivo autorizado
            │
            ▼
Adaptador específico ──► DTO canónico ──► Revisión humana ──► Producto interno
```

La importación no publicará cambios automáticos en productos existentes sin reglas y aprobación. Se almacenarán referencia externa, versión, fecha de sincronización, origen y evidencias de licencia/permiso cuando aplique.

**Impacto:** se pueden agregar Truper u otros catálogos sin contaminar el modelo central ni depender de una API concreta.

## 12. Seguridad y auditoría

- Laravel Policies y permisos granulares para cada operación sensible.
- Form Requests para validación de entradas; DTOs tipados hacia aplicación.
- Registro de actor, acción, entidad afectada, valores previos/posteriores, IP, sesión y fecha para operaciones auditables.
- Soft delete únicamente en catálogos que no afecten documentos históricos; documentos confirmados no se eliminarán.
- Restricciones de base de datos como última barrera de integridad, además de validaciones de aplicación.
- Secretos fuera del repositorio, cifrado en tránsito y copias de seguridad verificadas.

## 13. Estructura conceptual del código

La estructura exacta se validará al crear el proyecto Laravel, pero el criterio será modular y orientado a casos de uso:

```text
app/
  Modules/
    Sales/
      Domain/
      Application/
      Infrastructure/
      Presentation/
    Inventory/
    Purchasing/
    Cash/
    ...
  Shared/
    Domain/
    Application/
    Infrastructure/
```

Los controladores serán adaptadores delgados: autorizan, reciben un Form Request, construyen un DTO, invocan un caso de uso y devuelven una respuesta o vista. El dominio no retornará respuestas HTTP ni vistas Blade.

## 14. Requisitos no funcionales arquitectónicos iniciales

| Área | Criterio inicial |
|---|---|
| Rendimiento POS | Operaciones habituales percibidas como inmediatas; se medirán objetivos concretos antes de implementación |
| Disponibilidad | La venta no dependerá de integraciones externas ni de generación de reportes |
| Integridad | Transacciones ACID para venta, compra recibida, caja y ajustes |
| Seguridad | Autenticación, autorización, auditoría y principio de mínimo privilegio |
| Observabilidad | Logs estructurados, identificación de solicitudes y alertas de errores en producción |
| Mantenibilidad | Pruebas de casos de uso y contratos; módulos sin dependencias circulares |
| Respaldo | Backups automatizados de PostgreSQL con restauraciones de prueba periódicas |

## 15. Riesgos y mitigaciones

| Riesgo | Mitigación arquitectónica |
|---|---|
| Inconsistencias de stock | Libro de movimientos, transacciones, bloqueos/concurrencia y ajustes auditados |
| Cambios de precio que alteren reportes | Fotografías históricas en documentos confirmados |
| Lógica duplicada entre POS y administración | Casos de uso de aplicación compartidos; Blade solo presenta |
| Integración externa inestable o no autorizada | Adaptadores aislados, colas, revisión humana y sin dependencia en ventas |
| Crecimiento desordenado | Límites de módulo, convenciones y pruebas desde la primera implementación |

## 16. Decisiones tomadas en esta fase

1. Laravel 12 + Blade + Tailwind + JavaScript ES6 será un monolito modular.
2. PostgreSQL será la base transaccional y fuente de verdad.
3. La lógica de negocio residirá en dominio y aplicación; controladores mínimos.
4. Inventario y caja se registrarán como movimientos trazables, no como valores sobrescritos.
5. Las operaciones confirmadas serán inmutables y se revertirán mediante documentos relacionados.
6. Organización y sucursal existirán desde el modelo inicial.
7. El modelo estará preparado para unidades, conversiones, granel, cajas, variantes y códigos de barras.
8. Las integraciones de catálogo usarán puertos/adaptadores y no se implementan todavía.
9. Eventos y colas se reservan para desacoplar efectos secundarios; no para debilitar la consistencia transaccional.

## 17. Fuera del alcance de la Fase 1

- Modelo entidad-relación y diccionario de datos detallados.
- Migraciones, modelos, controladores, vistas o cualquier código.
- Diseño detallado de flujos POS, inventario, compras y caja.
- Matriz concreta de roles/permisos.
- Contratos de la API futura.
- Integración real con Truper u otros proveedores.

## 18. Entregable y puerta de salida

Este documento es la base para el resto de la documentación. Antes de avanzar, debe aprobarse especialmente:

- Monolito modular como estrategia inicial.
- Multi-organización/sucursal en el núcleo.
- Inventario basado en movimientos y documentos inmutables.
- Precio/costo histórico en transacciones confirmadas.
- Uso selectivo de repositorios y eventos asíncronos.

**Siguiente fase propuesta (solo tras aprobación):** Fase 2 — visión, objetivos, alcance y requerimientos funcionales/no funcionales priorizados. Después se diseñará el modelo entidad-relación y la base de datos antes de cualquier implementación.
