# ABARROTESBASE — Fase 7: Dashboard, Reportes y Métricas

**Estado:** Propuesta para aprobación  
**Alcance:** Indicadores, reportes, definiciones de cálculo, filtros, permisos y estrategia de lectura. No incluye consultas SQL, componentes visuales implementados ni exportaciones.

## 1. Qué estamos construyendo

Una capa de lectura que transforma ventas, compras, inventario, caja y gastos en información accionable, sin alterar los documentos fuente ni competir con el POS por recursos transaccionales.

El dashboard responde “¿qué debo atender ahora?”; los reportes responden “¿qué ocurrió, por qué y en qué detalle?”. Ambos usan los mismos conceptos y definiciones para evitar cifras contradictorias.

## 2. Principios de datos analíticos

1. Los documentos confirmados y movimientos inmutables son la fuente de verdad.
2. El periodo se filtra por la fecha operativa del hecho (`confirmed_at`, `occurred_at`, `closed_at`), no por su fecha de creación.
3. Cada métrica muestra organización, sucursal y zona horaria de contexto.
4. Totales monetarios se calculan con valores históricos de las líneas, no con precios/costos actuales.
5. El dashboard usa resúmenes y consultas acotadas; los reportes masivos se ejecutan en cola y se exportan de forma diferida.
6. Las definiciones de las métricas se documentan y versionan antes de mostrarlas a usuarios.
7. Las consultas respetan permisos por sucursal y sensibilidad: margen, efectivo y PII no se exponen por defecto a todos.

## 3. Dashboard operativo

### 3.1 Estructura propuesta

```text
Encabezado: sucursal · periodo · última actualización · filtros
  ├── KPI de hoy: ventas netas | tickets | ticket promedio | margen bruto*
  ├── Estado de caja: efectivo teórico | retiros/gastos | diferencia al cierre*
  ├── Alertas: bajo mínimo | stock negativo | caja abierta | conteos pendientes
  ├── Tendencias: ventas por hora | comparación de periodos
  ├── Productos: más vendidos | mayor margen | sin movimiento
  └── Operación: compras/recepciones pendientes | gastos recientes
```

Los bloques marcados con `*` requieren permisos de margen/caja. El shell de interfaz usará tarjetas, filtros, tablas y estados vacíos del sistema de componentes definido en Fase 2.

### 3.2 Indicadores clave

| Indicador | Definición | Fuente | Permiso mínimo |
|---|---|---|---|
| Ventas netas | Ventas confirmadas − devoluciones/cancelaciones aplicables, en periodo | `sales`, devoluciones | `report.sales.view` |
| Tickets | Número de ventas confirmadas netas según estado | `sales` | `report.sales.view` |
| Ticket promedio | Ventas netas / número de tickets, excluyendo divisor cero | `sales` | `report.sales.view` |
| Unidades vendidas | Suma de cantidad base de líneas netas de devolución | `sale_lines`, devoluciones | `report.sales.view` |
| Margen bruto | Ingreso neto − costo histórico neto de devolución | líneas de venta/devolución | `report.margin.view` |
| Margen % | Margen bruto / ingreso neto × 100 | cálculo | `report.margin.view` |
| Efectivo teórico | Fórmula de movimientos de sesión abierta/cerrada | `cash_movements` | `report.cash.view` |
| Diferencia de caja | Contado − teórico, tras arqueo/cierre | `cash_sessions` | `report.cash.view` |
| Stock bajo mínimo | Artículos cuya disponibilidad es inferior al mínimo configurado | balances + niveles | `report.inventory.view` |
| Valor de inventario | Existencia × costo vigente o capas, según política | balances/costos | `report.inventory.view` |

“Ventas netas” y “margen” deben presentar si incluyen impuestos, según la política comercial/fiscal aprobada. Recomendación inicial: ventas netas sin impuestos trasladados y margen calculado antes de impuestos no recuperables; la presentación podrá mostrar ambas cifras cuando sea necesaria.

## 4. Catálogo de reportes del MVP

### 4.1 Ventas

| Reporte | Pregunta que responde | Agrupación/filtros principales |
|---|---|---|
| Resumen de ventas | ¿Cuánto se vendió y cobró? | periodo, sucursal, cajero, estado, medio de pago |
| Detalle de ventas | ¿Qué tickets forman el total? | número, fecha, cliente, cajero, caja, estado |
| Ventas por producto | ¿Qué artículos venden más? | producto, categoría, marca, unidad, periodo, sucursal |
| Ventas por hora/día | ¿Cuándo hay mayor demanda? | hora, día, periodo, sucursal |
| Medios de pago | ¿Cómo pagan los clientes? | método, periodo, sucursal, caja |
| Descuentos y excepciones | ¿Qué descuentos se otorgaron y quién los autorizó? | usuario, motivo, producto, periodo |
| Cancelaciones/devoluciones | ¿Qué se revirtió y por qué? | motivo, vendedor, autorizador, periodo |

