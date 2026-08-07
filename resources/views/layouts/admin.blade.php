<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>DARSANA &mdash; AirNav</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/airnav-logo.png') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
        @if (file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @elseif (config('app.debug'))
            <div style="background:#b91c1c;color:#fff;padding:12px;font-family:sans-serif;font-size:14px">
                ⚠️ Vite manifest tidak ditemukan. Jalankan <code>npm run build</code> lalu commit folder <code>public/build/</code>.
            </div>
        @endif
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
        @livewireStyles
    </head>
    <body class="bg-slate-50 font-sans antialiased text-slate-900">
        <div class="min-h-screen">
            <div class="max-w-[1800px] mx-auto p-6 space-y-8">
                
                @include('layouts.navigation')
                
                {{-- PEMBAGIAN SIDEBAR & KONTEN --}}
                <div class="flex max-w-full mx-auto gap-8">
                    <aside class="w-64 shrink-0">
                        <div class="bg-white rounded-3xl">
                            @include('layouts.sidebar')
                        </div>
                    </aside>

                    <main class="flex-1 pb-12">
                        {{ $slot }}
                        @livewireScripts
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>