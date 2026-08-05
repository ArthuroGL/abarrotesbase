# ABARROTESBASE — Fase 2: Visión, Objetivos y Requerimientos Base

**Estado:** Propuesta para aprobación  
**Alcance:** Define el valor del producto, usuarios, alcance inicial, requisitos funcionales y no funcionales. Incluye la estrategia de interfaz modular. No incluye código, tablas, migraciones ni pantallas implementadas.

## 1. Qué estamos construyendo

Un sistema ERP + POS de escritorio web para pequeños y medianos comercios, capaz de registrar y controlar la operación diaria desde una misma plataforma: ventas, inventario, compras, caja, gastos y administración.

El producto prioriza tres resultados:

1. Vender con rapidez y con información correcta en caja.
2. Conocer existencias, costos, utilidad y dinero disponible sin cálculos manuales.
3. Mantener trazabilidad suficiente para detectar y explicar cualquier diferencia operativa.

## 2. Problema que resuelve

En el comercio objetivo, una venta puede depender de etiquetas, memoria del empleado o libretas. Esto provoca errores de precio, faltantes no detectados, compras reactivas, caja sin conciliación y decisiones sin datos.

ABARROTESBASE convierte esas actividades en procesos registrados y verificables:

| Situación actual | Resultado esperado |
|---|---|
| Búsqueda manual de precio o código | Búsqueda inmediata, código de barras y precio vigente |
| Stock estimado | Existencia por sucursal y movimientos explicables |
| Compras por intuición | Alertas y análisis de rotación, mínimos y proveedores |
| Ganancia desconocida | Utilidad basada en precio y costo históricos |
| Caja informal | Apertura, movimientos, arqueo y cierre auditables |
| Cambios sin responsable | Historial del actor, cuándo y qué cambió |

## 3. Usuarios y necesidades

| Perfil | Necesidad principal | Áreas usadas con mayor frecuencia |
|---|---|---|
| Cajero/a | Cobrar rápido y sin errores | POS, consulta de precio, caja |
| Encargado/a de tienda | Operar y corregir excepciones autorizadas | POS, inventario, caja, gastos |
| Comprador/a | Reponer a tiempo y controlar proveedores | Compras, inventario, proveedores |
| Administrador/a | Configurar, supervisar utilidad y permisos | Dashboard, reportes, usuarios, configuración |
| Propietario/a | Conocer situación real del negocio | Dashboard, reportes, auditoría |
| Auditor/a interno | Explicar una operación y sus cambios | Auditoría, inventario, caja, ventas |

## 4. Objetivos medibles del producto

Las métricas exactas de referencia se establecerán tras conocer la operación real, pero el sistema deberá permitir medirlas desde el inicio.

| Objetivo | Indicador |
|---|---|
| Agilizar la venta | Tiempo de atención y ventas por hora/caja |
| Reducir errores de cobro | Cancelaciones, devoluciones, ajustes y diferencias de precio |
| Mantener inventario confiable | Diferencia entre inventario teórico y conteos físicos |
| Mejorar disponibilidad | Quiebres, días sin existencia y cumplimiento de mínimos |
| Proteger efectivo | Diferencia de arqueo por sesión de caja |
| Mejorar rentabilidad | Margen por producto, categoría, sucursal y periodo |
| Reducir captura manual | Productos incorporados por código, importación autorizada o reutilización de catálogo |

## 5. Alcance funcional por etapas

### MVP operativo (primera liberación)

Permite operar una tienda con seguridad y trazabilidad básica.

- Organización, sucursal y configuración base.
- Usuarios, roles y permisos.
- Catálogos: productos, categorías, marcas, unidades, impuestos, clientes y proveedores.
- Productos por pieza, caja con conversión y granel; códigos y variantes.
- Precios, listas iniciales e historial de cambios.
- Inventario: existencias, movimientos, ajustes y conteos básicos.
- Compras: registro y recepción con impacto de inventario y costo.
- POS: venta, múltiples formas de pago, ticket interno, cancelación y devolución controlada.
- Caja: apertura, ingresos/retiros, arqueo y cierre.
- Gastos operativos ligados a caja cuando corresponda.
- Dashboard operativo y reportes esenciales.
- Auditoría de operaciones sensibles.

