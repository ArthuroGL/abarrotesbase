# ABARROTESBASE — Fase 3: Modelo de Dominio, Historias y Casos de Uso

**Estado:** Propuesta para aprobación  
**Alcance:** Lenguaje de negocio, responsabilidades de dominio, historias de usuario y casos de uso centrales. No define aún tablas, columnas, migraciones ni implementación.

## 1. Qué estamos definiendo

Esta fase traduce los módulos aprobados a conceptos de negocio y operaciones verificables. Su propósito es evitar que la base de datos o las pantallas definan accidentalmente las reglas del sistema.

Cada caso de uso posterior deberá usar estos conceptos y respetar sus invariantes. Los nombres pueden ajustarse antes de implementación, pero su significado no deberá duplicarse entre módulos.

## 2. Lenguaje ubicuo

| Término | Definición de negocio |
|---|---|
| Organización | Empresa o propietario lógico de la información y configuración. |
| Sucursal | Punto físico/operativo donde se almacena y vende mercancía. |
| Producto | Artículo comercial base, con identidad, reglas de inventario y venta. |
| Variante | Presentación específica de un producto, por ejemplo color o litro de pintura. |
| Unidad base | Unidad en la que se controla la existencia; por ejemplo pieza o gramo. |
| Unidad de venta | Unidad permitida para comprar o vender, relacionada con la base por conversión. |
| Existencia | Saldo disponible calculado por producto/variante y sucursal. |
| Movimiento de inventario | Hecho inmutable que aumenta, disminuye o reserva existencia con una causa. |
| Precio | Importe vigente bajo una lista, regla, vigencia y contexto comercial. |
| Costo | Valor de inventario asignado según la política de costo aprobada. |
| Venta | Documento comercial confirmado que contiene líneas, importes, pagos y efectos. |
| Devolución | Documento que revierte total o parcialmente una venta confirmada. |
| Sesión de caja | Periodo operativo abierto por una persona en una caja/sucursal. |
| Arqueo | Comparación entre efectivo teórico y efectivo contado al cerrar caja. |
| Compra | Documento comercial de adquisición a proveedor. |
| Recepción | Confirmación física de mercancía recibida; es la que afecta el inventario. |
| Ajuste | Corrección autorizada de inventario o caja, con motivo y responsable. |
| Auditoría | Evidencia de una acción sensible, su actor, contexto y cambio. |

## 3. Contextos delimitados

Un contexto delimitado es una frontera de significado y responsabilidad. Reduce la posibilidad de que un módulo modifique datos de otro sin respetar sus reglas.

```text
┌──────────────────────── Organización y acceso ────────────────────────┐
│ usuarios · roles · permisos · sucursales · configuración              │
└──────────────┬────────────────────────────────────────────────────────┘
               │
     ┌─────────┴──────────┐
     ▼                    ▼
Catálogo comercial    Operación de inventario
productos · unidades  existencias · movimientos · conteos
precios · impuestos             ▲          ▲
     │                           │          │
     ▼                           │          │
Ventas / POS ──────── Caja ──────┘       Compras / recepción
clientes · pagos      sesiones             proveedores · costos
     │                     │                    │
     └─────────────┬───────┴────────────────────┘
                   ▼
      Reportes, dashboard y auditoría (lectura/trazabilidad)
```

| Contexto | Es propietario de | Publica | Consume |
|---|---|---|---|
| Organización y acceso | Organización, sucursales, usuarios, roles, permisos | Usuario/sucursal configurados | — |
| Catálogo comercial | Productos, variantes, unidades, códigos, precios, impuestos | Producto/precio disponible | Organización |
| Inventario | Movimientos, saldos, conteos y ajustes | Existencia modificada | Productos, compras, ventas |
| Compras | Proveedores, compras y recepciones | Recepción confirmada | Catálogo, inventario |
| Ventas | Carrito, venta, líneas, pagos, devoluciones | Venta/devolución confirmada | Catálogo, inventario, clientes, caja |
| Caja | Sesiones, movimientos de efectivo, arqueos y cierres | Caja abierta/cerrada, diferencia | Ventas, gastos, acceso |
| Gastos | Solicitudes/autorizaciones de gastos | Gasto aprobado | Caja, proveedores |
| Analítica y auditoría | Proyecciones, bitácoras y consultas | — | Eventos de todos los contextos |

