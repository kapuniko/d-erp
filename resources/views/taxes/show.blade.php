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
        <strong> Выбираем один удобный для себя вариант.</strong>
        <br><strong>ЗОЛОТОЙ</strong>
        <br>16-20ур~170з в месяц или 1700з за год*+ДОП.НАЛОГ
        <br>11-15ур~120з в месяц или 1200з за год*+ДОП.НАЛОГ
        <br>9-10ур~60з в месяц или 600з за год*+ДОП.НАЛОГ
        <br>7-8ур~45з в месяц или 450з за год*+ДОП.НАЛОГ
        <br>5-6ур~35 месяц или 350з за год*+ДОП.НАЛОГ
        <br>
        <br><strong>СТРАНИЦЫ</strong>
        <br>16-20ур~240 в месяц или 2400 за год*+ДОП.НАЛОГ
        <br>11-15ур~190 в месяц или 1900 за год*+ДОП.НАЛОГ
        <br>9-10ур~130 в месяц или 1300 за год*+ДОП.НАЛОГ
        <br>7-8ур~120 в месяц или 1200 за год*+ДОП.НАЛОГ
        <br>5-6ур~110 месяц или 1100 за год*+ДОП.НАЛОГ
        <br>
        <br><strong>КРИСТАЛЛЫ ИСТИНЫ</strong>
        <br>6-20ур~1850 в месяц или 18500 за год*+ДОП.НАЛОГ
        <br>11-15ур~1350 в месяц или 13500 за год*+ДОП.НАЛОГ
        <br>9-10ур~750 в месяц или 7500 за год*+ДОП.НАЛОГ
        <br>7-8ур~550 в месяц или 5500 за год*+ДОП.НАЛОГ
        <br>5-6ур~450 месяц или 4500 за год*+ДОП.НАЛОГ
        <br>
        <br><strong>ДОП.НАЛОГ</strong>
        <br>жетоны ВГ 5-10шт, Прах с лабиринта(если вы качаете репу там)
        <br>* при уплате налог за год, то доп налог платим каждый месяц
    </x-moonshine::layout.box>

    <x-moonshine::layout.box title="Сводная таблица по ключевым ресурсам за последние 6 месяцев (без текущего):" @style('margin: 1.25rem')>
        <table class="table" border="1" style="width: 100%; text-align: center;">
            <thead>
            <tr>
                <th>Предмет</th>
                <th>Среднее (усеченное, без пиков)</th>
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
            @endforeach
            </tbody>
        </table>
    </x-moonshine::layout.box>

@endsection
