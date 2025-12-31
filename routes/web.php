<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ResultController;
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
Route::middleware('auth')->group(function () {
    Route::get('/participate/nominate', \App\Livewire\NominationVoting::class)->name('nomination.voting');
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

// Result routes

Route::get('/results', [ResultController::class, 'index'])->name('results.latest');
// Route::get('/results/acknowledgements', [ResultController::class, 'index'])->name('results.latest.acknowledgements');
// Route::get('/results/about', [ResultController::class, 'index'])->name('results.latest.about');
// Route::get('/results/archive', [ResultController::class, 'archive'])->name('results.archive');
Route::get('/results/{year}', [ResultController::class, 'result'])->name('results.year')->whereNumber('year');
// Route::get('/results/{year}/acknowledgements', [ResultController::class, 'acknowledgements'])->name('results.year.acknowledgements')->whereNumber('year');
// Route::get('/results/{year}/about', [ResultController::class, 'about'])->name('results.year.about')->whereNumber('year');
