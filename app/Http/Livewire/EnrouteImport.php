<?php
// app/Http/Livewire/EnrouteImport.php

namespace App\Http\Livewire;

use App\Models\EnrouteUpload;
use App\Models\EnrouteData;
use App\Models\Airline;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.admin')]
class EnrouteImport extends Component
{
    use WithPagination, WithFileUploads;

    public $fileImport;
    public $search = '';
    public $showDetail = false;
    public $selectedUploadId;
    public $selectedUpload;
    public $mapping = [];
    public $previewData = [];
    public $step = 'upload'; // upload, mapping, import

    public $sortField = 'tanggal_jam';
    public $sortDirection = 'desc';

    // Mapping default (bisa disimpan di database nanti)
    public $columnMapping = [
        'aircraft_id' => 1,
        'adep' => 2,
        'ades' => 3,
        'dof' => 4,
        'registrasi' => 5,
        'type' => 6,
        'point_in' => 7,
        'time_in' => 8,
        'point_out' => 9,
        'time_out' => 10,
        'faktor_jarak' => 11,
        'faktor_berat' => 12,
        'route_unit' => 13,
        'route_charge' => 14,
        'flight_type' => 15,
        'currency' => 16 // Kolom baru untuk mata uang
    ];

    public function updatedFileImport()
    {
        $this->validate(['fileImport' => 'required|mimes:xlsx,xls,csv|max:10240']);
        $this->preview();
    }

    public function preview()
    {
        try {
            $path = $this->fileImport->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            // Ambil header (baris pertama)
            $headers = array_shift($rows);
            
            // Preview 5 baris pertama
            $this->previewData = [
                'headers' => $headers,
                'rows' => array_slice($rows, 0, 5),
                'total_rows' => count($rows)
            ];
            
            // Deteksi otomatis mapping berdasarkan header
            $this->autoDetectMapping($headers);
            
            $this->step = 'mapping';
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membaca file: ' . $e->getMessage());
        }
    }

    public function autoDetectMapping($headers)
    {
        $keywords = [
            'aircraft_id' => ['aircraft', 'flight', 'no', 'id', 'penerbangan'],
            'adep' => ['adep', 'from', 'asal', 'departure', 'dari'],
            'ades' => ['ades', 'to', 'tujuan', 'arrival', 'ke'],
            'dof' => ['dof', 'date', 'tanggal', 'flight date'],
            'registrasi' => ['registrasi', 'reg', 'register', 'pk'],
            'type' => ['type', 'tipe', 'ac type', 'aircraft type', 'pesawat'],
            'point_in' => ['point in', 'entry', 'masuk'],
            'time_in' => ['time in', 'time masuk', 'waktu masuk'],
            'point_out' => ['point out', 'exit', 'keluar'],
            'time_out' => ['time out', 'time keluar', 'waktu keluar'],
            'faktor_jarak' => ['faktor jarak', 'distance', 'jarak'],
            'faktor_berat' => ['faktor berat', 'weight', 'berat'],
            'route_unit' => ['route unit', 'unit'],
            'route_charge' => ['route charge', 'charge', 'biaya', 'cost'],
            'flight_type' => ['flight type', 'type penerbangan', 'jenis'],
            'currency' => ['currency', 'mata uang', 'kurs', 'rp', 'usd', 'idr']
        ];

        foreach ($headers as $index => $header) {
            $headerLower = strtolower(trim($header));
            
            foreach ($keywords as $field => $patterns) {
                foreach ($patterns as $pattern) {
                    if (str_contains($headerLower, $pattern)) {
                        $this->columnMapping[$field] = $index;
                        
                        // Deteksi currency dari header
                        if ($field == 'currency' || str_contains($headerLower, 'rp') || str_contains($headerLower, 'idr')) {
                            $this->columnMapping['currency'] = $index;
                        }
                        if (str_contains($headerLower, 'usd') || str_contains($headerLower, '$')) {
                            $this->columnMapping['currency'] = $index;
                        }
                        break;
                    }
                }
            }
        }
    }

