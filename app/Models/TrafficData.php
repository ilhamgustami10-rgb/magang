<?php
// app/Models/TrafficData.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrafficData extends Model
{
    protected $table = 'traffic_data';
    protected $primaryKey = 'id_traffic_data';

    protected $fillable = [
        'id_traffic_upload',
        'tanggal',
        'aircraft_id',
        'airline3_code',
        'id_airline',
        'registrasi',
        'type',
        'adep',
        'ades',
        'eobt',
        'pushback',
        'taxi',
        'dep_arr_lcl',
        'atd',
        'eta',
        'ata',
        'ruid_dep',
        'rui_arr',
        'parking_dep',
        'parking_arr',
        'pob',
        'remark',
        'status_flight'
    ];

    protected $casts = [
        'tanggal' => 'date'
    ];

    public function upload(): BelongsTo
    {
        return $this->belongsTo(TrafficUpload::class, 'id_traffic_upload', 'id_traffic_upload');
    }

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class, 'id_airline', 'id');
    }

    public function getRuteAttribute()
    {
        return $this->adep . ' → ' . $this->ades;
    }
}