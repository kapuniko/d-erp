<?php

use App\Http\Controllers\CalendarController;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
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

Route::get('/cabinet', function () {
    return view('cabinet');
})->middleware('auth');

Route::get('/calendar', [CalendarController::class, 'index']);

Route::post('/calendar-events/save', [CalendarController::class, 'saveEvent']);

Route::get('/auth/telegram', function () {
    return Socialite::driver('telegram')->redirect();
})->name('auth.telegram');

Route::get('/auth/telegram/callback', function () {
    $telegramUser = Socialite::driver('telegram')->user();

    // Ищем юзера по telegram_id
    $user = User::where('telegram_id', $telegramUser->getId())->first();

    // Если нет - регистрируем нового
    if (!$user) {
        $user = User::create([
            'name' => $telegramUser->getName() ?? $telegramUser->getNickname(),
            'email' => $telegramUser->getId().'@telegram.fake', // Телега не отдает email, фейковый
            'telegram_id' => $telegramUser->getId(),
            'password' => bcrypt(Str::random(24)), // случайный пароль
        ]);
    }

    Auth::login($user);

    return redirect('/dashboard'); // или куда хочешь
});

require __DIR__.'/auth.php';
