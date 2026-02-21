<?php
use App\Models\Airline;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout; // Tambahkan ini
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

new #[Layout('layouts.admin')] 
class extends Component {
    use WithPagination, WithFileUploads;

    public $search = '';
    public $filterCountry = '';
    public $fileImport;
    public $showForm = false;
    public $isEdit = false;
    public $selectedId;

    public $airline3_code, $airline_name, $airline_country = 'Indonesia';
    public $sortField = 'airline_name';
    public $sortDirection = 'asc';

    
    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->isEdit = false;
    }

    public function edit($id)
    {
        $airline = Airline::findOrFail($id);
        $this->selectedId      = $airline->id;
        $this->airline3_code   = $airline->airline3_code;
        $this->airline_name    = $airline->airline_name;
        $this->airline_country = $airline->airline_country;
        $this->showForm = true;
        $this->isEdit = true;
    }

    public function store()
    {
        $this->validate([
            'airline3_code' => 'required|string|max:3|unique:airlines,airline3_code,' . ($this->selectedId ?? 'NULL'),
            'airline_name' => 'required|string|max:255',
            'airline_country' => 'required|string|max:100',
        ]);

        Airline::updateOrCreate(
            ['id' => $this->selectedId],
            [
                'airline3_code' => strtoupper(trim($this->airline3_code)),
                'airline_name' => trim($this->airline_name),
                'airline_country' => trim($this->airline_country),
            ]
        );

        session()->flash('message', $this->isEdit ? 'Data berhasil diperbarui' : 'Data berhasil ditambahkan');
        $this->resetForm();
    }

    public function delete($id)
    {
        Airline::destroy($id);
        session()->flash('message', 'Data berhasil dihapus');
    }

    private function resetForm()
    {
        $this->reset(['showForm', 'isEdit', 'selectedId', 'airline3_code', 'airline_name']);
        $this->airline_country = 'Indonesia';
    }

    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Kode');
        $sheet->setCellValue('B1', 'Nama Maskapai');
        $sheet->setCellValue('C1', 'Negara');

        $airlines = Airline::all();
        $row = 2;
        foreach ($airlines as $airline) {
            $sheet->setCellValue('A' . $row, $airline->airline3_code);
            $sheet->setCellValue('B' . $row, $airline->airline_name);
            $sheet->setCellValue('C' . $row, $airline->airline_country);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'airlines_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function import()
    {
        $this->validate(['fileImport' => 'required|mimes:xlsx,xls,csv|max:2048']);
        try {
            $path = $this->fileImport->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $rows = $spreadsheet->getActiveSheet()->toArray();
            array_shift($rows);

            foreach ($rows as $row) {
                if (empty($row[0])) continue;
                Airline::updateOrCreate(
                    ['airline3_code' => strtoupper(substr(trim($row[0]), 0, 3))],
                    ['airline_name' => trim($row[1] ?? 'N/A'), 'airline_country' => trim($row[2] ?? 'Indonesia')]
                );
            }
            session()->flash('message', 'Import Berhasil!');
            $this->reset('fileImport');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
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
        return [
            'airlines' => Airline::query()
                ->when($this->search, fn($q) => $q->where('airline_name', 'like', "%{$this->search}%")->orWhere('airline3_code', 'like', "%{$this->search}%"))
                ->when($this->filterCountry, fn($q) => $q->where('airline_country', $this->filterCountry))
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(10),
            'countries' => Airline::whereNotNull('airline_country')->distinct()->pluck('airline_country')->sort()->values()
        ];
    }
}; ?>

<div class="space-y-6">
    <header class="flex justify-between items-center">
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Master Data Airline</h1>
    </header>

    @if($showForm)
    <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100 shadow-sm animate-in fade-in slide-in-from-top-4 duration-300">
        <h3 class="text-lg font-black text-indigo-900 mb-4">{{ $isEdit ? 'Edit Airline' : 'Tambah Airline Baru' }}</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Kode Airline</label>
                <input type="text" wire:model="airline3_code" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 transition">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Nama Airline</label>
                <input type="text" wire:model="airline_name" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 transition">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 uppercase">Negara</label>
                <input type="text" wire:model="airline_country" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 transition">
            </div>
        </div>
        <div class="flex justify-end gap-2 mt-6">
            <button wire:click="$set('showForm', false)" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-700 font-bold text-sm hover:bg-slate-300 transition">Batal</button>
            <button wire:click="store" class="px-6 py-2 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 shadow-md transition">Simpan</button>
        </div>
    </div>
    @endif

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        @if (session()->has('message'))
            <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-100 text-sm font-bold animate-pulse">
                {{ session('message') }}
            </div>
        @endif

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
                           placeholder="Cari maskapai berdasarkan nama atau kode..." 
                           class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm transition-all focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500">
                </div>

                <button wire:click="create" 
                        class="w-full md:w-auto flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-2xl font-bold text-sm transition-all shadow-lg shadow-indigo-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Airline</span>
                </button>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4 pt-2 border-t border-slate-50">
                <!-- <div class="text-xs font-medium text-slate-400">Menampilkan data maskapai real-time</div> -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center bg-white border border-slate-200 p-1.5 rounded-2xl shadow-sm">
                        <input type="file" wire:model="fileImport" id="fileInput" class="hidden">
                        <label for="fileInput" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-slate-600 rounded-xl cursor-pointer text-xs font-bold transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1.01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>{{ $fileImport ? 'File Terpilih' : 'Pilih Excel' }}</span>
                        </label>
                        <div class="w-px h-4 bg-slate-200 mx-1"></div>
                        <button wire:click="import" wire:loading.attr="disabled" class="px-4 py-2 bg-slate-900 hover:bg-black text-white rounded-xl font-bold text-xs transition-all disabled:opacity-50">
                            <span wire:loading.remove wire:target="import">Import</span>
                            <span wire:loading wire:target="import">Proses...</span>
                        </button>
                    </div>

                    <button wire:click="export" class="flex items-center gap-2 px-5 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-2xl font-bold text-xs transition-all active:scale-95">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export Data
                    </button>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto border border-slate-50 rounded-2xl">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="p-4 text-xs font-black text-slate-500 uppercase text-center w-16">No</th>
                        @foreach(['airline3_code' => 'Kode', 'airline_name' => 'Nama Maskapai', 'airline_country' => 'Negara'] as $field => $label)
                            <th wire:click="sortBy('{{ $field }}')" 
                                class="p-4 text-xs uppercase cursor-pointer transition-all duration-200 group {{ $sortField === $field ? 'font-black text-black' : 'font-medium text-slate-500 hover:text-black hover:font-black' }}">
                                <div class="flex items-center justify-start gap-1">
                                    <span>{{ $label }}</span>
                                    <div class="w-4 h-4">
                                        @if($sortField === $field)
                                            <svg class="w-4 h-4 stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path></svg>
                                        @else
                                            <svg class="w-4 h-4 stroke-2 text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 10l5-5 5 5M7 14l5 5 5-5"></path></svg>
                                        @endif
                                    </div>
                                </div>
                            </th>
                        @endforeach
                        <th class="p-4 text-xs font-black text-slate-500 uppercase text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @forelse($airlines as $index => $airline)
                        <tr wire:key="airline-{{ $airline->id }}" class="hover:bg-slate-50 transition">
                            <td class="p-4 text-center text-slate-400 font-mono">{{ $airlines->firstItem() + $index }}</td>
                            <td class="p-4 font-black text-slate-700 tracking-wider">{{ $airline->airline3_code }}</td>
                            <td class="p-4 font-bold text-slate-600">{{ $airline->airline_name }}</td>
                            <td class="p-4 text-slate-500 italic">{{ $airline->airline_country }}</td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-1">
                                    <button wire:click="edit({{ $airline->id }})" class="p-2 text-amber-600 hover:bg-amber-50 rounded-xl transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button wire:click="delete({{ $airline->id }})" wire:confirm="Hapus data?" class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-slate-400 font-medium italic">
                                Data tidak ditemukan untuk pencarian ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-6 border-t border-slate-50 pt-4">
            {{ $airlines->links() }}
        </div>
    </div>
</div>