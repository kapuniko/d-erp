<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreasuryLog extends Model
{
    use HasFactory;

    // Указываем, какие поля могут быть массово присваиваемыми
    protected $fillable = [
        "clan_id",
        "date",
        "name",
        "type",
        "object",
        "quantity",
        "comment",
        "tax_status",
        "borrowed",
        "repaid_the_debt",
        "for_talents"
    ];

    // Если имя таблицы отличается от стандартного (например, таблица называется 'treasury_logs', а не 'treasury_log')
    protected $table = 'treasury_logs';

    // Указываем формат даты, если используете тип datetime (это не обязательно, но может быть полезно)
    protected $dates = [
        'date',
        'created_at',
        'updated_at',
    ];

    // Если хотите задать конкретный формат для хранения даты (не обязательно)
    // protected $dateFormat = 'Y-m-d H:i:s';  // Если вам нужно явно указать формат хранения
}
