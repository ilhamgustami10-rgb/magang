<x-app-layout>
    <!-- ========================= Traffic Movement ========================= -->
    <section class="space-y-8 px-6 sm:px-10 lg:px-14 w-full pb-12">

        <!-- Section Header -->
        <div class="mb-2 pt-2">
            <h2 class="flex items-center gap-4 text-[42px] font-black text-slate-800 tracking-tight">
                <span>Flight Traffic Overview</span>
                <span class="hidden sm:block w-px h-7 bg-slate-300"></span>
                <div class="inline-flex items-center gap-2">
                    <span class="inline-flex items-center bg-blue-50 text-blue-700 text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wide">Updated</span>
                    <span class="inline-flex items-center bg-slate-100 text-slate-600 text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wide">{{ $periodtraffic }}</span>
                </div>
            </h2>
            <div class="mt-3 h-1.5 w-20 bg-gradient-to-r from-orange-500 to-yellow-300 rounded-full"></div>
        </div>

        <!-- KPI + DONUT Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Monthly Traffic Trend Chart -->
            <div class="bg-white rounded-[1.75rem] border border-slate-100 shadow-xl shadow-slate-200/50 p-10 lg:col-span-2 relative overflow-hidden">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight uppercase">Monthly Traffic Trend</h3>
                        <div class="h-1 w-14 bg-amber-500 mt-3 rounded-full"></div>
                    </div>
                    <div class="flex gap-6">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded-full bg-[#ebac25]"></div>
                            <span class="text-sm font-bold text-slate-500 uppercase tracking-wide">Arrival</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded-full bg-[#f97316]"></div>
                            <span class="text-sm font-bold text-slate-500 uppercase tracking-wide">Departure</span>
                        </div>
                    </div>
                </div>
                <!-- LINE CHART Traffic Trend — taller for HD -->
                <div class="h-96 w-full">
                    <canvas id="lineEnroute2"
                            data-labels="{{ json_encode($chartLabels) }}"
                            data-arrival="{{ json_encode($arrivalTrend) }}"
                            data-departure="{{ json_encode($departureTrend) }}">
                    </canvas>
                </div>
            </div>

            <!-- Donut + Total -->
            <div class="bg-white rounded-[1.75rem] shadow-xl border border-slate-100 p-8 flex flex-col items-center">
                <h3 class="font-black mb-5 text-slate-800 text-xl uppercase tracking-tight">Traffic Composition</h3>
                <div class="relative h-72 w-full mb-6">
                    <canvas id="donutTraffic"
                            data-international="{{ $internationalPct }}"
                            data-domestic="{{ $domesticPct }}">
                    </canvas>
                </div>
                <div class="w-full bg-orange-50 border border-orange-100 rounded-2xl p-6 flex flex-col items-center">
                    <span class="text-slate-600 text-sm uppercase tracking-widest font-bold mb-1">Total Traffic</span>
                    <div class="h-0.5 w-24 bg-orange-500 mx-auto mb-3 rounded-full"></div>
                    <div class="flex items-baseline gap-3">
                        <span class="text-5xl font-black text-slate-900 leading-none">
                            {{ number_format($totalMovement, 0, ',', '.') }}
                        </span>
                        <span class="text-base font-semibold text-slate-400 italic">Movements</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Peak Window + Aircraft Mix + Top Airlines + Top Routes -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Peak Window + Aircraft Mix -->
            <div class="bg-white rounded-[1.75rem] p-8 shadow-xl border border-slate-100 flex flex-col gap-8">
                <!-- Peak Window -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-base font-black text-slate-800 uppercase tracking-tight">Traffic Peak Window</h4>
                        <span class="text-xs bg-amber-100 text-amber-700 px-3 py-1 rounded-full font-bold">Busiest Period</span>
                    </div>
                    <div class="flex items-end justify-between gap-1 h-32 mb-3">
                        @foreach($peakHeights as $height)
                            <div class="bg-orange-100 hover:bg-orange-500 w-full rounded-t transition-all duration-300"
                                style="height: {{ $height }}%"></div>
                        @endforeach
                    </div>
                    <div class="flex justify-between text-xs font-bold text-slate-400">
                        <span>00:00</span>
                        <span class="text-orange-600 font-black">PEAK: {{ $peakStart }} – {{ $peakEnd }}</span>
                        <span>23:59</span>
                    </div>
                </div>

                <!-- Aircraft Category Mix -->
                <div class="flex-grow">
                    <h4 class="text-base font-black text-slate-800 uppercase mb-5">Aircraft Category Mix</h4>
                    <div class="space-y-5">
                        <div>
                            <div class="flex justify-between text-sm mb-1.5">
                                <span class="font-bold text-slate-600">HEAVY (B777, A350, etc)</span>
                                <span class="font-black text-slate-900">{{ $heavyPercentage }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-orange-600 h-full rounded-full" style="width: {{ $heavyPercentage }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1.5">
                                <span class="font-bold text-slate-600">MEDIUM (B737, A320)</span>
                                <span class="font-black text-slate-900">{{ $mediumPercentage }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-yellow-400 h-full rounded-full" style="width: {{ $mediumPercentage }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1.5">
                                <span class="font-bold text-slate-600">LIGHT / OTHERS</span>
                                <span class="font-black text-slate-900">{{ $lightPercentage }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-slate-300 h-full rounded-full" style="width: {{ $lightPercentage }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Airlines -->
            <div class="bg-white rounded-[1.75rem] shadow-xl border border-slate-100 p-8">
                <h4 class="text-base font-black text-slate-800 uppercase tracking-tight mb-5">Top Airlines by Movement</h4>
                <div class="overflow-hidden rounded-xl border border-slate-200">
                    <table class="w-full text-base">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="text-left px-5 py-3 font-bold">Airline</th>
                                <th class="text-left px-5 py-3 font-bold">Bar</th>
                                <th class="text-right px-5 py-3 font-bold">Jumlah</th>
                                <th class="text-right px-5 py-3 font-bold">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($topTrafficAirlines as $airline)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3 font-semibold text-slate-700">{{ $airline['name'] }}</td>
                                <td class="px-5 py-3">
                                    <div class="w-full bg-slate-200 rounded-full h-4">
                                        <div class="bg-orange-400 h-4 rounded-full" style="width: {{ $airline['bar_width'] }}%"></div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-right font-bold text-slate-800">{{ number_format($airline['count']) }}</td>
                                <td class="px-5 py-3 text-right font-bold text-slate-500">{{ $airline['percentage'] }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Routes -->
            <div class="bg-white rounded-[1.75rem] shadow-xl border border-slate-100 p-8">
                <h4 class="text-base font-black text-slate-800 uppercase tracking-tight mb-5">Top Flight Routes</h4>
                <div class="overflow-hidden rounded-xl border border-slate-200">
                    <table class="w-full text-base">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="text-left px-5 py-3 font-bold">Route</th>
                                <th class="text-left px-5 py-3 font-bold">Bar</th>
                                <th class="text-right px-5 py-3 font-bold">Jumlah</th>
                                <th class="text-right px-5 py-3 font-bold">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($topTrafficRoutes as $route)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3 font-semibold text-slate-700">{{ $route['route'] }}</td>
                                <td class="px-5 py-3">
                                    <div class="w-full bg-slate-200 rounded-full h-4">
                                        <div class="bg-orange-400 h-4 rounded-full" style="width: {{ $route['bar_width'] }}%"></div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-right font-bold text-slate-800">{{ number_format($route['count']) }}</td>
                                <td class="px-5 py-3 text-right font-bold text-slate-500">{{ $route['percentage'] }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </section>

    <!-- ========================= ENROUTE ========================= -->

    <section class="space-y-8 px-6 sm:px-10 lg:px-14 w-full pb-12">
        <div class="mb-2">
            <h2 class="flex items-center gap-4 text-[42px] font-black text-slate-800 tracking-tight">
                <span>Enroute Revenue</span>
                <span class="hidden sm:block w-px h-7 bg-slate-300"></span>
                <div class="inline-flex items-center gap-2">
                    <span class="inline-flex items-center bg-blue-50 text-blue-700 text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wide">Updated</span>
                    <span class="inline-flex items-center bg-slate-100 text-slate-600 text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wide">{{ $periodenroute }}</span>
                </div>
            </h2>
            <div class="mt-3 h-1.5 w-20 bg-gradient-to-r from-blue-600 to-sky-300 rounded-full"></div>
        </div>
        <!-- KPI Cards Enroute -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Enroute Movement -->
            <div class="bg-white rounded-[1.5rem] shadow-xl border border-slate-100 px-8 py-7 flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Enroute Movement</p>
                    <p class="text-5xl font-black text-slate-800 leading-none">
                        {{ number_format($enrouteMovement, 0, ',', '.') }}
                    </p>
                </div>
                <div class="h-16 w-16 rounded-2xl bg-sky-100 flex items-center justify-center text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                </div>
            </div>

            <!-- Total Route Unit -->
            <div class="bg-white rounded-[1.5rem] shadow-xl border border-slate-100 px-8 py-7 flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Total Route Unit</p>
                    <p class="text-5xl font-black text-slate-800 leading-none">
                        {{ number_format($totalRouteUnit, 0, ',', '.') }}
                    </p>
                </div>
                <div class="h-16 w-16 rounded-2xl bg-sky-100 flex items-center justify-center text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 104 0 2 2 0 00-4 0zm12 6a2 2 0 104 0 2 2 0 00-4 0zM4 18a2 2 0 104 0 2 2 0 00-4 0zm4-12l8 6-8 6"/>
                    </svg>
                </div>
            </div>

            <!-- Estimate Revenue -->
            <div class="bg-white rounded-[1.5rem] shadow-xl border border-slate-100 px-8 py-7 flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Estimate Total Revenue</p>
                    <p class="text-3xl font-black text-slate-800 leading-none">
                        Rp {{ number_format($totalRevenueIdr, 0, ',', '.') }}
                    </p>
                </div>
                <div class="h-16 w-16 rounded-2xl bg-sky-100 flex items-center justify-center text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-[1rem] border border-slate-100 shadow-xl shadow-slate-200/50 p-8 relative overflow-hidden">
                {{-- Header --}}
                <div class="text-center relative z-10 mb-4">
                    <h3 class="text-xl font-black text-slate-800 tracking-tight uppercase">Estimate Revenue Enroute Composition</h3>
                    <div class="h-1 w-14 bg-blue-600 mx-auto mt-3 rounded-full"></div>
                </div>

                {{-- Chart Container --}}
                <div class="relative h-72 w-full flex justify-center items-center group">
                    <canvas id="donutEnroute"
                            data-dom="{{ $domPercentage ?? 0 }}"
                            data-int="{{ $intPercentage ?? 0 }}">
                    </canvas>
                    {{-- Center Text --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total</span>
                    </div>
                </div>

                {{-- Financial Details --}}
                <div class="space-y-4 relative z-10">
                    {{-- IDR --}}
                    <div class="flex items-center justify-between p-5 bg-slate-50 hover:bg-blue-50 border border-transparent hover:border-blue-100 rounded-2xl transition-all duration-300 group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-600 shadow-lg shadow-blue-200 flex items-center justify-center text-white transition-transform group-hover:scale-110">
                                <span class="font-bold text-base">Rp</span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-0.5">Revenue in IDR</p>
                                <p class="text-lg font-black text-slate-800">Rp {{ number_format($enctotalIdrOriginal ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-black text-blue-600 bg-blue-50 px-4 py-1.5 rounded-full uppercase">{{ $domPercentage ?? 0 }}%</span>
                    </div>

                    {{-- USD --}}
                    <div class="flex items-center justify-between p-5 bg-slate-50 hover:bg-sky-50 border border-transparent hover:border-sky-100 rounded-2xl transition-all duration-300 group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-sky-400 shadow-lg shadow-sky-100 flex items-center justify-center text-white transition-transform group-hover:scale-110">
                                <span class="font-bold text-xl">$</span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-0.5">Revenue in USD</p>
                                <p class="text-lg font-black text-slate-800">$ {{ number_format($enctotalUsdOriginal ?? 0, 2, '.', ',') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-sky-500 uppercase">Equiv. IDR</p>
                            <p class="text-sm font-black text-slate-600">Rp {{ number_format($enctotalUsdConverted, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full bg-slate-300 animate-pulse"></div>
                            <span class="text-xs font-bold text-slate-400 uppercase italic">Rate: 1 USD = sesuai tanggal Dof</span>
                        </div>
                        <span class="text-xs font-bold text-slate-400 uppercase">{{ $recordDate ?? now()->format('d M Y') }}</span>

                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow p-6 md:col-span-2">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800">Top Airline – Enroute Performance</h3>
                        <p class="text-xs text-slate-400">Perbandingan intensitas pergerakan terhadap kontribusi pendapatan</p>
                    </div>
                    <div class="flex gap-2">
                        <span class="flex items-center gap-1 text-[12px] font-bold text-slate-400">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span> Movement
                        </span>
                        <span class="flex items-center gap-1 text-[12px] font-bold text-slate-400">
                            <span class="w-2 h-2 rounded-full bg-sky-300"></span> Revenue Value
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
                            @foreach($topAirlinesData as $i => $row)
                            <tr class="group hover:bg-slate-50/50 transition-all">
                                <td class="py-4 px-4 text-center">
                                    <span class="text-[11px] font-black text-slate-400 group-hover:text-blue-600">0{{ $i+1 }}</span>
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-700">
                                    {{ $row['name'] }}
                                    <div class="text-[10px] font-medium text-slate-400 normal-case">{{ number_format($row['flights']) }} Flights</div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="space-y-2">
                                        <div class="relative w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="absolute top-0 left-0 bg-blue-500 h-full rounded-full transition-all duration-1000" style="width: {{ $row['movement_pct'] }}%"></div>
                                        </div>
                                        <div class="relative w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="absolute top-0 left-0 bg-sky-300 h-full rounded-full transition-all duration-1000" style="width: {{ $row['revenue_pct'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="text-base font-black text-slate-900 leading-none">{{ $row['revenue'] }}</div>
                                    @if($row['revenue_pct'] > $row['movement_pct'])
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

        <!-- TOP AIRLINE -->
        <div class="grid grid-cols-1 gap-6"> 
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 lg:col-span-2">
                <h3 class="font-semibold mb-4">Revenue Trend – Enroute</h3>
                <div class="h-60">
                    <canvas id="lineEnroute" 
                            data-labels="{{ json_encode($enrouteDailyLabels) }}"
                            data-values="{{ json_encode($enrouteDailyChartValues) }}">
                    </canvas>
                </div>  
            </div>
        </div>
    </section>
    
    <!-- ========================= TERMINAL ========================= -->
    <section class="space-y-6">
        <p></p>
        <div class="mb-4">
            <h2 class="flex items-center gap-3 text-2xl font-semibold text-slate-800">
                <span>Terminal Revenue</span>
                <span class="hidden sm:block w-px h-5 bg-slate-300"></span>
               <div class="inline-flex items-center gap-1">
                    <span class="inline-flex items-center bg-blue-50 text-blue-700 text-[10px] font-semibold px-2 py-1.5 rounded-full uppercase tracking-wide leading-none">
                        Updated
                    </span>
                    <span class="inline-flex items-center bg-slate-100 text-slate-600 text-[10px] font-semibold px-2 py-1.5 rounded-full uppercase tracking-wide leading-none">
                        {{ $periodenroute }}
                    </span>
                </div>
            </h2>
            <div class="mt-2 h-1 w-16 bg-gradient-to-r from-emerald-600 to-green-100 rounded-full"></div>
        </div>
        <!-- KPI + DONUT -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-sm px-5 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Terminal Movement</p>
                    <p class="text-3xl font-black text-slate-800 leading-none">{{ number_format($terminalMovement) }}</p>
                </div>
                <div class="h-11 w-11 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm px-5 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total MTOW</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($terminalServiceUnit, 0, ',', '.') }}</p>
                </div>
                <div class="h-11 w-11 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm px-5 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Estimate Terminal - Total Revenue</p>
                    <p class="text-3xl font-black text-slate-800 leading-none">Rp {{ number_format($terminalRevenueIdr, 0, ',', '.') }}</p>
                </div>
                <div class="h-11 w-11 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10v1m0 10v1m9-6a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-[1rem] border border-slate-100 shadow-xl shadow-slate-200/50 p-8 relative overflow-hidden">
                {{-- Header --}}
                <div class="text-center relative z-10 mb-0"> {{-- Margin bottom dicabut --}}
                    <h3 class="text-m font-black text-slate-800 tracking-tight uppercase">Estimate Revenue Terminal Composition</h3>
                    <div class="h-0.5 w-12 bg-emerald-600 mx-auto mt-2 rounded-full"></div>
                </div>

                <div class="relative h-60 w-full flex justify-center items-center group -mt-2 -mb-2"> 
                    <canvas id="donutTerminal" 
                            data-dom="{{ $terminalDomPercentage ?? 0 }}" 
                            data-int="{{ $terminalIntPercentage ?? 0 }}">
                    </canvas>
                    {{-- Center Text --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.5em]">Total</span>
                    </div>
                </div>

                {{-- Financial Details Section --}}
                <div class="space-y-3 relative z-10">
                    {{-- Row 1: IDR (Domestic) --}}
                    <div class="flex items-center justify-between p-4 bg-slate-50/50 hover:bg-blue-50/50 border border-transparent hover:border-blue-100 rounded-[2rem] transition-all duration-300 group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 shadow-lg shadow-blue-200 flex items-center justify-center text-white transition-transform group-hover:scale-110">
                                <span class="font-bold text-sm tracking-tighter">Rp</span>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-left">Revenue in IDR</p>
                                <p class="text-base font-black text-slate-800 leading-tight">Rp {{ number_format($tnctotalIdrOriginal ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        {{-- Tambahan Persentase agar lebih informatif --}}
                        <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase">{{ $terminalDomPercentage ?? 0 }}%</span>
                    </div>

                    {{-- Row 2: USD (International) --}}
                    <div class="flex items-center justify-between p-4 bg-slate-50/50 hover:bg-sky-50/50 border border-transparent hover:border-sky-100 rounded-[2rem] transition-all duration-300 group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-400 shadow-lg shadow-sky-100 flex items-center justify-center text-white transition-transform group-hover:scale-110">
                                <span class="font-bold text-xl">$</span>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-left">Revenue in USD</p>
                                <p class="text-base font-black text-slate-800 leading-tight">$ {{ number_format($tnctotalUsdOriginal ?? 0, 2, '.', ',') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] font-bold text-sky-500 uppercase tracking-tighter">Equiv. to IDR</p>
                            <p class="text-[11px] font-black text-slate-600">Rp {{ number_format($tnctotalUsdConverted, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Footer Info --}}
                    <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-slate-300 animate-pulse"></div>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter italic">
                                Rate: 1 USD = Sesuai tanggal dof
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 opacity-60">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z" />
                            </svg>
                            <span class="text-[9px] font-bold text-slate-400 uppercase">
                                {{ $recordDate ?? now()->format('d M Y') }}
                            </span>
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
                            @forelse($topTerminalAirlinesData as $i => $row)
                            <tr class="group hover:bg-emerald-50/30 transition-all">
                                <td class="py-4 px-4 text-center">
                                    <span class="text-[11px] font-black text-slate-400 group-hover:text-emerald-600">0{{ $i+1 }}</span>
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-700">
                                    {{ $row['name'] }}
                                    <div class="text-[10px] font-medium text-slate-400">{{ number_format($row['flights']) }} Flights</div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="space-y-2">
                                        <div class="relative w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="absolute top-0 left-0 bg-emerald-500 h-full rounded-full" style="width: {{ $row['movement_pct'] }}%"></div>
                                        </div>
                                        <div class="relative w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="absolute top-0 left-0 bg-teal-400 h-full rounded-full" style="width: {{ $row['revenue_pct'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <!-- <td class="py-4 px-4 text-right">
                                    <div class="text-base font-black text-slate-900 leading-none">{{ $row['revenue'] }}</div>
                                    <span class="text-[9px] font-bold {{ $row['revenue_pct'] > $row['movement_pct'] ? 'text-teal-600' : 'text-emerald-600' }} uppercase tracking-tighter">
                                        {{ $row['revenue_pct'] > $row['movement_pct'] ? 'High Yield' : 'High Volume' }}
                                    </span>
                                </td> -->
                                <td class="py-4 px-4 text-right">
                                        <div class="text-base font-black text-slate-900 leading-none">{{ $row['revenue'] }}</div>
                                    @if($row['revenue_pct'] > $row['movement_pct'])
                                        <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-tighter">High Yield</span>
                                    @else
                                        <span class="text-[9px] font-bold text-blue-600 uppercase tracking-tighter">High Volume</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">Belum ada data terminal</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TOP AIRLINE -->
        <div class="bg-white rounded-2xl shadow p-6 md:col-span-2">
            <h3 class="font-semibold mb-4 text-slate-800">Revenue Trend – Terminal</h3>
            <div class="h-60">
                <canvas id="lineTerminal" 
                        data-labels="{{ json_encode($terminalDailyLabels) }}"
                        data-values="{{ json_encode($terminalDailyChartValues) }}">
                </canvas>
            </div>
        </div> 
    </section>
    
</div>
<!-- ========================= CHART SCRIPT ========================= -->
<script>
// 1. Registrasi Plugin
Chart.register(ChartDataLabels);

const globalDonutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    layout: { padding: 20 },
    plugins: {
        legend: {
            position: 'bottom',
            labels: { usePointStyle: true, padding: 20, font: { size: 12 } }
        },
        datalabels: {
            color: (context) => context.dataIndex === 0 ? '#ffffff' : '#1e293b',
            anchor: 'center',
            align: 'center',
            font: { weight: 'bold', size: 14 },
            formatter: (value, context) => {
                let sum = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                return sum > 0 ? ((value / sum) * 100).toFixed(1) + "%" : "0%";
            }
        }
    }
};

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, datalabels: { display: false } },
    scales: {
        y: { beginAtZero: true }
    }
};

// ==================== DONUT TRAFFIC ====================
const donutTraffic = document.getElementById('donutTraffic');
if (donutTraffic) {
    new Chart(donutTraffic, {
        type: 'doughnut',
        data: {
            labels: ['International', 'Domestic'],
            datasets: [{
                data: [
                    donutTraffic.dataset.international ? parseFloat(donutTraffic.dataset.international) : 0,
                    donutTraffic.dataset.domestic ? parseFloat(donutTraffic.dataset.domestic) : 0
                ],
                backgroundColor: ['#ebac25', '#fdea93'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '45%',
            plugins: {
                legend: { position: 'bottom' },
                datalabels: {
                    color: '#1e293b',
                    formatter: (value) => value + '%'
                }
            }
        }
    });
}

// ==================== DONUT ENROUTE ====================
const donutEnroute = document.getElementById('donutEnroute');
if (donutEnroute) {
    const domValue = donutEnroute.dataset.dom ? parseFloat(donutEnroute.dataset.dom) : 0;
    const intValue = donutEnroute.dataset.int ? parseFloat(donutEnroute.dataset.int) : 0;

    new Chart(donutEnroute, {
        type: 'doughnut',
        data: {
            labels: ['Domestic (IDR)', 'International (USD)'],
            datasets: [{
                data: [domValue, intValue],
                backgroundColor: ['#2563eb', '#93c5fd'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: { ...globalDonutOptions, cutout: '55%' }
    });
}

// ==================== DONUT TERMINAL ====================
const donutTerminal = document.getElementById('donutTerminal');
if (donutTerminal) {
    const domValue = donutTerminal.dataset.dom ? parseFloat(donutTerminal.dataset.dom) : 0;
    const intValue = donutTerminal.dataset.int ? parseFloat(donutTerminal.dataset.int) : 0;

    new Chart(donutTerminal, {
        type: 'doughnut',
        data: {
            labels: ['Domestic (IDR)', 'International (USD)'],
            datasets: [{
                data: [domValue, intValue],
                backgroundColor: ['#16a34a', '#86efac'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: { ...globalDonutOptions, cutout: '55%' }
    });
}

// ==================== LINE ENROUTE (PER HARI) ====================
const lineEnroute = document.getElementById('lineEnroute');
if (lineEnroute) {
    const labels = JSON.parse(lineEnroute.dataset.labels || '[]');
    const values = JSON.parse(lineEnroute.dataset.values || '[]');
    
    new Chart(lineEnroute, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                borderWidth: 3,
                tension: 0.4,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(30, 103, 238, 0.23)',
                fill: true,
                pointRadius: 2,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false }, 
                datalabels: { display: false } 
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    ticks: { callback: function(value) { return value + ' Jt'; } }
                },
                x: {
                    ticks: {
                        maxTicksLimit: 15,
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

// // ==================== LINE TERMINAL (PER HARI) ====================
const lineTerminal = document.getElementById('lineTerminal');
if (lineTerminal) {
    const labels = JSON.parse(lineTerminal.dataset.labels || '[]');
    const values = JSON.parse(lineTerminal.dataset.values || '[]');
    
    new Chart(lineTerminal, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                borderWidth: 3,
                tension: 0.4,
                borderColor: '#16a34a',
                backgroundColor: 'rgba(22, 163, 74, 0.12)',
                fill: true,
                pointRadius: 2,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false }, 
                datalabels: { display: false } 
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    ticks: { callback: function(value) { return value + ' Jt'; } }
                },
                x: {
                    ticks: {
                        maxTicksLimit: 15,
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

// ==================== LINE TRAFFIC (Arrival vs Departure) ====================
const lineTraffic = document.getElementById('lineEnroute2');
if (lineTraffic) {
    const arrivalData = JSON.parse(lineTraffic.dataset.arrival || '[]');
    const departureData = JSON.parse(lineTraffic.dataset.departure || '[]');
    const labels = JSON.parse(lineTraffic.dataset.labels || '[]'); // TAMBAHKAN INI
    
    new Chart(lineTraffic, {
        type: 'line',
        data: {
            labels: labels, // Gunakan labels dinamis dari controller
            datasets: [
                {
                    label: 'Arrival',
                    data: arrivalData,
                    borderColor: '#ebac25',
                    backgroundColor: 'rgba(235, 172, 37, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#ebac25',
                    pointBorderColor: '#ffffff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Departure',
                    data: departureData,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#f97316',
                    pointBorderColor: '#ffffff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#1e293b',
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw + ' flights';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(226, 232, 240, 0.6)' },
                    ticks: { 
                        callback: function(value) { 
                            return value.toLocaleString() + ' flights'; 
                        },
                        color: '#64748b'
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { 
                        color: '#64748b',
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}
</script>
</x-app-layout>