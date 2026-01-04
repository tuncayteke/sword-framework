<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Throttle sınıfı - İşlem sınırlama (Rate Limiting)
 */

class Throttle
{
    /**
     * Throttle verileri
     */
    private static $data = [];

    /**
     * İşlem sınırlaması kontrol eder
     *
     * @param string $key Benzersiz anahtar
     * @param int $maxAttempts Maksimum deneme sayısı
     * @param int $decayMinutes Sıfırlama süresi (dakika)
     * @return bool İşlem yapılabilir mi?
     */
    public static function attempt($key, $maxAttempts = 5, $decayMinutes = 1)
    {
        $key = self::resolveKey($key);

        // Mevcut verileri al
        $attempts = self::getAttempts($key);
        $resetTime = self::getResetTime($key);

        // Süre dolmuşsa sıfırla
        if (time() > $resetTime) {
            self::clear($key);
            $attempts = 0;
        }

        // Limit aşıldı mı?
        if ($attempts >= $maxAttempts) {
            return false;
        }

        // Deneme sayısını artır
        self::incrementAttempts($key, $decayMinutes);

        return true;
    }

    /**
     * Deneme sayısını artırır
     *
     * @param string $key Anahtar
     * @return int Yeni deneme sayısı
     */
    public static function increase($key)
    {
        $key = self::resolveKey($key);

        self::$data[$key] = (self::$data[$key] ?? 0) + 1;

        return self::getAttempts($key);
    }

    /**
     * Deneme sayısını azaltır
     *
     * @param string $key Anahtar
     * @param int $amount Azaltılacak miktar
     * @return int Yeni deneme sayısı
     */
    public static function decrease($key, $amount = 1)
    {
        $key = self::resolveKey($key);

        $current = self::getAttempts($key);
        self::$data[$key] = max(0, $current - $amount);

        return self::getAttempts($key);
    }

    /**
     * Kalan deneme sayısını döndürür
     *
     * @param string $key Anahtar
     * @param int $maxAttempts Maksimum deneme
     * @return int Kalan deneme
     */
    public static function remaining($key, $maxAttempts)
    {
        $key = self::resolveKey($key);
        $attempts = self::getAttempts($key);

        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Sıfırlanma süresini döndürür
     *
     * @param string $key Anahtar
     * @return int Saniye cinsinden süre
     */
    public static function availableIn($key)
    {
        $key = self::resolveKey($key);
        $resetTime = self::getResetTime($key);

        return max(0, $resetTime - time());
    }

    /**
     * Throttle verilerini temizler
     *
     * @param string $key Anahtar
     * @return void
     */
    public static function clear($key)
    {
        $key = self::resolveKey($key);

        unset(self::$data[$key]);
        unset(self::$data[$key . ':timer']);
    }

    /**
     * Deneme sayısını döndürür
     *
     * @param string $key Anahtar
     * @return int Deneme sayısı
     */
    private static function getAttempts($key)
    {
        return self::$data[$key] ?? 0;
    }

    /**
     * Sıfırlama zamanını döndürür
     *
     * @param string $key Anahtar
     * @return int Timestamp
     */
    private static function getResetTime($key)
    {
        return self::$data[$key . ':timer'] ?? 0;
    }

    /**
     * Deneme sayısını artırır
     *
     * @param string $key Anahtar
     * @param int $decayMinutes Sıfırlama süresi
     * @return void
     */
    private static function incrementAttempts($key, $decayMinutes)
    {
        self::$data[$key] = (self::$data[$key] ?? 0) + 1;

        if (!isset(self::$data[$key . ':timer'])) {
            self::$data[$key . ':timer'] = time() + ($decayMinutes * 60);
        }
    }

    /**
     * Anahtarı çözümler (IP + action)
     *
     * @param string $key Anahtar
     * @return string Çözümlenmiş anahtar
     */
    private static function resolveKey($key)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return $ip . '|' . $key;
    }
}
