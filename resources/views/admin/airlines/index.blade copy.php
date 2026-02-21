<?php

use App\Models\Airline;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

new class extends Component {
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filterCountry = '';
    public $fileImport;

    public $showForm = false;
    public $isEdit = false;
    public $selectedId;

    public $airline3_code;
    public $airline_name;
    public $airline_country = 'Indonesia';
    public $sortField = 'airline_name';
    public $sortDirection = 'asc';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterCountry() { $this->resetPage(); }

    public function mount()
    {
        $this->layout('components.admin-layout');
    }
    
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

    public function with()
    {
        return [
            'airlines' => Airline::query()
                ->when($this->search, function ($q) {
                    $q->where(function($query) {
                        $query->where('airline_name', 'like', "%{$this->search}%")
                              ->orWhere('airline3_code', 'like', "%{$this->search}%");
                    });
                })
                ->when($this->filterCountry, fn($q) => $q->where('airline_country', $this->filterCountry))
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(10),

            'countries' => Airline::whereNotNull('airline_country')->where('airline_country', '!=', '')
                ->distinct()->pluck('airline_country')->sort()->values()
        ];
    }
}; ?>


    <!-- @include('layouts.sidebar') -->
    
    <div class="flex-1 space-y-8">
        <h1 class="text-3xl font-black text-slate-800">Master Data Airline</h1>
        
        @if($showForm)
        <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100 shadow-sm animate-in fade-in slide-in-from-top-4 duration-300">
            <h3 class="text-lg font-black text-indigo-900 mb-4">{{ $isEdit ? 'Edit Airline' : 'Tambah Airline Baru' }}</h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Kode Airline</label>
                    <input type="text" wire:model="airline3_code" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Nama Airline</label>
                    <input type="text" wire:model="airline_name" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase">Negara</label>
                    <input type="text" wire:model="airline_country" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button wire:click="$set('showForm', false)" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-700 font-bold text-sm">Batal</button>
                <button wire:click="store" class="px-6 py-2 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700">Simpan</button>
            </div>
        </div>
        @endif

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            @if (session()->has('message'))
                <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-100 text-sm font-bold">{{ session('message') }}</div>
            @endif

            <div class="flex flex-col xl:flex-row gap-4 mb-8 items-end justify-between">
                <div class="flex flex-1 gap-4 w-full">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1 ml-1">Cari</label>
                        <input wire:model.live.debounce.500ms="search" type="text" placeholder="Nama atau Kode..." class="w-full border-slate-200 rounded-xl text-sm focus:ring-blue-500">
                    </div>
                    <div class="w-48">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1 ml-1">Negara</label>
                        <select wire:model.live="filterCountry" class="w-full border-slate-200 rounded-xl text-sm focus:ring-blue-500">
                            <option value="">Semua</option>
                            @foreach($countries as $country) <option value="{{ $country }}">{{ $country }}</option> @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 w-full xl:w-auto justify-end">
                    <button wire:click="create" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 font-bold text-sm shadow-sm transition">+ Airline</button>
                    <div class="flex gap-1 bg-slate-50 p-1 rounded-xl border border-slate-200">
                        <input type="file" wire:model="fileImport" id="fileInput" class="hidden">
                        <label for="fileInput" class="px-4 py-1.5 bg-white text-slate-600 rounded-lg cursor-pointer text-xs font-bold border border-slate-200">Excel</label>
                        <button wire:click="import" class="px-4 py-1.5 bg-blue-600 text-white rounded-lg font-bold text-xs">Import</button>
                    </div>
                    <button wire:click="export" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 font-bold text-sm">Export</button>
                </div>
            </div>

            <div class="overflow-hidden border border-slate-100 rounded-2xl">
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
                                                <svg class="w-4 h-4 stroke-2 text-slate-300 opacity-0 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 10l5-5 5 5M7 14l5 5 5-5"></path></svg>
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
                                <td class="p-4 text-center text-slate-400">{{ $airlines->firstItem() + $index }}</td>
                                <td class="p-4 font-black text-slate-700">{{ $airline->airline3_code }}</td>
                                <td class="p-4 font-black text-slate-700">{{ $airline->airline_name }}</td>
                                <td class="p-4 text-slate-500 italic">{{ $airline->airline_country }}</td>
                                <td class="p-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button wire:click="edit({{ $airline->id }})" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                        <button wire:click="delete({{ $airline->id }})" wire:confirm="Hapus data?" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-12 text-center text-slate-400 font-medium">Data tidak ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-8">{{ $airlines->links() }}</div>
        </div>
    </div>
