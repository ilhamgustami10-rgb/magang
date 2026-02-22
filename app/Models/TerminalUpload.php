<?php
// app/Models/TerminalUpload.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TerminalUpload extends Model
{
    protected $table = 'terminal_upload';
    protected $primaryKey = 'id_terminal_upload';

    protected $fillable = [
        'file_name',
        'uploaded_by',
        'tanggal_awal',
        'tanggal_akhir',
        'status',
        'total_rows'
    ];

    protected $casts = [
        'tanggal_jam' => 'datetime',
        'tanggal_awal' => 'date',
        'tanggal_akhir' => 'date'
    ];

    public function terminalData(): HasMany
    {
        return $this->hasMany(TerminalData::class, 'id_terminal_upload', 'id_terminal_upload');
    }

    public function getRangeTanggalAttribute()
    {
        if ($this->tanggal_awal && $this->tanggal_akhir) {
            return $this->tanggal_awal->format('d/m/Y') . ' - ' . $this->tanggal_akhir->format('d/m/Y');
        }
        return '-';
    }
}