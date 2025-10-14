<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FeedbackController;
use App\Http\Middleware\RedirectUnauthorizedUsers;

Route::get('/', function () {
    return view('home');
});

Route::get('/participate', function () {
    return redirect('/');
});

Route::middleware([RedirectUnauthorizedUsers::class])->group(function () {
    Route::get('/participate/application', [ApplicationController::class, 'index'])->name('application.index');
});
Route::get('/participate/nomination', function () {
    return view('nomination');
});
Route::get('/participate/voting', function () {
    return view('voting');
});

Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

Route::get('/login', [LoginController::class, 'show'])->name('login');

Route::post('/participate/application/submit', [ApplicationController::class, 'store'])->name('application.store')->middleware('auth');
Route::get('/redirect-after-login', [ApplicationController::class, 'redirectAfterLogin'])->name('application.redirect-after-login')->middleware('auth');
Route::post('/set-redirect-url', [ApplicationController::class, 'setRedirectUrl'])->name('application.set-redirect-url')->middleware('auth');
