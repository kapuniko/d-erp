<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaxesController;
use App\Http\Controllers\ProfileController;

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
    Route::get('/cabinet', function () {
        return view('cabinet');
    })->name('cabinet'); // Добавим имя для удобства
});

Route::get('/calendar', [CalendarController::class, 'index']);

Route::post('/calendar-events/save', [CalendarController::class, 'saveEvent']);

// --- Единый роут для редиректа на Telegram (инициируется и для входа, и для привязки) ---
// Этот роут используется кнопками "Войти через Telegram" и "Привязать Telegram"
Route::get('/auth/telegram', [SocialAuthController::class, 'redirectToTelegram'])->name('auth.telegram');


// --- Единый роут для обработки ВСЕХ ответов (callback) от Telegram ---
// Логика внутри handleTelegramCallback определит, это был вход или привязка
Route::get('/auth/telegram/callback', [SocialAuthController::class, 'handleTelegramCallback']);


// --- Роут для инициирования привязки аккаунта из кабинета (ТРЕБУЕТ АУТЕНТИФИКАЦИИ) ---
// Этот роут просто перенаправляет на /auth/telegram, инициируя Socialite flow
Route::middleware('auth')->group(function () {
    Route::get('/profile/link/telegram', [SocialAuthController::class, 'redirectToTelegram'])->name('profile.link.telegram');
    // Обратите внимание: здесь НЕТ отдельного callback роута.
    // Callback пойдет на единый /auth/telegram/callback
});
require __DIR__.'/auth.php';
