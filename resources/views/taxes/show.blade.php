@extends('layouts.blank')

@section('title', 'Взносы на таланты: '.$clan->name)

@section('content')

    <x-moonshine::layout.grid @style('margin: 1.25rem')>
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="6" >
            <x-moonshine::layout.box>
                <p>Период сбора: <strong>{{ $special_date->format('d.m.Y') }} – {{ $special_next_date->format('d.m.Y') }}</strong></p>
                <p>Наша цель - красный талант физ.защиты.</p>
                <br>График <strong>Золото</strong> отражает общую стоимость всех собранных ресурсов, пересчитанных в золото по курсу биржи.
                <br><br> с ❤️ ваш <a target="_blank" href="https://w1.dwar.ru/user_info.php?nick=%D0%9C%D1%8C%D0%BE%D0%B4">Мьод</a>
            </x-moonshine::layout.box>
        </x-moonshine::layout.column>

        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="6">
            <x-moonshine::layout.box title="Общий прогресс (в Золотом эквиваленте)">
                <div id="chart-gold"></div>
            </x-moonshine::layout.box>
        </x-moonshine::layout.column>
    </x-moonshine::layout.grid>

    <x-moonshine::layout.grid @style('margin: 1.25rem')>
        @foreach(['truth' => 'Истина', 'dust' => 'Прах', 'pages' => 'Страницы', 'jetons' => 'Жетоны'] as $key => $label)
            <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3">
                <x-moonshine::layout.box title="{{ $label }} (цель {{ number_format($limits[$key], 0, ',', ' ') }})">
                    <div id="chart-{{ $key }}"></div>
                </x-moonshine::layout.box>
            </x-moonshine::layout.column>
        @endforeach
    </x-moonshine::layout.grid>

    {{-- ТАБЛИЦА 12 МЕСЯЦЕВ --}}
    <x-moonshine::layout.box title="Взносы за последние 12 месяцев" @style('margin: 1.25rem')>
        <div style="overflow-x: auto;">
            <table class="table" style="width: 100%; text-align: center; border-collapse: collapse;">
                <thead>
                <tr>
                    <th style="position: sticky; left: 0; background: #fff; z-index: 2;">Ник</th>
                    @foreach($monthLabels as $month) <th>{{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('M Y') }}</th> @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($playersData as $player)
                    <tr>
                        <td style="position: sticky; left: 0; background: #fff; z-index: 1; font-weight: bold; text-align: left;">{{ $player['name'] }}</td>
                        @foreach($player['months'] as $data)
                            <td style="font-size: 11px; text-align: left; white-space: nowrap;">
                                {{-- Стандартные ресурсы --}}
                                @foreach(['gold' => ['yellow', 'm_game3.gif'], 'dust' => ['red', 'data/artifacts/lab_powd_red.gif'], 'truth' => ['blue', 'data/artifacts/crystalsoftruth.gif'], 'pages' => ['gray', 'data/artifacts/talant_list1.gif'], 'jetons' => ['purple', 'data/artifacts/season_coin_04.png']] as $f => $cfg)
                                    @if($data[$f] != 0)
                                        <x-moonshine::badge color="{{ $data[$f] < 0 ? 'red' : $cfg[0] }}">
                                            <img src="https://w1.dwar.ru/images/{{ $cfg[1] }}" width="11px"> {{ number_format($data[$f], 0, ',', ' ') }}
                                        </x-moonshine::badge><br>
                                    @endif
                                @endforeach

                                {{-- Междумирье --}}
                                @if(isset($data['extra']))
                                    @foreach($data['extra'] as $exName => $exVal)
                                        @if($exVal != 0)
                                            <x-moonshine::badge color="{{ $exVal < 0 ? 'red' : 'blue' }}" style="opacity: 0.8;">
                                                <img src="https://w1.dwar.ru/images/data/artifacts/mo_dathar_item_01.gif" width="13px"> {{ number_format($exVal, 0, ',', ' ') }}
                                            </x-moonshine::badge><br>
                                        @endif
                                    @endforeach
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </x-moonshine::layout.box>

    {{-- ГЛОБАЛЬНЫЕ ЦЕЛИ (МЕЖДУМИРЬЕ) --}}
    <x-moonshine::layout.box title="Глобальные цели Междумирья" @style('margin: 1.25rem')>
        <x-moonshine::layout.grid>
            @foreach($extraLimits as $key => $limit)
                <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3">
                    <x-moonshine::layout.box title="Цель: {{ number_format($limit, 0, ',', ' ') }}">
                        <div id="chart-{{ $key }}"></div>
                    </x-moonshine::layout.box>
                </x-moonshine::layout.column>
            @endforeach
        </x-moonshine::layout.grid>
    </x-moonshine::layout.box>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        const chartData = @json($chartData);
        const extraCharts = @json($extraChartsData);
        const realTotals = @json($realTotals);
        const limits = @json($limits);
        const extraLimits = @json($extraLimits);
        const goldEquiv = @json($goldEquivalentData);

        function createChart(id, dataObj, limit, totalValue) {
            const container = document.querySelector("#chart-" + id);
            if (!container) return;

            let series = Object.values(dataObj).map(v => Number(v));
            let labels = Object.keys(dataObj);

            if (limit > 0 && totalValue < limit) {
                series.push(Number((limit - totalValue).toFixed(2)));
                labels.push('Осталось');
            }

            new ApexCharts(container, {
                series: series,
                labels: labels,
                chart: { type: 'donut', height: 300, animations: { enabled: false } },
                colors: ['#F7B924', '#3f6ad8', '#d92550', '#3ac47d', '#16aaff', '#E0E0E0'],
                legend: { position: 'bottom', fontSize: '10px' },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                total: { show: true, label: 'В казне', formatter: () => Math.round(totalValue).toLocaleString('ru-RU') }
                            }
                        }
                    }
                }
            }).render();
        }

        document.addEventListener("DOMContentLoaded", () => {
            createChart('gold', goldEquiv, limits.gold, realTotals.gold);
            Object.keys(chartData).forEach(k => createChart(k, chartData[k], limits[k], realTotals[k]));
            Object.keys(extraCharts).forEach(k => createChart(k, extraCharts[k], extraLimits[k], realTotals[k]));
        });
    </script>
@endsection
