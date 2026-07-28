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
        
        // Tab per Cabang
        foreach ($branches as $branch) {
            $branchName = $branch->name;
            
            $financeData[$branchName] = [
                'rkap'       => 0,
                'release'    => 0,
                'commitment' => 0,
                'consume'    => 0,
                'available'  => 0,
                'items'      => [],
            ];
            
            foreach ($branch->items as $item) {
                // Tambahkan nilai item ke agregat cabang
                $financeData[$branchName]['rkap']       += $item->rkap;
                $financeData[$branchName]['release']    += $item->release_budget;
                $financeData[$branchName]['commitment'] += $item->commitment;
                $financeData[$branchName]['consume']    += $item->total_consume;
                $financeData[$branchName]['available']  += $item->available_budget;

                // Push detail item
                $financeData[$branchName]['items'][] = [
                    'code'       => $item->code,
                    'name'       => $item->name,
                    'rkap'       => (float) $item->rkap,
                    'release'    => (float) $item->release_budget,
                    'commitment' => (float) $item->commitment,
                    'consume'    => (float) $item->total_consume,
                    'available'  => (float) $item->available_budget,
                ];
            }
        }

        $activeFinanceFiles = \App\Models\FinanceUpload::latest()->pluck('file_name')->toArray();
        $lastUpload = \App\Models\FinanceUpload::latest()->first();
        $financeUpdatedAt = $lastUpload ? $lastUpload->created_at->format('d M Y') : null;

        return view('finance', compact('financeData', 'activeFinanceFiles', 'financeUpdatedAt'));
    }

    public function import(Request $request, FinanceImportService $importService)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        try {
            $importService->import($request->file('file')->getRealPath(), $request->file('file')->getClientOriginalName());
            
            $count = \App\Models\FinanceBranch::count() + \App\Models\FinanceItem::count();
            return redirect()->back()->with('success', "{$count} baris Finance berhasil diimpor dan langsung tampil di tab Finance.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Gagal mengimpor file: " . $e->getMessage());
        }
    }
}
