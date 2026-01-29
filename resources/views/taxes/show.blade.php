@extends('layouts.blank')

@section('title', 'Взносы на таланты: '.$clan->name)

@section('content')

    <x-moonshine::layout.grid @style('margin: 1.25rem')>
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="6" >
            <x-moonshine::layout.box>
                <p>Период сбора: <strong>{{ $special_date->format('d.m.Y') }} – {{ $special_next_date->format('d.m.Y') }}</strong></p>
                <p>Наша цель - красный талант физ.защиты.</p>
                <br>График <strong>Золото</strong> отражает общую стоимость всех собранных ресурсов, пересчитанных в золото по курсу биржи.
                <br>График <strong>Страницы</strong> включает в себя также взносы ресурсов: Огневик (3 к 1), Горецвет, Инкарнум и Центридо (2 к 1).
                <br><br>На момент покраски предыдущего таланта, в казне оставались ресурсы:
                <br>
                <img src="https://w1.dwar.ru/images/data/artifacts/talant_list1.gif" width="15px" height="15px" style="margin: 0; display: inline" alt="Страницы" title="Страницы"> 2247 шт.,
                <img src="https://w1.dwar.ru/images/data/artifacts/crystalsoftruth.gif" width="15px" height="15px" style="margin: 0; display: inline" alt="Истина" title="Истина"> 370 шт.,
                <img src="https://w1.dwar.ru/images/data/artifacts/lab_powd_red.gif" width="15px" height="15px" style="margin: 0; display: inline" alt="Прах" title="Прах"> 2573 шт.,
                <img src="https://w1.dwar.ru/images/data/artifacts/season_coin_04.png" width="15px" height="15px" style="margin: 0; display: inline" alt="Жетоны" title="Жетоны"> 1394 шт.
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
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3">
            <x-moonshine::layout.box title="Истина (цель {{ number_format($limits['truth'], 0, ',', ' ') }})">
                <div id="chart-truth"></div>
            </x-moonshine::layout.box>
        </x-moonshine::layout.column>
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3">
            <x-moonshine::layout.box title="Прах (цель {{ number_format($limits['dust'], 0, ',', ' ') }})">
                <div id="chart-dust"></div>
            </x-moonshine::layout.box>
        </x-moonshine::layout.column>
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3">
            <x-moonshine::layout.box title="Страницы (цель {{ number_format($limits['pages'], 0, ',', ' ') }})">
                <div id="chart-pages"></div>
            </x-moonshine::layout.box>
        </x-moonshine::layout.column>
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3">
            <x-moonshine::layout.box title="Жетоны (цель {{ number_format($limits['jetons'], 0, ',', ' ') }})">
                <div id="chart-jetons"></div>
            </x-moonshine::layout.box>
        </x-moonshine::layout.column>
    </x-moonshine::layout.grid>

    {{-- Таблица 12 месяцев --}}
    <x-moonshine::layout.box title="Взносы за последние 12 месяцев" @style('margin: 1.25rem')>
        <div style="overflow-x: auto; max-width: 100%;">
            <table class="table" style="border-collapse: collapse; width: max-content; min-width: 100%; text-align: center;">
                <thead>
                <tr>
                    <th style="position: sticky; left: 0; background: #fff; z-index: 2;">Ник</th>
                    @foreach($monthLabels as $month)
                        <th style="white-space: nowrap;">{{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('M Y') }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($playersData as $player)
                    @php
                        $resIcons = [
                            'Огневик'  => 'https://w1.dwar.ru/images/data/artifacts/ognevikgreen1009.gif',
                            'Горецвет' => 'https://w1.dwar.ru/images/data/artifacts/feigoblue1009.gif',
                            'Инкарнум' => 'https://w1.dwar.ru/images/data/artifacts/eldserdce_090702.gif',
                            'Центридо' => 'https://w1.dwar.ru/images/data/artifacts/krofserdce_090702.gif',
                            'Браслеты джиннов' => 'https://w1.dwar.ru/images/data/artifacts/lib_jinn_brac_01.gif',
                            'Мо-датхар альвы благонравной' => 'https://w1.dwar.ru/images/data/artifacts/mo_dathar_item_01.gif',
                            'Мо-датхар нурида' => 'https://w1.dwar.ru/images/data/artifacts/mo_dathar_item_01.gif',
                            'Мо-датхар золтой шамсы' => 'https://w1.dwar.ru/images/data/artifacts/mo_dathar_item_01.gif',
                            'Мо-датхар чёрного лотоса' => 'https://w1.dwar.ru/images/data/artifacts/mo_dathar_item_02.gif',
                            'Мо-датхар шахифрита' => 'https://w1.dwar.ru/images/data/artifacts/mo_dathar_item_02.gif',
                            'Мо-датхар мистрасского рыбозмея' => 'https://w1.dwar.ru/images/data/artifacts/mo_dathar_item_02.gif',
                            'Мо-датхар аракша неугасимого' => 'https://w1.dwar.ru/images/data/artifacts/mo_dathar_item_03.gif',
                            'Мо-датхар замридина' => 'https://w1.dwar.ru/images/data/artifacts/mo_dathar_item_03.gif',
                            'Мо-датхар акдуфа-многонога' => 'https://w1.dwar.ru/images/data/artifacts/mo_dathar_item_03.gif',
                        ];
                    @endphp
                    <tr>
                        <td style="position: sticky; left: 0; background: #fff; z-index: 1; font-weight: bold; text-align: left;">{{ $player['name'] }}</td>
                        @foreach($player['months'] as $data)
                            <td style="font-size: 11px; text-align: left; white-space: nowrap; line-height: 1.4;">
                                @if(!empty($data['gold']))<x-moonshine::badge color="yellow"><img src="https://w1.dwar.ru/images/m_game3.gif" width="11px" height="11px" style="display: inline" alt="Золото"> {{ number_format($data['gold'], 0, ',', ' ') }}</x-moonshine::badge><br>@endif
                                @if(!empty($data['dust']))<x-moonshine::badge color="red"><img src="https://w1.dwar.ru/images/data/artifacts/lab_powd_red.gif" width="15px" height="15px" style="display: inline" alt="Прах" title="Прах"> {{ number_format($data['dust'], 0, ',', ' ') }}</x-moonshine::badge><br>@endif
                                @if(!empty($data['truth']))<x-moonshine::badge color="blue"><img src="https://w1.dwar.ru/images/data/artifacts/crystalsoftruth.gif" width="15px" height="15px" style="display: inline" alt="Истина" title="Истина"> {{ number_format($data['truth'], 0, ',', ' ') }}</x-moonshine::badge><br>@endif
                                @if(!empty($data['pages']))<x-moonshine::badge color="gray"><img src="https://w1.dwar.ru/images/data/artifacts/talant_list1.gif" width="15px" height="15px" style="display: inline" alt="Страницы" title="Страницы"> {{ number_format($data['pages'], 0, ',', ' ') }}</x-moonshine::badge><br>@endif
                                @if(!empty($data['jetons']))<x-moonshine::badge color="purple"><img src="https://w1.dwar.ru/images/data/artifacts/season_coin_04.png" width="15px" height="15px" style="display: inline" alt="Жетоны" title="Жетоны"> {{ number_format($data['jetons'], 0, ',', ' ') }}</x-moonshine::badge><br>@endif

                                {{-- Ресурсы для страниц --}}
                                @if(isset($data['resources']))
                                    @foreach($data['resources'] as $resName => $resCount)
                                        @if($resCount > 0)
                                            <x-moonshine::badge color="gray">
                                                <img src="{{ $resIcons[$resName] ?? '' }}" width="15px" height="15px" style="display: inline; margin-right: 2px;" alt="{{ $resName }}" title="{{ $resName }}">
                                                {{ number_format($resCount, 0, ',', ' ') }}
                                            </x-moonshine::badge><br>
                                        @endif
                                    @endforeach
                                @endif

                                {{-- Новые ресурсы Междумирья --}}
                                @if(isset($data['extra']))
                                    @foreach($data['extra'] as $exName => $exCount)
                                        @if($exCount > 0)
                                            <x-moonshine::badge color="blue" style="opacity: 0.9;">
                                                <img src="{{ $resIcons[$exName] ?? '' }}" width="15px" height="15px" style="display: inline; margin-right: 2px;" alt="{{ $exName }}" title="{{ $exName }}">
                                                {{ number_format($exCount, 0, ',', ' ') }}
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

    {{-- ГЛОБАЛЬНЫЕ ЦЕЛИ (МИСТРАС) --}}
    <x-moonshine::layout.box title="Таланты Мистрас" @style('margin: 1.25rem')>
        <x-moonshine::layout.grid>
            @php
                $extraNames = [
                    'brasleti_jinov' => 'Браслеты джиннов',
                    'mo_trava_zel'   => 'Зел. Мо-трава',
                    'mo_kamen_zel'   => 'Зел. Мо-камни',
                    'mo_riba_zel'    => 'Зел. Мо-рыба',
                    'mo_trava_sin'   => 'Син. Мо-трава',
                    'mo_kamen_sin'   => 'Син. Мо-камни',
                    'mo_riba_sin'    => 'Син. Мо-рыба',
                    'mo_trava_fiol'  => 'Фиол. Мо-трава',
                    'mo_kamen_fiol'  => 'Фиол. Мо-камни',
                    'mo_riba_fiol'   => 'Фиол. Мо-рыба',
                ];
            @endphp

            @foreach($extraNames as $key => $label)
                <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3">
                    <x-moonshine::layout.box title="{{ $label }} (цель {{ number_format($extraLimits[$key], 0, ',', ' ') }})">
                        <div id="chart-{{ $key }}"></div>
                    </x-moonshine::layout.box>
                </x-moonshine::layout.column>
            @endforeach
        </x-moonshine::layout.grid>
    </x-moonshine::layout.box>

    <x-moonshine::layout.box title="Сводная таблица (6 мес):" @style('margin: 1.25rem')>
        <table class="table" border="1" style="width: 100%; text-align: center;">
            <thead>
            <tr>
                <th>Предмет</th>
                <th>Среднее (без пиков)</th>
                @foreach($summaryMonths as $month) <th>{{ $month }}</th> @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($summaryTable as $row)
                <tr>
                    <td><strong>{{ $row['name'] }}</strong></td>
                    <td><strong>{{ number_format($row['average'], 0, ',', ' ') }}</strong></td>
                    @foreach($row['months'] as $val) <td>{{ number_format($val, 0, ',', ' ') }}</td> @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-moonshine::layout.box>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        const chartDataRaw = @json($chartData);
        const goldEquiv = @json($goldEquivalentData);
        const limits = @json($limits);
        const extraChartsData = @json($extraChartsData);
        const extraLimits = @json($extraLimits);
        const baseColors = ['#F7B924', '#3f6ad8', '#d92550', '#3ac47d', '#16aaff', '#f7b924', '#6610f2'];

        function createChart(id, label, dataObj, limit) {
            const container = document.querySelector("#chart-" + id);
            if (!container || !dataObj) return;

            let labels = Object.keys(dataObj);
            let series = Object.values(dataObj).map(v => Number(v));
            let total = series.reduce((a, b) => a + b, 0);
            let colors = [...baseColors];

            if (limit && limit > 0 && total < limit) {
                series.push(Number((limit - total).toFixed(2)));
                labels.push('Осталось собрать');
                colors.push('#E0E0E0');
            }

            const options = {
                series: series,
                labels: labels,
                chart: { type: 'donut', height: 350, animations: { enabled: false } },
                colors: colors,
                legend: { position: 'bottom', fontSize: '10px' },
                dataLabels: {
                    enabled: true,
                    formatter: (val, opt) => Number(opt.w.globals.series[opt.seriesIndex]).toLocaleString('ru-RU')
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Собрано',
                                    formatter: () => total.toLocaleString('ru-RU')
                                }
                            }
                        }
                    }
                },
                tooltip: { y: { formatter: (v) => v.toLocaleString('ru-RU') } }
            };

            new ApexCharts(container, options).render();
        }

        document.addEventListener("DOMContentLoaded", () => {
            // Таланты
            createChart('gold', 'Золото', goldEquiv, limits.gold);
            createChart('truth', 'Истина', chartDataRaw.truth, limits.truth);
            createChart('dust', 'Прах', chartDataRaw.dust, limits.dust);
            createChart('pages', 'Страницы', chartDataRaw.pages, limits.pages);
            createChart('jetons', 'Жетоны', chartDataRaw.jetons, limits.jetons);

            // Междумирье
            Object.keys(extraChartsData).forEach(key => {
                createChart(key, '', extraChartsData[key], extraLimits[key]);
            });
        });
    </script>
@endsection
