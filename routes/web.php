<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UomController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to login or dashboard
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // POS / Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/create', [OrderController::class, 'create'])->middleware('can:order.create')->name('create');
        Route::post('/', [OrderController::class, 'store'])->middleware('can:order.create')->name('store');
        Route::get('/', [OrderController::class, 'index'])->middleware('can:order.view')->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->middleware('can:order.view')->name('show');
        Route::get('/{order}/receipt', [OrderController::class, 'receipt'])->middleware('can:order.view')->name('receipt');
    });

    // Stock Management
    Route::prefix('stocks')->name('stocks.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->middleware('can:stock.view')->name('index');
        Route::get('/{item}/add', [StockController::class, 'createStockIn'])->middleware('can:stock.add')->name('add');
        Route::post('/{item}/add', [StockController::class, 'storeStockIn'])->middleware('can:stock.add')->name('store');
    });

    Route::get('/stock-movements', [StockController::class, 'movements'])
        ->middleware('can:stock.history')
        ->name('stocks.movements');

    // Master Items
    Route::resource('items', ItemController::class)->except(['create', 'show', 'edit'])->middleware('can:item.view');

    // Master UoM
    Route::resource('uoms', UomController::class)->except(['create', 'show', 'edit'])->middleware('can:uom.view');

    // Master Prices
    Route::get('/prices', [PriceController::class, 'index'])->middleware('can:price.view')->name('prices.index');
    Route::put('/prices/{item}', [PriceController::class, 'update'])->middleware('can:price.update')->name('prices.update');

    // Master Payment Methods
    Route::resource('payment-methods', PaymentMethodController::class)->except(['create', 'show', 'edit'])->middleware('can:payment_method.view');

    // Master Users & Roles (Superadmin)
    Route::resource('users', UserController::class)->except(['create', 'show', 'edit'])->middleware('can:user.view');
    Route::resource('roles', RoleController::class)->except(['show'])->middleware('can:role.view');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales'])->middleware('can:report.sales')->name('sales');
        Route::get('/stock', [ReportController::class, 'stock'])->middleware('can:report.stock')->name('stock');
    });
});
