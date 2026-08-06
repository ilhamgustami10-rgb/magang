<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'report_date',
        'source',
        'rows_imported',
        'branches_count',
        'items_count',
        'skipped_count',
    ];

    public function budgetRealisasi()
    {
        return $this->hasMany(BudgetRealisasi::class, 'import_id');
    }
}
