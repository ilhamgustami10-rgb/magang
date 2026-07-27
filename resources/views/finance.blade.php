<x-app-layout>
@php
// Pastikan $financeData dari controller sesuai spesifikasi
$financeData = $financeData ?? [];
$firstTab = !empty($financeData) ? array_key_first($financeData) : '';

if (!function_exists('fmtCard')) {
    function fmtCard($v) {
        return 'Rp ' . number_format(round($v), 0, ',', '.');
    }
}
if (!function_exists('fmtShort')) {
    function fmtShort($v) {
        if ($v >= 1e9) return number_format($v/1e9, 1, ',', '.') . ' M';
        if ($v >= 1e6) return number_format($v/1e6, 1, ',', '.') . ' Jt';
        if ($v >= 1e3) return number_format($v/1e3, 0, ',', '.') . ' Rb';
        return $v;
    }
}
@endphp

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
// HD Render Configuration
Chart.defaults.devicePixelRatio = Math.max(window.devicePixelRatio||1, 2);
Chart.defaults.font.size = 13;
Chart.defaults.color = '#475569';
Chart.defaults.font.family = 'Segoe UI, system-ui, sans-serif';

// Custom plugin to draw text in the middle of donut
const donutCenterTextPlugin = {
    id: 'donutCenterText',
    afterDraw: (chart) => {
        if (chart.config.type !== 'doughnut') return;
        if (!chart.config.options.plugins.donutCenterText) return;
        const opts = chart.config.options.plugins.donutCenterText;
        if (!opts.text) return;
        
        const ctx = chart.ctx;
        const width = chart.width;
        const height = chart.height;
        ctx.restore();
        
        // Draw percentage
        ctx.font = "900 36px 'Segoe UI', system-ui";
        ctx.textBaseline = "middle";
        ctx.textAlign = "center";
        ctx.fillStyle = '#0f172a';
        let textX = width / 2;
        let textY = height / 2 - 10;
        ctx.fillText(opts.text, textX, textY);
        
        // Draw label
        ctx.font = "600 14px 'Segoe UI', system-ui";
        ctx.fillStyle = '#64748b';
        ctx.fillText(opts.label, textX, textY + 28);
        ctx.save();
    }
};
Chart.register(donutCenterTextPlugin);

