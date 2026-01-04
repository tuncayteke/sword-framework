<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * MemoryManager - Bellek yönetimi
 */

class MemoryManager
{
    private static $instances = [];
    private static $memoryLimit = null;

    /**
     * Bellek limitini ayarlar
     */
    public static function setMemoryLimit($limit)
    {
        self::$memoryLimit = $limit;
        ini_set('memory_limit', $limit);
    }

    /**
     * Mevcut bellek kullanımını döndürür
     */
    public static function getMemoryUsage($real = false)
    {
        return memory_get_usage($real);
    }

    /**
     * Maksimum bellek kullanımını döndürür
     */
    public static function getPeakMemoryUsage($real = false)
    {
        return memory_get_peak_usage($real);
    }

    /**
     * Bellek kullanımını MB cinsinden döndürür
     */
    public static function getMemoryUsageMB($real = false)
    {
        return round(self::getMemoryUsage($real) / 1024 / 1024, 2);
    }

    /**
     * Static cache'leri temizler
     */
    public static function clearStaticCaches()
    {
        // Sword sınıfındaki static değişkenleri temizle
        if (class_exists('Sword')) {
            $reflection = new ReflectionClass('Sword');
            $properties = $reflection->getStaticProperties();

            foreach ($properties as $name => $value) {
                if (is_array($value)) {
                    $reflection->setStaticPropertyValue($name, []);
                }
            }
        }

        // Cache sınıfındaki static değişkenleri temizle
        if (class_exists('Cache')) {
            Cache::clear();
        }

        // Logger cache'ini temizle
        if (class_exists('Logger')) {
            // Logger'da static cache varsa temizle
        }
    }

    /**
     * Singleton instance'ları temizler
     */
    public static function clearSingletons()
    {
        self::$instances = [];
    }

    /**
     * Garbage collection'ı zorla çalıştır
     */
    public static function forceGarbageCollection()
    {
        if (function_exists('gc_collect_cycles')) {
            return gc_collect_cycles();
        }
        return false;
    }

    /**
     * Bellek limitine yaklaşıldığında uyarı ver
     */
    public static function checkMemoryLimit($threshold = 0.8)
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1') return false; // Limit yok

        $limitBytes = self::parseMemoryLimit($limit);
        $currentUsage = self::getMemoryUsage(true);

        if ($currentUsage > ($limitBytes * $threshold)) {
            Logger::warning('Bellek kullanımı yüksek', [
                'current' => self::formatBytes($currentUsage),
                'limit' => $limit,
                'percentage' => round(($currentUsage / $limitBytes) * 100, 2)
            ]);
            return true;
        }

        return false;
    }

    /**
     * Memory limit string'ini byte'a çevirir
     */
    private static function parseMemoryLimit($limit)
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $limit = (int) $limit;

        switch ($last) {
            case 'g':
                $limit *= 1024;
            case 'm':
                $limit *= 1024;
            case 'k':
                $limit *= 1024;
        }

        return $limit;
    }

    /**
     * Byte'ları okunabilir formata çevirir
     */
    private static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Request sonunda otomatik temizlik
     */
    public static function cleanup()
    {
        self::clearStaticCaches();
        self::forceGarbageCollection();

        Logger::debug('Memory cleanup completed', [
            'memory_usage' => self::formatBytes(self::getMemoryUsage(true)),
            'peak_usage' => self::formatBytes(self::getPeakMemoryUsage(true))
        ]);
    }
}
