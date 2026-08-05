# ABARROTESBASE — Fase 4: Modelo Entidad-Relación y Diseño de Base de Datos

**Estado:** Propuesta para aprobación  
**Alcance:** Modelo lógico y diseño físico propuesto para PostgreSQL. Define entidades, relaciones, claves, restricciones, índices, auditoría y retención. No contiene migraciones ni código.

## 1. Qué estamos construyendo

El modelo transaccional que soportará operaciones multi-organización y multi-sucursal de ABARROTESBASE. Está normalizado para preservar integridad y trazabilidad; los saldos y proyecciones de lectura se materializarán solo donde mejoren el rendimiento y siempre serán reconstruibles a partir de documentos/movimientos.

### Convenciones físicas

| Elemento | Decisión |
|---|---|
| Motor | PostgreSQL 16+ recomendado |
| Esquema | `public` inicialmente; prefijo funcional en nombres de tabla para mantener trazabilidad |
| Identificadores | `uuid` como PK, generados por aplicación/base; evita exponer secuencias e integra bien futuras sincronizaciones |
| Fechas | `timestamptz` en UTC; zona horaria de organización para presentación |
| Cantidades | `numeric(18,6)`; jamás `float` |
| Dinero y porcentajes | `numeric(18,6)` internamente; moneda ISO 4217 de tres caracteres |
| Estados/tipos | `varchar` con `CHECK` inicialmente, mapeado a enums PHP; PostgreSQL ENUM se evita por la fricción de evolución |
| Nombres | `snake_case`, plural para tablas, PK `id`, FK `{entidad}_id` |
| Timestamps | `created_at`, `updated_at`; documentos confirmados agregan `confirmed_at`/equivalente |
| Borrado | `deleted_at` solo para catálogos; hechos y documentos confirmados no se borran |
| Archivos/evidencias | Metadatos en BD y binario en almacenamiento externo/local, no BLOB en la tabla principal |

## 2. Vista ER general

```text
organizations ──< branches ──< registers ──< cash_sessions ──< cash_movements
      │               │                              ▲                 ▲
      │               │                              │                 │
      │               ├──< inventory_balances         │              expenses
      │               ├──< inventory_movements ───────┘
      │               ├──< sales ──< sale_lines ──────┐
      │               │       └──< sale_payments ─────┘
      │               └──< purchase_receipts ──< purchase_receipt_lines
      │
      ├──< users / roles / permissions / user_branch
      ├──< products ──< product_variants ──< product_barcodes
      │       │              │
      │       └──< product_units ─────────┘
      │
      ├──< price_lists ──< product_prices
      ├──< suppliers ──< purchases ──< purchase_lines
      ├──< customers
      ├──< tax_rates / categories / brands / units
      ├──< stock_counts ──< stock_count_lines
      ├──< audit_logs
      └──< outbox_events
```

## 3. Organización, sucursales y acceso

| Tabla | Propósito | Claves y restricciones principales |
|---|---|---|
| `organizations` | Empresa propietaria de datos y configuración. | PK `id`; `legal_name`, `display_name`, `currency_code`, `timezone`; `currency_code` CHECK de 3 mayúsculas. |
| `branches` | Punto operativo de venta/inventario. | PK `id`; FK `organization_id`; UNIQUE (`organization_id`, `code`) WHERE activa; `code` y nombre obligatorios. |
| `registers` | Caja física/lógica en una sucursal. | PK `id`; FK `branch_id`; UNIQUE (`branch_id`, `code`) WHERE activa. |
| `users` | Identidad de usuario. | PK `id`; email único global o por organización según estrategia de acceso; `is_active`; credenciales gestionadas por Laravel. |
| `organization_users` | Pertenencia de usuario a organización. | PK compuesta o UUID; UNIQUE (`organization_id`, `user_id`); fechas de vigencia. |
| `user_branches` | Sucursales en que puede operar un usuario. | UNIQUE (`organization_user_id`, `branch_id`). |
| `roles`, `permissions`, `role_permissions`, `organization_user_roles` | RBAC de capacidades. | Tablas pivote con UNIQUE de ambas FK; permisos con `code` único. |

