# ABARROTESBASE — Fase 9: Convenciones, Calidad y Estructura del Proyecto

**Estado:** Propuesta para aprobación  
**Alcance:** Estándares para la futura implementación Laravel 12: organización de código, responsabilidades, pruebas, calidad, UI modular y entrega. No crea el proyecto ni instala dependencias.

## 1. Objetivo

Establecer reglas que permitan que ABARROTESBASE crezca sin duplicar lógica, mezclar módulos o crear pantallas inconsistentes. Estas convenciones son obligatorias desde el primer incremento funcional y se revisan mediante automatización y revisión de cambios.

## 2. Estructura modular propuesta

```text
app/
  Modules/
    Sales/
      Domain/
        Entities/ ValueObjects/ Enums/ Events/ Contracts/
      Application/
        Commands/ Queries/ DTOs/ Services/ UseCases/
      Infrastructure/
        Persistence/ Repositories/ Listeners/ Providers/
      Presentation/
        Http/Controllers/ Requests/ Policies/ ViewModels/
    Inventory/
    Purchasing/
    Cash/
    Catalog/
    Identity/
    Reporting/
    Integrations/
  Shared/
    Domain/
    Application/
    Infrastructure/
    Presentation/
resources/
  views/
    components/
      ui/            # base: botón, campo, modal, tabla, etc.
      layout/        # shell, sidebar, topbar, tabs
      pos/           # compuestos exclusivos de POS
    layouts/
    modules/         # vistas por módulo, no lógica de negocio
  js/
    modules/         # comportamientos ES6 reutilizables
    pages/           # inicializadores pequeños por pantalla
    app.js
  css/
    app.css
database/
  migrations/
  factories/
  seeders/
tests/
  Unit/
  Feature/
  Integration/
  Architecture/
docs/
```

Esta es una estructura de organización, no una obligación de crear una carpeta por clase. Solo se crean subcarpetas cuando el módulo lo necesita; el propósito es hacer visibles las dependencias y responsabilidades.

## 3. Regla de dependencias entre módulos

```text
Presentation → Application → Domain
Infrastructure ────────────→ Domain / Application contracts
```

- Un módulo puede consumir un contrato público o evento de otro módulo; no sus modelos/repositorios internos.
- `Shared` contiene conceptos genuinamente transversales, no un lugar para clases sin propietario claro.
- No habrá dependencias circulares. Si Sales e Inventory se requieren mutuamente, se define un contrato/evento o se extrae un concepto de dominio compartido.
- Eloquent pertenece a infraestructura. Su uso directo en un controlador, componente Blade o clase de dominio está prohibido.
- Las consultas de lectura simples pueden vivir en servicios/query objects de aplicación; repositorios se reservan para contratos de persistencia con significado de dominio.

## 4. Convenciones PHP y Laravel

| Área | Convención |
|---|---|
| Estilo | PSR-12, `declare(strict_types=1);`, tipado de parámetros y retornos. |
| Clases | PascalCase; una responsabilidad clara; nombres que expresen intención (`ConfirmSale`, no `SaleService`). |
| Métodos/variables | camelCase; verbos para operaciones, sustantivos para consultas. |
| Enums | PHP native enums con valores estables para estados/tipos de dominio. |
| DTO | Inmutables cuando sea posible; representan entrada/salida de un caso de uso, no un Request HTTP. |
| Form Requests | Validan forma, autorizan la ruta y normalizan entrada superficial; no calculan negocio. |
| Controladores | Delgados: reciben request, crean DTO, invocan caso de uso y devuelven respuesta/vista. |
| Servicios/casos de uso | Un verbo de negocio por clase o método público; manejan transacción/orquestación. |
| Entidades | Encapsulan invariantes; no saben de HTTP, Blade, Eloquent o SQL. |
| Modelos Eloquent | Adaptadores de persistencia; `$fillable` explícito, casts declarados, relaciones sin lógica de negocio. |
| Excepciones | Tipadas por regla de dominio; se traducen centralmente a respuesta web/API amigable. |
| Fechas/dinero | Objetos/DTOs claros y `numeric` en persistencia; no `float` ni formatos de UI en dominio. |
| Comentarios | Explican decisión o restricción no obvia; no repiten lo que el código ya dice. |

### Ejemplo de límites correctos

```text
SaleController → ConfirmSaleRequest → ConfirmSaleData → ConfirmSale use case
ConfirmSale → SalePolicy / PriceResolver / InventoryGateway / CashGateway
ConfirmSale → transacción → evento SaleConfirmed
Listener asíncrono → actualiza proyección o prepara impresión
```

