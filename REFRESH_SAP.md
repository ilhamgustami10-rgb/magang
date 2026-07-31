# Panduan Tombol "Refresh Data SAP"

Dokumen ini menjelaskan cara kerja tombol **Refresh Data SAP** di halaman Finance dashboard DARSANA. Tombol ini menjalankan bot VBScript yang mengekspor data realisasi anggaran dari SAP GUI, lalu mengimpor file hasil ekspor secara otomatis ke database.

---

## 🛑 Syarat Operasional (WAJIB DIPENUHI)

### 1. Dijalankan via `php artisan serve` BIASA
- DARSANA **HARUS** dijalankan via `php artisan serve` dari Command Prompt BIASA (BUKAN "Run as Administrator").
- Aplikasi web harus berjalan di user dan sesi desktop yang **SAMA** dengan SAP GUI.
- **JANGAN** dijalankan lewat Laragon, XAMPP, atau Apache Service: server-server tersebut berjalan di konteks proses/session yang berbeda dan TIDAK BISA mengendalikan SAP GUI. Jika dijalankan via service, gejala yang muncul adalah bot selalu membalas *"SAP belum login"* meskipun SAP GUI sudah terbuka.

### 2. SAP GUI HARUS Sudah Login & Layar Terbuka
- Sebelum mengklik tombol Refresh, pastikan SAP GUI sudah **terbuka dan login**.
- **Layar tidak boleh terkunci** (locked/screensaver).
- Jangan ada popup atau dialog SAP yang menghalangi layar utama.

### 3. SAP GUI Scripting Harus Aktif & Bebas Notifikasi
Buka **SAP GUI** → menu **Customize Local Layout** (ikon ⚙️ di toolbar) → **Options**:
1. Buka tab **Accessibility & Scripting** → **Scripting**
2. ✅ Centang: **Enable Scripting**
3. ❌ **HARUS DIMATIKAN** (uncheck): `Notify when a script attaches to SAP GUI`
4. ❌ **HARUS DIMATIKAN** (uncheck): `Notify when a script opens a connection`

> **Penting:** Jika opsi "Notify" ini aktif, bot akan terhenti menunggu klik popup konfirmasi dari SAP GUI (yang seringkali tidak terlihat oleh user atau diblokir), mengakibatkan error atau timeout.

### 4. Laporan Dibuka Lewat T-Code ZFM001
- Bot dirancang untuk membuka laporan melalui kode transaksi **ZFM001** di Command Field, BUKAN melalui double-click menu Favorites. Pastikan t-code ini valid di env SAP Anda.

---

## 🛠️ Urutan Pengujian (Troubleshooting)

Setiap kali Anda selesai restart PC, atau jika menemukan error, **WAJIB** ikuti urutan ini:

1. **Test Koneksi SAP**
   - Buka `/finance` di DARSANA.
   - Klik tombol hijau **"Test Koneksi SAP"**.
   - Tunggu beberapa saat.
   - **Harus muncul toast hijau: "SUKSES: terhubung ke SAP..."**
   - Jika muncul "GAGAL", berarti konfigurasi di atas (seperti Laragon vs artisan serve, atau Run as Admin) belum benar. JANGAN lanjut ke Refresh.

2. **Refresh Data SAP**
   - HANYA SETELAH "Test Koneksi" mengembalikan SUKSES, barulah klik tombol biru **"Refresh Data SAP"**.
   - Bot akan mengambil alih kursor, masuk ke ZFM001, mengekspor data ke folder `D:/Sap_export`, dan DARSANA akan otomatis mengimpor hasilnya.

---

## Alur Kerja Tombol Refresh

```
Klik "Refresh Data SAP"
  │
  ├─ Cek kunci (anti eksekusi ganda)
  │   └─ Jika sedang berjalan → tampilkan pesan "tunggu"
  │
  ├─ Jalankan bot VBScript (sinkron, menunggu selesai)
  │   ├─ Exit 0 → Sukses, lanjut impor
  │   ├─ Exit 1 → Gagal: SAP belum login / tidak ada sesi aktif.
  │   ├─ Exit 2 → Gagal membuka laporan di SAP (kode transaksi/menu). Cek layar SAP.
  │   └─ Timeout/Lainnya → Menampilkan stderr dari proses.
  │
  ├─ Cari file baru di D:/Sap_export (setelah waktu mulai)
  │   └─ Tunggu file stabil (size tidak berubah)
  │
  ├─ Impor file → upsert ke database
  │   └─ report_date = hari ini (snapshot harian)
  │
  ├─ Pindahkan file ke archive
  │
  └─ Tampilkan hasil: "X baris, Y cabang diimpor"
```

---

## Mode Darurat: Impor dari Folder Saja

Jika bot gagal (misalnya karena sistem sedang maintenance) namun Anda memiliki file CSV ekspor yang diletakkan manual di `D:/Sap_export`, Anda bisa mengklik tombol **"Impor dari folder"** (ikon dokumen).
Tombol ini akan melewati bot VBS dan langsung memproses file terbaru di folder tersebut.

---

## Konfigurasi Utama (.env)

```ini
# Perintah bot SAP (VBScript) untuk PHP 64-bit cukup pakai cscript biasa
SAP_BOT_COMMAND='cscript //nologo "D:/PKL Project/Darsana/bot/export_sap.vbs"'

# Timeout bot dalam detik
SAP_BOT_TIMEOUT=150

# Folder tujuan export
SAP_EXPORT_DIR="D:/Sap_export"

# Mode DRYRUN (1=aktif, 0=nonaktif)
DARSANA_BOT_DRYRUN=0
```
