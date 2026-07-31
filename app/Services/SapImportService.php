<?php

namespace App\Services;

use App\Models\BudgetRealisasi;
use App\Models\ImportLog;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SapImportService
{
    public function import(string $filePath, string $originalFileName, string $source = 'upload')
    {
        $ext = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        
        $reportDate = now()->format('Y-m-d');
        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $originalFileName, $matches)) {
            $reportDate = $matches[1];
        } elseif (preg_match('/(\d{4}\d{2}\d{2})/', $originalFileName, $matches)) {
            $reportDate = \Carbon\Carbon::createFromFormat('Ymd', $matches[1])->format('Y-m-d');
        } elseif (preg_match('/(\d{1,2})([A-Za-z]+)\s*(\d{4})/', $originalFileName, $matches)) {
            try {
                $reportDate = \Carbon\Carbon::parse($matches[0])->format('Y-m-d');
            } catch (\Exception $e) {
                // Ignore if unparseable
            }
        }
        
        if (in_array($ext, ['csv', 'txt'])) {
            $stats = $this->parseCsv($filePath, $reportDate);
        } elseif (in_array($ext, ['xlsx', 'xls'])) {
            $stats = $this->parseExcel($filePath, $reportDate);
        } else {
            throw new \Exception("Unsupported file type: {$ext}");
        }

        // Log import
        $importLog = ImportLog::create([
            'file_name' => $originalFileName,
            'report_date' => $reportDate,
            'source' => $source,
            'rows_imported' => $stats['rows_imported'],
            'branches_count' => $stats['branches_count'],
            'items_count' => $stats['items_count'],
            'skipped_count' => $stats['skipped_count']
        ]);

        // Assosiate import_id with the new rows
        BudgetRealisasi::where('report_date', $reportDate)
            ->whereNull('import_id') // only update newly inserted/updated rows that have no import_id or we can just update all for this date
            ->update(['import_id' => $importLog->id]);

        return $stats;
    }

    private function parseCsv(string $filePath, string $reportDate): array
    {
        $content = file_get_contents($filePath);
        $bom = pack('H*','EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);
        $lines = explode("\n", $content);
        
        // Auto-deteksi delimiter: TAB, koma, titik-koma
        // SAP GUI sering mengekspor file .csv dengan delimiter TAB, bukan koma
        $sample = implode("\n", array_slice($lines, 0, 20));
        $counts = [
            ','  => substr_count($sample, ','),
            ';'  => substr_count($sample, ';'),
            "\t" => substr_count($sample, "\t"),
        ];
        arsort($counts);
        $delimiter = array_key_first($counts);
        if (($counts[$delimiter] ?? 0) === 0) {
            $delimiter = ','; // fallback ke koma jika tidak ada delimiter terdeteksi
        }

        $dataRows = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $dataRows[] = str_getcsv($line, $delimiter);
            }
        }

        return $this->processData($dataRows, $reportDate);
    }

    private function parseExcel(string $filePath, string $reportDate): array
    {
        if (!class_exists(IOFactory::class)) {
            throw new \Exception("PhpSpreadsheet is not installed.");
        }
        
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, false, false); 
        
        $spreadsheet->disconnectWorksheets();
        unset($worksheet, $spreadsheet);

        return $this->processData($rows, $reportDate);
    }

    private function processData(array $rows, string $reportDate): array
    {
        $headerMapping = [];
        $headerParsed = false;
        $targetColumns = ['rkap', 'release_budget', 'commitment', 'total_consume', 'available_budget'];
        $buffer = [];
        
        $stats = [
            'rows_imported' => 0,
            'branches_count' => 0,
            'items_count' => 0,
            'skipped_count' => 0
        ];

        foreach ($rows as $rowIndex => $row) {
            if (!$headerParsed) {
                foreach ($row as $index => $colName) {
                    $colName = (string) $colName;
                    $colClean = strtolower(trim($colName));
                    
                    if (str_contains($colClean, 'funds center') || str_contains($colClean, 'commitment item') || $colClean === 'rkap') {
                        $headerParsed = true;
                    }

                    if (str_contains($colClean, 'funds center') || str_contains($colClean, 'commitment item')) {
                        $headerMapping['funds_center'] = $index;
                    }
                    if ($colClean === 'rkap') $headerMapping['rkap'] = $index;
                    if (str_contains($colClean, 'release budget') && !str_contains($colClean, 'not release')) $headerMapping['release_budget'] = $index;
                    if ($colClean === 'commitment') $headerMapping['commitment'] = $index;
                    if (str_contains($colClean, 'total consume') && !str_contains($colClean, '%')) $headerMapping['total_consume'] = $index;
                    if (str_contains($colClean, 'available budget')) $headerMapping['available_budget'] = $index;
                }
                continue;
            }

            if (!isset($headerMapping['funds_center'])) {
                continue;
            }

            // Clean leading spaces and * or ** 
            $firstColRaw = (string) ($row[$headerMapping['funds_center']] ?? '');
            $firstColRaw = preg_replace('/^\*+\s*/', '', trim($firstColRaw));
            
            if (empty($firstColRaw)) {
                continue;
            }

            $code = '';
            $name = '';
            
            if (strpos($firstColRaw, '  ') !== false) {
                $parts = explode('  ', $firstColRaw, 2);
                $code = trim($parts[0]);
                $name = trim($parts[1] ?? '');
            } else {
                $code = $firstColRaw;
            }

            // If the code has spaces, take the first word as code
            if (strpos($code, ' ') !== false) {
                $codeParts = explode(' ', $code, 2);
                $code = trim($codeParts[0]);
                $name = trim($codeParts[1]) . ' ' . $name;
                $name = trim($name);
            }

            $values = [];
            foreach ($targetColumns as $col) {
                $idx = $headerMapping[$col] ?? -1;
                if ($idx !== -1 && isset($row[$idx])) {
                    $rawVal = $row[$idx];

                    if (is_numeric($rawVal)) {
                        // Raw numeric from spreadsheet or pure numeric string
                        $values[$col] = (int) round((float) $rawVal);
                    } else {
                        $valStr = (string) $rawVal;
                        
                        // Check if it's purely scientific notation inside a string
                        if (preg_match('/^[+\-]?(?:0|[1-9]\d*)(?:\.\d*)?(?:[eE][+\-]?\d+)?$/', trim($valStr))) {
                            $values[$col] = (int) round((float) trim($valStr));
                        } else {
                            // Text formatting from CSV or formatted Excel
                            // Remove thousands separator (dot)
                            $valStr = str_replace('.', '', $valStr);
                            // Replace decimal separator (comma) with dot
                            $valStr = str_replace(',', '.', $valStr);
                            // Trim spaces and quotes
                            $valStr = trim(str_replace(['"', ' '], '', $valStr));
                            
                            if (str_ends_with($valStr, '-')) {
                                $valStr = '-' . rtrim($valStr, '-');
                            }
                            
                            $values[$col] = (int) round((float) $valStr);
                        }
                    }
                } else {
                    $values[$col] = 0;
                }
            }

            try {
                if (stripos($firstColRaw, 'funds center') !== false) {
                    BudgetRealisasi::updateOrCreate([
                        'report_date' => $reportDate,
                        'branch_code' => null,
                        'item_code' => null,
                        'level' => 'total',
                    ], array_merge($values, [
                        'branch_name' => null,
                        'item_name' => 'Grand Total',
                    ]));
                    $stats['rows_imported']++;
                    continue;
                }

                if (preg_match('/^\d{10}$/', $code)) {
                    $buffer[] = [
                        'item_code' => $code,
                        'item_name' => $name,
                        'values' => $values,
                    ];
                } elseif (preg_match('/^A\d+$/i', $code) || preg_match('/^A\d+/', $code)) {
                    $branchCode = $code;
                    $branchName = $name;

                    foreach ($buffer as $item) {
                        BudgetRealisasi::updateOrCreate([
                            'report_date' => $reportDate,
                            'branch_code' => $branchCode,
                            'item_code' => $item['item_code'],
                            'level' => 'item',
                        ], array_merge($item['values'], [
                            'branch_name' => $branchName,
                            'item_name' => $item['item_name'],
                        ]));
                        $stats['rows_imported']++;
                        $stats['items_count']++;
                    }
                    $buffer = [];

                    BudgetRealisasi::updateOrCreate([
                        'report_date' => $reportDate,
                        'branch_code' => $branchCode,
                        'item_code' => null,
                        'level' => 'cabang',
                    ], array_merge($values, [
                        'branch_name' => $branchName,
                        'item_name' => null,
                    ]));
                    $stats['rows_imported']++;
                    $stats['branches_count']++;
                } else {
                    // Not item, not branch, not total
                    $stats['skipped_count']++;
                }
            } catch (\Exception $e) {
                Log::error("Failed to parse row: " . $e->getMessage(), ['row' => $row]);
                $stats['skipped_count']++;
            }
        }

        return $stats;
    }
}
