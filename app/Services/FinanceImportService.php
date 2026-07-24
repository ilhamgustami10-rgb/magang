<?php

namespace App\Services;

use App\Models\FinanceBranch;
use App\Models\FinanceItem;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FinanceImportService
{
    public function import($filePath)
    {
        // Use PhpSpreadsheet to read the file (supports CSV and XLSX)
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Start transaction
        DB::transaction(function () use ($rows) {
            // REPLACE data: clear existing
            FinanceItem::query()->delete();
            FinanceBranch::query()->delete();

            $buffer = [];
            
            // Skip header (assumed to be row 0)
            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // skip header

                // Ensure row has enough columns
                if (count($row) < 12) continue;

                $label = trim($row[0] ?? '');
                if (empty($label)) continue;

                if (strcasecmp($label, 'Funds Center') === 0) {
                    // Grand total row, skip
                    break;
                }

                // Split label into code and name
                // Token 1 = code, rest = name
                $parts = preg_split('/\s+/', $label, 2);
                $code = $parts[0] ?? '';
                $name = trim($parts[1] ?? '');

                // Extract values (RKAP -> 1, Release -> 3, Commitment -> 8, Consume -> 10, Available -> 11)
                $rkap = $this->parseNumber($row[1] ?? '');
                $release_budget = $this->parseNumber($row[3] ?? '');
                $commitment = $this->parseNumber($row[8] ?? '');
                $total_consume = $this->parseNumber($row[10] ?? '');
                $available_budget = $this->parseNumber($row[11] ?? '');

                $itemData = [
                    'code' => $code,
                    'name' => $name,
                    'rkap' => $rkap,
                    'release_budget' => $release_budget,
                    'commitment' => $commitment,
                    'total_consume' => $total_consume,
                    'available_budget' => $available_budget,
                ];

                if (preg_match('/^A\d+/', $code)) {
                    // This is a BRANCH row
                    $branch = FinanceBranch::create($itemData);

                    // Attach all items in buffer to this branch
                    foreach ($buffer as $item) {
                        $item['branch_id'] = $branch->id;
                        FinanceItem::create($item);
                    }
                    
                    // Clear buffer
                    $buffer = [];
                } else if (preg_match('/^\d/', $code)) {
                    // This is an ITEM row
                    $buffer[] = $itemData;
                }
            }
        });
    }

    private function parseNumber($value)
    {
        $value = trim((string)$value);
        if ($value === '' || $value === null) {
            return 0;
        }

        // Remove quotes if any
        $value = str_replace(['"', "'"], '', $value);
        $value = trim($value);

        if ($value === '') {
            return 0;
        }

        // Check if it's European format (contains comma)
        if (strpos($value, ',') !== false) {
            // Remove thousand separator dots
            $value = str_replace('.', '', $value);
            // Replace comma with dot for decimal
            $value = str_replace(',', '.', $value);
            return (int) round((float) $value);
        }

        // Otherwise parse directly
        return (int) $value;
    }
}
