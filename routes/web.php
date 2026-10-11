<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PinLoginController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Business Registration (public)
Route::get('/register-business', [BusinessController::class, 'showRegistrationForm'])->name('business.register');
Route::post('/register-business', [BusinessController::class, 'register'])->name('business.register.submit');

// Midtrans webhook (unauthenticated, signature verified)
Route::post('/midtrans/webhook', [MidtransController::class, 'notification'])->name('midtrans.webhook');

Route::middleware('auth')->group(function () {
    // Kasir (POS) - Primary Main Interface
    Route::middleware('can:sales create')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('/sales/pos', [PosController::class, 'index'])->name('sales.pos');
        Route::get('/pos/search-products', [PosController::class, 'searchProducts'])->name('pos.search-products');
        Route::get('/pos/categories', [PosController::class, 'getCategories'])->name('pos.categories');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::post('/pos/hold', [PosController::class, 'holdTransaction'])->name('pos.hold');
        Route::get('/pos/held', [PosController::class, 'getHeldTransactions'])->name('pos.held');
        Route::post('/pos/resume/{id}', [PosController::class, 'resumeTransaction'])->name('pos.resume');
        Route::delete('/pos/held/{id}', [PosController::class, 'deleteHeldTransaction'])->name('pos.held.delete');
        Route::get('/pos/receipt/{sale}', [PosController::class, 'getReceipt'])->name('pos.receipt');
        Route::get('/pos/sales/{sale}/status', [PosController::class, 'getSaleStatus'])->name('pos.sale-status');
        Route::post('/pos/discount', [PosController::class, 'applyDiscount'])->name('pos.discount');
        Route::get('/pos/search-customers', [PosController::class, 'searchCustomers'])->name('pos.search-customers');
    });

    // Laporan Penjualan (Image 3)
    Route::middleware('can:reports view')->group(function () {
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/sales/export-excel', [ReportController::class, 'exportExcel'])->name('reports.sales.export-excel');
        Route::get('/reports/sales/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.sales.export-pdf');
    });

    // Produk & Stok (Image 4)
    Route::middleware('can:products view')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    });
    Route::middleware('can:products create')->group(function () {
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    });
    Route::middleware('can:products edit')->group(function () {
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    // Categories & Units API / Backend Endpoints
    Route::middleware('can:categories view')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });
    Route::middleware('can:units view')->group(function () {
        Route::get('/units', [UnitController::class, 'index'])->name('units.index');
        Route::get('/units/create', [UnitController::class, 'create'])->name('units.create');
        Route::get('/units/{unit}/edit', [UnitController::class, 'edit'])->name('units.edit');
        Route::post('/units', [UnitController::class, 'store'])->name('units.store');
        Route::put('/units/{unit}', [UnitController::class, 'update'])->name('units.update');
        Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');
    });

    // Stock
    Route::middleware('can:stock view')->group(function () {
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    });
    Route::middleware('can:stock create')->group(function () {
        Route::post('/stock', [StockController::class, 'store'])->name('stock.store');
    });

    // Pelanggan
    Route::middleware('can:sales create')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    });

    // Supplier & Pembelian (Image 2)
    Route::middleware('can:purchases view')->group(function () {
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    });
    Route::middleware('can:purchases create')->group(function () {
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::post('/purchases/supplier', [PurchaseController::class, 'storeSupplier'])->name('suppliers.store');
        Route::post('/purchases/{purchase}/items', [PurchaseItemController::class, 'store'])->name('purchases.items.store');
        Route::delete('/purchases/{purchase}/items/{item}', [PurchaseItemController::class, 'destroy'])->name('purchases.items.destroy');
        Route::post('/purchases/{purchase}/complete', [PurchaseController::class, 'complete'])->name('purchases.complete');
    });
    Route::middleware('can:purchases edit')->group(function () {
        Route::put('/purchases/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');
    });

    // Karyawan & Hak Akses (Image 5)
    Route::middleware('can:users view')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    });

    // Pengaturan (Image 1)
    Route::get('/business/settings', [BusinessController::class, 'settings'])->name('business.settings');
    Route::put('/business/settings', [BusinessController::class, 'update'])->name('business.settings.update');

    // Onboarding Usaha Baru
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding/business', [OnboardingController::class, 'storeBusiness'])->name('onboarding.business');
    Route::post('/onboarding/branch', [OnboardingController::class, 'storeBranch'])->name('onboarding.branch');
    Route::post('/onboarding/preferences', [OnboardingController::class, 'storePreferences'])->name('onboarding.preferences');
    Route::post('/onboarding/products', [OnboardingController::class, 'storeProduct'])->name('onboarding.products');
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');

    // Pemilihan Cabang
    Route::get('/select-branch', [BranchController::class, 'select'])->name('branches.select');
    Route::post('/select-branch', [BranchController::class, 'switch'])->name('branches.switch');

    // Riwayat Transaksi & Detail
    Route::middleware('can:sales view-all')->group(function () {
        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    });
    Route::middleware('can:sales cancel')->group(function () {
        Route::get('/refunds/search', [RefundController::class, 'searchSale'])->name('refunds.search');
        Route::post('/refunds', [RefundController::class, 'process'])->name('refunds.process');
    });

    // PIN Login
    Route::get('/pin-login', [PinLoginController::class, 'show'])->name('pin.login');
    Route::post('/pin-login', [PinLoginController::class, 'login'])->name('pin.login.submit');

    // Dashboard Overview
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
