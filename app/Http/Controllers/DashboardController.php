<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\EnrouteUpload;
use App\Models\EnrouteData;
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
        $totalIdrOriginal = EnrouteData::where('currency', 'IDR')->sum('enroute_charge') ?? 0;
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
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'];
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
        $latestDate = EnrouteUpload::max('tanggal_akhir');
        $period = $latestDate ? Carbon::parse($latestDate)->format('d M Y') : 'Aug 2026';

        // ============================================
        // PASS KE VIEW
        // ============================================
        return view('traffic', compact(
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
            'period'
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