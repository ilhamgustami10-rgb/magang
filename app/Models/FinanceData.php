<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceData extends Model
{
    protected $table = 'finance_data';

    protected $fillable = ['branch', 'funds_center', 'rkap', 'release_budget', 'commitment', 'total_consume', 'available_budget'];

    protected $casts = [
        'rkap' => 'decimal:2', 'release_budget' => 'decimal:2', 'commitment' => 'decimal:2',
        'total_consume' => 'decimal:2', 'available_budget' => 'decimal:2',
    ];
}
