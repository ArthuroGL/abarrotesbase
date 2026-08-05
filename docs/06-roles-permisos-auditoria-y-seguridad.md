# ABARROTESBASE — Fase 6: Roles, Permisos, Auditoría y Seguridad

**Estado:** Propuesta para aprobación  
**Alcance:** Modelo de autorización, roles operativos iniciales, auditoría, segregación de funciones y controles de seguridad. No incluye implementación de autenticación, políticas ni interfaces.

## 1. Qué estamos protegiendo

ABARROTESBASE administra dinero, mercancía, precios, márgenes y datos personales. La seguridad no se limita al inicio de sesión: cada operación debe estar vinculada a una identidad, sucursal, permiso, motivo y —cuando sea sensible— a una evidencia auditable.

El modelo usa RBAC (roles basados en permisos) con alcance organizacional y de sucursal. Un rol agrupa capacidades; la autorización final evalúa permiso, estado de usuario, organización, sucursal y reglas del recurso.

## 2. Principios de control de acceso

1. **Mínimo privilegio:** se asigna solo la capacidad necesaria para la función.
2. **Denegación por defecto:** una capacidad no asignada se rechaza explícitamente.
3. **Servidor como autoridad:** ocultar un botón no concede ni revoca permiso; Laravel Policies/Gates validan la operación real.
4. **Ámbito de sucursal:** los datos operativos se consultan y modifican únicamente en sucursales asignadas, salvo capacidad corporativa explícita.
5. **Segregación de funciones:** operaciones de alto riesgo pueden requerir un autorizador diferente al solicitante.
6. **Trazabilidad:** acceso y cambios sensibles generan evidencia; los permisos no sustituyen auditoría.
7. **Roles configurables:** los roles iniciales son plantillas; una organización puede crear roles derivados sin modificar código.

## 3. Catálogo de permisos

Los códigos siguen la convención `{módulo}.{recurso}.{acción}`. Acciones: `view`, `create`, `update`, `delete`, `confirm`, `cancel`, `approve`, `export`, `manage` y `override`.

| Área | Permisos principales |
|---|---|
| Organización | `organization.view`, `organization.update`, `branch.view`, `branch.manage`, `register.manage` |
| Usuarios y acceso | `user.view`, `user.manage`, `role.view`, `role.manage`, `permission.view` |
| Productos | `product.view`, `product.create`, `product.update`, `product.archive`, `product.import` |
| Precios | `price.view`, `price.manage`, `price.override` |
| Inventario | `inventory.view`, `inventory.adjust`, `inventory.count`, `inventory.count.apply`, `inventory.transfer`, `inventory.negative_stock.override` |
| Compras | `supplier.view`, `supplier.manage`, `purchase.view`, `purchase.create`, `purchase.approve`, `purchase.receive`, `purchase.cancel` |
| Ventas | `sale.view`, `sale.create`, `sale.confirm`, `sale.cancel`, `sale.return`, `sale.reprint`, `sale.discount.override` |
| Caja | `cash_session.open`, `cash_session.close`, `cash_movement.create`, `cash_movement.approve`, `cash.view` |
| Gastos | `expense.view`, `expense.create`, `expense.approve`, `expense.pay`, `expense.cancel` |
| Clientes | `customer.view`, `customer.create`, `customer.update`, `customer.archive` |
| Reportes | `report.dashboard.view`, `report.sales.view`, `report.inventory.view`, `report.cash.view`, `report.margin.view`, `report.export` |
| Auditoría | `audit.view`, `audit.export` |
| Integraciones | `integration.view`, `integration.manage`, `catalog_sync.run`, `catalog_mapping.approve` |

Un permiso `manage` se reserva para administración de un catálogo/recurso; no implica automáticamente `override`, `approve`, cancelación o exportación de información sensible.

## 4. Roles iniciales

| Rol | Propósito | Ámbito predeterminado |
|---|---|---|
| Propietario | Supervisión integral y decisiones de negocio. | Organización completa |
| Administrador | Configuración y operación administrativa. | Organización completa o sucursales asignadas |
| Encargado de sucursal | Supervisión diaria y autorización local. | Una o más sucursales |
| Cajero | Cobro y operación de caja limitada. | Sucursal/caja asignada |
| Comprador | Proveedores, compras y recepción autorizada. | Sucursales asignadas |
| Almacenista | Recepciones, conteos y consulta de inventario. | Sucursales asignadas |
| Auditor | Consulta de evidencia y reportes, sin cambios operativos. | Organización completa o asignada |
| Consultor de reportes | Consulta de indicadores sin datos de configuración. | Organización o sucursales asignadas |

El Propietario no debe utilizarse para la operación cotidiana. Se recomienda una cuenta administrativa de emergencia protegida con MFA y cuentas nominativas para cada persona.

## 5. Matriz de permisos por rol

Leyenda: **C** consulta, **O** operación, **A** autorización/administración, **—** sin acceso.