## 4. Reglas de negocio transversales

1. Toda operación ocurre dentro de una organización y, cuando es operativa, de una sucursal.
2. Un usuario solo puede actuar dentro de sus sucursales asignadas y permisos otorgados.
3. Los documentos confirmados no se editan directamente; se cancelan, devuelven o ajustan con documento relacionado.
4. Ningún movimiento que altere inventario o efectivo carece de causa, actor, fecha y documento origen.
5. Una venta no puede confirmar una línea sin precio, unidad válida, impuesto aplicable y disponibilidad según la política de stock.
6. Un descuento fuera de la regla comercial exige autorización, motivo y auditoría.
7. Una recepción, no la creación de una orden de compra, altera existencias y costo.
8. No se puede registrar efectivo de venta sin una sesión de caja apta, salvo política explícita para otro medio de pago.
9. Los reportes usan documentos confirmados y sus valores históricos, no los valores actuales del catálogo.
10. Las cantidades y dinero usan decimales exactos y reglas de redondeo centralizadas.

## 5. Estados de documentos centrales

| Documento | Estados iniciales | Transiciones permitidas |
|---|---|---|
| Producto | activo, inactivo | activo ↔ inactivo; no se elimina si tiene historial |
| Compra | borrador, aprobada, parcial, recibida, cancelada | borrador → aprobada/cancelada; aprobada → parcial/recibida/cancelada |
| Venta | borrador, confirmada, cancelada, devuelta parcial, devuelta total | borrador → confirmada/cancelada; confirmada → devuelta parcial/total; no edición directa |
| Sesión de caja | abierta, en arqueo, cerrada | abierta → en arqueo → cerrada; apertura por sesión nueva |
| Gasto | borrador, pendiente de aprobación, aprobado, rechazado, pagado, cancelado | según autorización y pago |
| Conteo | borrador, en progreso, conciliado, aplicado, cancelado | borrador → en progreso → conciliado → aplicado |

Los estados se representarán por enums de dominio en la futura implementación. Las transiciones serán casos de uso, no simples actualizaciones de un campo.

## 6. Historias de usuario priorizadas

### 6.1 POS y ventas

| ID | Historia | Prioridad | Criterios de aceptación resumidos |
|---|---|---|---|
| HU-VEN-01 | Como cajero, quiero agregar productos por escáner, código o búsqueda para cobrar sin demora. | Debe | Identifica producto activo; muestra precio/unidad; registra trazabilidad del método si aplica. |
| HU-VEN-02 | Como cajero, quiero vender por peso o fracción para cobrar productos a granel correctamente. | Debe | Acepta cantidad decimal válida; convierte a unidad base; descuenta existencia exacta. |
| HU-VEN-03 | Como cajero, quiero aceptar pagos mixtos para concluir ventas reales. | Debe | Suma de pagos coincide con total; calcula cambio solo cuando corresponde. |
| HU-VEN-04 | Como cajero, quiero ver el total, impuestos y descuento antes de confirmar para evitar errores. | Debe | Totales recalculados desde reglas de servidor; precio histórico queda fijado al confirmar. |
| HU-VEN-05 | Como encargado, quiero cancelar o devolver una venta autorizada para corregir errores sin perder historial. | Debe | Requiere motivo/permiso; genera reversa de caja e inventario según corresponda. |
| HU-VEN-06 | Como encargado, quiero aplicar un descuento excepcional autorizado para atender un caso comercial sin debilitar el control. | Debería | Valida límite; registra autorizador, motivo y valor original. |
| HU-VEN-07 | Como vendedor, quiero consultar datos básicos de un cliente para asociar la venta cuando sea necesario. | Debe | Busca/crea cliente conforme a permisos y protección de datos. |

