<?php

use App\Livewire\Pages\LoginPage;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', LoginPage::class)->name('login');
});
