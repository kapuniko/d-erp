<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use Illuminate\Database\Eloquent\Model;
use App\Models\TaxAmount;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Decorations\Block;
use MoonShine\UI\Fields\Field;
use MoonShine\Components\MoonShineComponent;

use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<TaxAmount>
 */
class TaxAmountResource extends ModelResource
{
    protected string $model = TaxAmount::class;

    protected string $title = 'Размер налогов';

    /**
     * @return list<MoonShineComponent|Field>
     */
    protected function indexFields(): iterable
    {
        return [
                Text::make("Уровень", "pers_level"),
                Text::make("Золото (мес)", "gold_amount_month"),
                Text::make("Истина (мес)", "crystals_amount_month"),
                Text::make("Страницы (мес)", "pages_amount_month"),
                Text::make("Золото (год)", "gold_amount_year"),
                Text::make("Истина (год)", "crystals_amount_year"),
                Text::make("Страницы (год)", "pages_amount_year"),
        ];
    }

    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }

    protected function formFields(): iterable
    {
        return $this->indexFields();
    }

    /**
     * @param TaxAmount $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules($item): array
    {
        return [];
    }
}
