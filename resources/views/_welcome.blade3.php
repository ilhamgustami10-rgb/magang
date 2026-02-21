<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flight Revenue Dashboard</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-slate-100 text-slate-700">

<div class="p-6 space-y-6">

    <!-- FILTER -->
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-center font-semibold mb-4">Control Variable</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach(['Month','Departure','Date Of Flight','Destination','Aircraft ID','Registrasi','Airline','Aircraft Type'] as $item)
                <select class="border rounded-lg px-4 py-2 text-sm focus:ring focus:ring-blue-200">
                    <option>{{ $item }}</option>
                </select>
            @endforeach
        </div>
    </div>

    <!-- TOP CONTENT -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- TABLE -->
        <div class="bg-white rounded-xl shadow p-4 lg:col-span-2">
            <h3 class="font-semibold mb-3">Number of Flight</h3>
            <table class="w-full text-sm">
                <thead class="border-b">
                    <tr class="text-left">
                        <th>#</th>
                        <th>Airline</th>
                        <th>Number of Flight</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @php
                        $data = [
                            ['SINGAPORE AIRLINES',481,'Rp 5,321,934,200'],
                            ['OTHER',421,'Rp 3,339,871,999'],
                            ['BATIK AIR MALAYSIA',666,'Rp 3,123,017,946'],
                            ['QATAR',205,'Rp 3,020,649,914'],
                            ['GARUDA INDONESIA',844,'Rp 2,841,882,137'],
                            ['SCOOT',329,'Rp 2,792,679,234'],
                        ];
                    @endphp
                    @foreach($data as $i => $d)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $d[0] }}</td>
                        <td>{{ $d[1] }}</td>
                        <td>{{ $d[2] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- DONUT -->
        <div class="bg-white rounded-xl shadow p-4">
            <h3 class="font-semibold mb-3 text-center">Revenue ENC (%)</h3>
            <canvas id="donutChart"></canvas>
        </div>
    </div>

    <!-- KPI -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow p-6 text-center">
            <p class="text-sm">Revenue Movement</p>
            <h2 class="text-4xl font-bold">13,046</h2>
            <p class="text-sm mt-1">Recognized as Revenue</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6 text-center">
            <p class="text-sm">Total of Route Unit</p>
            <h2 class="text-4xl font-bold">6,216,504</h2>
        </div>

        <div class="bg-white rounded-xl shadow p-6 text-center">
            <p class="text-sm">Total of Revenue (Rp)</p>
            <h2 class="text-4xl font-bold">Rp 58,314,188,264</h2>
        </div>
    </div>

    <!-- LINE CHART -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-semibold mb-4">Revenue</h3>
        <canvas id="lineChart" height="90"></canvas>
    </div>

</div>

<!-- CHARTS -->
<script>
/* Donut */
new Chart(document.getElementById('donutChart'), {
    type: 'doughnut',
    data: {
        labels: ['International','Domestic'],
        datasets: [{
            data: [98.8, 1.2],
            backgroundColor: ['#d1d5db','#fb923c']
        }]
    },
    options: {
        plugins: { legend: { position: 'right' } },
        cutout: '70%'
    }
});

/* Line */
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: ['Jan 1','Jan 3','Jan 5','Jan 7','Jan 9','Jan 11','Jan 13','Jan 15','Jan 17','Jan 19','Jan 21','Jan 23','Jan 25','Jan 27','Jan 29','Jan 31','Feb 2','Feb 4','Feb 6','Feb 8','Feb 10'],
        datasets: [{
            label: 'Revenue',
            data: [1.44,1.70,1.55,1.58,1.60,1.51,1.28,1.32,1.57,1.27,1.30,1.49,1.33,1.19,1.18,1.55,1.28,1.32,1.50,1.22,1.29],
            borderWidth: 3,
            tension: 0.4
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: {
                ticks: {
                    callback: v => v + 'B'
                }
            }
        }
    }
});
</script>

</body>
</html>