**Regla de aislamiento:** toda tabla de negocio tendrá `organization_id` explícito, incluso cuando sea deducible desde una FK. Esto habilita filtros consistentes, RLS futuro y particionado; restricciones compuestas/validación de aplicación asegurarán que las FKs pertenezcan a la misma organización.

## 4. Catálogos comerciales

| Tabla | Campos esenciales | Restricciones e índices |
|---|---|---|
| `categories` | `organization_id`, `parent_id`, `code`, `name`, `is_active`, `deleted_at` | UNIQUE parcial (`organization_id`, `code`) WHERE `deleted_at IS NULL`; FK autorreferente; impedir ciclos en aplicación. |
| `brands` | `organization_id`, `name`, `is_active`, `deleted_at` | UNIQUE parcial por organización y nombre normalizado. |
| `units` | `organization_id` nullable para unidades globales, `code`, `name`, `dimension`, `decimal_precision`, `is_active` | `dimension` CHECK: `count`, `mass`, `volume`, `length`, `other`; código único por alcance. |
| `tax_rates` | `organization_id`, `code`, `name`, `rate`, `is_included`, vigencia, `is_active` | `rate >= 0 AND rate <= 1`; índice por organización/activo/vigencia. |
| `customers` | `organization_id`, `code`, datos de contacto, límites futuros, `is_active`, `deleted_at` | UNIQUE parcial de código; índices de búsqueda en nombre/teléfono; campos personales minimizados. |
| `suppliers` | `organization_id`, `code`, nombre comercial/legal, RFC opcional, contacto, condiciones, `is_active`, `deleted_at` | UNIQUE parcial de código; UNIQUE parcial RFC si se captura; índice por nombre. |
| `expense_categories` | `organization_id`, `code`, `name`, requiere autorización, `is_active`, `deleted_at` | UNIQUE parcial (`organization_id`, `code`). |

## 5. Productos, variantes, unidades y códigos

### 5.1 Tablas

| Tabla | Campos esenciales |
|---|---|
| `products` | `id`, `organization_id`, `category_id`, `brand_id`, `tax_rate_id`, `sku`, `name`, `description`, `product_type`, `inventory_unit_id`, `track_inventory`, `allow_negative_stock`, `is_active`, `deleted_at`, timestamps |
| `product_variants` | `id`, `organization_id`, `product_id`, `sku`, `name`, `attribute_summary`, `is_active`, `deleted_at`, timestamps |
| `product_variant_attributes` | `id`, `variant_id`, `attribute_name`, `attribute_value`, `sort_order` |
| `product_units` | `id`, `organization_id`, `product_id`, `variant_id` nullable, `unit_id`, `conversion_factor`, `is_inventory_unit`, `is_sale_unit`, `is_purchase_unit`, `allow_decimal`, `barcode_label`, `is_active` |
| `product_barcodes` | `id`, `organization_id`, `product_id`, `variant_id` nullable, `product_unit_id` nullable, `barcode`, `symbology`, `is_primary`, `is_active` |
| `product_images` | `id`, `product_id`, `storage_disk`, `path`, `alt_text`, `sort_order`, `is_primary` |
| `product_supplier_references` | `id`, `organization_id`, `product_id`, `variant_id` nullable, `supplier_id`, `supplier_sku`, `last_cost`, `last_purchased_at` |

### 5.2 Reglas y restricciones

- `products.product_type` CHECK: `simple`, `bulk`, `variant_parent`; los productos de venta deben tener unidad de inventario.
- `track_inventory = false` permite servicios/fletes futuros, pero no debe ser el valor por defecto de mercancía.
- Cada `product_unit` tiene `conversion_factor > 0`; la unidad inventariable tiene factor exactamente `1`.
- UNIQUE parcial: una sola fila `product_units` con `is_inventory_unit = true` por producto o variante cuando aplique.
- Una unidad de producto pertenece al mismo producto y organización de la variante, validado mediante FK compuestas o trigger de integridad mínimo. Se prefiere FK compuesta usando pares `(id, organization_id)` con índices UNIQUE auxiliares.
- `product_barcodes.barcode` será único por organización y activo; se permite código interno manual, pero no duplicidad ambigua en POS.
- `product_variants` solo es obligatoria para `variant_parent`; un producto simple puede venderse sin variante mediante su propio `product_id` como sujeto inventariable.

