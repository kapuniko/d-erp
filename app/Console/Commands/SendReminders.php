<?php

namespace App\Console\Commands;

use App\Models\Reminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отправить напоминания, время которых пришло';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        $reminders = Reminder::where('sent', false)
            ->where('remind_at', '<=', $now)
            ->get();

        foreach ($reminders as $reminder) {
            try {
                Http::post("https://api.telegram.org/bot" . env('TELEGRAM_TOKEN') . "/sendMessage", [
                    'chat_id' => $reminder->chat_id,
                    'text' => $reminder->message,
                ]);

                $reminder->update(['sent' => true]);

                $this->info("Напоминание #{$reminder->id} отправлено");
            } catch (\Exception $e) {
                \Log::error("Ошибка отправки напоминания #{$reminder->id}: " . $e->getMessage());
            }
        }

        return Command::SUCCESS;

    }
}