### Evolución posterior

- Transferencias avanzadas entre sucursales.
- Crédito a clientes y cuentas por cobrar, si se aprueba el riesgo comercial.
- Promociones complejas, cupones y reglas de descuento combinables.
- Importación e integración autorizada con catálogos externos.
- API pública y aplicaciones móviles.
- Facturación electrónica, solo tras definir requisitos fiscales, proveedor y alcance.
- Pronósticos de demanda y sugerencias de compra.

### Fuera de alcance inicial

- E-commerce y marketplace.
- Nómina y contabilidad fiscal completa.
- Integración automática no autorizada con catálogos de terceros.
- Operación sin conexión: se evaluará como iniciativa específica por su impacto en concurrencia e inventario.

## 6. Requerimientos funcionales prioritarios

La prioridad usa MoSCoW: **Debe**, **Debería**, **Podría** y **No inicial**.

### 6.1 Administración y acceso

| ID | Requerimiento | Prioridad |
|---|---|---|
| RF-ADM-01 | Administrar organización, sucursales y parámetros operativos. | Debe |
| RF-ADM-02 | Crear, activar, desactivar y asignar usuarios a sucursales. | Debe |
| RF-ADM-03 | Aplicar roles y permisos por operación, no solo por pantalla. | Debe |
| RF-ADM-04 | Registrar auditoría de acciones sensibles. | Debe |
| RF-ADM-05 | Permitir autorizaciones excepcionales con responsable y motivo. | Debería |

### 6.2 Productos y catálogos

| ID | Requerimiento | Prioridad |
|---|---|---|
| RF-PRO-01 | Registrar productos por pieza, granel, caja y sus combinaciones permitidas. | Debe |
| RF-PRO-02 | Configurar unidades base y conversiones explícitas. | Debe |
| RF-PRO-03 | Gestionar variantes como color, presentación o capacidad. | Debe |
| RF-PRO-04 | Buscar e identificar por código de barras o código interno. | Debe |
| RF-PRO-05 | Mantener categorías, marcas, impuestos e imágenes. | Debe |
| RF-PRO-06 | Mantener listas de precios y su historial. | Debe |
| RF-PRO-07 | Importar productos desde catálogos autorizados con revisión. | Podría |

### 6.3 Inventario y compras

| ID | Requerimiento | Prioridad |
|---|---|---|
| RF-INV-01 | Consultar disponibilidad por producto y sucursal. | Debe |
| RF-INV-02 | Registrar cada entrada, salida, devolución y ajuste con motivo y origen. | Debe |
| RF-INV-03 | Configurar mínimos y alertar productos por reponer. | Debe |
| RF-INV-04 | Realizar conteos físicos y ajustes controlados. | Debe |
| RF-INV-05 | Registrar proveedores y compras. | Debe |
| RF-INV-06 | Confirmar recepción de compra e impactar costo y existencias. | Debe |
| RF-INV-07 | Gestionar transferencias intersucursal. | Debería |
| RF-INV-08 | Sugerir compra por rotación, mínimo y proveedor. | Podría |

### 6.4 POS, ventas y clientes

| ID | Requerimiento | Prioridad |
|---|---|---|
| RF-VEN-01 | Vender por escaneo, código, búsqueda o selección rápida. | Debe |
| RF-VEN-02 | Vender cantidades decimales para productos a granel. | Debe |
| RF-VEN-03 | Aplicar precios, impuestos y descuentos conforme a permisos y vigencias. | Debe |
| RF-VEN-04 | Aceptar efectivo y medios de pago configurables, incluidos pagos mixtos. | Debe |
| RF-VEN-05 | Emitir comprobante interno de venta e imprimir/reimprimir según permiso. | Debe |
| RF-VEN-06 | Cancelar o devolver mediante proceso autorizado y trazable. | Debe |
| RF-VEN-07 | Registrar y consultar clientes. | Debe |
| RF-VEN-08 | Otorgar crédito y controlar saldo de clientes. | Debería |

