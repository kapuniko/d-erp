<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Привязка Telegram</h2>

                        @if (session('success'))
                            <div class="alert alert-success mt-2">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger mt-2">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if (Auth::user()->telegram_id)
                            <p class="mt-2 text-gray-900 dark:text-gray-100">
                                Ваш Telegram аккаунт привязан:
                                @if (Auth::user()->telegram_username)
                                    @ {{ Auth::user()->telegram_username }}
                                @else
                                    ID: {{ Auth::user()->telegram_id }}
                                @endif
                                @if (Auth::user()->telegram_photo_url)
                                    <img src="{{ Auth::user()->telegram_photo_url }}" alt="Фото профиля Telegram" class="inline-block w-8 h-8 rounded-full ml-2">
                                @endif
                            </p>
                            {{-- Кнопка "Отвязать Telegram" --}}
                            {{-- <form action="..." method="POST"> ... </form> --}}

                        @else
                            <p class="mt-2 text-gray-900 dark:text-gray-100">
                                Вы можете привязать ваш Telegram аккаунт для быстрого входа.
                            </p>
                            <a href="{{ route('profile.link.telegram') }}"
                               class="inline-flex items-center px-4 py-2 mt-4 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Привязать Telegram
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>


        </div>
    </div>
</x-app-layout>

