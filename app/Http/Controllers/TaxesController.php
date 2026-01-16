<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Clan;
use App\Models\TreasuryLog;
use Carbon\Carbon;

class TaxesController extends Controller
{
    public function show($token)
    {
        $clan = Clan::where('token', $token)->firstOrFail();

        // 🔹 СПЕЦИАЛЬНЫЙ ИНТЕРВАЛ
        $special_date = Carbon::createFromFormat('d.m.Y H:i', '12.01.2026 18:00');
        $special_next_date = Carbon::createFromFormat('d.m.Y', '15.03.2026')->endOfDay();

        // 🔹 КУРСЫ РЕСУРСОВ (за 1 единицу в золоте)
        $rates = [
            'pages'  => 0.62,
            'truth'  => 0.043,
            'dust'   => 0.05,
            'jetons' => 0,
        ];

        // 🔹 МАКСИМУМЫ (в штуках)
        $limits_count = [
            'pages'  => 60000 - 2247,
            'truth'  => 418500 - 370,
            'dust'   => 45000 - 2573,
            'jetons' => 530 - 1394,
        ];

        $total_gold_goal = ($limits_count['pages'] * $rates['pages']) +
            ($limits_count['truth'] * $rates['truth']) +
            ($limits_count['dust'] * $rates['dust']) +
            ($limits_count['jetons'] * $rates['jetons']);

        $logs = $this->getLog($clan->id);
        $yearlyLog = $this->getYearlyLog($clan->id);
        $summaryTable = $this->getMonthlySummary($clan->id);

        // 🔹 ВЗНОСЫ ЗА ПЕРИОД
        $specialTotals = TreasuryLog::select(
            'name',
            DB::raw("SUM(CASE WHEN object = 'Монеты' THEN quantity ELSE 0 END) as gold"),
            DB::raw("SUM(CASE WHEN object = 'Кристаллизованный прах' THEN quantity ELSE 0 END) as dust"),
            DB::raw("SUM(CASE WHEN object = 'Кристаллы истины' THEN quantity ELSE 0 END) as truth"),
            DB::raw("SUM(CASE WHEN object = 'Страница из трактата «Единство клана»' THEN quantity ELSE 0 END) as pages"),
            DB::raw("SUM(CASE WHEN object = 'Жетон «Времена года»' THEN quantity ELSE 0 END) as jetons"),
            // Новые ресурсы
            DB::raw("SUM(CASE WHEN object IN ('Огневик', 'Горецвет', 'Инкарнум', 'Центридо') THEN quantity ELSE 0 END) as resources_for_pages")
        )
            ->where('clan_id', $clan->id)
            ->whereBetween('date', [$special_date, $special_next_date])
            ->where(function ($q) { $q->where('for_talents', '!=', true)->orWhereNull('for_talents'); })
            ->where(function ($q) { $q->where('repaid_the_debt', '!=', true)->orWhereNull('repaid_the_debt'); })
            ->groupBy('name')
            ->get();

        // Формируем данные для графиков (Страницы теперь включают ресурсы с коэф 0.5)
        $chartData = [
            'gold'   => $specialTotals->pluck('gold', 'name')->filter(fn($v) => $v > 0)->map(fn($v) => (float)$v)->toArray(),
            'dust'   => $specialTotals->pluck('dust', 'name')->filter(fn($v) => $v > 0)->map(fn($v) => (float)$v)->toArray(),
            'truth'  => $specialTotals->pluck('truth', 'name')->filter(fn($v) => $v > 0)->map(fn($v) => (float)$v)->toArray(),
            'pages'  => $specialTotals->mapWithKeys(function ($item) {
                $total = $item->pages + ($item->resources_for_pages * 0.5);
                return [$item->name => (float)$total];
            })->filter(fn($v) => $v > 0)->toArray(),
            'jetons' => $specialTotals->pluck('jetons', 'name')->filter(fn($v) => $v > 0)->map(fn($v) => (float)$v)->toArray(),
        ];

        // ЗОЛОТОЙ ЭКВИВАЛЕНТ
        $goldEquivalentData = [];
        foreach ($specialTotals as $row) {
            $equiv = $row->gold +
                (($row->pages + ($row->resources_for_pages * 0.5)) * $rates['pages']) +
                ($row->truth * $rates['truth']) +
                ($row->dust * $rates['dust']) +
                ($row->jetons * $rates['jetons']);

            if ($equiv > 0) {
                $goldEquivalentData[$row->name] = round($equiv, 2);
            }
        }

        $limits = array_merge($limits_count, ['gold' => round($total_gold_goal, 2)]);

        return view('taxes.show', [
            'clan' => $clan,
            'logs' => $logs,
            'playersData' => $yearlyLog['players'],
            'monthLabels' => $yearlyLog['months'],
            'summaryTable' => $summaryTable['table'],
            'summaryMonths' => $summaryTable['months'],
            'chartData' => $chartData,
            'goldEquivalentData' => $goldEquivalentData,
            'limits' => $limits,
            'special_date' => $special_date,
            'special_next_date' => $special_next_date,
        ]);
    }

