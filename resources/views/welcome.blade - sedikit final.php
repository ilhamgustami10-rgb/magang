<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Darsana – Revenue Dashboard</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

</head>

<body class="bg-slate-100 min-h-screen">

<div class="max-w-[1800px] mx-auto p-6 space-y-14">

    <!-- HEADER -->
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex flex-col gap-2">
            <h1 class="text-3xl font-bold text-slate-800">
                Traffic Dashboard
            </h1>

            <div class="flex items-center gap-3 flex-wrap">
                <p class="text-slate-500 text-sm">
                    Traffic Movement, Enroute & Terminal Overview
                </p>

                <!-- Divider -->
                <span class="hidden sm:block w-px h-4 bg-slate-300"></span>

                <!-- Update Info -->
                <div class="flex items-center gap-1">
                    <span class="bg-blue-50 text-blue-700 text-[10px] font-semibold px-2 py-1 rounded-full uppercase tracking-wide">
                        Updated
                    </span>
                    <span class="bg-slate-100 text-slate-600 text-[10px] font-semibold px-2 py-1 rounded-full">
                        Aug 2026
                    </span>
                </div>
            </div>
        </div>

        <!-- MENU TAB -->
        <div class="flex items-center gap-2 bg-white border rounded-xl p-1 shadow-sm">
            <a href="#traffic"
            class="px-4 py-1.5 text-sm rounded-lg bg-blue-600 text-white font-medium">
                Traffic
            </a>
            <a href="#enroute"
            class="px-4 py-1.5 text-sm rounded-lg text-slate-600 hover:bg-slate-100">
                Finance
            </a>
            <a href="#terminal"
            class="px-4 py-1.5 text-sm rounded-lg text-slate-600 hover:bg-slate-100">
                Personnel
            </a>
            <a href="#terminal"
            class="px-4 py-1.5 text-sm rounded-lg text-slate-600 hover:bg-slate-100">
                Login
            </a>
        </div>
    </header>

    
    <!-- ========================= Traffic Movement ========================= -->
    <section class="space-y-6">
        <div class="mb-4">
            <h2 class="text-2xl font-semibold text-slate-800">
                Flight Traffic Overview
            </h2>
            <div class="mt-2 h-1 w-16 bg-gradient-to-r from-orange-600 to-yellow-00 rounded-full"></div>
        </div>

        <!-- KPI + DONUT -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl shadow p-6 md:col-span-2">
                <h3 class="font-semibold mb-4">Monthly Traffic Movement Trend</h3>
                <div class="h-60">
                    <canvas id="lineEnroute2"></canvas>
                </div>
            </div> 

            <div class="bg-white rounded-2xl shadow p-6 flex flex-col h-full overflow-hidden">
                <h3 class="font-semibold mb-4 text-center text-slate-800">Traffic Movement Composition</h3>
                
                <div class="flex-grow relative flex items-center justify-center min-h-0"> 
                    <div style="position: relative; height: 220px; width: 100%;">
                        <canvas id="donutTraffic"></canvas>
                    </div>
                </div>
            </div>

            <section class="space-y-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl text-slate-800 tracking-tight">Traffic Statistics Overview</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-5 transition-all hover:shadow-md">
                        <div class="h-14 w-14 rounded-2xl bg-orange-400 flex items-center justify-center text-white shadow-lg shadow-blue-100 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Movement</p>
                            <h3 class="text-3xl font-black text-slate-800 leading-none">13,046</h3>
                            <p class="mt-2 text-xs text-slate-400 font-medium">Flights / Year</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-5 transition-all hover:shadow-md">
                        <div class="h-14 w-14 rounded-2xl bg-orange-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Avg. Daily</p>
                            <h3 class="text-3xl font-black text-slate-800 leading-none">435</h3>
                            <div class="mt-2 flex items-center gap-1">
                                <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">▲ 4.2%</span>
                                <span class="text-[10px] text-slate-400 font-medium whitespace-nowrap">vs yesterday</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>  


            <div class="bg-white rounded-xl shadow-sm px-5 py-4">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">
                        Top 5 Airlines by Flight Movement
                    </p>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-lg border border-slate-200">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="text-left px-3 py-2 font-medium">Airline</th>
                                <th class="text-left px-3 py-2 font-medium">Bars</th>
                                <th class="text-right px-3 py-2 font-medium">Jumlah</th>
                                <th class="text-right px-3 py-2 font-medium">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <!-- Row -->
                            <tr>
                                <td class="px-3 py-2">INDONESIA AIRASIA</td>
                                <td class="px-3 py-2">
                                    <div class="w-full bg-slate-200 rounded h-3">
                                        <div class="bg-orange-400 h-3 rounded" style="width: 96%"></div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right font-medium">1,756</td>
                                <td class="px-3 py-2 text-right text-slate-500">9.63%</td>
                            </tr>

                            <tr>
                                <td class="px-3 py-2">GARUDA INDONESIA</td>
                                <td class="px-3 py-2">
                                    <div class="w-full bg-slate-200 rounded h-3">
                                        <div class="bg-orange-400 h-3 rounded" style="width: 75%"></div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right font-medium">1,366</td>
                                <td class="px-3 py-2 text-right text-slate-500">7.49%</td>
                            </tr>

                            <tr>
                                <td class="px-3 py-2">JETSTAR</td>
                                <td class="px-3 py-2">
                                    <div class="w-full bg-slate-200 rounded h-3">
                                        <div class="bg-orange-400 h-3 rounded" style="width: 68%"></div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right font-medium">1,247</td>
                                <td class="px-3 py-2 text-right text-slate-500">6.84%</td>
                            </tr>

                            <tr>
                                <td class="px-3 py-2">WINGS AIR</td>
                                <td class="px-3 py-2">
                                    <div class="w-full bg-slate-200 rounded h-3">
                                        <div class="bg-orange-400 h-3 rounded" style="width: 66%"></div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right font-medium">1,232</td>
                                <td class="px-3 py-2 text-right text-slate-500">6.75%</td>
                            </tr>

                            <tr>
                                <td class="px-3 py-2">BATIK AIR</td>
                                <td class="px-3 py-2">
                                    <div class="w-full bg-slate-200 rounded h-3">
                                        <div class="bg-orange-400 h-3 rounded" style="width: 65%"></div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right font-medium">1,205</td>
                                <td class="px-3 py-2 text-right text-slate-500">6.61%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm px-5 py-4">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">
                        Top 5 Flight Route by Movement
                    </p>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-lg border border-slate-200">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="text-left px-3 py-2 font-medium">Flight Route</th>
                                <th class="text-left px-3 py-2 font-medium">Bars</th>
                                <th class="text-right px-3 py-2 font-medium">Jumlah</th>
                                <th class="text-right px-3 py-2 font-medium">%</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="px-3 py-2">WADD – WIII</td>
                                <td class="px-3 py-2">
                                    <div class="w-full bg-slate-200 rounded h-3">
                                        <div class="bg-orange-400 h-3 rounded" style="width: 100%"></div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right font-medium">1,443</td>
                                <td class="px-3 py-2 text-right text-slate-500">7.91%</td>
                            </tr>

                            <tr>
                                <td class="px-3 py-2">WIII – WADD</td>
                                <td class="px-3 py-2">
                                    <div class="w-full bg-slate-200 rounded h-3">
                                        <div class="bg-orange-400 h-3 rounded" style="width: 99%"></div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right font-medium">1,439</td>
                                <td class="px-3 py-2 text-right text-slate-500">7.89%</td>
                            </tr>

                            <tr>
                                <td class="px-3 py-2">WADD – WSSS</td>
                                <td class="px-3 py-2">
                                    <div class="w-full bg-slate-200 rounded h-3">
                                        <div class="bg-orange-400 h-3 rounded" style="width: 40%"></div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right font-medium">581</td>
                                <td class="px-3 py-2 text-right text-slate-500">3.19%</td>
                            </tr>

                            <tr>
                                <td class="px-3 py-2">WSSS – WADD</td>
                                <td class="px-3 py-2">
                                    <div class="w-full bg-slate-200 rounded h-3">
                                        <div class="bg-orange-400 h-3 rounded" style="width: 40%"></div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right font-medium">580</td>
                                <td class="px-3 py-2 text-right text-slate-500">3.18%</td>
                            </tr>

                            <tr>
                                <td class="px-3 py-2">WMKK – WADD</td>
                                <td class="px-3 py-2">
                                    <div class="w-full bg-slate-200 rounded h-3">
                                        <div class="bg-orange-400 h-3 rounded" style="width: 38%"></div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right font-medium">552</td>
                                <td class="px-3 py-2 text-right text-slate-500">3.03%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    <!-- ========================= ENROUTE ========================= -->
    <section class="space-y-6">
        <div class="mb-4">
            <h2 class="text-2xl font-semibold text-slate-800">
                Enroute Performance
            </h2>
            <div class="mt-2 h-1 w-16 bg-gradient-to-r from-blue-600 to-sky-100 rounded-full"></div>
        </div>

        <!-- KPI + DONUT -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-sm px-5 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">
                        Enroute Movement
                    </p>
                    <p class="text-3xl font-black text-slate-800 leading-none">
                        13,046
                    </p>
                </div>

                <div class="h-11 w-11 rounded-lg bg-sky-200 flex items-center justify-center text-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                </div>
            </div>


            <div class="bg-white rounded-xl shadow-sm px-5 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">
                        Enroute - Total of Route Unit
                    </p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">
                        6,216,504
                    </p>
                </div>

                <div class="h-11 w-11 rounded-lg bg-sky-200 flex items-center justify-center text-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 104 0 2 2 0 00-4 0zm12 6a2 2 0 104 0 2 2 0 00-4 0zM4 18a2 2 0 104 0 2 2 0 00-4 0zm4-12l8 6-8 6"/>
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm px-5 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">
                        Enroute - Total of Revenue
                    </p>
                    <p class="text-3xl font-black text-slate-800 leading-none">
                        Rp 58,314,188,264
                    </p>
                </div>

                <div class="h-11 w-11 rounded-lg bg-sky-200 flex items-center justify-center text-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2
                            3 .895 3 2-1.343 2-3 2m0-10v1m0 10v1
                            m9-6a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            

            <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="font-semibold mb-2 text-center">Revenue Composition</h3>
                <!-- <div class="h-56">
                    <canvas id="donutEnroute"></canvas>
                </div> -->
                <div class="h-64 w-64 mx-auto relative"> 
                    <canvas id="donutEnroute"></canvas>
                </div>
            </div>

           <div class="bg-white rounded-2xl shadow p-6 md:col-span-2">
                <h3 class="font-semibold mb-4">Revenue Trend – Enroute</h3>
                <div class="h-60">
                    <canvas id="lineEnroute"></canvas>
                </div>
            </div>  
        </div>

        <!-- TOP AIRLINE -->
        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold mb-4">Top Airline – Enroute</h3>
            <table class="w-full text-sm">
                <thead class="border-b text-slate-500">
                    <tr>
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
    </section>

    <!-- ========================= TERMINAL ========================= -->
    <section class="space-y-6">
        <h2 class="text-2xl font-semibold text-slate-800">🏢 Terminal Performance</h2>

        <!-- KPI + DONUT -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-sm text-slate-500">Terminal Revenue</p>
                <p class="text-3xl font-bold">Rp 25.9B</p>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-sm text-slate-500">Terminal Movement</p>
                <p class="text-3xl font-bold">5,234</p>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="font-semibold mb-2 text-center">Revenue Composition</h3>
                <div class="h-56">
                    <canvas id="donutTerminal"></canvas>
                </div>
            </div>
        </div>

        <!-- LINE -->
        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold mb-4">Revenue Trend – Terminal</h3>
            <div class="h-80">
                <canvas id="lineTerminal"></canvas>
            </div>
        </div>

        <!-- TOP AIRLINE -->
        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold mb-4">Top Airline – Terminal</h3>
            <table class="w-full text-sm">
                <thead class="border-b text-slate-500">
                    <tr>
                        <th>#</th>
                        <th>Airline</th>
                        <th>Flights</th>
                        <th class="text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach([
                        ['GARUDA INDONESIA',644,'Rp 4.12B'],
                        ['SCOOT',329,'Rp 2.79B'],
                        ['AIR ASIA',510,'Rp 2.45B'],
                    ] as $i => $row)
                    <tr class="hover:bg-slate-50">
                        <td>{{ $i+1 }}</td>
                        <td class="font-medium">{{ $row[0] }}</td>
                        <td>{{ $row[1] }}</td>
                        <td class="text-right font-semibold">{{ $row[2] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

</div>

<!-- ========================= CHART SCRIPT ========================= -->
<script>
// Daftarkan plugin secara global
Chart.register(ChartDataLabels);

const donutOptions = {
    responsive: true,
    maintainAspectRatio: false, // WAJIB false agar tidak memaksa ukuran
    devicePixelRatio: window.devicePixelRatio, // Menjaga ketajaman pixel saat zoom
    layout: {
        padding: 10 // Memberi ruang agar label tidak terpotong
    },
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                usePointStyle: true,
                font: { size: 11 }
            }
        },
        datalabels: {
            // Kecilkan ukuran font sedikit agar tidak "menabrak" saat zoom
            font: {
                size: 14, 
                weight: 'bold'
            },
            formatter: (value) => value.toFixed(1) + "%",
            color: '#475569' // Warna abu-abu gelap agar lebih elegan
        }
    }
};

