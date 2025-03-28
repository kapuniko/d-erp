<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\ColorManager\ColorManager;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Laravel\Components\Layout\{Locales, Notifications, Profile, Search};
use MoonShine\UI\Components\{Breadcrumbs,
    Components,
    Layout\Flash,
    Layout\Div,
    Layout\Body,
    Layout\Burger,
    Layout\Content,
    Layout\Footer,
    Layout\Head,
    Layout\Favicon,
    Layout\Assets,
    Layout\Meta,
    Layout\Header,
    Layout\Html,
    Layout\Layout,
    Layout\Logo,
    Layout\Menu,
    Layout\Sidebar,
    Layout\ThemeSwitcher,
    Layout\TopBar,
    Layout\Wrapper,
    When};

use App\MoonShine\Resources\TreasuryLogResource;
use App\MoonShine\Resources\TaxAmountResource;
use App\MoonShine\Resources\ClanResource;
use App\MoonShine\Resources\ClanMemberResource;

use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use MoonShine\Laravel\Resources\MoonShineUserResource;
use MoonShine\Laravel\Resources\MoonShineUserRoleResource;

final class MoonShineLayout extends AppLayout
{
    protected function assets(): array
    {
        return [
            ...parent::assets(),
        ];
    }

    protected function menu(): array
    {

        $menu = [
            MenuGroup::make('Клан', [
                MenuItem::make(
                    'Лог клановой казны',
                    TreasuryLogResource::class,
                    'table-cells'),
                MenuItem::make(
                    'Игроки клана',
                    ClanMemberResource::class,
                    'users'),
                MenuItem::make(
                    'Размер налогов',
                    TaxAmountResource::class
                ),

            ]),


        ];

        if (request()->user('moonshine')->moonshine_user_role_id == 1) {
            $menu[] = MenuGroup::make(static fn() => __('moonshine::ui.resource.system'), [
                MenuItem::make(
                    static fn() => __('moonshine::ui.resource.admins_title'),
                    MoonShineUserResource::class
                ),
                MenuItem::make(
                    static fn() => __('moonshine::ui.resource.role_title'),
                    MoonShineUserRoleResource::class
                ),
                MenuItem::make(
                    'Cписок кланов',
                    ClanResource::class,
                    'user-group'),
            ]);
        }

        return $menu;

    }

    /**
     * @param ColorManager $colorManager
     */
    protected function colors(ColorManagerContract $colorManager): void
    {
        parent::colors($colorManager);

        // $colorManager->primary('#00000');
    }

    public function build(): Layout
    {
        return parent::build();
    }
}
