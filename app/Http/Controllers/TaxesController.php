<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\Clan;
use App\Models\TreasuryLog;

use MoonShine\Apexcharts\Components\DonutChartMetric;
use Carbon\Carbon;

class TaxesController extends Controller
{
    public function show($token)
    {
        // Поиск клана по токену
        $clan = Clan::where('token', $token)->first();

        if (!$clan) {
            abort(404, 'Clan not found');
        }

        // Старые данные (для графиков)
        $logs = $this->getLog($clan->id);

        $coins_val = $this->getValuesToChart('coins_current_month', $logs);
        $donutChart_coins = DonutChartMetric::make('Золото')->values($coins_val);

        $dust_val = $this->getValuesToChart('dust_current_month', $logs);
        $donutChart_dust = DonutChartMetric::make('Прах')->values($dust_val);

        $crystals_val = $this->getValuesToChart('crystals_current_month', $logs);
        $donutChart_crystals = DonutChartMetric::make('Истина')->values($crystals_val);

        $pages_val = $this->getValuesToChart('pages_current_month', $logs);
        $donutChart_pages = DonutChartMetric::make('Страницы')->values($pages_val);

        $jetons_val = $this->getValuesToChart('jetons_current_month', $logs);
        $donutChart_jetons = DonutChartMetric::make('Жетоны')->values($jetons_val);

        // Новая таблица (12 месяцев)
        $summary12Months = $this->getLast12MonthsLog($clan->id);

        return view('taxes.show', [
            'clan' => $clan,
            'logs' => $logs,
            'donutChart_coins' => $donutChart_coins,
            'donutChart_dust' => $donutChart_dust,
            'donutChart_crystals' => $donutChart_crystals,
            'donutChart_pages' => $donutChart_pages,
            'donutChart_jetons' => $donutChart_jetons,
            'summary12Months' => $summary12Months['table'],
            'summaryMonths' => $summary12Months['months'],
        ]);
    }

    public function getLog($clan_id)
    {
        $currentMonth = Carbon::now();
        $previousMonth = Carbon::now()->subMonth();
        $twoMonthsAgo = Carbon::now()->subMonths(2);

        return TreasuryLog::select(
            'name',
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Монеты' THEN quantity ELSE 0 END) as coins_current_month"),
            DB::raw("SUM(CASE WHEN object = 'Монеты' THEN quantity ELSE 0 END) as coins_total"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year}  AND object = 'Кристаллизованный прах' THEN quantity ELSE 0 END) as dust_current_month"),
            DB::raw("SUM(CASE WHEN object = 'Кристаллизованный прах' THEN quantity ELSE 0 END) as dust_total"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Кристаллы истины' THEN quantity ELSE 0 END) as crystals_current_month"),
            DB::raw("SUM(CASE WHEN object = 'Кристаллы истины' THEN quantity ELSE 0 END) as crystals_total"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Страница из трактата «Единство клана»' THEN quantity ELSE 0 END) as pages_current_month"),
            DB::raw("SUM(CASE WHEN object = 'Страница из трактата «Единство клана»' THEN quantity ELSE 0 END) as pages_total"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Жетон «Времена года»' THEN quantity ELSE 0 END) as jetons_current_month"),
            DB::raw("SUM(CASE WHEN object = 'Жетон «Времена года»' THEN quantity ELSE 0 END) as jetons_total")
        )
            ->where(function ($query) {
                $query->where('for_talents', '!=', true)
                    ->orWhereNull('for_talents');
            })
            ->where(function ($query) {
                $query->where('repaid_the_debt', '!=', true)
                    ->orWhereNull('repaid_the_debt');
            })
            ->where('clan_id', $clan_id)
            ->groupBy('name')
            ->get();
    }

    public function getValuesToChart($val, $log)
    {
        $values = $log->pluck($val, 'name')->toArray();
        $values = array_map(fn($value) => round($value), $values);

        return array_filter($values, fn($value) => $value > 0);
    }

    private function getLast12MonthsLog($clanId)
    {
        $start = now()->subMonths(11)->startOfMonth();
        $end = now()->endOfMonth();

        $logs = TreasuryLog::selectRaw("
            name,
            object,
            TO_CHAR(date, 'YYYY-MM') as month,
            SUM(quantity) as total
        ")
            ->where('clan_id', $clanId)
            ->whereBetween('date', [$start, $end])
            ->where(function ($query) {
                $query->where('for_talents', '!=', true)
                    ->orWhereNull('for_talents');
            })
            ->where(function ($query) {
                $query->where('repaid_the_debt', '!=', true)
                    ->orWhereNull('repaid_the_debt');
            })
            ->groupBy('name', 'object', DB::raw("TO_CHAR(date, 'YYYY-MM')"))
            ->get();

        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->format('Y-m'));
        }

        $table = [];

        foreach ($logs as $log) {
            $name = $log->name;
            $month = $log->month;

            if (!isset($table[$name])) {
                $table[$name] = [];
            }

            if (!isset($table[$name][$month])) {
                $table[$name][$month] = [
                    'gold' => 0,
                    'dust' => 0,
                    'truth' => 0,
                    'jetons' => 0,
                ];
            }

            switch ($log->object) {
                case 'Монеты':
                    $table[$name][$month]['gold'] += $log->total;
                    break;
                case 'Кристаллизованный прах':
                    $table[$name][$month]['dust'] += $log->total;
                    break;
                case 'Кристаллы истины':
                    $table[$name][$month]['truth'] += $log->total;
                    break;
                case 'Жетон «Времена года»':
                    $table[$name][$month]['jetons'] += $log->total;
                    break;
            }
        }

        return [
            'table' => $table,
            'months' => $months,
        ];
    }
}
