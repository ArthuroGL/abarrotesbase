<?php

use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;
use App\Modules\Dashboard\Presentation\Http\Controllers\DashboardController;
use App\Modules\Identity\Presentation\Http\Controllers\AuditController;
use App\Modules\Identity\Presentation\Http\Controllers\AuthController;
use App\Modules\Identity\Presentation\Http\Controllers\RoleController;
use App\Modules\Inventory\Presentation\Http\Controllers\BrandController;
use App\Modules\Inventory\Presentation\Http\Controllers\CategoryController;
use App\Modules\Inventory\Presentation\Http\Controllers\ProductController;
use App\Modules\Inventory\Presentation\Http\Controllers\StockController;
use App\Modules\Operation\Presentation\Http\Controllers\CashController;
use App\Modules\Operation\Presentation\Http\Controllers\ExpenseCategoryController;
use App\Modules\Operation\Presentation\Http\Controllers\ExpenseController;
use App\Modules\Operation\Presentation\Http\Controllers\SaleController;
use App\Modules\Operation\Presentation\Http\Controllers\SaleReturnController;
use App\Modules\Payment\Presentation\Http\Controllers\MercadoPagoWebhookController;
use App\Modules\Identity\Presentation\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Rutas para Usuarios Invitados
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login']);
});


/*
|--------------------------------------------------------------------------
| Rutas Protegidas
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    /*
|--------------------------------------------------------------------------
| USUARIOS
|--------------------------------------------------------------------------
*/

    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:users.view')
        ->name('users.index');

    Route::get('/users/create', [UserController::class, 'create'])
        ->middleware('permission:users.create')
        ->name('users.create');

    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:users.create')
        ->name('users.store');

    Route::get('/users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('permission:users.update')
        ->name('users.edit');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.update')
        ->name('users.update');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users.delete')
        ->name('users.destroy');
    Route::patch('/users/{user}/reactivate', [UserController::class, 'reactivate'])
        ->middleware('permission:users.update')
        ->name('users.reactivate');

    /*
|--------------------------------------------------------------------------
| ROLES Y PERMISOS
|--------------------------------------------------------------------------
*/

    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view')
        ->name('roles.index');

    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
        ->middleware('permission:roles.update')
        ->name('roles.edit');

    Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.update')
        ->name('roles.update');

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');


    /*
    |--------------------------------------------------------------------------
    | Búsqueda Global
    |--------------------------------------------------------------------------
    */

    Route::get('/search', [GlobalSearchController::class, 'index'])
        ->name('global.search');


    /*
    |--------------------------------------------------------------------------
    | INVENTARIO - PRODUCTOS
    |--------------------------------------------------------------------------
    */

    Route::get('/products', [ProductController::class, 'index'])
        ->middleware('permission:products.view')
        ->name('products.index');

    Route::get('/products/create', [ProductController::class, 'create'])
        ->middleware('permission:products.create')
        ->name('products.create');

    Route::post('/products', [ProductController::class, 'store'])
        ->middleware('permission:products.create')
        ->name('products.store');

    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
        ->middleware('permission:products.update')
        ->name('products.edit');

    Route::put('/products/{product}', [ProductController::class, 'update'])
        ->middleware('permission:products.update')
        ->name('products.update');

    Route::get(
        '/categories/search',
        [CategoryController::class, 'search']
    )->name('categories.search');

    Route::get(
        '/brands/search',
        [BrandController::class, 'search']
    )->name('brands.search');

    /*
    |--------------------------------------------------------------------------
    | API PRODUCTOS
    |--------------------------------------------------------------------------
    */

    Route::get('/api/products/lookup/{barcode}', [ProductController::class, 'lookup'])
        ->middleware('permission:products.view')
        ->name('products.lookup');

    Route::get('/api/products/search', [ProductController::class, 'search'])
        ->middleware('permission:products.view')
        ->name('products.search');


    /*
    |--------------------------------------------------------------------------
    | CATEGORÍAS
    |--------------------------------------------------------------------------
    |
    | Las categorías forman parte de la administración del catálogo
    | de productos.
    |
    */

    Route::get('/categories', [CategoryController::class, 'index'])
        ->middleware('permission:products.view')
        ->name('categories.index');

    Route::post('/categories', [CategoryController::class, 'store'])
        ->middleware('permission:products.create')
        ->name('categories.store');

    Route::post('/api/categories/quick-store', [CategoryController::class, 'quickStore'])
        ->middleware('permission:products.create')
        ->name('categories.quick-store');


    /*
    |--------------------------------------------------------------------------
    | MARCAS
    |--------------------------------------------------------------------------
    */

    Route::get('/brands', [BrandController::class, 'index'])
        ->middleware('permission:products.view')
        ->name('brands.index');

    Route::post('/brands', [BrandController::class, 'store'])
        ->middleware('permission:products.create')
        ->name('brands.store');

    Route::post('/api/brands/quick-store', [BrandController::class, 'quickStore'])
        ->middleware('permission:products.create')
        ->name('brands.quick-store');


    /*
    |--------------------------------------------------------------------------
    | INVENTARIO - EXISTENCIAS
    |--------------------------------------------------------------------------
    */

    Route::get('/stock', [StockController::class, 'index'])
        ->middleware('permission:stock.view')
        ->name('stock.index');

    Route::post('/stock/adjust', [StockController::class, 'adjust'])
        ->middleware('permission:stock.adjust')
        ->name('stock.adjust');


    /*
    |--------------------------------------------------------------------------
    | COMPRAS
    |--------------------------------------------------------------------------
    */

    Route::get('/purchases', [PurchaseController::class, 'index'])
        ->middleware('permission:purchases.view')
        ->name('purchases.index');

    Route::get('/purchases/create', [PurchaseController::class, 'create'])
        ->middleware('permission:purchases.create')
        ->name('purchases.create');

    Route::post('/purchases', [PurchaseController::class, 'store'])
        ->middleware('permission:purchases.create')
        ->name('purchases.store');

    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])
        ->middleware('permission:purchases.view')
        ->name('purchases.show');

    Route::get('/purchases/{purchase}/edit', [PurchaseController::class, 'edit'])
        ->middleware('permission:purchases.update')
        ->name('purchases.edit');

    Route::put('/purchases/{purchase}', [PurchaseController::class, 'update'])
        ->middleware('permission:purchases.update')
        ->name('purchases.update');

    /*
    | Cancelación de compra
    */

    Route::post(
        '/purchases/{purchase}/cancel',
        [PurchaseController::class, 'cancel']
    )
        ->middleware('permission:purchases.cancel')
        ->name('purchases.cancel');

    /*
    | Recepción de compra
    */

    Route::post(
        '/purchases/{purchase}/receive',
        [PurchaseController::class, 'receive']
    )
        ->middleware('permission:purchases.receive')
        ->name('purchases.receive');

    /*
    | API Compras
    */

    Route::get('/api/purchases/search', [PurchaseController::class, 'search'])
        ->middleware('permission:purchases.view')
        ->name('purchases.search');

    Route::get(
        '/api/purchases/product-units/{stockItemId}',
        [PurchaseController::class, 'productUnits']
    )
        ->middleware('permission:purchases.create')
        ->name('purchases.product-units');


    /*
    |--------------------------------------------------------------------------
    | PROVEEDORES
    |--------------------------------------------------------------------------
    */

    Route::get('/suppliers', [SupplierController::class, 'index'])
        ->middleware('permission:suppliers.view')
        ->name('suppliers.index');

    Route::get('/suppliers/create', [SupplierController::class, 'create'])
        ->middleware('permission:suppliers.create')
        ->name('suppliers.create');

    Route::post('/suppliers', [SupplierController::class, 'store'])
        ->middleware('permission:suppliers.create')
        ->name('suppliers.store');

    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])
        ->middleware('permission:suppliers.view')
        ->name('suppliers.show');

    Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])
        ->middleware('permission:suppliers.update')
        ->name('suppliers.edit');

    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])
        ->middleware('permission:suppliers.update')
        ->name('suppliers.update');

    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])
        ->middleware('permission:suppliers.delete')
        ->name('suppliers.destroy');

    /*
    | API Proveedores
    */

    Route::get('/api/suppliers/search', [SupplierController::class, 'search'])
        ->middleware('permission:suppliers.view')
        ->name('suppliers.search');


    /*
    |--------------------------------------------------------------------------
    | CAJA
    |--------------------------------------------------------------------------
    */

    Route::get('/cash', [CashController::class, 'index'])
        ->middleware('permission:cash.view')
        ->name('cash.index');

    Route::post('/cash/open', [CashController::class, 'open'])
        ->middleware('permission:cash.open')
        ->name('cash.open');

    Route::post('/cash/movements', [CashController::class, 'storeMovement'])
        ->middleware('permission:cash.movement')
        ->name('cash.movements.store');

    Route::post('/cash/{cashSession}/counting', [CashController::class, 'startCounting'])
        ->middleware('permission:cash.close')
        ->name('cash.counting');

    Route::post('/cash/{cashSession}/close', [CashController::class, 'close'])
        ->middleware('permission:cash.close')
        ->name('cash.close');

    Route::get('/cash/history', [CashController::class, 'history'])
        ->middleware('permission:cash.history')
        ->name('cash.history');

    Route::get('/cash/{cashSession}', [CashController::class, 'show'])
        ->middleware('permission:cash.view')
        ->name('cash.show');


    /*
    |--------------------------------------------------------------------------
    | DEVOLUCIONES
    |--------------------------------------------------------------------------
    */

    Route::get('/returns', [SaleReturnController::class, 'index'])
        ->middleware('permission:sales.return')
        ->name('returns.index');

    Route::get('/returns/sale/{sale}', [SaleReturnController::class, 'show'])
        ->middleware('permission:sales.return')
        ->name('returns.show');

    Route::post('/returns/sale/{sale}', [SaleReturnController::class, 'store'])
        ->middleware('permission:sales.return')
        ->name('returns.store');


    /*
    |--------------------------------------------------------------------------
    | VENTAS
    |--------------------------------------------------------------------------
    */

    Route::get('/sales', [SaleController::class, 'index'])
        ->middleware('permission:sales.view')
        ->name('sales.index');

    Route::get('/api/sales/lookup', [SaleController::class, 'lookup'])
        ->middleware('permission:sales.view')
        ->name('sales.lookup');

    Route::post('/sales', [SaleController::class, 'store'])
        ->middleware('permission:sales.create')
        ->name('sales.store');

    Route::get('/sales/history', [SaleController::class, 'history'])
        ->middleware('permission:sales.view')
        ->name('sales.history');

    Route::get('/sales/{sale}/ticket', [SaleController::class, 'ticket'])
        ->middleware('permission:sales.view')
        ->name('sales.ticket');

    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])
        ->middleware('permission:sales.cancel')
        ->name('sales.cancel');

    Route::get('/sales/{sale}', [SaleController::class, 'show'])
        ->middleware('permission:sales.view')
        ->name('sales.show');

    Route::get('/api/sales/search', [SaleController::class, 'search'])
        ->middleware('permission:sales.view')
        ->name('sales.search');

    /*
    | Mercado Pago Point
    */

    Route::post(
        '/sales/point/start',
        [SaleController::class, 'startPointPayment']
    )
        ->middleware('permission:sales.create')
        ->name('sales.point.start');

    Route::post(
        '/sales/point/{paymentTransaction}/finalize',
        [SaleController::class, 'finalizePointPayment']
    )
        ->middleware('permission:sales.create')
        ->name('sales.point.finalize');


    /*
    |--------------------------------------------------------------------------
    | GASTOS
    |--------------------------------------------------------------------------
    */

    Route::get('/expenses', [ExpenseController::class, 'index'])
        ->middleware('permission:expenses.view')
        ->name('expenses.index');

    Route::get('/expenses/create', [ExpenseController::class, 'create'])
        ->middleware('permission:expenses.create')
        ->name('expenses.create');

    Route::post('/expenses', [ExpenseController::class, 'store'])
        ->middleware('permission:expenses.create')
        ->name('expenses.store');

    Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])
        ->middleware('permission:expenses.view')
        ->name('expenses.show');

    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])
        ->middleware('permission:expenses.update')
        ->name('expenses.edit');

    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])
        ->middleware('permission:expenses.update')
        ->name('expenses.update');

    Route::post('/expenses/{expense}/approve', [ExpenseController::class, 'approve'])
        ->middleware('permission:expenses.approve')
        ->name('expenses.approve');

    Route::post('/expenses/{expense}/reject', [ExpenseController::class, 'reject'])
        ->middleware('permission:expenses.reject')
        ->name('expenses.reject');

    Route::post('/expenses/{expense}/pay', [ExpenseController::class, 'pay'])
        ->middleware('permission:expenses.pay')
        ->name('expenses.pay');

    Route::post('/expenses/{expense}/cancel', [ExpenseController::class, 'cancel'])
        ->middleware('permission:expenses.cancel')
        ->name('expenses.cancel');


    /*
    |--------------------------------------------------------------------------
    | CATEGORÍAS DE GASTOS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/expense-categories',
        [ExpenseCategoryController::class, 'index']
    )
        ->middleware('permission:expenses.view')
        ->name('expenses.categories.index');

    Route::post(
        '/expense-categories',
        [ExpenseCategoryController::class, 'store']
    )
        ->middleware('permission:expenses.create')
        ->name('expenses.categories.store');


    /*
|--------------------------------------------------------------------------
| AUDITORÍA
|--------------------------------------------------------------------------
*/

    Route::get('/audit', [AuditController::class, 'index'])
        ->middleware('permission:audit.view')
        ->name('audit.index');
});


/*
|--------------------------------------------------------------------------
| Webhook Mercado Pago
|--------------------------------------------------------------------------
|
| No debe usar auth ni permission porque Mercado Pago realiza la
| petición directamente hacia este endpoint.
|
*/

Route::post(
    '/webhooks/mercadopago',
    [MercadoPagoWebhookController::class, 'handle']
)->name('webhooks.mercadopago');
