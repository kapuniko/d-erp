<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Coin;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<Coin>
 */
class CoinResource extends ModelResource
{
    protected string $model = Coin::class;

    public function getTitle(): string
    {
        return 'Coin';
    }

    public function indexFields(): iterable
    {
        // TODO correct labels values
        return [
			ID::make('id'),
			Text::make('name', 'name'),
			Text::make('image', 'image'),
			Text::make('description', 'description'),
			Text::make('type', 'type'),
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
			'name' => ['string', 'required'],
			'image' => ['string', 'required'],
			'description' => ['string', 'nullable'],
			'type' => ['string', 'nullable'],
        ];
    }
}
