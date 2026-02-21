<?php
use App\Models\EnrouteUpload;
use App\Models\EnrouteData;
use App\Models\Airline;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use App\Services\CurrencyService;

new #[Layout('layouts.admin')] 
class extends Component {
    use WithPagination, WithFileUploads;

    public $search = '';
    public $filterAirline = '';
    public $filterMonth = '';
    public $fileImport;
    public $showDetail = false;
    public $selectedUploadId;
    public $selectedUpload;

    public $sortField = 'tanggal_jam';
    public $sortDirection = 'desc';

    public function __construct()
    {
        parent::__construct();
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
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

    public function deleteDetail($id)
    {
        EnrouteData::destroy($id);
        $this->selectedUpload = EnrouteUpload::with('enrouteData.airline')->find($this->selectedUploadId);
        session()->flash('message', 'Data penerbangan berhasil dihapus');
    }

    public function import()
    {
        $this->prosesImport();
    }

    private function parseRouteCharge($value)
    {
        if (empty($value)) {
            return ['value' => null, 'currency' => 'IDR'];
        }
        
        $value = trim($value);
        $currency = 'IDR';
        $number = null;
        
        // Deteksi currency
        if (strpos($value, '$') !== false) {
            $currency = 'USD';
        }
        
        // Bersihkan dari Rp, $, spasi
        $clean = preg_replace('/[Rp$\s]/i', '', $value);
        
        // Handle format dengan koma sebagai pemisah ribuan
        if (preg_match('/^\d{1,3}(,\d{3})*(\.\d+)?$/', $clean)) {
            // Format US: 1,303.09
            $clean = str_replace(',', '', $clean); // Hapus koma ribuan
            $number = floatval($clean);
        }
        // Handle format Indonesia: 1.328,580 atau 1,328,580 (Rp)
        elseif (preg_match('/^\d{1,3}(\.\d{3})*(,\d+)?$/', $clean) || preg_match('/^\d{1,3}(,\d{3})*(\.\d+)?$/', $clean)) {
            $clean = str_replace('.', '', $clean); // Hapus titik ribuan
            $clean = str_replace(',', '.', $clean); // Ganti koma desimal
            $number = floatval($clean);
        } else {
            $number = floatval($clean);
        }
        
        return [
            'value' => $number,
            'currency' => $currency
        ];
    }
    
    public function prosesImport()
    {
        try {
            $this->validate([
                'fileImport' => 'required|file|max:5120'
            ]);
            
            $extension = $this->fileImport->getClientOriginalExtension();
            $allowed = ['xlsx', 'xls', 'csv'];
            
            if (!in_array(strtolower($extension), $allowed)) {
                session()->flash('error', 'File harus berformat: ' . implode(', ', $allowed));
                return;
            }
            
            // ============================================
            // TAHAP 6: Simpan ke tabel enroute_upload dan enroute_data
            // ============================================
            $path = $this->fileImport->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $rows = $spreadsheet->getActiveSheet()->toArray();
            
            // Buang 5 baris pertama (header)
            array_shift($rows); array_shift($rows); array_shift($rows); 
            array_shift($rows); array_shift($rows);
            
            // Ambil header kolom (baris ke-6)
            $headers = array_shift($rows);
            
            // Filter data bersih
            $dataBersih = [];
            foreach ($rows as $row) {
                if (empty(array_filter($row))) continue;
                if (!empty($row[0]) && stripos($row[0], 'total') !== false) continue;
                $dataBersih[] = $row;
            }
            
            $totalData = count($dataBersih);
            
            // ============================================
            // INIT CURRENCY SERVICE & KUMPULKAN TANGGAL
            // ============================================
            $currencyService = new \App\Services\CurrencyService();
            
            // Kumpulkan semua tanggal DOF
            $uniqueDates = [];
            foreach ($dataBersih as $row) {
                if (!empty($row[4])) {
                    $tanggal = trim($row[4]);
                    if (strpos($tanggal, ' ') !== false) {
                        $tanggal = explode(' ', $tanggal)[0];
                    }
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                        $uniqueDates[$tanggal] = true;
                    }
                }
            }
            
            // Ambil kurs untuk semua tanggal (batch request)
            $rates = [];
            if (!empty($uniqueDates)) {
                $dates = array_keys($uniqueDates);
                $minDate = min($dates);
                $maxDate = max($dates);
                
                \Log::info("Ambil kurs dari {$minDate} sampai {$maxDate}");
                $rates = $currencyService->getRatesForDateRange($minDate, $maxDate);
            }
            
            // SIMPAN KE DATABASE
            DB::beginTransaction();
            
            // 1. Simpan ke enroute_upload
            $upload = EnrouteUpload::create([
                'file_name' => $this->fileImport->getClientOriginalName(),
                'uploaded_by' => auth()->user()->name ?? 'System',
                'status' => 'processed',
                'total_rows' => $totalData
            ]);
            
            // 2. Simpan detail ke enroute_data
            $successCount = 0;
            $dofValues = [];
            
            foreach ($dataBersih as $row) {
                // Mapping sesuai struktur Excel
                $aircraftId = $row[1] ?? null;      // Kolom Aircraft ID
                if (empty($aircraftId)) continue;
                
                $airline3Code = substr($aircraftId, 0, 3);
                $airline = Airline::where('airline3_code', $airline3Code)->first();
                
                // Parse DOF
                $dof = null;
                if (!empty($row[4])) {
                    $tanggal = trim($row[4]); // "2026-01-01 00:00:00" atau "1/6/2026"
                    
                    // Ambil hanya tanggalnya (jika ada waktu)
                    if (strpos($tanggal, ' ') !== false) {
                        $tanggal = explode(' ', $tanggal)[0];
                    }
                    
                    // Cek format Y-m-d (2026-01-31)
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                        $dof = $tanggal;
                        $dofValues[] = $dof;
                    }
                    // Cek format m/d/yyyy atau mm/dd/yyyy (US format)
                    elseif (preg_match('#^\d{1,2}/\d{1,2}/\d{4}$#', $tanggal)) {
                        $parts = explode('/', $tanggal);
                        // parts[0] = bulan, parts[1] = tanggal, parts[2] = tahun
                        $bulan = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                        $hari = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
                        $tahun = $parts[2];
                        $dof = $tahun . '-' . $bulan . '-' . $hari;
                        $dofValues[] = $dof;
                    } else {
                        \Log::warning('Format DOF tidak dikenal: ' . $tanggal);
                        continue;
                    }
                } else {
                    continue;
                }

                // SEBELUM KONVERSI, CEK SEMUA DATA USD
                if (strpos($row[14] ?? '', '$') !== false) {
                    \Log::info('=== DATA USD DITEMUKAN ===');
                    \Log::info('Baris lengkap: ' . json_encode($row));
                    \Log::info('Kolom 14 (asli): ' . ($row[14] ?? 'NULL'));
                    \Log::info('Hasil parse: ' . json_encode($chargeData));
                    \Log::info('dof: ' . ($dof ?? 'NULL'));
                    \Log::info('rates[$dof]: ' . (isset($rates[$dof]) ? $rates[$dof] : 'TIDAK ADA'));
                }

                // PARSE ROUTE CHARGE
                $chargeData = $this->parseRouteCharge($row[14] ?? null);
                
                // CEK apakah $chargeData array
                if (is_array($chargeData)) {
                    $enrouteCharge = $chargeData['value'] ?? null;
                    $currency = $chargeData['currency'] ?? 'IDR';
                } else {
                    \Log::error('parseRouteCharge tidak mengembalikan array: ' . json_encode($chargeData));
                    $enrouteCharge = null;
                    $currency = 'IDR';
                }
                
                // ============================================
                // KONVERSI KE IDR (dengan FALLBACK)
                // ============================================
                $exchangeRate = null;
                $enrouteChargeIdr = null;

                if ($enrouteCharge) {
                    if ($currency == 'USD' && $dof) {
                        // USD: konversi pakai kurs dengan fallback
                        $exchangeRate = $currencyService->getRateWithFallback($dof, $rates);
                        $enrouteChargeIdr = $enrouteCharge * $exchangeRate;
                        \Log::info("USD → IDR: {$enrouteCharge} x {$exchangeRate} = {$enrouteChargeIdr} (dof: {$dof})");
                        
                    } elseif ($currency == 'IDR') {
                        // IDR: langsung pakai nilainya
                        $enrouteChargeIdr = $enrouteCharge;
                        \Log::info("IDR: {$enrouteChargeIdr}");
                    }
                }

                // SIMPAN DATA
                EnrouteData::create([
                    'id_enroute_upload' => $upload->id_enroute_upload,
                    'aircraft_id' => $aircraftId,
                    'airline3_code' => $airline3Code,
                    'id_airline' => $airline->id ?? null,
                    'adep' => $row[2] ?? null,
                    'ades' => $row[3] ?? null,
                    'dof' => $dof,
                    'registrasi' => $row[5] ?? null,
                    'type' => $row[6] ?? null,
                    'point_in' => $row[7] ?? null,
                    'time_in' => !empty($row[8]) ? $row[8] : '00:00',
                    'point_out' => $row[9] ?? null,
                    'time_out' => !empty($row[10]) ? $row[10] : '00:00',
                    'faktor_jarak' => $this->parseNumber($row[11] ?? null),
                    'faktor_berat' => $this->parseNumber($row[12] ?? null, true),
                    'route_unit' => $this->parseNumber($row[13] ?? null),
                    'enroute_charge' => $enrouteCharge,
                    'currency' => $currency,
                    'exchange_rate' => $exchangeRate,
                    'enroute_charge_idr' => $enrouteChargeIdr,
                    'flight_type' => $row[15] ?? null,
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
            
            session()->flash('message', 
                "✅ DATA BERHASIL DISIMPAN!<br>" .
                "ID Upload: " . $upload->id_enroute_upload . "<br>" .
                "File: " . $upload->file_name . "<br>" .
                "Total data: " . $successCount . " baris<br>" .
                "Range DOF: " . ($upload->tanggal_awal ?? '-') . " s/d " . ($upload->tanggal_akhir ?? '-')
            );
            
            $this->reset('fileImport');
            $this->dispatch('$refresh');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error: ' . $e->getMessage() . ' - Line: ' . $e->getLine());
            \Log::error('Import error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
        }
    }

    // Helper functions
    private function parseNumber($value, $integer = false)
    {
        if (empty($value)) return null;
        
        if (is_string($value)) {
            $value = preg_replace('/[Rp$\s]/i', '', $value);
            
            if (strpos($value, ',') !== false && preg_match('/,\d{2}$/', $value)) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            }
        }
        
        $number = floatval($value);
        return $integer ? intval($number) : $number;
    }

    private function parseDecimal($value)
    {
        if (empty($value)) return null;
        
        if (is_string($value) && strpos($value, ',') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }
        
        return floatval($value);
    }

    private function parseInt($value)
    {
        if (empty($value)) return null;
        return intval(preg_replace('/[^0-9]/', '', $value));
    }

    public function export()
    {
        // Implementasi export
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

    public function with() {
        if ($this->showDetail) {
            return [
                'detailData' => EnrouteData::with('airline')
                    ->where('id_enroute_upload', $this->selectedUploadId)
                    ->when($this->search, fn($q) => $q->where('aircraft_id', 'like', "%{$this->search}%")
                        ->orWhere('adep', 'like', "%{$this->search}%")
                        ->orWhere('ades', 'like', "%{$this->search}%"))
                    ->when($this->filterAirline, fn($q) => $q->where('airline3_code', $this->filterAirline))
                    ->orderBy('dof', 'desc')
                    ->paginate(20, pageName: 'detail-page')
            ];
        }

        return [
            'uploads' => EnrouteUpload::query()
                ->when($this->search, fn($q) => $q->where('file_name', 'like', "%{$this->search}%")
                    ->orWhere('uploaded_by', 'like', "%{$this->search}%"))
                ->when($this->filterMonth, function($q) {
                    [$year, $month] = explode('-', $this->filterMonth);
                    $q->whereYear('tanggal_jam', $year)
                      ->whereMonth('tanggal_jam', $month);
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(10),
            'airlines' => Airline::orderBy('airline_name')->get(),
            'months' => EnrouteUpload::select(DB::raw("DATE_FORMAT(tanggal_jam, '%Y-%m') as month"))
                ->distinct()
                ->orderBy('month', 'desc')
                ->pluck('month')
        ];
    }
};
?>

<div class="space-y-6">
    <header class="flex justify-between items-center">
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">
            @if($showDetail)
                <button wire:click="backToUploads" class="mr-2 text-indigo-600 hover:text-indigo-800 transition">
                    ←
                </button>
                Detail Upload: {{ $selectedUpload->file_name }}
            @else
                Data Enroute Charge
            @endif
        </h1>
        @if(!$showDetail)
        <div class="flex items-center gap-2 bg-indigo-50 px-4 py-2 rounded-2xl">
            <span class="text-xs font-bold text-indigo-600 uppercase">Total Upload</span>
            <span class="text-xl font-black text-indigo-900">{{ \App\Models\EnrouteUpload::count() }}</span>
        </div>
        @endif
    </header>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-100 text-sm font-bold animate-pulse">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 text-sm font-bold">
            {{ session('error') }}
        </div>
    @endif

    @if($showDetail)
        {{-- DETAIL VIEW --}}
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <div class="mb-6 grid md:grid-cols-4 gap-4">
                <div class="bg-slate-50 p-4 rounded-xl">
                    <div class="text-xs font-bold text-slate-400 uppercase">Range Tanggal</div>
                    <div class="text-lg font-black text-slate-800">{{ $selectedUpload->range_tanggal }}</div>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl">
                    <div class="text-xs font-bold text-slate-400 uppercase">Total Data</div>
                    <div class="text-lg font-black text-slate-800">{{ $selectedUpload->total_rows ?? $selectedUpload->enrouteData->count() }} baris</div>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl">
                    <div class="text-xs font-bold text-slate-400 uppercase">Diupload oleh</div>
                    <div class="text-lg font-black text-slate-800">{{ $selectedUpload->uploaded_by ?? 'System' }}</div>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl">
                    <div class="text-xs font-bold text-slate-400 uppercase">Tanggal Upload</div>
                    <div class="text-lg font-black text-slate-800">{{ $selectedUpload->tanggal_jam->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <div class="space-y-6 mb-8">
                <div class="relative flex-1 group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.500ms="search" 
                           type="text" 
                           placeholder="Cari aircraft ID, rute (ADEP/ADES)..." 
                           class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm">
                </div>
            </div>

            <div class="overflow-x-auto border border-slate-50 rounded-2xl">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase text-center w-12">No</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase">Aircraft ID</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase">Airline</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase">Rute</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase">DOF</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase">Type</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase">Time In/Out</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase">Route Charge</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase text-center w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm">
                        @forelse($detailData as $index => $data)
                            <tr wire:key="detail-{{ $data->id_enroute_data }}" class="hover:bg-slate-50 transition">
                                <td class="p-3 text-center text-slate-400 font-mono">{{ $detailData->firstItem() + $index }}</td>
                                <td class="p-3 font-mono font-bold text-slate-800">{{ $data->aircraft_id }}</td>
                                <td class="p-3">
                                    <span class="font-bold text-indigo-600">{{ $data->airline3_code }}</span>
                                    @if($data->airline)
                                        <span class="text-xs block text-slate-400">{{ $data->airline->airline_name }}</span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <span class="font-mono font-bold">{{ $data->adep }}</span> 
                                    <span class="text-slate-300 mx-1">→</span> 
                                    <span class="font-mono font-bold">{{ $data->ades }}</span>
                                </td>
                                <td class="p-3 font-mono">{{ $data->dof->format('d/m/Y') }}</td>
                                <td class="p-3 font-mono">{{ $data->type }}</td>
                                <td class="p-3 font-mono text-xs">
                                    {{ substr($data->time_in, 0, 5) }} / {{ substr($data->time_out, 0, 5) }}
                                </td>
                                <td class="p-3 font-mono font-bold">
                                    {{ $data->enroute_charge_formatted }}
                                    <span class="text-xs block text-slate-400">{{ $data->currency ?? 'IDR' }}</span>
                                </td>
                                <td class="p-3 text-center">
                                    <button wire:click="deleteDetail({{ $data->id_enroute_data }})" wire:confirm="Hapus data ini?" class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-12 text-center text-slate-400 font-medium italic">
                                    Tidak ada data dalam file upload ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6">
                {{ $detailData->links(data: ['scrollTo' => false]) }}
            </div>
        </div>

    @else
        {{-- LIST UPLOAD VIEW --}}
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            
            <div class="space-y-6 mb-8">
                <div class="flex flex-col md:flex-row gap-4 items-center">
                    <div class="relative flex-1 group w-full">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input wire:model.live.debounce.500ms="search" 
                               type="text" 
                               placeholder="Cari berdasarkan nama file..." 
                               class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm">
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4 pt-2 border-t border-slate-50">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center bg-white border border-slate-200 p-1.5 rounded-2xl shadow-sm">
                            <input type="file" wire:model="fileImport" id="fileInput" class="hidden" accept=".xlsx,.xls,.csv">
                            <label for="fileInput" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-slate-600 rounded-xl cursor-pointer text-xs font-bold transition-colors">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1.01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>{{ $fileImport ? $fileImport->getClientOriginalName() : 'Pilih Excel' }}</span>
                            </label>
                            <div class="w-px h-4 bg-slate-200 mx-1"></div>
                            <button wire:click="import" wire:loading.attr="disabled" class="px-4 py-2 bg-slate-900 hover:bg-black text-white rounded-xl font-bold text-xs transition-all disabled:opacity-50">
                                <span wire:loading.remove wire:target="import">Import</span>
                                <span wire:loading wire:target="import">Upload...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto border border-slate-50 rounded-2xl">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="p-4 text-xs font-black text-slate-500 uppercase text-center w-16">No</th>
                            <th wire:click="sortBy('file_name')" class="p-4 text-xs uppercase cursor-pointer {{ $sortField === 'file_name' ? 'font-black text-black' : 'font-medium text-slate-500 hover:text-black' }}">
                                Nama File
                            </th>
                            <th wire:click="sortBy('tanggal_jam')" class="p-4 text-xs uppercase cursor-pointer {{ $sortField === 'tanggal_jam' ? 'font-black text-black' : 'font-medium text-slate-500 hover:text-black' }}">
                                Tanggal Upload
                            </th>
                            <th class="p-4 text-xs font-black text-slate-500 uppercase">Range Tanggal</th>
                            <th class="p-4 text-xs font-black text-slate-500 uppercase text-center">Jumlah Data</th>
                            <th class="p-4 text-xs font-black text-slate-500 uppercase text-center">Status</th>
                            <th class="p-4 text-xs font-black text-slate-500 uppercase text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm">
                        @forelse($uploads as $index => $upload)
                            <tr wire:key="upload-{{ $upload->id_enroute_upload }}" class="hover:bg-slate-50 transition cursor-pointer" wire:click="showDetail({{ $upload->id_enroute_upload }})">
                                <td class="p-4 text-center text-slate-400 font-mono">{{ $uploads->firstItem() + $index }}</td>
                                <td class="p-4 font-mono font-bold text-indigo-600">{{ $upload->file_name }}</td>
                                <td class="p-4 text-slate-600">{{ $upload->tanggal_jam->format('d/m/Y H:i') }}</td>
                                <td class="p-4 font-mono text-slate-500">{{ $upload->range_tanggal }}</td>
                                <td class="p-4 text-center font-mono font-bold">{{ $upload->total_rows ?? $upload->enrouteData->count() }}</td>
                                <td class="p-4 text-center">
                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">{{ $upload->status }}</span>
                                </td>
                                <td class="p-4 text-center" wire:click.stop>
                                    <div class="flex justify-center gap-1">
                                        <button wire:click="delete({{ $upload->id_enroute_upload }})" wire:confirm="Hapus data upload ini?" class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-12 text-center text-slate-400 font-medium italic">
                                    Belum ada data upload.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6 border-t border-slate-50 pt-4">
                {{ $uploads->links() }}
            </div>
        </div>
    @endif
</div>