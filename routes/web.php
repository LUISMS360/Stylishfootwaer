<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::livewire('/productos', 'pages::shop.product-index')->name('products.index');



Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas clientes
    Route::middleware('isCustomer')->group(function () {
        Route::livewire('/cliente/home', 'pages::clients.home')->name('customer.home');
        Route::livewire('/carrito', 'pages::car.car-index')->name('cart.index');
        Route::livewire('/ordenes', 'pages::clients.orders.orders-index')->name('orders.index');
    });

    // Rutas administradores
    Route::middleware('isAdmin')->group(function () {
        Route::livewire('/admin/productos/crear', 'pages::admin.products.product-form')->name('products.create');
        Route::livewire('/admin/productos/crear/variante', 'pages::admin.products.product-variant-form')->name('products.create-variant');
        Route::livewire('/admin/dashboard', 'pages::admin.dashboard')->name('admin.dashboard');
        Route::livewire('/admin/marcas', 'pages::admin.brands.brand-index')->name('admin.brand.index');
        Route::livewire('/admin/categorias', 'pages::admin.categories.category-index')->name('admin.categories-index');
        Route::livewire('/admin/ordenes', 'pages::admin.orders.order-index')->name('admin.orders.index');
        Route::livewire('/admin/ordenes/show', 'pages::admin.orders.order-show')->name('admin.orders.show');
        Route::livewire('/admin/products', 'pages::admin.products.product-index')->name('admin.products-index');
    });
});

require __DIR__ . '/auth.php';
