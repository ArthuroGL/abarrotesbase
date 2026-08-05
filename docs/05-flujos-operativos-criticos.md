# ABARROTESBASE — Fase 5: Flujos Operativos Críticos

**Estado:** Propuesta para aprobación  
**Alcance:** Especificación operativa de POS, inventario, compras y caja. Define pasos, validaciones, excepciones, efectos transaccionales y autorizaciones. No incluye código ni migraciones.

## 1. Principios de ejecución

1. Todo flujo identifica organización, sucursal, usuario y fecha/hora operativa.
2. El servidor recalcula importes, permisos, impuestos, stock y transiciones; la interfaz nunca es autoridad final.
3. Una operación crítica es atómica: se completa por entero o no deja cambios persistentes.
4. Las correcciones se hacen con documentos/movimientos relacionados, nunca modificando hechos confirmados.
5. Excepciones, descuentos, ajustes y diferencias requieren motivo; cuando aplique, autorización.
6. La impresión, envío de notificaciones, sincronización y actualización de dashboard son posteriores a la transacción crítica.

## 2. Flujo POS: venta de mostrador

### 2.1 Objetivo

Registrar una venta rápida y segura, descontar inventario, recibir el pago y reflejar el efectivo sin operaciones manuales posteriores.

### 2.2 Precondiciones

- Usuario autenticado, activo, asignado a la sucursal y con permiso de venta.
- Sucursal activa y productos disponibles/activos.
- Para pagos que afectan efectivo: sesión de caja abierta en la caja seleccionada.
- Catálogos de impuestos, precios y medios de pago configurados.

### 2.3 Flujo principal

```text
Abrir POS
  → validar contexto de sucursal/caja
  → localizar producto (escáner, código, SKU, búsqueda)
  → agregar línea con unidad y cantidad
  → resolver precio, impuesto, promoción y disponibilidad
  → repetir líneas / editar cantidades
  → asociar cliente opcional
  → capturar uno o varios pagos
  → validar y confirmar venta
  → transacción: venta + pagos + inventario + caja
  → emitir comprobante / limpiar carrito
```

### 2.4 Validaciones por línea

| Validación | Regla |
|---|---|
| Identificación | Código debe resolver un único producto/unidad activos en la organización. |
| Sucursal | Producto vendible y habilitado para la sucursal activa. |
| Unidad | Unidad permitida para venta del `stock_item`; conversión definida y positiva. |
| Cantidad | Mayor a cero; respeta precisión de la unidad y `allow_decimal`. |
| Precio | Existe precio vigente para artículo/unidad/contexto, o usuario posee autorización para captura excepcional. |
| Descuento | Respeta promoción, límites de rol y vigencia; excepción lleva motivo y autorizador. |
| Inventario | Saldo disponible cubre cantidad base, salvo excepción de stock negativo autorizada. |
| Impuesto | Tasa activa aplicable; cálculo con política de redondeo única. |

### 2.5 Confirmación transaccional

Dentro de una única transacción PostgreSQL:

1. Se bloquean/actualizan condicionalmente los saldos de cada `stock_item` afectado en la sucursal.
2. Se vuelven a resolver y validan precios, descuentos, impuestos y permisos críticos.
3. Se crea la cabecera `sales` con estado `confirmed` y sus valores fotográficos.
4. Se crean `sale_lines` con descripción, SKU, unidad, cantidad, precio, impuesto, descuento y costo históricos.
5. Se crean los `sale_payments`; la suma aplicada debe coincidir exactamente con el total de venta.
6. Se crean `inventory_movements` tipo `sale` y se actualizan `inventory_balances`.
7. Se crean `cash_movements` para los pagos que afecten caja y para cambio en efectivo, si aplica.
8. Se registra `audit_logs` y `outbox_events` en la misma transacción.

Si cualquier paso falla, PostgreSQL revierte todo. Tras `COMMIT`, procesos asíncronos preparan ticket, dashboard y alertas.

### 2.6 Pagos y cambio

