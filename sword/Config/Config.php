<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Config sınıfı - Yapılandırma yönetimi
 */

namespace Sword\Config;

class Config
{
    /**
     * Yüklenen yapılandırma verileri
     */
    private static $config = [];

    /**
     * Yapılandırma dosyalarının yüklü olup olmadığı
     */
    private static $loaded = [];

    /**
     * Yapılandırma dosyası yükler
     *
     * @param string $name Yapılandırma dosyası adı
     * @param string|null $path Dosya yolu (opsiyonel)
     * @return array|null Yapılandırma verileri
     */
    public static function load($name, $path = null)
    {
        if (isset(self::$loaded[$name])) {
            return self::$config[$name] ?? null;
        }

        // Dosya yolunu belirle
        if ($path === null) {
            $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(dirname(__FILE__)));
            $configPaths = [
                $basePath . '/app/config/' . $name . '.php',
                dirname(dirname(__FILE__)) . '/Config/' . $name . '.php',
                $basePath . '/' . $name . '.php'
            ];
        } else {
            $configPaths = [$path];
        }

        // Dosyayı bul ve yükle
        foreach ($configPaths as $configPath) {
            if (file_exists($configPath)) {
                $config = include $configPath;
                if (is_array($config)) {
                    self::$config[$name] = $config;
                    self::$loaded[$name] = true;
                    return $config;
                }
            }
        }

        self::$loaded[$name] = true;
        return null;
    }

    /**
     * Yapılandırma değeri alır
     *
     * @param string $key Anahtar (nokta notasyonu desteklenir)
     * @param mixed $default Varsayılan değer
     * @return mixed Değer
     */
    public static function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $configName = array_shift($keys);

        // Yapılandırma dosyası yüklenmemişse yükle
        if (!isset(self::$loaded[$configName])) {
            self::load($configName);
        }

        $config = self::$config[$configName] ?? [];

        // Nokta notasyonu ile değere ulaş
        foreach ($keys as $k) {
            if (is_array($config) && isset($config[$k])) {
                $config = $config[$k];
            } else {
                return $default;
            }
        }

        return $config;
    }

    /**
     * Yapılandırma değeri ayarlar
     *
     * @param string $key Anahtar (nokta notasyonu desteklenir)
     * @param mixed $value Değer
     * @return void
     */
    public static function set($key, $value)
    {
        $keys = explode('.', $key);
        $configName = array_shift($keys);

        // Yapılandırma dosyası yüklenmemişse yükle
        if (!isset(self::$loaded[$configName])) {
            self::load($configName);
        }

        if (!isset(self::$config[$configName])) {
            self::$config[$configName] = [];
        }

        // Nokta notasyonu ile değer ata
        $config = &self::$config[$configName];
        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }
        $config = $value;
    }

    /**
     * Yapılandırma değeri var mı?
     *
     * @param string $key Anahtar (nokta notasyonu desteklenir)
     * @return bool Var mı?
     */
    public static function has($key)
    {
        $keys = explode('.', $key);
        $configName = array_shift($keys);

        // Yapılandırma dosyası yüklenmemişse yükle
        if (!isset(self::$loaded[$configName])) {
            self::load($configName);
        }

        $config = self::$config[$configName] ?? [];

        // Nokta notasyonu ile değeri kontrol et
        foreach ($keys as $k) {
            if (is_array($config) && isset($config[$k])) {
                $config = $config[$k];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Tüm yapılandırma verilerini döndürür
     *
     * @param string|null $name Belirli bir yapılandırma dosyası adı
     * @return array Yapılandırma verileri
     */
    public static function all($name = null)
    {
        if ($name !== null) {
            if (!isset(self::$loaded[$name])) {
                self::load($name);
            }
            return self::$config[$name] ?? [];
        }

        return self::$config;
    }

    /**
     * Yapılandırma verilerini temizler
     *
     * @param string|null $name Belirli bir yapılandırma dosyası adı
     * @return void
     */
    public static function clear($name = null)
    {
        if ($name !== null) {
            unset(self::$config[$name], self::$loaded[$name]);
        } else {
            self::$config = [];
            self::$loaded = [];
        }
    }
}
