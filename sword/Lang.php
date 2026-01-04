<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Lang sınıfı - Çok dilli destek sistemi
 */

class Lang
{
    /**
     * Dil dizinleri
     */
    private static $langDirectories = [
        'core' => null // BASE_PATH ile doldurulacak
    ];

    /**
     * Yüklenen çeviriler
     */
    private static $translations = [];

    /**
     * Aktif dil
     */
    private static $currentLang = 'tr';

    /**
     * Başlatma durumu
     */
    private static $initialized = false;

    /**
     * Sistemi başlatır
     */
    public static function init()
    {
        if (self::$initialized) {
            return;
        }

        // Core dizinini ayarla
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        self::$langDirectories['core'] = $basePath . '/app/langs';

        // Aktif dili ayarla
        $configLang = Sword::getData('language', 'tr');
        self::setLanguage($configLang);

        self::$initialized = true;
    }

    /**
     * Dil dizini ekler
     *
     * @param string $key Anahtar
     * @param string $path Dizin yolu
     */
    public static function addDirectory($key, $path)
    {
        self::init();
        self::$langDirectories[$key] = $path;
    }

    /**
     * Aktif dili ayarlar
     *
     * @param string $lang Dil kodu
     */
    public static function setLanguage($lang)
    {
        self::$currentLang = $lang;
        self::$translations = []; // Cache temizle
    }

    /**
     * Aktif dili döndürür
     *
     * @return string Dil kodu
     */
    public static function getLanguage()
    {
        return self::$currentLang;
    }

    /**
     * Çeviri döndürür
     *
     * @param string $key Çeviri anahtarı
     * @param array $params Parametreler
     * @param string $directory Dizin anahtarı
     * @return string Çeviri
     */
    public static function get($key, $params = [], $directory = 'core')
    {
        self::init();

        // Çeviriyi yükle
        $translation = self::loadTranslation($key, $directory);

        // Parametreleri değiştir
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                $translation = str_replace(':' . $param, $value, $translation);
            }
        }

        return $translation;
    }

    /**
     * Çeviriyi yükler
     *
     * @param string $key Çeviri anahtarı
     * @param string $directory Dizin anahtarı
     * @return string Çeviri
     */
    private static function loadTranslation($key, $directory)
    {
        $cacheKey = $directory . '.' . self::$currentLang;

        // Cache'de var mı?
        if (!isset(self::$translations[$cacheKey])) {
            self::loadDirectory($directory);
        }

        // Nested key desteği (user.not_found)
        $keys = explode('.', $key);
        $translation = self::$translations[$cacheKey] ?? [];

        foreach ($keys as $k) {
            if (isset($translation[$k])) {
                $translation = $translation[$k];
            } else {
                return $key; // Çeviri bulunamazsa anahtarı döndür
            }
        }

        return is_string($translation) ? $translation : $key;
    }

    /**
     * Dizinden çevirileri yükler
     *
     * @param string $directory Dizin anahtarı
     */
    private static function loadDirectory($directory)
    {
        if (!isset(self::$langDirectories[$directory])) {
            return;
        }

        $langDir = self::$langDirectories[$directory];
        $langFile = $langDir . '/' . self::$currentLang . '.php';

        $cacheKey = $directory . '.' . self::$currentLang;

        if (file_exists($langFile)) {
            self::$translations[$cacheKey] = include $langFile;
        } else {
            self::$translations[$cacheKey] = [];
        }
    }

    /**
     * Tüm dizinleri döndürür
     *
     * @return array Dizinler
     */
    public static function getDirectories()
    {
        self::init();
        return self::$langDirectories;
    }

    /**
     * Belirli dizini döndürür
     *
     * @param string $key Dizin anahtarı
     * @return string|null Dizin yolu
     */
    public static function getDirectory($key)
    {
        self::init();
        return self::$langDirectories[$key] ?? null;
    }
}

/**
 * Global çeviri fonksiyonu
 *
 * @param string $key Çeviri anahtarı
 * @param array $params Parametreler
 * @param string $directory Dizin anahtarı
 * @return string Çeviri
 */
function __($key, $params = [], $directory = 'core')
{
    return Lang::get($key, $params, $directory);
}
