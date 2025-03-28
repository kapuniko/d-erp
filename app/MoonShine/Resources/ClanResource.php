<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use Illuminate\Database\Eloquent\Model;
use App\Models\Clan;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Decorations\Block;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\UI\Fields\Field;
use MoonShine\UI\Components\MoonShineComponent;
use App\MoonShine\Resources\MoonShineUserResource;


/**
 * @extends ModelResource<Clan>
 */
class ClanResource extends ModelResource
{
    protected string $model = Clan::class;

    protected string $title = 'Clans';

    public string $column = 'name';

    /**
     * @return list<MoonShineComponent|Field>
     */
    protected function indexFields(): iterable
    {
        return [
                ID::make()->sortable(),
                Text::make('Name')->required(),
                Text::make('token'),
                BelongsTo::make('owner', 'user', resource: MoonShineUserResource::class),
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

    protected function rules($item): array
    {
        return [
            'name' => 'required'
        ];
    }

    /**
     * @param Clan $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
//    public function rules(Model $item): array
//    {
//        return [];
//    }
}
