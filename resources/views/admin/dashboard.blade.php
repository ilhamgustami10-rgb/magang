<x-app-layout>
    <div class="flex flex-col gap-8 lg:flex-row">
        <aside class="w-64 shrink-0">@include('layouts.sidebar')</aside>
        <div class="flex-1 space-y-8">
        <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
            <div><h1 class="text-3xl font-black text-slate-800">Overview Admin</h1><p class="mt-1 text-sm text-slate-500">Status data yang masuk ke dashboard dan visualisasi.</p></div>
            <p class="text-xs font-semibold text-slate-400">Input terakhir: {{ $lastInputAt ? \Carbon\Carbon::parse($lastInputAt)->format('d M Y H:i') : 'Belum ada data' }}</p>
        </div>

        <div class="rounded-3xl bg-slate-900 p-6 text-white shadow-lg"><p class="text-sm font-semibold text-slate-300">Total record siap divisualisasikan</p><p class="mt-1 text-4xl font-black">{{ number_format($totalRecords, 0, ',', '.') }}</p><p class="mt-2 text-xs text-slate-400">Angka ini berubah setelah impor, hapus, atau pembaruan master data.</p></div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach($sources as $source)
                <a href="{{ route($source['route']) }}" class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-sm font-bold text-slate-500">{{ $source['label'] }}</p>
                    <p class="mt-3 text-3xl font-black text-slate-800">{{ number_format($source['records'], 0, ',', '.') }}</p>
                    <span class="mt-5 inline-block text-xs font-bold text-blue-600">Buka data →</span>
                </a>
            @endforeach
        </div>

        <div class="rounded-3xl border border-blue-100 bg-blue-50 p-5 text-sm text-blue-800"><b>Validasi proses:</b> kartu di atas menghitung data langsung dari tabel yang dipakai dashboard. Jika jumlah record bertambah setelah impor, data telah masuk dan akan dibaca oleh visualisasi terkait.</div>
        </div>
    </div>
</x-app-layout>
