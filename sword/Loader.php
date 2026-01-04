<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Loader sınıfı - Sınıfların ve kaynakların yüklenmesini yönetir
 * Autoload özelliğini de içerir
 */

namespace Sword;

class Loader
{
    /**
     * Yüklenen sınıfların önbelleği
     */
    private static $loadedClasses = [];

    /**
     * Kayıtlı yollar
     */
    private static $paths = [];

    /**
     * Kayıtlı namespace'ler
     */
    private static $namespaces = [];

    /**
     * Yüklenen dosyalar
     */
    private static $loadedFiles = [];

    /**
     * Kayıtlı sınıf haritası
     */
    private static $classMap = [];

    /**
     * Ön tanımlı dizinler
     */
    private static $defaultDirectories = [
        'controllers',
        'models',
        'config',
        'helpers',
        'libraries',
        'middlewares'
    ];

    /**
     * Ön tanımlı sınıflar
     */
    private static $defaultClasses = [
        'Router' => 'sword/Router.php',
        'View' => 'sword/View.php',
        'Database' => 'sword/Database.php',
        'Request' => 'sword/Request.php',
        'Response' => 'sword/Response.php',
        'Controller' => 'sword/Controller.php',
        'Model' => 'sword/Model.php',
        'Theme' => 'sword/Theme.php',
        'Session' => 'sword/Session.php',
        'Lang' => 'sword/Lang.php',
        'Mailer' => 'sword/Mailer.php',
        'Cryptor' => 'sword/Cryptor.php',
        'Events' => 'sword/Events.php',
        'Image' => 'sword/Image.php',
        'Logger' => 'sword/Logger.php',
        'Security' => 'sword/Security.php',
        'Permalink' => 'sword/Permalink.php',
        'Shortcode' => 'sword/Shortcode.php',
        'Throttle' => 'sword/Throttle.php',
        'Thumbnails' => 'sword/Thumbnails.php',
        'Upload' => 'sword/Upload.php',
        'Validation' => 'sword/Validation.php',
        'QueryBuilder' => 'sword/QueryBuilder.php',
        'ModelMethod' => 'sword/ModelMethod.php',
        'ExceptionHandler' => 'sword/ExceptionHandler.php',
        'SwordException' => 'sword/Exception/SwordException.php',
        'ValidationException' => 'sword/Exception/ValidationException.php',
        'DatabaseException' => 'sword/Exception/DatabaseException.php',
        'MemoryManager' => 'sword/MemoryManager.php',
        'FrontendController' => 'app/controllers/FrontendController.php',
        'BackendController' => 'app/controllers/BackendController.php',
        'Sword\\View\\Renderer' => 'sword/View/Renderer.php',
        'Sword\\View\\Layout' => 'sword/View/Layout.php',
        'Sword\\View\\Cell' => 'sword/View/Cell.php',
        'Sword\\View\\Decorator' => 'sword/View/Decorator.php',
        'Sword\\Config\\Paths' => 'sword/Config/Paths.php',
        'Sword\\Config\\Constants' => 'sword/Config/Constants.php',
        'Cookie' => 'sword/Cookie.php',
        'TableBuilder' => 'sword/TableBuilder.php',
        'Monitor' => 'sword/Monitor.php'
    ];

    /**
     * Autoload başlatıldı mı?
     */
    private static $autoloadInitialized = false;

