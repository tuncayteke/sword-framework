<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Monitor - Basit sistem izleme
 */

class Monitor
{
    private static $startTime = null;
    private static $metrics = [];

    /**
     * İzlemeyi başlat
     */
    public static function start()
    {
        self::$startTime = microtime(true);
        self::$metrics = [
            'requests' => 0,
            'errors' => 0,
            'memory_peak' => 0,
            'response_times' => []
        ];
    }

    /**
     * Request tamamlandığında çağır
     */
    public static function endRequest($statusCode = 200)
    {
        if (self::$startTime === null) return;

        $responseTime = microtime(true) - self::$startTime;
        $memoryUsage = memory_get_peak_usage(true);

        // Metrikleri kaydet
        self::$metrics['requests']++;
        self::$metrics['response_times'][] = $responseTime;
        self::$metrics['memory_peak'] = max(self::$metrics['memory_peak'], $memoryUsage);

        if ($statusCode >= 400) {
            self::$metrics['errors']++;
        }

        // Yavaş request uyarısı
        if ($responseTime > 2.0) {
            Logger::warning('Yavaş request', [
                'response_time' => $responseTime,
                'memory_usage' => round($memoryUsage / 1024 / 1024, 2) . 'MB',
                'url' => $_SERVER['REQUEST_URI'] ?? 'unknown'
            ]);
        }

        // Yüksek memory uyarısı
        if ($memoryUsage > 50 * 1024 * 1024) { // 50MB
            Logger::warning('Yüksek memory kullanımı', [
                'memory_usage' => round($memoryUsage / 1024 / 1024, 2) . 'MB',
                'url' => $_SERVER['REQUEST_URI'] ?? 'unknown'
            ]);
        }
    }

    /**
     * Günlük rapor oluştur
     */
    public static function dailyReport()
    {
        $avgResponseTime = count(self::$metrics['response_times']) > 0
            ? array_sum(self::$metrics['response_times']) / count(self::$metrics['response_times'])
            : 0;

        Logger::info('Günlük rapor', [
            'total_requests' => self::$metrics['requests'],
            'total_errors' => self::$metrics['errors'],
            'error_rate' => self::$metrics['requests'] > 0
                ? round((self::$metrics['errors'] / self::$metrics['requests']) * 100, 2) . '%'
                : '0%',
            'avg_response_time' => round($avgResponseTime, 3) . 's',
            'peak_memory' => round(self::$metrics['memory_peak'] / 1024 / 1024, 2) . 'MB'
        ]);
    }

    /**
     * Sistem sağlık kontrolü
     */
    public static function healthCheck()
    {
        $health = [
            'status' => 'healthy',
            'checks' => []
        ];

        // Database bağlantısı
        try {
            Sword::db()->query('SELECT 1');
            $health['checks']['database'] = 'ok';
        } catch (Exception $e) {
            $health['checks']['database'] = 'error';
            $health['status'] = 'unhealthy';
        }

        // Disk alanı
        $freeSpace = disk_free_space('.');
        $totalSpace = disk_total_space('.');
        $usagePercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;

        if ($usagePercent > 90) {
            $health['checks']['disk'] = 'warning';
            $health['status'] = 'degraded';
        } else {
            $health['checks']['disk'] = 'ok';
        }

        // Memory
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');

        if ($memoryLimit !== '-1') {
            $limitBytes = MemoryManager::parseMemoryLimit($memoryLimit);
            $memoryPercent = ($memoryUsage / $limitBytes) * 100;

            if ($memoryPercent > 80) {
                $health['checks']['memory'] = 'warning';
                $health['status'] = 'degraded';
            } else {
                $health['checks']['memory'] = 'ok';
            }
        }

        return $health;
    }
}
