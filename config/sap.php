<?php

return [
    'import_path'  => env('SAP_IMPORT_PATH', 'D:/Sap_export'),
    'archive_path' => env('SAP_ARCHIVE_PATH', 'D:/Sap_export/archive'),
    'failed_path'  => env('SAP_FAILED_PATH', 'D:/Sap_export/failed'),
    'export_dir'   => env('SAP_EXPORT_DIR', 'D:/Sap_export'),
    'cscript_path' => env('SAP_CSCRIPT_PATH', 'C:\Windows\System32\cscript.exe'),
    'bot_command'  => env('SAP_BOT_COMMAND', 'C:\Windows\System32\cscript.exe //nologo "D:/PKL Project/Darsana/bot/export_sap.vbs"'),
    'bot_timeout'  => (int) env('SAP_BOT_TIMEOUT', 150),
    'bot_dryrun'   => env('DARSANA_BOT_DRYRUN', 0),
];
