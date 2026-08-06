<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceItem extends Model
{
    protected $fillable = [
        'branch_id', 'code', 'name', 'rkap', 'release_budget', 'commitment', 'total_consume', 'available_budget'
    ];

    public function branch()
    {
        return $this->belongsTo(FinanceBranch::class, 'branch_id');
    }
}
