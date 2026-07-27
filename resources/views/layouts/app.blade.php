<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Darsana Admin</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
        @livewireStyles
    </head>
    <body class="bg-slate-50 font-sans antialiased text-slate-900">
        <div class="min-h-screen w-full">
            <!-- Navigation dengan padding -->
            <div class="px-4 sm:px-6 lg:px-8 py-6">
                @include('layouts.navigation')
            </div>

            <main class="w-full">
                {{ $slot }}
                
                @livewireScripts
            </main>
        </div>
    </body>
</html>