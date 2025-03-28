<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use Illuminate\Database\Eloquent\Model;
use App\Models\ClanMember;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Decorations\Block;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Field;
use MoonShine\UI\Components\MoonShineComponent;

use MoonShine\Laravel\Enums\Action;
use MoonShine\Support\ListOf;


use MoonShine\UI\Fields\Text;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Date;

use App\MoonShine\Resources\MoonShineUserResource;




/**
 * @extends ModelResource<ClanMember>
 */
class ClanMemberResource extends ModelResource
{
    protected string $model = ClanMember::class;

    protected string $title = 'Список игроков клана';

    /**
     * @return list<MoonShineComponent|Field>
     */
    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(Action::VIEW);
    }

    protected function indexFields(): iterable
    {
        return [
                ID::make()->sortable(),
                Text::make('name')->required(),
                Number::make('level')->required(),
                Text::make('real_name'),
                Date::make('birthday')->format("d.m.Y"),
                Date::make('date_of_joining')->format("d.m.Y"),
                BelongsTo::make('clan'),
                BelongsTo::make('moonshine_user', 'moonshine_user', resource: MoonShineUserResource::class)->nullable(),

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
            'name' => 'required',
            'level' => 'required'
        ];
    }

    /**
     * @param ClanMember $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
//    public function rules(Model $item): array
//    {
//        return [];
//    }
}