La primera línea no es una implementación ni define nombres finales; muestra la dirección permitida.

## 5. Persistencia y migraciones

- Una migración representa un cambio reversible y pequeño del esquema; no combina tablas no relacionadas.
- Nombrar índices, FKs y restricciones explícitamente cuando Laravel no proporcione un nombre legible/estable.
- Crear índice para cada FK consultada y verificar plan de consulta antes de añadir índices redundantes.
- Usar `numeric` para importes/cantidades, `uuid` para IDs y `timestamptz` de PostgreSQL según Fase 4.
- Las restricciones de base de datos complementan validaciones de aplicación; no se omiten porque exista un Form Request.
- No editar migraciones ejecutadas en un entorno compartido. Crear una migración correctiva.
- Seeders de catálogo/desarrollo son idempotentes y no contienen datos personales ni secretos.
- Factories generan estados válidos de dominio; los casos inválidos se crean intencionalmente dentro de pruebas específicas.

## 6. Convenciones Blade, Tailwind y JavaScript

### 6.1 Componentes visuales

- Todo módulo usa `app-shell` y encabezado común salvo excepción documentada (POS de alta densidad).
- Botones, campos, tablas, modales, estados vacíos, badges, alertas y paginación se implementan primero como componentes `resources/views/components/ui`.
- Un componente recibe intención/variante semántica (`variant="danger"`, `size="sm"`), no una colección libre de clases Tailwind desde cada vista.
- Los tokens visuales (color, espaciado, tipografía, radios, sombras y estados) viven centralizados en configuración/theme CSS; no se replican por pantalla.
- Las vistas de módulo componen componentes y presentan `ViewModels`; no consultan DB ni autorizan acciones.
- Cada pantalla define explícitamente loading, error, vacío, sin permisos y datos disponibles cuando corresponda.

### 6.2 JavaScript ES6

- Cada comportamiento se ubica en un módulo con inicialización explícita y limpieza cuando aplique.
- No se usa JavaScript inline ni listeners globales no encapsulados.
- `data-*` comunica la intención desde Blade a JavaScript; no se pasan reglas de negocio completas al navegador.
- Componentes como modal, toast, tabla filtrable, selector de pagos y buscador tienen API mínima y pruebas de comportamiento cuando sean críticos.
- Vite compila los entry points; cada pantalla carga solo los módulos que necesita.
- Accesibilidad: foco administrado en modal, Escape para cerrar cuando sea seguro, navegación por teclado y mensajes de error anunciables.

## 7. Estrategia de pruebas

La pirámide se ajusta al riesgo: las reglas de dinero, inventario y caja requieren muchas pruebas de unidad/integración; las vistas requieren pruebas de flujo y de regresión visual selectiva.

| Nivel | Propósito | Ejemplos |
|---|---|---|
| Unit | Reglas puras de dominio sin DB/HTTP | conversión de unidades, redondeo, transiciones de estado, cálculo de totales. |
| Integration | PostgreSQL real/entorno aislado para persistencia y transacciones | constraints, índices, concurrencia, movimientos y saldos. |
| Feature | Flujos HTTP y autorización Laravel | confirmar venta, cerrar caja, denegar descuento, filtrar reporte. |
| Architecture | Impedir dependencias prohibidas | dominio sin `Illuminate\Http`, controladores delgados, módulos sin ciclos. |
| Browser/UX | Casos críticos de interfaz | flujo de escáner/POS, foco, validaciones, navegación con teclado. |
| Regression | Errores corregidos | cada incidente crítico agrega prueba que lo reproduzca. |

### Cobertura y pruebas mínimas por flujo crítico

No se adopta un porcentaje global como sustituto de calidad. Un cambio que toca POS, inventario, compras, caja, permisos, dinero o auditoría debe tener pruebas de éxito, rechazo y reversa cuando aplique.

Mínimos antes de liberar una venta:

- Venta por pieza, caja y granel con conversión correcta.
- Pagos mixtos, cambio, descuento autorizado/no autorizado.
- Stock insuficiente y concurrencia/reintento idempotente.
- Movimiento de inventario y caja generado una sola vez.
- Cancelación/devolución sin borrar evidencia.
- Permisos y ámbitos de sucursal denegados correctamente.

## 8. Herramientas y validaciones de calidad

