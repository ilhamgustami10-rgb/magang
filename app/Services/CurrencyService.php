<?php
// app/Services/CurrencyService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CurrencyService
{
    protected $baseUrl = 'https://api.frankfurter.app';
    
    /**
     * Ambil kurs USD ke IDR untuk tanggal tertentu
     */
    public function getUSDRate($date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');
        $cacheKey = "usd_idr_rate_{$date}";
        
        return Cache::remember($cacheKey, now()->addDay(), function () use ($date) {
            try {
                $url = "{$this->baseUrl}/{$date}?from=USD&to=IDR";
                \Log::info('Calling Frankfurter: ' . $url);
                
                $response = Http::get($url);
                
                if ($response->successful()) {
                    $data = $response->json();
                    return $data['rates']['IDR'] ?? null;
                }
                
                return null;
                
            } catch (\Exception $e) {
                \Log::error('CurrencyService error: ' . $e->getMessage());
                return null;
            }
        });
    }
    
    /**
     * Ambil kurs untuk range tanggal
     */
    public function getRatesForDateRange($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->format('Y-m-d');
        $end = Carbon::parse($endDate)->format('Y-m-d');
        
        $cacheKey = "usd_idr_rates_{$start}_{$end}";
        
        return Cache::remember($cacheKey, now()->addDay(), function () use ($start, $end) {
            try {
                $url = "{$this->baseUrl}/{$start}..{$end}?from=USD&to=IDR";
                \Log::info('Calling Frankfurter range: ' . $url);
                
                $response = Http::get($url);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    $result = [];
                    if (isset($data['rates'])) {
                        foreach ($data['rates'] as $date => $rateData) {
                            $result[$date] = $rateData['IDR'] ?? null;
                        }
                    }
                    
                    return $result;
                }
                
                return [];
                
            } catch (\Exception $e) {
                \Log::error('CurrencyService range error: ' . $e->getMessage());
                return [];
            }
        });
    }
    
    /**
     * Ambil kurs dengan fallback ke tanggal sebelumnya
     */
    public function getRateWithFallback($date, $rates = [])
    {
        $originalDate = Carbon::parse($date);
        $dateStr = $originalDate->format('Y-m-d');
        
        // Cek di rates yang sudah ada
        if (isset($rates[$dateStr])) {
            return $rates[$dateStr];
        }
        
        // Fallback: cari 7 hari sebelumnya
        for ($i = 1; $i <= 7; $i++) {
            $prevDate = $originalDate->copy()->subDays($i)->format('Y-m-d');
            if (isset($rates[$prevDate])) {
                \Log::info("Fallback rate untuk {$dateStr} menggunakan rate {$prevDate} = {$rates[$prevDate]}");
                return $rates[$prevDate];
            }
        }
        
        // Fallback ke API untuk tanggal tertentu
        for ($i = 1; $i <= 7; $i++) {
            $prevDate = $originalDate->copy()->subDays($i)->format('Y-m-d');
            $rate = $this->getUSDRate($prevDate);
            if ($rate) {
                \Log::info("Fallback API untuk {$dateStr} menggunakan rate {$prevDate} = {$rate}");
                return $rate;
            }
        }
        
        // Default rate jika semua gagal
        \Log::warning("Menggunakan default rate 16000 untuk {$dateStr}");
        return 16000;
    }
}