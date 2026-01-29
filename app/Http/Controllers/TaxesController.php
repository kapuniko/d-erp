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

        // 🔹 ИНТЕРВАЛ ДЛЯ ТАЛАНТОВ
        $special_date = Carbon::createFromFormat('d.m.Y H:i', '12.01.2026 18:00');
        $special_next_date = Carbon::createFromFormat('d.m.Y', '15.03.2026')->endOfDay();

        $rates = ['pages' => 0.62, 'truth' => 0.043, 'dust' => 0.05, 'jetons' => 0];
        $exchange_rates = ['Огневик' => 3, 'Горецвет' => 2, 'Инкарнум' => 2, 'Центридо' => 2];

        $limits_count = [
            'pages'  => 60000 - 2247,
            'truth'  => 418500 - 370,
            'dust'   => 45000 - 2573,
            'jetons' => 530 - 1394,
        ];

        $extra_limits = [
            'brasleti_jinov' => 100,
            'mo_trava_zel'   => 500,
            'mo_kamen_zel'   => 500,
            'mo_riba_zel'    => 500,
            'mo_trava_sin'   => 300,
            'mo_kamen_sin'   => 300,
            'mo_riba_sin'    => 300,
            'mo_trava_fiol'  => 100,
            'mo_kamen_fiol'  => 100,
            'mo_riba_fiol'   => 100,
        ];

        $total_gold_goal = ($limits_count['pages'] * $rates['pages']) +
            ($limits_count['truth'] * $rates['truth']) +
            ($limits_count['dust'] * $rates['dust']);

        $logs = $this->getLog($clan->id);
        $yearlyLog = $this->getYearlyLog($clan->id);
        $summaryTable = $this->getMonthlySummary($clan->id);

        // 🔹 ДАННЫЕ ПО ТАЛАНТАМ (с учетом минусов для точного Total)
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
            ->where('clan_id', $clan->id)
            ->whereBetween('date', [$special_date, $special_next_date])
            ->where(function ($q) { $q->where('for_talents', '!=', true)->orWhereNull('for_talents'); })
            ->groupBy('name')->get();

        $calculatePages = function($row) use ($exchange_rates) {
            return floor(($row->res_ognevik / ($exchange_rates['Огневик'] ?? 3)) + ($row->res_gorecvet / 2) + ($row->res_incarnum / 2) + ($row->res_centrido / 2));
        };

        // 🔹 ДАННЫЕ ПО МЕЖДУМИРЬЮ (с группировкой по игрокам)
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

        $chartData = [];
        $extraChartsData = [];
        $realTotals = [];

        // Обработка данных для талантов
        foreach (['gold', 'dust', 'truth', 'jetons'] as $k) {
            $chartData[$k] = $specialTotals->pluck($k, 'name')->filter(fn($v) => $v > 0)->toArray();
            $realTotals[$k] = $specialTotals->sum($k);
        }
        $chartData['pages'] = $specialTotals->mapWithKeys(fn($item) => [$item->name => (float)($item->pages + $calculatePages($item))])->filter(fn($v) => $v > 0)->toArray();
        $realTotals['pages'] = $specialTotals->sum('pages') + $specialTotals->sum(fn($i) => $calculatePages($i));

        // Обработка данных для Междумирья
        foreach (array_keys($extra_limits) as $key) {
            $extraChartsData[$key] = $extraTotalsRaw->pluck($key, 'name')->filter(fn($v) => $v > 0)->toArray();
            $realTotals[$key] = $extraTotalsRaw->sum($key);
        }

        $goldEquivalentData = [];
        foreach ($specialTotals as $row) {
            $equiv = $row->gold + (($row->pages + $calculatePages($row)) * $rates['pages']) + ($row->truth * $rates['truth']) + ($row->dust * $rates['dust']);
            if ($equiv > 0) $goldEquivalentData[$row->name] = round($equiv, 2);
        }
        $realTotals['gold'] = array_sum($goldEquivalentData);

        return view('taxes.show', [
            'clan' => $clan, 'logs' => $logs, 'playersData' => $yearlyLog['players'], 'monthLabels' => $yearlyLog['months'],
            'summaryTable' => $summaryTable['table'], 'summaryMonths' => $summaryTable['months'],
            'chartData' => $chartData, 'goldEquivalentData' => $goldEquivalentData,
            'extraChartsData' => $extraChartsData, 'extraLimits' => $extra_limits, 'realTotals' => $realTotals,
            'limits' => array_merge($limits_count, ['gold' => round($total_gold_goal, 2)]),
            'special_date' => $special_date, 'special_next_date' => $special_next_date,
        ]);
    }

    public function getYearlyLog($clanId) {
        $startDate = now()->subMonths(11)->startOfMonth();
        $rows = TreasuryLog::select('name', DB::raw("TO_CHAR(date, 'YYYY-MM') as ym"),
            DB::raw("SUM(CASE WHEN object = 'Монеты' THEN quantity ELSE 0 END) as gold, SUM(CASE WHEN object = 'Кристаллизованный прах' THEN quantity ELSE 0 END) as dust, SUM(CASE WHEN object = 'Кристаллы истины' THEN quantity ELSE 0 END) as truth, SUM(CASE WHEN object = 'Жетон «Времена года»' THEN quantity ELSE 0 END) as jetons, SUM(CASE WHEN object = 'Страница из трактата «Единство клана»' THEN quantity ELSE 0 END) as pages, SUM(CASE WHEN object = 'Огневик' THEN quantity ELSE 0 END) as ognevik, SUM(CASE WHEN object = 'Горецвет' THEN quantity ELSE 0 END) as gorecvet, SUM(CASE WHEN object = 'Инкарнум' THEN quantity ELSE 0 END) as incarnum, SUM(CASE WHEN object = 'Центридо' THEN quantity ELSE 0 END) as centrido,
                SUM(CASE WHEN object = 'Браслеты джиннов' THEN quantity ELSE 0 END) as extra_1, SUM(CASE WHEN object = 'Мо-датхар альвы благонравной' THEN quantity ELSE 0 END) as extra_2, SUM(CASE WHEN object = 'Мо-датхар нурида' THEN quantity ELSE 0 END) as extra_3, SUM(CASE WHEN object = 'Мо-датхар золтой шамсы' THEN quantity ELSE 0 END) as extra_4, SUM(CASE WHEN object = 'Мо-датхар чёрного лотоса' THEN quantity ELSE 0 END) as extra_5, SUM(CASE WHEN object = 'Мо-датхар шахифрита' THEN quantity ELSE 0 END) as extra_6, SUM(CASE WHEN object = 'Мо-датхар мистрасского рыбозмея' THEN quantity ELSE 0 END) as extra_7, SUM(CASE WHEN object = 'Мо-датхар аракша неугасимого' THEN quantity ELSE 0 END) as extra_8, SUM(CASE WHEN object = 'Мо-датхар замридина' THEN quantity ELSE 0 END) as extra_9, SUM(CASE WHEN object = 'Мо-датхар акдуфа-многонога' THEN quantity ELSE 0 END) as extra_10")
        )->where('clan_id', $clanId)->whereBetween('date', [$startDate, now()->endOfMonth()])->groupBy('name', 'ym')->get();

        $months = collect();
        for ($i = 0; $i <= 11; $i++) $months->push(now()->subMonths($i)->format('Y-m'));
        $players = [];
        foreach ($rows as $row) {
            $players[$row->name]['months'][$row->ym] = [
                'gold' => (int)$row->gold, 'dust' => (int)$row->dust, 'truth' => (int)$row->truth, 'jetons' => (int)$row->jetons, 'pages' => (int)$row->pages,
                'resources' => ['Огневик' => (int)$row->ognevik, 'Горецвет' => (int)$row->gorecvet, 'Инкарнум' => (int)$row->incarnum, 'Центридо' => (int)$row->centrido],
                'extra' => ['Браслеты джиннов' => (int)$row->extra_1, 'Мо-датхар альвы благонравной' => (int)$row->extra_2, 'Мо-датхар нурида' => (int)$row->extra_3, 'Мо-датхар золтой шамсы' => (int)$row->extra_4, 'Мо-датхар чёрного лотоса' => (int)$row->extra_5, 'Мо-датхар шахифрита' => (int)$row->extra_6, 'Мо-датхар мистрасского рыбозмея' => (int)$row->extra_7, 'Мо-датхар аракша неугасимого' => (int)$row->extra_8, 'Мо-датхар замридина' => (int)$row->extra_9, 'Мо-датхар акдуфа-многонога' => (int)$row->extra_10]
            ];
        }
        foreach ($players as $n => $p) {
            $players[$n]['name'] = $n;
            foreach ($months as $m) if (!isset($players[$n]['months'][$m])) $players[$n]['months'][$m] = ['gold'=>0,'dust'=>0,'truth'=>0,'jetons'=>0,'pages'=>0,'resources'=>[],'extra'=>[]];
        }
        return ['players' => $players, 'months' => $months->toArray()];
    }

    public function getLog($clan_id) {
        $c = Carbon::now();
        return TreasuryLog::select('name', DB::raw("SUM(CASE WHEN EXTRACT(MONTH FROM date) = {$c->month} AND EXTRACT(YEAR FROM date) = {$c->year} AND object = 'Монеты' THEN quantity ELSE 0 END) as coins_current_month"))->where('clan_id', $clan_id)->groupBy('name')->get();
    }

    private function getMonthlySummary($clanId) {
        $res = ['Монеты' => 'Золото', 'Кристаллизованный прах' => 'Прах', 'Кристаллы истины' => 'Истина', 'Страница из трактата «Единство клана»' => 'Страницы'];
        $rawData = TreasuryLog::selectRaw("object, TO_CHAR(date, 'YYYY-MM') as month, SUM(quantity) as total")->where('clan_id', $clanId)->whereIn('object', array_keys($res))->whereBetween('date', [now()->subMonths(6)->startOfMonth(), now()->subMonth()->endOfMonth()])->groupBy('object', 'month')->get();
        $months = collect(); for ($i = 6; $i >= 1; $i--) $months->push(now()->subMonths($i)->format('Y-m'));
        $table = [];
        foreach ($res as $db => $label) {
            $mTotals = []; foreach ($months as $m) $mTotals[$m] = round($rawData->where('object', $db)->where('month', $m)->first()->total ?? 0);
            $robust = $this->getRobustAverage(array_values($mTotals));
            $table[] = ['name' => $label, 'average' => $robust['average'], 'months' => $mTotals, 'excluded' => $robust['excluded']];
        }
        return ['table' => $table, 'months' => $months->map(fn($m) => Carbon::parse($m . '-01')->translatedFormat('F Y'))->toArray()];
    }

    private function getRobustAverage(array $v): array { $f = array_filter($v, fn($x) => $x != 0); if (count($f) === 0) return ['average' => 0, 'excluded' => []]; sort($f); $med = $this->getMedian($f); $mad = $this->getMedian(array_map(fn($x) => abs($x - $med), $f)); $c = []; $e = []; foreach ($f as $x) { if ($mad == 0 || abs($x - $med) <= 2.0 * $mad) $c[] = $x; else $e[] = $x; } return ['average' => count($c) > 0 ? round(array_sum($c) / count($c)) : round(array_sum($f) / count($f)), 'excluded' => $e]; }
    private function getMedian(array $a): float { $c = count($a); if ($c === 0) return 0; sort($a); $m = (int) floor($c / 2); return $c % 2 ? $a[$m] : ($a[$m - 1] + $a[$m]) / 2; }
}
