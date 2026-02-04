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

        $special_date = Carbon::createFromFormat('d.m.Y H:i', '12.01.2026 18:00');
        $special_next_date = Carbon::createFromFormat('d.m.Y', '15.03.2026')->endOfDay();

        $rates = ['pages' => 0.65, 'truth' => 0.043, 'dust' => 0.05, 'jetons' => 0];
        $exchange_rates = ['Огневик' => 3, 'Горецвет' => 2, 'Инкарнум' => 2, 'Центридо' => 2];

        $limits_count = [
            'pages'  => 60000 - 2247,
            'truth'  => 418500 - 370,
            'dust'   => 45000 - 2573,
            'jetons' => 530 - 1394,
        ];

        // 🔹 ЛИМИТЫ ДЛЯ МЕЖДУМИРЬЯ
        $extra_limits = [
            'brasleti_jinov' => 1500, 'mo_trava_zel' => 1500, 'mo_kamen_zel' => 1500, 'mo_riba_zel' => 1500,
            'mo_trava_sin' => 600, 'mo_kamen_sin' => 600, 'mo_riba_sin' => 600,
            'mo_trava_fiol' => 200, 'mo_kamen_fiol' => 200, 'mo_riba_fiol' => 200,
        ];

        $total_gold_goal = ($limits_count['pages'] * $rates['pages']) + ($limits_count['truth'] * $rates['truth']) + ($limits_count['dust'] * $rates['dust']);

        $logs = $this->getLog($clan->id);
        $yearlyLog = $this->getYearlyLog($clan->id);
        $summaryTable = $this->getMonthlySummary($clan->id);

        // 🔹 ВЗНОСЫ ЗА ПЕРИОД (ТАЛАНТЫ)
        $specialTotals = TreasuryLog::select('name',
            DB::raw("SUM(CASE WHEN object = 'Монеты' THEN quantity ELSE 0 END) as gold"),
            DB::raw("SUM(CASE WHEN object = 'Кристаллизованный прах' THEN quantity ELSE 0 END) as dust"),
            DB::raw("SUM(CASE WHEN object = 'Кристаллы истины' THEN quantity ELSE 0 END) as truth"),
            DB::raw("SUM(CASE WHEN object = 'Страница из трактата «Единство клана»' THEN quantity ELSE 0 END) as pages"),
            DB::raw("SUM(CASE WHEN object = 'Жетон «Времена года»' THEN quantity ELSE 0 END) as jetons"),
            DB::raw("SUM(CASE WHEN object = 'Огневик' THEN quantity ELSE 0 END) as res_ognevik"),
            DB::raw("SUM(CASE WHEN object = 'Горецвет' THEN quantity ELSE 0 END) as res_gorecvet"),
            DB::raw("SUM(CASE WHEN object = 'Инкарнум' THEN quantity ELSE 0 END) as res_incarnum"),
            DB::raw("SUM(CASE WHEN object = 'Центридо' THEN quantity ELSE 0 END) as res_centrido")
        )
            ->where('clan_id', $clan->id)->whereBetween('date', [$special_date, $special_next_date])
            ->where(function ($q) { $q->where('for_talents', '!=', true)->orWhereNull('for_talents'); })
            ->where(function ($q) { $q->where('repaid_the_debt', '!=', true)->orWhereNull('repaid_the_debt'); })
            ->groupBy('name')->get();

        // 🔹 ВЗНОСЫ ЗА ВСЁ ВРЕМЯ (Мистрас)
        $extraTotalsRaw = TreasuryLog::select('name',
            DB::raw("SUM(CASE WHEN object = 'Браслеты джиннов' THEN quantity ELSE 0 END) as brasleti_jinov"),
            DB::raw("SUM(CASE WHEN object = 'Мо-датхар альвы благонравной' THEN quantity ELSE 0 END) as mo_trava_zel"),
            DB::raw("SUM(CASE WHEN object = 'Мо-датхар нурида' THEN quantity ELSE 0 END) as mo_kamen_zel"),
            DB::raw("SUM(CASE WHEN object = 'Мо-датхар золтой шамсы' THEN quantity ELSE 0 END) as mo_riba_zel"),
            DB::raw("SUM(CASE WHEN object = 'Мо-датхар чёрного лотоса' THEN quantity ELSE 0 END) as mo_trava_sin"),
            DB::raw("SUM(CASE WHEN object = 'Мо-датхар шахифрита' THEN quantity ELSE 0 END) as mo_kamen_sin"),
            DB::raw("SUM(CASE WHEN object = 'Мо-датхар мистрасского рыбозмея' THEN quantity ELSE 0 END) as mo_riba_sin"),
            DB::raw("SUM(CASE WHEN object = 'Мо-датхар аракша неугасимого' THEN quantity ELSE 0 END) as mo_trava_fiol"),
            DB::raw("SUM(CASE WHEN object = 'Мо-датхар замридина' THEN quantity ELSE 0 END) as mo_kamen_fiol"),
            DB::raw("SUM(CASE WHEN object = 'Мо-датхар акдуфа-многонога' THEN quantity ELSE 0 END) as mo_riba_fiol")
        )
            ->where('clan_id', $clan->id)->groupBy('name')->get();

        $calculatePagesContribution = function($row) use ($exchange_rates) {
            return ($row->res_ognevik / 3) + ($row->res_gorecvet / 2) + ($row->res_incarnum / 2) + ($row->res_centrido / 2);
        };

        $chartData = [
            'gold'   => $specialTotals->pluck('gold', 'name')->map(fn($v) => (float)$v)->toArray(),
            'dust'   => $specialTotals->pluck('dust', 'name')->map(fn($v) => (float)$v)->toArray(),
            'truth'  => $specialTotals->pluck('truth', 'name')->map(fn($v) => (float)$v)->toArray(),
            'pages'  => $specialTotals->mapWithKeys(fn($item) => [$item->name => (float)($item->pages + $calculatePagesContribution($item))])->toArray(),
            'jetons' => $specialTotals->pluck('jetons', 'name')->map(fn($v) => (float)$v)->toArray(),
        ];

        // Группируем данные для диаграмм Мистрас
        $extraChartsData = [];
        $realTotals = [];
        foreach (array_keys($extra_limits) as $key) {
            $extraChartsData[$key] = $extraTotalsRaw->pluck($key, 'name')->toArray();
            $realTotals[$key] = $extraTotalsRaw->sum($key); // Честный Total для центра круга
        }

        $goldEquivalentData = [];
        foreach ($specialTotals as $row) {
            $equiv = $row->gold + (($row->pages + $calculatePagesContribution($row)) * $rates['pages']) + ($row->truth * $rates['truth']) + ($row->dust * $rates['dust']);
            if ($equiv !== 0) $goldEquivalentData[$row->name] = round($equiv, 2);
        }

        $lastUpdate = TreasuryLog::where('clan_id', $clan->id)->max('date');

        return view('taxes.show', [
            'clan' => $clan, 'logs' => $logs, 'playersData' => $yearlyLog['players'], 'monthLabels' => $yearlyLog['months'],
            'summaryTable' => $summaryTable['table'], 'summaryMonths' => $summaryTable['months'],
            'chartData' => $chartData, 'goldEquivalentData' => $goldEquivalentData,
            'extraChartsData' => $extraChartsData, 'extraLimits' => $extra_limits, 'realTotals' => $realTotals,
            'limits' => array_merge($limits_count, ['gold' => round($total_gold_goal, 2)]),
            'special_date' => $special_date, 'special_next_date' => $special_next_date,
            'lastUpdate' => $lastUpdate ? Carbon::parse($lastUpdate) : now(),
        ]);
    }

    public function getYearlyLog($clanId) {
        $startDate = now()->subMonths(11)->startOfMonth();
        $rows = TreasuryLog::select('name', DB::raw("TO_CHAR(date, 'YYYY-MM') as ym"),
            DB::raw("
                SUM(CASE WHEN object = 'Монеты' THEN quantity ELSE 0 END) as gold,
                SUM(CASE WHEN object = 'Кристаллизованный прах' THEN quantity ELSE 0 END) as dust,
                SUM(CASE WHEN object = 'Кристаллы истины' THEN quantity ELSE 0 END) as truth,
                SUM(CASE WHEN object = 'Жетон «Времена года»' THEN quantity ELSE 0 END) as jetons,
                SUM(CASE WHEN object = 'Страница из трактата «Единство клана»' THEN quantity ELSE 0 END) as pages,
                SUM(CASE WHEN object = 'Огневик' THEN quantity ELSE 0 END) as ognevik,
                SUM(CASE WHEN object = 'Горецвет' THEN quantity ELSE 0 END) as gorecvet,
                SUM(CASE WHEN object = 'Инкарнум' THEN quantity ELSE 0 END) as incarnum,
                SUM(CASE WHEN object = 'Центридо' THEN quantity ELSE 0 END) as centrido,
                SUM(CASE WHEN object = 'Браслеты джиннов' THEN quantity ELSE 0 END) as extra_1,
                SUM(CASE WHEN object = 'Мо-датхар альвы благонравной' THEN quantity ELSE 0 END) as extra_2,
                SUM(CASE WHEN object = 'Мо-датхар нурида' THEN quantity ELSE 0 END) as extra_3,
                SUM(CASE WHEN object = 'Мо-датхар золтой шамсы' THEN quantity ELSE 0 END) as extra_4,
                SUM(CASE WHEN object = 'Мо-датхар чёрного лотоса' THEN quantity ELSE 0 END) as extra_5,
                SUM(CASE WHEN object = 'Мо-датхар шахифрита' THEN quantity ELSE 0 END) as extra_6,
                SUM(CASE WHEN object = 'Мо-датхар мистрасского рыбозмея' THEN quantity ELSE 0 END) as extra_7,
                SUM(CASE WHEN object = 'Мо-датхар аракша неугасимого' THEN quantity ELSE 0 END) as extra_8,
                SUM(CASE WHEN object = 'Мо-датхар замридина' THEN quantity ELSE 0 END) as extra_9,
                SUM(CASE WHEN object = 'Мо-датхар акдуфа-многонога' THEN quantity ELSE 0 END) as extra_10
            ")
        )->where('clan_id', $clanId)->whereBetween('date', [$startDate, now()->endOfMonth()])
            ->where(function ($q) { $q->where('for_talents', '!=', true)->orWhereNull('for_talents'); })
            ->where(function ($q) { $q->where('repaid_the_debt', '!=', true)->orWhereNull('repaid_the_debt'); })
            ->groupBy('name', 'ym')->get();

        $months = collect();
        for ($i = 0; $i <= 11; $i++) { $months->push(now()->subMonths($i)->format('Y-m')); }

        $players = [];
        foreach ($rows as $row) {
            $name = $row->name;
            if (!isset($players[$name])) { $players[$name] = ['name' => $name, 'months' => []]; }
            $players[$name]['months'][$row->ym] = [
                'gold' => (int)$row->gold, 'dust' => (int)$row->dust, 'truth' => (int)$row->truth,
                'jetons' => (int)$row->jetons, 'pages' => (int)$row->pages,
                'resources' => ['Огневик' => (int)$row->ognevik, 'Горецвет' => (int)$row->gorecvet, 'Инкарнум' => (int)$row->incarnum, 'Центридо' => (int)$row->centrido],
                // 🔹 ДОБАВЛЯЕМ НОВЫЕ РЕСУРСЫ В МАССИВ МЕСЯЦА
                'extra' => [
                    'Браслеты джиннов' => (int)$row->extra_1, 'Мо-датхар альвы благонравной' => (int)$row->extra_2, 'Мо-датхар нурида' => (int)$row->extra_3, 'Мо-датхар золтой шамсы' => (int)$row->extra_4,
                    'Мо-датхар чёрного лотоса' => (int)$row->extra_5, 'Мо-датхар шахифрита' => (int)$row->extra_6, 'Мо-датхар мистрасского рыбозмея' => (int)$row->extra_7,
                    'Мо-датхар аракша неугасимого' => (int)$row->extra_8, 'Мо-датхар замридина' => (int)$row->extra_9, 'Мо-датхар акдуфа-многонога' => (int)$row->extra_10
                ]
            ];
        }

        foreach ($players as $name => $player) {
            $ordered = [];
            foreach ($months as $month) {
                $ordered[$month] = $player['months'][$month] ?? [
                    'gold' => 0, 'dust' => 0, 'truth' => 0, 'jetons' => 0, 'pages' => 0,
                    'resources' => ['Огневик' => 0, 'Горецвет' => 0, 'Инкарнум' => 0, 'Центридо' => 0],
                    'extra' => [] // Пусто для месяцев без взносов
                ];
            }
            $players[$name]['months'] = $ordered;
        }
        return ['players' => $players, 'months' => $months->toArray()];
    }

    // Остальные методы (getLog, getMonthlySummary, getRobustAverage, getMedian)
    // оставляем как в вашем старом контроллере без изменений...
    public function getLog($clan_id) {
        $currentMonth = Carbon::now();
        return TreasuryLog::select('name',
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Монеты' THEN quantity ELSE 0 END) as coins_current_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Кристаллизованный прах' THEN quantity ELSE 0 END) as dust_current_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Кристаллы истины' THEN quantity ELSE 0 END) as crystals_current_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Страница из трактата «Единство клана»' THEN quantity ELSE 0 END) as pages_current_month"),
            DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$currentMonth->month} AND EXTRACT(YEAR FROM date) = {$currentMonth->year} AND object = 'Жетон «Времена года»' THEN quantity ELSE 0 END) as jetons_current_month")
        )->where('clan_id', $clan_id)->where(function ($q) { $q->where('for_talents', '!=', true)->orWhereNull('for_talents'); })->where(function ($q) { $q->where('repaid_the_debt', '!=', true)->orWhereNull('repaid_the_debt'); })->groupBy('name')->get();
    }

    private function getMonthlySummary($clanId) {
        $resourceNames = ['Монеты' => 'Золото', 'Кристаллизованный прах' => 'Прах', 'Кристаллы истины' => 'Истина', 'Страница из трактата «Единство клана»' => 'Страницы'];
        $rawData = TreasuryLog::selectRaw("object, TO_CHAR(date, 'YYYY-MM') as month, SUM(quantity) as total")->where('clan_id', $clanId)->whereIn('object', array_keys($resourceNames))->whereBetween('date', [now()->subMonths(6)->startOfMonth(), now()->subMonth()->endOfMonth()])->where(function ($q) { $q->where('for_talents', '!=', true)->orWhereNull('for_talents'); })->groupBy('object', 'month')->get();
        $months = collect(); for ($i = 6; $i >= 1; $i--) $months->push(now()->subMonths($i)->format('Y-m'));
        $months = $months->reverse();
        $table = [];
        foreach ($resourceNames as $dbName => $label) {
            $monthlyTotals = []; foreach ($months as $month) $monthlyTotals[$month] = round($rawData->where('object', $dbName)->where('month', $month)->first()->total ?? 0);
            $robust = $this->getRobustAverage(array_values($monthlyTotals));
            $table[] = ['name' => $label, 'average' => $robust['average'], 'months' => $monthlyTotals, 'excluded' => $robust['excluded']];
        }
        return ['table' => $table, 'months' => $months->map(fn($m) => Carbon::parse($m . '-01')->translatedFormat('F Y'))->toArray()];
    }
    private function getRobustAverage(array $values): array { $filtered = array_filter($values, fn($v) => $v != 0); if (count($filtered) === 0) return ['average' => 0, 'excluded' => []]; sort($filtered); $median = $this->getMedian($filtered); $deviations = array_map(fn($v) => abs($v - $median), $filtered); $mad = $this->getMedian($deviations); $cleaned = []; $excluded = []; foreach ($filtered as $v) { if ($mad == 0 || abs($v - $median) <= 2.0 * $mad) $cleaned[] = $v; else $excluded[] = $v; } return ['average' => count($cleaned) > 0 ? round(array_sum($cleaned) / count($cleaned)) : round(array_sum($filtered) / count($filtered)), 'excluded' => $excluded]; }
    private function getMedian(array $arr): float { $count = count($arr); if ($count === 0) return 0; sort($arr); $mid = (int) floor($count / 2); return $count % 2 ? $arr[$mid] : ($arr[$mid - 1] + $arr[$mid]) / 2; }
}
