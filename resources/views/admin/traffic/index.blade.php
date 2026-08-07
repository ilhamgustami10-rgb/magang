<?php
use App\Models\TrafficUpload;
use App\Models\TrafficData;
use App\Models\Airline;
use App\Helpers\CsvHelper;
use Illuminate\Support\Facades\Log;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

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
    
    public function lihatDetail($id)
    {
        $this->selectedUploadId = $id;
        $this->selectedUpload = TrafficUpload::with('trafficData.airline')->find($id);
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
        TrafficUpload::destroy($id);
        session()->flash('message', 'Data upload berhasil dihapus');
    }

    public function deleteDetail($id)
    {
        TrafficData::destroy($id);
        $this->selectedUpload = TrafficUpload::with('trafficData.airline')->find($this->selectedUploadId);
        session()->flash('message', 'Data penerbangan berhasil dihapus');
    }

    public function import()
    {
        $this->prosesImport();
    }

    private function parseDate($value)
    {
        return CsvHelper::parseDate($value);
    }

    private function parseTime($value)
    {
        if ($value === null || $value === '') return null;

        // Excel time serial (pecahan dari 1 hari)
        if (is_numeric($value)) {
            $frac = (float)$value - floor((float)$value);
            return gmdate('H:i:s', (int) round($frac * 86400));
        }

        $value = trim((string)$value);
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
            return strlen($value) === 5 ? $value . ':00' : $value;
        }
        return null;
    }

    /**
     * Cari index kolom berdasarkan nama header (case-insensitive).
     */
    private function findCol(array $map, array $candidates)
    {
        foreach ($candidates as $c) {
            $c = strtolower(trim($c));
            if (array_key_exists($c, $map)) return $map[$c];
        }
        return null;
    }

    public function prosesImport()
    {
        try {
            @set_time_limit(300);
            @ini_set('memory_limit', '512M');
            DB::disableQueryLog();

            $this->validate([
                'fileImport' => 'required|file|max:20480'
            ]);

            $extension = strtolower($this->fileImport->getClientOriginalExtension());
            if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
                session()->flash('error', 'File harus berformat: xlsx, xls, csv');
                return;
            }

            $path = $this->fileImport->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $rows = $spreadsheet->getActiveSheet()->toArray();

            // === Deteksi baris header secara dinamis ===
            // (file punya beberapa baris metadata sebelum header asli)
            $headerIndex = null;
            foreach ($rows as $i => $row) {
                $lower = array_map(fn($c) => strtolower(trim((string)$c)), $row);
                if (in_array('dof', $lower) || in_array('aircraft id', $lower) || in_array('acid', $lower)) {
                    $headerIndex = $i;
                    break;
                }
            }
            if ($headerIndex === null) {
                session()->flash('error', 'Tidak menemukan baris header (kolom DOF / Aircraft ID). Periksa format file Excel.');
                return;
            }

            // Peta nama kolom -> index
            $map = [];
            foreach ($rows[$headerIndex] as $idx => $name) {
                $key = strtolower(trim((string)$name));
                if ($key !== '') $map[$key] = $idx;
            }

            $iDof     = $this->findCol($map, ['dof', 'tanggal', 'date', 'flight date']);
            $iAcid    = $this->findCol($map, ['aircraft id', 'acid', 'aircraft_id']);
            $iAdep    = $this->findCol($map, ['adep']);
            $iAdes    = $this->findCol($map, ['ades']);
            $iReg     = $this->findCol($map, ['registrasi', 'reg', 'a-reg']);
            $iType    = $this->findCol($map, ['type', 'tipe', 'a-type']);
            $iTimeIn  = $this->findCol($map, ['time in']);
            $iTimeOut = $this->findCol($map, ['time out']);

            $dataRows = array_slice($rows, $headerIndex + 1);

            $upload = TrafficUpload::create([
                'file_name'   => $this->fileImport->getClientOriginalName(),
                'uploaded_by' => auth()->user()->name ?? 'System',
                'status'      => 'processed',
                'total_rows'  => 0
            ]);

            $successCount  = 0;
            $skippedCount  = 0;
            $failedCount   = 0;
            $tanggalValues = [];
            $homeBase      = 'WARR'; // Kantor Cabang WARR (Juanda, Surabaya)

            $airlines = \App\Models\Airline::pluck('id', 'airline3_code');
            $batch = [];
            $now = now();

            foreach ($dataRows as $row) {
                if (!CsvHelper::isDataRow($row)) { 
                    $skippedCount++; 
                    continue; 
                }
                
                if (empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) {
                    $skippedCount++;
                    continue;
                }

                $tanggal = $this->parseDate($iDof !== null ? ($row[$iDof] ?? null) : null);
                if (!$tanggal) {
                    $failedCount++;
                    continue;
                }

                $aircraftId = $iAcid !== null ? trim((string)($row[$iAcid] ?? '')) : '';
                if ($aircraftId === '') {
                    $failedCount++;
                    continue;
                }

                $adep = $iAdep !== null ? trim((string)($row[$iAdep] ?? '')) : null;
                $ades = $iAdes !== null ? trim((string)($row[$iAdes] ?? '')) : null;

                // Turunkan arrival/departure relatif ke home base (WARR)
                $depArr = null;
                if ($ades && strtoupper($ades) === $homeBase) {
                    $depArr = 'A';
                } elseif ($adep && strtoupper($adep) === $homeBase) {
                    $depArr = 'D';
                }

                $airline3Code = substr($aircraftId, 0, 3);
                $airlineId = $airlines[$airline3Code] ?? null;

                $tanggalValues[] = $tanggal;

                $batch[] = [
                    'id_traffic_upload' => $upload->id_traffic_upload,
                    'tanggal'       => $tanggal,
                    'aircraft_id'   => $aircraftId,
                    'airline3_code' => $airline3Code,
                    'id_airline'    => $airlineId,
                    'registrasi'    => $iReg !== null ? ($row[$iReg] ?? null) : null,
                    'type'          => $iType !== null ? ($row[$iType] ?? null) : null,
                    'adep'          => $adep,
                    'ades'          => $ades,
                    'eobt'          => null,
                    'pushback'      => null,
                    'taxi'          => null,
                    'dep_arr_lcl'   => $depArr,
                    'atd'           => $iTimeIn !== null ? $this->parseTime($row[$iTimeIn] ?? null) : null,
                    'eta'           => null,
                    'ata'           => $iTimeOut !== null ? $this->parseTime($row[$iTimeOut] ?? null) : null,
                    'ruid_dep'      => null,
                    'rui_arr'       => null,
                    'parking_dep'   => null,
                    'parking_arr'   => null,
                    'pob'           => null,
                    'remark'        => null,
                    'status_flight' => 'REGULER',
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];

                $successCount++;
            }

            foreach (array_chunk($batch, 500) as $chunk) {
                DB::transaction(function () use ($chunk) {
                    DB::table('traffic_data')->insert($chunk);
                });
            }

            if (!empty($tanggalValues)) {
                $upload->update([
                    'tanggal_awal'  => min($tanggalValues),
                    'tanggal_akhir' => max($tanggalValues),
                    'total_rows'    => $successCount
                ]);
            } else {
                $upload->update(['total_rows' => $successCount]);
            }

            Log::info("Import traffic selesai: {$successCount} baris data, {$skippedCount} dilewati, {$failedCount} gagal");

            session()->flash('message',
                "\u{2705} DATA TRAFFIC BERHASIL DISIMPAN!<br>" .
                "ID Upload: " . $upload->id_traffic_upload . "<br>" .
                "File: " . $upload->file_name . "<br>" .
                "Berhasil: " . $successCount . " baris<br>" .
                "Dilewati: " . $skippedCount . " baris<br>" .
                "Gagal: " . $failedCount . " baris<br>" .
                "Range Tanggal: " . ($upload->tanggal_awal ?? '-') . " s/d " . ($upload->tanggal_akhir ?? '-')
            );

            $this->reset('fileImport');
            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage() . ' - Line: ' . $e->getLine());
            \Log::error('Import traffic error: ' . $e->getMessage());
        }
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
                'detailData' => TrafficData::with('airline')
                    ->where('id_traffic_upload', $this->selectedUploadId)
                    ->when($this->search, fn($q) => $q->where('aircraft_id', 'like', "%{$this->search}%")
                        ->orWhere('registrasi', 'like', "%{$this->search}%")
                        ->orWhere('adep', 'like', "%{$this->search}%")
                        ->orWhere('ades', 'like', "%{$this->search}%"))
                    ->when($this->filterAirline, fn($q) => $q->where('airline3_code', $this->filterAirline))
                    ->orderBy('tanggal', 'desc')
                    ->paginate(20, pageName: 'detail-page')
            ];
        }

        return [
            'uploads' => TrafficUpload::query()
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
            'months' => TrafficUpload::select(DB::raw("DATE_FORMAT(tanggal_jam, '%Y-%m') as month"))
                ->distinct()
                ->orderBy('month', 'desc')
                ->pluck('month')
        ];
    }
};
?>

