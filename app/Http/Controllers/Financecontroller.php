<?php

namespace App\Http\Controllers;

use App\Models\FinanceData;

class FinanceController extends Controller
{
    public function index()
    {
        $financeData = FinanceData::query()
            ->orderBy('branch')->orderBy('funds_center')->get()
            ->groupBy('branch')
            ->map(fn ($rows) => $rows->map(fn ($row) => [
                'item' => $row->funds_center,
                'rkap' => (float) $row->rkap,
                'release_budget' => (float) $row->release_budget,
                'commitment' => (float) $row->commitment,
                'total_consume' => (float) $row->total_consume,
                'available_budget' => (float) $row->available_budget,
            ])->values()->all())
            ->all();

        return view('finance', compact('financeData'));
    }
}
