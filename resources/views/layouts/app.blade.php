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
        <div class="min-h-screen">
            <div class="max-w-[1800px] mx-auto p-6 space-y-8">
                
                @include('layouts.navigation')

                <main>
                    {{ $slot }}
                    
                    @livewireScripts
                </main>
            </div>
        </div>
    </body>
</html>