document.addEventListener('alpine:init', () => {
@foreach($financeData as $branchName => $branch)
@php
    $bid = str_replace([' ','-'], '_', Str::slug($branchName));
@endphp
    Alpine.data('fin_{{ $bid }}', () => ({
        rawData: {!! json_encode($branch['items'] ?? []) !!},
        selRC: null, // index for Release vs Consume
        selCA: null, // index for Consume vs Available
        openRC: false,
        openCA: false,
        chartRC: null,
        chartCA: null,
        chartBarTopC: null,
        chartBarTopA: null,
        searchQuery: '',
        sortCol: 'consume',
        sortDir: 'desc',
        
        init() {
            if(this.rawData.length > 0) {
                this.selRC = 0;
                this.selCA = 0;
            }
            // watch tab visibility
            this.$watch('activeTab', tab => {
                if(tab === '{{ $branchName }}') {
                    this.$nextTick(() => { this.renderAllCharts(); });
                }
            });
            // Initial render if active tab
            if(this.activeTab === '{{ $branchName }}') {
                this.$nextTick(() => { this.renderAllCharts(); });
            }
        },
        
        renderAllCharts() {
            this.renderDonutRC();
            this.renderDonutCA();
            this.renderBarC();
            this.renderBarA();
        },
        
        destroyChart(chartVar) {
            if(this[chartVar]) { this[chartVar].destroy(); this[chartVar] = null; }
        },
        
        fmt(v) { return 'Rp ' + Math.round(v).toLocaleString('id-ID'); },
        
        shortFmt(v) {
            let num = parseFloat(v);
            if (num >= 1e9) return (num/1e9).toLocaleString('id-ID',{minimumFractionDigits:1,maximumFractionDigits:1}) + ' M';
            if (num >= 1e6) return (num/1e6).toLocaleString('id-ID',{minimumFractionDigits:1,maximumFractionDigits:1}) + ' Jt';
            if (num >= 1e3) return (num/1e3).toLocaleString('id-ID',{maximumFractionDigits:0}) + ' Rb';
            return num;
        },
        
        pct(a,b) { return b > 0 ? (a/b*100) : 0; },
        
        renderDonutRC() {
            if(this.selRC === null || !this.rawData[this.selRC]) return;
            this.destroyChart('chartRC');
            const item = this.rawData[this.selRC];
            const p = this.pct(item.consume, item.release);
            
            const ctx = document.getElementById('rc_{{ $bid }}');
            if(!ctx) return;
            this.chartRC = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Release Budget', 'Total Consume'],
                    datasets: [{
                        data: [item.release, item.consume],
                        backgroundColor: ['#1e40af', '#93c5fd'],
                        borderWidth: 3,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                        tooltip: { callbacks: { label: c => c.label + ': ' + this.fmt(c.raw) } },
                        donutCenterText: { text: p.toFixed(1)+'%', label: 'Serapan' }
                    }
                }
            });
        },
        
        renderDonutCA() {
            if(this.selCA === null || !this.rawData[this.selCA]) return;
            this.destroyChart('chartCA');
            const item = this.rawData[this.selCA];
            const p = this.pct(item.available, item.release);
            
            const ctx = document.getElementById('ca_{{ $bid }}');
            if(!ctx) return;
            this.chartCA = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Total Consume', 'Available Budget'],
                    datasets: [{
                        data: [item.consume, item.available],
                        backgroundColor: ['#1e40af', '#93c5fd'],
                        borderWidth: 3,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                        tooltip: { callbacks: { label: c => c.label + ': ' + this.fmt(c.raw) } },
                        donutCenterText: { text: p.toFixed(1)+'%', label: 'Tersisa' }
                    }
                }
            });
        },
        
        renderBarC() {
            this.destroyChart('chartBarTopC');
            const ctx = document.getElementById('bar_c_{{ $bid }}');
            if(!ctx) return;
            const top = [...this.rawData].sort((a,b)=>b.consume - a.consume);
            
            this.chartBarTopC = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: top.map(x=>x.name),
                    datasets: [{
                        data: top.map(x=>x.consume),
                        backgroundColor: '#f97316',
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: c => this.fmt(c.raw) } }
                    },
                    scales: { x: { ticks: { callback: v => this.shortFmt(v) } } }
                }
            });
        },
        
        renderBarA() {
            this.destroyChart('chartBarTopA');
            const ctx = document.getElementById('bar_a_{{ $bid }}');
            if(!ctx) return;
            const top = [...this.rawData].sort((a,b)=>b.available - a.available);
            
            this.chartBarTopA = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: top.map(x=>x.name),
                    datasets: [{
                        data: top.map(x=>x.available),
                        backgroundColor: '#16a34a',
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: c => this.fmt(c.raw) } }
                    },
                    scales: { x: { ticks: { callback: v => this.shortFmt(v) } } }
                }
            });
        },
        
        get filteredRows() {
            let res = this.rawData;
            if(this.searchQuery) {
                const q = this.searchQuery.toLowerCase();
                res = res.filter(x => x.name.toLowerCase().includes(q) || x.code.toLowerCase().includes(q));
            }
            return res.sort((a,b) => {
                let va, vb;
                if (this.sortCol === 'serapan_pct') {
                    va = this.pct(a.consume, a.release);
                    vb = this.pct(b.consume, b.release);
                } else {
                    va = a[this.sortCol];
                    vb = b[this.sortCol];
                }
                return this.sortDir === 'desc' ? vb - va : va - vb;
            });
        },
        
        setSort(col) {
            if(this.sortCol === col) this.sortDir = this.sortDir === 'desc' ? 'asc' : 'desc';
            else { this.sortCol = col; this.sortDir = 'desc'; }
        },
        
        sortIcon(col) {
            if(this.sortCol !== col) return '↕';
            return this.sortDir === 'desc' ? '↓' : '↑';
        }
    }));
@endforeach
});
</script>

