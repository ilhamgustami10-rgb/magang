# Update Fitur Import Dashboard Finance (Darsana)

Paket ini memperbaiki logika **import** dan membuat dashboard **100% dinamis** dari DB:
- Terima file tabel apa pun: **.csv / .tsv / .txt / .xls / .xlsx / .xlsm / .ods**
- Deteksi header & kolom **berdasarkan nama** (bukan posisi) — tahan kolom/baris kosong & kolom bergeser
- Grouping **item dulu, cabang (A0...) menutup grup**
- **Cabang baru otomatis** jadi tab baru (tidak ada cabang/item yang di-hardcode)
- Replace-on-import dalam 1 transaksi DB

## Isi paket
```
app/Http/Controllers/FinanceController.php
app/Models/FinanceBranch.php
app/Models/FinanceItem.php
app/Services/SapBudgetImporter.php
database/migrations/2026_07_27_000001_create_finance_branches_table.php
database/migrations/2026_07_27_000002_create_finance_items_table.php
resources/views/finance.blade.php
routes/web-finance-snippet.php   (cuplikan untuk ditempel ke routes/web.php)
```

## Langkah pemasangan

1. **Salin file** ke project Laravel (`D:\PKL Project\Darsana`) mengikuti struktur folder di atas (timpa yang lama; backup dulu bila perlu).

2. **Install PhpSpreadsheet** (untuk baca .xls/.xlsx):
   ```bash
   composer require phpoffice/phpspreadsheet
   ```

3. **Tambahkan route** — buka `routes/web.php`, salin isi `routes/web-finance-snippet.php`:
   ```php
   use App\Http\Controllers\FinanceController;

   Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
   Route::post('/finance/import', [FinanceController::class, 'import'])->name('finance.import');
   ```
   > Kalau kamu sudah punya route/controller Finance sendiri, cukup pindahkan method `index()` & `import()` ke controllermu, dan pastikan nama route `finance.import` cocok dengan action form di Blade.

4. **Jalankan migrasi**:
   ```bash
   php artisan migrate
   ```
   (Ini juga menyelesaikan error `Table 'darsana.finance_branches' doesn't exist`.)

5. **Bersihkan cache view/route** (opsional tapi disarankan):
   ```bash
   php artisan optimize:clear
   ```

6. Buka `http://localhost/finance` (atau `php artisan serve` lalu `http://127.0.0.1:8000/finance`), lalu klik **Upload & Import** dan pilih file `BUDGET JULI 2026_UPDATE 2707.xls`.

## Yang akan terjadi setelah import
- Muncul 6 tab cabang otomatis, termasuk **Unit Blora (A022020006)** yang baru — tanpa mengubah kode.
- Setiap tab menampilkan KPI, 2 donut (single-select item), 2 bar chart top, dan tabel detail sesuai item cabang tsb.
- Ringkasan sukses: `Import berhasil: N cabang, M item terbaca.`

## Catatan integrasi Blade
`resources/views/finance.blade.php` di sini adalah halaman **berdiri sendiri** (lengkap `<html>`).
Kalau dashboardmu memakai layout (`@extends('layouts.app')`), pindahkan bagian:
- `<style>...</style>` ke `@push('styles')`
- markup `.wrap` ke dalam `@section('content')`
- kedua `<script>` ke `@push('scripts')`
Struktur data (`$data`) dari controller tetap sama.

## Struktur data yang dikirim controller ke Blade
```json
{
  "period": "Terakhir diperbarui 27 Jul 2026 19:30",
  "branches": [
    { "code": "A022020000", "name": "Cabang Surabaya",
      "rkap": 0, "release": 0, "commitment": 0, "consume": 0, "available": 0,
      "items": [ { "code": "5102021000", "name": "H-PMLH Bang Lap", "rkap": 0, "release": 0, "commitment": 0, "consume": 0, "available": 0 } ]
    }
  ]
}
```