    /**
     * Autoload'u başlatır
     *
     * @return void
     */
    public static function init()
    {
        if (!self::$autoloadInitialized) {
            // Sword namespace'ini ekle
            self::addNamespace('Sword', dirname(__FILE__));

            // App namespace'ini ekle
            $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(__FILE__));
            self::addNamespace('App', $basePath . DIRECTORY_SEPARATOR . 'app');

            // Ön tanımlı sınıfları ekle
            foreach (self::$defaultClasses as $className => $filePath) {
                self::addClass($className, $filePath);
            }

            // Sword dizinini yol olarak ekle
            self::addPath(dirname(__FILE__));

            // Ön tanımlı dizinleri ekle
            self::addPath($basePath);
            self::addPath($basePath . DIRECTORY_SEPARATOR . 'app');

            foreach (self::$defaultDirectories as $directory) {
                $dirPath = $basePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $directory;
                if (is_dir($dirPath)) {
                    self::addPath($dirPath);
                }
            }

            // Config dizinini özel olarak ekle
            $configPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Config';
            if (is_dir($configPath)) {
                self::addPath($configPath);
                self::loadDirectory($configPath, true);
            }

            // Autoload'u kaydet
            spl_autoload_register([__CLASS__, 'loadClass']);

            // Global alias'ları tanımla
            if (!class_exists('Sword', false)) {
                class_alias('\\Sword', 'Sword');
            }

            self::$autoloadInitialized = true;
        }
    }

    /**
     * Ön tanımlı dizinleri ayarlar
     *
     * @param array $directories Dizin listesi
     * @return void
     */
    public static function setDefaultDirectories(array $directories)
    {
        self::$defaultDirectories = $directories;
    }

    /**
     * Ön tanımlı dizinlere yeni dizin ekler
     *
     * @param string $directory Dizin adı
     * @return void
     */
    public static function addDefaultDirectory($directory)
    {
        if (!in_array($directory, self::$defaultDirectories)) {
            self::$defaultDirectories[] = $directory;
        }
    }

    /**
     * Ön tanımlı sınıfları ayarlar
     *
     * @param array $classes Sınıf haritası
     * @return void
     */
    public static function setDefaultClasses(array $classes)
    {
        self::$defaultClasses = $classes;
    }

    /**
     * Ön tanımlı sınıflara yeni sınıf ekler
     *
     * @param string $className Sınıf adı
     * @param string $filePath Dosya yolu
     * @return void
     */
    public static function addDefaultClass($className, $filePath)
    {
        self::$defaultClasses[$className] = $filePath;
    }

    /**
     * Sınıf yükleme yollarını ekler
     *
     * @param string $path Yüklenecek sınıfların bulunduğu dizin
     * @return void
     */
    public static function addPath($path)
    {
        if (!in_array($path, self::$paths)) {
            self::$paths[] = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
        }
    }

    /**
     * Namespace için özel bir yol ekler
     *
     * @param string $namespace Namespace
     * @param string $path Yol
     * @return void
     */
    public static function addNamespace($namespace, $path)
    {
        $namespace = trim($namespace, '\\') . '\\';
        self::$namespaces[$namespace] = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Sınıf haritasına bir sınıf ekler
     *
     * @param string $className Sınıf adı
     * @param string $filePath Dosya yolu
     * @return void
     */
    public static function addClass($className, $filePath)
    {
        self::$classMap[$className] = $filePath;
    }

    /**
     * Bir sınıfı yükler
     *
     * @param string $className Yüklenecek sınıf adı
     * @return bool Yükleme başarılı oldu mu?
     */
    public static function loadClass($className)
    {
        // Hata ayıklama için sınıf yükleme girişimini loglayalım
        // error_log("Trying to load class: " . $className);

        // Zaten yüklenmişse tekrar yükleme
        if (isset(self::$loadedClasses[$className]) || class_exists($className, false)) {
            return true;
        }



        // Sınıf haritasında var mı?
        if (isset(self::$classMap[$className])) {
            $file = self::$classMap[$className];

            // Eğer tam yol değilse BASE_PATH ekle
            if (!file_exists($file)) {
                $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(__FILE__));
                $file = $basePath . '/' . $file;
            }

            // Dosya yolunu normalize et
            $file = str_replace(['/', '\\\\'], DIRECTORY_SEPARATOR, $file);

            if (self::requireFile($file)) {
                self::$loadedClasses[$className] = $file;
                return true;
            }
        }

        // Namespace'e göre yükleme
        $namespace = '';
        $classShortName = $className;

        if (($lastNsPos = strripos($className, '\\'))) {
            $namespace = substr($className, 0, $lastNsPos);
            $classShortName = substr($className, $lastNsPos + 1);

            // Namespace için özel yol var mı?
            foreach (self::$namespaces as $ns => $path) {
                if (strpos($namespace . '\\', $ns) === 0) {
                    $relPath = substr($namespace, strlen($ns)) . DIRECTORY_SEPARATOR;
                    $relPath = str_replace('\\', DIRECTORY_SEPARATOR, $relPath);
                    $file = $path . $relPath . $classShortName . '.php';

                    if (self::requireFile($file)) {
                        self::$loadedClasses[$className] = $file;
                        return true;
                    }
                }
            }

            // Namespace'i doğrudan dizin yapısına çevirerek dene
            $namespacePath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);

            // Framework kök dizininde ara
            $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(__FILE__));
            $file = $basePath . DIRECTORY_SEPARATOR . $namespacePath . DIRECTORY_SEPARATOR . $classShortName . '.php';
            if (self::requireFile($file)) {
                self::$loadedClasses[$className] = $file;
                return true;
            }

            // Sword dizininde ara
            $swordPath = dirname(__FILE__);
            $file = $swordPath . DIRECTORY_SEPARATOR . $namespacePath . DIRECTORY_SEPARATOR . $classShortName . '.php';
            if (self::requireFile($file)) {
                self::$loadedClasses[$className] = $file;
                return true;
            }
        }

        // PSR-4 stili yükleme
        $classFile = str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';

        // Kayıtlı yollarda ara
        foreach (self::$paths as $path) {
            $file = $path . $classFile;

            if (self::requireFile($file)) {
                self::$loadedClasses[$className] = $file;
                return true;
            }
        }

        // PSR-0 stili yükleme dene
        $classFile = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        foreach (self::$paths as $path) {
            $file = $path . $classFile;

            if (self::requireFile($file)) {
                self::$loadedClasses[$className] = $file;
                return true;
            }
        }

        // Ön tanımlı dizinlerde ara
        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(__FILE__));
        foreach (self::$defaultDirectories as $directory) {
            $dirPath = $basePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $directory;
            if (is_dir($dirPath)) {
                $file = $dirPath . DIRECTORY_SEPARATOR . $classShortName . '.php';
                if (self::requireFile($file)) {
                    self::$loadedClasses[$className] = $file;
                    return true;
                }
            }
        }

        // Sword dizininde doğrudan ara
        $swordPath = dirname(__FILE__);
        $file = $swordPath . DIRECTORY_SEPARATOR . $classShortName . '.php';
        if (self::requireFile($file)) {
            self::$loadedClasses[$className] = $file;
            return true;
        }

        // Yükleme başarısız olduğunda hata ayıklama için log
        // error_log("Failed to load class: " . $className);

        return false;
    }

    /**
     * Bir dosyayı yükler
     *
     * @param string $file Dosya yolu
     * @return bool Dosya yüklendi mi?
     */
    public static function requireFile($file)
    {
        // Dosya yolunu normalize et
        $file = str_replace(['/', '\\\\'], DIRECTORY_SEPARATOR, $file);

        // Dosya var mı kontrol et
        if (file_exists($file)) {
            // Hata ayıklama için yüklenen dosyayı loglayabiliriz
            // error_log("Loading file: " . $file);

            require_once $file;
            self::$loadedFiles[] = $file;
            return true;
        }

        // Hata ayıklama için bulunamayan dosyayı loglayabiliriz
        // error_log("File not found: " . $file);

        return false;
    }

    /**
     * Bir dosyayı yükler ve içeriğini döndürür
     *
     * @param string $file Dosya yolu
     * @return mixed Dosya içeriği veya false
     */
    public static function loadFile($file)
    {
        if (file_exists($file)) {
            return include $file;
        }
        return false;
    }

    /**
     * Bir dizindeki tüm PHP dosyalarını yükler
     *
     * @param string $directory Dizin yolu
     * @param bool $recursive Alt dizinleri de yükle
     * @return int Yüklenen dosya sayısı
     */
    public static function loadDirectory($directory, $recursive = false)
    {
        $directory = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR;
        $count = 0;

        if (!is_dir($directory)) {
            return $count;
        }

        $files = scandir($directory);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $directory . $file;

            if (is_dir($path) && $recursive) {
                $count += self::loadDirectory($path, true);
            } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                if (self::requireFile($path)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Yüklenen sınıfların listesini döndürür
     *
     * @return array Yüklenen sınıflar
     */
    public static function getLoadedClasses()
    {
        return self::$loadedClasses;
    }

    /**
     * Yüklenen dosyaların listesini döndürür
     *
     * @return array Yüklenen dosyalar
     */
    public static function getLoadedFiles()
    {
        return self::$loadedFiles;
    }

    /**
     * Kayıtlı yolların listesini döndürür
     *
     * @return array Kayıtlı yollar
     */
    public static function getPaths()
    {
        return self::$paths;
    }

    /**
     * Sınıf haritasını döndürür
     *
     * @return array Sınıf haritası
     */
    public static function getClassMap()
    {
        return self::$classMap;
    }

    /**
     * Kayıtlı namespace'lerin listesini döndürür
     *
     * @return array Kayıtlı namespace'ler
     */
    public static function getNamespaces()
    {
        return self::$namespaces;
    }

    /**
     * Ön tanımlı dizinlerin listesini döndürür
     *
     * @return array Ön tanımlı dizinler
     */
    public static function getDefaultDirectories()
    {
        return self::$defaultDirectories;
    }

    /**
     * Ön tanımlı sınıfların listesini döndürür
     *
     * @return array Ön tanımlı sınıflar
     */
    public static function getDefaultClasses()
    {
        return self::$defaultClasses;
    }
}
