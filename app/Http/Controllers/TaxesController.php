<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\Clan; // Импорт модели
use App\Models\TreasuryLog;

use MoonShine\Apexcharts\Components\DonutChartMetric;



class TaxesController extends Controller
{
    public function show($token)
    {
        // Поиск клана по токену
        $clan = Clan::where('token', $token)->first();

        if ($clan) {
            $logs = $this->getLog($clan->id);

            $coins_val = $this->getValuesToChart('coins_current_month', $logs );
            $donutChart_coins = DonutChartMetric::make('Золото')
                ->values($coins_val);

            $dust_val = $this->getValuesToChart('dust_current_month', $logs );
            $donutChart_dust = DonutChartMetric::make('Прах')
                ->values($dust_val);

            $crystals_val = $this->getValuesToChart('crystals_current_month', $logs );
            $donutChart_crystals = DonutChartMetric::make('Истина')
                ->values($crystals_val);

            $pages_val = $this->getValuesToChart('pages_current_month', $logs );
            $donutChart_pages = DonutChartMetric::make('Страницы')
                ->values($pages_val);

            $jetons_val = $this->getValuesToChart('jetons_current_month', $logs );
            $donutChart_jetons = DonutChartMetric::make('Жетоны')
                ->values($jetons_val);

            // Если клан найден, передаем данные во вью
            return view('taxes.show', [
                'clan' => $clan,
                'logs' => $logs,
                'donutChart_coins' => $donutChart_coins,
                'donutChart_dust' => $donutChart_dust,
                'donutChart_crystals' => $donutChart_crystals,
                'donutChart_pages' => $donutChart_pages,
                'donutChart_jetons' => $donutChart_jetons,
            ]);
        }

        // Если клан не найден, возвращаем 404 страницу
        abort(404, 'Clan not found');
    }

    public function getLog($clan_id)
    {
        $currentMonth = now(); // Текущий месяц
        $previousMonth = now()->subMonth(); // Предыдущий месяц
        $twoMonthsAgo = now()->subMonths(2); // Месяц перед предыдущим

        return TreasuryLog::select(
            'name',
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Монеты' THEN quantity ELSE 0 END) as coins_current_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$previousMonth->month} AND EXTRACT(YEAR FROM date) = {$previousMonth->year} AND object = 'Монеты' THEN quantity ELSE 0 END) as coins_previous_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$twoMonthsAgo->month} AND EXTRACT(YEAR FROM date) = {$twoMonthsAgo->year} AND object = 'Монеты' THEN quantity ELSE 0 END) as coins_two_months_ago"),
            DB::raw("SUM(CASE WHEN object = 'Монеты' THEN quantity ELSE 0 END) as coins_total"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year}  AND object = 'Кристаллизованный прах' THEN quantity ELSE 0 END) as dust_current_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$previousMonth->month} AND EXTRACT(YEAR FROM date) = {$previousMonth->year} AND object = 'Кристаллизованный прах' THEN quantity ELSE 0 END) as dust_previous_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$twoMonthsAgo->month} AND EXTRACT(YEAR FROM date) = {$twoMonthsAgo->year} AND object = 'Кристаллизованный прах' THEN quantity ELSE 0 END) as dust_two_months_ago"),
            DB::raw("SUM(CASE WHEN object = 'Кристаллизованный прах' THEN quantity ELSE 0 END) as dust_total"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Кристаллы истины' THEN quantity ELSE 0 END) as crystals_current_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$previousMonth->month} AND EXTRACT(YEAR FROM date) = {$previousMonth->year} AND object = 'Кристаллы истины' THEN quantity ELSE 0 END) as crystals_previous_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$twoMonthsAgo->month} AND EXTRACT(YEAR FROM date) = {$twoMonthsAgo->year} AND object = 'Кристаллы истины' THEN quantity ELSE 0 END) as crystals_two_months_ago"),
            DB::raw("SUM(CASE WHEN object = 'Кристаллы истины' THEN quantity ELSE 0 END) as crystals_total"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Страница из трактата «Единство клана»' THEN quantity ELSE 0 END) as pages_current_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$previousMonth->month} AND EXTRACT(YEAR FROM date) = {$previousMonth->year} AND object = 'Страница из трактата «Единство клана»' THEN quantity ELSE 0 END) as pages_previous_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$twoMonthsAgo->month} AND EXTRACT(YEAR FROM date) = {$twoMonthsAgo->year} AND object = 'Страница из трактата «Единство клана»' THEN quantity ELSE 0 END) as pages_two_months_ago"),
            DB::raw("SUM(CASE WHEN object = 'Страница из трактата «Единство клана»' THEN quantity ELSE 0 END) as pages_total"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Жетон «Времена года»' THEN quantity ELSE 0 END) as jetons_current_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$previousMonth->month} AND EXTRACT(YEAR FROM date) = {$previousMonth->year} AND object = 'Жетон «Времена года»' THEN quantity ELSE 0 END) as jetons_previous_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$twoMonthsAgo->month} AND EXTRACT(YEAR FROM date) = {$twoMonthsAgo->year} AND object = 'Жетон «Времена года»' THEN quantity ELSE 0 END) as jetons_two_months_ago"),
            DB::raw("SUM(CASE WHEN object = 'Жетон «Времена года»' THEN quantity ELSE 0 END) as jetons_total")

        )
            ->where(function ($query) {
                $query->where('for_talents', '!=', true)
                    ->orWhereNull('for_talents'); // Учитываем пустые значения
            })
            ->where(function ($query) {
                $query->where('repaid_the_debt', '!=', true)
                    ->orWhereNull('repaid_the_debt'); // Учитываем пустые значения
            })
            ->where('clan_id', $clan_id)
            ->groupBy('name')
            ->get();
    }

    public function getValuesToChart($val, $log)
    {
        // Преобразуем данные в формат для DonutChartMetric
        $values = $log->pluck($val, 'name')->toArray();

        $values = array_map(function ($value) {
            return round($value);
        }, $values);

        return array_filter($values, function ($value) {
            return $value > 0; // Оставляем только значения больше нуля
        });
    }
}

