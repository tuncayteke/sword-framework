<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Constants sınıfı - Sabit değerleri tanımlar
 */

namespace Sword\Config;

class Constants
{
    /**
     * Framework sürümü
     */
    const VERSION = '1.0.0';

    /**
     * Framework adı
     */
    const NAME = 'Sword Framework';

    /**
     * Şifreleme metodu
     * Bu değer değiştirilmemelidir, aksi halde şifrelenmiş veriler çözülemez
     */
    const CRYPTOR_METHOD = 'AES-256-CBC';

    /**
     * Varsayılan zaman dilimi
     */
    const DEFAULT_TIMEZONE = 'UTC';

    /**
     * Varsayılan dil
     */
    const DEFAULT_LOCALE = 'en';

    /**
     * Varsayılan karakter seti
     */
    const DEFAULT_CHARSET = 'UTF-8';

    /**
     * Varsayılan önbellek sürücüsü
     */
    const DEFAULT_CACHE_DRIVER = 'file';

    /**
     * Varsayılan önbellek süresi (saniye)
     */
    const DEFAULT_CACHE_TTL = 3600;

    /**
     * Varsayılan oturum süresi (dakika)
     */
    const DEFAULT_SESSION_LIFETIME = 120;

    /**
     * Varsayılan çerez öneki
     */
    const DEFAULT_COOKIE_PREFIX = 'sword_';

    /**
     * Varsayılan çerez yolu
     */
    const DEFAULT_COOKIE_PATH = '/';

    /**
     * Varsayılan çerez SameSite değeri
     */
    const DEFAULT_COOKIE_SAMESITE = 'Lax';

    /**
     * Varsayılan dizin yolları
     */
    const DEFAULT_PATHS = [
        'cache' => '/content/storage/cache',
        'views' => '/app/views',
        'logs' => '/content/storage/logs',
        'sessions' => '/content/storage/sessions',
        'uploads' => '/content/uploads'
    ];

    /**
     * Desteklenen önbellek sürücüleri
     */
    const SUPPORTED_CACHE_DRIVERS = [
        'file',
        'encrypted',
        'view',
        'model',
        'redis',
        'memcached',
        'lscache'
    ];

    /**
     * Desteklenen oturum sürücüleri
     */
    const SUPPORTED_SESSION_DRIVERS = [
        'file',
        'database',
        'redis',
        'memcached'
    ];

    /**
     * Desteklenen ortamlar
     */
    const SUPPORTED_ENVIRONMENTS = [
        'development',
        'testing',
        'production'
    ];

    /**
     * Sabit değeri döndürür
     *
     * @param string $name Sabit adı
     * @param mixed $default Varsayılan değer
     * @return mixed Sabit değeri
     */
    public static function get($name, $default = null)
    {
        $constName = 'self::' . $name;
        return defined($constName) ? constant($constName) : $default;
    }

    /**
     * Varsayılan dizin yolunu döndürür
     *
     * @param string $key Dizin anahtarı
     * @param string $basePath Temel dizin
     * @return string Dizin yolu
     */
    public static function getPath($key, $basePath = '')
    {
        if (isset(self::DEFAULT_PATHS[$key])) {
            return $basePath . self::DEFAULT_PATHS[$key];
        }
        return $basePath;
    }
}
