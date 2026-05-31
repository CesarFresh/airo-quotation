<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/login'));

Route::get('/login', fn () => view('auth.login'))->name('login');

Route::get('/quotation', fn () => view('quotation.create'))->name('quotation.create');