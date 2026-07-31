<?php

namespace App\Http\Controllers;

use App\Models\BudgetRealisasi;
use App\Services\SapImportService;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class FinanceController extends Controller
{
    public function index()
    {
        // Get the latest date
        $latestDate = BudgetRealisasi::max('report_date');

        // Collect branches (level = cabang) for the latest date
        $branches = BudgetRealisasi::where('level', 'cabang')
            ->when($latestDate, function($q) use ($latestDate) {
                return $q->where('report_date', $latestDate);
            })
            ->orderBy('id')
            ->get();

        // Collect items
        $items = BudgetRealisasi::where('level', 'item')
            ->when($latestDate, function($q) use ($latestDate) {
                return $q->where('report_date', $latestDate);
            })
            ->get()
            ->groupBy('branch_code');

        $financeData = [];
        foreach ($branches as $branch) {
            $branchName = $branch->branch_name ?: $branch->branch_code;
            
            $financeData[$branchName] = [
                'rkap'       => (float) $branch->rkap,
                'release'    => (float) $branch->release_budget,
                'commitment' => (float) $branch->commitment,
                'consume'    => (float) $branch->total_consume,
                'available'  => (float) $branch->available_budget,
                'items'      => [],
            ];
            
            if (isset($items[$branch->branch_code])) {
                foreach ($items[$branch->branch_code] as $item) {
                    $financeData[$branchName]['items'][] = [
                        'code'       => $item->item_code,
                        'name'       => $item->item_name,
                        'rkap'       => (float) $item->rkap,
                        'release'    => (float) $item->release_budget,
                        'commitment' => (float) $item->commitment,
                        'consume'    => (float) $item->total_consume,
                        'available'  => (float) $item->available_budget,
                    ];
                }
            }
        }

        // Summary Data (use Total if exists, else sum branches)
        $grandTotal = BudgetRealisasi::where('level', 'total')
            ->when($latestDate, function($q) use ($latestDate) {
                return $q->where('report_date', $latestDate);
            })->first();

        if ($grandTotal) {
            $sumRkap = $grandTotal->rkap;
            $sumRelease = $grandTotal->release_budget;
            $sumCommit = $grandTotal->commitment;
            $sumConsume = $grandTotal->total_consume;
            $sumAvail = $grandTotal->available_budget;
        } else {
            $sumRkap = $branches->sum('rkap');
            $sumRelease = $branches->sum('release_budget');
            $sumCommit = $branches->sum('commitment');
            $sumConsume = $branches->sum('total_consume');
            $sumAvail = $branches->sum('available_budget');
        }
        
        $totalCabang = $branches->count();

        // Pass standard variables
        $activeFinanceFiles = \App\Models\FinanceUpload::latest()->pluck('file_name')->toArray();
        $financeUpdatedAt = $latestDate ? \Carbon\Carbon::parse($latestDate)->format('d M Y') : null;

        return view('finance', compact(
            'financeData', 
            'activeFinanceFiles', 
            'financeUpdatedAt',
            'sumRkap', 'sumRelease', 'sumCommit', 'sumConsume', 'sumAvail',
            'totalCabang'
        ));
    }

    public function import(Request $request, SapImportService $importService)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        try {
            $stats = $importService->import(
                $request->file('file')->getRealPath(), 
                $request->file('file')->getClientOriginalName()
            );
            $count = $stats['rows_imported'];
            
            return redirect()->back()->with('success', "{$count} baris Realisasi Anggaran berhasil diimpor dan langsung tampil di tab Finance.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Gagal mengimpor file: " . $e->getMessage());
        }
    }

    /**
     * REFRESH DATA SAP — Alur lengkap:
     * 1. Kunci anti-eksekusi ganda (Cache lock 180 detik)
     * 2. Jalankan bot export SAP (.vbs) secara sinkron
     * 3. Petakan exit code → pesan error
     * 4. Verifikasi file baru di folder export
     * 5. Impor file → upsert budget_realisasi → archive
     * 6. Tampilkan hasil
     *
     * Proses HARUS sinkron (di web request) agar bot bisa mengendalikan
     * SAP GUI di sesi desktop yang sama.
     */
    public function refresh(SapImportService $importService)
    {
        // Naikkan batas waktu PHP untuk aksi ini saja
        set_time_limit(180);

        $lock = Cache::lock('sap_refresh', 180);

        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Refresh sedang berjalan, tunggu sampai selesai.');
        }

        try {
            // ── 1. Pastikan folder export ada ─────────────────────────
            $exportDir  = config('sap.export_dir', 'D:/Sap_export');
            $archivePath = config('sap.archive_path', 'D:/Sap_export/archive');
            $failedPath  = config('sap.failed_path', 'D:/Sap_export/failed');

            File::ensureDirectoryExists($exportDir);
            File::ensureDirectoryExists($archivePath);
            File::ensureDirectoryExists($failedPath);

            // ── 2. Catat waktu mulai (untuk filter file baru) ────────
            $startTime = time();

            // ── 3. Jalankan bot export SAP ───────────────────────────
            $botCommand = config('sap.bot_command');
            $botTimeout = config('sap.bot_timeout', 150);

            if (empty($botCommand)) {
                Log::error('SAP Refresh: SAP_BOT_COMMAND tidak dikonfigurasi di .env');
                return redirect()->back()->with('error', 'Konfigurasi bot SAP belum diatur. Hubungi administrator.');
            }

            Log::info("SAP Refresh: Menjalankan bot — {$botCommand}");

            // Gunakan fromShellCommandline karena perintah mengandung //nologo dan path berspasi
            $process = Process::fromShellCommandline($botCommand);
            $process->setWorkingDirectory(base_path('bot'));
            $process->setTimeout($botTimeout);

            try {
                $process->run();
            } catch (ProcessTimedOutException $e) {
                Log::error('SAP Refresh: Bot timeout setelah ' . $botTimeout . ' detik', [
                    'stdout' => $process->getOutput(),
                    'stderr' => $process->getErrorOutput(),
                ]);
                return redirect()->back()->with('error',
                    'Bot tidak merespons dalam waktu yang ditentukan. Pastikan SAP terbuka, login, dan layar tidak terkunci.'
                );
            }

            $exitCode    = $process->getExitCode();
            $stdout      = $process->getOutput();
            $stderr      = $process->getErrorOutput();

            // Log output bot
            if ($exitCode === 0) {
                Log::info("SAP Refresh: Bot selesai sukses (exit=0)", [
                    'command' => $botCommand,
                    'stdout'  => $stdout,
                    'stderr'  => $stderr,
                ]);
            } else {
                Log::error("SAP Refresh: Bot gagal (exit={$exitCode})", [
                    'command' => $botCommand,
                    'stdout'  => $stdout,
                    'stderr'  => $stderr,
                ]);
            }

            // ── 4. Petakan EXIT CODE → aksi & pesan ─────────────────
            if ($exitCode === 1) {
                return redirect()->back()->with('error',
                    'Gagal: SAP belum login / tidak ada sesi aktif. Pastikan SAP GUI terbuka & login.'
                );
            }
            if ($exitCode === 2) {
                return redirect()->back()->with('error',
                    'Gagal membuka laporan di SAP (kode transaksi/menu). Cek layar SAP.'
                );
            }
            if ($exitCode !== 0) {
                $snippetStderr = mb_substr(trim($stderr ?: $stdout), 0, 300);
                return redirect()->back()->with('error',
                    "Bot selesai dengan error ({$exitCode}). Rincian: {$snippetStderr}"
                );
            }

            // ── 5. Verifikasi HASIL FILE sebelum impor ──────────────
            // Cari file .csv terbaru di folder export yang dibuat SETELAH proses bot dimulai
            $newFile = $this->findNewestExportFile($exportDir, $startTime);

            if (!$newFile) {
                Log::warning('SAP Refresh: Bot selesai sukses tapi tidak ada file baru ditemukan', [
                    'export_dir' => $exportDir,
                    'start_time' => date('Y-m-d H:i:s', $startTime),
                ]);
                return redirect()->back()->with('error',
                    'Bot selesai tapi tidak ada file baru ditemukan di folder export. Cek apakah SAP benar-benar mengekspor file.'
                );
            }

            // Pastikan file sudah selesai ditulis (cek ukuran stabil)
            $this->waitForFileStable($newFile->getRealPath());

            $filePath = $newFile->getRealPath();
            $fileName = $newFile->getFilename();

            Log::info("SAP Refresh: File ditemukan — {$fileName} ({$newFile->getSize()} bytes)");

            // ── 6. Jalankan proses IMPORT ────────────────────────────
            // report_date = tanggal ekspor (hari ini).
            // Upsert memakai kunci unik (report_date, branch_code, item_code, level)
            // sehingga refresh berkali-kali pada HARI yang sama akan MENIMPA baris hari itu
            // (tidak menggandakan), dan hari berbeda menyimpan snapshot baru.
            // Jika ingin perilaku "selalu ganti total" (tanpa snapshot harian),
            // ubah $reportDate menjadi tanggal tetap atau hapus report_date dari kunci unik.
            try {
                $stats = $importService->import($filePath, $fileName, 'sap_bot');

                $reportDate = now()->format('Y-m-d');
                $totalCabang = BudgetRealisasi::where('level', 'cabang')
                    ->where('report_date', $reportDate)
                    ->count();

                // Pindahkan file ke archive
                File::move($filePath, $archivePath . '/' . $fileName);

                $count = $stats['rows_imported'];

                Log::info("SAP Refresh: Berhasil — {$count} baris, {$totalCabang} cabang", $stats);

                return redirect()->back()->with('success',
                    "Berhasil tarik data dari SAP: {$count} baris, {$totalCabang} cabang diimpor (report_date: {$reportDate})."
                );
            } catch (\Exception $e) {
                Log::error("SAP Refresh: Parse/import gagal untuk {$fileName}: " . $e->getMessage());
                // Pindahkan ke failed
                File::move($filePath, $failedPath . '/' . $fileName);
                return redirect()->back()->with('error',
                    "File diterima dari SAP tapi gagal diproses: " . $e->getMessage()
                );
            }

        } catch (\Exception $e) {
            Log::error('SAP Refresh: Error tidak terduga — ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error',
                'Terjadi kesalahan saat refresh data SAP. Periksa log untuk detail.'
            );
        } finally {
            // SELALU lepaskan kunci, apa pun hasilnya
            $lock->forceRelease();
        }
    }

    /**
     * TOMBOL CADANGAN: Impor dari folder saja (tanpa memanggil bot).
     * Untuk keadaan darurat ketika file sudah ada di D:/Sap_export
     * tapi bot tidak bisa dijalankan.
     */
    public function refreshFromFolder(SapImportService $importService)
    {
        $importPath  = config('sap.import_path');
        $archivePath = config('sap.archive_path');
        $failedPath  = config('sap.failed_path');

        File::ensureDirectoryExists($importPath);
        File::ensureDirectoryExists($archivePath);
        File::ensureDirectoryExists($failedPath);

        $files = collect(File::files($importPath))
            ->filter(function ($file) {
                return in_array(strtolower($file->getExtension()), ['csv', 'txt', 'xlsx', 'xls']);
            })
            ->sortByDesc(function ($file) {
                return $file->getMTime();
            });

        $latestFile = $files->first();

        if (!$latestFile) {
            return redirect()->back()->with('error', "Tidak ada file di folder {$importPath}.");
        }

        $filePath = $latestFile->getRealPath();
        $fileName = $latestFile->getFilename();

        try {
            $stats = $importService->import($filePath, $fileName, 'folder');
            $count = $stats['rows_imported'];
            $totalCabang = BudgetRealisasi::where('level', 'cabang')
                ->where('report_date', BudgetRealisasi::max('report_date'))
                ->count();
                
            File::move($filePath, $archivePath . '/' . $fileName);
            
            return redirect()->back()->with('success', "File terbaru ({$fileName}) berhasil diproses. {$count} baris masuk dengan {$totalCabang} cabang terbaru.");
        } catch (\Exception $e) {
            Log::error("Manual Refresh Failed for file {$fileName}: " . $e->getMessage());
            File::move($filePath, $failedPath . '/' . $fileName);
            return redirect()->back()->with('error', "Gagal memproses file {$fileName}: " . $e->getMessage());
        }
    }

    /**
     * Cari file .csv/.txt/.xlsx/.xls TERBARU di folder yang dibuat SETELAH $afterTimestamp.
     */
    private function findNewestExportFile(string $dir, int $afterTimestamp): ?\SplFileInfo
    {
        if (!File::isDirectory($dir)) {
            return null;
        }

        $candidates = collect(File::files($dir))
            ->filter(function ($file) use ($afterTimestamp) {
                $ext = strtolower($file->getExtension());
                return in_array($ext, ['csv', 'txt', 'xlsx', 'xls'])
                    && $file->getMTime() >= $afterTimestamp;
            })
            ->sortByDesc(function ($file) {
                return $file->getMTime();
            });

        return $candidates->first();
    }

    /**
     * Tunggu sampai ukuran file stabil (tidak berubah dalam 500ms).
     * Maksimal 3 kali percobaan. Tujuannya memastikan SAP sudah
     * selesai menulis file sebelum kita baca.
     */
    private function waitForFileStable(string $filePath, int $maxRetries = 3): void
    {
        for ($i = 0; $i < $maxRetries; $i++) {
            $size1 = filesize($filePath);
            usleep(500_000); // 500ms
            clearstatcache(true, $filePath);
            $size2 = filesize($filePath);

            if ($size1 === $size2) {
                return; // stabil
            }

            Log::info("SAP Refresh: File masih ditulis (size {$size1} -> {$size2}), menunggu...");
        }
    }

    /**
     * TOMBOL DIAGNOSA: Test Koneksi SAP
     * Hanya mengetes apakah bot (VBS) bisa mendeteksi GUI SAP.
     */
    public function testKoneksi()
    {
        $cscript = config('sap.cscript_path', 'C:\Windows\System32\cscript.exe');
        $command = $cscript . ' //nologo "' . base_path('bot/test_koneksi_sap.vbs') . '"';
        Log::info("SAP Test Koneksi: Menjalankan {$command}");

        $process = Process::fromShellCommandline($command);
        $process->setWorkingDirectory(base_path('bot'));
        $process->setTimeout(30);

        try {
            $process->run();
        } catch (ProcessTimedOutException $e) {
            Log::error('SAP Test Koneksi: Timeout', [
                'stdout' => $process->getOutput(),
                'stderr' => $process->getErrorOutput(),
            ]);
            return redirect()->back()->with('error', 'Gagal: Bot timeout. Pastikan tidak ada popup di SAP.');
        }

        $exitCode = $process->getExitCode();
        $output = trim($process->getOutput());
        $error = trim($process->getErrorOutput());
        
        $finalOutput = $output;
        if ($error) {
            $finalOutput .= " | ERROR: " . $error;
        }

        if ($exitCode === 0) {
            return redirect()->back()->with('success', "SUKSES: " . $finalOutput);
        } else {
            return redirect()->back()->with('error', "GAGAL (Exit {$exitCode}): " . $finalOutput);
        }
    }
}
