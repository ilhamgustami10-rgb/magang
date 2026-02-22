<?php
// app/Models/TerminalData.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerminalData extends Model
{
    protected $table = 'terminal_data';
    protected $primaryKey = 'id_terminal_data';

    protected $fillable = [
        'id_terminal_upload',
        'aircraft_id',
        'airline3_code',
        'id_airline',
        'bandara',
        'tanggal',
        'registrasi',
        'type',
        'terminal',
        'waktu_kedatangan',
        'waktu_keberangkatan',
        'gate',
        'parking_stand',
        'biaya_terminal',
        'currency',
        'biaya_terminal_idr',
        'exchange_rate',
        'status_penerbangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_kedatangan' => 'string',
        'waktu_keberangkatan' => 'string',
        'biaya_terminal' => 'decimal:2',
        'biaya_terminal_idr' => 'decimal:2',
        'exchange_rate' => 'decimal:4'
    ];

    public function upload(): BelongsTo
    {
        return $this->belongsTo(TerminalUpload::class, 'id_terminal_upload', 'id_terminal_upload');
    }

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class, 'id_airline', 'id');
    }

    public function getBiayaTerminalFormattedAttribute()
    {
        $amount = $this->biaya_terminal ?? 0;
        $currency = $this->currency ?? 'IDR';
        
        if ($currency == 'USD') {
            return '$ ' . number_format($amount, 2, '.', ',');
        }
        
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}