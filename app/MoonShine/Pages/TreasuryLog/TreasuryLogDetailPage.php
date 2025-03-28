<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\TreasuryLog;

use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Components\MoonShineComponent;
use MoonShine\UI\Fields\Field;
use Throwable;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

class TreasuryLogDetailPage extends DetailPage
{
    /**
     * @return list<MoonShineComponent|Field>
     */
    public function fields(): array
    {
        return [
            ID::make(),
            Text::make("Date"),
            Text::make("Name"),
            Text::make("Type"),
            Text::make("Object"),
            Text::make("Quantity"),
        ];
    }

    /**
     * @return list<MoonShineComponent>
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
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
}