### 6.2 Productos e inventario

| ID | Historia | Prioridad | Criterios de aceptación resumidos |
|---|---|---|---|
| HU-INV-01 | Como administrador, quiero registrar un producto con unidad, tipo de venta y código para comercializarlo correctamente. | Debe | Campos y unidades coherentes; código único en su alcance; producto inactivo no se vende. |
| HU-INV-02 | Como administrador, quiero configurar caja y pieza para vender ambas presentaciones del mismo artículo. | Debe | Conversión explícita; movimientos expresados en unidad base; no hay conversión ambigua. |
| HU-INV-03 | Como encargado, quiero consultar existencia y sus movimientos para explicar faltantes o sobrantes. | Debe | Filtra por producto/sucursal/periodo; cada movimiento incluye causa y origen. |
| HU-INV-04 | Como encargado, quiero ajustar inventario con motivo y autorización para reflejar una diferencia real. | Debe | Ajuste crea movimiento inmutable; requiere motivo, usuario y permiso. |
| HU-INV-05 | Como encargado, quiero realizar conteos físicos para conciliar el sistema con la tienda. | Debe | Conserva conteo capturado; calcula diferencia; aplicación es controlada y auditable. |
| HU-INV-06 | Como comprador, quiero conocer productos bajo mínimo para priorizar reposición. | Debe | Evalúa mínimo por sucursal; muestra saldo y último movimiento/venta relevante. |
| HU-INV-07 | Como administrador, quiero administrar variantes de un producto para distinguir presentaciones vendibles. | Debe | Cada variante puede tener código, precio y stock propios si aplica. |

### 6.3 Compras y proveedores

| ID | Historia | Prioridad | Criterios de aceptación resumidos |
|---|---|---|
| HU-COM-01 | Como comprador, quiero registrar proveedores para relacionar compras y condiciones comerciales. | Debe | Datos únicos según reglas aprobadas; proveedor inactivo no admite compras nuevas. |
| HU-COM-02 | Como comprador, quiero crear una compra con productos, cantidades y costos esperados. | Debe | Es borrador hasta aprobación; no afecta stock al crearla. |
| HU-COM-03 | Como almacenista, quiero confirmar la recepción real de una compra para aumentar existencias correctamente. | Debe | Permite recepción parcial; crea movimientos; actualiza costo bajo política elegida. |
| HU-COM-04 | Como comprador, quiero consultar historial de compras y costos para negociar y planificar. | Debería | Filtra por producto/proveedor/periodo; preserva costos históricos. |

### 6.4 Caja y gastos

| ID | Historia | Prioridad | Criterios de aceptación resumidos |
|---|---|---|---|
| HU-CAJ-01 | Como cajero, quiero abrir caja con un fondo inicial para iniciar turno controlado. | Debe | Una sesión válida por caja/responsable según política; registra monto y hora. |
| HU-CAJ-02 | Como cajero, quiero registrar retiro o ingreso con motivo para mantener el efectivo explicable. | Debe | Requiere caja abierta, monto, tipo y motivo; aplica permisos. |
| HU-CAJ-03 | Como encargado, quiero registrar gastos operativos para descontarlos correctamente de caja. | Debe | Gasto autorizado genera movimiento de caja relacionado; conserva evidencia/motivo si aplica. |
| HU-CAJ-04 | Como cajero, quiero cerrar mi sesión con arqueo para reportar cualquier diferencia. | Debe | Compara teórico vs. contado por medio; requiere explicación cuando hay diferencia. |
| HU-CAJ-05 | Como administrador, quiero revisar diferencias de caja para detectar incidencias. | Debe | Consulta responsable, periodo, motivo y estado de revisión. |

### 6.5 Administración, reportes y auditoría