new Chart(donutTraffic, {
    type: 'doughnut',
    data: {
        labels: ['Passenger','Cargo'],
        datasets: [{
            data: [72,28],
            backgroundColor: ['#ebac25','#fdea93']
        }]
    },
    options: donutOptions
});


new Chart(donutEnroute, {
    type: 'doughnut',
    data: {
        labels: ['International', 'Domestic'],
        datasets: [{
            data: [85, 15],
            backgroundColor: ['#2563eb', '#93c5fd'],
            hoverOffset: 4,
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: {
        cutout: '68%', // Membuat lubang tengah lebih besar (lebih modern)
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    padding: 10,
                    font: { size: 10 }
                }
            },
            datalabels: {
                color: (context) => {
                    // International (biru tua) teks putih, Domestic (biru muda) teks biru tua
                    return context.dataIndex === 0 ? '#221e1e' : '#1e40af';
                },
                font: {
                    weight: 'bold',
                    size: 20
                },
                formatter: (value, context) => {
                    // Menghitung total untuk memastikan persentase akurat
                    let sum = 0;
                    let dataArr = context.chart.data.datasets[0].data;
                    dataArr.map(data => { sum += data; });
                    let percentage = (value * 100 / sum).toFixed(1) + "%";
                    return percentage;
                },
                anchor: 'center',
                align: 'center'
            }
        },
        // Tambahkan animasi yang halus
        animation: {
            animateScale: true,
            animateRotate: true
        }
    }
});

new Chart(donutTerminal, {
    type: 'doughnut',
    data: {
        labels: ['Passenger','Cargo'],
        datasets: [{
            data: [72,28],
            backgroundColor: ['#16a34a','#86efac']
        }]
    },
    options: donutOptions
});

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { ticks: { callback:v => v + 'B' } }
    }
};

new Chart(lineEnroute2, {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'],
        datasets: [{
            data: [1.4,1.6,1.5,1.7,1.8,1.6,1.7,1.9],
            borderWidth: 3,
            tension: 0.4,
            borderColor: '#ebac25',
            fill: true
        }]
    },
    options: lineOptions
});

new Chart(lineEnroute, {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'],
        datasets: [{
            data: [1.4,1.6,1.5,1.7,1.8,1.6,1.7,1.9],
            borderWidth: 3,
            tension: 0.4,
            borderColor: '#2563eb',
            fill: true
        }]
    },
    options: lineOptions
});

new Chart(lineTerminal, {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'],
        datasets: [{
            data: [1.2,1.3,1.4,1.3,1.5,1.4,1.6,1.5],
            borderWidth: 3,
            tension: 0.4,
            borderColor: '#16a34a',
            fill: true
        }]
    },
    options: lineOptions
});
</script>

</body>
</html>