| Capacidad | Propietario | Administrador | Encargado | Cajero | Comprador | Almacenista | Auditor | Reportes |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| Configuración/sucursales/cajas | A | A | C | — | — | — | C | — |
| Usuarios, roles y permisos | A | A | — | — | — | — | C | — |
| Productos/categorías/unidades | A | A | O limitada | C | O limitada | C | C | C |
| Precios y listas | A | A | C | C | C | — | C | C |
| Venta y reimpresión | C | O | O | O | C | — | C | C |
| Descuento excepcional | A | A | A por límite | — | — | — | C | — |
| Cancelación/devolución | A | A | A por límite | Según política | — | — | C | — |
| Inventario/movimientos | C | A | A | C limitada | C | O | C | C |
| Ajuste/conteo aplicado | A | A | A por límite | — | — | O / sin aprobar propio | C | — |
| Compras/proveedores | C | A | C | — | O/A | O recepción | C | C |
| Caja y arqueo | C | A | A | O caja propia | — | — | C | C |
| Gastos | C | A | A por límite | O limitado | — | — | C | C |
| Margen/utilidad | A | A | C según política | — | C compras | — | C | C |
| Auditoría | A | A | C limitada | — | — | — | C/A exportación | — |
| Exportaciones | A | A | Según permiso | — | C compras | — | A si asignado | A asignado |

“Por límite” requiere una política configurable por monto, diferencia, sucursal o tipo de operación. “O limitada” significa que el rol puede realizar la acción sobre recursos permitidos, sin cambiar parámetros globales.

## 6. Políticas de autorización por recurso

Además del permiso, cada Policy valida el recurso concreto:

| Recurso/acción | Validaciones obligatorias |
|---|---|
| Venta | Usuario activo; sucursal asignada; caja/sesión válida para efectivo; venta pertenece a organización. |
| Descuento | Permiso de excepción; máximo por porcentaje/monto; motivo; autorizador distinto si política aplica. |
| Cancelación/devolución | Estado/plazo válidos; origen pertenece a sucursal/organización permitida; motivo y monto dentro de límite. |
| Inventario | Sucursal asignada; stock item activo; ajuste/conteo no aplicado; no autoaprobación cuando está prohibida. |
| Compra/recepción | Proveedor, artículos y sucursal de misma organización; compra en estado correcto; recepción no duplicada. |
| Caja | Caja asignada; sesión en estado correcto; usuario responsable o rol superior; diferencia dentro de regla de cierre. |
| Reportes | Periodo/sucursal dentro del alcance; permiso específico para margen, PII o exportación. |
| Configuración | Rol administrativo; no archivar datos referenciados sin proceso controlado. |

## 7. Flujos de autorización reforzada

Una autorización no es prestar la contraseña. Se registra como una decisión independiente y verificable.

```text
Usuario solicita excepción
  → sistema muestra impacto y motivo obligatorio
  → autorizador autenticado con permiso distinto
  → sistema valida que no sea el mismo actor, si aplica
  → se crea decisión de autorización vinculada al documento
  → se confirma operación y se audita
```

| Caso | Regla recomendada |
|---|---|
| Descuento fuera de regla | Segundo usuario cuando excede umbral o política promocional. |
| Stock negativo | Administrador/encargado con permiso `override`; siempre motivo. |
| Ajuste inventario elevado | Solicitante y aprobador distintos. |
| Sobre-recepción/costo anómalo | Comprador o administrador aprueba. |
| Retiro/gasto alto | Responsable distinto al cajero solicitante. |
| Cierre con diferencia | Encargado aprueba fuera de tolerancia. |
| Cambio de roles/precios/impuestos | Administrador; auditoría obligatoria; opcional flujo de doble aprobación. |

La entidad futura `approvals` o un conjunto de campos de autorización deberá guardar: tipo de operación, documento afectado, solicitante, autorizador, motivo, umbral, decisión, fecha y evidencia opcional.

## 8. Auditoría

### 8.1 Eventos auditables mínimos

| Categoría | Acciones que deben auditarse |
|---|---|
| Acceso | Inicio/cierre de sesión, fallo de autenticación, cambio/restablecimiento de contraseña, MFA, bloqueo/desbloqueo. |
| Administración | Alta/baja/cambio de usuarios, roles, permisos, sucursales, cajas, impuestos y métodos de pago. |
| Producto/precio | Crear/archivar producto, cambio de código, unidad, conversión, precio, impuesto o costo configurado. |
| Inventario | Ajuste, aplicación de conteo, transferencia, stock negativo y reversas. |
| Compras | Aprobación/cancelación, recepción, diferencia de costo/cantidad fuera de regla. |
| Ventas | Confirmación, descuento excepcional, cancelación, devolución, reimpresión y anulación. |
| Caja/gastos | Apertura, retiro, ingreso, gasto, depósito, arqueo, diferencia y cierre. |
| Datos y exportación | Consulta/exportación de margen, auditoría o datos de clientes cuando la política lo requiera. |
| Integraciones | Configurar fuente, ejecutar importación, aprobar mapeo y aplicar cambios importados. |

