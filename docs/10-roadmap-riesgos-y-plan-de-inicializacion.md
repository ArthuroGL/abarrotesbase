# ABARROTESBASE — Fase 10: Roadmap, Riesgos y Plan de Inicialización

**Estado:** Propuesta para aprobación  
**Alcance:** Orden de entregas, dependencias, criterios de salida, riesgos y preparación técnica previa al desarrollo. No crea código, repositorio Laravel, migraciones ni dependencias.

## 1. Qué estamos planificando

La transición de arquitectura aprobada a un producto operativo. El roadmap prioriza primero integridad, seguridad y operación de caja/inventario; después analítica, automatización e integraciones. No se considera completa una pantalla si el flujo que representa no es transaccional, autorizado, auditable y probado.

## 2. Estrategia de entrega

Se trabajará por incrementos verticales: cada uno incluye datos, dominio, caso de uso, autorización, interfaz modular, pruebas y documentación. No se construirá todo el backend y luego todas las pantallas, porque eso retrasa la validación de flujos reales.

```text
Fundación técnica
    → Identidad + catálogos + componentes base
    → Inventario y compras
    → POS y caja
    → Gastos, reportes y auditoría
    → Mejoras operativas e integraciones
```

Cada incremento se valida antes de iniciar el siguiente. Las decisiones pendientes se resuelven en la etapa que las necesita, no se ocultan en código.

## 3. Roadmap de producto

| Etapa | Qué se entrega | Dependencias | Criterio de salida |
|---|---|---|---|
| 0. Fundación | Laravel 12, PostgreSQL, Vite/Tailwind, calidad, CI, configuración de entornos y sistema visual base | Aprobación documental | Proyecto arranca, migra y prueba en entorno limpio; shell y componentes base disponibles. |
| 1. Organización y acceso | Organización, sucursales, cajas, usuarios, roles, permisos, sesión y auditoría base | Fundación | Usuario autorizado accede solo a su sucursal/capacidades; acciones sensibles se auditan. |
| 2. Catálogos comerciales | Unidades, impuestos, categorías, marcas, productos, variantes, códigos y precios | Acceso + DB base | Producto simple, caja y granel quedan correctamente configurados y buscables. |
| 3. Inventario y compras | Saldos, movimientos, mínimos, ajustes, conteos, proveedores, compras y recepciones | Catálogos | Recepción crea stock/costo; conteo/ajuste queda trazable y conciliable. |
| 4. POS y ventas | Carrito, escáner/búsqueda, pagos, descuento, ticket, cancelación/devolución | Inventario + precios + caja base | Venta por pieza/caja/granel actualiza inventario y pagos sin duplicidad. |
| 5. Caja y gastos | Apertura, retiros, ingresos, gastos, arqueo, cierre y diferencias | Ventas + permisos | Caja se concilia desde movimientos y las diferencias requieren explicación/autorización. |
| 6. Dashboard y reportes | Indicadores, reportes operativos, filtros, exportación controlada y reconciliación | Hechos de operación | Totales se concilian con documentos fuente y no degradan el POS. |
| 7. Madurez operativa | Transferencias, promociones, alertas avanzadas, optimización y recuperación | MVP estable | Flujos nuevos preservan los controles definidos y métricas de calidad. |
| 8. Integraciones/API | Catálogos autorizados, API, webhooks y periféricos seleccionados | Contratos/API, seguridad, operación estable | Adaptadores cumplen idempotencia, scopes, observabilidad y revisión humana. |

## 4. Backlog inicial priorizado

### 4.1 Fundación (Etapa 0)

1. Inicializar proyecto y configurar PHP, Composer, NPM, Laravel, PostgreSQL, Vite y Tailwind.
2. Definir `.env.example`, configuración por entorno, health check y guía de arranque.
3. Configurar formatter, análisis estático, pruebas con PostgreSQL y pipeline CI básico.
4. Crear shell de aplicación, tokens visuales y componentes UI esenciales sin pantallas de negocio.
5. Crear arquitectura modular, convenciones de rutas y pruebas de arquitectura.

### 4.2 Núcleo operacional (Etapas 1 a 5)

