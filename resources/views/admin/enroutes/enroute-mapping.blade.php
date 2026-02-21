<div class="space-y-6">
    <header class="flex justify-between items-center">
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">
            Mapping Kolom Excel
        </h1>
    </header>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <div class="mb-6 p-4 bg-blue-50 rounded-xl">
            <p class="text-sm text-blue-700">
                <strong>Total baris:</strong> {{ $preview['total_rows'] }} data | 
                <strong>File:</strong> {{ $fileImport->getClientOriginalName() }}
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="p-3 text-xs font-black text-slate-500">Kolom Excel</th>
                        @foreach($preview['headers'] as $header)
                            <th class="p-3 text-xs font-mono border">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-indigo-50/50">
                        <td class="p-3 font-bold text-indigo-600">Mapping ke:</td>
                        @foreach($preview['headers'] as $index => $header)
                            <td class="p-3">
                                <select wire:model="columnMapping.{{ $index }}" class="text-xs border rounded-lg p-1">
                                    <option value="">- Abaikan -</option>
                                    <option value="aircraft_id">Aircraft ID</option>
                                    <option value="adep">ADEP</option>
                                    <option value="ades">ADES</option>
                                    <option value="dof">DOF</option>
                                    <option value="registrasi">Registrasi</option>
                                    <option value="type">Type</option>
                                    <option value="point_in">Point In</option>
                                    <option value="time_in">Time In</option>
                                    <option value="point_out">Point Out</option>
                                    <option value="time_out">Time Out</option>
                                    <option value="faktor_jarak">Faktor Jarak</option>
                                    <option value="faktor_berat">Faktor Berat</option>
                                    <option value="route_unit">Route Unit</option>
                                    <option value="route_charge">Route Charge</option>
                                    <option value="flight_type">Flight Type</option>
                                    <option value="currency">Currency</option>
                                </select>
                            </td>
                        @endforeach
                    </tr>
                    
                    @foreach($preview['rows'] as $rowIndex => $row)
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 font-mono text-slate-400">Baris {{ $rowIndex + 2 }}</td>
                            @foreach($row as $cell)
                                <td class="p-3 border font-mono text-xs">{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <button wire:click="$set('step', 'upload')" class="px-6 py-3 rounded-xl bg-slate-200 text-slate-700 font-bold hover:bg-slate-300 transition">
                ← Kembali
            </button>
            <button wire:click="processImport" wire:loading.attr="disabled" class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition shadow-lg">
                <span wire:loading.remove wire:target="processImport">Proses Import</span>
                <span wire:loading wire:target="processImport">Mengimport...</span>
            </button>
        </div>
    </div>
</div>