### 4.2 Inventario y compras

| Reporte | Pregunta que responde | Agrupación/filtros principales |
|---|---|---|
| Existencia actual | ¿Qué hay disponible por sucursal? | producto, categoría, sucursal, estado, bajo mínimo |
| Kardex | ¿Cómo cambió la existencia? | artículo, sucursal, movimiento, documento, periodo |
| Valuación de inventario | ¿Cuánto vale la mercancía? | categoría, producto, sucursal, fecha de corte |
| Bajo mínimo/sin existencia | ¿Qué debe reponerse? | sucursal, categoría, proveedor preferido |
| Rotación | ¿Qué mercancía gira lento o rápido? | artículo/categoría, periodo, sucursal |
| Ajustes y mermas | ¿Qué pérdidas o correcciones ocurrieron? | motivo, usuario, importe/cantidad, periodo |
| Compras por proveedor | ¿A quién se compra y cuánto? | proveedor, producto, periodo, sucursal |
| Variación de costo | ¿Qué costos subieron o bajaron? | artículo, proveedor, periodo |

### 4.3 Caja y gastos

| Reporte | Pregunta que responde | Agrupación/filtros principales |
|---|---|---|
| Sesiones de caja | ¿Cómo cerró cada caja/turno? | sucursal, caja, responsable, estado, periodo |
| Movimientos de caja | ¿De dónde salió/entró dinero? | tipo, medio, documento, usuario, periodo |
| Diferencias de arqueo | ¿Qué diferencias requieren revisión? | caja, responsable, umbral, estado de revisión |
| Gastos operativos | ¿En qué se gastó y quién aprobó? | categoría, proveedor, sucursal, periodo |
| Retiros y depósitos | ¿Qué efectivo se retiró o depositó? | responsable, destino, periodo |

### 4.4 Administración y auditoría

| Reporte | Pregunta que responde | Acceso |
|---|---|---|
| Actividad de usuarios | ¿Qué acciones relevantes realizó cada usuario? | Auditoría/administrador |
| Cambios de precio | ¿Qué cambió, cuándo y por quién? | Administrador/auditor |
| Excepciones autorizadas | ¿Qué operaciones fuera de política ocurrieron? | Administrador/auditor |
| Catálogo incompleto | ¿Qué productos no están listos para vender? | Administrador |

## 5. Definiciones de cálculos críticos

| Métrica | Fórmula / regla |
|---|---|
| Importe bruto de venta | Suma de líneas antes de descuentos e impuestos, según configuración de precio. |
| Descuento | Suma de descuentos fotográficos por línea; promociones y excepciones se distinguen. |
| Impuesto | Suma de impuestos calculados/fotografiados por línea; no se recalcula desde tasa actual. |
| Venta neta | Importe de líneas confirmadas menos importes devueltos/cancelados que la política incluya. |
| Costo de venta | Suma de `cantidad_base × costo_unitario_historico` de líneas netas. |
| Utilidad bruta | Venta neta − costo de venta. No incluye gastos operativos ni nómina. |
| Ticket promedio | Venta neta / tickets netos; `0` si no hay tickets. |
| Rotación inicial | Unidades vendidas en periodo / inventario promedio del periodo, con definición visible de inventario promedio. |
| Cobertura | Existencia disponible / consumo promedio diario; se muestra “sin consumo” si divisor es cero. |
| Diferencia de caja | Total contado − total teórico del mismo medio/sesión. |

El costo de una línea queda fijado al confirmar la venta. Cualquier ajuste posterior de costo no reescribe márgenes históricos; la política de corrección contable se definirá aparte si fuera necesaria.

## 6. Filtros y detalle progresivo

Todos los reportes usarán un patrón consistente:

1. Contexto obligatorio de organización y, por defecto, sucursal activa.
2. Rango de fechas explícito con accesos rápidos: hoy, ayer, semana, mes y personalizado.
3. Filtros opcionales propios del reporte (usuario, producto, categoría, proveedor, estado, medio de pago).
4. Tabla resumida con ordenamiento definido y paginación.
5. Enlace al detalle fuente —venta, compra, sesión, movimiento o producto— sujeto a permiso.
6. Exportación solo si el usuario la puede solicitar y el volumen es admisible.