<div class="w-full" x-data="{ activeTab: '{{ $firstTab }}' }">

    <!-- Tab Navigasi Cabang -->
    <div class="px-4 sm:px-6 lg:px-8 mb-6">
        <div class="flex gap-2 overflow-x-auto pb-2" style="scrollbar-width: none;">
            @foreach($financeData as $branchName => $branch)
            <button @click="activeTab = '{{ $branchName }}'"
                :class="activeTab === '{{ $branchName }}' ? 'bg-blue-600 text-white shadow-lg border-blue-600 scale-105' : 'bg-white text-slate-700 hover:bg-slate-100 border-slate-200'"
                class="px-5 py-2 rounded-full font-bold text-sm transition-all whitespace-nowrap border">
                {{ $branchName }}
            </button>
            @endforeach
        </div>
    </div>

    <div class="px-4 sm:px-6 lg:px-8 w-full">


    @foreach($financeData as $branchName => $branch)
    @php
        $bid = str_replace([' ','-'], '_', Str::slug($branchName));
        $rkap = $branch['rkap'] ?? 0;
        $rel = $branch['release'] ?? 0;
        $com = $branch['commitment'] ?? 0;
        $cons = $branch['consume'] ?? 0;
        $avail = $branch['available'] ?? 0;
        $sRPct = $rkap > 0 ? ($rel/$rkap*100) : 0;
        $sCPct = $rel > 0 ? ($cons/$rel*100) : 0;
        $sComPct = $rel > 0 ? ($com/$rel*100) : 0;
        $sAPct = $rel > 0 ? ($avail/$rel*100) : 0;
    @endphp
    
    <div x-show="activeTab === '{{ $branchName }}'" x-data="fin_{{ $bid }}" style="display:none;" class="space-y-6">
        
        <!-- KPI Cards (2 Rows) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- RKAP -->
            <div class="bg-white rounded-[18px] p-6 border-l-[6px] border-l-[#0f172a] shadow-sm flex flex-col justify-between">
                <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">RKAP</p>
                <p class="text-3xl font-black text-slate-800 mt-3">{{ fmtCard($rkap) }}</p>
            </div>
            <!-- Release -->
            <div class="bg-white rounded-[18px] p-6 border-l-[6px] border-l-[#2563eb] shadow-sm flex flex-col justify-between">
                <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">Release Budget</p>
                <p class="text-3xl font-black text-slate-800 mt-3">{{ fmtCard($rel) }}</p>
                <p class="text-sm font-semibold text-blue-600 mt-2">{{ number_format($sRPct,1,',','.') }}% dari RKAP</p>
            </div>
            <!-- Consume -->
            <div class="bg-white rounded-[18px] p-6 border-l-[6px] border-l-[#f97316] shadow-sm flex flex-col justify-between">
                <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">Total Consume</p>
                <p class="text-3xl font-black text-slate-800 mt-3">{{ fmtCard($cons) }}</p>
                <p class="text-sm font-semibold text-orange-500 mt-2">{{ number_format($sCPct,1,',','.') }}% serapan</p>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Commitment -->
            <div class="bg-white rounded-[18px] p-6 border-l-[6px] border-l-[#7c3aed] shadow-sm flex flex-col justify-between">
                <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">Commitment</p>
                <p class="text-3xl font-black text-slate-800 mt-3">{{ fmtCard($com) }}</p>
                <p class="text-sm font-semibold text-violet-500 mt-2">{{ number_format($sComPct,1,',','.') }}% dari Release</p>
            </div>
            <!-- Available -->
            <div class="bg-white rounded-[18px] p-6 border-l-[6px] border-l-[#16a34a] shadow-sm flex flex-col justify-between">
                <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">Available Budget</p>
                <p class="text-3xl font-black text-slate-800 mt-3">{{ fmtCard($avail) }}</p>
                <p class="text-sm font-semibold text-emerald-600 mt-2">{{ number_format($sAPct,1,',','.') }}% dari Release</p>
            </div>
        </div>

        <!-- Serapan Progress -->
        <div class="bg-white rounded-[18px] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row items-center gap-6">
            <div class="flex-shrink-0 text-center md:text-left">
                <p class="text-4xl font-black text-blue-600">{{ number_format($sCPct,1,',','.') }}%</p>
                <p class="text-xs font-bold text-slate-400 uppercase mt-1">Serapan Anggaran</p>
            </div>
            <div class="flex-1 w-full">
                <div class="w-full bg-slate-100 h-4 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 transition-all duration-500" style="width: {{ min(100, $sCPct) }}%"></div>
                </div>
                <p class="text-right text-sm font-bold text-slate-600 mt-2">{{ fmtCard($cons) }} dari {{ fmtCard($rel) }}</p>
            </div>
        </div>

        <!-- Donut Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- RC -->
            <div class="bg-white rounded-[18px] p-6 shadow-sm border border-slate-100 relative flex flex-col">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-lg font-black text-slate-800">Release vs Total Consume</h2>
                    <div class="relative" @click.outside="openRC=false">
                        <button @click="openRC=!openRC" class="text-xs font-bold bg-slate-100 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-200 transition">
                            Filter: <span x-text="rawData[selRC]?.name || 'Pilih'"></span>
                        </button>
                        <div x-show="openRC" class="absolute right-0 top-10 w-64 bg-white border border-slate-200 shadow-xl rounded-xl p-3 z-10 max-h-60 overflow-y-auto">
                            <template x-for="(itm, i) in rawData" :key="i">
                                <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 cursor-pointer rounded">
                                    <input type="radio" name="rc_{{ $bid }}" :value="i" x-model="selRC" @change="renderDonutRC(); openRC=false" class="accent-blue-600">
                                    <span class="text-xs text-slate-700 font-semibold" x-text="itm.name"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="h-[480px] w-full flex-1"><canvas id="rc_{{ $bid }}"></canvas></div>
            </div>
            
            <!-- CA -->
            <div class="bg-white rounded-[18px] p-6 shadow-sm border border-slate-100 relative flex flex-col">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-lg font-black text-slate-800">Consume vs Available Budget</h2>
                    <div class="relative" @click.outside="openCA=false">
                        <button @click="openCA=!openCA" class="text-xs font-bold bg-slate-100 px-3 py-1.5 rounded-md text-slate-600 hover:bg-slate-200 transition">
                            Filter: <span x-text="rawData[selCA]?.name || 'Pilih'"></span>
                        </button>
                        <div x-show="openCA" class="absolute right-0 top-10 w-64 bg-white border border-slate-200 shadow-xl rounded-xl p-3 z-10 max-h-60 overflow-y-auto">
                            <template x-for="(itm, i) in rawData" :key="i">
                                <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 cursor-pointer rounded">
                                    <input type="radio" name="ca_{{ $bid }}" :value="i" x-model="selCA" @change="renderDonutCA(); openCA=false" class="accent-blue-600">
                                    <span class="text-xs text-slate-700 font-semibold" x-text="itm.name"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="h-[480px] w-full flex-1"><canvas id="ca_{{ $bid }}"></canvas></div>
            </div>
        </div>
        
        <!-- Bar Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-[18px] p-6 shadow-sm border border-slate-100 flex flex-col overflow-y-auto" style="max-height: 800px;">
                <h2 class="text-lg font-black text-slate-800 mb-4 sticky top-0 bg-white z-10 pb-2 border-b border-slate-100">Konsumsi Tertinggi</h2>
                <div :style="'height: ' + Math.max(460, rawData.length * 35) + 'px'" class="w-full mt-2"><canvas id="bar_c_{{ $bid }}"></canvas></div>
            </div>
            <div class="bg-white rounded-[18px] p-6 shadow-sm border border-slate-100 flex flex-col overflow-y-auto" style="max-height: 800px;">
                <h2 class="text-lg font-black text-slate-800 mb-4 sticky top-0 bg-white z-10 pb-2 border-b border-slate-100">Sisa Anggaran Tertinggi</h2>
                <div :style="'height: ' + Math.max(460, rawData.length * 35) + 'px'" class="w-full mt-2"><canvas id="bar_a_{{ $bid }}"></canvas></div>
            </div>
        </div>

        <!-- Detail Table -->
        <div class="bg-white rounded-[18px] p-6 shadow-sm border border-slate-100">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <h2 class="text-lg font-black text-slate-800">Detail Funds Center</h2>
                <input type="text" x-model="searchQuery" placeholder="Cari item..." class="text-sm border border-slate-200 rounded-lg px-4 py-2 w-full sm:w-64 bg-slate-50 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap min-w-[800px]">
                    <thead>
                        <tr class="text-xs font-bold text-slate-400 uppercase border-b border-slate-200">
                            <th class="py-3 px-4 cursor-pointer hover:text-slate-700 transition select-none" @click="setSort('name')">
                                Funds Center <span class="ml-1 inline-block text-[10px]" x-text="sortIcon('name')"></span>
                            </th>
                            <th class="py-3 px-4 text-right cursor-pointer hover:text-slate-700 transition select-none" @click="setSort('rkap')">
                                RKAP <span class="ml-1 inline-block text-[10px]" x-text="sortIcon('rkap')"></span>
                            </th>
                            <th class="py-3 px-4 text-right cursor-pointer hover:text-slate-700 transition select-none" @click="setSort('release')">
                                Release <span class="ml-1 inline-block text-[10px]" x-text="sortIcon('release')"></span>
                            </th>
                            <th class="py-3 px-4 text-right cursor-pointer hover:text-slate-700 transition select-none" @click="setSort('consume')">
                                Consume <span class="ml-1 inline-block text-[10px]" x-text="sortIcon('consume')"></span>
                            </th>
                            <th class="py-3 px-4 text-right cursor-pointer hover:text-slate-700 transition select-none" @click="setSort('available')">
                                Available <span class="ml-1 inline-block text-[10px]" x-text="sortIcon('available')"></span>
                            </th>
                            <th class="py-3 px-4 text-right cursor-pointer hover:text-slate-700 transition select-none" @click="setSort('commitment')">
                                Commit <span class="ml-1 inline-block text-[10px]" x-text="sortIcon('commitment')"></span>
                            </th>
                            <th class="py-3 px-4 w-48 cursor-pointer hover:text-slate-700 transition select-none" @click="setSort('serapan_pct')">
                                Serapan <span class="ml-1 inline-block text-[10px]" x-text="sortIcon('serapan_pct')"></span>
                            </th>
                            <th class="py-3 px-4 text-right cursor-pointer hover:text-slate-700 transition select-none" @click="setSort('serapan_pct')">
                                % <span class="ml-1 inline-block text-[10px]" x-text="sortIcon('serapan_pct')"></span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="r in filteredRows" :key="r.code">
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4">
                                    <p class="font-bold text-slate-700" x-text="r.name"></p>
                                    <p class="text-xs text-slate-400" x-text="r.code"></p>
                                </td>
                                <td class="py-3 px-4 text-right font-medium text-slate-600" x-text="fmt(r.rkap)"></td>
                                <td class="py-3 px-4 text-right font-medium text-slate-600" x-text="fmt(r.release)"></td>
                                <td class="py-3 px-4 text-right font-medium text-slate-600" x-text="fmt(r.consume)"></td>
                                <td class="py-3 px-4 text-right font-medium text-slate-600" x-text="fmt(r.available)"></td>
                                <td class="py-3 px-4 text-right font-medium text-slate-600" x-text="fmt(r.commitment)"></td>
                                <td class="py-3 px-4">
                                    <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                                        <div class="h-full transition-all duration-300" :class="pct(r.consume,r.release)>=90 ? 'bg-[#dc2626]' : pct(r.consume,r.release)>=60 ? 'bg-[#16a34a]' : pct(r.consume,r.release)>=30 ? 'bg-[#2563eb]' : 'bg-[#94a3b8]'" :style="'width:'+Math.min(100, pct(r.consume,r.release))+'%'"></div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-700" x-text="pct(r.consume,r.release).toFixed(1)+'%'"></td>
                            </tr>
                        </template>
                        <template x-if="filteredRows.length === 0">
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400 font-medium">Data tidak ditemukan.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
    @endforeach
    
</div>
</x-app-layout>