| ID | Historia | Prioridad | Criterios de aceptación resumidos |
|---|---|---|---|
| HU-ADM-01 | Como administrador, quiero asignar roles y permisos para limitar las acciones por responsabilidad. | Debe | Permisos aplican en servidor y por sucursal cuando corresponda. |
| HU-ADM-02 | Como administrador, quiero mantener listas de precio e impuestos para que el POS calcule correctamente. | Debe | Vigencias no ambiguas; cambios no modifican transacciones pasadas. |
| HU-REP-01 | Como propietario, quiero ver ventas, utilidad, caja y alertas en un dashboard para decidir cada día. | Debe | Indicadores por organización/sucursal/periodo con datos consistentes. |
| HU-REP-02 | Como propietario, quiero analizar margen por producto y categoría para saber qué conviene vender. | Debe | Usa precio y costo histórico; permite filtros. |
| HU-AUD-01 | Como auditor, quiero ver quién hizo una acción sensible para investigar una incidencia. | Debe | Bitácora filtrable con actor, acción, fecha, entidad y contexto. |

## 7. Casos de uso esenciales

### CU-01 — Confirmar venta POS

**Actor principal:** Cajero autorizado.  
**Precondiciones:** Usuario autenticado, sucursal activa, sesión de caja apta para efectivo y productos disponibles.

**Flujo principal:**

1. El cajero agrega productos y cantidades al carrito.
2. El sistema identifica unidad, precio, impuesto, promoción y disponibilidad aplicables.
3. El cajero indica los medios de pago.
4. El sistema valida permisos, totales, stock y sesión de caja.
5. El sistema confirma la venta en una transacción.
6. El sistema registra pagos, movimientos de inventario y movimientos de caja aplicables.
7. El sistema emite comprobante interno y evento posterior a confirmación.

**Excepciones:** código no encontrado, producto inactivo, stock insuficiente, descuento no autorizado, pago incompleto, sesión de caja cerrada o concurrencia de stock.  
**Postcondiciones:** Venta inmutable confirmada o ninguna modificación persistente.

### CU-02 — Registrar recepción de compra

**Actor principal:** Comprador o almacenista autorizado.  
**Precondiciones:** Compra aprobada o recepción directa permitida; proveedor y productos válidos.

**Flujo principal:**

1. El actor selecciona compra/proveedor y registra cantidades realmente recibidas.
2. El sistema valida unidades, diferencias y política de recepción parcial.
3. El actor confirma recepción.
4. El sistema registra recepción y movimientos de inventario en una transacción.
5. El sistema actualiza costo conforme a la política definida.
6. El sistema cambia estado de compra a parcial o recibida y publica evento.

**Postcondiciones:** Stock y trazabilidad de costo actualizados, sin modificar la compra de origen.

### CU-03 — Aplicar ajuste por conteo físico

**Actor principal:** Encargado autorizado.  
**Precondiciones:** Conteo conciliado, productos/sucursal activos y permiso de ajuste.

**Flujo principal:**

1. El actor captura o revisa cantidades contadas.
2. El sistema calcula diferencias contra el saldo teórico en el corte definido.
3. El actor indica motivo y solicita/aplica autorización.
4. El sistema crea movimientos de ajuste por diferencia en una transacción.
5. El sistema registra auditoría y marca el conteo como aplicado.

**Postcondiciones:** El saldo refleja el ajuste; conteo y movimientos permanecen vinculados.

### CU-04 — Abrir y cerrar sesión de caja

**Actor principal:** Cajero autorizado.  
**Precondiciones apertura:** No existe sesión incompatible abierta según configuración.  
**Flujo de apertura:** registra fondo inicial, responsable, caja y hora; sistema valida y abre sesión.

**Flujo de cierre:**

1. El cajero inicia arqueo y registra cantidades contadas por medio de pago.
2. El sistema calcula el teórico desde fondo, ventas, gastos, retiros e ingresos.
3. El sistema presenta diferencias.
4. El cajero explica diferencias; un responsable autoriza si la política lo exige.
5. El sistema cierra sesión y deja la conciliación inmutable.

