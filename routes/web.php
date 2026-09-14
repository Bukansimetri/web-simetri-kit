<?php

use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\ArticleController;
use App\Http\Controllers\Public\CalculatorController;
use App\Http\Controllers\Public\CareerController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\CustomPageController;
use App\Http\Controllers\Public\FaqController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PortfolioController;
use App\Http\Controllers\Public\ProductController;
use App\Http\Controllers\Public\SitemapController;
use Illuminate\Support\Facades\Route;

// Sitemap & robots.txt (spec 015-sitemap-robots) — robots.txt dinamis
// menggantikan public/robots.txt statis (lihat research.md §2).
Route::get('/sitemap.xml', [SitemapController::class, 'xml'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// Halaman publik company profile (spec 002-theme-branding-system,
// contracts/public-routes.md).
Route::get('/', HomeController::class)->name('home');
Route::get('/produk', [ProductController::class, 'index'])->name('produk.index');
Route::get('/produk/{product:slug}', [ProductController::class, 'show'])->name('produk.show');

Route::get('/tentang-kami', AboutController::class)->name('tentang-kami');
Route::get('/kontak', [ContactController::class, 'show'])->name('kontak');
Route::post('/kontak', [ContactController::class, 'store'])->name('kontak.store')->middleware('throttle:5,1');
Route::post('/kalkulator/lead', [CalculatorController::class, 'storeLead'])->name('kalkulator.lead')->middleware('throttle:5,1');
Route::get('/karir', CareerController::class)->name('karir');
Route::get('/artikel', [ArticleController::class, 'index'])->name('artikel.index');
Route::get('/artikel/{article:slug}', [ArticleController::class, 'show'])->name('artikel.show');
Route::get('/faq', FaqController::class)->name('faq');

// Portfolio (spec 010-portfolio-showcase-module) — listing + filter kategori & detail.
Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
Route::get('/portfolio/{portfolioProject:slug}', [PortfolioController::class, 'show'])->name('portfolio.show');

// Custom Page (spec 007-custom-page) — satu route dinamis, prefix /halaman/
// supaya tidak pernah bentrok dengan route statis mana pun (FR-014).
Route::get('/halaman/{customPage:slug}', CustomPageController::class)->name('halaman.show');
