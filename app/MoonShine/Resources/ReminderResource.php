<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Reminder;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Switcher;

/**
 * @extends ModelResource<Reminder>
 */
class ReminderResource extends ModelResource
{
    protected string $model = Reminder::class;

    public function getTitle(): string
    {
        return 'Reminder';
    }

    public function indexFields(): iterable
    {
        // TODO correct labels values
        return [
			ID::make('id'),
			Number::make('user_id', 'user_id'),
			Text::make('chat_id', 'chat_id'),
			Text::make('message', 'message'),
			Text::make('remind_at', 'remind_at'),
			Switcher::make('sent', 'sent'),
			Number::make('calendar_event_id', 'calendar_event_id'),
            Text::make('event_key', 'event_key'),
        ];
    }

    public function formFields(): iterable
    {
        return [
            Box::make([
                ...$this->indexFields()
            ])
        ];
    }

    public function detailFields(): iterable
    {
        return [
            ...$this->indexFields()
        ];
    }

    public function filters(): iterable
    {
        return [
        ];
    }

    public function rules(mixed $item): array
    {
        // TODO change it to your own rules
        return [
			'user_id' => ['int', 'required'],
			'chat_id' => ['string', 'required'],
			'message' => ['string', 'required'],
			'remind_at' => ['string', 'required'],
			'sent' => ['boolean', 'required'],
			'calendar_event_id' => ['int', 'nullable'],
        ];
    }
}
