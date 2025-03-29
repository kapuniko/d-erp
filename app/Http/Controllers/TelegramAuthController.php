<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\Request;

class TelegramAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('telegram')->redirect();
    }

    public function callback()
    {
        $telegramUser = Socialite::driver('telegram')->user();

        $user = User::updateOrCreate([
            'telegram_id' => $telegramUser->getId(),
        ], [
            'name' => $telegramUser->getName() ?? 'User',
            'email' => $telegramUser->getId() . '@telegram.com',
            'password' => bcrypt(str()->random(10)),
        ]);

        Auth::login($user);

        return redirect('/cabinet');
    }
}
