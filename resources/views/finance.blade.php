<x-app-layout>
@php
$dummyFinanceData = [
    'AirNav Juanda (Utama)' => [
        [ 'item' => '5102021000 H-PMLH Bang Lapangan',     'rkap' => 68834756,   'release_budget' => 230834756,  'commitment' => 75214260,  'total_consume' => 149593132,  'available_budget' => 81241624  ],
        [ 'item' => '5102022000 H-PMLH Bang Gedung',        'rkap' => 500000000,  'release_budget' => 460000000,  'commitment' => 0,         'total_consume' => 550000,     'available_budget' => 459450000 ],
        [ 'item' => '5102024001 H-PMLH Air-Ground',         'rkap' => 6000000000, 'release_budget' => 5380026549, 'commitment' => 209910000, 'total_consume' => 4473063938, 'available_budget' => 906962611 ],
        [ 'item' => '5102024003 H-PMLH Navigation',         'rkap' => 80000000,   'release_budget' => 75164660,   'commitment' => 0,         'total_consume' => 0,          'available_budget' => 75164660  ],
        [ 'item' => '5102024005 H-PMLH Surveillance',       'rkap' => 40000000,   'release_budget' => 38068396,   'commitment' => 0,         'total_consume' => 0,          'available_budget' => 38068396  ],
        [ 'item' => '5102026000 H-PMLH Inst&Jaringan',      'rkap' => 350000000,  'release_budget' => 318681000,  'commitment' => 117694000, 'total_consume' => 120714000,  'available_budget' => 197967000 ],
        [ 'item' => '5102028000 H-PMLH Kebersihan',         'rkap' => 457454023,  'release_budget' => 457454023,  'commitment' => 102306769, 'total_consume' => 411121073,  'available_budget' => 46332950  ],
        [ 'item' => '5102030004 H-Beban Perlengkapan BBM',  'rkap' => 84147498,   'release_budget' => 84147498,   'commitment' => 3968000,   'total_consume' => 68899528,   'available_budget' => 15247970  ],
        [ 'item' => '5102040002 H-Utilitas Listrik',        'rkap' => 839446689,  'release_budget' => 839446689,  'commitment' => 2053192,   'total_consume' => 544517668,  'available_budget' => 294929021 ],
        [ 'item' => '5103030001 H-Outsourcing TK',          'rkap' => 1238801303, 'release_budget' => 1238801303, 'commitment' => 0,         'total_consume' => 843518220,  'available_budget' => 395283083 ],
    ],
    'Cabang Surabaya' => [
        [ 'item' => '5102021000 H-PMLH Bang Lapangan',     'rkap' => 1200000,   'release_budget' => 1200000,   'commitment' => 300000,  'total_consume' => 600000,   'available_budget' => 600000   ],
        [ 'item' => '5102027000 H-PMLH Alat Angkut',       'rkap' => 1386000,   'release_budget' => 1386000,   'commitment' => 346500,  'total_consume' => 693000,   'available_budget' => 693000   ],
        [ 'item' => '5102028000 H-PMLH Kebersihan',        'rkap' => 120896,    'release_budget' => 120896,    'commitment' => 0,       'total_consume' => 75000,    'available_budget' => 45896    ],
        [ 'item' => '5102030004 H-Beban Perlengkapan BBM', 'rkap' => 2520000,   'release_budget' => 2520000,   'commitment' => 560000,  'total_consume' => 1620000,  'available_budget' => 900000   ],
        [ 'item' => '5102030005 H-PLKPN Kep. ATK&CU',     'rkap' => 3069559,   'release_budget' => 3069559,   'commitment' => 0,       'total_consume' => 650000,   'available_budget' => 2419559  ],
        [ 'item' => '5102030099 H-PLKPN Kep. Lain2',      'rkap' => 2880862,   'release_budget' => 2880862,   'commitment' => 755982,  'total_consume' => 1755000,  'available_budget' => 1125862  ],
        [ 'item' => '5102040002 H-Utilitas Listrik',       'rkap' => 103565409, 'release_budget' => 103565409, 'commitment' => 600073,  'total_consume' => 77163525, 'available_budget' => 26401884 ],
        [ 'item' => '5102040003 H-Utilitas Kom/Telp',      'rkap' => 9995433,   'release_budget' => 9995433,   'commitment' => 210000,  'total_consume' => 7130606,  'available_budget' => 2864827  ],
    ],
    'Cabang Banyuwangi' => [
        [ 'item' => '5102021000 H-PMLH Bang Lapangan',     'rkap' => 800000,    'release_budget' => 800000,    'commitment' => 100000,  'total_consume' => 450000,   'available_budget' => 350000   ],
        [ 'item' => '5102030004 H-Beban Perlengkapan BBM', 'rkap' => 1520000,   'release_budget' => 1520000,   'commitment' => 260000,  'total_consume' => 820000,   'available_budget' => 700000   ],
        [ 'item' => '5102040002 H-Utilitas Listrik',       'rkap' => 63565409,  'release_budget' => 63565409,  'commitment' => 200073,  'total_consume' => 41163525, 'available_budget' => 22401884 ],
        [ 'item' => '5103030001 H-Outsourcing TK',         'rkap' => 15880130,  'release_budget' => 15880130,  'commitment' => 0,       'total_consume' => 12435182, 'available_budget' => 3444948  ],
    ]
];

