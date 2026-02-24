<?php
// app/Models/TrafficUpload.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrafficUpload extends Model
{
    protected $table = 'traffic_upload';
    protected $primaryKey = 'id_traffic_upload';

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

    public function trafficData(): HasMany
    {
        return $this->hasMany(TrafficData::class, 'id_traffic_upload', 'id_traffic_upload');
    }

    public function getRangeTanggalAttribute()
    {
        if ($this->tanggal_awal && $this->tanggal_akhir) {
            return $this->tanggal_awal->format('d/m/Y') . ' - ' . $this->tanggal_akhir->format('d/m/Y');
        }
        return '-';
    }
}