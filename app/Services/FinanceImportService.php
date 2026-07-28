<?php

namespace App\Services;

use App\Models\FinanceBranch;
use App\Models\FinanceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Importer realisasi anggaran SAP.
 * Dipanggil dari komponen Volt admin.finances.index:
 *     app(FinanceImportService::class)->import($this->fileImport->getRealPath());
 *
 * ATURAN EMAS: TIDAK ADA daftar cabang, jumlah item, atau posisi kolom yang di-hardcode.
 * Semua "belajar" dari struktur file. Cabang baru otomatis dibuat.
 */
class FinanceImportService
{
    /** NAMA HEADER (dinormalisasi) -> nama KOLOM tabel finance_items. */
    private array $metricAliases = [
        'rkap'             => 'rkap',
        'release budget'   => 'release_budget',
        'commitment'       => 'commitment',
        'total consume'    => 'total_consume',
        'available budget' => 'available_budget',
    ];

    private array $metricColumns = ['rkap', 'release_budget', 'commitment', 'total_consume', 'available_budget'];

    /** @return array{branches:int, items:int} */
    public function import(string $path): array
    {
        $rows = $this->readRows($path);
        $branches = $this->extract($rows);

        if (empty($branches)) {
            throw new \RuntimeException('Tidak ada baris cabang (A0...) yang terbaca. Pastikan file export asli dari SAP.');
        }

        return $this->persist($branches);
    }

    /** Deteksi format dari ISI file (temp file Livewire tak punya ekstensi). */
    private function readRows(string $path): array
    {
        $handle = @fopen($path, 'rb');
        $magic = $handle ? (string) fread($handle, 8) : '';
        if ($handle) {
            fclose($handle);
        }

        $isOle = str_starts_with($magic, "\xD0\xCF\x11\xE0"); // .xls (OLE)
        $isZip = str_starts_with($magic, "PK\x03\x04");        // .xlsx / .ods (zip)

        if ($isOle || $isZip) {
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getSheet(0); // sheet pertama, nama tidak di-hardcode
            return $sheet->toArray(null, true, true, false);
        }

        return $this->readDelimited($path);
    }

    /** Baca file berdelimiter dengan auto-deteksi (koma / titik koma / tab). */
    private function readDelimited(string $path): array
    {
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new \RuntimeException('File tidak dapat dibaca.');
        }
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
        $lines = preg_split('/\r\n|\r|\n/', $raw);

        $sample = implode("\n", array_slice($lines, 0, 20));
        $counts = [',' => substr_count($sample, ','), ';' => substr_count($sample, ';'), "\t" => substr_count($sample, "\t")];
        arsort($counts);
        $delimiter = array_key_first($counts);
        if (($counts[$delimiter] ?? 0) === 0) {
            $delimiter = ',';
        }

        $rows = [];
        foreach ($lines as $line) {
            $rows[] = $line === '' ? [] : str_getcsv($line, $delimiter);
        }
        return $rows;
    }

    /** Ekstrak cabang + item: header dinamis, kolom by-nama, item→cabang. */
    private function extract(array $rows): array
    {
        $headerRow = null;
        $fcCol = null;
        foreach ($rows as $r => $cols) {
            if (!is_array($cols)) {
                continue;
            }
            foreach ($cols as $c => $val) {
                if (is_string($val) && str_contains($this->norm($val), 'funds center/commitment item')) {
                    $headerRow = $r;
                    $fcCol = $c;
                    break 2;
                }
            }
        }
        if ($headerRow === null) {
            throw new \RuntimeException('Format file tidak dikenali: header "Funds Center/Commitment Item" tidak ditemukan.');
        }

        $metricCols = [];
        foreach ($rows[$headerRow] as $c => $val) {
            if (!is_string($val)) {
                continue;
            }
            $key = $this->norm($val);
            if (isset($this->metricAliases[$key])) {
                $metricCols[$this->metricAliases[$key]] = $c;
            }
        }
        if (empty($metricCols)) {
            throw new \RuntimeException('Format file tidak dikenali: kolom anggaran tidak ditemukan.');
        }

        $branches = [];
        $buffer = [];
        $count = count($rows);

        for ($r = $headerRow + 1; $r < $count; $r++) {
            $cols = $rows[$r] ?? [];
            if (!is_array($cols)) {
                continue;
            }
            $first = isset($cols[$fcCol]) ? trim((string) $cols[$fcCol]) : '';
            if ($first === '') {
                continue;
            }

            if (preg_match('/^\**\s*(A\d+)\s+(.+)$/u', $first, $m)) {
                $branch = ['code' => $m[1], 'name' => trim($m[2]), 'items' => $buffer];
                foreach ($this->metricColumns as $col) {
                    $branch[$col] = array_sum(array_column($buffer, $col));
                }
                $branches[] = $branch;
                $buffer = [];
                continue;
            }

            if (preg_match('/^\**\s*(\d{6,})\s+(.+)$/u', $first, $m)) {
                $item = ['code' => $m[1], 'name' => trim($m[2])];
                foreach ($this->metricColumns as $col) {
                    $item[$col] = isset($metricCols[$col]) ? $this->num($cols[$metricCols[$col]] ?? 0) : 0.0;
                }
                $buffer[] = $item;
                continue;
            }

            $normFirst = strtolower($first);
            if (str_starts_with($first, '*') || str_contains($normFirst, 'funds center') || str_contains($normFirst, 'overall result')) {
                continue;
            }
        }

        return $branches;
    }

    /** Simpan: replace-on-import dalam 1 transaksi. Cabang baru otomatis dibuat. */
    private function persist(array $branches): array
    {
        return DB::transaction(function () use ($branches) {
            FinanceItem::query()->delete();
            FinanceBranch::query()->delete();

            $itemCount = 0;
            foreach ($branches as $b) {
                $branchAttrs = ['name' => $b['name']];
                foreach ($this->metricColumns as $col) {
                    if (Schema::hasColumn('finance_branches', $col)) {
                        $branchAttrs[$col] = $b[$col];
                    }
                }
                $branch = FinanceBranch::updateOrCreate(['code' => $b['code']], $branchAttrs);

                foreach ($b['items'] as $it) {
                    FinanceItem::create([
                        'branch_id'        => $branch->id,
                        'code'             => $it['code'],
                        'name'             => $it['name'],
                        'rkap'             => $it['rkap'],
                        'release_budget'   => $it['release_budget'],
                        'commitment'       => $it['commitment'],
                        'total_consume'    => $it['total_consume'],
                        'available_budget' => $it['available_budget'],
                    ]);
                    $itemCount++;
                }
            }

            return ['branches' => count($branches), 'items' => $itemCount];
        });
    }

    private function norm(?string $s): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', (string) $s)));
    }

    private function num($v): float
    {
        if (is_int($v) || is_float($v)) {
            return (float) $v;
        }
        $s = trim((string) $v);
        if ($s === '' || $s === '-') {
            return 0.0;
        }
        $negative = str_contains($s, '-');
        $digits = preg_replace('/[^\d]/', '', $s);
        if ($digits === '') {
            return 0.0;
        }
        $n = (float) $digits;
        return $negative ? -$n : $n;
    }
}