// Gunakan data contoh untuk visualisasi awal; data CSV akan menggantikannya saat tersedia.
$financeData = !empty($financeData) ? $financeData : $dummyFinanceData;

if (!function_exists('fmtCard')) {
    function fmtCard($v) {
        return 'Rp ' . number_format($v, 0, ',', '.');
    }
}
if (!function_exists('fmtShort')) {
    function fmtShort($v) {
        if ($v >= 1e9) return 'Rp ' . number_format($v/1e9, 2, '.', ',') . ' M';
        if ($v >= 1e6) return 'Rp ' . number_format($v/1e6, 2, '.', ',') . ' Jt';
        return 'Rp ' . number_format($v, 0, ',', '.');
    }
}
if (!function_exists('itemShort')) {
    function itemShort($s) {
        $p = explode(' ', $s); array_shift($p);
        return implode(' ', $p);
    }
}
@endphp

{{-- ============================================================
     ALPINE INIT — register one component per branch
     ============================================================ --}}
<script>
document.addEventListener('alpine:init', () => {

@foreach($financeData as $branchName => $data)
@php
    $totalRkap       = array_sum(array_column($data,'rkap'));
    $totalRelease    = array_sum(array_column($data,'release_budget'));
    $totalCommitment = array_sum(array_column($data,'commitment'));
    $totalConsume    = array_sum(array_column($data,'total_consume'));
    $totalAvailable  = array_sum(array_column($data,'available_budget'));
    $releasePct      = $totalRkap    > 0 ? round($totalRelease/$totalRkap*100,1)    : 0;
    $consumePct      = $totalRelease > 0 ? round($totalConsume/$totalRelease*100,1)  : 0;
    $availPct        = $totalRelease > 0 ? round($totalAvailable/$totalRelease*100,1): 0;
    $commitPct       = $totalRelease > 0 ? round($totalCommitment/$totalRelease*100,1): 0;
    $bid = str_replace('-','_', Str::slug($branchName));
@endphp

    Alpine.data('fin_{{ $bid }}', () => ({
        /* ---- raw data from PHP ---- */
        rawData : {!! json_encode($data) !!},
        totalConsume    : {{ $totalConsume }},
        totalAvailable  : {{ $totalAvailable }},
        totalRelease    : {{ $totalRelease }},

        /* ---- filter: indices of items INCLUDED in each pie ---- */
        releaseConsumeSel : {!! json_encode(range(0, count($data)-1)) !!},
        consumeAvailSel  : {!! json_encode(range(0, count($data)-1)) !!},

        /* ---- filter panel open state ---- */
        releaseConsumeOpen : false,
        consumeAvailOpen   : false,

        /* ---- table sort ---- */
        sortCol : 'total_consume',
        sortDir : 'desc',

        /* ---- chart instances ---- */
        releaseConsumeChart : null,
        consumeAvailChart   : null,
        availChart          : null,
        consumeBarChart     : null,

        /* ---- colour palette (matching traffic tab) ---- */
        PIE_COLORS : ['#2563eb','#16a34a','#ebac25','#dc2626','#7c3aed','#ea580c',
                      '#0891b2','#d946ef','#ca8a04','#059669'],

        /* ---- helpers ---- */
        shortName(s) {
            const p = s.split(' '); p.shift();
            const n = p.join(' ');
            return n.length > 15 ? n.substring(0,15)+'…' : n;
        },
        fmtRp(v) {
            if (v >= 1e9) return 'Rp '+(v/1e9).toFixed(2)+' M';
            if (v >= 1e6) return 'Rp '+(v/1e6).toFixed(1)+' Jt';
            return 'Rp '+new Intl.NumberFormat('id-ID').format(v);
        },
        scaleRp(v) {
            if (v >= 1e9) return (v/1e9).toFixed(1)+'M';
            if (v >= 1e6) return (v/1e6).toFixed(0)+'Jt';
            if (v >= 1e3) return (v/1e3).toFixed(0)+'rb';
            return v;
        },
        pct(c,r){ return r>0 ? Math.round(c/r*100) : 0; },

        /* ---- computed sorted rows ---- */
        get sortedRows() {
            return [...this.rawData].sort((a,b) => {
                const va = a[this.sortCol]||0, vb = b[this.sortCol]||0;
                return this.sortDir==='desc' ? vb-va : va-vb;
            });
        },
        setSort(col) {
            if (this.sortCol===col) this.sortDir = this.sortDir==='desc'?'asc':'desc';
            else { this.sortCol=col; this.sortDir='desc'; }
        },
        sortIcon(col) {
            if (this.sortCol!==col) return '↕';
            return this.sortDir==='desc' ? '↓' : '↑';
        },

        /* ---- Release vs Consume PIE filter ---- */
        toggleReleaseConsume(i) {
            const idx = this.releaseConsumeSel.indexOf(i);
            if (idx>-1) this.releaseConsumeSel.splice(idx,1); else this.releaseConsumeSel.push(i);
            this.updateReleaseConsumeChart();
        },
        allReleaseConsume()  { this.releaseConsumeSel=[...Array(this.rawData.length).keys()]; this.updateReleaseConsumeChart(); },
        noneReleaseConsume() { this.releaseConsumeSel=[]; this.updateReleaseConsumeChart(); },
        updateReleaseConsumeChart() {
            if(!this.releaseConsumeChart) return;
            const f=this.rawData.filter((_,i)=>this.releaseConsumeSel.includes(i));
            const releaseData = f.map(r=>r.release_budget);
            const consumeData = f.map(r=>r.total_consume);
            this.releaseConsumeChart.data.labels=[...f.map(r=>'[R] '+this.shortName(r.item)), ...f.map(r=>'[C] '+this.shortName(r.item))];
            this.releaseConsumeChart.data.datasets[0].data=[...releaseData, ...consumeData];
            this.releaseConsumeChart.data.datasets[0].backgroundColor=[...this.PIE_COLORS.slice(0,f.length), ...this.PIE_COLORS.slice(0,f.length).map(c=>{
                const rgb = parseInt(c.slice(1),16);
                const r = (rgb >> 16) & 255;
                const g = (rgb >> 8) & 255;
                const b = rgb & 255;
                return `rgba(${r},${g},${b},0.4)`;
            })];
            this.releaseConsumeChart.update();
        },

        /* ---- Consume vs Available PIE filter ---- */
        toggleConsumeAvail(i) {
            const idx = this.consumeAvailSel.indexOf(i);
            if (idx>-1) this.consumeAvailSel.splice(idx,1); else this.consumeAvailSel.push(i);
            this.updateConsumeAvailChart();
        },
        allConsumeAvail()  { this.consumeAvailSel=[...Array(this.rawData.length).keys()]; this.updateConsumeAvailChart(); },
        noneConsumeAvail() { this.consumeAvailSel=[]; this.updateConsumeAvailChart(); },
        updateConsumeAvailChart() {
            if(!this.consumeAvailChart) return;
            const f=this.rawData.filter((_,i)=>this.consumeAvailSel.includes(i));
            const consumeData = f.map(r=>r.total_consume);
            const availData = f.map(r=>r.available_budget);
            this.consumeAvailChart.data.labels=[...f.map(r=>'[C] '+this.shortName(r.item)), ...f.map(r=>'[A] '+this.shortName(r.item))];
            this.consumeAvailChart.data.datasets[0].data=[...consumeData, ...availData];
            this.consumeAvailChart.data.datasets[0].backgroundColor=[...this.PIE_COLORS.slice(0,f.length), ...this.PIE_COLORS.slice(0,f.length).map(c=>{
                const rgb = parseInt(c.slice(1),16);
                const r = (rgb >> 16) & 255;
                const g = (rgb >> 8) & 255;
                const b = rgb & 255;
                return `rgba(${r},${g},${b},0.3)`;
            })];
            this.consumeAvailChart.update();
        },

        /* ---- init ---- */
        init() {
            this.$nextTick(()=>this._waitChart());
        },
        _waitChart() {
            if(typeof Chart==='undefined'){ setTimeout(()=>this._waitChart(),80); return; }
            if(typeof ChartDataLabels!=='undefined'){
                try{Chart.register(ChartDataLabels);}catch(e){}
            }
            this._buildCharts();
        },
        _buildCharts() {
            const fmtRp = v=>'Rp '+new Intl.NumberFormat('id-ID').format(v);
            const scaleRp = v=>{
                if(v>=1e9) return (v/1e9).toFixed(1)+'M';
                if(v>=1e6) return (v/1e6).toFixed(0)+'Jt';
                return v;
            };
            const datalabelPlugin = {
                color:'#ffffff', font:{weight:'bold',size:11},
                formatter:(val,ctx)=>{
                    const sum=ctx.chart.data.datasets[0].data.reduce((a,b)=>a+b,0);
                    const p=sum>0?(val/sum*100):0;
                    return p>4?p.toFixed(1)+'%':'';
                }
            };

            /* Release vs Consume PIE */
            const el1=document.getElementById('pie-release-consume-{{ $bid }}');
            if(el1){
                const releaseData = this.rawData.map(r=>r.release_budget);
                const consumeData = this.rawData.map(r=>r.total_consume);
                this.releaseConsumeChart=new Chart(el1,{
                    type:'pie',
                    data:{
                        labels:[...this.rawData.map(r=>'[R] '+this.shortName(r.item)), ...this.rawData.map(r=>'[C] '+this.shortName(r.item))],
                        datasets:[{
                            data:[...releaseData, ...consumeData],
                            backgroundColor:[...this.PIE_COLORS.slice(0,this.rawData.length), ...this.PIE_COLORS.slice(0,this.rawData.length).map(c=>{
                                const rgb = parseInt(c.slice(1),16);
                                const r = (rgb >> 16) & 255;
                                const g = (rgb >> 8) & 255;
                                const b = rgb & 255;
                                return `rgba(${r},${g},${b},0.4)`;
                            })],
                            borderWidth:2,borderColor:'#ffffff',hoverOffset:6
                        }]
                    },
                    options:{
                        responsive:true,maintainAspectRatio:false,devicePixelRatio:Math.max(window.devicePixelRatio||1,2),
                        plugins:{
                            legend:{position:'bottom',labels:{usePointStyle:true,padding:10,font:{size:11},boxWidth:10}},
                            tooltip:{titleFont:{size:13},bodyFont:{size:12},callbacks:{label:ctx=>ctx.label+': '+fmtRp(ctx.raw)}},
                            datalabels:datalabelPlugin
                        }
                    }
                });
            }

            /* Total Consume vs Available Budget PIE - 100% BULAT (FLATTENED) */
            const el3=document.getElementById('pie-consume-avail-{{ $bid }}');
            if(el3){
                const consumeData = this.rawData.map(r=>r.total_consume);
                const availData = this.rawData.map(r=>r.available_budget);
                this.consumeAvailChart=new Chart(el3,{
                    type:'pie',
                    data:{
                        labels:[...this.rawData.map(r=>'[C] '+this.shortName(r.item)), ...this.rawData.map(r=>'[A] '+this.shortName(r.item))],
                        datasets:[{
                            data:[...consumeData, ...availData],
                            backgroundColor:[...this.PIE_COLORS.slice(0,this.rawData.length), ...this.PIE_COLORS.slice(0,this.rawData.length).map(c=>{
                                const rgb = parseInt(c.slice(1),16);
                                const r = (rgb >> 16) & 255;
                                const g = (rgb >> 8) & 255;
                                const b = rgb & 255;
                                return `rgba(${r},${g},${b},0.3)`;
                            })],
                            borderWidth:2,borderColor:'#ffffff',hoverOffset:6
                        }]
                    },
                    options:{
                        responsive:true,maintainAspectRatio:false,devicePixelRatio:Math.max(window.devicePixelRatio||1,2),
                        plugins:{
                            legend:{position:'bottom',labels:{usePointStyle:true,padding:10,font:{size:11},boxWidth:10}},
                            tooltip:{titleFont:{size:13},bodyFont:{size:12},callbacks:{label:ctx=>ctx.label+': '+fmtRp(ctx.raw)}},
                            datalabels:datalabelPlugin
                        }
                    }
                });
            }

            /* Highest Available HORIZONTAL BAR - ALL ITEMS */
            const av=[...this.rawData].sort((a,b)=>b.available_budget-a.available_budget);
            const el4=document.getElementById('chart-avail-{{ $bid }}');
            if(el4){
                this.availChart=new Chart(el4,{
                    type:'bar',
                    data:{
                        labels:av.map(r=>this.shortName(r.item)),
                        datasets:[{label:'Available',data:av.map(r=>r.available_budget),
                                   backgroundColor:'#16a34a',borderRadius:6,barPercentage:0.65}]
                    },
                    options:{
                        indexAxis:'y',responsive:true,maintainAspectRatio:false,devicePixelRatio:Math.max(window.devicePixelRatio||1,2),
                        plugins:{legend:{display:false},tooltip:{titleFont:{size:13},bodyFont:{size:12},callbacks:{label:ctx=>fmtRp(ctx.raw)}},datalabels:{display:false}},
                        scales:{
                            x:{beginAtZero:true,grid:{color:'rgba(226,232,240,.6)'},ticks:{callback:scaleRp,font:{size:11},color:'#94a3b8'}},
                            y:{grid:{display:false},ticks:{font:{size:11},color:'#475569'}}
                        }
                    }
                });
            }

            /* Highest Consume VERTICAL BAR - ALL ITEMS */
            const cs=[...this.rawData].sort((a,b)=>b.total_consume-a.total_consume);
            const el5=document.getElementById('chart-consume-{{ $bid }}');
            if(el5){
                this.consumeBarChart=new Chart(el5,{
                    type:'bar',
                    data:{
                        labels:cs.map(r=>this.shortName(r.item)),
                        datasets:[{label:'Consume',data:cs.map(r=>r.total_consume),
                                   backgroundColor:'#ea580c',borderRadius:6,barPercentage:0.65}]
                    },
                    options:{
                        responsive:true,maintainAspectRatio:false,devicePixelRatio:Math.max(window.devicePixelRatio||1,2),
                        plugins:{legend:{display:false},tooltip:{titleFont:{size:13},bodyFont:{size:12},callbacks:{label:ctx=>fmtRp(ctx.raw)}},datalabels:{display:false}},
                        scales:{
                            x:{grid:{display:false},ticks:{font:{size:10},color:'#475569'}},
                            y:{beginAtZero:true,grid:{color:'rgba(226,232,240,.6)'},ticks:{callback:scaleRp,font:{size:11},color:'#94a3b8'}}
                        }
                    }
                });
            }
        }
    }));

@endforeach

}); /* end alpine:init */
</script>

