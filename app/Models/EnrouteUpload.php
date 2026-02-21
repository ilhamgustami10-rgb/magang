<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnrouteUpload extends Model
{
    protected $table = 'enroute_upload';
    protected $primaryKey = 'id_enroute_upload';

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

    public function enrouteData(): HasMany
    {
        return $this->hasMany(EnrouteData::class, 'id_enroute_upload', 'id_enroute_upload');
    }

    public function getRangeTanggalAttribute()
    {
        if ($this->tanggal_awal && $this->tanggal_akhir) {
            return $this->tanggal_awal->format('d/m/Y') . ' - ' . $this->tanggal_akhir->format('d/m/Y');
        }
        return '-';
    }
}