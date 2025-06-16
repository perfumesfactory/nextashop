<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Public Product Routes
Route::get('/products', [App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [App\Http\Controllers\ProductController::class, 'show'])->name('products.show');

// Cart Route
Route::get('/cart', App\Livewire\ShoppingCart::class)->name('cart.index');

// Checkout Route
Route::get('/checkout', App\Livewire\CheckoutPage::class)->name('checkout.index')->middleware('auth');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Admin Product Management Routes
    Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
        Route::get('/products', \App\Livewire\Admin\Products\ProductList::class)->name('products.index');
        Route::get('/products/create', \App\Livewire\Admin\Products\ProductForm::class)->name('products.create');
        Route::get('/products/{productId}/edit', \App\Livewire\Admin\Products\ProductForm::class)->name('products.edit');

        // Admin Category Management Routes
        Route::get('/categories', \App\Livewire\Admin\Categories\CategoryList::class)->name('categories.index');
        Route::get('/categories/create', \App\Livewire\Admin\Categories\CategoryForm::class)->name('categories.create');
        Route::get('/categories/{categoryId}/edit', \App\Livewire\Admin\Categories\CategoryForm::class)->name('categories.edit');

        // Admin Order Viewing Routes
        Route::get('/orders', \App\Livewire\Admin\Orders\OrderList::class)->name('orders.index');
        Route::get('/orders/{order}', \App\Livewire\Admin\Orders\OrderDetail::class)->name('orders.show');
    });
});

require __DIR__.'/auth.php';