| Categoría | Herramienta/criterio propuesto |
|---|---|
| Formato | Laravel Pint con configuración del repositorio. |
| Análisis estático | PHPStan/Larastan en nivel gradual creciente; sin errores nuevos en código modificado. |
| Pruebas | PHPUnit o Pest, decisión única antes de iniciar; se recomienda Pest por legibilidad en flujos. |
| Arquitectura | Tests de arquitectura (Pest/PHPUnit) y revisión de dependencias de módulos. |
| Frontend | ESLint/Prettier si se incorpora configuración JavaScript; build Vite obligatorio. |
| Dependencias | Auditoría de Composer/NPM en CI y proceso de actualización. |
| Seguridad | Análisis de secretos, revisión de dependencias y headers/CSRF en pruebas de entorno. |
| Base de datos | Migración desde cero en CI y pruebas contra PostgreSQL, no SQLite para reglas específicas. |

Las herramientas exactas se confirmarán al inicializar el proyecto para verificar compatibilidad con Laravel 12, PHP 8.4 y el gestor de paquetes elegido.

## 9. Flujo de entrega y revisión

```text
Historia aprobada
  → caso de uso y criterios de aceptación
  → pruebas de dominio/feature primero o junto con cambio
  → implementación modular
  → formato + análisis estático + pruebas + build frontend
  → revisión de seguridad/arquitectura
  → entorno de prueba
  → liberación y monitoreo
```

### Lista de revisión de cambios

- ¿El cambio tiene propietario de módulo y no introduce dependencia circular?
- ¿La regla vive en dominio/aplicación y no en controlador, Blade o JavaScript?
- ¿La autorización se aplica en servidor y se cubre con prueba?
- ¿Se usan transacciones, decimal exacto y restricciones cuando afecta dinero/stock/caja?
- ¿Se preserva auditoría e inmutabilidad de documentos confirmados?
- ¿La interfaz reutiliza componentes y conserva accesibilidad/teclado?
- ¿Migración, índices y rollback son seguros?
- ¿Logs/errores no exponen secretos o PII?
- ¿Las pruebas cubren éxito, rechazo y escenario de excepción relevante?

## 10. Gestión de configuración y entornos

| Entorno | Finalidad | Regla |
|---|---|---|
| Local | Desarrollo individual | Datos sintéticos, `.env` no versionado, Docker/entorno documentado. |
| Test | Automatización | BD aislada PostgreSQL, datos efímeros, sin servicios externos reales. |
| Staging | Validación integrada | Configuración similar a producción, datos anonimizados/sintéticos. |
| Producción | Operación real | Secretos gestionados, logs/monitoreo, backups y acceso restringido. |

- `config` no contiene secretos de negocio ni credenciales.
- Variables necesarias se documentan en un archivo de ejemplo sin valores reales.
- Feature flags se usan para funcionalidades incompletas o integraciones; nunca para omitir controles de seguridad.
- Cambios de configuración sensible se auditan y exigen autorización administrativa.

## 11. Observabilidad y manejo de errores

- Logs estructurados: nivel, `request_id`, usuario (ID), organización, sucursal, módulo y entidad, sin datos sensibles innecesarios.
- Excepciones de dominio se convierten en mensajes claros y códigos estables; las excepciones inesperadas producen un ID de seguimiento, no detalles internos.
- Alertas: errores críticos, fallo de colas, agotamiento de disco/BD, backups fallidos, integraciones detenidas y diferencias de conciliación.
- Métricas técnicas: latencia del POS, tasa de errores, colas pendientes, duración de consultas lentas, uso de conexiones.
- Los intentos de recuperación/reintento son idempotentes y registrados.

## 12. Documentación viva

Los documentos de `docs/` son el contrato de diseño. Cada cambio que altere una decisión de negocio, una regla de datos, un permiso, un flujo o contrato externo debe actualizar el documento correspondiente en el mismo cambio.

Se mantendrá:

- Un registro de decisiones de arquitectura (ADR) para cambios relevantes.
- Un changelog orientado a usuarios/operación en liberaciones.
- OpenAPI versionado al comenzar API externa.
- Manual técnico actualizado con instalación, configuración, colas, backups y recuperación antes de producción.

## 13. Puerta de salida de la Fase 9

Esta fase queda lista al aprobar:

- Estructura modular, límites de dependencia y reglas de implementación.
- Sistema de componentes Blade/Tailwind y módulos JavaScript reutilizables.
- Estrategia de pruebas, calidad y revisión de cambios.
- Convenciones de migraciones, seguridad, entornos y documentación viva.

**Siguiente fase propuesta (solo tras aprobación):** Fase 10 — roadmap de entregas, dependencias, riesgos, criterios de salida y plan de inicialización técnica. Esa fase cerrará la documentación de arquitectura antes de crear el esqueleto Laravel y los componentes base.