No se usará un filtro global ambiguo que altere silenciosamente las métricas. La interfaz mostrará siempre los filtros activos y la zona horaria aplicable.

## 7. Proyecciones y rendimiento

### 7.1 Estrategia por nivel de costo

| Tipo de consulta | Estrategia |
|---|---|
| Indicador pequeño y de hoy | Consulta indexada sobre documentos confirmados/saldos, con caché breve si se requiere. |
| Listado operativo | Consulta paginada y filtrada, usando índices de Fase 4. |
| Tendencia diaria/horaria | Tabla de resumen o vista materializada refrescada por eventos/cola. |
| Exportación extensa | Trabajo en cola, archivo temporal privado y notificación de disponibilidad. |
| Reproceso de métricas | Trabajo administrativo que reconstruye una proyección desde hechos fuente, nunca edita fuentes. |

### 7.2 Proyecciones iniciales sugeridas

| Proyección | Dimensiones | Hechos origen | Actualización |
|---|---|---|---|
| `daily_sales_summary` | organización, sucursal, fecha, medio/punto de venta opcional | ventas y devoluciones confirmadas | evento/outbox y reproceso nocturno verificable |
| `daily_product_sales_summary` | organización, sucursal, fecha, stock item, categoría | líneas de venta/devolución | evento/outbox |
| `daily_cash_summary` | organización, sucursal, caja, fecha, medio | movimientos de caja | evento/outbox |
| `inventory_alert_projection` | organización, sucursal, stock item | balance + mínimos | cambio de saldo/configuración + tarea periódica |

Estas proyecciones no serán requisito para la primera pantalla si el volumen inicial permite consultas directas. Se introducen cuando se midan necesidades de rendimiento, manteniendo su capacidad de reconstrucción.

## 8. Experiencia de reportes

- Dashboard por defecto: “Hoy” y sucursal activa; usuarios corporativos pueden elegir todas las sucursales cuando el permiso lo permita.
- Las tarjetas muestran estado de actualización y enlace al detalle, no solo un número aislado.
- Datos sensibles muestran ocultamiento o ausencia según rol, no meramente un color diferente.
- Tablas grandes tienen búsqueda, filtros, columnas consistentes, paginación y exportación diferida.
- Gráficas se reservan para tendencias; no sustituyen las tablas cuando se necesita auditoría o detalle numérico.
- Estados vacíos explican si no hay actividad, si faltan permisos o si el filtro no produce datos.
- En el POS no se cargan paneles analíticos pesados; únicamente alertas operativas breves y autorizadas.

## 9. Calidad y conciliación

Cada reporte financiero/operativo debe poder conciliarse:

| Reporte | Conciliación base |
|---|---|
| Ventas por medio de pago | Suma de `sale_payments` contra ventas confirmadas netas. |
| Efectivo de caja | Movimientos `cash_movements` contra resumen de sesión. |
| Inventario actual | Saldos materializados contra suma de movimientos por artículo/sucursal. |
| Utilidad | Líneas de venta netas y costo histórico, sin usar precio/costo vigente. |
| Compras | Recepciones/líneas recibidas contra movimientos de entrada y costo. |

Se crearán pruebas de reconciliación antes de liberar reportes financieros. Cualquier diferencia deberá señalar datos fuente, periodo y estado de proyección, no ocultarse con un total manual.

## 10. Decisiones pendientes

| Tema | Recomendación | Impacto |
|---|---|---|
| Venta neta para tablero | Mostrar sin impuestos trasladados y ofrecer total cobrado como métrica separada | Claridad de margen y comparación |
| Fecha operativa | Zona horaria de organización, con corte configurable | Cierres diarios y reportes de turno |
| Rotación | Iniciar por unidades/stock promedio; refinar al tener historial suficiente | Compras y alertas |
| Exportaciones | CSV inicialmente; XLSX/PDF según necesidad y permisos | Tecnología y seguridad de archivos |
| Consolidado multi-sucursal | Permitido a administrador/propietario; cajeros solo su sucursal | Seguridad y rendimiento |

## 11. Puerta de salida de la Fase 7

Esta fase queda lista al aprobar:

- Dashboard operativo, indicadores y visibilidad por rol.
- Catálogo de reportes del MVP y sus filtros.
- Fórmulas consistentes de ventas, margen, rotación, inventario y caja.
- Estrategia de proyecciones, exportaciones y conciliación.

**Siguiente fase propuesta (solo tras aprobación):** Fase 8 — API futura, integraciones externas y estrategia de interoperabilidad. Después se cerrarán convenciones de código, roadmap y plan de implementación.
