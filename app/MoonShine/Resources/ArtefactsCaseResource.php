<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\ArtefactsCase;

use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends ModelResource<ArtefactsCase>
 */
class ArtefactsCaseResource extends ModelResource
{
    protected string $model = ArtefactsCase::class;

	protected array $with = ['artefacts'];

    protected string $column = 'name';

    public function getTitle(): string
    {
        return 'ArtefactsCase';
    }

    public function indexFields(): iterable
    {
        // TODO correct labels values
        return [
			ID::make('id'),
			Text::make('name', 'name'),
            Textarea::make('case_description', 'case_description'),
			Text::make('type', 'type'),
			Text::make('calendar_date', 'calendar_date'),
			Text::make('calendar_time', 'calendar_time'),
			Text::make('sample_order', 'sample_order'),
			Number::make('case_cost', 'case_cost'),
			Number::make('case_profit', 'case_profit'),
            BelongsTo::make('user_id', 'user', resource: UserResource::class)->nullable(),
            BelongsToMany::make('artefacts', 'artefacts', resource: ArtefactResource::class)->nullable()->creatable(),
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
			'name' => ['string', 'nullable'],
			'user_id' => ['int', 'required'],
			'type' => ['string', 'required'],
			'calendar_date' => ['string', 'nullable'],
			'calendar_time' => ['string', 'nullable'],
			'sample_order' => ['string', 'nullable'],
			'case_cost' => ['numeric', 'nullable'],
			'case_profit' => ['numeric', 'nullable'],
        ];
    }
}
