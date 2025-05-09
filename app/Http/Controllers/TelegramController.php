<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramController extends Controller
{
    public function sendTestMessage(Request $request)
    {
        $chatId = '195145209'; // или подставь динамически
        $message = 'Привет! Это тест через fetch.';

        $response = Http::post("https://api.telegram.org/bot" . env('TELEGRAM_TOKEN') . "/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
        ]);

        if ($response->ok()) {
            return response()->json(['success' => true, 'message' => 'Успешно отправлено!']);
        } else {
            return response()->json(['success' => false, 'message' => 'Ошибка: ' . $response->body()], 500);
        }
    }
}
