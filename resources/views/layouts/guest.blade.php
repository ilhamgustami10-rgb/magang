<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Dashboard Monitoring – AirNav Surabaya</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            * { font-family: 'Inter', sans-serif; }

            body {
                margin: 0;
                padding: 0;
                min-height: 100vh;
                background-image: url('/images/airnav_bg.png');
                background-size: cover;
                background-position: center;
                background-attachment: fixed;
            }

            .bg-overlay {
                position: fixed;
                inset: 0;
                background: linear-gradient(
                    135deg,
                    rgba(3, 7, 30, 0.80) 0%,
                    rgba(8, 18, 60, 0.70) 40%,
                    rgba(3, 10, 30, 0.85) 100%
                );
                z-index: 0;
            }

            .login-card {
                background: rgba(255, 255, 255, 0.10);
                backdrop-filter: blur(24px);
                -webkit-backdrop-filter: blur(24px);
                border: 1px solid rgba(255, 255, 255, 0.18);
                box-shadow:
                    0 32px 64px rgba(0, 0, 0, 0.5),
                    0 0 0 1px rgba(255,255,255,0.05) inset;
            }

            .form-input-glass {
                background: rgba(255, 255, 255, 0.10);
                border: 1px solid rgba(255, 255, 255, 0.20);
                color: #ffffff;
                transition: all 0.2s ease;
            }

            .form-input-glass::placeholder {
                color: rgba(255, 255, 255, 0.35);
            }

            .form-input-glass:focus {
                background: rgba(255, 255, 255, 0.16);
                border-color: rgba(99, 179, 237, 0.60);
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.20);
                outline: none;
                color: #ffffff;
            }

            .label-glass {
                color: rgba(255, 255, 255, 0.60);
                font-size: 0.65rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.12em;
            }

            .btn-login {
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                border: 1px solid rgba(255,255,255,0.15);
                box-shadow: 0 8px 24px rgba(37, 99, 235, 0.45);
                transition: all 0.2s ease;
            }

            .btn-login:hover {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                box-shadow: 0 12px 32px rgba(37, 99, 235, 0.60);
                transform: translateY(-1px);
            }

            .btn-login:active {
                transform: translateY(0);
            }

            .logo-ring {
                background: linear-gradient(135deg, #1e40af, #0ea5e9);
                animation: pulse-ring 2.5s ease-in-out infinite;
            }

            @keyframes pulse-ring {
                0%   { box-shadow: 0 0 0 0 rgba(59,130,246,0.45); }
                70%  { box-shadow: 0 0 0 14px rgba(59,130,246,0); }
                100% { box-shadow: 0 0 0 0 rgba(59,130,246,0); }
            }

            /* Decorative floating orbs */
            .orb {
                position: fixed;
                border-radius: 50%;
                filter: blur(80px);
                opacity: 0.12;
                pointer-events: none;
                z-index: 0;
            }
        </style>
    </head>
    <body class="antialiased">

        <!-- Dark overlay on top of BG image -->
        <div class="bg-overlay"></div>

        <!-- Decorative orbs -->
        <div class="orb" style="width:500px;height:500px;background:#3b82f6;top:-100px;left:-100px;"></div>
        <div class="orb" style="width:400px;height:400px;background:#06b6d4;bottom:-80px;right:-80px;"></div>

        <!-- Full-screen centering wrapper -->
        <div class="relative z-10 min-h-screen flex flex-col items-center justify-center px-4 py-10">

            <!-- Top Branding -->
            <div class="flex flex-col items-center gap-3 mb-8">
                <!-- Logo -->
                <div class="logo-ring w-16 h-16 rounded-2xl flex items-center justify-center shadow-2xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                    </svg>
                </div>

                <!-- Title -->
                <div class="text-center">
                    <p class="text-white/50 text-[10px] font-bold uppercase tracking-[0.3em]">Perum LPPNPI</p>
                    <h1 class="text-white text-3xl font-black tracking-tight leading-tight mt-0.5">AirNav Indonesia</h1>
                    <p class="text-sky-300 text-sm font-semibold tracking-wider mt-0.5">Cabang Surabaya</p>
                </div>

                <!-- Badge -->
                <span class="inline-flex items-center gap-1.5 bg-blue-500/20 border border-blue-400/30 text-blue-300 text-[10px] font-bold px-4 py-1.5 rounded-full uppercase tracking-widest">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                    Dashboard Monitoring System
                </span>
            </div>

            <!-- Login Card -->
            <div class="login-card rounded-3xl w-full max-w-md p-8 sm:p-10">
                <div class="mb-7 text-center">
                    <h2 class="text-white text-2xl font-black tracking-tight">Selamat Datang</h2>
                    <p class="text-white/50 text-sm font-medium mt-1">Masuk untuk mengakses dashboard monitoring</p>
                </div>

                <!-- Slot: form -->
                {{ $slot }}
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center">
                <p class="text-white/25 text-xs font-medium">
                    © {{ date('Y') }} AirNav Indonesia – Cabang Surabaya &nbsp;·&nbsp; Dashboard Monitoring v1.0
                </p>
            </div>

        </div>
    </body>
</html>
