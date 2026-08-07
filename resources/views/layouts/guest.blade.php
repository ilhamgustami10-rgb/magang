<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Dashboard Monitoring – AirNav Surabaya</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/airnav-logo.png') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @if (file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @elseif (config('app.debug'))
            <div style="background:#b91c1c;color:#fff;padding:12px;font-family:sans-serif;font-size:14px">
                ⚠️ Vite manifest tidak ditemukan. Jalankan <code>npm run build</code> lalu commit folder <code>public/build/</code>.
            </div>
        @endif

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/airnav-logo.png') }}">
        
        <style>
            * { font-family: 'Inter', sans-serif; }

            body {
                margin: 0;
                padding: 0;
                min-height: 100vh;
                background-color: #0a0a0a;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
            }

            .login-box {
                width: 100%;
                max-width: 1120px;
                min-height: 620px;
                background: #ffffff;
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 24px 70px rgba(0, 0, 0, 0.6);
                display: flex;
                flex-direction: row;
                position: relative;
            }

            .login-left {
                width: 45%;
                background: #ffffff;
                display: flex;
                flex-direction: column;
                justify-content: center;
                padding: 40px 48px;
                position: relative;
                z-index: 20;
                clip-path: polygon(0 0, 100% 0, 88% 100%, 0 100%);
            }

            .login-right {
                position: absolute;
                top: 0; right: 0; bottom: 0;
                width: 60%;
                z-index: 10;
            }

            @media (max-width: 768px) {
                .login-box {
                    flex-direction: column;
                    min-height: auto;
                }
                .login-left {
                    width: 100%;
                    clip-path: none;
                    padding: 32px 24px;
                }
                .login-right {
                    display: none; /* Hide photo on small screens to prioritize form */
                }
            }

            .login-bg-slide { 
                position: absolute; 
                inset: 0; 
                width: 100%; 
                height: 100%;
                opacity: 0; 
                transition: opacity 1.5s ease-in-out;
            }
            .login-bg-slide img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: center;
                image-rendering: high-quality;
            }
            .login-bg-slide.active { opacity: 1; }
            
            @media (prefers-reduced-motion: reduce) {
                .login-bg-slide { transition: none; }
            }
            
            .right-overlay {
                position: absolute;
                inset: 0;
                background: linear-gradient(to right, rgba(0,0,0,0.1), rgba(10,20,45,0.5));
                z-index: 15;
            }
        </style>
    </head>
    <body class="antialiased">
        
        @php
            $loginImages = glob(public_path('images/login/*.{webp,jpg,jpeg,png}'), GLOB_BRACE);
            $loginImages = array_filter($loginImages, function($path) {
                return !str_contains($path, '-mobile');
            });
            $loginImages = array_values($loginImages);
            
            $loginImages = $loginImages ? array_map(function($path) {
                return 'images/login/' . basename($path);
            }, $loginImages) : [];
            if(empty($loginImages)) {
                $loginImages = ['images/airnav_bg.png']; // fallback
            }
        @endphp

        <!-- Main Box Container -->
        <div class="login-box">
            
            <!-- Left Panel (Form) -->
            <div class="login-left">
                <div class="mb-8">
                    <img src="{{ asset('images/airnav-logo.png') }}" alt="AirNav Logo" class="h-12 mb-4 object-contain">
                    <p class="text-slate-500 text-[10px] font-bold uppercase tracking-[0.2em] mb-1">Perum LPPNPI</p>
                    <h2 class="text-blue-900 text-2xl font-black tracking-tight">Selamat Datang</h2>
                    <p class="text-slate-500 text-xs mt-1 font-medium">Masuk untuk mengakses dashboard monitoring</p>
                </div>

                <!-- Slot: form -->
                {{ $slot }}
                
                <div class="mt-10 pt-4 border-t border-slate-100 text-left">
                    <p class="text-slate-400 text-[10px] font-medium">
                        &copy; {{ date('Y') }} AirNav Indonesia &mdash; Cabang Surabaya
                    </p>
                </div>
            </div>

            <!-- Right Panel (Photo & Headline) -->
            <div class="login-right">
                @foreach($loginImages as $i => $img)
                    @php
                        $ext = pathinfo($img, PATHINFO_EXTENSION);
                        $basename = basename($img, '.'.$ext);
                        $mobileImg = 'images/login/' . $basename . '-mobile.' . $ext;
                        $hasMobile = file_exists(public_path($mobileImg));
                    @endphp
                    
                    @if($hasMobile)
                        <link rel="preload" as="image" href="{{ asset($mobileImg) }}" media="(max-width: 1023px)">
                        <link rel="preload" as="image" href="{{ asset($img) }}" media="(min-width: 1024px)">
                    @else
                        <link rel="preload" as="image" href="{{ asset($img) }}">
                    @endif
                    
                    <picture class="login-bg-slide {{ $i === 0 ? 'active' : '' }}">
                        @if($hasMobile)
                            <source media="(min-width: 1024px)" srcset="{{ asset($img) }}">
                            <source media="(max-width: 1023px)" srcset="{{ asset($mobileImg) }}">
                        @endif
                        <img src="{{ asset($img) }}" alt="AirNav Background" class="w-full h-full object-cover">
                    </picture>
                @endforeach
                
                <div class="right-overlay"></div>
                
                <!-- Headline over photo -->
                <div class="absolute inset-0 z-20 flex flex-col justify-center items-end p-12 text-right pointer-events-none">
                    <h1 class="text-white text-4xl font-black tracking-tight drop-shadow-lg mb-2">
                        AIRNAV INDONESIA
                    </h1>
                    <p class="text-white/90 text-sm font-medium drop-shadow-md max-w-xs">
                        Dashboard Monitoring System &mdash; Cabang Surabaya
                    </p>
                </div>
            </div>

        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if(prefersReducedMotion) return;

                const slides = document.querySelectorAll('.login-bg-slide');
                let idx = 0;
                if(slides.length > 1) {
                    setInterval(() => {
                        slides[idx].classList.remove('active');
                        idx = (idx + 1) % slides.length;
                        slides[idx].classList.add('active');
                    }, 6000);
                }
            });
        </script>
    </body>
</html>
