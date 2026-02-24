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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ============================================
        // DATA ENROUTE SAJA
        // ============================================
        
        // 1. KPI Cards
        $enrouteMovement = EnrouteData::count();
        $totalRouteUnit = EnrouteData::sum('route_unit') ?? 0;
        $totalRevenueIdr = EnrouteData::sum('enroute_charge_idr') ?? 0;
        
        // 2. Revenue Composition (Sesuai rumus Anda)
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
        
        // 3. Revenue Trend per Bulan (Jan-Agu 2026)
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug','Sep','Okt','Nov','Des'];
        $revenueTrend = [];
        
        foreach ($months as $index => $month) {
            $monthNumber = $index + 1;
            $year = 2026;
            
            $total = EnrouteData::whereYear('dof', $year)
                ->whereMonth('dof', $monthNumber)
                ->sum('enroute_charge_idr') ?? 0;
            
            // Konversi ke Miliar untuk grafik
            $revenueTrend[] = round($total / 1000000000, 1);
        }
        
        // 3. Revenue Trend Enroute per Hari (30 hari terakhir)
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(29); // 30 hari termasuk hari ini

        $enrouteDailyRevenue = EnrouteData::select(
                DB::raw('DATE(dof) as date'),
                DB::raw('SUM(enroute_charge_idr) as total_revenue')
            )
            ->whereBetween('dof', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy(DB::raw('DATE(dof)'))
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');

        // Buat array untuk 30 hari dengan nilai default 0
        $enrouteDailyLabels = [];
        $enrouteDailyValues = [];

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $enrouteDailyLabels[] = $date->format('d/m');
            $enrouteDailyValues[] = $enrouteDailyRevenue[$dateStr]->total_revenue ?? 0;
        }

        // Konversi ke Miliar untuk grafik
        $enrouteDailyChartValues = array_map(function($value) {
            return round($value / 1000000000, 2); // Dalam Miliar
        }, $enrouteDailyValues);

        // Untuk Terminal
        $terminalDailyRevenue = TerminalData::select(
                DB::raw('DATE(tanggal) as date'),
                DB::raw('SUM(biaya_terminal_idr) as total_revenue')
            )
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');

        $terminalDailyValues = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $terminalDailyValues[] = $terminalDailyRevenue[$dateStr]->total_revenue ?? 0;
        }

        $terminalDailyChartValues = array_map(function($value) {
            return round($value / 1000000000, 2);
        }, $terminalDailyValues);
        

        // Ambil data revenue per hari (SEMUA data, bukan hanya 30 hari)
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
            // Format label: 24/01, 25/01, dst
            $enrouteDailyLabels[] = Carbon::parse($item->date)->format('d/m');
            // Nilai dalam Juta
            $enrouteDailyChartValues[] = round($item->total_revenue / 1000000, 2);
        }

        // Jika tidak ada data, beri default
        if (empty($enrouteDailyLabels)) {
            $enrouteDailyLabels = ['Tidak ada data'];
            $enrouteDailyChartValues = [0];
        }

        // 4. Traffic Peak Window (distribusi per jam dengan filter)
        $hourlyTraffic = [];
        for ($hour = 0; $hour < 24; $hour += 3) {
            $startHour = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $endHour = str_pad($hour + 3, 2, '0', STR_PAD_LEFT);
            
            // Hitung hanya yang time_in valid (bukan '00:00:00' default)
            $count = EnrouteData::whereNotNull('time_in')
                ->where('time_in', '!=', '00:00:00')
                ->whereTime('time_in', '>=', "{$startHour}:00:00")
                ->whereTime('time_in', '<', "{$endHour}:00:00")
                ->count();
            
            $hourlyTraffic[] = $count;
        }

        // Log untuk debug
        \Log::info('Hourly traffic distribution:', $hourlyTraffic);
        
        // Hitung total data dengan time_in valid
        $totalValidTime = array_sum($hourlyTraffic);
        \Log::info("Total data dengan time_in valid: " . $totalValidTime);
        \Log::info("Total seluruh data: " . EnrouteData::count());
        
        // Normalize untuk tinggi bar (0-100%)
        $maxTraffic = max($hourlyTraffic) ?: 1;
        $peakHeights = array_map(function($val) use ($maxTraffic) {
            return round(($val / $maxTraffic) * 100);
        }, $hourlyTraffic);
        
        // Cari jam peak (hanya jika ada data)
        if (max($hourlyTraffic) > 0) {
            $peakIndex = array_search(max($hourlyTraffic), $hourlyTraffic);
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
            
            $movementPct = round(($item->flight_count / $totalFlights) * 300);
            $revenuePct = $totalRevenue > 0 ? round(($item->total_revenue / $totalRevenue) * 300) : 0;
            
            $topAirlinesData[] = [
                'name' => $airline->airline_name ?? $item->airline3_code,
                'flights' => $item->flight_count,
                'revenue' => $this->formatRupiah($item->total_revenue),
                'movement_pct' => $movementPct,
                'revenue_pct' => $revenuePct,
            ];
        }
        
        // 7. Top Airlines by Movement (untuk tabel di bagian Traffic Overview)
        $topAirlinesMovement = EnrouteData::select(
                'airline3_code',
                DB::raw('COUNT(*) as flight_count')
            )
            ->whereNotNull('airline3_code')
            ->groupBy('airline3_code')
            ->orderBy('flight_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) use ($totalFlights) {
                $airline = Airline::where('airline3_code', $item->airline3_code)->first();
                $percentage = round(($item->flight_count / $totalFlights) * 100, 2);
                $barWidth = round(($item->flight_count / $totalFlights) * 100);
                
                return [
                    'name' => $airline->airline_name ?? $item->airline3_code,
                    'code' => $item->airline3_code,
                    'count' => $item->flight_count,
                    'percentage' => $percentage,
                    'bar_width' => $barWidth,
                ];
            });
        
        // 8. Top 5 Flight Routes
        $topRoutes = EnrouteData::select(
                'adep',
                'ades',
                DB::raw('COUNT(*) as route_count')
            )
            ->whereNotNull('adep')
            ->whereNotNull('ades')
            ->groupBy('adep', 'ades')
            ->orderBy('route_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) use ($totalFlights) {
                $percentage = round(($item->route_count / $totalFlights) * 100, 2);
                $barWidth = round(($item->route_count / $totalFlights) * 100);
                
                return [
                    'route' => $item->adep . ' – ' . $item->ades,
                    'count' => $item->route_count,
                    'percentage' => $percentage,
                    'bar_width' => $barWidth,
                ];
            });
        
        // 9. International vs Domestic untuk donut traffic
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
        
        // 10. Traffic per bulan (untuk line chart di Traffic Overview)
        $trafficPerMonth = [];
        foreach ($months as $index => $month) {
            $monthNumber = $index + 1;
            $year = 2026;
            
            $enrouteCount = EnrouteData::whereYear('dof', $year)
                ->whereMonth('dof', $monthNumber)
                ->count();
            
            $trafficPerMonth[] = $enrouteCount;
        }
        
        // Misal ambil dari upload terakhir
        $latestDateenroute = EnrouteUpload::max('tanggal_akhir');
        $periodenroute = $latestDateenroute ? Carbon::parse($latestDateenroute)->format('d M Y') : 'Aug 2026';

        // ============================================
        // DATA TERMINAL
        // ============================================
        
        // 1. KPI Cards Terminal
        $terminalMovement = TerminalData::count();
        $terminalRevenueIdr = TerminalData::sum('biaya_terminal_idr') ?? 0;
        $terminalServiceUnit = TerminalData::sum('parking_stand') ?? 0; // MTOW sebagai service unit
        
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
        
        // 3. Revenue Trend Terminal per Bulan
        $terminalRevenueTrend = [];
        foreach ($months as $index => $month) {
            $monthNumber = $index + 1;
            $year = 2026;
            
            $total = TerminalData::whereYear('tanggal', $year)
                ->whereMonth('tanggal', $monthNumber)
                ->sum('biaya_terminal_idr') ?? 0;
            
            $terminalRevenueTrend[] = round($total / 1000000000, 1);
        }
        
        // Ambil data revenue per hari dari Terminal
        $terminalDailyRevenue = TerminalData::select(
                DB::raw('DATE(tanggal) as date'),
                DB::raw('SUM(biaya_terminal_idr) as total_revenue')
            )
            ->whereNotNull('tanggal')
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->orderBy('date', 'asc')
            ->get();

        // Buat array untuk grafik Terminal
        $terminalDailyLabels = [];
        $terminalDailyChartValues = [];

        foreach ($terminalDailyRevenue as $item) {
            // Format label: 24/01, 25/01, dst
            $terminalDailyLabels[] = Carbon::parse($item->date)->format('d/m');
            // Nilai dalam Juta
            $terminalDailyChartValues[] = round($item->total_revenue / 1000000, 2);
        }

        // Jika tidak ada data, beri default
        if (empty($terminalDailyLabels)) {
            $terminalDailyLabels = ['Tidak ada data'];
            $terminalDailyChartValues = [0];
        }

        // 4. Traffic Peak Window Terminal (berdasarkan waktu kedatangan)
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
            
            $movementPct = round(($item->flight_count / $totalTerminalFlights) * 300);
            $revenuePct = $totalTerminalRevenue > 0 ? round(($item->total_revenue / $totalTerminalRevenue) * 300) : 0;
            
            $topTerminalAirlinesData[] = [
                'name' => $airline->airline_name ?? $item->airline3_code,
                'flights' => $item->flight_count,
                'revenue' => $this->formatRupiah($item->total_revenue),
                'movement_pct' => $movementPct,
                'revenue_pct' => $revenuePct,
            ];
        }
        
        // 7. Terminal Traffic per bulan
        $terminalTrafficPerMonth = [];
        foreach ($months as $index => $month) {
            $monthNumber = $index + 1;
            $year = 2026;
            
            $terminalCount = TerminalData::whereYear('tanggal', $year)
                ->whereMonth('tanggal', $monthNumber)
                ->count();
            
            $terminalTrafficPerMonth[] = $terminalCount;
        }

        // Misal ambil dari upload terakhir
        $latestDateterminal = TerminalUpload::max('tanggal_akhir');
        $periodterminal = $latestDateterminal ? Carbon::parse($latestDateterminal)->format('d M Y') : 'Aug 2026';


        // ============================================
        // DATA UNTUK TRAFFIC OVERVIEW
        // ============================================
        
        $totalMovement = TrafficData::count();
        // 1. Monthly Traffic Trend (Enroute + Terminal)
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Des'];
        $arrivalTrend = [];  // Kedatangan (Arrival)
        $departureTrend = []; // Keberangkatan (Departure)
        
        foreach ($months as $index => $month) {
            $monthNumber = $index + 1;
            $year = 2026;
            
            // Arrival: data dengan ADES = bandara tujuan (misal WADD)
            $arrival = TrafficData::whereYear('tanggal', $year)
                ->whereMonth('tanggal', $monthNumber)
                ->where('ades', 'LIKE', 'WADD%')
                ->count();
            
            // Departure: data dengan ADEP = bandara asal (misal WADD)
            $departure = TrafficData::whereYear('tanggal', $year)
                ->whereMonth('tanggal', $monthNumber)
                ->where('adep', 'LIKE', 'WADD%')
                ->count();
            
            $arrivalTrend[] = $arrival;
            $departureTrend[] = $departure;
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
        $hourlyTraffic = [];
        for ($hour = 0; $hour < 24; $hour += 3) {
            $startHour = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $endHour = str_pad($hour + 3, 2, '0', STR_PAD_LEFT);
            
            // Gunakan waktu keberangkatan (ATD) atau kedatangan (ATA)
            $count = TrafficData::whereNotNull('atd')
                ->whereTime('atd', '>=', "{$startHour}:00:00")
                ->whereTime('atd', '<', "{$endHour}:00:00")
                ->count();
            
            $hourlyTraffic[] = $count;
        }
        
        $maxTraffic = max($hourlyTraffic) ?: 1;
        $peakHeights = array_map(function($val) use ($maxTraffic) {
            return round(($val / $maxTraffic) * 100);
        }, $hourlyTraffic);
        
        if (max($hourlyTraffic) > 0) {
            $peakIndex = array_search(max($hourlyTraffic), $hourlyTraffic);
            $peakStart = str_pad($peakIndex * 3, 2, '0', STR_PAD_LEFT) . ':00';
            $peakEnd = str_pad(($peakIndex * 3) + 3, 2, '0', STR_PAD_LEFT) . ':00';
        } else {
            $peakStart = '00:00';
            $peakEnd = '03:00';
        }
        
        // 4. Aircraft Category Mix
        $heavyTypes = ['B77', 'B78', 'B74', 'A33', 'A34', 'A35', 'A38'];
        $mediumTypes = ['B73', 'B75', 'B76', 'A31', 'A32', 'A20'];
        
        $heavyCount = TrafficData::where(function($q) use ($heavyTypes) {
            foreach ($heavyTypes as $type) {
                $q->orWhere('type', 'LIKE', "{$type}%");
            }
        })->count();
        
        $mediumCount = TrafficData::where(function($q) use ($mediumTypes) {
            foreach ($mediumTypes as $type) {
                $q->orWhere('type', 'LIKE', "{$type}%");
            }
        })->count();
        
        $lightCount = TrafficData::count() - ($heavyCount + $mediumCount);
        $totalAircraft = TrafficData::count() ?: 1;
        
        $heavyPercentage = round(($heavyCount / $totalAircraft) * 100);
        $mediumPercentage = round(($mediumCount / $totalAircraft) * 100);
        $lightPercentage = 100 - ($heavyPercentage + $mediumPercentage);
        
        // 5. Top 5 Airlines by Movement
        $topAirlines = TrafficData::select(
                'airline3_code',
                DB::raw('COUNT(*) as flight_count')
            )
            ->whereNotNull('airline3_code')
            ->groupBy('airline3_code')
            ->orderBy('flight_count', 'desc')
            ->limit(7)
            ->get()
            ->map(function($item) use ($totalAircraft) {
                $airline = Airline::where('airline3_code', $item->airline3_code)->first();
                $percentage = round(($item->flight_count / $totalAircraft) * 100, 2);
                $barWidth = round(($item->flight_count / $totalAircraft) * 100);
                
                return [
                    'name' => $airline->airline_name ?? $item->airline3_code,
                    'code' => $item->airline3_code,
                    'count' => $item->flight_count,
                    'percentage' => $percentage,
                    'bar_width' => $barWidth,
                ];
            });
        
        // 6. Top 5 Flight Routes
        $topRoutes = TrafficData::select(
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
            ->map(function($item) use ($totalAircraft) {
                $percentage = round(($item->route_count / $totalAircraft) * 100, 2);
                $barWidth = round(($item->route_count / $totalAircraft) * 100);
                
                return [
                    'route' => $item->adep . ' – ' . $item->ades,
                    'count' => $item->route_count,
                    'percentage' => $percentage,
                    'bar_width' => $barWidth,
                ];
            });

        // ============================================
        // PASS KE VIEW
        // ============================================
        return view('traffic', compact(
            //Enroute
            'enrouteMovement',
            'totalRouteUnit',
            'totalRevenueIdr',
            'domPercentage',
            'intPercentage',
            'revenueTrend',
            'hourlyTraffic',
            'peakHeights',
            'peakStart',
            'peakEnd',
            'heavyPercentage',
            'mediumPercentage',
            'lightPercentage',
            'topAirlinesData',
            'trafficPerMonth',
            'internationalPct',
            'domesticPct',
            'topAirlinesMovement',
            'topRoutes',
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
            'terminalRevenueTrend',
            'terminalHourlyTraffic',
            'terminalPeakHeights',
            'terminalPeakStart',
            'terminalPeakEnd',
            'terminalHeavyPercentage',
            'terminalMediumPercentage',
            'terminalLightPercentage',
            'topTerminalAirlinesData',
            'terminalTrafficPerMonth',
            'periodterminal',
            'terminalDailyLabels',
            'terminalDailyChartValues',
            'tnctotalIdrOriginal',
            'tnctotalUsdOriginal',
            'tnctotalUsdConverted',

            //Traffic
            'arrivalTrend',
            'departureTrend',
            'internationalPct',
            'domesticPct',
            'peakHeights',
            'peakStart',
            'peakEnd',
            'heavyPercentage',
            'mediumPercentage',
            'lightPercentage',
            'topAirlines',
            'topRoutes',
            'totalMovement'
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