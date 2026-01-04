<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Paths yapılandırma dosyası - Uygulama yollarını tanımlar
 */

namespace Sword\Config;

class Paths
{
    /**
     * Uygulama kök dizini
     */
    private static $basePath;

    /**
     * Uygulama dizini
     */
    private static $appPath;

    /**
     * Görünüm dosyaları dizini
     */
    private static $viewsPath;

    /**
     * Geçici dosyalar dizini
     */
    private static $tempPath;

    /**
     * Yapılandırma dosyaları dizini
     */
    private static $configPath;

    /**
     * Genel dosyalar dizini
     */
    private static $publicPath;

    /**
     * Kaynak dosyaları dizini
     */
    private static $resourcesPath;

    /**
     * Depolama dizini
     */
    private static $storagePath;

    /**
     * Günlük dosyaları dizini
     */
    private static $logsPath;

    /**
     * Kök dizini ayarlar
     *
     * @param string $path Kök dizin
     * @return void
     */
    public static function setBasePath($path)
    {
        self::$basePath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;

        // Varsayılan yolları ayarla
        if (empty(self::$appPath)) {
            self::$appPath = self::$basePath . 'app' . DIRECTORY_SEPARATOR;
        }

        if (empty(self::$viewsPath)) {
            self::$viewsPath = self::$basePath . 'views' . DIRECTORY_SEPARATOR;
        }

        if (empty(self::$tempPath)) {
            self::$tempPath = self::$basePath . 'temp' . DIRECTORY_SEPARATOR;
        }

        if (empty(self::$configPath)) {
            self::$configPath = self::$basePath . 'config' . DIRECTORY_SEPARATOR;
        }

        if (empty(self::$publicPath)) {
            self::$publicPath = self::$basePath . 'public' . DIRECTORY_SEPARATOR;
        }

        if (empty(self::$resourcesPath)) {
            self::$resourcesPath = self::$basePath . 'resources' . DIRECTORY_SEPARATOR;
        }

        if (empty(self::$storagePath)) {
            self::$storagePath = self::$basePath . 'storage' . DIRECTORY_SEPARATOR;
        }

        if (empty(self::$logsPath)) {
            self::$logsPath = self::$storagePath . 'logs' . DIRECTORY_SEPARATOR;
        }
    }

    /**
     * Uygulama dizinini ayarlar
     *
     * @param string $path Uygulama dizini
     * @return void
     */
    public static function setAppPath($path)
    {
        self::$appPath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Görünüm dosyaları dizinini ayarlar
     *
     * @param string $path Görünüm dosyaları dizini
     * @return void
     */
    public static function setViewsPath($path)
    {
        self::$viewsPath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Geçici dosyalar dizinini ayarlar
     *
     * @param string $path Geçici dosyalar dizini
     * @return void
     */
    public static function setTempPath($path)
    {
        self::$tempPath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Yapılandırma dosyaları dizinini ayarlar
     *
     * @param string $path Yapılandırma dosyaları dizini
     * @return void
     */
    public static function setConfigPath($path)
    {
        self::$configPath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Genel dosyalar dizinini ayarlar
     *
     * @param string $path Genel dosyalar dizini
     * @return void
     */
    public static function setPublicPath($path)
    {
        self::$publicPath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Kaynak dosyaları dizinini ayarlar
     *
     * @param string $path Kaynak dosyaları dizini
     * @return void
     */
    public static function setResourcesPath($path)
    {
        self::$resourcesPath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Depolama dizinini ayarlar
     *
     * @param string $path Depolama dizini
     * @return void
     */
    public static function setStoragePath($path)
    {
        self::$storagePath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Günlük dosyaları dizinini ayarlar
     *
     * @param string $path Günlük dosyaları dizini
     * @return void
     */
    public static function setLogsPath($path)
    {
        self::$logsPath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Kök dizini döndürür
     *
     * @return string Kök dizin
     */
    public static function getBasePath()
    {
        return self::$basePath;
    }

    /**
     * Uygulama dizinini döndürür
     *
     * @return string Uygulama dizini
     */
    public static function getAppPath()
    {
        return self::$appPath;
    }

    /**
     * Görünüm dosyaları dizinini döndürür
     *
     * @return string Görünüm dosyaları dizini
     */
    public static function getViewsPath()
    {
        return self::$viewsPath;
    }

    /**
     * Geçici dosyalar dizinini döndürür
     *
     * @return string Geçici dosyalar dizini
     */
    public static function getTempPath()
    {
        return self::$tempPath;
    }

    /**
     * Yapılandırma dosyaları dizinini döndürür
     *
     * @return string Yapılandırma dosyaları dizini
     */
    public static function getConfigPath()
    {
        return self::$configPath;
    }

    /**
     * Genel dosyalar dizinini döndürür
     *
     * @return string Genel dosyalar dizini
     */
    public static function getPublicPath()
    {
        return self::$publicPath;
    }

    /**
     * Kaynak dosyaları dizinini döndürür
     *
     * @return string Kaynak dosyaları dizini
     */
    public static function getResourcesPath()
    {
        return self::$resourcesPath;
    }

    /**
     * Depolama dizinini döndürür
     *
     * @return string Depolama dizini
     */
    public static function getStoragePath()
    {
        return self::$storagePath;
    }

    /**
     * Günlük dosyaları dizinini döndürür
     *
     * @return string Günlük dosyaları dizini
     */
    public static function getLogsPath()
    {
        return self::$logsPath;
    }
}
