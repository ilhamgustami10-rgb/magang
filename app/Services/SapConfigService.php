<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class SapConfigService
{
    /**
     * Get the absolute path to the config_sap.ini file
     *
     * @return string
     */
    public function getPath(): string
    {
        $botPath = config('sapbot.path', 'bot');
        $configFile = config('sapbot.file', 'config_sap.ini');
        
        return base_path($botPath . DIRECTORY_SEPARATOR . $configFile);
    }

    /**
     * Check if the config file exists
     *
     * @return bool
     */
    public function exists(): bool
    {
        return File::exists($this->getPath());
    }

    /**
     * Check if the config file or its directory is writable
     *
     * @return bool
     */
    public function isWritable(): bool
    {
        $path = $this->getPath();
        
        if ($this->exists()) {
            return is_writable($path);
        }
        
        // If file doesn't exist, check if directory is writable
        return is_writable(dirname($path));
    }

    /**
     * Ensure the config file exists, create empty one if it doesn't
     *
     * @return void
     */
    public function ensureExists(): void
    {
        if (!$this->exists()) {
            $defaultContent = "[SAP]\nsapSystem=\nsapClient=\nsapUser=\nsapPass=\nsapLang=EN\nlogoutAfter=1\nScheduleTime=\n\n[Export]\nexportFolder=\nfilePrefix=realisasi_\nreportTx=ZFM001\nfmArea=1000\n\n[FundCenter]\nfundCenterLow=A022020000\nfundCenterHigh=A022020005\n";
            File::put($this->getPath(), $defaultContent);
        }
    }

    /**
     * Read the INI file into an associative array
     *
     * @return array
     */
    public function read(): array
    {
        if (!$this->exists()) {
            return [];
        }
        
        $parsed = parse_ini_file($this->getPath(), true);
        return $parsed ?: [];
    }

    /**
     * Write an associative array back to the INI file safely
     *
     * @param array $data
     * @return bool
     */
    public function write(array $data): bool
    {
        $path = $this->getPath();
        
        // Create backup
        if ($this->exists()) {
            File::copy($path, $path . '.bak');
        }
        
        $content = "";
        foreach ($data as $section => $values) {
            $content .= "[$section]\n";
            foreach ($values as $key => $val) {
                // Escape values if needed, but keep it simple for now
                $content .= "$key=$val\n";
            }
            $content .= "\n";
        }
        
        $result = File::put($path, trim($content) . "\n");
        return $result !== false;
    }
}