    public function getYearlyLog($clanId) {
        $startDate = now()->subMonths(11)->startOfMonth();
        $endDate = now()->endOfMonth();
        $rows = TreasuryLog::select('name',
            DB::raw("TO_CHAR(date, 'YYYY-MM') as ym"),
            DB::raw("
                SUM(CASE WHEN object = 'Монеты' THEN quantity ELSE 0 END) as gold,
                SUM(CASE WHEN object = 'Кристаллизованный прах' THEN quantity ELSE 0 END) as dust,
                SUM(CASE WHEN object = 'Кристаллы истины' THEN quantity ELSE 0 END) as truth,
                SUM(CASE WHEN object = 'Жетон «Времена года»' THEN quantity ELSE 0 END) as jetons,
                SUM(CASE WHEN object = 'Страница из трактата «Единство клана»' THEN quantity ELSE 0 END) as pages,
                SUM(CASE WHEN object = 'Огневик' THEN quantity ELSE 0 END) as ognevik,
                SUM(CASE WHEN object = 'Горецвет' THEN quantity ELSE 0 END) as gorecvet,
                SUM(CASE WHEN object = 'Инкарнум' THEN quantity ELSE 0 END) as incarnum,
                SUM(CASE WHEN object = 'Центридо' THEN quantity ELSE 0 END) as centrido
            ")
        )
            ->where('clan_id', $clanId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where(function ($query) { $query->where('for_talents', '!=', true)->orWhereNull('for_talents'); })
            ->where(function ($query) { $query->where('repaid_the_debt', '!=', true)->orWhereNull('repaid_the_debt'); })
            ->groupBy('name', DB::raw("TO_CHAR(date, 'YYYY-MM')"))
            ->get();

        $months = collect();
        for ($i = 0; $i <= 11; $i++) { $months->push(now()->subMonths($i)->format('Y-m')); }
        $players = [];
        foreach ($rows as $row) {
            $name = $row->name;
            if (!isset($players[$name])) { $players[$name] = ['name' => $name, 'months' => []]; }
            $players[$name]['months'][$row->ym] = [
                'gold' => (int)$row->gold, 'dust' => (int)$row->dust, 'truth' => (int)$row->truth,
                'jetons' => (int)$row->jetons, 'pages' => (int)$row->pages,
                'resources' => [
                    'Огневик' => (int)$row->ognevik, 'Горецвет' => (int)$row->gorecvet,
                    'Инкарнум' => (int)$row->incarnum, 'Центридо' => (int)$row->centrido
                ]
            ];
        }
        foreach ($players as $name => $player) {
            $ordered = [];
            foreach ($months as $month) {
                $ordered[$month] = $player['months'][$month] ?? [
                    'gold' => 0, 'dust' => 0, 'truth' => 0, 'jetons' => 0, 'pages' => 0,
                    'resources' => ['Огневик' => 0, 'Горецвет' => 0, 'Инкарнум' => 0, 'Центридо' => 0]
                ];
            }
            $players[$name]['months'] = $ordered;
        }
        return ['players' => $players, 'months' => $months->toArray()];
    }

    public function getLog($clan_id) {
        $currentMonth = Carbon::now();
        return TreasuryLog::select('name',
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Монеты' THEN quantity ELSE 0 END) as coins_current_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Кристаллизованный прах' THEN quantity ELSE 0 END) as dust_current_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Кристаллы истины' THEN quantity ELSE 0 END) as crystals_current_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Страница из трактата «Единство клана»' THEN quantity ELSE 0 END) as pages_current_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Жетон «Времена года»' THEN quantity ELSE 0 END) as jetons_current_month")
        )
            ->where('clan_id', $clan_id)
            ->where(function ($q) { $q->where('for_talents', '!=', true)->orWhereNull('for_talents'); })
            ->where(function ($q) { $q->where('repaid_the_debt', '!=', true)->orWhereNull('repaid_the_debt'); })
            ->groupBy('name')->get();
    }

