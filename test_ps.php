<?php
use Illuminate\Support\Facades\Process;
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

putenv('USERPROFILE=C:\Users\Public');
putenv('LOCALAPPDATA=C:\Users\Public\AppData\Local');
putenv('APPDATA=C:\Users\Public\AppData\Roaming');

$process = Process::run([
    'C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\powershell.exe',
    '-NoProfile',
    '-ExecutionPolicy', 'Bypass',
    '-Command',
    "Write-Output 'Hello from PowerShell'"
]);
echo "Output: " . $process->output() . "\n";
echo "Error: " . $process->errorOutput() . "\n";
