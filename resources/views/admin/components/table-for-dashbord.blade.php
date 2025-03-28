<x-moonshine::box>
    <x-moonshine::table :sticky="true">
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
                    <td>{{ $log->name }}</td>
                    <td><strong>Всего: {{ rtrim(rtrim(number_format($log->coins_total, 4, ',', ' '), '\0'), '\,') }}</strong></td>
                    <td><strong>Всего: {{ rtrim(rtrim(number_format($log->dust_total, 4, ',', ' '), '\0'), '\,') }}</strong></td>
                    <td><strong>Всего: {{ rtrim(rtrim(number_format($log->crystals_total, 4, ',', ' '), '\0'), '\,') }}</strong></td>
                    <td><strong>Всего: {{ rtrim(rtrim(number_format($log->pages_total, 4, ',', ' '), '\0'), '\,') }}</strong></td>
                    <td><strong>Всего: {{ rtrim(rtrim(number_format($log->jetons_total, 4, ',', ' '), '\0'), '\,') }}</strong></td>
                </tr>
            @endforeach
        </x-slot:tbody>
    </x-moonshine::table>
</x-moonshine::box>

