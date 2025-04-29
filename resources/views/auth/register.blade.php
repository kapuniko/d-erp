<x-guest-layout>
    <style>
        .captcha {
            display: flex;
            flex-direction: column;
            width: 240px;
            padding: 50px;
            margin: 30px auto;
            gap: 20px;
            box-shadow: 0px 0px 80px 0px rgba(158, 158, 158, 0.4);
            border-radius: 10px;
            text-align: center;
            color: #333333;
        }

        .labirint {
            border: 20px solid #dbd8d8;
            border-radius: 10px;
            background-color: #dbd8d8;
        }


        .submitButton:active {
            background: linear-gradient(65deg, #f600aa 0%, #9600fc 100%);
            filter: drop-shadow(0 0 4px #9600fc);
        }

        .submitButton:disabled {
            background: linear-gradient(65deg, rgb(255, 0, 0) 0%, rgb(255, 0, 46) 100%);
            filter: drop-shadow(0 0 8px crimson);
            cursor: not-allowed;
        }


        .controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .controls div {
            border: 1px solid white;
            border-radius: 50%;
            color: white;
            font-size: 28px;
            padding: 3px;
            cursor: pointer;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }

        .controls div:active {
            filter: drop-shadow(0 0 5px #9600fc);
        }

        @keyframes collapse{
            0%{
                display: flex;
                opacity: 1;
                height: 64px;
                margin-top: 0px;
                gap: 27px;
            }
            100%{
                display: none;
                opacity: 0;
                height: 0px;
                margin-top: -20px;
                gap: 0px;
            }
        }

        .collapsed{
            display: none;
            height: 0px;
            margin-top: -20px;
            gap:0px;
            animation:collapse .4s;
        }
    </style>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Имя (Ник)')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Пароль')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Повторите пароль')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>
        <div class="captcha">
            <div style="text-align: center; color: white"><b>Вы точно не робот?</b><br>Отведите <span id="cat">😺</span> к <span id="fish">🐟</span></div>

            <canvas class="labirint" width="200" height="200"></canvas>
            <div class="controls">
                <div id="up"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75 12 3m0 0 3.75 3.75M12 3v18" />
                    </svg>
                </div>
                <div id="left"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15.75 3 12m0 0 3.75-3.75M3 12h18" />
                    </svg>
                </div>
                <div id="right"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                    </svg>
                </div>
                <div id="down"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25 12 21m0 0-3.75-3.75M12 21V3" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4 submitButton">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        let emodziSet = [
            ["😺","🐟","👹","💘", "#7fff6a"],
            ["🦄","⭐","👹","✨", "#ECB6FA"],
            ["🦕","🌳","👹","💘", "#7fff6a"],
            ["🐺","🍗","🐻","💘", "#7fff6a"],
        ];

        let emodzi = emodziSet[0]; //control the Emodzi Set

        let submitButton = document.querySelector(".submitButton");

        let captchaComplited = false;

        function captchaCheck() {
            if (captchaComplited === false) {
                submitButton.disabled = true;
            } else {
                submitButton.disabled = false;
                document.querySelector(".controls").classList.add("collapsed");
            }
        }
        captchaCheck();

        let container = document.querySelector(".labirint");
        let context = container.getContext("2d");
        context.imageSmoothingEnabled = false;

        let a = 200; //canvas size
        container.width = a;
        container.height = a;

        let pixel = a / 5; //pixel size

        function drawMaze(color) {
            context.fillStyle = color;
            context.fillRect(0, 0, pixel, 4 * pixel);
            context.fillRect(pixel, 3 * pixel, pixel, pixel);
            context.fillRect(2 * pixel, pixel, pixel, 4 * pixel);
            context.fillRect(3 * pixel, 4 * pixel, 2 * pixel, pixel);
            context.fillRect(4 * pixel, 0, pixel, pixel);
            context.fillRect(3 * pixel, pixel, 2 * pixel, pixel);
        }

        drawMaze("white");

        let cat = emodzi[0];
        context.font = "32px 'Noto Color Emoji', sans-serif";
        context.fillText(cat, 0, 30);

        document.getElementById('cat').innerHTML = cat;

        let fish = emodzi[1];
        context.fillText(fish, 160, 190);

        document.getElementById('fish').innerHTML = fish;

        let devil = emodzi[2];
        context.fillText(devil, 160, 30);

        let heart = emodzi[3];

        // Получаем координаты котика и рыбки
        let catX = 0;
        let catY = 30;

        let fishX = 160;
        let fishY = 190;

        let devilX = 160;
        let devilY = 30;

        // Рисуем котика и рыбку
        function drawCat() {
            context.clearRect(0, 0, container.width, container.height);
            drawMaze("white"); // Рисуем лабиринт
            context.fillText(cat, catX, catY);
            context.fillText(fish, fishX, fishY);
            context.fillText(devil, devilX, devilY);
        }

        // Проверка на достижение рыбки
        function checkWin() {
            if (catX === fishX && catY === fishY) {
                captchaComplited = true;
                captchaCheck();

                context.clearRect(0, 0, container.width, container.height);
                drawMaze(emodzi[4]); // Рисуем лабиринт
                context.fillText(heart, fishX, fishY); // и сердечко
            }
        }

        // Функция обработки нажатия клавиш
        function moveCat(e) {
            if (!captchaComplited) {
                switch (e.keyCode) {
                    case 37: // Влево
                        if (
                            catX - pixel >= 0 &&
                            context.getImageData(catX - pixel, catY, 1, 1).data[3] !== 0
                        ) {
                            catX -= pixel;
                        }
                        break;
                    case 38: // Вверх
                        if (
                            catY - pixel >= 0 &&
                            context.getImageData(catX, catY - pixel, 1, 1).data[3] !== 0
                        ) {
                            catY -= pixel;
                        }
                        break;
                    case 39: // Вправо
                        if (
                            catX + pixel <= container.width - pixel &&
                            context.getImageData(catX + pixel, catY, 1, 1).data[3] !== 0
                        ) {
                            catX += pixel;
                        }
                        break;
                    case 40: // Вниз
                        if (
                            catY + pixel <= container.height &&
                            context.getImageData(catX, catY + pixel, 1, 1).data[3] !== 0
                        ) {
                            catY += pixel;
                        }
                        break;
                }
                drawCat(); // Перерисовываем котика
                checkWin(); // Проверяем, достиг ли он рыбки
            }
        }

        // Добавляем обработчик события нажатия клавиш
        document.addEventListener("keydown", moveCat);

        document.getElementById("up").addEventListener("click", function () {
            var event = new KeyboardEvent("keydown", { keyCode: 38 });
            document.dispatchEvent(event);
        });

        document.getElementById("left").addEventListener("click", function () {
            var event = new KeyboardEvent("keydown", { keyCode: 37 });
            document.dispatchEvent(event);
        });

        document.getElementById("right").addEventListener("click", function () {
            var event = new KeyboardEvent("keydown", { keyCode: 39 });
            document.dispatchEvent(event);
        });

        document.getElementById("down").addEventListener("click", function () {
            var event = new KeyboardEvent("keydown", { keyCode: 40 });
            document.dispatchEvent(event);
        });
    </script>
</x-guest-layout>