{{-- ============================================================
     MAIN SECTION
     ============================================================ --}}
<section class="space-y-5" x-data="{ activeTab: 'AirNav Juanda (Utama)' }">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-slate-500 text-sm">Budget Usage Overview</p>
            <h2 class="flex items-center gap-3 text-2xl font-semibold text-slate-800 mt-0.5">
                <span>Finance Dashboard</span>
                <span class="hidden sm:block w-px h-5 bg-slate-300"></span>
                <span class="inline-flex items-center bg-blue-50 text-blue-700 text-[10px] font-semibold px-2 py-1.5 rounded-full uppercase tracking-wide">Updated</span>
                <span class="inline-flex items-center bg-slate-100 text-slate-600 text-[10px] font-semibold px-2 py-1.5 rounded-full uppercase">{{ now()->format('d M Y') }}</span>
            </h2>
            <div class="mt-2 h-1 w-16 bg-gradient-to-r from-blue-600 to-sky-400 rounded-full"></div>
        </div>
        {{-- Branch Tabs --}}
        <div class="flex items-center gap-1 bg-slate-100/80 p-1.5 rounded-xl border border-slate-200 overflow-x-auto fin-scroll">
            @foreach($financeData as $branchName => $__)
            <button
                @click="activeTab='{{ $branchName }}'; setTimeout(()=>window.dispatchEvent(new Event('resize')),80);"
                :class="activeTab==='{{ $branchName }}' ? 'bg-white text-blue-700 font-bold shadow-sm border border-blue-100' : 'text-slate-500 hover:text-slate-700 hover:bg-white/60'"
                class="px-4 py-2 text-sm rounded-lg transition-all duration-200 whitespace-nowrap">
                {{ $branchName }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- ===== BRANCH LOOP ===== --}}
    @foreach($financeData as $branchName => $data)
    @php
        $totalRkap       = array_sum(array_column($data,'rkap'));
        $totalRelease    = array_sum(array_column($data,'release_budget'));
        $totalCommitment = array_sum(array_column($data,'commitment'));
        $totalConsume    = array_sum(array_column($data,'total_consume'));
        $totalAvailable  = array_sum(array_column($data,'available_budget'));
        $releasePct      = $totalRkap    > 0 ? round($totalRelease/$totalRkap*100,1)     : 0;
        $consumePct      = $totalRelease > 0 ? round($totalConsume/$totalRelease*100,1)   : 0;
        $availPct        = $totalRelease > 0 ? round($totalAvailable/$totalRelease*100,1) : 0;
        $commitPct       = $totalRelease > 0 ? round($totalCommitment/$totalRelease*100,1): 0;
        $bid = str_replace('-','_', Str::slug($branchName));
    @endphp

    <div x-show="activeTab==='{{ $branchName }}'"
         x-data="fin_{{ $bid }}"
         style="display:none;"
         class="space-y-4">

        {{-- ===== KPI ROW 1: RKAP | Release | Consume ===== --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

            {{-- RKAP --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total RKAP (Diajukan)</span>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-black text-slate-800 leading-tight tracking-tight">{{ fmtCard($totalRkap) }}</p>
                <p class="text-xs text-slate-400 font-semibold mt-2 uppercase">Anggaran Ditetapkan</p>
            </div>

            {{-- Release Budget --}}
            <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-6 relative overflow-hidden">
                <div class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-blue-500 to-sky-400 rounded-t-2xl"></div>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Release Budget</span>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10v1m0 10v1m9-6a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-black text-blue-600 leading-tight tracking-tight">{{ fmtCard($totalRelease) }}</p>
                <div class="flex items-center gap-2 mt-1.5">
                    <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded-md">{{ $releasePct }}%</span>
                    <span class="text-[10px] text-slate-400 font-semibold uppercase">dari RKAP</span>
                </div>
            </div>

            {{-- Total Consume --}}
            <div class="bg-white rounded-2xl border border-orange-100 shadow-sm p-6 relative overflow-hidden">
                <div class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-orange-500 to-amber-400 rounded-t-2xl"></div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Consume</span>
                    <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-black text-orange-500 leading-tight tracking-tight">{{ fmtCard($totalConsume) }}</p>
                <div class="flex items-center gap-2 mt-1.5">
                    <span class="text-[10px] font-black text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded-md">{{ $consumePct }}%</span>
                    <span class="text-[10px] text-slate-400 font-semibold uppercase">dari Release</span>
                </div>
            </div>
        </div>

        {{-- ===== KPI ROW 2: Commitment | Available ===== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            {{-- Commitment --}}
            <div class="bg-white rounded-2xl border border-violet-100 shadow-sm p-5 flex items-center gap-5 relative overflow-hidden">
                <div class="absolute inset-y-0 left-0 w-1 bg-violet-400 rounded-l-2xl"></div>
                <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Commitment</p>
                    <p class="text-xl font-black text-violet-600 leading-tight">{{ fmtCard($totalCommitment) }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-[10px] font-black text-violet-600 bg-violet-50 px-1.5 py-0.5 rounded-md">{{ $commitPct }}%</span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase">dari Release</span>
                    </div>
                </div>
            </div>

            {{-- Available Budget --}}
            <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-5 flex items-center gap-5 relative overflow-hidden">
                <div class="absolute inset-y-0 left-0 w-1 bg-emerald-400 rounded-l-2xl"></div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Available Budget</p>
                    <p class="text-xl font-black text-emerald-600 leading-tight">{{ fmtCard($totalAvailable) }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-md">{{ $availPct }}%</span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase">Tersisa</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== SERAPAN ANGGARAN BOX ===== --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex items-center gap-6">
            <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center shrink-0">
                <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div class="flex-1">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Serapan Anggaran</p>
                <div class="flex items-end gap-4">
                    <div>
                        <p class="text-3xl font-black text-blue-600 leading-tight">{{ $consumePct }}%</p>
                        <p class="text-[10px] text-slate-500 mt-1">dari Total Release</p>
                    </div>
                    <div class="flex-1">
                        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                            <div class="h-3 rounded-full bg-gradient-to-r from-blue-500 to-sky-400 transition-all"
                                 style="width: {{ $consumePct }}%"></div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-black text-slate-800">{{ fmtCard($totalConsume) }}</p>
                        <p class="text-[10px] text-slate-500 mt-1">dari {{ fmtCard($totalRelease) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== 4 CHART PANELS (2x2 Layout) ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- ---- Panel 1: Release vs Total Consume PIE (TOP LEFT) ---- --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
                <div class="flex items-start justify-between mb-5">
                    <div>
                        <p class="text-lg font-black text-slate-800">Release vs Total Consume</p>
                        <p class="text-xs text-slate-400 mt-1">Perbandingan total release dan konsumsi (100%)</p>
                    </div>
                    <div class="relative" @click.outside="releaseConsumeOpen=false">
                        <button @click="releaseConsumeOpen=!releaseConsumeOpen"
                            class="flex items-center gap-1 text-xs font-bold px-3 py-2 rounded-lg border border-slate-200 text-slate-600 hover:border-blue-300 hover:text-blue-600 transition-all">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            Filter
                            <span class="bg-blue-100 text-blue-700 rounded-full px-1.5 font-black text-[9px]" x-text="releaseConsumeSel.length"></span>
                        </button>
                        <div x-show="releaseConsumeOpen" x-transition
                             class="absolute right-0 top-10 z-50 bg-white rounded-xl border border-slate-200 shadow-xl w-56 p-3">
                            <div class="flex gap-1.5 mb-2">
                                <button @click="allReleaseConsume()" class="text-[9px] font-bold px-2.5 py-1 rounded bg-blue-50 text-blue-600 hover:bg-blue-100 transition">Semua</button>
                                <button @click="noneReleaseConsume()" class="text-[9px] font-bold px-2.5 py-1 rounded bg-slate-100 text-slate-500 hover:bg-slate-200 transition">Kosong</button>
                            </div>
                            <div class="space-y-1 max-h-56 overflow-y-auto pr-2 fin-scroll2">
                                <template x-for="(item, i) in rawData" :key="i">
                                    <label class="flex items-center gap-2 cursor-pointer group py-1">
                                        <input type="checkbox" :checked="releaseConsumeSel.includes(i)" @change="toggleReleaseConsume(i)"
                                               class="w-3.5 h-3.5 rounded accent-blue-600">
                                        <span class="text-[10px] font-semibold text-slate-600 group-hover:text-blue-600 leading-tight" x-text="shortName(item.item)"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="releaseConsumeSel.length===0" class="flex-1 flex items-center justify-center min-h-[420px]">
                    <p class="text-[11px] text-slate-400 italic">Tidak ada item dipilih</p>
                </div>

                <div :class="releaseConsumeSel.length===0?'hidden':''" class="flex-1 min-h-[420px]">
                    <canvas id="pie-release-consume-{{ $bid }}" style="max-height: 420px;"></canvas>
                </div>
            </div>

            {{-- ---- Panel 2: Total Consume vs Available Budget PIE (TOP RIGHT) ---- --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
                <div class="flex items-start justify-between mb-5">
                    <div>
                        <p class="text-lg font-black text-slate-800">Consume vs Available Budget</p>
                        <p class="text-xs text-slate-400 mt-1">Perbandingan sisa anggaran per item (100%)</p>
                    </div>
                    <div class="relative" @click.outside="consumeAvailOpen=false">
                        <button @click="consumeAvailOpen=!consumeAvailOpen"
                            class="flex items-center gap-1 text-xs font-bold px-3 py-2 rounded-lg border border-slate-200 text-slate-600 hover:border-emerald-300 hover:text-emerald-600 transition-all">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            Filter
                            <span class="bg-emerald-100 text-emerald-700 rounded-full px-1.5 font-black text-[9px]" x-text="consumeAvailSel.length"></span>
                        </button>
                        <div x-show="consumeAvailOpen" x-transition
                             class="absolute right-0 top-10 z-50 bg-white rounded-xl border border-slate-200 shadow-xl w-56 p-3">
                            <div class="flex gap-1.5 mb-2">
                                <button @click="allConsumeAvail()" class="text-[9px] font-bold px-2.5 py-1 rounded bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition">Semua</button>
                                <button @click="noneConsumeAvail()" class="text-[9px] font-bold px-2.5 py-1 rounded bg-slate-100 text-slate-500 hover:bg-slate-200 transition">Kosong</button>
                            </div>
                            <div class="space-y-1 max-h-56 overflow-y-auto pr-2 fin-scroll2">
                                <template x-for="(item, i) in rawData" :key="i">
                                    <label class="flex items-center gap-2 cursor-pointer group py-1">
                                        <input type="checkbox" :checked="consumeAvailSel.includes(i)" @change="toggleConsumeAvail(i)"
                                               class="w-3.5 h-3.5 rounded accent-emerald-600">
                                        <span class="text-[10px] font-semibold text-slate-600 group-hover:text-emerald-600 leading-tight" x-text="shortName(item.item)"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="consumeAvailSel.length===0" class="flex-1 flex items-center justify-center min-h-[420px]">
                    <p class="text-[11px] text-slate-400 italic">Tidak ada item dipilih</p>
                </div>

                <div :class="consumeAvailSel.length===0?'hidden':''" class="flex-1 min-h-[420px]">
                    <canvas id="pie-consume-avail-{{ $bid }}" style="max-height: 420px;"></canvas>
                </div>
            </div>

            {{-- ---- Panel 3: Highest Available BAR CHART (BOTTOM LEFT) ---- --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
                <div class="mb-4">
                    <p class="text-lg font-black text-slate-800">Highest Available (Semua Item)</p>
                    <p class="text-[10px] text-slate-400 mt-1">Budget tersisa per item — sorted descending</p>
                </div>
                <div class="flex-1 min-h-[420px]">
                    <canvas id="chart-avail-{{ $bid }}" style="max-height: 420px;"></canvas>
                </div>
            </div>

            {{-- ---- Panel 4: Highest Consume BAR CHART (BOTTOM RIGHT) ---- --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
                <div class="mb-4">
                    <p class="text-lg font-black text-slate-800">Highest Consume (Semua Item)</p>
                    <p class="text-[10px] text-slate-400 mt-1">Konsumsi per item — sorted descending</p>
                </div>
                <div class="flex-1 min-h-[420px]">
                    <canvas id="chart-consume-{{ $bid }}" style="max-height: 420px;"></canvas>
                </div>
            </div>
        </div>

        {{-- ===== DETAIL TABLE (sortable) ===== --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <p class="text-lg font-black text-slate-800">Detail Funds Center</p>
                    <p class="text-xs text-slate-400 font-semibold mt-1">{{ $branchName }} — Klik header kolom untuk sort</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg">{{ count($data) }} Items</span>
                    <span class="text-xs text-slate-400 font-semibold">Sorted by:
                        <span class="text-blue-600 font-bold" x-text="sortCol.replace('_',' ')"></span>
                        <span x-text="sortDir==='desc'?' ↓':' ↑'" class="text-blue-600 font-bold"></span>
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-slate-100">
                            <th class="pb-3 px-3 text-left text-xs font-bold text-slate-400 uppercase tracking-widest w-10">#</th>
                            <th class="pb-3 px-3 text-left text-xs font-bold text-slate-400 uppercase tracking-widest">Funds Center</th>

                            {{-- Sortable headers --}}
                            @foreach([
                                ['rkap','RKAP','text-slate-600'],
                                ['release_budget','Release','text-blue-600'],
                                ['total_consume','Consume','text-orange-500'],
                                ['available_budget','Available','text-emerald-600'],
                                ['commitment','Commit','text-violet-600'],
                            ] as [$col, $label, $color])
                            <th class="pb-2.5 px-2 text-right">
                                <button @click="setSort('{{ $col }}')"
                                    :class="sortCol==='{{ $col }}' ? 'opacity-100' : 'opacity-50 hover:opacity-80'"
                                    class="text-xs font-bold {{ $color }} uppercase tracking-widest transition-opacity flex items-center gap-0.5 ml-auto">
                                    {{ $label }}
                                    <span x-text="sortIcon('{{ $col }}')" class="text-[9px]"></span>
                                </button>
                            </th>
                            @endforeach

                            <th class="pb-3 px-3 text-left text-xs font-bold text-slate-400 uppercase tracking-widest w-36">Progress</th>
                            <th class="pb-3 px-3 text-center text-xs font-bold text-slate-400 uppercase tracking-widest">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-for="(row, i) in sortedRows" :key="row.item">
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-3 text-xs font-black text-slate-300" x-text="String(i+1).padStart(2,'0')"></td>
                                <td class="py-3.5 px-3">
                                    <p class="text-sm font-bold text-slate-700 whitespace-nowrap" x-text="shortName(row.item)"></p>
                                    <p class="text-xs text-slate-400 mt-0.5" x-text="row.item.split(' ')[0]"></p>
                                </td>
                                <td class="py-3.5 px-3 text-right text-sm text-slate-500 whitespace-nowrap" x-text="fmtRp(row.rkap)"></td>
                                <td class="py-3.5 px-3 text-right text-sm font-bold text-blue-600 whitespace-nowrap" x-text="fmtRp(row.release_budget)"></td>
                                <td class="py-3.5 px-3 text-right text-sm font-bold text-orange-500 whitespace-nowrap" x-text="fmtRp(row.total_consume)"></td>
                                <td class="py-3.5 px-3 text-right text-sm font-bold text-emerald-600 whitespace-nowrap" x-text="fmtRp(row.available_budget)"></td>
                                <td class="py-3.5 px-3 text-right text-sm text-violet-600 whitespace-nowrap" x-text="fmtRp(row.commitment)"></td>
                                <td class="py-3.5 px-3">
                                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                        <div class="h-2 rounded-full transition-all duration-500"
                                             :class="pct(row.total_consume, row.release_budget)>=90 ? 'bg-red-400' : pct(row.total_consume, row.release_budget)>=70 ? 'bg-amber-400' : 'bg-blue-400'"
                                             :style="'width:'+Math.min(pct(row.total_consume, row.release_budget),100)+'%'"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    <span class="text-xs font-black px-2 py-1 rounded"
                                          :class="pct(row.total_consume,row.release_budget)>=90 ? 'text-red-600 bg-red-50' : pct(row.total_consume,row.release_budget)>=70 ? 'text-amber-600 bg-amber-50' : 'text-emerald-600 bg-emerald-50'"
                                          x-text="pct(row.total_consume,row.release_budget)+'%'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- end branch div --}}
    @endforeach

    <style>
        .fin-scroll::-webkit-scrollbar,.fin-scroll2::-webkit-scrollbar{height:4px;width:4px;}
        .fin-scroll::-webkit-scrollbar-track,.fin-scroll2::-webkit-scrollbar-track{background:transparent;}
        .fin-scroll::-webkit-scrollbar-thumb,.fin-scroll2::-webkit-scrollbar-thumb{background:#bfdbfe;border-radius:4px;}
    </style>
</section>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</x-app-layout>