    private function getMonthlySummary($clanId) {
        $resourceNames = ['Монеты' => 'Золото', 'Кристаллизованный прах' => 'Прах', 'Кристаллы истины' => 'Истина', 'Страница из трактата «Единство клана»' => 'Страницы'];
        $start = now()->subMonths(6)->startOfMonth();
        $end = now()->subMonth()->endOfMonth();
        $rawData = TreasuryLog::selectRaw("object, TO_CHAR(date, 'YYYY-MM') as month, SUM(quantity) as total")->where('clan_id', $clanId)->whereIn('object', array_keys($resourceNames))->whereBetween('date', [$start, $end])->where(function ($q) { $q->where('for_talents', '!=', true)->orWhereNull('for_talents'); })->groupBy('object', DB::raw("TO_CHAR(date, 'YYYY-MM')"))->get();
        $months = collect();
        for ($i = 6; $i >= 1; $i--) { $months->push(now()->subMonths($i)->format('Y-m')); }
        $months = $months->reverse();
        $table = [];
        foreach ($resourceNames as $dbName => $label) {
            $monthlyTotals = [];
            foreach ($months as $month) { $monthlyTotals[$month] = round($rawData->where('object', $dbName)->where('month', $month)->first()->total ?? 0); }
            $robust = $this->getRobustAverage(array_values($monthlyTotals));
            $table[] = ['name' => $label, 'average' => $robust['average'], 'months' => $monthlyTotals, 'excluded' => $robust['excluded']];
        }
        return ['table' => $table, 'months' => $months->map(fn($m) => Carbon::parse($m . '-01')->translatedFormat('F Y'))->toArray()];
    }

    private function getRobustAverage(array $values): array { $filtered = array_filter($values, fn($v) => $v != 0); if (count($filtered) === 0) return ['average' => 0, 'excluded' => []]; sort($filtered); $median = $this->getMedian($filtered); $deviations = array_map(fn($v) => abs($v - $median), $filtered); $mad = $this->getMedian($deviations); $cleaned = []; $excluded = []; foreach ($filtered as $v) { if ($mad == 0 || abs($v - $median) <= 2.0 * $mad) { $cleaned[] = $v; } else { $excluded[] = $v; } } return ['average' => count($cleaned) > 0 ? round(array_sum($cleaned) / count($cleaned)) : round(array_sum($filtered) / count($filtered)), 'excluded' => $excluded]; }
    private function getMedian(array $arr): float { $count = count($arr); if ($count === 0) return 0; sort($arr); $mid = (int) floor($count / 2); return $count % 2 ? $arr[$mid] : ($arr[$mid - 1] + $arr[$mid]) / 2; }
}
