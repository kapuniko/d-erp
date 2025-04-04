<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use App\MoonShine\Resources\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRoleResource;
use App\MoonShine\Resources\ClanMemberResource;
use App\MoonShine\Resources\ClanResource;
use App\MoonShine\Resources\TaxAmountResource;
use App\MoonShine\Resources\TreasuryLogResource;
use App\MoonShine\Resources\CalendarEventResource;

class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param  MoonShine  $core
     * @param  MoonShineConfigurator  $config
     *
     */
    public function boot(CoreContract $core, ConfiguratorContract $config): void
    {
        // $config->authEnable();

        $core
            ->resources([
                MoonShineUserResource::class,
                MoonShineUserRoleResource::class,
                TreasuryLogResource::class,
                ClanMemberResource::class,
                ClanResource::class,
                TaxAmountResource::class,
                CalendarEventResource::class,
            ])
            ->pages([
                ...$config->getPages(),
            ])
        ;
    }
}
