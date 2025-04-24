<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Artefact;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends ModelResource<Artefact>
 */
class ArtefactResource extends ModelResource
{
    protected string $model = Artefact::class;

    protected string $column = 'name';

    public function getTitle(): string
    {
        return 'Artefact';
    }

    public function indexFields(): iterable
    {
        // TODO correct labels values
        return [
			ID::make('id'),
			Text::make('game_id', 'game_id')->updateOnPreview(),
			Text::make('name', 'name')->updateOnPreview(),
			Text::make('type', 'type')->updateOnPreview(),
			Text::make('image', 'image')->updateOnPreview(),
			//Textarea::make('description', 'description'),
			Number::make('duration_sec', 'duration_sec')->updateOnPreview(),
			Number::make('level', 'level')->updateOnPreview(),
			Text::make('group', 'group')->updateOnPreview(),
			Number::make('price', 'price')->updateOnPreview(),
			BelongsTo::make('user_id', 'user')->nullable(),
            //BelongsToMany::make('artefactsCases', 'artefactsCases', resource: ArtefactsCaseResource::class)->nullable()->creatable(),
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
			'game_id' => ['string', 'nullable'],
			'name' => ['string', 'required'],
			'type' => ['string', 'required'],
			'image' => ['string', 'nullable'],
			'description' => ['string', 'nullable'],
			'duration_sec' => ['int', 'nullable'],
			'level' => ['int', 'nullable'],
			'group' => ['string', 'nullable'],
			'price' => ['numeric', 'nullable'],
			'user_id' => ['int', 'nullable'],
        ];
    }
}
