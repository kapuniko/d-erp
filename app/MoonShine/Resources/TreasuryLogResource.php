<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use Illuminate\Database\Eloquent\Model;
use App\Models\TreasuryLog;
use App\MoonShine\Pages\TreasuryLog\TreasuryLogIndexPage;
use App\MoonShine\Pages\TreasuryLog\TreasuryLogFormPage;
use App\MoonShine\Pages\TreasuryLog\TreasuryLogDetailPage;

use MoonShine\UI\Fields\Text;


use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Pages\Page;

use MoonShine\AssetManager\Css;


/**
 * @extends ModelResource<TreasuryLog>
 */
class TreasuryLogResource extends ModelResource
{

    protected string $model = TreasuryLog::class;

    protected string $title = 'Лог клановой казны';

    /**
     * @return list<Page>
     */
    protected function pages(): array
    {
        return [
            TreasuryLogIndexPage::class,
            TreasuryLogFormPage::class,
            TreasuryLogDetailPage::class,
        ];
    }

    /**
     * @param TreasuryLog $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
//    public function rules(Model $item): array
//    {
//        return [];
//    }

       protected function onLoad(): void
    {
        $this->getAssetManager()
            ->append(Css::make('/css/kapuStyles.css'));
    }



    public function filters(): array
    {
        return [
            Text::make('Ник игрока', 'name'),
            Text::make('Обьект', 'object'),

        ];
    }

}
