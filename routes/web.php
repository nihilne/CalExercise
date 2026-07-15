<?php

use App\Http\Controllers\CalendarController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CalendarController::class, 'index']);
Route::post('/log', [CalendarController::class, 'store'])->name('log.store');
