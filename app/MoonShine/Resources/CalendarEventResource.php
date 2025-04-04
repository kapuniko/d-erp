<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\CalendarEvent;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Number;

/**
 * @extends ModelResource<CalendarEvent>
 */
class CalendarEventResource extends ModelResource
{
    protected string $model = CalendarEvent::class;

    public function indexFields(): iterable
    {
        // TODO correct labels values
        return [
			ID::make('id'),
			Text::make('emoji', 'emoji'),
			Date::make('event_date', 'event_date'),
			Text::make('event_time', 'event_time'),
			Date::make('repeat_until', 'repeat_until'),
			Number::make('interval_hours', 'interval_hours'),
			Number::make('amount', 'amount'),
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
			'emoji' => ['string', 'required'],
			'event_date' => ['string', 'required'],
			'event_time' => ['string', 'required'],
			'repeat_until' => ['string', 'nullable'],
			'interval_hours' => ['int', 'nullable'],
			'amount' => ['int', 'nullable'],
        ];
    }
}
