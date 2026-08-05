@echo off
REM ================================================================
REM DARSANA - Pembungkus Task Scheduler (jalan otomatis malam)
REM Alur: LANGKAH 1 export dari SAP  ->  LANGKAH 2 impor ke dashboard
REM ================================================================

REM --- Ganti ini kalau 'php' tidak dikenali di CMD ---
REM Contoh XAMPP  : set PHP=C:\xampp\php\php.exe
REM Contoh Laragon: set PHP=C:\laragon\bin\php\php-8.x\php.exe
set PHP=php

set LOG=D:\PKL Project\Darsana\bot\log_bot.txt

echo ================================================== >> "%LOG%"
echo [%date% %time%] LANGKAH 1 - Mulai export SAP >> "%LOG%"

REM ---------- LANGKAH 1: export dari SAP ----------
cd /d "D:\PKL Project\Darsana\bot"
"C:\Windows\System32\cscript.exe" //nologo "D:\PKL Project\Darsana\bot\export_sap.vbs" >> "%LOG%" 2>&1
set EXPORT_CODE=%errorlevel%
echo [%date% %time%] Export selesai - exit code %EXPORT_CODE% >> "%LOG%"

REM Kalau export gagal, lewati impor
if not "%EXPORT_CODE%"=="0" goto :export_gagal

REM ---------- LANGKAH 2: impor ke dashboard ----------
echo [%date% %time%] LANGKAH 2 - Mulai impor ke dashboard >> "%LOG%"
cd /d "D:\PKL Project\Darsana"
%PHP% artisan sap:import-latest >> "%LOG%" 2>&1
echo [%date% %time%] Impor selesai - exit code %errorlevel% >> "%LOG%"
goto :selesai

:export_gagal
echo [%date% %time%] Export GAGAL - impor dilewati >> "%LOG%"

:selesai
echo. >> "%LOG%"
