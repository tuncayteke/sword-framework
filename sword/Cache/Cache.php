<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Cache sınıfı - Önbellek sistemlerini yönetir
 */

require_once __DIR__ . '/CacheInterface.php';
require_once __DIR__ . '/FileCache.php';
require_once __DIR__ . '/ViewCache.php';
require_once __DIR__ . '/ModelCache.php';

class Cache
{
    /**
     * Önbellek sürücüleri
     */
    private static $drivers = [];

    /**
     * Varsayılan sürücü
     */
    private static $defaultDriver = 'file';

    /**
     * Şifreleme sınıfı
     */
    private static $cryptor = null;

    /**
     * Önbellek yapılandırması
     */
    private static $config = [
        'driver' => 'file',
        'path' => null,
        'ttl' => 3600,
        'encrypt' => false
    ];

    /**
     * Sınıfı başlatır
     */
    public static function init()
    {
        // Sword sınıfından yapılandırma değerlerini al
        if (class_exists('Sword')) {
            self::$config['driver'] = Sword::getData('cache_driver') ?: self::$config['driver'];
            self::$config['ttl'] = Sword::getData('cache_ttl') ?: self::$config['ttl'];
            self::$config['encrypt'] = Sword::getData('cache_encrypt') !== null ? Sword::getData('cache_encrypt') : self::$config['encrypt'];

            // Sword::getPath kullanarak önbellek yolunu al
            if (method_exists('Sword', 'getPath')) {
                self::$config['path'] = Sword::getPath('cache');
            } else {
                self::$config['path'] = Sword::getData('cache_path') ?: self::$config['path'];
            }
        }

        // Constants sınıfından varsayılan değerleri al
        if (class_exists('\\Sword\\Config\\Constants')) {
            if (self::$config['driver'] === 'file') {
                self::$config['driver'] = \Sword\Config\Constants::DEFAULT_CACHE_DRIVER;
            }

            if (self::$config['ttl'] === 3600) {
                self::$config['ttl'] = \Sword\Config\Constants::DEFAULT_CACHE_TTL;
            }
        }

        // Yol belirtilmemişse varsayılan yolu kullan
        if (self::$config['path'] === null && defined('BASE_PATH')) {
            self::$config['path'] = BASE_PATH . '/content/storage/cache';

            // Dizin yoksa oluştur
            if (!is_dir(self::$config['path'])) {
                mkdir(self::$config['path'], 0755, true);
            }
        }
    }

    /**
     * Önbellek sürücüsü alır veya oluşturur
     *
     * @param string $driver Sürücü adı
     * @return CacheInterface Önbellek sürücüsü
     */
    public static function driver($driver = null)
    {
        $driver = $driver ?: self::$defaultDriver;

        if (!isset(self::$drivers[$driver])) {
            self::$drivers[$driver] = self::createDriver($driver);
        }

        return self::$drivers[$driver];
    }

    /**
     * Önbellek sürücüsü oluşturur
     *
     * @param string $driver Sürücü adı
     * @return CacheInterface Önbellek sürücüsü
     * @throws Exception Geçersiz sürücü
     */
    private static function createDriver($driver)
    {
        // Yapılandırma yüklenmemişse yükle
        if (self::$config['path'] === null) {
            self::init();
        }

        // Cryptor sınıfını al
        if (self::$cryptor === null && class_exists('Sword')) {
            self::$cryptor = Sword::cryptor();
        }

        // Sword sınıfından yapılandırma değerlerini al
        if (class_exists('Sword')) {
            $path = Sword::getData('cache_path') ?: self::$config['path'];
            $useEncryption = Sword::getData('cache_encrypt') !== null ? Sword::getData('cache_encrypt') : self::$config['encrypt'];
        } else {
            $path = self::$config['path'];
            $useEncryption = self::$config['encrypt'];
        }

        // Desteklenen sürücüleri kontrol et
        if (class_exists('\\Sword\\Config\\Constants')) {
            $supportedDrivers = \Sword\Config\Constants::SUPPORTED_CACHE_DRIVERS;
            if (!in_array($driver, $supportedDrivers)) {
                throw new Exception("Geçersiz önbellek sürücüsü: $driver. Desteklenen sürücüler: " . implode(', ', $supportedDrivers));
            }
        }

        switch ($driver) {
            case 'file':
                return new FileCache($path, $useEncryption, self::$cryptor);

            case 'encrypted':
                return new FileCache($path, true, self::$cryptor);

            case 'view':
                $viewPath = $path . '/views';
                if (!is_dir($viewPath)) {
                    mkdir($viewPath, 0755, true);
                }
                return new ViewCache($viewPath, $useEncryption, self::$cryptor);

            case 'model':
                $modelPath = $path . '/models';
                if (!is_dir($modelPath)) {
                    mkdir($modelPath, 0755, true);
                }
                return new ModelCache($modelPath, $useEncryption, self::$cryptor);

            case 'redis':
                // Redis sürücüsü için gerekli sınıfı yükle
                if (!class_exists('RedisCache')) {
                    require_once __DIR__ . '/RedisCache.php';
                }
                return new RedisCache(self::$cryptor);

            case 'memcached':
                // Memcached sürücüsü için gerekli sınıfı yükle
                if (!class_exists('MemcachedCache')) {
                    require_once __DIR__ . '/MemcachedCache.php';
                }
                return new MemcachedCache(self::$cryptor);

            case 'lscache':
                // LiteSpeed Cache sürücüsü için gerekli sınıfı yükle
                if (!class_exists('LSCache')) {
                    require_once __DIR__ . '/LSCache.php';
                }
                return new LSCache(self::$cryptor);

            default:
                throw new Exception("Geçersiz önbellek sürücüsü: $driver");
        }
    }

