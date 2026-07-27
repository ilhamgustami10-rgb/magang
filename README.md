📊 Finance Dashboard — Realisasi Anggaran AirNav Indonesia (Cabang Surabaya)
Proyek magang (PKL) untuk membangun dashboard monitoring realisasi anggaran berbasis data laporan SAP (Budget/Fund Management) milik AirNav Indonesia Cabang Surabaya. Data mentah dari SAP diolah menjadi visualisasi interaktif agar realisasi anggaran per cabang & per Funds Center lebih mudah dipantau.

🎯 Latar Belakang
Laporan realisasi anggaran dari SAP masih berbentuk tabel/CSV yang panjang dan sulit dibaca. Proyek ini bertujuan:

Mengubah data laporan SAP menjadi dashboard visual yang informatif.
Memudahkan pemantauan serapan anggaran (berapa yang sudah terpakai vs sisa) per cabang.
Menyajikan perbandingan antar Funds Center (item anggaran) secara cepat.
✨ Fitur Dashboard
Navigasi tab per cabang — Surabaya, Banyuwangi, Malang, Sumenep, dan Unit Bawean.
5 KPI utama dalam satu baris: RKAP, Release Budget, Total Consume, Commitment, dan Available Budget (lengkap dengan persentasenya).
Kartu Serapan Anggaran — persentase serapan (Total Consume / Release Budget) dengan progress bar.
2 Donut chart "Komposisi per Item":
Release vs Total Consume
Consume vs Available Budget
Dilengkapi filter single-select (radio) di dalam masing-masing kartu, angka persentase di tengah donut, dan skema 2 warna (biru tua / biru muda).
Bar chart Konsumsi Tertinggi & Sisa Anggaran Tertinggi (12 item teratas).
Tabel Detail Funds Center — dengan kolom serapan (progress bar berwarna), pencarian, dan sorting.
Tampilan HD & responsif — render tajam di layar retina, tata letak menyesuaikan ukuran layar.
🧮 Metrik yang Digunakan
Dashboard fokus pada 5 metrik dari laporan SAP:

Metrik	Keterangan
RKAP	Rencana Kerja & Anggaran Perusahaan
Release Budget	Anggaran yang sudah dirilis/dibuka
Commitment	Anggaran yang sudah dikomitmenkan (mis. kontrak/PO)
Total Consume	Realisasi anggaran yang sudah terpakai
Available Budget	Sisa anggaran yang masih tersedia
Rumus serapan: % Serapan = Total Consume / Release Budget × 100

📈 Ringkasan Analisis Data (per 10 Sep 2025)
Total keseluruhan (5 cabang, 33 item):

Metrik	Nilai (Rp)
RKAP	5.744.200.285
Release Budget	14.275.060.605
Commitment	1.255.010.566
Total Consume	10.815.710.844
Available Budget	3.459.349.761
Serapan	± 75,8%
Sebaran item per cabang:

Cabang	Kode	Jumlah Item
Cabang Surabaya	A022020000	31
Cabang Pembantu Banyuwangi	A022020001	14
Cabang Pembantu Malang	A022020002	11
Cabang Pembantu Sumenep	A022020003	13
Unit Bawean	A022020005	2
Temuan singkat:

Serapan anggaran keseluruhan berada di angka ±75,8%, artinya sebagian besar Release Budget sudah terealisasi.
Masih tersisa ± Rp 3,46 miliar Available Budget yang belum terpakai.
Cabang Surabaya memiliki item anggaran terbanyak (31 item) sehingga jadi kontributor utama realisasi.
🛠️ Teknologi
Laravel (Blade view) — kerangka aplikasi & penyaji data.
Chart.js v4 — visualisasi chart (donut & bar).
HTML + CSS custom — tampilan responsif.
PHP 8.3, MySQL — backend & database.
Data sumber: laporan SAP (Budget/Fund Management) dalam format CSV/Excel.
🗂️ Struktur Data
Data dikirim dari controller ke Blade sebagai JSON. Struktur tiap cabang:

{
  "code": "A022020000",
  "name": "Cabang Surabaya",
  "rkap": 0, "release": 0, "commitment": 0, "consume": 0, "available": 0,
  "items": [
    { "code": "...", "name": "...", "rkap": 0, "release": 0, "commitment": 0, "consume": 0, "available": 0 }
  ]
}
🚀 Cara Menjalankan
# 1. Clone repo
git clone https://github.com/ilhamgustami10-rgb/magang.git
cd magang

# 2. Install dependency
composer install
npm install

# 3. Siapkan environment
cp .env.example .env
php artisan key:generate
# sesuaikan konfigurasi database di file .env

# 4. Migrasi database
php artisan migrate

# 5. Jalankan aplikasi
php artisan serve
npm run dev
Buka http://127.0.0.1:8000 di browser.

Catatan: dashboard membutuhkan koneksi internet untuk memuat Chart.js dari CDN (jika belum di-bundle lokal).

📌 Sumber Data (SAP)
Data diambil dari laporan drilldown Budget/Fund Management SAP dengan parameter:

Area: 1000
Budget Category: 9F
Version: 0
Fiscal Year: 2026
Period: 1–12
Funds Center: A022020000 – A022020005
👥 Kontributor
Proyek magang / Praktik Kerja Lapangan (PKL) — AirNav Indonesia Cabang Surabaya.
