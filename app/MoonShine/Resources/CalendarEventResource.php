<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Enums\CalendarEventType;
use App\Models\CalendarEvent;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Checkbox;
use MoonShine\UI\Fields\Color;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Fieldset;
use MoonShine\UI\Fields\Textarea;


/**
 * @extends ModelResource<CalendarEvent>
 */
class CalendarEventResource extends ModelResource
{
    protected string $model = CalendarEvent::class;

    protected string $title = 'События на календаре';

    public function indexFields(): iterable
    {
        // TODO correct labels values
        return [
			ID::make('id'),
			Text::make('emoji', 'emoji'),
            Text::make('name', 'name'),
            Fieldset::make('Начало', [
                Date::make('Дата', 'event_date')->nullable(),
                Text::make('Время', 'event_time')->nullable(),
            ]),
            Fieldset::make('Повтор',[
                Number::make('Интервал в часах', 'interval_hours'),
                Date::make('Дата окончания', 'repeat_until')->nullable(),
            ]),

            Fieldset::make('Мультидэй',[
                Checkbox::make('is_all_day', 'is_all_day'),
                Date::make('Дата окончания', 'event_end_date')->nullable(),
                Enum::make('display_type')->attach(CalendarEventType::class)->nullable(),
            ]),

			//Number::make('amount', 'amount'),

            Textarea::make('description', 'description'),
            Text::make('type', 'type'),
            Color::make('color', 'color'),
            BelongsTo::make('User', 'user', resource: UserResource::class)->nullable(),
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
