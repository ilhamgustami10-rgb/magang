<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-slate-100 min-h-screen">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white border-r hidden md:flex flex-col">
        <div class="px-6 py-5 border-b">
            <h1 class="text-xl font-bold text-blue-600 flex items-center gap-2">
                📊 Darsana
            </h1>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 text-sm">
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-50 text-blue-600 font-semibold">
                <!-- icon -->
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24"><path d="M3 3h18v18H3z"/></svg>
                Dashboard
            </a>

            <a class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-100 text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                Data
            </a>

            <a class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-100 text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24"><path d="M9 17v-6h13"/></svg>
                Laporan
            </a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="flex-1 flex flex-col">

        <!-- HEADER -->
        <header class="bg-white border-b px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold">Dashboard</h2>

            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-600">Admin</span>
                <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">
                    A
                </div>
            </div>
        </header>

        <!-- CONTENT -->
        <section class="p-6 flex-1">

            <!-- CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                <div class="bg-white p-6 rounded-2xl shadow flex items-center gap-4">
                    <div class="p-3 bg-blue-100 rounded-xl text-blue-600">
                        👤
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Total User</p>
                        <h3 class="text-2xl font-bold">1.245</h3>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow flex items-center gap-4">
                    <div class="p-3 bg-green-100 rounded-xl text-green-600">
                        📥
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Data Masuk</p>
                        <h3 class="text-2xl font-bold">320</h3>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow flex items-center gap-4">
                    <div class="p-3 bg-yellow-100 rounded-xl text-yellow-600">
                        📄
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Laporan</p>
                        <h3 class="text-2xl font-bold">87</h3>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow flex items-center gap-4">
                    <div class="p-3 bg-emerald-100 rounded-xl text-emerald-600">
                        ✅
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Status</p>
                        <span class="text-green-600 font-semibold">Aktif</span>
                    </div>
                </div>

            </div>

            <!-- CHART -->
            <div class="bg-white p-6 rounded-2xl shadow">
                <h3 class="text-lg font-semibold mb-4">Statistik Bulanan</h3>
                <canvas id="myChart" height="100"></canvas>
            </div>

        </section>
    </main>
</div>

<!-- CHART SCRIPT -->
<script>
const ctx = document.getElementById('myChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
        datasets: [{
            label: 'Data Masuk',
            data: [120, 190, 170, 220, 260, 300],
            borderWidth: 3,
            tension: 0.4,
            fill: false
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});
</script>

</body>
</html>
