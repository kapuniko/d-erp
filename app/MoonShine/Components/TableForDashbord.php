<?php

declare(strict_types=1);

namespace App\MoonShine\Components;

use Closure;
use Illuminate\Contracts\View\View;
use MoonShine\Components\MoonShineComponent;
use App\Models\TreasuryLog;
use Illuminate\Support\Facades\DB;


/**
 * @method static static make()
 */
final class TableForDashbord extends MoonShineComponent
{
    protected string $view = 'admin.components.table-for-dashbord';

    public function __construct()
    {
        //
    }

    /*
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        return [
            'logs' => $this->getLog(),
        ];
    }

    public function getLog()
    {
        $startDate = '2024-01-01'; // Начало января 2024 года
        $endDate = '2024-08-31'; // Конец августа 2024 года

        return TreasuryLog::select(
            'name',
            DB::raw("SUM(CASE WHEN object = 'Монеты' AND date >= '$startDate' AND date <= '$endDate' THEN quantity ELSE 0 END) as coins_total"),
            DB::raw("SUM(CASE WHEN object = 'Кристаллизованный прах' AND date >= '$startDate' AND date <= '$endDate' THEN quantity ELSE 0 END) as dust_total"),
            DB::raw("SUM(CASE WHEN object = 'Кристаллы истины' AND date >= '$startDate' AND date <= '$endDate' THEN quantity ELSE 0 END) as crystals_total"),
            DB::raw("SUM(CASE WHEN object = 'Страница из трактата «Единство клана»' AND date >= '$startDate' AND date <= '$endDate' THEN quantity ELSE 0 END) as pages_total"),
            DB::raw("SUM(CASE WHEN object = 'Жетон «Времена года»' AND date >= '$startDate' AND date <= '$endDate' THEN quantity ELSE 0 END) as jetons_total")
        )
            ->where(function ($query) {
                $query->where('for_talents', '!=', true)
                    ->orWhereNull('for_talents'); // Учитываем пустые значения
            })
            ->groupBy('name')
            ->get();
    }
}