- Cada pago tiene método, importe aplicado y referencia cuando el método la exige.
- La suma de pagos no puede ser menor que el total, excepto cuando se habilite explícitamente venta a crédito en una fase posterior.
- El excedente solo se admite conforme a reglas de cambio; por defecto, se devuelve en efectivo y genera movimiento de caja de cambio.
- Pagos electrónicos no pueden usarse para “dar cambio” sin una regla/medio de devolución explícitos.
- Si la venta es totalmente no-efectivo, no requiere sesión de caja por ese pago, pero la venta conserva caja/terminal conforme a configuración.

### 2.7 Excepciones POS

| Situación | Resultado |
|---|---|
| Código no encontrado | Mostrar búsqueda/alta rápida solo a usuarios autorizados; nunca crear producto implícitamente. |
| Producto sin precio | Bloquear confirmación salvo permiso de precio excepcional; auditar. |
| Stock insuficiente | Bloquear; permitir excepción únicamente con permiso, motivo y regla de producto. |
| Precio cambió durante carrito | Recalcular y solicitar confirmación visible antes de cobrar. |
| Sesión de caja cerrada | Bloquear pagos que afecten efectivo; permitir elegir otra sesión/caja válida si el usuario está autorizado. |
| Falla de impresión | Venta permanece confirmada; permitir reimpresión autorizada. |
| Doble clic/reintento | Usar clave de idempotencia por intento de confirmación para impedir ventas duplicadas. |

### 2.8 Cancelación y devolución

**Cancelación:** aplica a una venta confirmada que cumpla plazo/estado definido. Requiere motivo y permiso. Crea movimientos inversos de caja e inventario vinculados y cambia estado de la venta sin borrar líneas/pagos.

**Devolución:** crea `sale_returns` y líneas referenciando líneas originales; se limita a cantidad neta no devuelta. El ingreso a inventario depende de condición (vendible, merma, no retornable) y se registra con tipo de movimiento correspondiente. El reembolso se enlaza a caja/medio autorizado.

## 3. Flujo de inventario

### 3.1 Objetivo

Mantener una existencia explicable por producto y sucursal desde cualquier cambio: recepción, venta, devolución, ajuste, conteo o transferencia.

### 3.2 Regla central

La fuente de verdad histórica es `inventory_movements`. `inventory_balances` es una proyección transaccional para lectura y control de concurrencia. Nunca se modifica un saldo sin crear primero o simultáneamente un movimiento con causa.

### 3.3 Entradas permitidas

| Origen | Movimiento | Responsable habitual | Autorización |
|---|---|---|---|
| Recepción de compra | `purchase_receipt` | Almacenista/comprador | Permiso de recepción |
| Devolución vendible | `sale_return` | Encargado | Permiso de devolución |
| Ajuste positivo | `adjustment_in` | Encargado | Motivo + permiso; umbral si aplica |
| Conteo | `count_adjustment` | Encargado | Conteo conciliado y autorización |
| Transferencia recibida | `transfer_in` | Receptor | Transferencia enviada y recepción confirmada |
| Carga inicial | `initial_load` | Administrador | Solo puesta en marcha, auditable |

### 3.4 Salidas permitidas

| Origen | Movimiento | Responsable habitual | Autorización |
|---|---|---|---|
| Venta | `sale` | Cajero | Confirmación de venta |
| Cancelación/reversa | `void_reversal` | Encargado | Permiso y motivo |
| Ajuste negativo | `adjustment_out` | Encargado | Motivo + umbral |
| Merma/caducidad | `adjustment_out` | Encargado | Categoría de merma y evidencia si aplica |
| Transferencia enviada | `transfer_out` | Encargado | Autorización de transferencia |

### 3.5 Ajuste directo

1. El usuario selecciona sucursal y `stock_item`.
2. Captura incremento/decremento, motivo estandarizado, nota y evidencia opcional.
3. El sistema valida permiso, umbral y saldo para una salida.
4. Si requiere aprobación, queda como solicitud; el aprobador no debe ser el solicitante cuando la política de segregación lo exija.
5. Al aplicarse, crea un movimiento y actualiza saldo en una transacción.
6. El sistema registra auditoría y puede disparar alerta de diferencia significativa.

Un ajuste no edita conteos, recepciones ni ventas anteriores.

### 3.6 Conteo físico