### 6.5 Caja y gastos

| ID | Requerimiento | Prioridad |
|---|---|---|
| RF-CAJ-01 | Abrir una sesión de caja con fondo inicial y responsable. | Debe |
| RF-CAJ-02 | Vincular cobros en efectivo de ventas a la sesión abierta. | Debe |
| RF-CAJ-03 | Registrar ingresos, retiros, gastos y depósitos con motivo. | Debe |
| RF-CAJ-04 | Realizar arqueo y cierre con diferencia explicada. | Debe |
| RF-CAJ-05 | Aplicar autorización a retiros, gastos o cierres con diferencia según umbral. | Debería |

### 6.6 Reportes y analítica

| ID | Requerimiento | Prioridad |
|---|---|---|
| RF-REP-01 | Mostrar indicadores operativos de ventas, caja, inventario y alertas. | Debe |
| RF-REP-02 | Consultar ventas, utilidad y margen por periodo, producto, categoría y sucursal. | Debe |
| RF-REP-03 | Consultar movimientos, existencia y valuación de inventario. | Debe |
| RF-REP-04 | Consultar compras y desempeño de proveedores. | Debería |
| RF-REP-05 | Exportar reportes autorizados. | Debería |

## 7. Estrategia de interfaz modular

### Decisión

Se construirá una sola capa de diseño reutilizable antes de las pantallas funcionales. Blade será la tecnología de presentación, con componentes Blade, layouts, parciales, clases Tailwind reutilizables y módulos JavaScript ES6 por comportamiento.

Esto evita que cada vista defina por separado menús, tablas, formularios, botones, modales, estados de carga o reglas de interacción.

### Estructura de navegación propuesta

```text
Shell de aplicación
├── Barra superior
│   ├── sucursal activa
│   ├── búsqueda global (futura)
│   ├── notificaciones
│   └── menú de usuario
├── Barra lateral principal
│   ├── Dashboard
│   ├── POS
│   ├── Operación
│   │   ├── Ventas
│   │   ├── Caja
│   │   └── Gastos
│   ├── Inventario
│   │   ├── Productos
│   │   ├── Existencias y movimientos
│   │   ├── Compras
│   │   └── Proveedores
│   ├── Clientes
│   ├── Reportes
│   └── Administración
│       ├── Usuarios y roles
│       ├── Catálogos
│       ├── Configuración
│       └── Auditoría
└── Área de contenido
    ├── encabezado de módulo: título, contexto y acciones
    ├── pestañas locales: secciones de un mismo recurso
    └── contenido principal
```

El POS tendrá un layout especializado de alta densidad, pero conservará el mismo sistema visual, permisos, componentes y navegación de retorno. Las pestañas son locales a un recurso o módulo; no reemplazarán la barra lateral como navegación principal.

### Inventario inicial de componentes

| Familia | Componentes reutilizables previstos |
|---|---|
| Estructura | `app-shell`, barra lateral, barra superior, encabezado de página, breadcrumbs, pestañas |
| Acciones | botón, botón de ícono, grupo de acciones, menú contextual, confirmación |
| Formularios | campo de texto, moneda, cantidad, selector, autocompletar, escáner/código, fecha, interruptor, errores |
| Datos | tabla, filtros, paginación, tarjeta de indicador, etiqueta de estado, resumen, lista vacía |
| Superficies | panel, modal, drawer, alerta, toast, tooltip, carga/skeleton |
| POS | línea de venta, resumen de totales, selector de pago, teclado numérico, buscador de producto |

Los componentes expondrán variantes semánticas —por ejemplo, `primary`, `danger`, `warning`, `success`— en vez de que cada vista use clases visuales arbitrarias. La guía de diseño y tokens se documentarán antes de implementar las primeras pantallas.

### JavaScript modular

JavaScript se organizará por comportamiento y no por página completa: modal, búsqueda de producto, tabla filtrable, selector de pagos, notificaciones y escáner. Cada módulo tendrá una API pequeña, inicialización explícita y no contendrá reglas de negocio que el servidor deba validar.

