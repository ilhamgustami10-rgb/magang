<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class SapSettingsController extends Controller
{
    public function index()
    {
        $iniPath = config('sap.config_ini_path');
        
        $settings = [
            'sapUser' => '',
            'exportFolder' => '',
        ];

        if (file_exists($iniPath)) {
            $parsedIni = parse_ini_file($iniPath, true);
            $settings['sapUser'] = $parsedIni['SAP']['sapUser'] ?? '';
            $settings['exportFolder'] = $parsedIni['Export']['exportFolder'] ?? '';
        }

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
        if ($parentPath === $path || $path === '') {
            $parentPath = null;
        }

        return response()->json([
            'current_path' => $path,
            'parent_path' => $parentPath,
            'directories' => $directories
        ]);
    }

    public function update(Request $request)
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
        if (!is_dir($folder)) {
            return back()->with('error', 'Folder Export tidak valid atau tidak ditemukan: ' . $folder . '. Silakan buat folder terlebih dahulu.');
        }
        if (!is_writable($folder)) {
            return back()->with('error', 'Folder Export tidak bisa ditulis (Permission Denied): ' . $folder);
        }

        $iniPath = config('sap.config_ini_path');
        
        if (!file_exists($iniPath)) {
            return back()->with('error', 'File konfigurasi SAP tidak ditemukan: ' . $iniPath);
        }

        // Parse existing INI
        $parsedIni = parse_ini_file($iniPath, true);

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
        $this->writeIniFile($parsedIni, $iniPath);

        return back()->with('success', 'Pengaturan Bot SAP berhasil disimpan. Jadwal eksekusi ditetapkan pukul ' . $scheduleTime . ' WIB.');
    }

    private function writeIniFile($data, $filepath)
    {
        $content = "";
        foreach ($data as $section => $values) {
            $content .= "[$section]\r\n";
            foreach ($values as $key => $val) {
                // Keep values without quotes
                $content .= "$key=$val\r\n";
            }
            $content .= "\r\n";
        }
        
        // Remove trailing empty line for exact format preservation
        $content = rtrim($content) . "\r\n";
        
        file_put_contents($filepath, $content);
    }
}
