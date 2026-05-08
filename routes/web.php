<?php

use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Accounts\Index as AccountsIndex;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('accounts.index'));
    Route::livewire('/accounts', AccountsIndex::class)->name('accounts.index');
});
