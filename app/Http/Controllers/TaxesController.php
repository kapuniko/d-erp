<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\Clan; // Импорт модели
use App\Models\TreasuryLog;

use MoonShine\Apexcharts\Components\DonutChartMetric;
use Carbon\Carbon;



class TaxesController extends Controller
{
    public function show($token)
    {
        $clan = Clan::where('token', $token)->firstOrFail();

        // 🔹 СПЕЦИАЛЬНЫЙ ИНТЕРВАЛ
        $special_date = Carbon::createFromFormat('d.m.Y', '01.11.2025')->startOfDay();
        $special_next_date = Carbon::createFromFormat('d.m.Y', '12.01.2026')->endOfDay();

        $logs = $this->getLog($clan->id);
        $yearlyLog = $this->getYearlyLog($clan->id);

        // Графики
        $donutChart_coins = DonutChartMetric::make('Золото')
            ->values($this->getValuesToChart('coins_current_month', $logs));

        $donutChart_dust = DonutChartMetric::make('Прах')
            ->values($this->getValuesToChart('dust_current_month', $logs));

        $donutChart_crystals = DonutChartMetric::make('Истина')
            ->values($this->getValuesToChart('crystals_current_month', $logs));

        $donutChart_pages = DonutChartMetric::make('Страницы')
            ->values($this->getValuesToChart('pages_current_month', $logs));

        $donutChart_jetons = DonutChartMetric::make('Жетоны')
            ->values($this->getValuesToChart('jetons_current_month', $logs));

        // 🔹 СУММЫ ЗА СПЕЦ-ИНТЕРВАЛ
        $specialTotals = TreasuryLog::select(
            'name',
            DB::raw("
                SUM(CASE WHEN object = 'Монеты' THEN quantity ELSE 0 END) as gold,
                SUM(CASE WHEN object = 'Кристаллизованный прах' THEN quantity ELSE 0 END) as dust,
                SUM(CASE WHEN object = 'Кристаллы истины' THEN quantity ELSE 0 END) as truth,
                SUM(CASE WHEN object = 'Страница из трактата «Единство клана»' THEN quantity ELSE 0 END) as pages,
                SUM(CASE WHEN object = 'Жетон «Времена года»' THEN quantity ELSE 0 END) as jetons
            ")
        )
            ->where('clan_id', $clan->id)
            ->whereBetween('date', [$special_date, $special_next_date])
            ->where(function ($q) {
                $q->where('for_talents', '!=', true)->orWhereNull('for_talents');
            })
            ->where(function ($q) {
                $q->where('repaid_the_debt', '!=', true)->orWhereNull('repaid_the_debt');
            })
            ->groupBy('name')
            ->get()
            ->filter(function ($row) {
                return $row->gold || $row->dust || $row->truth || $row->pages || $row->jetons;
            });

        $summaryTable = $this->getMonthlySummary($clan->id);

        return view('taxes.show', [
            'clan' => $clan,
            'logs' => $logs,
            'donutChart_coins' => $donutChart_coins,
            'donutChart_dust' => $donutChart_dust,
            'donutChart_crystals' => $donutChart_crystals,
            'donutChart_pages' => $donutChart_pages,
            'donutChart_jetons' => $donutChart_jetons,
            'playersData' => $yearlyLog['players'],
            'monthLabels' => $yearlyLog['months'],
            'summaryTable' => $summaryTable['table'],
            'summaryMonths' => $summaryTable['months'],
            'specialTotals' => $specialTotals,
            'special_date' => $special_date,
            'special_next_date' => $special_next_date,
        ]);
    }
    public function getYearlyLog($clanId)
    {
        $startDate = now()->subMonths(11)->startOfMonth();
        $endDate = now()->endOfMonth();

        $rows = TreasuryLog::select(
            'name',
            DB::raw("TO_CHAR(date, 'YYYY-MM') as ym"),
            DB::raw("
            SUM(CASE WHEN object = 'Монеты' THEN quantity ELSE 0 END) as gold,
            SUM(CASE WHEN object = 'Кристаллизованный прах' THEN quantity ELSE 0 END) as dust,
            SUM(CASE WHEN object = 'Кристаллы истины' THEN quantity ELSE 0 END) as truth,
            SUM(CASE WHEN object = 'Жетон «Времена года»' THEN quantity ELSE 0 END) as jetons,
            SUM(CASE WHEN object = 'Страница из трактата «Единство клана»' THEN quantity ELSE 0 END) as pages
        ")
        )
            ->where('clan_id', $clanId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where(function ($query) {
                $query->where('for_talents', '!=', true)->orWhereNull('for_talents');
            })
            ->where(function ($query) {
                $query->where('repaid_the_debt', '!=', true)->orWhereNull('repaid_the_debt');
            })
            ->groupBy('name', DB::raw("TO_CHAR(date, 'YYYY-MM')"))
            ->get();

        // Месяцы: текущий первый, затем предыдущие
        $months = collect();
        for ($i = 0; $i <= 11; $i++) {
            $months->push(now()->subMonths($i)->format('Y-m'));
        }
        // $months now: [current, -1, -2, ..., -11]

        // Группируем по игрокам и собираем суммы по месяцам
        $players = [];
        foreach ($rows as $row) {
            $name = $row->name;
            if (!isset($players[$name])) {
                $players[$name] = [
                    'name' => $name,
                    'months' => []
                ];
            }
            $players[$name]['months'][$row->ym] = [
                'gold' => (int) $row->gold,
                'dust' => (int) $row->dust,
                'truth' => (int) $row->truth,
                'jetons' => (int) $row->jetons,
                'pages' => (int) $row->pages
            ];
        }

        // Заполняем нулями отсутствующие месяцы и упорядочиваем согласно $months
        foreach ($players as $name => $player) {
            $ordered = [];
            foreach ($months as $month) {
                if (!isset($player['months'][$month])) {
                    $ordered[$month] = ['gold' => 0, 'dust' => 0, 'truth' => 0, 'jetons' => 0];
                } else {
                    $ordered[$month] = $player['months'][$month];
                }
            }
            $players[$name]['months'] = $ordered;
        }

        return [
            'players' => $players,
            'months' => $months->toArray()
        ];
    }
    public function getLog($clan_id)
    {
        $currentMonth = Carbon::now(); // Текущий месяц
        $previousMonth = Carbon::now()->subMonth(); // Предыдущий месяц
        $twoMonthsAgo = Carbon::now()->subMonths(2); // Месяц перед предыдущим

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

    private function getMonthlySummary($clanId)
    {
        $resourceNames = [
            'Монеты' => 'Золото',
            'Кристаллизованный прах' => 'Прах',
            'Кристаллы истины' => 'Истина',
            'Страница из трактата «Единство клана»' => 'Страницы',
        ];

        $start = now()->subMonths(6)->startOfMonth(); // За 6 месяцев
        $end = now()->subMonth()->endOfMonth(); // До прошлого месяца включительно

        $rawData = TreasuryLog::selectRaw("
        object,
        TO_CHAR(date, 'YYYY-MM') as month,
        SUM(quantity) as total
    ")
            ->where('clan_id', $clanId)
            ->whereIn('object', array_keys($resourceNames))
            ->whereBetween('date', [$start, $end])
            ->where(function ($query) {
                $query->where('for_talents', '!=', true)
                    ->orWhereNull('for_talents');
            })
            ->where(function ($query) {
                $query->where('repaid_the_debt', '!=', true)
                    ->orWhereNull('repaid_the_debt');
            })
            ->groupBy('object', DB::raw("TO_CHAR(date, 'YYYY-MM')"))
            ->get();

        $months = collect();
        for ($i = 6; $i >= 1; $i--) {
            $monthKey = now()->subMonths($i)->format('Y-m');
            $months->push($monthKey);
        }

        $months = $months->reverse(); // <-- ВАЖНО: разворот порядка

        $table = [];
        foreach ($resourceNames as $dbName => $label) {
            $monthlyTotals = [];

            foreach ($months as $month) {
                $value = $rawData
                    ->where('object', $dbName)
                    ->where('month', $month)
                    ->first()
                    ->total ?? 0;

                $monthlyTotals[$month] = round($value);
            }

            // расчёт устойчивого среднего (усечённого по MAD)
            $robust = $this->getRobustAverage(array_values($monthlyTotals));

            $table[] = [
                'name' => $label,
                'average' => $robust['average'],
                'months' => $monthlyTotals,
                'excluded' => $robust['excluded'], // добавим список исключённых значений
            ];
        }

        $monthLabels = $months->map(fn($m) => \Carbon\Carbon::parse($m . '-01')->translatedFormat('F Y'))->toArray();

        return [
            'table' => $table,
            'months' => $monthLabels,
        ];
    }


    private function getRobustAverage(array $values, float $thresholdMultiplier = 2.0): array
    {
        $filtered = array_filter($values, fn($v) => $v != 0);
        $count = count($filtered);

        if ($count === 0) {
            return [
                'average' => 0,
                'excluded' => [],
            ];
        }

        sort($filtered);
        $median = $this->getMedian($filtered);

        $deviations = array_map(fn($v) => abs($v - $median), $filtered);
        $mad = $this->getMedian($deviations);

        $cleaned = [];
        $excluded = [];

        foreach ($filtered as $v) {
            if ($mad == 0 || abs($v - $median) <= $thresholdMultiplier * $mad) {
                $cleaned[] = $v;
            } else {
                $excluded[] = $v;
            }
        }

        $average = count($cleaned) > 0
            ? round(array_sum($cleaned) / count($cleaned))
            : round(array_sum($filtered) / $count);

        return [
            'average' => $average,
            'excluded' => $excluded,
        ];
    }

    private function getMedian(array $arr): float
    {
        $count = count($arr);
        if ($count === 0) {
            return 0;
        }

        sort($arr);
        $middle = (int) floor($count / 2);

        return $count % 2
            ? $arr[$middle]
            : ($arr[$middle - 1] + $arr[$middle]) / 2;
    }
}

