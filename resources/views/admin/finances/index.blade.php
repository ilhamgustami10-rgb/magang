<?php

use App\Models\FinanceData;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\IOFactory;

new #[Layout('layouts.admin')]
class extends Component {
    use WithFileUploads, WithPagination;

    public $fileImport;
    public string $search = '';

    public function import(): void
    {
        $this->validate(['fileImport' => 'required|file|mimes:csv,xls,xlsx|max:10240']);

        try {
            $rows = IOFactory::load($this->fileImport->getRealPath())->getActiveSheet()->toArray(null, true, true, false);
            $headerIndex = collect($rows)->search(function ($row) {
                $header = array_map(fn ($value) => Str::lower(trim((string) $value)), $row);
                return count(array_intersect($header, ['funds center', 'funds_center', 'item'])) > 0;
            });

            if ($headerIndex === false) {
                session()->flash('error', 'Header CSV tidak ditemukan. Gunakan kolom: Branch, Funds Center, RKAP, Release Budget, Commitment, Total Consume, Available Budget.');
                return;
            }

            $headers = array_map(fn ($value) => Str::lower(trim((string) $value)), $rows[$headerIndex]);
            $column = function (array $names) use ($headers) {
                foreach ($names as $name) {
                    $index = array_search($name, $headers, true);
                    if ($index !== false) return $index;
                }
                return null;
            };
            $funds = $column(['funds center', 'funds_center', 'item']);
            if ($funds === null) throw new \RuntimeException('Kolom Funds Center wajib diisi.');

            $branch = $column(['branch', 'cabang']);
            $fields = [
                'rkap' => $column(['rkap']),
                'release_budget' => $column(['release budget', 'release_budget', 'release']),
                'commitment' => $column(['commitment', 'commit']),
                'total_consume' => $column(['total consume', 'total_consume', 'consume']),
                'available_budget' => $column(['available budget', 'available_budget', 'available']),
            ];
            $number = function ($value): float {
                $value = preg_replace('/[^0-9,.-]/', '', (string) $value);
                if (str_contains($value, ',') && str_contains($value, '.')) {
                    $value = strrpos($value, ',') > strrpos($value, '.') ? str_replace(['.', ','], ['', '.'], $value) : str_replace(',', '', $value);
                } elseif (str_contains($value, ',')) {
                    $value = str_replace(',', '.', $value);
                }
                return (float) $value;
            };

            $imported = 0;
            foreach (array_slice($rows, $headerIndex + 1) as $row) {
                $name = trim((string) ($row[$funds] ?? ''));
                if ($name === '') continue;
                $attributes = ['branch' => trim((string) ($branch !== null ? ($row[$branch] ?? '') : '')) ?: 'AirNav Juanda (Utama)', 'funds_center' => $name];
                $values = [];
                foreach ($fields as $field => $index) $values[$field] = $number($index !== null ? ($row[$index] ?? 0) : 0);
                FinanceData::updateOrCreate($attributes, $values);
                $imported++;
            }

            $this->reset('fileImport');
            session()->flash('message', "$imported baris Finance berhasil diimpor dan langsung tampil di tab Finance.");
        } catch (\Throwable $exception) {
            report($exception);
            session()->flash('error', 'Import gagal: ' . $exception->getMessage());
        }
    }

    public function delete(int $id): void
    {
        FinanceData::destroy($id);
        session()->flash('message', 'Baris Finance berhasil dihapus.');
    }

    public function with(): array
    {
        return ['items' => FinanceData::query()->when($this->search, fn ($query) => $query->where('branch', 'like', "%{$this->search}%")->orWhere('funds_center', 'like', "%{$this->search}%"))->latest()->paginate(15)];
    }
};
?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Master Data Finance</h1>
        <p class="mt-1 text-sm text-slate-500">Impor CSV/Excel untuk memperbarui visualisasi pada tab Finance.</p>
    </div>

    @if(session('message')) <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-sm font-bold text-emerald-700">{{ session('message') }}</div> @endif
    @if(session('error')) <div class="rounded-xl border border-red-100 bg-red-50 p-4 text-sm font-bold text-red-700">{{ session('error') }}</div> @endif

    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center bg-white border border-slate-200 p-1.5 rounded-2xl shadow-sm">
                <input type="file" wire:model="fileImport" id="financeFileInput" class="hidden" accept=".csv,.xls,.xlsx">
                <label for="financeFileInput" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 text-slate-600 rounded-xl cursor-pointer text-xs font-bold transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
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
        <p class="mt-3 text-xs text-slate-500">Format header: <b>Branch, Funds Center, RKAP, Release Budget, Commitment, Total Consume, Available Budget</b>. Kolom Branch opsional.</p>
    </div>

    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <input wire:model.live.debounce.300ms="search" placeholder="Cari cabang atau funds center..." class="mb-5 w-full rounded-xl border-slate-200 text-sm">
        <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="text-left text-xs uppercase text-slate-400"><tr><th class="p-3">Cabang</th><th class="p-3">Funds Center</th><th class="p-3 text-right">RKAP</th><th class="p-3 text-right">Release</th><th class="p-3 text-right">Commitment</th><th class="p-3 text-right">Consume</th><th class="p-3 text-right">Available</th><th></th></tr></thead><tbody>
            @forelse($items as $item)<tr class="border-t border-slate-100"><td class="p-3">{{ $item->branch }}</td><td class="p-3 font-semibold">{{ $item->funds_center }}</td><td class="p-3 text-right">{{ number_format($item->rkap,0,',','.') }}</td><td class="p-3 text-right">{{ number_format($item->release_budget,0,',','.') }}</td><td class="p-3 text-right">{{ number_format($item->commitment,0,',','.') }}</td><td class="p-3 text-right">{{ number_format($item->total_consume,0,',','.') }}</td><td class="p-3 text-right">{{ number_format($item->available_budget,0,',','.') }}</td><td class="p-3"><button wire:click="delete({{ $item->id }})" wire:confirm="Hapus baris ini?" class="text-red-500">Hapus</button></td></tr>@empty <tr><td colspan="8" class="p-8 text-center text-slate-400">Belum ada data Finance. Impor file CSV untuk memulai.</td></tr>@endforelse
        </tbody></table></div>
        <div class="mt-5">{{ $items->links() }}</div>
    </div>
</div>
