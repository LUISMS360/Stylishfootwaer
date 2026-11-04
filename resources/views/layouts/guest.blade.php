
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">

    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased">

    <div class="page page-center">
        <div class="container container-tight py-4">

          

            {{-- Authentication Card --}}
            <div class="card card-md">
                <div class="card-body">
                    {{ $slot }}
                </div>
            </div>

            {{-- Footer --}}
            <div class="text-center text-secondary mt-3">
                <small>
                    &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}.
                    Todos los derechos reservados.
                </small>
            </div>

        </div>
    </div>

</body>
</html>
