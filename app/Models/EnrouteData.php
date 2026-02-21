<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrouteData extends Model
{
    protected $table = 'enroute_data';
    protected $primaryKey = 'id_enroute_data';

    protected $fillable = [
        'id_enroute_upload',
        'aircraft_id',
        'airline3_code',
        'id_airline',
        'adep',
        'ades',
        'dof',
        'registrasi',
        'type',
        'point_in',
        'time_in',
        'point_out',
        'time_out',
        'faktor_jarak',
        'faktor_berat',
        'route_unit',
        'enroute_charge',
        'currency',
        'enroute_charge_idr',
        'exchange_rate',
        'flight_type'
    ];

    protected $casts = [
        'dof' => 'date',
        'time_in' => 'string',
        'time_out' => 'string',
        'faktor_jarak' => 'decimal:2',
        'route_unit' => 'decimal:2',
        'route_charge' => 'decimal:2',
        'route_charge_idr' => 'decimal:2',
        'exchange_rate' => 'decimal:4'
    ];

    public function upload(): BelongsTo
    {
        return $this->belongsTo(EnrouteUpload::class, 'id_enroute_upload', 'id_enroute_upload');
    }

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class, 'id_airline', 'id');
    }

    public function getRouteChargeFormattedAttribute()
    {
        $amount = $this->route_charge ?? 0;
        $currency = $this->currency ?? 'IDR';
        
        if ($currency == 'USD') {
            return '$ ' . number_format($amount, 2, '.', ',');
        }
        
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}