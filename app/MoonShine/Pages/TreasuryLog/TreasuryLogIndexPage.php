<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\TreasuryLog;

use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Components\MoonShineComponent;
use MoonShine\UI\Fields\Field;
use Throwable;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Checkbox;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Td;
use MoonShine\UI\Fields\StackFields;

use Illuminate\View\ComponentAttributeBag;


use MoonShine\UI\Components\Heading;
use App\MoonShine\Components\editableDiv;
use MoonShine\UI\Components\Layout\Divider;




class TreasuryLogIndexPage extends IndexPage
{
    /**
     * @return list<MoonShineComponent|Field>
     */
    public function fields(): array
    {
        return [
            ID::make()->badge(),
            Text::make('clan_id'),
            Date::make("Дата", "date")->format('d.m.y H:i')->badge(),
            StackFields::make('name')->fields([
                Text::make("Ник", "name"),
                Text::make("comment")->badge(),
            ]),
            Text::make("Объект", "object"),
            Text::make("Кол-во", "quantity")
                ->badge(fn($quantity) => $this->quantityColor($quantity) ),
            StackFields::make('а?')->fields([
                Checkbox::make("Налог?", "tax_status")->updateOnPreview(),
                Checkbox::make("Таланты?", "for_talents")->updateOnPreview()
            ]),
            StackFields::make('Долги')->fields([
                Checkbox::make("В долг?", "borrowed")->updateOnPreview(),
                Checkbox::make("Возврат?", "repaid_the_debt")->updateOnPreview(),
            ]),
        ];
    }

    /**
     * @return list<MoonShineComponent>
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            Heading::make('Скопируйте содержимое таблицы в казне клана, и вставьте в поле ниже. Система автоматически разберёт строки и запишет их в базу данных. После чего - страница будет перезагружена.'),
            editableDiv::make(2),
            Divider::make(),
            ...parent::topLayer()
        ];
    }

    /**
     * @return list<MoonShineComponent>
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer()
        ];
    }

    /**
     * @return list<MoonShineComponent>
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer()
        ];
    }


    public function quantityColor($quantity): String
    {
        $color = 'green';

        if (substr($quantity, 0, 1) === '-') {
            $color = 'red';
        } else {
            $color = 'green';
        }

        return $color;
    }
}
