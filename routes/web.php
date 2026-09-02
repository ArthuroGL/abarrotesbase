<?php

use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;
use App\Modules\Dashboard\Presentation\Http\Controllers\DashboardController;
use App\Modules\Identity\Presentation\Http\Controllers\AuthController;
use App\Modules\Inventory\Presentation\Http\Controllers\BrandController;
use App\Modules\Inventory\Presentation\Http\Controllers\CategoryController;
use App\Modules\Inventory\Presentation\Http\Controllers\ProductController;
use App\Modules\Inventory\Presentation\Http\Controllers\StockController;
use App\Modules\Operation\Presentation\Http\Controllers\CashController;
use App\Modules\Operation\Presentation\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

// Rutas para Usuarios Invitados (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Rutas Protegidas (Auth)
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // Inventario - Productos
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    // Edición de Productos
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    //Api para búsqueda de productos por código de barras
    Route::get('/api/products/lookup/{barcode}', [ProductController::class, 'lookup'])->name('products.lookup');
});

Route::middleware('auth')->group(function () {
    // Categorías
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('/api/categories/quick-store', [CategoryController::class, 'quickStore'])->name('categories.quick-store');

    // Marcas
    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    Route::post('/api/brands/quick-store', [BrandController::class, 'quickStore'])->name('brands.quick-store');
    // Inventario - Existencias
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::post('/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');

    Route::resource('purchases', PurchaseController::class);
    Route::post('purchases/{purchase}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive');
    Route::post('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');

    Route::resource('suppliers', SupplierController::class);


    // Rutas para Caja
    Route::get('/cash', [CashController::class, 'index'])
        ->name('cash.index');

    Route::post('/cash/open', [CashController::class, 'open'])
        ->name('cash.open');
    // Rutas para Ventas

    Route::get('/sales', [SaleController::class, 'index'])
        ->name('sales.index');

    Route::get('/api/sales/lookup', [SaleController::class, 'lookup'])
        ->name('sales.lookup');

    Route::post('/sales', [SaleController::class, 'store'])
        ->name('sales.store');

    Route::get('/sales/history', [SaleController::class, 'history'])
        ->name('sales.history');

    Route::get('/sales/{sale}/ticket', [SaleController::class, 'ticket'])
        ->name('sales.ticket');

    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])
        ->name('sales.cancel');

    Route::get('/sales/{sale}', [SaleController::class, 'show'])
        ->name('sales.show');
});
