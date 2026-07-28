<?php

use App\Models\FinanceBranch;
use App\Models\FinanceItem;
use App\Services\FinanceImportService;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Layout('layouts.admin')]
class extends Component {
    use WithFileUploads, WithPagination;

    public $fileImport;
    public string $search = '';

    public function import(): void
    {
        $this->validate(['fileImport' => 'required|file|mimes:csv,xls,xlsx,txt|max:10240']);

        try {
            $service = app(FinanceImportService::class);
            $service->import($this->fileImport->getRealPath(), $this->fileImport->getClientOriginalName());

            $this->reset('fileImport');
            $count = FinanceItem::count();
            session()->flash('message', "$count baris item berhasil diimpor dan langsung tampil di dashboard.");
        } catch (\Throwable $exception) {
            report($exception);
            session()->flash('error', 'Import gagal: ' . $exception->getMessage());
        }
    }

    public function deleteAll(): void
    {
        FinanceItem::query()->delete();
        FinanceBranch::query()->delete();
        \App\Models\FinanceUpload::query()->delete();
        session()->flash('message', 'Semua data Finance berhasil dihapus.');
    }

    public function with(): array
    {
        $items = FinanceItem::with('branch')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('code', 'like', "%{$this->search}%")
                      ->orWhereHas('branch', function ($q) {
                          $q->where('name', 'like', "%{$this->search}%")
                            ->orWhere('code', 'like', "%{$this->search}%");
                      });
            })
            ->latest()
            ->paginate(15);
            
        $activeFinanceFiles = \App\Models\FinanceUpload::latest()->pluck('file_name')->toArray();
            
        return ['items' => $items, 'activeFinanceFiles' => $activeFinanceFiles];
    }
};
?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Master Data Finance</h1>
        <p class="mt-1 text-sm text-slate-500">Impor data realisasi SAP (CSV/Excel) untuk memperbarui visualisasi pada dashboard Finance.</p>
    </div>

    @if(!empty($activeFinanceFiles))
    <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm font-semibold text-slate-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span>File saat ini digunakan: <b>{{ implode(', ', $activeFinanceFiles) }}</b></span>
    </div>
    @endif

    @if(session('message')) <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-sm font-bold text-emerald-700">{{ session('message') }}</div> @endif
    @if(session('error')) <div class="rounded-xl border border-red-100 bg-red-50 p-4 text-sm font-bold text-red-700">{{ session('error') }}</div> @endif

    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center bg-white border border-slate-200 p-1.5 rounded-2xl shadow-sm">
                <input type="file" wire:model="fileImport" id="financeFileInput" class="hidden" accept=".csv,.xls,.xlsx,.txt">
                <label for="financeFileInput" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-slate-600 rounded-xl cursor-pointer text-xs font-bold transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>{{ $fileImport ? $fileImport->getClientOriginalName() : 'Pilih CSV/Excel SAP' }}</span>
                </label>
                <div class="w-px h-4 bg-slate-200 mx-1"></div>
                <button wire:click="import" wire:loading.attr="disabled" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold text-xs transition-all disabled:opacity-50">
                    <span wire:loading.remove wire:target="import">Import Data</span>
                    <span wire:loading wire:target="import">Upload...</span>
                </button>
            </div>
            
            <button wire:click="deleteAll" wire:confirm="Apakah Anda yakin ingin menghapus seluruh data Finance?" class="flex items-center gap-2 px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl font-bold text-xs transition-colors border border-red-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hapus Semua Data
            </button>
        </div>
        <p class="mt-3 text-xs text-slate-500">Gunakan file export asli dari SAP (mengandung label Cabang dan Item). Data yang diimpor akan menghapus dan <b>mengganti seluruh data sebelumnya</b> (Replace).</p>
    </div>

    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <input wire:model.live.debounce.300ms="search" placeholder="Cari kode/nama cabang atau funds center..." class="mb-5 w-full rounded-xl border-slate-200 text-sm">
        <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="text-left text-xs uppercase text-slate-400"><tr><th class="p-3">Cabang</th><th class="p-3">Funds Center</th><th class="p-3 text-right">RKAP</th><th class="p-3 text-right">Release</th><th class="p-3 text-right">Commitment</th><th class="p-3 text-right">Consume</th><th class="p-3 text-right">Available</th></tr></thead><tbody>
            @forelse($items as $item)<tr class="border-t border-slate-100"><td class="p-3"><div class="font-semibold">{{ $item->branch->name }}</div><div class="text-[10px] text-slate-400">{{ $item->branch->code }}</div></td><td class="p-3"><div class="font-semibold">{{ $item->name }}</div><div class="text-[10px] text-slate-400">{{ $item->code }}</div></td><td class="p-3 text-right">{{ number_format($item->rkap,0,',','.') }}</td><td class="p-3 text-right">{{ number_format($item->release_budget,0,',','.') }}</td><td class="p-3 text-right">{{ number_format($item->commitment,0,',','.') }}</td><td class="p-3 text-right">{{ number_format($item->total_consume,0,',','.') }}</td><td class="p-3 text-right">{{ number_format($item->available_budget,0,',','.') }}</td></tr>@empty <tr><td colspan="7" class="p-8 text-center text-slate-400">Belum ada data Finance. Impor file untuk memulai.</td></tr>@endforelse
        </tbody></table></div>
        <div class="mt-5">{{ $items->links() }}</div>
    </div>
</div>
