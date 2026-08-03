<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\TrafficUpload;
use App\Models\TrafficData;
use App\Models\EnrouteUpload;
use App\Models\EnrouteData;
use App\Models\TerminalUpload;
use App\Models\TerminalData;
use App\Models\Airline;
use App\Models\FinanceData;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function adminOverview()
    {
        $sources = [
            ['label' => 'Master Airline', 'records' => Airline::count(), 'uploads' => null, 'route' => 'admin.airlines.index'],
            ['label' => 'Traffic Movement', 'records' => TrafficData::count(), 'uploads' => TrafficUpload::count(), 'route' => 'admin.traffic.index'],
            ['label' => 'Enroute', 'records' => EnrouteData::count(), 'uploads' => EnrouteUpload::count(), 'route' => 'admin.enroutes.index'],
            ['label' => 'Terminal', 'records' => TerminalData::count(), 'uploads' => TerminalUpload::count(), 'route' => 'admin.terminals.index'],
            ['label' => 'Finance', 'records' => \App\Models\BudgetRealisasi::count(), 'uploads' => \App\Models\FinanceUpload::count(), 'route' => 'admin.finances.index'],
        ];

        return view('admin.dashboard', [
            'sources' => $sources,
            'totalRecords' => collect($sources)->sum('records'),
            'lastInputAt' => collect([
                TrafficData::max('updated_at'), EnrouteData::max('updated_at'),
                TerminalData::max('updated_at'), \App\Models\BudgetRealisasi::max('updated_at'), Airline::max('updated_at'),
            ])->filter()->max(),
        ]);
    }

    public function index()
    {
        // ============================================
        // DATA ENROUTE SAJA
        // ============================================
        
        // 1. KPI Cards
        $enrouteMovement = EnrouteData::count();
        $totalRouteUnit = EnrouteData::sum('route_unit') ?? 0;
        $totalRevenueIdr = EnrouteData::sum('enroute_charge_idr') ?? 0;
        
        // 2. Revenue Composition
        $enctotalIdrOriginal = EnrouteData::where('currency', 'IDR')->sum('enroute_charge') ?? 0;
        $enctotalUsdOriginal = EnrouteData::where('currency', 'USD')->sum('enroute_charge') ?? 0;
        $enctotalUsdConverted = EnrouteData::where('currency', 'USD')->sum('enroute_charge_idr') ?? 0;
        
        $totalIdrOriginal = EnrouteData::where('currency', 'IDR')->sum('enroute_charge') ?? 0;
        $totalUsdOriginal = EnrouteData::where('currency', 'USD')->sum('enroute_charge') ?? 0;
        $totalUsdConverted = EnrouteData::where('currency', 'USD')->sum('enroute_charge_idr') ?? 0;
        $totalSemua = $totalIdrOriginal + $totalUsdConverted;
        
        if ($totalSemua > 0) {
            $domPercentage = round(($totalIdrOriginal / $totalSemua) * 100, 1);
            $intPercentage = round(($totalUsdConverted / $totalSemua) * 100, 1);
        } else {
            $domPercentage = 0;
            $intPercentage = 0;
        }
        
        // 3. Revenue Trend Enroute per Hari (SEMUA DATA)
        $enrouteDailyRevenue = EnrouteData::select(
                DB::raw('DATE(dof) as date'),
                DB::raw('SUM(enroute_charge_idr) as total_revenue')
            )
            ->whereNotNull('dof')
            ->groupBy(DB::raw('DATE(dof)'))
            ->orderBy('date', 'asc')
            ->get();

        // Buat array untuk grafik
        $enrouteDailyLabels = [];
        $enrouteDailyChartValues = [];

        foreach ($enrouteDailyRevenue as $item) {
            $enrouteDailyLabels[] = Carbon::parse($item->date)->format('d/m');
            $enrouteDailyChartValues[] = round($item->total_revenue / 1000000, 2);
        }

        if (empty($enrouteDailyLabels)) {
            $enrouteDailyLabels = ['Tidak ada data'];
            $enrouteDailyChartValues = [0];
        }
        
        // 4. Traffic Peak Window Enroute
        $enrouteHourlyTraffic = [];
        for ($hour = 0; $hour < 24; $hour += 3) {
            $startHour = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $endHour = str_pad($hour + 3, 2, '0', STR_PAD_LEFT);
            
            $count = EnrouteData::whereNotNull('time_in')
                ->where('time_in', '!=', '00:00:00')
                ->whereTime('time_in', '>=', "{$startHour}:00:00")
                ->whereTime('time_in', '<', "{$endHour}:00:00")
                ->count();
            
            $enrouteHourlyTraffic[] = $count;
        }

        // Normalize untuk tinggi bar (0-100%)
        $maxTraffic = max($enrouteHourlyTraffic) ?: 1;
        $peakHeights = array_map(function($val) use ($maxTraffic) {
            return round(($val / $maxTraffic) * 100);
        }, $enrouteHourlyTraffic);
        
        // Cari jam peak
        if (max($enrouteHourlyTraffic) > 0) {
            $peakIndex = array_search(max($enrouteHourlyTraffic), $enrouteHourlyTraffic);
            $peakStart = str_pad($peakIndex * 3, 2, '0', STR_PAD_LEFT) . ':00';
            $peakEnd = str_pad(($peakIndex * 3) + 3, 2, '0', STR_PAD_LEFT) . ':00';
        } else {
            $peakStart = '00:00';
            $peakEnd = '00:00';
        }
        
        // 5. Aircraft Category Mix
        $heavyTypes = ['B77', 'B78', 'B74', 'A33', 'A34', 'A35', 'A38'];
        $mediumTypes = ['B73', 'B75', 'B76', 'A31', 'A32', 'A20'];
        
        $heavyCount = EnrouteData::where(function($q) use ($heavyTypes) {
            foreach ($heavyTypes as $type) {
                $q->orWhere('type', 'LIKE', "{$type}%");
            }
        })->count();
        
        $mediumCount = EnrouteData::where(function($q) use ($mediumTypes) {
            foreach ($mediumTypes as $type) {
                $q->orWhere('type', 'LIKE', "{$type}%");
            }
        })->count();
        
        $lightCount = EnrouteData::count() - ($heavyCount + $mediumCount);
        $totalAircraft = EnrouteData::count() ?: 1;
        
        $heavyPercentage = round(($heavyCount / $totalAircraft) * 100);
        $mediumPercentage = round(($mediumCount / $totalAircraft) * 100);
        $lightPercentage = 100 - ($heavyPercentage + $mediumPercentage);
        
        // 6. Top Airline Performance
        $topAirlines = EnrouteData::select(
                'airline3_code',
                DB::raw('COUNT(*) as flight_count'),
                DB::raw('SUM(enroute_charge_idr) as total_revenue')
            )
            ->whereNotNull('airline3_code')
            ->groupBy('airline3_code')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();
        
        $topAirlinesData = [];
        $totalFlights = EnrouteData::count() ?: 1;
        $totalRevenue = EnrouteData::sum('enroute_charge_idr') ?? 0;
        
        foreach ($topAirlines as $index => $item) {
            $airline = Airline::where('airline3_code', $item->airline3_code)->first();
            
            $movementPct = round(($item->flight_count / $totalFlights) * 350);
            $revenuePct = $totalRevenue > 0 ? round(($item->total_revenue / $totalRevenue) * 350) : 0;
            
            $topAirlinesData[] = [
                'name' => $airline->airline_name ?? $item->airline3_code,
                'flights' => $item->flight_count,
                'revenue' => $this->formatRupiah($item->total_revenue),
                'movement_pct' => $movementPct,
                'revenue_pct' => $revenuePct,
            ];
        }
        
        // 7. International vs Domestic untuk donut traffic
        $internationalCount = EnrouteData::where(function($q) {
                $q->where('adep', 'NOT LIKE', 'W%')
                  ->orWhere('ades', 'NOT LIKE', 'W%');
            })->count();
        
        $domesticCount = EnrouteData::where('adep', 'LIKE', 'W%')
            ->where('ades', 'LIKE', 'W%')
            ->count();
        
        $totalForDonut = $internationalCount + $domesticCount ?: 1;
        $internationalPct = round(($internationalCount / $totalForDonut) * 100);
        $domesticPct = 100 - $internationalPct;
        
        // Period Enroute
        $latestDateenroute = EnrouteUpload::max('tanggal_akhir');
        $periodenroute = $latestDateenroute ? Carbon::parse($latestDateenroute)->format('d M Y') : 'Aug 2026';

        // ============================================
        // DATA TERMINAL
        // ============================================
        
        // 1. KPI Cards Terminal
        $terminalMovement = TerminalData::count();
        $terminalRevenueIdr = TerminalData::sum('biaya_terminal_idr') ?? 0;
        $terminalServiceUnit = TerminalData::sum('parking_stand') ?? 0;
        
        // 2. Revenue Composition Terminal
        $tnctotalIdrOriginal = TerminalData::where('currency', 'IDR')->sum('biaya_terminal') ?? 0;
        $tnctotalUsdOriginal = TerminalData::where('currency', 'USD')->sum('biaya_terminal') ?? 0;
        $tnctotalUsdConverted = TerminalData::where('currency', 'USD')->sum('biaya_terminal_idr') ?? 0;
                
        $terminalIdrOriginal = TerminalData::where('currency', 'IDR')->sum('biaya_terminal') ?? 0;
        $terminalUsdConverted = TerminalData::where('currency', 'USD')->sum('biaya_terminal_idr') ?? 0;
        $terminalTotalSemua = $terminalIdrOriginal + $terminalUsdConverted;
        
        if ($terminalTotalSemua > 0) {
            $terminalDomPercentage = round(($terminalIdrOriginal / $terminalTotalSemua) * 100, 1);
            $terminalIntPercentage = round(($terminalUsdConverted / $terminalTotalSemua) * 100, 1);
        } else {
            $terminalDomPercentage = 0;
            $terminalIntPercentage = 0;
        }
        
        // 3. Revenue Trend Terminal per Hari (SEMUA DATA)
        $terminalDailyRevenue = TerminalData::select(
                DB::raw('DATE(tanggal) as date'),
                DB::raw('SUM(biaya_terminal_idr) as total_revenue')
            )
            ->whereNotNull('tanggal')
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->orderBy('date', 'asc')
            ->get();

        $terminalDailyLabels = [];
        $terminalDailyChartValues = [];

        foreach ($terminalDailyRevenue as $item) {
            $terminalDailyLabels[] = Carbon::parse($item->date)->format('d/m');
            $terminalDailyChartValues[] = round($item->total_revenue / 1000000, 2);
        }

        if (empty($terminalDailyLabels)) {
            $terminalDailyLabels = ['Tidak ada data'];
            $terminalDailyChartValues = [0];
        }

        // 4. Traffic Peak Window Terminal
        $terminalHourlyTraffic = [];
        for ($hour = 0; $hour < 24; $hour += 3) {
            $startHour = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $endHour = str_pad($hour + 3, 2, '0', STR_PAD_LEFT);
            
            $count = TerminalData::whereNotNull('waktu_kedatangan')
                ->whereTime('waktu_kedatangan', '>=', "{$startHour}:00:00")
                ->whereTime('waktu_kedatangan', '<', "{$endHour}:00:00")
                ->count();
            
            $terminalHourlyTraffic[] = $count;
        }

        $maxTerminalTraffic = max($terminalHourlyTraffic) ?: 1;
        $terminalPeakHeights = array_map(function($val) use ($maxTerminalTraffic) {
            return round(($val / $maxTerminalTraffic) * 100);
        }, $terminalHourlyTraffic);
        
        if (max($terminalHourlyTraffic) > 0) {
            $terminalPeakIndex = array_search(max($terminalHourlyTraffic), $terminalHourlyTraffic);
            $terminalPeakStart = str_pad($terminalPeakIndex * 3, 2, '0', STR_PAD_LEFT) . ':00';
            $terminalPeakEnd = str_pad(($terminalPeakIndex * 3) + 3, 2, '0', STR_PAD_LEFT) . ':00';
        } else {
            $terminalPeakStart = '08:00';
            $terminalPeakEnd = '11:00';
        }
        
        // 5. Terminal Aircraft Category Mix
        $terminalHeavyCount = TerminalData::where(function($q) use ($heavyTypes) {
            foreach ($heavyTypes as $type) {
                $q->orWhere('type', 'LIKE', "{$type}%");
            }
        })->count();
        
        $terminalMediumCount = TerminalData::where(function($q) use ($mediumTypes) {
            foreach ($mediumTypes as $type) {
                $q->orWhere('type', 'LIKE', "{$type}%");
            }
        })->count();
        
        $terminalLightCount = TerminalData::count() - ($terminalHeavyCount + $terminalMediumCount);
        $totalTerminalAircraft = TerminalData::count() ?: 1;
        
        $terminalHeavyPercentage = round(($terminalHeavyCount / $totalTerminalAircraft) * 100);
        $terminalMediumPercentage = round(($terminalMediumCount / $totalTerminalAircraft) * 100);
        $terminalLightPercentage = 100 - ($terminalHeavyPercentage + $terminalMediumPercentage);
        
        // 6. Top Airline Performance Terminal
        $topTerminalAirlines = TerminalData::select(
                'airline3_code',
                DB::raw('COUNT(*) as flight_count'),
                DB::raw('SUM(biaya_terminal_idr) as total_revenue')
            )
            ->whereNotNull('airline3_code')
            ->groupBy('airline3_code')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();
        
        $topTerminalAirlinesData = [];
        $totalTerminalFlights = TerminalData::count() ?: 1;
        $totalTerminalRevenue = TerminalData::sum('biaya_terminal_idr') ?? 0;
        
        foreach ($topTerminalAirlines as $index => $item) {
            $airline = Airline::where('airline3_code', $item->airline3_code)->first();
            
            $movementPct = round(($item->flight_count / $totalTerminalFlights) * 350);
            $revenuePct = $totalTerminalRevenue > 0 ? round(($item->total_revenue / $totalTerminalRevenue) * 350) : 0;
            
            $topTerminalAirlinesData[] = [
                'name' => $airline->airline_name ?? $item->airline3_code,
                'flights' => $item->flight_count,
                'revenue' => $this->formatRupiah($item->total_revenue),
                'movement_pct' => $movementPct,
                'revenue_pct' => $revenuePct,
            ];
        }

        // Period Terminal
        $latestDateterminal = TerminalUpload::max('tanggal_akhir');
        $periodterminal = $latestDateterminal ? Carbon::parse($latestDateterminal)->format('d M Y') : 'Aug 2026';

        // ============================================
        // DATA UNTUK TRAFFIC OVERVIEW
        // ============================================
        
        $totalMovement = TrafficData::count();

        // 1. Monthly Traffic Trend - 12 BULAN TERAKHIR (TANPA BATASAN TAHUN)
        $latestDate = TrafficData::max('tanggal');
        $endDate = $latestDate ? Carbon::parse($latestDate) : Carbon::now();
        $startDate = $endDate->copy()->subMonths(11)->startOfMonth();

        // Buat array periode 12 bulan
        $periods = [];
        $currentDate = $startDate->copy();

        for ($i = 0; $i < 12; $i++) {
            $periods[] = [
                'year' => $currentDate->year,
                'month' => $currentDate->month,
                'label' => $currentDate->format('M y'), // 'Mar 26', 'Apr 26'
            ];
            $currentDate->addMonth();
        }

        // Ambil data agregat per bulan
        $monthlyStats = TrafficData::select(
                DB::raw('YEAR(tanggal) as tahun'),
                DB::raw('MONTH(tanggal) as bulan'),
                DB::raw('SUM(CASE WHEN dep_arr_lcl = "A" THEN 1 ELSE 0 END) as arrival'),
                DB::raw('SUM(CASE WHEN dep_arr_lcl = "D" THEN 1 ELSE 0 END) as departure')
            )
            ->whereNotNull('tanggal')
            ->whereNotNull('dep_arr_lcl')
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get()
            ->keyBy(function($item) {
                return $item->tahun . '-' . str_pad($item->bulan, 2, '0', STR_PAD_LEFT);
            });

        // Inisialisasi array untuk grafik
        $chartLabels = [];
        $arrivalTrend = [];
        $departureTrend = [];

        foreach ($periods as $period) {
            $key = $period['year'] . '-' . str_pad($period['month'], 2, '0', STR_PAD_LEFT);
            
            $chartLabels[] = $period['label'];
            $arrivalTrend[] = isset($monthlyStats[$key]) ? (int)$monthlyStats[$key]->arrival : 0;
            $departureTrend[] = isset($monthlyStats[$key]) ? (int)$monthlyStats[$key]->departure : 0;
        }
        
        // 2. Traffic Movement Composition (International vs Domestic)
        $internationalCount = TrafficData::where(function($q) {
                $q->where('adep', 'NOT LIKE', 'W%')
                  ->orWhere('ades', 'NOT LIKE', 'W%');
            })->count();
        
        $domesticCount = TrafficData::where('adep', 'LIKE', 'W%')
            ->where('ades', 'LIKE', 'W%')
            ->count();
        
        $totalForDonut = $internationalCount + $domesticCount ?: 1;
        $internationalPct = round(($internationalCount / $totalForDonut) * 100);
        $domesticPct = 100 - $internationalPct;
        
        // 3. Traffic Peak Window (per 3 jam)
        $trafficHourlyTraffic = [];
        for ($hour = 0; $hour < 24; $hour += 3) {
            $startHour = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $endHour = str_pad($hour + 3, 2, '0', STR_PAD_LEFT);
            
            $count = TrafficData::whereNotNull('atd')
                ->whereTime('atd', '>=', "{$startHour}:00:00")
                ->whereTime('atd', '<', "{$endHour}:00:00")
                ->count();
            
            $trafficHourlyTraffic[] = $count;
        }
        
        $maxTraffic = max($trafficHourlyTraffic) ?: 1;
        $trafficPeakHeights = array_map(function($val) use ($maxTraffic) {
            return round(($val / $maxTraffic) * 100);
        }, $trafficHourlyTraffic);
        
        if (max($trafficHourlyTraffic) > 0) {
            $peakIndex = array_search(max($trafficHourlyTraffic), $trafficHourlyTraffic);
            $trafficPeakStart = str_pad($peakIndex * 3, 2, '0', STR_PAD_LEFT) . ':00';
            $trafficPeakEnd = str_pad(($peakIndex * 3) + 3, 2, '0', STR_PAD_LEFT) . ':00';
        } else {
            $trafficPeakStart = '00:00';
            $trafficPeakEnd = '03:00';
        }
        
        // 4. Aircraft Category Mix untuk Traffic
        $trafficHeavyCount = TrafficData::where(function($q) use ($heavyTypes) {
            foreach ($heavyTypes as $type) {
                $q->orWhere('type', 'LIKE', "{$type}%");
            }
        })->count();
        
        $trafficMediumCount = TrafficData::where(function($q) use ($mediumTypes) {
            foreach ($mediumTypes as $type) {
                $q->orWhere('type', 'LIKE', "{$type}%");
            }
        })->count();
        
        $trafficLightCount = TrafficData::count() - ($trafficHeavyCount + $trafficMediumCount);
        $totalTrafficAircraft = TrafficData::count() ?: 1;
        
        $trafficHeavyPercentage = round(($trafficHeavyCount / $totalTrafficAircraft) * 100);
        $trafficMediumPercentage = round(($trafficMediumCount / $totalTrafficAircraft) * 100);
        $trafficLightPercentage = 100 - ($trafficHeavyPercentage + $trafficMediumPercentage);
        
        // 5. Top 5 Airlines by Movement (Traffic)
        $topTrafficAirlines = TrafficData::select(
                'airline3_code',
                DB::raw('COUNT(*) as flight_count'),
                DB::raw('SUM(CASE WHEN dep_arr_lcl = "A" THEN 1 ELSE 0 END) as arrival_count'),
                DB::raw('SUM(CASE WHEN dep_arr_lcl = "D" THEN 1 ELSE 0 END) as departure_count')
            )
            ->whereNotNull('airline3_code')
            ->groupBy('airline3_code')
            ->orderBy('flight_count', 'desc')
            ->limit(7)
            ->get()
            ->map(function($item) use ($totalMovement) {
                $airline = Airline::where('airline3_code', $item->airline3_code)->first();
                $percentage = $totalMovement > 0 ? round(($item->flight_count / $totalMovement) * 100, 2) : 0;
                $barWidth = $totalMovement > 0 ? round(($item->flight_count / $totalMovement) * 100) : 0;
                
                return [
                    'name' => $airline->airline_name ?? $item->airline3_code,
                    'code' => $item->airline3_code,
                    'count' => $item->flight_count,
                    'arrival' => $item->arrival_count,
                    'departure' => $item->departure_count,
                    'percentage' => $percentage,
                    'bar_width' => $barWidth,
                ];
            });
        
        // 6. Top 5 Flight Routes
        $topTrafficRoutes = TrafficData::select(
                'adep',
                'ades',
                DB::raw('COUNT(*) as route_count')
            )
            ->whereNotNull('adep')
            ->whereNotNull('ades')
            ->groupBy('adep', 'ades')
            ->orderBy('route_count', 'desc')
            ->limit(7)
            ->get()
            ->map(function($item) use ($totalMovement) {
                $percentage = $totalMovement > 0 ? round(($item->route_count / $totalMovement) * 100, 2) : 0;
                $barWidth = $totalMovement > 0 ? round(($item->route_count / $totalMovement) * 100) : 0;
                
                return [
                    'route' => $item->adep . ' – ' . $item->ades,
                    'count' => $item->route_count,
                    'percentage' => $percentage,
                    'bar_width' => $barWidth,
                ];
            });

        // Period Terminal
        $latestDatetraffic = TrafficData::max('tanggal');
        $periodtraffic = $latestDatetraffic ? Carbon::parse($latestDatetraffic)->format('d M Y') : 'Aug 2026';

        $activeTrafficFiles = TrafficUpload::latest('tanggal_jam')->pluck('file_name')->toArray();
        $activeEnrouteFiles = EnrouteUpload::latest('tanggal_jam')->pluck('file_name')->toArray();
        $activeTerminalFiles = TerminalUpload::latest('tanggal_jam')->pluck('file_name')->toArray();

        // ============================================
        // PASS KE VIEW
        // ============================================
        return view('traffic', compact(
            'activeTrafficFiles',
            'activeEnrouteFiles',
            'activeTerminalFiles',
            // Enroute
            'enrouteMovement',
            'totalRouteUnit',
            'totalRevenueIdr',
            'domPercentage',
            'intPercentage',
            'enrouteHourlyTraffic',
            'peakHeights',
            'peakStart',
            'peakEnd',
            'heavyPercentage',
            'mediumPercentage',
            'lightPercentage',
            'topAirlinesData',
            'internationalPct',
            'domesticPct',
            'periodenroute',
            'enrouteDailyLabels',
            'enrouteDailyChartValues',
            'enctotalIdrOriginal',
            'enctotalUsdOriginal',
            'enctotalUsdConverted',

            // Terminal
            'terminalMovement',
            'terminalServiceUnit',
            'terminalRevenueIdr',
            'terminalDomPercentage',
            'terminalIntPercentage',
            'terminalHourlyTraffic',
            'terminalPeakHeights',
            'terminalPeakStart',
            'terminalPeakEnd',
            'terminalHeavyPercentage',
            'terminalMediumPercentage',
            'terminalLightPercentage',
            'topTerminalAirlinesData',
            'periodterminal',
            'terminalDailyLabels',
            'terminalDailyChartValues',
            'tnctotalIdrOriginal',
            'tnctotalUsdOriginal',
            'tnctotalUsdConverted',

            // Traffic
            'totalMovement',
            'chartLabels',
            'arrivalTrend',
            'departureTrend',
            'trafficHeavyPercentage',
            'trafficMediumPercentage',
            'trafficLightPercentage',
            'topTrafficAirlines',
            'topTrafficRoutes',
            'trafficPeakHeights',
            'trafficPeakStart',
            'trafficPeakEnd',
            'trafficHourlyTraffic',
            'periodtraffic'
        ));
    }
    
    private function formatRupiah($angka)
    {
        if ($angka >= 1000000000) {
            return 'Rp ' . round($angka / 1000000000, 1) . ' Miliar';
        } elseif ($angka >= 1000000) {
            return 'Rp ' . round($angka / 1000000, 1) . ' Juta';
        } else {
            return 'Rp ' . number_format($angka, 0, ',', '.');
        }
    }
}
