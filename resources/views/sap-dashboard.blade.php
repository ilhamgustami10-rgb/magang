<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAP Import Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">SAP Realisasi Dashboard</h1>
            
            <form action="{{ route('sap.import') }}" method="POST">
                @csrf
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                    Import Sekarang
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if($latestDate)
            <p class="mb-4 text-gray-600">Menampilkan data laporan tanggal: <strong>{{ \Carbon\Carbon::parse($latestDate)->format('d F Y') }}</strong></p>
            
            @if($grandTotal)
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-gray-700">Grand Total</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 p-4 rounded border">
                            <p class="text-sm text-gray-500">RKAP</p>
                            <p class="text-2xl font-bold">Rp {{ number_format($grandTotal->rkap, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded border">
                            <p class="text-sm text-gray-500">Release Budget</p>
                            <p class="text-2xl font-bold">Rp {{ number_format($grandTotal->release_budget, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded border">
                            <p class="text-sm text-gray-500">Total Consume</p>
                            <p class="text-2xl font-bold text-orange-600">Rp {{ number_format($grandTotal->total_consume, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded border">
                            <p class="text-sm text-gray-500">Available Budget</p>
                            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($grandTotal->available_budget, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Grafik RKAP vs Total Consume per Cabang</h2>
                <div class="h-96">
                    <canvas id="branchChart"></canvas>
                </div>
            </div>

            <h2 class="text-2xl font-bold mb-4 text-gray-800">Detail Cabang</h2>
            
            @foreach($branches as $branch)
                <div class="bg-white rounded-lg shadow mb-8 overflow-hidden">
                    <div class="bg-blue-50 p-4 border-b">
                        <h3 class="text-lg font-bold text-blue-800">{{ $branch->branch_name }} ({{ $branch->branch_code }})</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            RKAP: Rp {{ number_format($branch->rkap, 0, ',', '.') }} | 
                            Consume: Rp {{ number_format($branch->total_consume, 0, ',', '.') }} | 
                            Available: Rp {{ number_format($branch->available_budget, 0, ',', '.') }}
                        </p>
                    </div>
                    
                    @if(isset($items[$branch->branch_code]))
                    <div class="overflow-x-auto p-4">
                        <table class="min-w-full text-left text-sm whitespace-nowrap">
                            <thead class="uppercase tracking-wider border-b-2 text-gray-600">
                                <tr>
                                    <th class="px-4 py-2">Item Code</th>
                                    <th class="px-4 py-2">Item Name</th>
                                    <th class="px-4 py-2 text-right">RKAP</th>
                                    <th class="px-4 py-2 text-right">Total Consume</th>
                                    <th class="px-4 py-2 text-right">Available</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items[$branch->branch_code] as $item)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2">{{ $item->item_code }}</td>
                                    <td class="px-4 py-2 font-medium">{{ $item->item_name }}</td>
                                    <td class="px-4 py-2 text-right">Rp {{ number_format($item->rkap, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right text-orange-600">Rp {{ number_format($item->total_consume, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right text-green-600">Rp {{ number_format($item->available_budget, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <p class="p-4 text-gray-500">Tidak ada detail item.</p>
                    @endif
                </div>
            @endforeach

        @else
            <div class="bg-white p-8 text-center rounded shadow">
                <p class="text-gray-500">Belum ada data. Silakan salin file CSV ke folder <code>D:/Sap_export</code> lalu klik "Import Sekarang".</p>
            </div>
        @endif
    </div>

    @if($latestDate)
    <script>
        fetch("{{ route('sap.api') }}")
            .then(response => response.json())
            .then(data => {
                const labels = data.map(d => d.branch_name || d.branch_code);
                const rkap = data.map(d => d.rkap);
                const consume = data.map(d => d.total_consume);

                const ctx = document.getElementById('branchChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'RKAP',
                                data: rkap,
                                backgroundColor: '#3b82f6', // blue-500
                            },
                            {
                                label: 'Total Consume',
                                data: consume,
                                backgroundColor: '#f97316', // orange-500
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
    </script>
    @endif
</body>
</html>
