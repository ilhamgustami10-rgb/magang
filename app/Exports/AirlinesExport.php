<?php

namespace App\Exports;

use App\Models\Airline;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

// Pastikan "implements" ada di sini
class AirlinesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Sesuaikan nama kolom dengan database Anda
        return Airline::select('airline3_code', 'airline_name', 'airline_country')->get();
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Nama Maskapai',
            'Negara',
        ];
    }
}