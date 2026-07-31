<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetRealisasi extends Model
{
    protected $table = 'budget_realisasi';
    protected $guarded = [];

    public function importLog()
    {
        return $this->belongsTo(ImportLog::class, 'import_id');
    }
}
