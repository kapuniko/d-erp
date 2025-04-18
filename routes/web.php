<?php

use App\Http\Controllers\CalendarController;
use App\Services\CalendarService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaxesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TelegramAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/taxes/{token}', [TaxesController::class, 'show'])->name('taxes.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/auth/telegram', [TelegramAuthController::class, 'redirect']);
Route::get('/auth/telegram/callback', [TelegramAuthController::class, 'callback']);

Route::get('/cabinet', function () {
    return view('cabinet');
})->middleware('auth');

Route::get('/calendar', [CalendarController::class, 'index']);

Route::post('/calendar-events/save', [CalendarController::class, 'saveEvent']);

require __DIR__.'/auth.php';
