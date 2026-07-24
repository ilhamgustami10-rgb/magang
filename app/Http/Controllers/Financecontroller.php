<?php

namespace App\Http\Controllers;

use App\Models\FinanceBranch;
use App\Services\FinanceImportService;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index()
    {
        $branches = FinanceBranch::with('items')->orderBy('id')->get();
        
        $financeData = [];
        
        // Tab Semua
        $allItems = [];
        foreach ($branches as $branch) {
            foreach ($branch->items as $item) {
                // Group items by code for the "Semua" tab
                $key = $item->code;
                if (!isset($allItems[$key])) {
                    $allItems[$key] = [
                        'item' => $item->code . ' ' . $item->name,
                        'rkap' => 0,
                        'release_budget' => 0,
                        'commitment' => 0,
                        'total_consume' => 0,
                        'available_budget' => 0,
                    ];
                }
                $allItems[$key]['rkap'] += $item->rkap;
                $allItems[$key]['release_budget'] += $item->release_budget;
                $allItems[$key]['commitment'] += $item->commitment;
                $allItems[$key]['total_consume'] += $item->total_consume;
                $allItems[$key]['available_budget'] += $item->available_budget;
            }
        }
        if (!empty($allItems)) {
            $financeData['Semua'] = array_values($allItems);
        }

        // Tab per Cabang
        foreach ($branches as $branch) {
            $branchItems = [];
            foreach ($branch->items as $item) {
                $branchItems[] = [
                    'item' => $item->code . ' ' . $item->name,
                    'rkap' => $item->rkap,
                    'release_budget' => $item->release_budget,
                    'commitment' => $item->commitment,
                    'total_consume' => $item->total_consume,
                    'available_budget' => $item->available_budget,
                ];
            }
            $financeData[$branch->name] = $branchItems;
        }

        return view('finance', compact('financeData'));
    }

    public function import(Request $request, FinanceImportService $importService)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        try {
            $importService->import($request->file('file')->getRealPath());
            
            $count = \App\Models\FinanceBranch::count() + \App\Models\FinanceItem::count();
            return redirect()->back()->with('success', "{$count} baris Finance berhasil diimpor dan langsung tampil di tab Finance.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Gagal mengimpor file: " . $e->getMessage());
        }
    }
}
