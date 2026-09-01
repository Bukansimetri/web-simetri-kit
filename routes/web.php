<?php

use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\ArticleController;
use App\Http\Controllers\Public\CareerController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\FaqController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ProductController;
use Illuminate\Support\Facades\Route;

// Halaman publik company profile (spec 002-theme-branding-system,
// contracts/public-routes.md).
Route::get('/', HomeController::class)->name('home');
Route::get('/produk', [ProductController::class, 'index'])->name('produk.index');
Route::get('/produk/{product:slug}', [ProductController::class, 'show'])->name('produk.show');

Route::get('/tentang-kami', AboutController::class)->name('tentang-kami');
Route::get('/kontak', ContactController::class)->name('kontak');
Route::get('/karir', CareerController::class)->name('karir');
Route::get('/artikel', [ArticleController::class, 'index'])->name('artikel.index');
Route::get('/artikel/{article:slug}', [ArticleController::class, 'show'])->name('artikel.show');
Route::get('/faq', FaqController::class)->name('faq');
