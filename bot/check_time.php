<?php
// Script ini digunakan oleh run_export_sap.bat untuk mencocokkan waktu saat ini
// dengan waktu jadwal yang ada di config_sap.ini.
// Mengembalikan exit code 0 jika cocok, dan 1 jika tidak cocok.

$iniFile = __DIR__ . '/config_sap.ini';

// Set timezone ke Asia/Jakarta agar date('H:i') tidak memakai UTC dari php.ini server
date_default_timezone_set('Asia/Jakarta');

if (!file_exists($iniFile)) {
    exit(1);
}

$ini = parse_ini_file($iniFile);
$scheduleTime = isset($ini['ScheduleTime']) ? trim($ini['ScheduleTime']) : '';

if (empty($scheduleTime)) {
    exit(1);
}

// Bandingkan jam dan menit saat ini
$currentHourMin = date('H:i');

if ($currentHourMin === $scheduleTime) {
    exit(0);
} else {
    exit(1);
}