### 5.3 Sujeto inventariable normalizado

Para evitar dos caminos de inventario, se introducirá una entidad interna:

| Tabla | Propósito |
|---|---|
| `stock_items` | Representa la unidad vendible/inventariable: apunta a un `product` simple o a un `product_variant`, nunca ambos. Contiene `organization_id`, unidad base, estado y referencia comercial. |

`inventory_balances`, `inventory_movements`, líneas de compra y líneas de venta apuntarán a `stock_item_id`. Un `CHECK` garantiza que exactamente una de `product_id` o `product_variant_id` exista. Esto simplifica índices, reportes, movimientos y variantes sin perder la relación comercial.

## 6. Precios, promociones e impuestos

| Tabla | Campos esenciales | Restricciones |
|---|---|---|
| `price_lists` | `organization_id`, `code`, `name`, `currency_code`, `priority`, `starts_at`, `ends_at`, `is_active` | UNIQUE parcial por organización/código; `ends_at > starts_at` si existe. |
| `product_prices` | `organization_id`, `price_list_id`, `stock_item_id`, `product_unit_id`, `amount`, `min_quantity`, vigencia, `is_active` | `amount >= 0`, `min_quantity > 0`; índice de resolución por artículo/unidad/vigencia/prioridad. |
| `promotions` | `organization_id`, `code`, `name`, tipo, reglas JSONB, vigencia, prioridad, combinable, estado | CHECK de estado/vigencia; reglas validadas por DTO antes de persistir. |
| `promotion_targets` | `promotion_id`, tipo objetivo, `target_id` | Índice por objetivo; no usar FK polimórfica sin validación de tipo. |

Los impuestos y descuentos calculados se guardarán como fotografías en cada línea de venta/compra. Las tablas de catálogo solo determinan lo aplicable al crear el documento.

## 7. Inventario

### 7.1 Tablas de hechos y saldos

| Tabla | Campos esenciales |
|---|---|
| `inventory_movements` | `id`, `organization_id`, `branch_id`, `stock_item_id`, `movement_type`, `quantity_delta`, `unit_cost`, `total_cost`, `occurred_at`, `source_type`, `source_id`, `reason_code`, `notes`, `created_by`, timestamps |
| `inventory_balances` | `organization_id`, `branch_id`, `stock_item_id`, `on_hand_quantity`, `reserved_quantity`, `available_quantity`, `updated_at`, `version` |
| `stock_reorder_levels` | `organization_id`, `branch_id`, `stock_item_id`, `minimum_quantity`, `maximum_quantity` nullable, `reorder_quantity` nullable |
| `stock_counts` | `id`, `organization_id`, `branch_id`, `status`, `started_at`, `counted_at`, `applied_at`, `created_by`, `approved_by`, `notes` |
| `stock_count_lines` | `id`, `stock_count_id`, `stock_item_id`, `system_quantity`, `counted_quantity`, `difference_quantity`, `reason_code`, `notes` |
| `stock_transfers` | `id`, `organization_id`, sucursal origen/destino, estado, fechas, responsables, notas |
| `stock_transfer_lines` | `id`, `stock_transfer_id`, `stock_item_id`, `quantity_requested`, `quantity_sent`, `quantity_received` |

### 7.2 Integridad crítica

- `inventory_movements.quantity_delta <> 0` y `total_cost = quantity_delta * unit_cost` conforme a regla de redondeo.
- `movement_type` CHECK: `purchase_receipt`, `sale`, `sale_return`, `adjustment_in`, `adjustment_out`, `transfer_out`, `transfer_in`, `count_adjustment`, `void_reversal`, `initial_load`.
- Cada movimiento tiene exactamente una fuente de negocio (`source_type`, `source_id`); se validará por aplicación y auditoría. Las FKs polimórficas no son viables en SQL estándar; se crearán restricciones específicas para cada generador crítico cuando se implante.
- `inventory_balances` tiene PK UNIQUE (`organization_id`, `branch_id`, `stock_item_id`) y `CHECK (reserved_quantity >= 0)`, `CHECK (available_quantity = on_hand_quantity - reserved_quantity)`.
- `on_hand_quantity` puede ser negativo únicamente cuando la configuración y una operación autorizada lo permitan. Por defecto, una actualización condicional impedirá valores negativos.
- `stock_count_lines` UNIQUE (`stock_count_id`, `stock_item_id`); cantidades de sistema se fijan al corte del conteo.
- Movimientos de inventario son solo inserción: no `updated_at`, no soft delete y permisos de BD restringidos al rol de aplicación. Correcciones generan movimientos nuevos.

