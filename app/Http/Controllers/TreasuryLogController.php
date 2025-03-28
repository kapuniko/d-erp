<?php

namespace App\Http\Controllers;

use App\Models\TreasuryLog;
use Illuminate\Http\Request;

class TreasuryLogController extends Controller
{
    public function store(Request $request)
    {
        // Получаем массив данных из JSON
        $data = $request->all();

        // Проверяем, что данные переданы корректно
        if (!is_array($data)) {
            return response()->json(['error' => 'Invalid data format'], 400);
        }

        // Перебираем массив и сохраняем записи
        foreach ($data as $item) {
            TreasuryLog::create([
                'clan_id' => $item['clan_id'],
                'date' => \Carbon\Carbon::createFromFormat('d.m.Y H:i', $item['Date']), // Преобразование даты
                'name' => $item['Name'],
                'type' => $item['Type'],
                'object' => $item['Object'],
                'quantity' => $item['Quantity'],
            ]);
        }

        return response()->json(['message' => 'Data saved successfully'], 200);
    }
}

