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



<x-moonshine::layout.box title="{{ $clan->name }}: сводная таблица, в которой отражен вклад каждого участника за всё время, а так-же за текущий и 2 предыдущих месяца." @style('margin: 1.25rem')>
    <x-moonshine::table :sticky="true" @style('max-height: 78dvh !important;')>
        <x-slot:thead class="text-center">
            <th>Ник</th>
            <th>Золото</th>
            <th>Прах</th>
            <th>Истина</th>
            <th>Страницы</th>
            <th>Жетоны</th>
        </x-slot:thead>
        <x-slot:tbody>
            @foreach($logs as $log)
                <tr>
                    <td style="font-size: 20px">{{ $log->name }}</td>
                    <td><strong>Всего: {{ rtrim(rtrim(number_format($log->coins_total, 4, ',', ' '), '\0'), '\,') }}</strong><br>
                        <x-moonshine::badge color="yellow">
                            {{ \Carbon\Carbon::now()->translatedFormat('M') }}: {{ rtrim(rtrim(number_format($log->coins_current_month, 4, ',', ' '), '\0'), '\,') }}<br>
                            {{ \Carbon\Carbon::now()->subMonth()->translatedFormat('M') }}: {{ rtrim(rtrim(number_format($log->coins_previous_month, 4, ',', ' '), '\0'), '\,') }}<br>
                            {{ \Carbon\Carbon::now()->subMonth(2)->translatedFormat('M') }}: {{ rtrim(rtrim(number_format($log->coins_two_months_ago, 4, ',', ' '), '\0'), '\,') }}</x-moonshine::badge></td>
                    <td><strong>Всего: {{ rtrim(rtrim(number_format($log->dust_total, 4, ',', ' '), '\0'), '\,') }}</strong><br><x-moonshine::badge color="red">{{ now()->translatedFormat('M') }}: {{ rtrim(rtrim(number_format($log->dust_current_month, 4, ',', ' '), '\0'), '\,') }}<br>{{ now()->subMonth()->translatedFormat('M') }}: {{ rtrim(rtrim(number_format($log->dust_previous_month, 4, ',', ' '), '\0'), '\,') }}<br>{{ now()->subMonth(2)->translatedFormat('M') }}: {{ rtrim(rtrim(number_format($log->dust_two_months_ago, 4, ',', ' '), '\0'), '\,') }}</x-moonshine::badge></td>
                    <td><strong>Всего: {{ rtrim(rtrim(number_format($log->crystals_total, 4, ',', ' '), '\0'), '\,') }}</strong><br><x-moonshine::badge color="blue">{{ now()->translatedFormat('M') }}: {{ rtrim(rtrim(number_format($log->crystals_current_month, 4, ',', ' '), '\0'), '\,') }}<br>{{ now()->subMonth()->translatedFormat('M') }}: {{ rtrim(rtrim(number_format($log->crystals_previous_month, 4, ',', ' '), '\0'), '\,') }}<br>{{ now()->subMonth(2)->translatedFormat('M') }}: {{ rtrim(rtrim(number_format($log->crystals_two_months_ago, 4, ',', ' '), '\0'), '\,') }}</x-moonshine::badge></td>
                    <td><strong>Всего: {{ rtrim(rtrim(number_format($log->pages_total, 4, ',', ' '), '\0'), '\,') }}</strong><br><x-moonshine::badge color="gray">{{ now()->translatedFormat('M') }}: {{ rtrim(rtrim(number_format($log->pages_current_month, 4, ',', ' '), '\0'), '\,') }}<br>{{ now()->subMonth()->translatedFormat('M') }}: {{ rtrim(rtrim(number_format($log->pages_previous_month, 4, ',', ' '), '\0'), '\,') }}<br>{{ now()->subMonth(2)->translatedFormat('M') }}: {{ rtrim(rtrim(number_format($log->pages_two_months_ago, 4, ',', ' '), '\0'), '\,') }}</x-moonshine::badge></td>
                    <td><strong>Всего: {{ rtrim(rtrim(number_format($log->jetons_total, 4, ',', ' '), '\0'), '\,') }}</strong><br><x-moonshine::badge color="purple">{{ now()->translatedFormat('M') }}: {{ rtrim(rtrim(number_format($log->jetons_current_month, 4, ',', ' '), '\0'), '\,') }}<br>{{ now()->subMonth()->translatedFormat('M') }}: {{ rtrim(rtrim(number_format($log->jetons_previous_month, 4, ',', ' '), '\0'), '\,') }}<br>{{ now()->subMonth(2)->translatedFormat('M') }}: {{ rtrim(rtrim(number_format($log->jetons_two_months_ago, 4, ',', ' '), '\0'), '\,') }}</x-moonshine::badge></td>
                </tr>
            @endforeach
        </x-slot:tbody>
    </x-moonshine::table>

</x-moonshine::layout.box >

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
                </td>>
            </tr>
            </tbody>
        </table>
    </x-moonshine::layout.box>

@endsection
