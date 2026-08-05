<?php

use App\Modules\Dashboard\Presentation\Http\Controllers\DashboardController;
use App\Modules\Identity\Presentation\Http\Controllers\AuthController;
use App\Modules\Inventory\Presentation\Http\Controllers\BrandController;
use App\Modules\Inventory\Presentation\Http\Controllers\CategoryController;
use App\Modules\Inventory\Presentation\Http\Controllers\ProductController;
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
});
