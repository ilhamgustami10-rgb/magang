<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Darsana App</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 flex items-center justify-center">

    <div class="w-full max-w-4xl grid md:grid-cols-2 gap-10 items-center px-6">

        <!-- LEFT -->
        <div>
            <span class="inline-block mb-4 px-4 py-1 text-sm font-medium text-blue-600 bg-blue-100 rounded-full">
                Laravel + Tailwind
            </span>

            <h1 class="text-4xl md:text-5xl font-bold text-slate-800 leading-tight mb-6">
                Bangun Aplikasi <br>
                <span class="text-blue-600">Lebih Cepat & Elegan</span>
            </h1>

            <p class="text-slate-600 text-lg mb-8">
                Darsana adalah fondasi aplikasi modern berbasis Laravel
                dengan tampilan rapi, responsif, dan siap produksi.
            </p>

            <div class="flex gap-4">
                <a href="#"
                   class="px-6 py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition shadow">
                    Mulai Sekarang
                </a>

                <a href="#"
                   class="px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-100 transition">
                    Dokumentasi
                </a>
            </div>
        </div>

        <!-- RIGHT CARD -->
        <div class="bg-white rounded-3xl shadow-xl p-8">

            <div class="grid grid-cols-2 gap-6">

                <!-- Item -->
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-xl bg-blue-100 text-blue-600">
                        <!-- icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 7.5h18M3 12h18M3 16.5h18"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Struktur Rapi</h3>
                        <p class="text-sm text-slate-500">Kode mudah dikembangkan</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-xl bg-green-100 text-green-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 6v6l4 2"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Cepat</h3>
                        <p class="text-sm text-slate-500">Build tanpa ribet</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-xl bg-purple-100 text-purple-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9.75 17L6 21h12l-3.75-4M12 3v14"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Modern UI</h3>
                        <p class="text-sm text-slate-500">Clean & profesional</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-xl bg-orange-100 text-orange-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v3.75m0 3.75h.01M21 12A9 9 0 113 12a9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Siap Produksi</h3>
                        <p class="text-sm text-slate-500">Best practice Laravel</p>
                    </div>
                </div>

            </div>

        </div>

    </div>

</body>
</html>
