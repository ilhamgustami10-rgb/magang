<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceUpload extends Model
{
    protected $table = 'finance_uploads';

    protected $fillable = [
        'file_name',
        'uploaded_by',
        'total_rows',
    ];
}