```text
Crear conteo → congelar cantidad teórica de corte
  → capturar contado (por lista/escáner)
  → conciliar diferencias
  → registrar razones y aprobación
  → aplicar movimientos de diferencia
  → cerrar conteo como aplicado
```

- El sistema conserva `system_quantity` como fotografía al inicio/corte, aunque después haya ventas.
- El conteo puede ser total, por categoría, ubicación o selección de artículos.
- El usuario capturista no autoriza su propio ajuste si la política lo prohíbe.
- Aplicar el conteo es idempotente: un conteo ya aplicado no vuelve a generar movimientos.

### 3.7 Transferencia entre sucursales (posterior MVP si se activa)

1. Crear solicitud origen-destino y líneas.
2. Autorizar y enviar: se genera salida del origen; estado `in_transit`.
3. Recibir: registrar cantidades reales; se genera entrada al destino.
4. Diferencias se documentan como incidencia, no se ocultan con una edición de transferencia.

## 4. Flujo de compras y recepción

### 4.1 Objetivo

Planear, registrar y recibir mercancía sin inflar existencia ni perder el costo real de adquisición.

### 4.2 Flujo principal

```text
Necesidad de compra / mínimo
  → crear compra en borrador
  → agregar proveedor, artículos, unidades y costos esperados
  → aprobar o cancelar compra
  → recibir total o parcialmente
  → confirmar recepción
  → inventario y costo actualizados
  → compra queda parcial o recibida
```

### 4.3 Compra en borrador

- No afecta inventario, costo ni caja.
- Captura proveedor, sucursal destino, moneda, productos, unidades de compra, cantidades y costos estimados.
- Calcula totales como ayuda, pero los importes reales se fijan al recibir/registrar documento del proveedor según política.
- Cambios posteriores a aprobación se versionan o regresan a borrador con auditoría, nunca se silencian.

### 4.4 Recepción

1. El actor abre una compra aprobada o recepción directa autorizada.
2. Captura por línea cantidad recibida, unidad, costo real, impuestos/descuentos y, en futuro, lote/caducidad.
3. El sistema valida conversiones, proveedor, producto y que la sobre-recepción cumpla política.
4. Confirma la recepción en una transacción.
5. El sistema crea recepción/líneas, movimientos de entrada y actualiza saldos/costo.
6. La compra se marca `partial` o `received` según cantidades acumuladas.

### 4.5 Costeo recomendado: promedio ponderado

Para cada `stock_item` y sucursal, al confirmar una entrada:

```text
costo_promedio_nuevo =
  (existencia_anterior × costo_promedio_anterior + cantidad_entrada × costo_unitario_entrada)
  / (existencia_anterior + cantidad_entrada)
```

La operación se realiza con precisión decimal, bajo bloqueo del saldo/costo correspondiente y reglas de redondeo centrales. Devoluciones a proveedor y ajustes requieren un tratamiento de costo documentado en la fase de reportes/contabilidad de inventario.

### 4.6 Excepciones de compra

| Situación | Resultado |
|---|---|
| Recepción parcial | Permitida; compra permanece `partial`. |
| Cantidad superior | Bloquear por defecto; permitir autorización explícita y auditoría. |
| Costo distinto al esperado | Permitido con motivo si supera umbral configurado; afecta costo real. |
| Producto no pedido | Recepción directa/autorizada o línea adicional auditada; nunca se incorpora de forma invisible. |
| Proveedor inactivo | Bloquear compras nuevas; permitir consultar historial. |
| Doble confirmación | Idempotencia de recepción; una recepción confirmada no puede aplicarse dos veces. |

## 5. Flujo de caja

### 5.1 Apertura

1. El cajero selecciona caja física y confirma sucursal.
2. Captura fondo inicial por medio/denominación cuando la configuración lo requiera.
3. El sistema valida usuario, caja asignada y ausencia de sesión incompatible abierta.
4. Se crea `cash_session` estado `open` y movimiento `opening_float`.
5. Se registra auditoría y el POS queda habilitado para efectivo.

### 5.2 Movimientos durante la sesión

