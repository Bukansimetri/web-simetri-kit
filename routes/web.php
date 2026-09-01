<?php

use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ProductController;
use Illuminate\Support\Facades\Route;

// Halaman publik company profile (spec 002-theme-branding-system,
// contracts/public-routes.md). US1 (Home, Produk) — US2 halaman pendukung
// menyusul.
Route::get('/', HomeController::class)->name('home');
Route::get('/produk', [ProductController::class, 'index'])->name('produk.index');
Route::get('/produk/{product:slug}', [ProductController::class, 'show'])->name('produk.show');