### 7.3 Índices

- `inventory_movements (organization_id, branch_id, stock_item_id, occurred_at DESC)` para kardex.
- `inventory_movements (source_type, source_id)` para trazabilidad de documento.
- `inventory_balances (organization_id, branch_id, stock_item_id)` UNIQUE.
- `inventory_balances (organization_id, branch_id, available_quantity)` para alertas, junto con `stock_reorder_levels`.
- `stock_counts (organization_id, branch_id, status, created_at DESC)`.

## 8. Compras y recepción

| Tabla | Campos esenciales | Restricciones importantes |
|---|---|---|
| `purchases` | `organization_id`, `branch_id`, `supplier_id`, `purchase_number`, `status`, referencias proveedor, fechas, moneda, totales fotográficos, notas, creador/aprobador | UNIQUE (`organization_id`, `purchase_number`); estado CHECK; proveedor/sucursal de misma organización. |
| `purchase_lines` | `purchase_id`, `line_number`, `stock_item_id`, `product_unit_id`, descripción, cantidad ordenada, costos/impuestos/descuentos/subtotales fotográficos | UNIQUE (`purchase_id`, `line_number`); cantidades positivas; valores no negativos. |
| `purchase_receipts` | `organization_id`, `purchase_id` nullable para recepción directa, `branch_id`, `receipt_number`, `status`, fecha, responsable, proveedor fotográfico | UNIQUE (`organization_id`, `receipt_number`); una recepción confirmada genera movimientos. |
| `purchase_receipt_lines` | `purchase_receipt_id`, `purchase_line_id` nullable, `stock_item_id`, `product_unit_id`, cantidad recibida, unidad/costo total, lote/caducidad futura | cantidades positivas; recepción acumulada no supera orden salvo permiso explícito. |

La política de costo recomendada es promedio ponderado por `stock_item` y sucursal. Para aplicar la recepción con concurrencia se bloqueará el saldo/costo correspondiente dentro de la misma transacción. Si posteriormente se elige FIFO, se incorporarán capas de costo (`inventory_cost_layers`) sin cambiar documentos fuente.

## 9. Ventas, pagos y devoluciones

| Tabla | Campos esenciales | Restricciones importantes |
|---|---|---|
| `sales` | `organization_id`, `branch_id`, `register_id`, `cash_session_id` nullable, `customer_id` nullable, `sale_number`, `status`, fecha, moneda, subtotal, descuento, impuesto, total, cambio, vendedor, confirmador, motivo cancelación | UNIQUE (`organization_id`, `sale_number`); total consistente por líneas/pagos; sesión corresponde a caja/sucursal. |
| `sale_lines` | `sale_id`, `line_number`, `stock_item_id`, `product_unit_id`, sku/nombre fotográficos, cantidad, precio unitario, descuento, impuesto, costo unitario, totales, `inventory_movement_id` nullable | UNIQUE (`sale_id`, `line_number`); cantidad positiva; fotografías requeridas en confirmación. |
| `payment_methods` | `organization_id`, `code`, `name`, tipo, requiere referencia, afecta caja, activo | UNIQUE parcial por organización/código. |
| `sale_payments` | `sale_id`, `line_number`, `payment_method_id`, importe recibido/aplicado, referencia, metadatos JSONB, fecha | UNIQUE (`sale_id`, `line_number`); aplicado > 0; total aplicado debe igualar total venta. |
| `sale_returns` | `organization_id`, `branch_id`, `original_sale_id`, `return_number`, estado, fecha, motivo, usuario, totales | UNIQUE (`organization_id`, `return_number`); no exceder cantidades devueltas de venta origen. |
| `sale_return_lines` | `sale_return_id`, `original_sale_line_id`, cantidad, importe fotográfico, condición de reintegro, `stock_item_id` | cantidades positivas; acumulado por línea no supera original. |

