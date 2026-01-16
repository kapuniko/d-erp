@extends('layouts.blank')

@section('title', 'Взносы на таланты: '.$clan->name)

@section('content')

    <x-moonshine::layout.grid @style('margin: 1.25rem')>
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="6" >
            <x-moonshine::layout.box>
                Спасибо всем за поддержку нашего общего дела! ❤️
                <br><br>На графиках ниже отражены взносы за период:
                <x-moonshine::badge color="purple">{{ $special_date->format('d.m.Y') }} — {{ $special_next_date->format('d.m.Y') }}</x-moonshine::badge>
                <br><br>Для ресурсов с установленным лимитом серый сектор диаграммы показывает, сколько еще осталось собрать до цели.
                <br><br> с ❤️ ваш <a target="_blank" href="https://w1.dwar.ru/user_info.php?nick=%D0%9C%D1%8C%D0%BE%D0%B4">Мьод</a>
            </x-moonshine::layout.box>
        </x-moonshine::layout.column>

        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="6">
            <x-moonshine::layout.box title="Золото (Без лимита)">
                <div id="chart-gold"></div>
            </x-moonshine::layout.box>
        </x-moonshine::layout.column>
    </x-moonshine::layout.grid>

    <x-moonshine::layout.grid @style('margin: 1.25rem')>
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3">
            <x-moonshine::layout.box title="Истина (макс. {{ number_format($limits['truth'], 0, ',', ' ') }})">
                <div id="chart-truth"></div>
            </x-moonshine::layout.box>
        </x-moonshine::layout.column>
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3">
            <x-moonshine::layout.box title="Прах (макс. {{ number_format($limits['dust'], 0, ',', ' ') }})">
                <div id="chart-dust"></div>
            </x-moonshine::layout.box>
        </x-moonshine::layout.column>
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3">
            <x-moonshine::layout.box title="Страницы (макс. {{ number_format($limits['pages'], 0, ',', ' ') }})">
                <div id="chart-pages"></div>
            </x-moonshine::layout.box>
        </x-moonshine::layout.column>
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3">
            <x-moonshine::layout.box title="Жетоны (макс. {{ number_format($limits['jetons'], 0, ',', ' ') }})">
                <div id="chart-jetons"></div>
            </x-moonshine::layout.box>
        </x-moonshine::layout.column>
    </x-moonshine::layout.grid>

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
                    <tr>
                        <td style="position: sticky; left: 0; background: #fff; z-index: 1; font-weight: bold; text-align: left;">{{ $player['name'] }}</td>
                        @foreach($player['months'] as $data)
                            <td style="font-size: 11px; text-align: left; white-space: nowrap; line-height: 1.4;">
                                @if(!empty($data['gold']))<x-moonshine::badge color="yellow">З: {{ number_format($data['gold'], 0, ',', ' ') }}</x-moonshine::badge><br>@endif
                                @if(!empty($data['dust']))<x-moonshine::badge color="red">П: {{ number_format($data['dust'], 0, ',', ' ') }}</x-moonshine::badge><br>@endif
                                @if(!empty($data['truth']))<x-moonshine::badge color="blue">И: {{ number_format($data['truth'], 0, ',', ' ') }}</x-moonshine::badge><br>@endif
                                @if(!empty($data['pages']))<x-moonshine::badge color="gray">С: {{ number_format($data['pages'], 0, ',', ' ') }}</x-moonshine::badge><br>@endif
                                @if(!empty($data['jetons']))<x-moonshine::badge color="purple">Ж: {{ number_format($data['jetons'], 0, ',', ' ') }}</x-moonshine::badge><br>@endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </x-moonshine::layout.box>

    <x-moonshine::layout.box title="Сводная таблица по ресурсам (6 мес):" @style('margin: 1.25rem')>
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
        const chartData = @json($chartData);
        const limits = @json($limits);
        const baseColors = ['#F7B924', '#3f6ad8', '#d92550', '#3ac47d', '#16aaff', '#f7b924', '#6610f2'];

        function createChart(id, label, data, limit) {
            let series = Object.values(data);
            let labels = Object.keys(data);
            let total = series.reduce((a, b) => a + b, 0);
            let colors = [...baseColors];

            if (limit && total < limit) {
                series.push(limit - total);
                labels.push('Осталось собрать');
                colors.push('#E0E0E0'); // Серый для остатка
            }

            const options = {
                series: series,
                labels: labels,
                chart: { type: 'donut', height: 300 },
                colors: colors,
                legend: { position: 'bottom', fontSize: '12px' },
                dataLabels: { enabled: true, formatter: (val, opt) => opt.w.globals.series[opt.seriesIndex].toLocaleString() },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Собрано',
                                    formatter: () => total.toLocaleString()
                                }
                            }
                        }
                    }
                },
                tooltip: { y: { formatter: (v) => v.toLocaleString() } }
            };

            new ApexCharts(document.querySelector("#chart-" + id), options).render();
        }

        document.addEventListener("DOMContentLoaded", () => {
            createChart('gold', 'Золото', chartData.gold, limits.gold);
            createChart('truth', 'Истина', chartData.truth, limits.truth);
            createChart('dust', 'Прах', chartData.dust, limits.dust);
            createChart('pages', 'Страницы', chartData.pages, limits.pages);
            createChart('jetons', 'Жетоны', chartData.jetons, limits.jetons);
        });
    </script>
@endsection