<div class="space-y-6">
    @include('admin.traffic-tabs')
    <header class="flex justify-between items-center">
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">
            @if($showDetail)
                <button wire:click="backToUploads" class="mr-2 text-purple-600 hover:text-purple-800 transition">
                    ←
                </button>
                Detail Upload Traffic: {{ $selectedUpload->file_name }}
            @else
                Data Traffic Movement
            @endif
        </h1>
    </header>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-100 text-sm font-bold animate-pulse">
            {!! session('message') !!}
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
            
            {{-- HEADER DETAIL dengan Statistik --}}
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-black text-slate-800">{{ $selectedUpload->file_name }}</h2>
                        <p class="text-sm text-slate-500 mt-1">Detail data traffic</p>
                    </div>
                    <button wire:click="backToUploads" class="flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-sm font-bold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali
                    </button>
                </div>

                {{-- STATISTIK RINGKASAN --}}
                @php
                    $totalData = $selectedUpload->trafficData->count();
                    $uniqueAirlines = $selectedUpload->trafficData->pluck('airline3_code')->unique()->count();
                    $uniqueRoutes = $selectedUpload->trafficData->groupBy(function($item) {
                        return $item->adep . '-' . $item->ades;
                    })->count();
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-xl">
                        <div class="text-xs font-bold text-purple-600 uppercase tracking-wide mb-1">Total Data</div>
                        <div class="text-2xl font-black text-purple-900">{{ number_format($totalData) }}</div>
                        <div class="text-[10px] text-purple-500 mt-1">baris data</div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-xl">
                        <div class="text-xs font-bold text-purple-600 uppercase tracking-wide mb-1">Maskapai</div>
                        <div class="text-2xl font-black text-purple-900">{{ $uniqueAirlines }}</div>
                        <div class="text-[10px] text-purple-500 mt-1">unique airlines</div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-xl">
                        <div class="text-xs font-bold text-purple-600 uppercase tracking-wide mb-1">Rute</div>
                        <div class="text-2xl font-black text-purple-900">{{ $uniqueRoutes }}</div>
                        <div class="text-[10px] text-purple-500 mt-1">unique routes</div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-xl">
                        <div class="text-xs font-bold text-purple-600 uppercase tracking-wide mb-1">Status Flight</div>
                        <div class="text-2xl font-black text-purple-900">
                            @php
                                $reguler = $selectedUpload->trafficData->where('status_flight', 'REGULER')->count();
                                $other = $totalData - $reguler;
                            @endphp
                            {{ $reguler }} Reguler
                        </div>
                    </div>
                </div>

                {{-- INFO UPLOAD --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs bg-slate-50 p-4 rounded-xl">
                    <div>
                        <span class="text-slate-400 block">Range Tanggal</span>
                        <span class="font-bold text-slate-700">{{ $selectedUpload->range_tanggal }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Upload oleh</span>
                        <span class="font-bold text-slate-700">{{ $selectedUpload->uploaded_by ?? 'System' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Tanggal Upload</span>
                        <span class="font-bold text-slate-700">{{ $selectedUpload->tanggal_jam->format('d/m/Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Status</span>
                        <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-[10px] font-bold">{{ $selectedUpload->status }}</span>
                    </div>
                </div>
            </div>

            <div class="space-y-6 mb-8">
                <div class="relative flex-1 group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-purple-500 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.500ms="search" 
                           type="text" 
                           placeholder="Cari aircraft ID, registrasi, rute..." 
                           class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm">
                </div>
            </div>

            <div class="overflow-x-auto border border-slate-50 rounded-2xl">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase text-center w-12">No</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase">Tanggal</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase">ACID</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase">Airline</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase">Reg</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase">Type</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase">Rute</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase">ATD</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase">ATA</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase">Status</th>
                            <th class="p-3 text-xs font-black text-slate-500 uppercase text-center w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm">
                        @forelse($detailData as $index => $data)
                            <tr wire:key="detail-{{ $data->id_traffic_data }}" class="hover:bg-slate-50 transition">
                                <td class="p-3 text-center text-slate-400 font-mono">{{ $detailData->firstItem() + $index }}</td>
                                <td class="p-3 font-mono">{{ $data->tanggal->format('d/m/Y') }}</td>
                                <td class="p-3 font-mono font-bold text-slate-800">{{ $data->aircraft_id }}</td>
                                <td class="p-3">
                                    <span class="font-bold text-purple-600">{{ $data->airline3_code }}</span>
                                    @if($data->airline)
                                        <span class="text-xs block text-slate-400">{{ $data->airline->airline_name }}</span>
                                    @endif
                                </td>
                                <td class="p-3 font-mono">{{ $data->registrasi }}</td>
                                <td class="p-3 font-mono">{{ $data->type }}</td>
                                <td class="p-3 font-mono">
                                    <span>{{ $data->adep }}</span> 
                                    <span class="text-slate-300 mx-1">→</span> 
                                    <span>{{ $data->ades }}</span>
                                </td>
                                <td class="p-3 font-mono">{{ $data->atd ?? '-' }}</td>
                                <td class="p-3 font-mono">{{ $data->ata ?? '-' }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-1 {{ $data->status_flight == 'REGULER' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }} rounded-full text-[10px] font-bold">
                                        {{ $data->status_flight ?? '-' }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <button wire:click="deleteDetail({{ $data->id_traffic_data }})" wire:confirm="Hapus data ini?" class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="p-12 text-center text-slate-400 font-medium italic">
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
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-purple-500 transition-colors">
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
                            <button wire:click="import" wire:loading.attr="disabled" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold text-xs transition-all disabled:opacity-50">
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
                            <tr wire:key="upload-{{ $upload->id_traffic_upload }}" class="hover:bg-slate-50 transition cursor-pointer" wire:click="lihatDetail({{ $upload->id_traffic_upload }})">
                                <td class="p-4 text-center text-slate-400 font-mono">{{ $uploads->firstItem() + $index }}</td>
                                <td class="p-4 font-mono font-bold text-purple-600">{{ $upload->file_name }}</td>
                                <td class="p-4 text-slate-600">{{ $upload->tanggal_jam->format('d/m/Y H:i') }}</td>
                                <td class="p-4 font-mono text-slate-500">{{ $upload->range_tanggal }}</td>
                                <td class="p-4 text-center font-mono font-bold">{{ $upload->total_rows ?? $upload->trafficData->count() }}</td>
                                <td class="p-4 text-center">
                                    <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-bold">{{ $upload->status }}</span>
                                </td>
                                <td class="p-4 text-center" wire:click.stop>
                                    <div class="flex justify-center gap-1">
                                        <button wire:click="delete({{ $upload->id_traffic_upload }})" wire:confirm="Hapus data upload ini?" class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-12 text-center text-slate-400 font-medium italic">
                                    Belum ada data upload traffic.
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