| Movimiento | Fuente | Regla |
|---|---|---|
| Cobro de venta | Venta confirmada | Generado automáticamente; no edición manual. |
| Cambio | Venta con excedente | Generado automáticamente y enlazado a venta. |
| Reembolso | Devolución/cancelación | Generado desde documento autorizado. |
| Retiro | Solicitud de usuario | Importe, motivo, responsable y autorización según umbral. |
| Ingreso | Aportación/corrección autorizada | Motivo obligatorio; no usar para ocultar diferencias. |
| Gasto | Gasto aprobado/pagado | Documento de gasto asociado. |
| Depósito | Entrega bancaria/administrativa | Responsable, evidencia y destino cuando aplique. |

No se permite modificar ni borrar movimientos confirmados de caja. Todo movimiento debe tener un origen o una razón normalizada.

### 5.3 Arqueo y cierre

```text
Iniciar arqueo
  → bloquear nuevos cobros efectivos o dirigirlos a otra sesión
  → calcular efectivo teórico por medio de pago
  → capturar contado por denominación/medio
  → calcular diferencia
  → explicar y autorizar diferencia si aplica
  → cerrar sesión y fijar resumen
```

- Una sesión en `counting` no puede recibir nuevos movimientos; la interfaz deberá advertirlo antes de iniciar arqueo.
- El sistema conserva totales teóricos, contados, diferencia, notas y aprobador como fotografía de cierre.
- Una diferencia fuera del umbral requiere aprobación de rol superior y puede abrir una incidencia para seguimiento.
- Reabrir una sesión cerrada no está permitido. Un error se corrige con sesión/documento posterior auditable.

### 5.4 Fórmula de efectivo teórico

```text
fondo inicial
+ cobros en efectivo
+ ingresos
- cambios entregados
- devoluciones en efectivo
- retiros
- gastos en efectivo
- depósitos
= efectivo teórico
```

El cálculo se realiza a partir de `cash_movements`, no de los totales de pantalla. Los medios no efectivos se concilian por separado para informe, sin mezclarse con el efectivo físico.

## 6. Puntos de autorización

| Operación | Autorización mínima | Datos obligatorios |
|---|---|---|
| Descuento fuera de política | Encargado/administrador según umbral | Motivo, valor original, autorizador |
| Venta con stock negativo | Administrador o permiso excepcional | Motivo, artículo, saldo, usuario |
| Cancelación/devolución | Encargado según plazo/monto | Venta origen, motivo, condición del artículo |
| Ajuste de inventario | Encargado; segundo aprobador por umbral | Motivo, evidencia, impacto |
| Sobre-recepción | Comprador/administrador | Diferencia, proveedor, motivo |
| Retiro/gasto alto | Encargado/administrador | Monto, categoría, beneficiario, evidencia |
| Cierre con diferencia | Encargado/administrador por umbral | Contado, teórico, explicación |

Los umbrales se parametrizarán por organización/sucursal y no se codificarán como números fijos.

## 7. Reglas de interfaz derivadas de los flujos

- El POS mantiene foco de teclado en la búsqueda/escáner al agregar una línea y reduce pasos de cobro.
- La pantalla muestra siempre sucursal, caja, usuario y estado de sesión para evitar operaciones en contexto incorrecto.
- Acciones irreversibles o de alto impacto usan un componente de confirmación común que presenta efecto, motivo y autorización si aplica.
- Formularios de documentos guardan borrador explícitamente; confirmar es una acción separada, visible y protegida de doble envío.
- Tablas de movimientos muestran tipo, origen, responsable, fecha y enlace a documento, usando componentes compartidos.
- Estados de carga, error y vacío se resuelven con componentes reutilizables, sin lógica visual dispersa.

## 8. Puerta de salida de la Fase 5

Esta fase está lista al aprobar:

- Flujos principales y excepciones de POS, inventario, compras y caja.
- Transacciones y efectos atómicos requeridos.
- Política de promedio ponderado como recomendación de costo.
- Puntos de autorización y reglas de interfaz operativa.

**Siguiente fase propuesta (solo tras aprobación):** Fase 6 — matriz detallada de roles y permisos, auditoría y seguridad operativa. Después se documentarán dashboard/reportes, API futura, convenciones y roadmap antes de pasar a implementación.
