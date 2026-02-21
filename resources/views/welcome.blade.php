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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl shadow p-6 lg:col-span-2">
                <h3 class="font-semibold mb-4 text-slate-800">Monthly Traffic Movement Trend</h3>
                <div class="h-64">
                    <canvas id="lineEnroute2"></canvas>
                </div>
            </div> 

            <div class="bg-white rounded-2xl shadow p-6 flex flex-col">
                <h3 class="font-semibold mb-4 text-center text-slate-800">Traffic Movement Composition</h3>
                <div class="relative h-64 w-full"> 
                    <canvas id="donutTraffic"></canvas>
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
                <div class="relative h-64 w-full"> 
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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 h-full flex flex-col">
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-bold text-slate-800 uppercase tracking-tight">Traffic Peak Window</h4>
                        <span class="text-[10px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded-md font-bold italic">Busiest Period</span>
                    </div>
                    
                    <div class="flex items-end justify-between gap-1 h-24 mb-2">
                        @foreach([30, 45, 60, 90, 100, 85, 50, 40] as $height)
                            <div class="bg-slate-100 hover:bg-blue-500 w-full rounded-t-sm transition-all duration-300" style="height: {{ $height }}%"></div>
                        @endforeach
                    </div>
                    <div class="flex justify-between text-[9px] font-bold text-slate-400">
                        <span>00:00</span>
                        <span class="text-blue-600 font-black">PEAK: 10:00 - 14:00</span>
                        <span>23:59</span>
                    </div>
                </div>

                <div class="flex-grow">
                    <h4 class="text-sm font-bold text-slate-800 uppercase mb-4">Aircraft Category Mix</h4>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-[10px] mb-1">
                                <span class="font-bold text-slate-600">HEAVY (B777, A350, etc)</span>
                                <span class="font-black text-slate-900">42%</span>
                            </div>
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-blue-600 h-full rounded-full" style="width: 42%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-[10px] mb-1">
                                <span class="font-bold text-slate-600">MEDIUM (B737, A320)</span>
                                <span class="font-black text-slate-900">55%</span>
                            </div>
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-sky-400 h-full rounded-full" style="width: 55%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-[10px] mb-1">
                                <span class="font-bold text-slate-600">LIGHT / OTHERS</span>
                                <span class="font-black text-slate-900">3%</span>
                            </div>
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-slate-300 h-full rounded-full" style="width: 3%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-50">
                    <p class="text-[10px] text-slate-400 leading-relaxed italic">
                        *High percentage of **Heavy Aircraft** correlates with the high revenue yield observed in Singapore Airlines & Qatar routes.
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 lg:col-span-2">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800">Top Airline – Enroute Performance</h3>
                        <p class="text-xs text-slate-400">Perbandingan intensitas pergerakan terhadap kontribusi pendapatan</p>
                    </div>
                    <div class="flex gap-2">
                        <span class="flex items-center gap-1 text-[10px] font-bold text-slate-400">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span> Movement
                        </span>
                        <span class="flex items-center gap-1 text-[10px] font-bold text-slate-400">
                            <span class="w-2 h-2 rounded-full bg-sky-400"></span> Revenue Value
                        </span>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50/80 text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 text-left font-bold uppercase tracking-wider text-[10px]">Rank</th>
                                <th class="py-3 px-4 text-left font-bold uppercase tracking-wider text-[10px]">Airline</th>
                                <th class="py-3 px-4 text-left font-bold uppercase tracking-wider text-[10px] w-1/3">Traffic vs Value Comparison</th>
                                <th class="py-3 px-4 text-right font-bold uppercase tracking-wider text-[10px]">Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach([  
                                ['SINGAPORE AIRLINES', 481, 'Rp 5.32B', 45, 95], // [Nama, Flight, Rev, Movement%, Revenue%]
                                ['BATIK AIR MALAYSIA', 666, 'Rp 3.12B', 75, 60],
                                ['QATAR AIRWAYS', 205, 'Rp 3.02B', 25, 58],
                                ['GARUDA INDONESIA', 844, 'Rp 2.84B', 95, 55],
                                ['CATHAY PACIFIC', 198, 'Rp 2.10B', 20, 40],
                            ] as $i => $row)
                            <tr class="group hover:bg-slate-50/50 transition-all">
                                <td class="py-4 px-4 text-center">
                                    <span class="text-[11px] font-black text-slate-400 group-hover:text-blue-600">0{{ $i+1 }}</span>
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-700">
                                    {{ $row[0] }}
                                    <div class="text-[10px] font-medium text-slate-400 normal-case">{{ number_format($row[1]) }} Flights</div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="space-y-2">
                                        <div class="relative w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="absolute top-0 left-0 bg-blue-500 h-full rounded-full transition-all duration-1000" style="width: {{ $row[3] }}%"></div>
                                        </div>
                                        <div class="relative w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="absolute top-0 left-0 bg-sky-400 h-full rounded-full transition-all duration-1000" style="width: {{ $row[4] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="text-base font-black text-slate-900 leading-none">{{ $row[2] }}</div>
                                    @if($row[4] > $row[3])
                                        <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-tighter">High Yield</span>
                                    @else
                                        <span class="text-[9px] font-bold text-blue-600 uppercase tracking-tighter">High Volume</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================= TERMINAL ========================= -->
    <section class="space-y-6">
        <div class="mb-4">
            <h2 class="text-2xl font-semibold text-slate-800">
                🏢 Terminal Performance
            </h2>
            <div class="mt-2 h-1 w-16 bg-gradient-to-r from-emerald-600 to-green-100 rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-sm px-5 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Terminal Movement</p>
                    <p class="text-3xl font-black text-slate-800 leading-none">5,234</p>
                </div>
                <div class="h-11 w-11 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm px-5 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Terminal - Service Unit</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">1,245,800</p>
                </div>
                <div class="h-11 w-11 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm px-5 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Terminal - Total Revenue</p>
                    <p class="text-3xl font-black text-slate-800 leading-none">Rp 25,942,105,000</p>
                </div>
                <div class="h-11 w-11 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10v1m0 10v1m9-6a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="font-semibold mb-2 text-center text-slate-800">Revenue Composition</h3>
                <div class="relative h-64 w-full"> 
                    <canvas id="donutTerminal"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow p-6 md:col-span-2">
                <h3 class="font-semibold mb-4 text-slate-800">Revenue Trend – Terminal</h3>
                <div class="h-60">
                    <canvas id="lineTerminal"></canvas>
                </div>
            </div> 
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 h-full flex flex-col">
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-bold text-slate-800 uppercase tracking-tight">Terminal Peak Window</h4>
                        <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-md font-bold italic">Busiest Period</span>
                    </div>
                    
                    <div class="flex items-end justify-between gap-1 h-24 mb-2">
                        @foreach([20, 35, 80, 100, 90, 60, 30, 25] as $height)
                            <div class="bg-slate-100 hover:bg-emerald-500 w-full rounded-t-sm transition-all duration-300" style="height: {{ $height }}%"></div>
                        @endforeach
                    </div>
                    <div class="flex justify-between text-[9px] font-bold text-slate-400">
                        <span>00:00</span>
                        <span class="text-emerald-600 font-black">PEAK: 08:00 - 11:00</span>
                        <span>23:59</span>
                    </div>
                </div>

                <div class="flex-grow">
                    <h4 class="text-sm font-bold text-slate-800 uppercase mb-4">Terminal Aircraft Mix</h4>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-[10px] mb-1">
                                <span class="font-bold text-slate-600">HEAVY</span>
                                <span class="font-black text-slate-900">15%</span>
                            </div>
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-emerald-600 h-full rounded-full" style="width: 15%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-[10px] mb-1">
                                <span class="font-bold text-slate-600">MEDIUM</span>
                                <span class="font-black text-slate-900">75%</span>
                            </div>
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-green-400 h-full rounded-full" style="width: 75%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-[10px] mb-1">
                                <span class="font-bold text-slate-600">LIGHT</span>
                                <span class="font-black text-slate-900">10%</span>
                            </div>
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-slate-300 h-full rounded-full" style="width: 10%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 lg:col-span-2">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800">Top Airline – Terminal Performance</h3>
                        <p class="text-xs text-slate-400">Comparison of movement intensity vs terminal revenue contribution</p>
                    </div>
                    <div class="flex gap-2">
                        <span class="flex items-center gap-1 text-[10px] font-bold text-slate-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Movement
                        </span>
                        <span class="flex items-center gap-1 text-[10px] font-bold text-slate-400">
                            <span class="w-2 h-2 rounded-full bg-teal-400"></span> Revenue Value
                        </span>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50/80 text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 text-left font-bold uppercase tracking-wider text-[10px]">Rank</th>
                                <th class="py-3 px-4 text-left font-bold uppercase tracking-wider text-[10px]">Airline</th>
                                <th class="py-3 px-4 text-left font-bold uppercase tracking-wider text-[10px] w-1/3">Comparison</th>
                                <th class="py-3 px-4 text-right font-bold uppercase tracking-wider text-[10px]">Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach([  
                                ['GARUDA INDONESIA', 644, 'Rp 4.12B', 90, 85],
                                ['SCOOT', 329, 'Rp 2.79B', 50, 70],
                                ['AIR ASIA', 510, 'Rp 2.45B', 80, 55],
                                ['LION AIR', 844, 'Rp 2.14B', 95, 45],
                                ['JETSTAR', 210, 'Rp 1.80B', 30, 40],
                            ] as $i => $row)
                            <tr class="group hover:bg-emerald-50/30 transition-all">
                                <td class="py-4 px-4 text-center">
                                    <span class="text-[11px] font-black text-slate-400 group-hover:text-emerald-600">0{{ $i+1 }}</span>
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-700">
                                    {{ $row[0] }}
                                    <div class="text-[10px] font-medium text-slate-400">{{ number_format($row[1]) }} Flights</div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="space-y-2">
                                        <div class="relative w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="absolute top-0 left-0 bg-emerald-500 h-full rounded-full" style="width: {{ $row[3] }}%"></div>
                                        </div>
                                        <div class="relative w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="absolute top-0 left-0 bg-teal-400 h-full rounded-full" style="width: {{ $row[4] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="text-base font-black text-slate-900 leading-none">{{ $row[2] }}</div>
                                    <span class="text-[9px] font-bold {{ $row[4] > $row[3] ? 'text-teal-600' : 'text-emerald-600' }} uppercase tracking-tighter">
                                        {{ $row[4] > $row[3] ? 'High Yield' : 'High Volume' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

</div>

<!-- ========================= CHART SCRIPT ========================= -->
<script>
// 1. Registrasi Plugin (Hanya satu kali)
Chart.register(ChartDataLabels);

const globalDonutOptions = {
    responsive: true,
    maintainAspectRatio: false, // Penting agar grafik mengikuti tinggi div
    layout: { 
        padding: 20 // Memberi ruang agar label tidak terpotong
    },
    plugins: {
        legend: {
            position: 'bottom',
            labels: { 
                usePointStyle: true, 
                padding: 20, // Jarak antar teks legend
                font: { size: 12 } 
            }
        },
        datalabels: {
            // Logika Warna Otomatis
            color: (context) => {
                const index = context.dataIndex;
                // Jika International (gelap) -> Teks Putih
                // Jika Domestic (terang) -> Teks Hitam/Abu gelap
                return index === 0 ? '#ffffff' : '#1e293b';
            },
            anchor: 'center',
            align: 'center',
            font: { 
                weight: 'bold', 
                size: 14 
            },
            formatter: (value, context) => {
                let sum = 0;
                let dataArr = context.chart.data.datasets[0].data;
                dataArr.map(data => { sum += data; });
                return (value * 100 / sum).toFixed(1) + "%";
            }
        }
    }
};

// 3. Konfigurasi Global Line (Mematikan Datalabels agar muncul)
const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { 
        legend: { display: false },
        datalabels: { display: false } 
    },
    scales: {
        y: { 
            beginAtZero: true,
            ticks: { callback: v => v + 'B' } 
        }
    }
};

// --- DONUT TRAFFIC ---
new Chart(document.getElementById('donutTraffic'), {
    type: 'doughnut',
    data: {
        labels: ['International', 'Domestic'],
        datasets: [{
            data: [85, 15],
            backgroundColor: ['#ebac25','#fdea93'],
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: { ...globalDonutOptions, cutout: '45%' }
});

// --- DONUT ENROUTE ---
new Chart(document.getElementById('donutEnroute'), {
    type: 'doughnut',
    data: {
        labels: ['International', 'Domestic'],
        datasets: [{
            data: [85, 15],
            backgroundColor: ['#2563eb', '#93c5fd'],
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: { ...globalDonutOptions, cutout: '55%' }
});

// --- DONUT TERMINAL ---
new Chart(document.getElementById('donutTerminal'), {
    type: 'doughnut',
    data: {
        labels: ['Passenger','Cargo'],
        datasets: [{
            data: [72,28],
            backgroundColor: ['#16a34a','#86efac']
        }]
    },
    options: globalDonutOptions // Tadi di sini Anda salah tulis variabel
});

// --- LINE ENROUTE 2 ---
new Chart(document.getElementById('lineEnroute2'), {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'],
        datasets: [{
            data: [1.4, 1.6, 1.5, 1.7, 1.8, 1.6, 1.7, 1.9],
            borderWidth: 3,
            tension: 0.4,
            borderColor: '#ebac25',
            backgroundColor: 'rgba(235, 172, 37, 0.1)',
            fill: true
        }]
    },
    options: lineOptions
});

// --- LINE ENROUTE ---
new Chart(document.getElementById('lineEnroute'), {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'],
        datasets: [{
            data: [1.4,1.6,1.5,1.7,1.8,1.6,1.7,1.9],
            borderWidth: 3,
            tension: 0.4,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(30, 103, 238, 0.23)',
            fill: true
        }]
    },
    options: lineOptions
});

// --- LINE TERMINAL ---
new Chart(document.getElementById('lineTerminal'), {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'],
        datasets: [{
            data: [1.2,1.3,1.4,1.3,1.5,1.4,1.6,1.5],
            borderWidth: 3,
            tension: 0.4,
            borderColor: '#16a34a',
            backgroundColor: 'rgba(30, 238, 40, 0.12)',
            fill: true
        }]
    },
    options: lineOptions
});
</script>

</body>
</html>