    /**
     * Varsayılan sürücüyü ayarlar
     *
     * @param string $driver Sürücü adı
     * @return void
     */
    public static function setDefaultDriver($driver)
    {
        self::$defaultDriver = $driver;
    }

    /**
     * Şifreleme sınıfını ayarlar
     *
     * @param Cryptor $cryptor Şifreleme sınıfı
     * @return void
     */
    public static function setCryptor($cryptor)
    {
        self::$cryptor = $cryptor;

        // Mevcut sürücüleri güncelle
        foreach (self::$drivers as $driver) {
            if (method_exists($driver, 'setCryptor')) {
                $driver->setCryptor($cryptor);
            }
        }
    }

    /**
     * Önbelleğe veri ekler
     *
     * @param string $key Anahtar
     * @param mixed $value Değer
     * @param int $ttl Yaşam süresi (saniye)
     * @return bool Başarılı mı?
     */
    public static function set($key, $value, $ttl = null)
    {
        $ttl = $ttl ?: self::$config['ttl'];
        return self::driver()->set($key, $value, $ttl);
    }

    /**
     * Önbellekten veri alır
     *
     * @param string $key Anahtar
     * @param mixed $default Varsayılan değer
     * @return mixed Değer
     */
    public static function get($key, $default = null)
    {
        return self::driver()->get($key, $default);
    }

    /**
     * Önbellekte anahtar var mı?
     *
     * @param string $key Anahtar
     * @return bool Var mı?
     */
    public static function has($key)
    {
        return self::driver()->has($key);
    }

    /**
     * Önbellekten veri siler
     *
     * @param string $key Anahtar
     * @return bool Başarılı mı?
     */
    public static function delete($key)
    {
        return self::driver()->delete($key);
    }

    /**
     * Tüm önbelleği temizler
     *
     * @return bool Başarılı mı?
     */
    public static function clear()
    {
        return self::driver()->clear();
    }

    /**
     * Önbellekte yoksa ekler, varsa alır
     *
     * @param string $key Anahtar
     * @param callable $callback Değer üretecek fonksiyon
     * @param int $ttl Yaşam süresi (saniye)
     * @return mixed Değer
     */
    public static function remember($key, callable $callback, $ttl = null)
    {
        $ttl = $ttl ?: self::$config['ttl'];
        return self::driver()->remember($key, $callback, $ttl);
    }

    /**
     * Görünüm dosyasını önbelleğe alır
     *
     * @param string $viewFile Görünüm dosyası
     * @param array $data Görünüm verileri
     * @param int $ttl Yaşam süresi (saniye)
     * @return string Önbelleğe alınmış görünüm içeriği
     */
    public static function view($viewFile, array $data = [], $ttl = null)
    {
        $ttl = $ttl ?: self::$config['ttl'];
        return self::driver('view')->cacheView($viewFile, $data, $ttl);
    }

    /**
     * Model sorgu sonucunu önbelleğe alır
     *
     * @param string $model Model adı
     * @param string $method Metod adı
     * @param array $params Parametreler
     * @param mixed $result Sonuç
     * @param int $ttl Yaşam süresi (saniye)
     * @return bool Başarılı mı?
     */
    public static function model($model, $method, array $params = [], $result, $ttl = null)
    {
        $ttl = $ttl ?: self::$config['ttl'];
        return self::driver('model')->cacheQuery($model, $method, $params, $result, $ttl);
    }

    /**
     * Model sorgu sonucunu önbellekten alır
     *
     * @param string $model Model adı
     * @param string $method Metod adı
     * @param array $params Parametreler
     * @param mixed $default Varsayılan değer
     * @return mixed Sonuç
     */
    public static function getModel($model, $method, array $params = [], $default = null)
    {
        return self::driver('model')->getQuery($model, $method, $params, $default);
    }

    /**
     * Model için önbelleği temizler
     *
     * @param string $model Model adı
     * @return bool Başarılı mı?
     */
    public static function clearModel($model)
    {
        return self::driver('model')->clearModel($model);
    }

    /**
     * Yapılandırmayı ayarlar
     *
     * @param array $config Yapılandırma
     * @return void
     */
    public static function setConfig(array $config)
    {
        self::$config = array_merge(self::$config, $config);
    }

    /**
     * Yapılandırmayı döndürür
     *
     * @return array Yapılandırma
     */
    public static function getConfig()
    {
        return self::$config;
    }
}
