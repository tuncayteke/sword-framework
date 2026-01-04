<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Theme sınıfı - Tema yönetimi
 */

class Theme
{
    /**
     * Aktif tema bilgileri
     */
    private static $activeThemes = [];

    /**
     * Tema yolları
     */
    private static $themePaths = [
        'frontend' => 'content/themes',
        'admin' => 'content/admin/themes'
    ];

    /**
     * Tema ayarlar
     */
    private static $settings = null;

    /**
     * Tema ayarlar
     *
     * @param string $type Tema tipi (frontend/admin)
     * @param string $theme Tema adı
     * @return void
     */
    public static function set($type, $theme)
    {
        self::$activeThemes[$type] = $theme;

        // Sword::setData ile de kaydet
        $themeKey = $type . '_theme';
        Sword::setData($themeKey, $theme);

        // Functions.php dosyasını yükle
        self::loadFunctions($type, $theme);
    }

    /**
     * Tema functions.php dosyasını yükler
     *
     * @param string $type Tema tipi (frontend/admin)
     * @param string $theme Tema adı
     * @return void
     */
    public static function loadFunctions($type, $theme = null)
    {
        $theme = $theme ?: self::get($type);
        $functionsFile = self::getPath($type, $theme) . '/functions.php';

        if (file_exists($functionsFile)) {
            require_once $functionsFile;
        }
    }

    /**
     * Aktif temayı döndürür
     *
     * @param string $type Tema tipi (frontend/admin)
     * @return string Tema adı
     */
    public static function get($type)
    {
        // Önce cache'den kontrol et
        if (isset(self::$activeThemes[$type])) {
            return self::$activeThemes[$type];
        }

        // Sword::getData'dan kontrol et
        $themeKey = $type . '_theme';
        $theme = Sword::getData($themeKey);

        if ($theme) {
            self::$activeThemes[$type] = $theme;
            return $theme;
        }

        // Varsayılan tema
        self::$activeThemes[$type] = 'default';
        return 'default';
    }

    /**
     * Tema yolunu döndürür
     *
     * @param string $type Tema tipi (frontend/admin)
     * @param string|null $theme Tema adı (null ise aktif tema)
     * @return string Tema yolu
     */
    public static function getPath($type, $theme = null)
    {
        $theme = $theme ?: self::get($type);
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        return $basePath . '/' . self::$themePaths[$type] . '/' . $theme;
    }



    /**
     * Tema asset yolunu döndürür
     *
     * @param string $type Tema tipi (frontend/admin)
     * @param string|null $theme Tema adı
     * @return string Asset yolu
     */
    public static function getAssetPath($type, $theme = null)
    {
        return self::getPath($type, $theme) . '/assets';
    }



    /**
     * Tema dosyası var mı kontrol eder
     *
     * @param string $type Tema tipi (frontend/admin)
     * @param string $theme Tema adı
     * @return bool Var mı?
     */
    public static function exists($type, $theme)
    {
        return is_dir(self::getPath($type, $theme));
    }

    /**
     * Tema bilgilerini döndürür
     *
     * @param string $type Tema tipi (frontend/admin)
     * @param string $theme Tema adı
     * @return array|null Tema bilgileri
     */
    public static function getInfo($type, $theme)
    {
        $themeFile = self::getPath($type, $theme) . '/theme.php';
        if (file_exists($themeFile)) {
            return include $themeFile;
        }
        return null;
    }

    /**
     * Mevcut temaları listeler
     *
     * @param string $type Tema tipi (frontend/admin)
     * @return array Tema listesi
     */
    public static function getAvailable($type)
    {
        $themesPath = defined('BASE_PATH') ? BASE_PATH . '/' . self::$themePaths[$type] : '';
        $themes = [];

        if (is_dir($themesPath)) {
            $dirs = scandir($themesPath);
            foreach ($dirs as $dir) {
                if ($dir !== '.' && $dir !== '..' && is_dir($themesPath . '/' . $dir)) {
                    if (file_exists($themesPath . '/' . $dir . '/theme.php')) {
                        $themes[] = $dir;
                    }
                }
            }
        }

        return $themes;
    }
}
