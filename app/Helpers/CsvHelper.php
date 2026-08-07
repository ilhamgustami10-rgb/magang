<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Utility untuk memproses file CSV/data tabular dari laporan SAP/mainframe.
 *
 * Fungsi utama:
 * - isDataRow()           : Memfilter baris non-data (header, separator, metadata)
 * - parseDate()           : Parser tanggal multi-format
 * - extractReportPeriod() : Deteksi metadata periode laporan
 * - streamCsv()           : Baca CSV baris-per-baris (generator, hemat memori)
 */
class CsvHelper
{
    /**
     * Periksa apakah baris CSV merupakan data aktual
     * (bukan header laporan, garis pemisah, atau metadata).
     *
     * @param  array  $row  Satu baris CSV (array kolom)
     * @return bool
     */
    public static function isDataRow(array $row): bool
    {
        // Semua kolom kosong
        $filtered = array_filter($row, fn($v) => $v !== null && trim((string) $v) !== '');
        if (empty($filtered)) {
            return false;
        }

        $first = trim((string) ($row[0] ?? ''));

        // Kolom pertama kosong
        if ($first === '') {
            return false;
        }

        // Baris pemisah: hanya berisi karakter - = _ * | + dan spasi
        if (preg_match('/^[\-=_\*\|+\s]+$/', $first)) {
            return false;
        }

        // Kata kunci header/metadata — hanya cocokkan jika kolom pertama
        // TIDAK mengandung digit (baris data biasanya ada angkanya)
        if (!preg_match('/\d/', $first)) {
            $keywords = [
                'tanggal', 'laporan', 'report', 'total', 'page',
                'halaman', 'periode', 'date', 'daily movement',
            ];
            $firstLower = strtolower($first);
            foreach ($keywords as $kw) {
                if (str_contains($firstLower, $kw)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Coba ekstrak periode laporan dari baris metadata.
     * Contoh: "Tanggal : 01/07/2026" → "2026-07-01"
     *
     * @param  array  $row
     * @return string|null  Tanggal Y-m-d atau null
     */
    public static function extractReportPeriod(array $row): ?string
    {
        $text = implode(' ', array_map(fn($v) => trim((string) $v), $row));

        if (preg_match('/(?:tanggal|date|periode)\s*:?\s*(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{4})/i', $text, $m)) {
            return self::parseDate($m[1]);
        }

        return null;
    }

    /**
     * Parse string tanggal ke format Y-m-d, mendukung berbagai format:
     *   d/m/Y   d-m-Y   Y-m-d   d.m.Y   dmY (tanpa pemisah)
     *   d/m/Y H:i   Y-m-d H:i:s
     *   Excel serial number (mis. 46023)
     *
     * @param  mixed       $value  Nilai mentah (string, int, float, null)
     * @return string|null Y-m-d atau null jika gagal
     */
    public static function parseDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Excel serial number (bilangan bulat besar, biasanya > 10000)
        if (is_numeric($value) && (float) $value > 10000) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                // fall through
            }
        }

        $value = trim((string) $value);

        // Daftar format yang dicoba secara berurutan
        $formats = [
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'd/m/Y',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'd-m-Y',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
            'd.m.Y H:i:s',
            'd.m.Y H:i',
            'd.m.Y',
        ];

        foreach ($formats as $fmt) {
            try {
                $date = Carbon::createFromFormat($fmt, $value);
                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Format tanpa pemisah: dmY (contoh: 07082026 → 2026-08-07)
        if (preg_match('/^\d{8}$/', $value)) {
            try {
                $date = Carbon::createFromFormat('dmY', $value);
                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // fall through
            }
        }

        // Upaya terakhir: strtotime()
        $ts = @strtotime($value);
        if ($ts && $ts > 0) {
            $parsed = date('Y-m-d', $ts);
            // Sanity check: tahun harus masuk akal (2000-2099)
            $year = (int) date('Y', $ts);
            if ($year >= 2000 && $year <= 2099) {
                return $parsed;
            }
        }

        return null;
    }

    /**
     * Baca file CSV secara streaming (generator), satu baris pada satu waktu.
     * Auto-deteksi delimiter (koma, titik-koma, atau tab).
     * Menangani BOM UTF-8.
     *
     * @param  string       $filePath   Path absolut ke file CSV
     * @param  string|null  $delimiter  Delimiter manual (null = auto-detect)
     * @return \Generator<int, array>   Yield satu baris sebagai array per iterasi
     */
    public static function streamCsv(string $filePath, ?string $delimiter = null): \Generator
    {
        $handle = @fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Gagal membuka file: {$filePath}");
        }

        try {
            // Skip BOM UTF-8
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            // Auto-detect delimiter dari 20 baris pertama
            if ($delimiter === null) {
                $sampleLines = [];
                $pos = ftell($handle);
                for ($i = 0; $i < 20; $i++) {
                    $line = fgets($handle);
                    if ($line === false) {
                        break;
                    }
                    $sampleLines[] = $line;
                }
                fseek($handle, $pos);

                $sample = implode('', $sampleLines);
                $counts = [
                    ','  => substr_count($sample, ','),
                    ';'  => substr_count($sample, ';'),
                    "\t" => substr_count($sample, "\t"),
                ];
                arsort($counts);
                $delimiter = array_key_first($counts);
                if (($counts[$delimiter] ?? 0) === 0) {
                    $delimiter = ',';
                }
            }

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                yield $row;
            }
        } finally {
            fclose($handle);
        }
    }
}
