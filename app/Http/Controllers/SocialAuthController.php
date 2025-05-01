<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class SocialAuthController extends Controller
{
    /**
     * Перенаправление на страницу аутентификации Telegram.
     * Используется как для входа, так и для привязки.
     */
    public function redirectToTelegram()
    {
        // Socialite использует redirect URL из config/services.php
        return Socialite::driver('telegram')->redirect();
    }

    /**
     * Обработка всех ответов (callback) от Telegram.
     * Определяет, это был вход или привязка, и выполняет соответствующую логику.
     */
    public function handleTelegramCallback()
    {
        Log::info('handleTelegramCallback started. Authenticated user ID: ' . (Auth::id() ?? 'none'));

        try {
            // Получаем данные пользователя от Telegram
            $telegramUser = Socialite::driver('telegram')->user();
            Log::info('Telegram User Data fetched. ID: ' . $telegramUser->getId() . ', Username: ' . $telegramUser->getNickname());

        } catch (\Exception $e) {
            Log::error('Telegram Callback Error: ' . $e->getMessage());
            // В случае ошибки перенаправляем либо на логин, либо в кабинет, в зависимости от того, были ли мы залогинены
            $redirectRoute = Auth::check() ? 'profile.edit' : 'login';
            return redirect()->route($redirectRoute)->with('error', 'Ошибка при аутентификации через Telegram. Пожалуйста, попробуйте еще раз.');
        }

        $telegramId = $telegramUser->getId();
        Log::info('Processing callback for Telegram ID: ' . $telegramId);

        // --- Определяем контекст: Вход или Привязка? ---
        if (Auth::check()) {
            // --- СЦЕНАРИЙ: Привязка аккаунта (пользователь был аутентифицирован) ---
            $currentUser = Auth::user();
            Log::info('Context: Linking account. Current user ID: ' . $currentUser->id);

            // Проверяем, не привязан ли этот Telegram аккаунт уже к другому пользователю (кроме текущего)
            $existingUserWithTelegram = User::where('telegram_id', $telegramId)
                ->where('id', '!=', $currentUser->id) // Исключаем текущего пользователя
                ->first();

            if ($existingUserWithTelegram) {
                Log::warning('Linking failed: Telegram ID ' . $telegramId . ' is already linked to another user (ID: ' . $existingUserWithTelegram->id . ').');
                // Этот Telegram аккаунт уже используется другим пользователем
                return redirect()->route('profile.edit')->with('error', 'Этот Telegram аккаунт уже привязан к другому пользователю.');
            }

            // Проверяем, не привязан ли этот же Telegram ID уже к текущему пользователю
            if ($currentUser->telegram_id === $telegramId) {
                Log::info('Linking: Current user already has this Telegram ID linked. No action needed.');
                return redirect()->route('profile.edit')->with('info', 'Ваш аккаунт Telegram уже привязан.');
            }

            // Привязываем Telegram ID и другую информацию к текущему аутентифицированному пользователю
            Log::info('Linking: Attaching Telegram ID ' . $telegramId . ' to user ' . $currentUser->id);
            $currentUser->telegram_id = $telegramId;
            $currentUser->telegram_username = $telegramUser->getNickname();
            $currentUser->telegram_photo_url = $telegramUser->getAvatar();
            $currentUser->save();

            Log::info('Linking: Telegram account ' . $telegramId . ' successfully linked to user ' . $currentUser->id);
            return redirect()->route('profile.edit')->with('success', 'Аккаунт Telegram успешно привязан!');

        } else {
            // --- СЦЕНАРИЙ: Вход в систему (пользователь НЕ был аутентифицирован) ---
            Log::info('Context: Logging in.');

            // Ищем пользователя по telegram_id
            $user = User::where('telegram_id', $telegramId)->first();

            if ($user) {
                // Пользователь найден - выполняем вход
                Log::info('Login successful: User found with Telegram ID ' . $telegramId . '. User ID: ' . $user->id);
                Auth::login($user);
                // Перенаправляем на предполагаемую страницу после входа или на дашборд
                return redirect()->intended('/dashboard');
            } else {
                // Пользователь НЕ найден - НЕ РЕГИСТРИРУЕМ!
                // Перенаправляем обратно на страницу входа с сообщением.
                Log::warning('Login failed: No user found with Telegram ID ' . $telegramId . '.');
                return redirect('/login')->with('error', 'Ваш Telegram аккаунт не привязан к существующему аккаунту. Пожалуйста, зарегистрируйтесь обычным способом, а затем привяжите Telegram в личном кабинете.');
            }
        }
    }
}
