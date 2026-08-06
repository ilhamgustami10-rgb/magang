# Bot RPA SAP Darsana

Bot ini dirancang untuk mengekspor data realisasi dari SAP GUI secara otomatis.

## Persiapan & Instalasi
1. Buka Command Prompt atau PowerShell, lalu navigasi ke folder bot:
   ```cmd
   cd "D:\PKL Project\Darsana\bot"
   ```
2. Buat virtual environment (venv) dan aktifkan:
   ```cmd
   python -m venv venv
   venv\Scripts\activate
   ```
3. Instal semua library yang dibutuhkan:
   ```cmd
   pip install -r requirements.txt
   ```

## Verifikasi Instalasi
Jalankan perintah ini di CMD (pastikan venv masih aktif) untuk memverifikasi apakah library sudah siap:
```cmd
python -c "import pyautogui, cv2, pygetwindow, pyperclip; print('Semua library siap!')"
```

## Syarat Menjalankan Bot (Skenario B)
Jika bot dijalankan dalam mode normal (bukan DRYRUN), SAP GUI HARUS memenuhi syarat:
- Sudah login secara manual.
- Layar/komputer tidak dalam keadaan terkunci (locked).
- Bot dijalankan di sesi desktop yang sama dengan SAP GUI terbuka.

## Pengujian Mode DRYRUN
Mode DRYRUN (mensimulasikan proses export tanpa interaksi SAP) dapat dilakukan dengan menset variable environment sebelum menjalankan skrip utama (lihat `bot_export.py`). Mode ini akan menyalin file dari `sample/realisasi_sample.csv` ke folder tujuan (misal `D:/Sap_export`).
