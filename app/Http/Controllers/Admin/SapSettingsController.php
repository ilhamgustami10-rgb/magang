<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SapConfigService;
use Illuminate\Support\Facades\Process;

class SapSettingsController extends Controller
{
    public function index(SapConfigService $sapConfigService)
    {
        $settings = [
            'sapUser' => '',
            'exportFolder' => '',
        ];

        $sapConfigService->ensureExists();
        $parsedIni = $sapConfigService->read();

        $settings['sapUser'] = $parsedIni['SAP']['sapUser'] ?? '';
        $settings['exportFolder'] = $parsedIni['Export']['exportFolder'] ?? '';

        // Ambil scheduleTime dari INI
        $scheduleTime = $parsedIni['SAP']['ScheduleTime'] ?? '';
        $hour = '';
        $minute = '';
        if ($scheduleTime) {
            $parts = explode(':', $scheduleTime);
            if (count($parts) == 2) {
                $h = (int)$parts[0];
                $m = $parts[1];
                if ($h == 0) $h = 24; // 00:xx -> 24:xx (tampilan)
                $hour = $h;
                $minute = $m;
            }
        }

        return view('admin.sap-settings.index', compact('settings', 'hour', 'minute'));
    }

    public function browseFolder(Request $request)
    {
        $path = $request->get('path', 'C:\\');
        
        // Mode khusus untuk melihat daftar Drive (Windows)
        if ($path === 'DRIVES') {
            $directories = [];
            foreach (range('A', 'Z') as $letter) {
                $drive = $letter . ':\\';
                if (is_dir($drive)) {
                    $directories[] = [
                        'name' => 'Local Disk (' . $letter . ':)',
                        'path' => $drive
                    ];
                }
            }
            return response()->json([
                'current_path' => 'My Computer',
                'parent_path' => null, // Paling atas
                'directories' => $directories
            ]);
        }

        // Hapus backslash di akhir agar seragam, kecuali untuk root drive seperti C:\
        $path = rtrim($path, '\\/');
        if (preg_match('/^[A-Z]:$/i', $path)) {
            $path .= '\\';
        }

        if (!is_dir($path)) {
            return response()->json(['error' => 'Path tidak valid', 'path' => $path], 404);
        }

        $directories = [];
        try {
            $items = scandir($path);
            foreach ($items as $item) {
                if ($item == '.' || $item == '..') continue;
                $fullPath = rtrim($path, '\\/') . DIRECTORY_SEPARATOR . $item;
                if (is_dir($fullPath)) {
                    $directories[] = [
                        'name' => $item,
                        'path' => $fullPath
                    ];
                }
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Permission denied', 'path' => $path], 403);
        }

        // Parent path logic
        $parentPath = dirname($path);
        // Jika sudah di root drive (misal C:\), parent-nya adalah 'DRIVES'
        if ($parentPath === $path || $path === '') {
            $parentPath = 'DRIVES';
        }

        return response()->json([
            'current_path' => $path,
            'parent_path' => $parentPath,
            'directories' => $directories
        ]);
    }

    public function update(Request $request, SapConfigService $sapConfigService)
    {
        $request->validate([
            'sapUser' => 'required|string',
            'exportFolder' => 'required|string',
            'hour' => 'required|numeric|min:1|max:24',
            'minute' => 'required|numeric|min:0|max:59',
        ], [
            'hour.required' => 'Jam wajib dipilih.',
            'minute.required' => 'Menit wajib dipilih.',
            'exportFolder.required' => 'Folder Export tidak boleh kosong.'
        ]);

        $folder = $request->exportFolder;
        
        // HAPUS VALIDASI is_dir dan is_writable di sisi PHP
        // Karena jika di-hosting di server yang berbeda (atau dibatasi open_basedir), 
        // PHP tidak bisa mengecek folder lokal PC Windows.
        // Validasi ketersediaan folder akan diserahkan sepenuhnya ke script VBS/Bot saat berjalan.

        if (!$sapConfigService->isWritable()) {
            return back()->with('error', 'Folder / File konfigurasi SAP tidak bisa ditulis (Permission Denied): ' . dirname($sapConfigService->getPath()) . '. Pastikan folder memiliki izin tulis.');
        }

        $sapConfigService->ensureExists();
        $parsedIni = $sapConfigService->read();

        // Update values
        $parsedIni['SAP']['sapUser'] = $request->sapUser;
        
        if ($request->filled('sapPass')) {
            $parsedIni['SAP']['sapPass'] = $request->sapPass;
        }

        // Format hour dan minute
        $h = (int)$request->hour;
        $m = (int)$request->minute;
        if ($h == 24) $h = 0;
        $scheduleTime = sprintf('%02d:%02d', $h, $m);

        $parsedIni['SAP']['ScheduleTime'] = $scheduleTime;
        $parsedIni['Export']['exportFolder'] = $folder;

        // Write INI back
        $sapConfigService->write($parsedIni);

        return back()->with('success', 'Pengaturan Bot SAP berhasil disimpan. Jadwal eksekusi ditetapkan pukul ' . $scheduleTime . ' WIB.');
    }
}