1. Organización, sucursales, usuarios, RBAC, autenticación, sesiones y auditoría.
2. Catálogos de unidades/impuestos/categorías/marcas y producto simple.
3. Unidades de producto: pieza, caja y granel; conversión decimal exacta.
4. Productos con variantes, códigos de barras, precios e historial.
5. Movimientos/saldos de inventario y carga inicial controlada.
6. Proveedores, compras y recepción; costo promedio ponderado si se aprueba.
7. Apertura de caja y consulta de sesión.
8. POS de venta, pagos, cambio y reducción de stock.
9. Cancelación/devolución controlada.
10. Retiros, ingresos, gastos, arqueo y cierre.

### 4.3 Información y madurez (Etapas 6 a 8)

1. Dashboard de hoy y alertas de operación.
2. Reportes de ventas, inventario, caja, gastos y utilidad.
3. Conciliaciones, exportaciones asíncronas y proyecciones cuando el volumen lo exija.
4. Transferencias, promociones y alertas de compra.
5. Importaciones CSV/XLSX controladas.
6. Adaptadores de catálogo autorizados, API y webhooks.

## 5. Dependencias críticas

| Decisión/dependencia | Afecta | Fecha límite para decidir |
|---|---|---|
| Costo promedio ponderado vs. FIFO | Recepciones, margen, valuación, devoluciones | Antes de Etapa 3 |
| Política de stock negativo | POS, inventario, autorizaciones | Antes de Etapa 3/4 |
| Una sesión abierta por caja física | Caja y POS | Antes de Etapa 1/5 |
| Impuestos y redondeo aplicables | Productos, precios, venta, reportes | Antes de Etapa 2/4 |
| Hardware objetivo (impresora/báscula) | Diseño POS e integración | Antes de piloto de Etapa 4 |
| Operación multi-sucursal inicial | Ámbitos, transferencias, despliegue | Antes de Etapa 1 |
| Requisitos fiscales | Venta/facturación/reportes | Antes de cualquier módulo fiscal |
| Infraestructura de producción | Backups, colas, almacenamiento, monitoreo | Antes de staging/producción |

## 6. Riesgos del programa

| Riesgo | Impacto | Prevención/mitigación | Señal temprana |
|---|---|---|---|
| Cambiar reglas de costo tarde | Alto | Aprobar política y probar escenarios de recepción/devolución antes de POS | Diferencias en valuación/margen de pruebas |
| Construir pantallas sin reglas | Alto | Incrementos verticales y criterios de salida | Vistas con lógica duplicada o sin pruebas |
| Exceso de alcance en MVP | Alto | Priorización MoSCoW y etapas cerradas | Se añaden integraciones/facturación antes de caja estable |
| Datos iniciales incorrectos | Alto | Importación con preview, validación y conteo de carga | Códigos duplicados/saldos no conciliables |
| POS lento | Alto | Medir consultas, índices, pruebas de carga focalizadas | Latencia alta en búsqueda/cobro |
| Diferencias de caja/stock sin explicación | Alto | Movimientos inmutables, auditoría, arqueos y conteos | Ajustes manuales frecuentes |
| Cuentas compartidas | Medio/alto | Cuentas nominativas, MFA y política operativa | Acciones no atribuibles |
| Dependencia de proveedor externo | Medio | Adaptadores, colas, revisión y sin dependencia de venta | API externa caída bloquea operación |
| Sin pruebas de recuperación | Alto | Backups y restauraciones en staging | Backups sin evidencia de restauración |

## 7. Criterios de salida para el MVP operativo

El MVP está listo para piloto cuando se cumplan todos los puntos:

- Usuario, roles, sucursales y permisos impiden accesos/operaciones no autorizadas.
- Productos por pieza, caja y granel se configuran, buscan y venden con cantidades/precios exactos.
- Venta confirmada crea una sola vez documentos, pagos, movimientos de inventario y efectos de caja.
- Cancelación/devolución conserva trazabilidad y no altera hechos previos.
- Recepción de compra aumenta existencia y aplica política de costo aprobada.
- Ajustes y conteos generan movimientos explicables y auditados.
- Apertura, movimientos, arqueo y cierre de caja se concilian desde fuentes de datos.
- Reportes de ventas, inventario, caja y margen se concilian con los documentos fuente.
- La interfaz crítica POS es operable por teclado/escáner y presenta contexto de sucursal/caja.
- Pruebas automatizadas cubren casos críticos de éxito, rechazo, autorización e idempotencia.
- Backups/restauración, logs y monitoreo mínimo han sido verificados en staging.

