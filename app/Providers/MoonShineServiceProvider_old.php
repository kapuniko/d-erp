<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\ClanMember;

use MoonShine\Providers\MoonShineApplicationServiceProvider;
use MoonShine\MoonShine;
use MoonShine\Menu\MenuGroup;
use MoonShine\Menu\MenuItem;
use MoonShine\Resources\MoonShineUserResource;
use MoonShine\Resources\MoonShineUserRoleResource;
use App\MoonShine\Resources\TreasuryLogResource;
use App\MoonShine\Resources\TaxAmountResource;
use App\MoonShine\Resources\ClanResource;
use App\MoonShine\Resources\ClanMemberResource;
use MoonShine\Contracts\Resources\ResourceContract;
use MoonShine\Menu\MenuElement;
use MoonShine\Pages\Page;
use Closure;


class MoonShineServiceProvider extends MoonShineApplicationServiceProvider
{
    /**
     * @return list<ResourceContract>
     */
    protected function resources(): array
    {
        return [];
    }

    /**
     * @return list<Page>
     */
    protected function pages(): array
    {
        return [];
    }

    /**
     * @return Closure|list<MenuElement>
     */
    protected function menu(): array
    {
       return [
            MenuGroup::make('Клан', [
                MenuItem::make(
                    'Лог клановой казны',
                    new TreasuryLogResource()
                )->icon('heroicons.table-cells'),
                MenuItem::make(
                    'Игроки клана',
                    new ClanMemberResource()
                )->icon('heroicons.users'),
                MenuItem::make(
                    'Размер налогов',
                    new TaxAmountResource()
                ),
            ]),


            MenuGroup::make(static fn() => __('moonshine::ui.resource.system'), [
                MenuItem::make(
                    static fn() => __('moonshine::ui.resource.admins_title'),
                    new MoonShineUserResource()
                ),
                MenuItem::make(
                    static fn() => __('moonshine::ui.resource.role_title'),
                    new MoonShineUserRoleResource()
                ),
                MenuItem::make(
                    'Cписок кланов',
                    new ClanResource()
                )->icon('heroicons.user-group'),
            ]),

            MenuItem::make('Documentation', 'https://moonshine-laravel.com/docs')
                ->badge(fn() => 'Check')
                ->blank(),
        ];
    }

    /**
     * @return Closure|array{css: string, colors: array, darkColors: array}
     */
    protected function theme(): array
    {
        return [];
    }

}