### 8.2 Contenido mínimo de la bitácora

Cada entrada incluye:

- Identificador, fecha UTC y zona operativa presentada.
- Organización, sucursal y caja/sesión si corresponde.
- Actor autenticado, actor efectivo y autorizador cuando exista delegación.
- Acción, módulo, tipo e identificador de entidad afectada.
- Valores anteriores y posteriores en JSONB con campos sensibles enmascarados.
- Motivo, referencia a documento origen, IP, user agent y `request_id`.
- Resultado: exitoso, rechazado o fallido; motivo de rechazo cuando sea seguro registrarlo.

La bitácora es solo inserción. La purga, si una obligación legal lo permite, será una tarea administrativa con política de retención y registro de su propia ejecución.

## 9. Autenticación y sesiones

| Control | Decisión/recomendación |
|---|---|
| Identidad | Cuenta nominativa por persona; prohibir cuentas compartidas. |
| Contraseñas | Hash moderno provisto por Laravel, longitud mínima configurable, sin registro de contraseña en logs. |
| MFA | Obligatorio para Propietario/Administrador; habilitable para otros roles. |
| Sesiones | Cookies seguras, HTTP-only, SameSite apropiado; regeneración de sesión al autenticar. |
| Inactividad | Tiempo de expiración configurable; POS puede usar un bloqueo rápido que exija reautenticación. |
| Intentos fallidos | Rate limiting y bloqueo temporal progresivo; auditar intentos relevantes. |
| Recuperación | Enlaces de un uso y vencimiento; notificación/auditoría de restablecimiento. |
| Autorización sensible | Reautenticación o MFA reciente para cambios de acceso, exportaciones masivas y configuración crítica. |

No se almacenarán números completos de tarjetas, secretos de proveedores ni contraseñas en tablas de negocio, sesiones, auditoría o logs.

## 10. Protección de datos y seguridad de aplicación

1. TLS obligatorio en entornos no locales; HSTS en producción cuando el dominio esté estabilizado.
2. Protección CSRF para formularios de sesión; validación y codificación de salida para prevenir XSS.
3. Form Requests/DTOs con lista explícita de campos permitidos; prohibido el mass assignment indiscriminado.
4. Consultas parametrizadas/Eloquent; prohibido concatenar SQL con entrada de usuario.
5. Almacenamiento privado para evidencias y documentos; autorización al descargar.
6. Secretos solo por variables de entorno/gestor de secretos; nunca en repositorio, vistas o bitácora.
7. Dependencias actualizadas, análisis de vulnerabilidades y parches con proceso controlado.
8. Logs estructurados con redacción de PII, tokens, contraseñas y datos financieros sensibles.
9. Backups cifrados, acceso limitado y pruebas de restauración periódicas.

## 11. Modelo de amenazas operativo

| Riesgo | Control preventivo | Detección/respuesta |
|---|---|---|
| Cajero aplica descuentos indebidos | Límites, permisos y segundo autorizador | Auditoría de descuentos y reporte de excepciones |
| Faltante de efectivo | Sesión individual, retiros motivados, arqueo | Diferencias, alertas y revisión de movimientos |
| Manipulación de inventario | Movimientos inmutables, aprobación de ajustes | Kardex, conteos y auditoría |
| Cuenta compartida | Cuentas nominativas y política operativa | Correlación de sesión/dispositivo y auditoría |
| Usuario fuera de sucursal | Scope y Policies por sucursal | Auditoría de accesos denegados relevantes |
| Exportación de datos | Permiso específico y reautenticación | Registro de exportaciones y revisión |
| Integración maliciosa/inestable | Adaptadores aislados, secreto protegido, revisión humana | Logs y estado de sincronización |

## 12. Reglas de retención y privacidad iniciales

- Documentos comerciales, inventario, caja y auditoría: retención mínima propuesta de cinco años, sujeta a obligaciones legales/fiscales aplicables.
- Datos personales de clientes/proveedores: solo los necesarios para operación; acceso limitado y actualización/archivo controlado.
- Archivos de evidencia: misma retención del documento que respaldan, salvo una política legal distinta.
- Logs técnicos: periodo menor configurable, con redacción de datos sensibles.
- Antes de integrar facturación electrónica o pagos procesados, se realizará una revisión legal y de privacidad específica para México y el proveedor seleccionado.

## 13. Puerta de salida de la Fase 6

Esta fase queda lista al aprobar:

- RBAC con permisos por acción y alcance de sucursal.
- Roles iniciales y segregación de funciones.
- Acciones auditables, datos de bitácora y política de autorización reforzada.
- Controles de autenticación, protección de datos y respuesta a riesgos operativos.

**Siguiente fase propuesta (solo tras aprobación):** Fase 7 — dashboard, reportes, métricas y proyecciones de lectura. Posteriormente se documentarán API futura, convenciones, roadmap y plan de implementación antes de generar código.