## 8. Plan de inicialización técnica (Etapa 0)

Cuando se autorice empezar código, el orden de trabajo será:

1. Verificar versiones disponibles de PHP, Composer, Node/NPM, PostgreSQL y Git contra los requisitos acordados.
2. Crear la aplicación Laravel 12 en la carpeta raíz existente, preservando `docs/`.
3. Configurar conexión PostgreSQL y entorno de pruebas PostgreSQL aislado.
4. Instalar/configurar Tailwind y Vite con Blade; no instalar Livewire, Inertia, Vue ni React.
5. Añadir herramientas de calidad aprobadas (Pint, pruebas, análisis estático y CI).
6. Crear estructura modular y reglas de arquitectura vacías con pruebas que las hagan cumplir.
7. Implementar tokens de diseño, shell, navegación lateral, encabezado, pestañas y componentes UI base.
8. Crear autenticación base y solo después la primera migración funcional de organización/acceso.

No se crearán migraciones de negocio, pantallas de POS o integraciones antes de completar y validar la Fundación.

## 9. Validación de piloto

Antes de producción se realizará un piloto con datos no críticos o una sucursal controlada:

| Área | Prueba de aceptación |
|---|---|
| Catálogo | Alta/búsqueda de productos reales, códigos y unidades con revisión de duplicados. |
| Inventario | Carga inicial, venta, compra, conteo y ajuste conciliados. |
| POS | Venta por escáner, búsqueda, granel, pago mixto, cambio, cancelación y reimpresión. |
| Caja | Apertura, retiro, gasto, devolución, arqueo y cierre con/sin diferencia. |
| Seguridad | Roles sin privilegios indebidos, sesión, auditoría y autorización reforzada. |
| Reportes | Ventas/caja/stock/margen reconciliados contra muestra de documentos. |
| Recuperación | Restauración de backup y revisión de registros/colas. |

Los incidentes del piloto se clasifican por severidad. Un incidente de integridad, autorización, duplicidad de venta o pérdida de trazabilidad bloquea producción hasta corregirse y contar con prueba de regresión.

## 10. Estado documental y continuidad

La arquitectura esencial está documentada en las fases 1 a 10. Los documentos solicitados originalmente se cubren de forma consolidada así:

| Documento solicitado | Documento(s) actual(es) |
|---|---|
| Visión, objetivos, requisitos funcionales/no funcionales | Fase 2 |
| Arquitectura y diagrama general | Fase 1 |
| Historias de usuario y casos de uso | Fase 3 |
| Modelo ER y diseño de base de datos | Fase 4 |
| Módulos y flujos POS/inventario/compras/caja | Fases 1 y 5 |
| Roles, permisos y auditoría | Fase 6 |
| Dashboard/reportes | Fase 7 |
| API futura e integraciones | Fase 8 |
| Convenciones de código | Fase 9 |
| Roadmap | Esta fase |

Antes de iniciar código se elaborará el **manual técnico inicial** como documento operativo de Fundación (requisitos locales, comandos, variables, tests, arquitectura de despliegue y recuperación), ya que sus instrucciones exactas dependen de las versiones y herramientas confirmadas al inicializar el proyecto.

## 11. Puerta de salida de la Fase 10

Esta fase queda lista al aprobar:

- Orden de desarrollo por incrementos verticales y dependencias.
- Alcance y criterios de salida del MVP/piloto.
- Riesgos operativos y técnicos con mitigaciones.
- Plan explícito de Fundación antes de funcionalidades de negocio.

**Siguiente paso propuesto:** aprobar las decisiones pendientes (costo, stock negativo, caja, impuestos/hardware) y autorizar la Etapa 0 de implementación. En ese momento se creará el proyecto Laravel y el sistema de componentes base, siguiendo este roadmap.
