<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\BudgetRealisasi;
use Illuminate\Support\Facades\Log;

class SapDashboardController extends Controller
{
    public function index()
    {
        $latestDate = BudgetRealisasi::max('report_date');
        $grandTotal = BudgetRealisasi::where('level', 'total')->latest('report_date')->first();
        
        $branches = BudgetRealisasi::where('level', 'cabang')
            ->when($latestDate, function($q) use ($latestDate) {
                return $q->where('report_date', $latestDate);
            })
            ->get();
            
        $items = BudgetRealisasi::where('level', 'item')
            ->when($latestDate, function($q) use ($latestDate) {
                return $q->where('report_date', $latestDate);
            })
            ->get()
            ->groupBy('branch_code');

        return view('sap-dashboard', compact('latestDate', 'grandTotal', 'branches', 'items'));
    }

    public function import()
    {
        try {
            $exitCode = Artisan::call('sap:import');
            $output = Artisan::output();
            
            return redirect()->route('sap.dashboard')->with('success', 'Import completed successfully: ' . $output);
        } catch (\Exception $e) {
            Log::error('Manual SAP Import failed: ' . $e->getMessage());
            return redirect()->route('sap.dashboard')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function apiData()
    {
        $latestDate = BudgetRealisasi::max('report_date');
        $branches = BudgetRealisasi::where('level', 'cabang')
            ->when($latestDate, function($q) use ($latestDate) {
                return $q->where('report_date', $latestDate);
            })
            ->get();
            
        return response()->json($branches);
    }
}
