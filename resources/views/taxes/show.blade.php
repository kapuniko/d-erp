@extends('layouts.blank')

@section('title', 'Взносы на таланты: '.$clan->name)

@section('content')

    <x-moonshine::layout.grid @style('margin: 1.25rem')>
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="6" >
            <x-moonshine::layout.box>
                Спасибо всем, кто понимает, что наши клан-таланты качаются не сами собой. Что это наше, самое большое, общее дело.
                <br><br>Спасибо всем, кто выделяет на общее дело одну, а иногда и две (и три) радуги в месяце!
                <br><br>В диаграммах приведено количество поступивших в казну ресурсов, для прокачки клановых талантов, за <strong>{{ \Carbon\Carbon::now()->translatedFormat('F') }}</strong>.
                <br><br>В будущем тут появится "прогресс-бар", отражающий - сколько нам осталось до апа намеченного таланта.
                <br><br> с ❤️ ваш <a target="_blank" href="https://w1.dwar.ru/user_info.php?nick=%D0%9C%D1%8C%D0%BE%D0%B4">Мьод</a>
            </x-moonshine::layout.box>

        </x-moonshine::layout.column>
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="6">
            {!! $donutChart_coins->render() !!}
        </x-moonshine::layout.column>
    </x-moonshine::layout.grid>

    <x-moonshine::layout.grid @style('margin: 1.25rem')>
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3">
            {!! $donutChart_crystals->render() !!}
        </x-moonshine::layout.column>
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3">
            {!! $donutChart_dust->render() !!}
        </x-moonshine::layout.column>
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3">
            {!! $donutChart_pages->render() !!}
        </x-moonshine::layout.column>
        <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3">
            {!! $donutChart_jetons->render() !!}
        </x-moonshine::layout.column>
    </x-moonshine::layout.grid>



    <x-moonshine::layout.box title="Взносы за последние 12 месяцев" @style('margin: 1.25rem')>
        <div style="overflow-x: auto; max-width: 100%;">
            <table class="table" border="1" style="border-collapse: collapse; width: max-content; min-width: 100%; text-align: center;">
                <thead>
                <tr>
                    <th style="position: sticky; left: 0; background: #fff; z-index: 2;">
                        Ник
                    </th>
                    @foreach($monthLabels as $month)
                        <th style="white-space: nowrap;">
                            {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('M Y') }}
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($playersData as $player)
                    <tr>
                        <td style="position: sticky; left: 0; background: #fff; z-index: 1; font-weight: bold; text-align: left;">
                            {{ $player['name'] }}
                        </td>
                        @foreach($player['months'] as $data)
                            <td style="font-size: 12px; text-align: left; white-space: nowrap;">
                                @if(!empty($data['gold']))
                                    <x-moonshine::badge color="yellow">Золото: {{ number_format($data['gold'], 0, ',', ' ') }}</x-moonshine::badge><br>
                                @endif
                                @if(!empty($data['dust']))
                                        <x-moonshine::badge color="red">Прах: {{ number_format($data['dust'], 0, ',', ' ') }}</x-moonshine::badge><br>
                                @endif
                                @if(!empty($data['truth']))
                                        <x-moonshine::badge color="blue">Истина: {{ number_format($data['truth'], 0, ',', ' ') }}</x-moonshine::badge><br>
                                @endif
                                    @if(!empty($data['pages']))
                                        <x-moonshine::badge color="gray">Страницы: {{ number_format($data['pages'], 0, ',', ' ') }}</x-moonshine::badge>
                                    @endif
                                @if(!empty($data['jetons']))
                                        <x-moonshine::badge color="purple">Жетоны: {{ number_format($data['jetons'], 0, ',', ' ') }}</x-moonshine::badge><br>
                                @endif

                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </x-moonshine::layout.box>


    <x-moonshine::layout.box title="Размеры взносов:" @style('margin: 1.25rem')>
        <br><strong>6-14 лвл:</strong> 200з (или 3000 истины / 300 страниц)
        <br><strong>15-18 лвл:</strong> 300з (или 5000 истины / 500 страниц)
        <br><strong>19-20 лвл:</strong> 400з (или 6666 истины / 666 страниц)
    </x-moonshine::layout.box>

    <x-moonshine::layout.box title="Сводная таблица по ключевым ресурсам за последние 6 месяцев (без текущего):" @style('margin: 1.25rem')>
        <table class="table" border="1" style="width: 100%; text-align: center;">
            <thead>
            <tr>
                <th>Предмет</th>
                <th>Среднее<br>(медианное*,<br>без пиков)</th>
                @foreach($summaryMonths as $month)
                    <th>{{ $month }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($summaryTable as $row)
                <tr>
                    <td><strong>{{ $row['name'] }}</strong></td>
                    <td><strong>{{ number_format($row['average'], 0, ',', ' ') }}</strong></td>
                    @foreach($row['months'] as $val)
                        <td>{{ number_format($val, 0, ',', ' ') }}</td>
                    @endforeach
                </tr>

                @if(!empty($row['excluded']))
                    <tr>
                        <td colspan="{{ count($row['months']) + 2 }}" style="text-align: left; font-size: 12px; color: #666;">
                            <em>Исключено из расчёта: {{ implode(', ', array_map(fn($v) => number_format($v, 0, ',', ' '), $row['excluded'])) }}</em>
                        </td>
                    </tr>
                @endif
            @endforeach

            <tr>
                <td colspan="8" style="text-align: left; font-size: 12px; color: #666;">
                Суть метода:<br>
                1. Находим медиану значений.<br>

                2. Считаем отклонения каждого значения от медианы.<br>

                3. Находим медиану отклонений (MAD).<br>

                4. Оставляем только те значения, чьё отклонение от медианы не превышает заданный порог (например, 2×MAD).<br>

                5. Считаем обычное среднее по оставшимся значениям.<br>

                Такой подход позволяет автоматически исключать один или несколько "выпрыгивающих" пиков, даже если они не самые большие.<br>
                </td>
            </tr>
            </tbody>
        </table>
    </x-moonshine::layout.box>

@endsection