**Postcondiciones:** No se aceptan nuevos movimientos vinculados a la sesión cerrada.

### CU-05 — Crear o actualizar producto comercial

**Actor principal:** Administrador autorizado.  
**Precondiciones:** Catálogos de unidades, categorías e impuestos disponibles.

**Flujo principal:**

1. El actor captura identidad, tipo de producto, unidades, códigos, precios y variantes cuando apliquen.
2. El sistema valida unicidad, conversiones, precisión y reglas de venta.
3. El sistema guarda una nueva versión/configuración del catálogo.
4. Si el producto ya tiene operaciones, cambios sensibles quedan auditados y no alteran históricos.

**Postcondiciones:** Producto apto para comercialización solo si está activo y correctamente configurado.

### CU-06 — Registrar gasto desde caja

**Actor principal:** Cajero o encargado autorizado.  
**Precondiciones:** Sesión de caja abierta y categoría de gasto válida.

**Flujo principal:**

1. El actor captura importe, categoría, concepto, beneficiario/evidencia y medio.
2. El sistema valida límite y necesidad de autorización.
3. Al aprobarse, se registra gasto y movimiento de caja de forma atómica.
4. Se registra auditoría y se actualizan indicadores de caja.

**Postcondiciones:** El efectivo teórico se ajusta y el gasto es consultable.

## 8. Matriz de permisos por capacidad

Esta matriz indica capacidades; los roles concretos se detallarán en la fase de roles y permisos.

| Capacidad | Cajero | Encargado | Comprador | Administrador | Propietario |
|---|---:|---:|---:|---:|---:|
| Confirmar venta | Sí | Sí | Opcional | Sí | Consulta |
| Descuento excepcional | No | Según límite | No | Sí | Sí |
| Cancelar/devolver venta | Según política | Sí | No | Sí | Consulta |
| Abrir/cerrar caja | Sí | Sí | No | Sí | Consulta |
| Ajustar inventario | No | Sí | Limitado | Sí | Consulta |
| Recibir compra | No | Sí | Sí | Sí | Consulta |
| Gestionar productos/precios | No | Limitado | Limitado | Sí | Consulta |
| Consultar reportes | Limitado | Operativos | Compras | Sí | Sí |
| Gestionar usuarios/permisos | No | No | No | Sí | Opcional |
| Consultar auditoría | No | Limitado | No | Sí | Sí |

La palabra “según límite” implica reglas configurables por monto, sucursal, tipo de operación y, cuando proceda, autorización de segundo usuario.

## 9. Reglas que requieren decisión antes del diseño de datos

| Decisión | Alternativas | Impacto |
|---|---|---|
| Política de costo | Promedio ponderado, FIFO | Valuación, margen y devoluciones |
| Stock negativo | Prohibir, permitir solo autorizado | Flujo de POS y concurrencia |
| Caja por sucursal | Una sesión por caja física, por usuario o ambas | Apertura/cierre y auditoría |
| Precio por variante | Heredado de producto, independiente, ambos | Modelo de precios y POS |
| Devoluciones | Mismo día, cualquier periodo, con/sin ticket | Documentos, permisos y movimientos |
| Ajustes | Directos, por conteo obligatorio, ambos | Control de inventario |

**Recomendación inicial:** promedio ponderado, stock negativo prohibido salvo permiso excepcional, sesiones por caja física y usuario responsable, precios específicos por variante cuando aplique, devoluciones con venta de referencia y ajustes controlados por motivo/conteo.

## 10. Puerta de salida de la Fase 3

Esta fase queda lista al aprobar:

- Lenguaje de negocio y límites de contexto.
- Reglas transversales y ciclos de vida de documentos.
- Historias y casos de uso prioritarios del MVP.
- Recomendaciones sobre decisiones operativas pendientes.

**Siguiente fase propuesta (solo tras aprobación):** Fase 4 — modelo entidad-relación, diccionario de datos, restricciones e índices PostgreSQL. Esta fase antecede a cualquier migración o implementación.
