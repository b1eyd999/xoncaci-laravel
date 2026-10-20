<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/xoncalar', [SiteController::class, 'catalog'])->name('catalog');
Route::get('/xoncalar/{occasion:slug}', [SiteController::class, 'catalog'])->name('catalog.occasion');
Route::get('/xonca/{xonca:slug}', [SiteController::class, 'show'])->name('xonca');
// Not linked anywhere: try «Evinizdə baxın» before any xonça has a scan.
Route::get('/ar-sinaq', fn () => \App\Models\Setting::get('ar_enabled') === '1' ? view('site.ar-demo') : abort(404))->name('ar.demo');
Route::get('/movcudluq', [SiteController::class, 'availability'])->name('availability');

Route::get('/sebet', [CartController::class, 'page'])->name('cart');
Route::post('/sebet/hesabla', [CartController::class, 'quote'])->name('cart.quote')->middleware('throttle:60,1');
Route::post('/sifaris', [CartController::class, 'store'])->name('booking.store')->middleware('throttle:10,1');
Route::get('/sifaris/{booking:code}', [CartController::class, 'done'])->name('booking.done');

// A sheet for the courier: who, where, when, what goes and what comes back.
Route::get('/admin/bookings/{booking:code}/print', fn (App\Models\Booking $booking) => view('admin.print', ['booking' => $booking->load('items', 'occasion')]))
    ->middleware('auth')->name('admin.booking.print');

// The 360° view: frames cut from the owner's turntable video in the admin.
Route::middleware('auth')->group(function () {
    Route::post('/admin/xonca-spin/{xonca:id}', [App\Http\Controllers\SpinController::class, 'store'])->name('admin.spin.store');
    Route::delete('/admin/xonca-spin/{xonca:id}', [App\Http\Controllers\SpinController::class, 'destroy'])->name('admin.spin.destroy');
});
