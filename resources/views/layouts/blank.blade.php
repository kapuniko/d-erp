<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data>
<head>
    <meta charset="utf-8" />

    <title>@yield('title', config("moonshine.title"))</title>

    <meta name="description"
          content="{{ config("moonshine.title") }}"
    />

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"
    />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('images/bloodyhard.gif') }}">



    <meta name="msapplication-TileColor" content="{{ moonshineColors()->get('body') }}">
    <meta name="theme-color" content="{{ moonshineColors()->get('body') }}">

    <x-moonshine::layout.assets>
        @vite([
            'resources/css/main.css',
            'resources/js/app.js',
        ], 'vendor/moonshine')
    </x-moonshine::layout.assets>
</head>
<body class="antialiased !bg-dark">
@yield('content')
</body>
</html>