**Venta confirmada:** conserva precios, impuestos, descuentos, costos y nombre/SKU de línea. Nunca se recalcula desde el producto al consultar historia.  
**Cancelación:** solo para venta en el estado y plazo permitidos; genera reversas de inventario/caja en lugar de borrar pagos o líneas.  
**Devolución:** es documento separado y puede restaurar existencias solo si la política y condición del artículo lo permiten.

Índices esenciales: `sales (organization_id, branch_id, confirmed_at DESC)`, `sales (cash_session_id, status)`, `sales (customer_id, confirmed_at DESC)`, `sale_lines (stock_item_id)`, `sale_returns (original_sale_id)`.

## 10. Caja y gastos

| Tabla | Campos esenciales | Restricciones importantes |
|---|---|---|
| `cash_sessions` | `organization_id`, `branch_id`, `register_id`, usuario responsable, estado, fondo inicial, abierto/cerrado, totales teóricos/contados/diferencia, aprobador, notas | Índice de sesión abierta; exclusión/índice parcial para impedir sesiones incompatibles según política. |
| `cash_movements` | `organization_id`, `branch_id`, `cash_session_id`, tipo, importe, medio de pago, fecha, documento origen tipo/id, motivo, usuario, notas | importe > 0; tipo CHECK: `opening_float`, `sale_payment`, `sale_change`, `return_payment`, `expense`, `withdrawal`, `income`, `deposit`, `closing_adjustment`. Solo inserción. |
| `cash_count_lines` | `cash_session_id`, `payment_method_id`, denominación nullable, cantidad, importe contado | UNIQUE por sesión/medio/denominación; importe >= 0. |
| `expenses` | `organization_id`, `branch_id`, `cash_session_id` nullable, categoría, proveedor/beneficiario, número, estado, moneda, importe, fecha, motivo, solicitante/aprobador, evidencia | importe > 0; gasto pagado desde caja exige sesión válida. |
| `expense_attachments` | `expense_id`, metadatos de archivo, hash, almacenamiento | FK a gasto; no almacena binario en BD. |

Para restringir una sesión activa por caja física se usará un índice único parcial sobre `register_id` WHERE `status IN ('open','counting')`. Si se aprueba otra política, se ajustará a la combinación definida en la Fase 3.

## 11. Auditoría, integración y reportes

| Tabla | Propósito | Diseño |
|---|---|---|
| `audit_logs` | Bitácora de acciones sensibles | `organization_id`, actor, sucursal, acción, tipo/id de entidad, valores antes/después JSONB, IP, user agent, request_id, fecha. Solo inserción. |
| `outbox_events` | Publicación confiable de eventos posteriores a transacción | `id`, organización, tipo, payload JSONB, ocurrió en, disponible en, publicado en, intentos, error. Índice de pendientes. |
| `external_catalog_sources` | Proveedor autorizado y configuración no secreta | organización, código, nombre, tipo, estado, última sincronización. |
| `external_catalog_items` | Datos importados/mapeados de proveedor | fuente, identificador externo, payload normalizado JSONB, hash, visto en, `product_id` nullable, estado de revisión. UNIQUE por fuente/ID externo. |
| `report_snapshots` (opcional) | Proyecciones/materializaciones de reportes costosos | organización, sucursal nullable, tipo, periodo, payload/calculados, refrescado en. No sustituye el origen transaccional. |

`audit_logs` se particionará por mes o trimestre cuando el volumen lo amerite. Antes de esa necesidad, los índices por organización/fecha y entidad serán suficientes. `outbox_events` tendrá un trabajador con bloqueo de filas (`FOR UPDATE SKIP LOCKED`) al implementarse las colas.

## 12. Integridad referencial y borrado