**Impacto:** la UI se vuelve consistente, más rápida de construir y más segura de evolucionar, sin introducir Livewire, Vue, React o Inertia.

## 8. Requerimientos no funcionales

| ID | Requerimiento | Criterio de aceptación inicial |
|---|---|---|
| RNF-01 | Rendimiento de POS | Búsqueda y cobro habituales deben responder con fluidez en hardware de escritorio objetivo; se fijará presupuesto de milisegundos durante diseño UX/técnico. |
| RNF-02 | Integridad | Venta, recepción, ajuste y cierre de caja no pueden quedar parcialmente aplicados. |
| RNF-03 | Seguridad | Toda acción está autenticada, autorizada y validada del lado del servidor. |
| RNF-04 | Auditoría | Las operaciones sensibles permiten identificar actor, fecha, sucursal, motivo y efecto. |
| RNF-05 | Usabilidad | Los flujos de caja requieren mínima navegación y se pueden operar principalmente con teclado/escáner. |
| RNF-06 | Accesibilidad | Controles navegables por teclado, etiquetas, foco visible y contraste suficiente. |
| RNF-07 | Compatibilidad | Navegadores modernos de escritorio; diseño adaptable para consulta móvil. |
| RNF-08 | Mantenibilidad | Componentes de UI y casos de uso reutilizables, con pruebas proporcionales al riesgo. |
| RNF-09 | Observabilidad | Errores y operaciones relevantes generan registros consultables y correlacionables. |
| RNF-10 | Recuperación | Respaldos de base de datos y pruebas regulares de restauración. |
| RNF-11 | Privacidad | Acceso a datos personales limitado por rol, sucursal y necesidad operativa. |

## 9. Reglas transversales de aceptación

Todo módulo que se implemente deberá cumplir estas reglas desde el primer incremento:

1. Cada operación tiene autorización explícita y validación de servidor.
2. Los estados y transiciones de documentos se modelan de forma explícita; no se permiten cambios arbitrarios.
3. Los importes y cantidades no usan punto flotante.
4. Las modificaciones sensibles se auditan.
5. Las listas usan filtros, paginación y estados vacíos consistentes mediante componentes compartidos.
6. Ninguna pantalla crea estilos de navegación, botones, tablas o formularios fuera del sistema de componentes sin justificación aprobada.
7. El comportamiento JavaScript es progresivo: si falla, el servidor conserva las validaciones e integridad.

## 10. Decisiones tomadas en esta fase

1. La primera liberación será un MVP operativo completo, no un catálogo de pantallas aisladas.
2. La interfaz se diseñará primero como un shell común con navegación lateral, encabezado y pestañas locales.
3. Blade Components, Tailwind y módulos ES6 serán el sistema de componentes; no se usará Livewire, Inertia, Vue ni React.
4. El POS tendrá una variante de layout optimizada para velocidad, dentro del mismo sistema visual.
5. Todo diseño UI futuro deberá extender componentes y tokens existentes antes de crear estilos específicos.
6. Crédito, facturación, e-commerce e integración externa quedan fuera del MVP salvo aprobación explícita posterior.

## 11. Dependencias y preguntas a resolver en fases posteriores

- Política de costo de inventario: promedio ponderado, FIFO u otra; impacta utilidad y valuación.
- Reglas exactas de impuestos y redondeo aplicables al negocio/ubicación.
- Medios de pago, impresora/ticket y periféricos objetivo.
- Operación con una o múltiples cajas por sucursal.
- Alcance de devoluciones, cambios y cancelaciones.
- Política comercial para crédito a clientes.
- Requisitos fiscales si se incorpora facturación electrónica.

## 12. Puerta de salida de la Fase 2

Esta fase queda lista al aprobar:

- Alcance y prioridades del MVP.
- Navegación base, layout de pestañas y estrategia de componentes reutilizables.
- Requerimientos funcionales y no funcionales presentados.
- Exclusiones y decisiones pendientes identificadas.

**Siguiente fase propuesta (solo tras aprobación):** Fase 3 — modelo de dominio, historias de usuario y casos de uso priorizados. El diseño entidad-relación y la base de datos se realizará después de validar estos flujos de negocio.
