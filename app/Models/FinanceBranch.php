<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceBranch extends Model
{
    protected $fillable = [
        'code', 'name', 'rkap', 'release_budget', 'commitment', 'total_consume', 'available_budget'
    ];

    public function items()
    {
        return $this->hasMany(FinanceItem::class, 'branch_id');
    }
}
