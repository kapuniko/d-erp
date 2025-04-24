<form wire:submit.prevent="store">

    <h2 class="text-xl font-semibold mb-4">Добавить новый артефакт</h2>

    {{-- Поле для имени --}}
    <div class="mb-4">
        <label for="artefactName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Название:</label>
        <input type="text" id="artefactName" wire:model="name" required
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    {{-- Поле для типа (селект) --}}
    <div class="mb-4">
        <label for="artefactType" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Тип:</label>
        <select id="artefactType" wire:model="type" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="buf">Каст (всё что можно кастануть)</option>
            <option value="pot">Банка (всё что ставится в карманы)</option>
        </select>
        @error('type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    {{-- Поле для стоимости --}}
    <div class="mb-4">
        <label for="artefactPrice" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Стоимость (в золоте, в коробочке затраты на все штуки будут магическим образом складываться):</label>
        <input type="text" id="artefactPrice" wire:model="price" step="0.01"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    {{-- <-- ИЗМЕНЕНО: Поле для ввода URL изображения --> --}}
    <div class="mb-4">
        <label for="artefactImage" class="block text-sm font-medium text-gray-700 dark:text-gray-300">URL изображения (копируем из кода игры например (без кавычек) "images/data/artifacts/blessingskyorange.gif"):</label>
        <input type="text" id="artefactImage" wire:model="image"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('image') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror


    </div>

    {{-- Кнопка сохранения --}}
    <div class="text-center mb-1">
        <button type="submit"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Сохранить артефакт
        </button>
    </div>
    <span class="w-full text-center text-xs">слава роботам</span>
</form>