| Tipo de dato | Política |
|---|---|
| Documentos, líneas, movimientos, pagos, auditoría | `ON DELETE RESTRICT`; no soft delete; reversa documental. |
| Catálogos (productos, categorías, clientes, proveedores) | Soft delete solo si no se desea nuevo uso; FK histórica permanece. |
| Pivotes de acceso | `ON DELETE CASCADE` únicamente al retirar una asignación sin historial financiero. |
| Imágenes/evidencias | `ON DELETE CASCADE` desde el catálogo o gasto que las posee, con eliminación de archivo mediante proceso controlado. |
| Organización | No eliminable desde aplicación; baja administrativa/archivado con retención. |

Los borrados físicos de catálogo solo se permitirán cuando no existan referencias operativas, mediante una tarea administrativa explícita y auditable; no en flujos normales.

## 13. Campos calculados y materializados

| Concepto | Fuente de verdad | Estrategia |
|---|---|---|
| Total de venta/compra | Líneas y reglas aplicadas | Se guarda fotografía en cabecera para consulta; se valida al confirmar. |
| Disponible de inventario | Movimientos | `inventory_balances` materializado y protegido por transacción. |
| Valor de inventario | Saldo × costo vigente/capas | Consulta/proyección; no escribir como única verdad. |
| Efectivo teórico de caja | Movimientos de caja | Se guarda resumen al cierre y se puede recalcular. |
| Diferencia de arqueo | Contado − teórico | Columna calculada en aplicación/consulta, persistida al cierre como fotografía. |
| Utilidad de venta | Precio neto − costo histórico | Calculada desde líneas de venta confirmadas. |

PostgreSQL generated columns se reservarán para expresiones locales, deterministas y simples. No se usarán para totales que dependan de otras filas, ya que esos requieren transacción, validación y trazabilidad.

## 14. Seguridad de datos y rendimiento

1. Todas las consultas de aplicación se filtran por `organization_id`; un Global Scope de Laravel ayuda, pero la autorización de aplicación sigue siendo obligatoria.
2. Se evaluará Row-Level Security (RLS) después de validar compatibilidad con conexiones, trabajos y administración. La recomendación inicial es activarla para instalaciones multi-tenant centralizadas; para instalación única se mantiene como defensa futura.
3. Índices B-tree para FK, filtros frecuentes y ordenamientos; GIN para `JSONB` solo ante consultas probadas que lo requieran.
4. Búsqueda de productos usará `pg_trgm` y/o full-text search en `name`, `sku` y código, con índices específicos. Código de barras siempre usa B-tree exacto.
5. Las lecturas de dashboard usarán proyecciones/consultas agregadas, sin bloquear las transacciones del POS.
6. El rol de aplicación no tendrá permisos de `DELETE` sobre tablas de hechos ni de escritura directa sobre saldos fuera de casos de uso controlados.

## 15. Decisiones pendientes que bloquean el modelo físico final

| Tema | Recomendación | Debe aprobarse antes de migraciones |
|---|---|---|
| Método de costo | Promedio ponderado por stock item y sucursal | Sí |
| Stock negativo | Prohibido por defecto, excepción auditable | Sí |
| Identidad | UUID v7/ordenable si está disponible en la plataforma; UUID estándar como alternativa | Sí |
| Sesión de caja | Una sesión abierta por caja física | Sí |
| Moneda | MXN como configuración inicial, campo ISO preparado para futuro | Sí |
| Retención auditoría | Mínimo 5 años, sujeto a política legal/negocio | Sí |
| Impuestos/facturación | Catálogo configurable; reglas fiscales detalladas en fase específica | Sí, antes de facturación |

## 16. Puerta de salida de la Fase 4

Esta fase está lista al aprobar:

- Entidades y relaciones principales.
- Uso de `stock_items` como sujeto único de inventario/venta.
- Libro inmutable de movimientos para inventario y caja, con saldos materializados.
- Fotografías históricas en documentos comerciales.
- Política propuesta de índices, auditoría, soft delete y aislamiento por organización.
- Decisiones pendientes antes de crear migraciones.

**Siguiente fase propuesta (solo tras aprobación):** Fase 5 — especificación de flujos detallados de POS, inventario, compras y caja; con sus validaciones, excepciones y puntos de autorización. Tras esa aprobación se podrá preparar la documentación de convenciones y el plan de implementación, aún sin escribir funcionalidad de negocio.
