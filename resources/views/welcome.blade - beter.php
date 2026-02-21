<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Darsana Dashboard</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gradient-to-br from-slate-100 to-slate-200 min-h-screen">

<div class="max-w-7xl mx-auto px-6 py-8 space-y-8">

    <!-- HEADER -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Flight Revenue Dashboard</h1>
            <p class="text-slate-500 text-sm">Overview performance & revenue analytics</p>
        </div>
        <div class="text-sm text-slate-500">
            {{ date('d M Y') }}
        </div>
    </div>

    <!-- KPI -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl shadow p-6 flex items-center gap-4">
            <div class="bg-blue-100 p-3 rounded-xl text-blue-600">
                📈
            </div>
            <div>
                <p class="text-sm text-slate-500">Revenue Movement</p>
                <p class="text-3xl font-bold">13,046</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow p-6 flex items-center gap-4">
            <div class="bg-emerald-100 p-3 rounded-xl text-emerald-600">
                🛫
            </div>
            <div>
                <p class="text-sm text-slate-500">Total Route Unit</p>
                <p class="text-3xl font-bold">6,216,504</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow p-6 flex items-center gap-4">
            <div class="bg-orange-100 p-3 rounded-xl text-orange-600">
                💰
            </div>
            <div>
                <p class="text-sm text-slate-500">Total Revenue</p>
                <p class="text-3xl font-bold">Rp 58.3B</p>
            </div>
        </div>
    </div>

    <!-- MAIN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LINE CHART -->
        <div class="bg-white rounded-2xl shadow p-6 lg:col-span-2">
            <h3 class="font-semibold mb-4">Revenue Trend</h3>
            <canvas id="lineChart" height="100"></canvas>
        </div>

        <!-- DONUT -->
        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold mb-4 text-center">Revenue Composition</h3>
            <canvas id="donutChart"></canvas>
        </div>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded-2xl shadow p-6">
        <h3 class="font-semibold mb-4">Top Airlines by Revenue</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-slate-500 border-b">
                        <th class="py-2 text-left">#</th>
                        <th class="py-2 text-left">Airline</th>
                        <th class="py-2 text-left">Flights</th>
                        <th class="py-2 text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach([
                        ['SINGAPORE AIRLINES',481,'Rp 5.32B'],
                        ['BATIK AIR MALAYSIA',666,'Rp 3.12B'],
                        ['QATAR',205,'Rp 3.02B'],
                        ['GARUDA INDONESIA',844,'Rp 2.84B'],
                        ['SCOOT',329,'Rp 2.79B']
                    ] as $i => $row)
                    <tr class="hover:bg-slate-50">
                        <td class="py-2">{{ $i+1 }}</td>
                        <td class="py-2 font-medium">{{ $row[0] }}</td>
                        <td class="py-2">{{ $row[1] }}</td>
                        <td class="py-2 text-right font-semibold">{{ $row[2] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
new Chart(document.getElementById('donutChart'), {
    type: 'doughnut',
    data: {
        labels: ['International','Domestic'],
        datasets: [{
            data: [98.8,1.2],
            backgroundColor: ['#64748b','#fb923c']
        }]
    },
    options: {
        cutout: '70%',
        plugins: { legend: { position: 'bottom' } }
    }
});

new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct'],
        datasets: [{
            data: [1.4,1.7,1.5,1.6,1.3,1.4,1.6,1.3,1.5,1.4],
            borderWidth: 3,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { ticks: { callback:v=>v+'B' } }
        }
    }
});
</script>

</body>
</html>