    public function processImport()
    {
        try {
            $path = $this->fileImport->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $rows = $spreadsheet->getActiveSheet()->toArray();
            
            // Hapus header
            array_shift($rows);

            DB::beginTransaction();

            // Buat record upload
            $upload = EnrouteUpload::create([
                'file_name' => $this->fileImport->getClientOriginalName(),
                'uploaded_by' => auth()->user()->name ?? 'System',
                'status' => 'processed'
            ]);

            $successCount = 0;
            $dofValues = [];

            foreach ($rows as $row) {
                // Ambil data berdasarkan mapping
                $aircraftId = $this->getMappedValue($row, 'aircraft_id');
                if (empty($aircraftId)) continue; // Skip jika tidak ada aircraft ID

                // Ekstrak kode airline
                $airline3Code = substr($aircraftId, 0, 3);
                $airline = Airline::where('airline3_code', $airline3Code)->first();

                // Parse DOF
                $dof = $this->parseDate($this->getMappedValue($row, 'dof'));
                if ($dof) {
                    $dofValues[] = $dof;
                }

                // Parse route charge dan deteksi currency
                $routeCharge = $this->parseNumber($this->getMappedValue($row, 'route_charge'));
                $currency = $this->detectCurrency(
                    $this->getMappedValue($row, 'currency'),
                    $this->getMappedValue($row, 'route_charge')
                );

                // Simpan data
                EnrouteData::create([
                    'id_enroute_upload' => $upload->id_enroute_upload,
                    'aircraft_id' => $aircraftId,
                    'airline3_code' => $airline3Code,
                    'id_airline' => $airline->id ?? null,
                    'adep' => $this->getMappedValue($row, 'adep'),
                    'ades' => $this->getMappedValue($row, 'ades'),
                    'dof' => $dof,
                    'registrasi' => $this->getMappedValue($row, 'registrasi'),
                    'type' => $this->getMappedValue($row, 'type'),
                    'point_in' => $this->getMappedValue($row, 'point_in'),
                    'time_in' => $this->getMappedValue($row, 'time_in'),
                    'point_out' => $this->getMappedValue($row, 'point_out'),
                    'time_out' => $this->getMappedValue($row, 'time_out'),
                    'faktor_jarak' => $this->parseNumber($this->getMappedValue($row, 'faktor_jarak')),
                    'faktor_berat' => $this->parseNumber($this->getMappedValue($row, 'faktor_berat'), true),
                    'route_unit' => $this->parseNumber($this->getMappedValue($row, 'route_unit')),
                    'route_charge' => $routeCharge,
                    'currency' => $currency,
                    'flight_type' => $this->getMappedValue($row, 'flight_type'),
                ]);

                $successCount++;
            }

            // Update range tanggal
            if (!empty($dofValues)) {
                $upload->update([
                    'tanggal_awal' => min($dofValues),
                    'tanggal_akhir' => max($dofValues),
                    'total_rows' => $successCount
                ]);
            }

            DB::commit();

            session()->flash('message', "Import berhasil! {$successCount} data tersimpan.");
            $this->reset(['fileImport', 'previewData', 'step']);
            $this->step = 'upload';

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import failed: ' . $e->getMessage());
            session()->flash('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    private function getMappedValue($row, $field)
    {
        $index = $this->columnMapping[$field] ?? null;
        if ($index === null || !isset($row[$index])) {
            return null;
        }
        return trim($row[$index]);
    }

    private function parseDate($value)
    {
        if (empty($value)) return null;
        
        try {
            // Coba parse sebagai Excel date
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            
            // Coba parse sebagai string tanggal
            $date = date_create_from_format('d/m/Y', $value);
            if ($date) return $date->format('Y-m-d');
            
            $date = date_create_from_format('Y-m-d', $value);
            if ($date) return $date->format('Y-m-d');
            
            return date('Y-m-d', strtotime($value));
            
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseNumber($value, $integer = false)
    {
        if (empty($value)) return null;
        
        // Handle format Indonesia (Rp1.328,580)
        if (is_string($value)) {
            // Hapus "Rp", spasi, dan titik ribuan
            $value = preg_replace('/[Rp\s]/i', '', $value);
            
            // Ganti koma desimal dengan titik
            if (strpos($value, ',') !== false) {
                $value = str_replace('.', '', $value); // Hapus titik ribuan
                $value = str_replace(',', '.', $value); // Ganti koma desimal
            }
        }
        
        $number = floatval($value);
        return $integer ? intval($number) : $number;
    }

    private function detectCurrency($currencyField, $chargeField)
    {
        // Cek dari kolom currency jika ada
        if (!empty($currencyField)) {
            $curr = strtoupper(trim($currencyField));
            if (in_array($curr, ['USD', 'IDR', 'EUR', 'SGD'])) {
                return $curr;
            }
        }
        
        // Deteksi dari format charge
        if (is_string($chargeField)) {
            if (strpos($chargeField, '$') !== false || strpos($chargeField, 'USD') !== false) {
                return 'USD';
            }
            if (strpos($chargeField, 'Rp') !== false) {
                return 'IDR';
            }
        }
        
        return 'IDR'; // Default
    }

    public function showDetail($id)
    {
        $this->selectedUploadId = $id;
        $this->selectedUpload = EnrouteUpload::with('enrouteData.airline')->find($id);
        $this->showDetail = true;
    }

    public function backToUploads()
    {
        $this->showDetail = false;
        $this->selectedUploadId = null;
        $this->selectedUpload = null;
    }

    public function delete($id)
    {
        EnrouteUpload::destroy($id);
        session()->flash('message', 'Data upload berhasil dihapus');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        if ($this->step == 'mapping') {
            return view('livewire.enroute-mapping', [
                'preview' => $this->previewData,
                'mapping' => $this->columnMapping
            ])->layout('layouts.admin');
        }

        if ($this->showDetail) {
            $detailData = EnrouteData::with('airline')
                ->where('id_enroute_upload', $this->selectedUploadId)
                ->when($this->search, fn($q) => $q->where('aircraft_id', 'like', "%{$this->search}%")
                    ->orWhere('adep', 'like', "%{$this->search}%")
                    ->orWhere('ades', 'like', "%{$this->search}%"))
                ->orderBy('dof', 'desc')
                ->paginate(20, pageName: 'detail-page');

            return view('livewire.enroute-detail', [
                'detailData' => $detailData
            ])->layout('layouts.admin');
        }

        $uploads = EnrouteUpload::query()
            ->when($this->search, fn($q) => $q->where('file_name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.enroute-index', [
            'uploads' => $uploads
        ])->layout('layouts.admin');
    }
}