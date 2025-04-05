<?php
namespace App\Models;

use App\Enums\CalendarEventType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $casts = [
        'display_type' => CalendarEventType::class,
        'event_date' => 'date',
        'event_end_date' => 'date',
        'repeat_until' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'event_date',
        'event_end_date',
        'is_all_day',
        'display_type',
        'event_time',
        'repeat_until',
        'interval_hours',
        'amount',
        'emoji',
        'name',
        'description',
        'type',
        'color',
        'user_id',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function getEventTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }

    public function getEventDateAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }

    public function getEmojiAttribute($value)
    {
        return $value; // Просто возвращаем эмодзи
    }

    // Фильтрация повторяющихся событий
    public static function getRepeatEvents()
    {
        return self::where('display_type', 'repeat')->get();
    }

    // Фильтрация многодневных событий
    public static function getRangeEvents()
    {
        return self::where('display_type', 'range')->get();
    }

    public function getRepeatedInstances(): array
    {
        $instances = [];
        $formattedDateTime = $this->event_date . ' ' . substr($this->event_time, 0, 5); // Отрезаем секунды

        $start = null;
        try {
            $start = Carbon::createFromFormat('Y-m-d H:i', $formattedDateTime);
            $instances[] = $start;
        } catch (\Exception $e) {
            // Обработка ошибки парсинга
        }

        if ($start && $this->repeat_until && $this->interval_hours) {
            $end = Carbon::parse($this->repeat_until)->endOfDay();
            $next = $start->copy()->addHours($this->interval_hours);

            while ($next <= $end) {
                $instances[] = $next->copy();
                $next->addHours($this->interval_hours);
            }
        }

        return $instances;
    }

    public function isMultiDay(): bool
    {
        // Проверяем, что дата окончания существует и больше даты начала
        return $this->event_end_date && $this->event_end_date > $this->event_date;
    }

    public function getMultiDayInstances(): array
    {
        // Проверяем, если событие не многодневное
        if (!$this->isMultiDay()) {
            return [];
        }

        $instances = [];
        $current = Carbon::parse($this->event_date);
        $end = Carbon::parse($this->event_end_date);

        // Добавляем каждый день в интервале
        while ($current <= $end) {
            $instances[] = $current->copy();
            $current->addDay();
        }

        return $instances;
    }
}
