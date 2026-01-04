<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Sword sınıfı - Framework'ün ana sınıfı
 */



class Sword
{
    /**
     * Uygulama örneği
     */
    private static $app = null;

    /**
     * Özel metodlar
     */
    private static $methods = [];

    /**
     * Kayıtlı sınıflar
     */
    private static $classes = [];

    /**
     * Filtreler
     */
    private static $filters = [
        'before' => [],
        'after' => []
    ];

    /**
     * Sınıf yükleme yolları
     */
    private static $paths = [];

    /**
     * Veri deposu
     */
    private static $data = [];

    /**
     * Yapılandırıcı
     */
    private function __construct()
    {
        // Singleton pattern
    }

    /**
     * Sınıfı başlatır
     */
    public static function bootstrap()
    {
        // Environment ayarlarını uygula
        self::setupEnvironment();

        // Exception handler'ı aktif et
        if (class_exists('ExceptionHandler')) {
            ExceptionHandler::register();
        }

        // Helper fonksiyonlarını yükle
        self::loadHelpers();

        // Decorator'ları yükle
        self::loadDecorators();

        // Autoloader'ı başlat
        if (file_exists(__DIR__ . '/Loader.php')) {
            require_once __DIR__ . '/Loader.php';

            // Eğer BASE_PATH tanımlıysa
            if (defined('BASE_PATH')) {
                \Sword\Loader::init();
                // Config\Paths sınıfını kontrol et ve yükle
                $pathsFile = __DIR__ . '/Config/Paths.php';
                if (file_exists($pathsFile)) {
                    require_once $pathsFile;
                    $pathsClass = 'Sword\\Config\\Paths';
                    if (class_exists($pathsClass)) {
                        $pathsClass::setBasePath(BASE_PATH);
                    }
                }

                self::path(BASE_PATH);
            }
        }
    }

    /**
     * Environment ayarlarını yapar
     */
    private static function setupEnvironment()
    {
        $environment = defined('ENVIRONMENT') ? ENVIRONMENT : 'development';

        if ($environment === 'production') {
            // Production ayarları
            ini_set('display_errors', 0);
            error_reporting(0);
            self::setData('environment', 'production');
        } else {
            // Development ayarları
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            self::setData('environment', 'development');
        }
    }

    /**
     * Uygulama örneğini başlatır
     */
    public static function init()
    {
        if (self::$app === null) {
            // Bootstrap'i çağır (helper'ları yükler)
            self::bootstrap();

            self::$app = new self();

            // Request ve Response nesnelerini oluştur
            self::$classes['request'] = self::request();
            self::$classes['response'] = self::response();
            self::$classes['router'] = self::router();
        }

        return self::$app;
    }

    /**
     * Request sınıfını döndürür
     *
     * @return Request
     */
    public static function request()
    {
        // Request sınıfını yükle
        if (!class_exists('Request')) {
            require_once __DIR__ . '/Request.php';
        }
        return new Request();
    }

    /**
     * Response sınıfını döndürür
     *
     * @return Response
     */
    public static function response()
    {
        // Response sınıfını yükle
        if (!class_exists('Response')) {
            require_once __DIR__ . '/Response.php';
        }
        return new Response();
    }

    /**
     * Router sınıfını döndürür
     *
     * @return Router
     */
    /**
     * Router sınıfını döndürür
     *
     * @return Router
     */
    public static function router()
    {
        // Singleton pattern - her zaman aynı Router örneğini döndür
        static $router = null;

        if ($router === null) {
            // Router sınıfını yükle
            if (!class_exists('Router')) {
                require_once __DIR__ . '/Router.php';
            }
            $router = new Router();
        }

        return $router;
    }


    /**
     * Özel metod ekler
     *
     * @param string $name Metod adı
     * @param callable $callback Geri çağırma fonksiyonu
     * @param array $options Seçenekler
     * @return void
     */
    public static function map($name, $callback, $options = [])
    {
        self::$methods[$name] = [
            'callback' => $callback,
            'options' => $options
        ];
    }

    /**
     * GET isteği için rota ekler
     *
     * @param string $pattern URL deseni
     * @param callable|string $callback Çağrılacak fonksiyon veya Controller@method
     * @param string|null $name Rota adı
     * @return Router
     */
    public static function routerGet($pattern, $callback, $name = null)
    {
        return self::router()->get($pattern, $callback, $name);
    }

    /**
     * POST isteği için rota ekler
     *
     * @param string $pattern URL deseni
     * @param callable|string $callback Çağrılacak fonksiyon veya Controller@method
     * @param string|null $name Rota adı
     * @return Router
     */
    public static function routerPost($pattern, $callback, $name = null)
    {
        return self::router()->post($pattern, $callback, $name);
    }

    /**
     * PUT isteği için rota ekler
     *
     * @param string $pattern URL deseni
     * @param callable|string $callback Çağrılacak fonksiyon veya Controller@method
     * @param string|null $name Rota adı
     * @return Router
     */
    public static function routerPut($pattern, $callback, $name = null)
    {
        return self::router()->put($pattern, $callback, $name);
    }

    /**
     * DELETE isteği için rota ekler
     *
     * @param string $pattern URL deseni
     * @param callable|string $callback Çağrılacak fonksiyon veya Controller@method
     * @param string|null $name Rota adı
     * @return Router
     */
    public static function routerDelete($pattern, $callback, $name = null)
    {
        return self::router()->delete($pattern, $callback, $name);
    }

    /**
     * Tüm HTTP metodları için rota ekler
     *
     * @param string $pattern URL deseni
     * @param callable|string $callback Çağrılacak fonksiyon veya Controller@method
     * @param string|null $name Rota adı
     * @return Router
     */
    public static function routerAny($pattern, $callback, $name = null)
    {
        return self::router()->any($pattern, $callback, $name);
    }

    /**
     * Rota grubu oluşturur
     *
     * @param string $prefix Grup öneki
     * @param callable $callback Grup içeriği
     * @return Router
     */
    public static function routerGroup($prefix, $callback)
    {
        return self::router()->group($prefix, $callback);
    }

    /**
     * Placeholder değeri ekler
     *
     * @param string $key Anahtar
     * @param string $value Değer
     * @return Router
     */
    public static function routerPlaceholder($key, $value)
    {
        return self::router()->placeholder($key, $value);
    }

    /**
     * Özel bir placeholder deseni ekler
     *
     * @param string $name Desen adı (:name şeklinde kullanılacak)
     * @param string $pattern Regex deseni
     * @return Router
     */
    public static function routerPattern($name, $pattern)
    {
        return self::router()->pattern($name, $pattern);
    }

    /**
     * 404 hata işleyicisini ayarlar
     *
     * @param callable $handler İşleyici fonksiyon
     * @return Router
     */
    public static function routerNotFound(callable $handler)
    {
        return self::router()->notFound($handler);
    }

    /**
     * İsimlendirilmiş rotaya URL oluşturur
     *
     * @param string $name Rota adı
     * @param array $params Parametreler
     * @return string URL
     */
    public static function routerRoute($name, array $params = [])
    {
        return self::router()->route($name, $params);
    }

    /**
     * RESTful resource rotaları oluşturur
     *
     * @param string $uri Resource URI
     * @param string $controller Controller sınıfı
     * @param string|null $name Resource adı
     * @return Router
     */
    public static function routerResource($uri, $controller, $name = null)
    {
        return self::router()->resource($uri, $controller, $name);
    }

    /**
     * Uygulama kök dizinini tespit eder
     * 
     * @return string Kök dizin (örn: /sword-framework)
     */
    public static function getBasePath()
    {
        static $basePath = null;

        if ($basePath === null) {
            // Önce manuel olarak ayarlanmış bir değer var mı kontrol et
            $configBasePath = self::getData('base_path');
            if ($configBasePath !== null) {
                $basePath = $configBasePath;
            } else {
                // index.php'nin bulunduğu dizini al
                $scriptName = $_SERVER['SCRIPT_NAME'];
                $scriptDir = dirname($scriptName);

                // Kök dizin olarak kullan (/ ile başlayıp / ile bitmeyen)
                $basePath = rtrim($scriptDir, '/');
                if (empty($basePath)) {
                    $basePath = '';
                }

                // Veri olarak sakla
                self::setData('base_path', $basePath);
            }
        }

        return $basePath;
    }

    /**
     * Mevcut domain'i tespit eder
     * 
     * @return string Domain adı
     */
    public static function getCurrentDomain()
    {
        static $domain = null;

        if ($domain === null) {
            // Manuel ayarlanmış domain var mı?
            $configDomain = self::getData('cookie_domain_override');
            if ($configDomain !== null) {
                $domain = $configDomain;
            } else {
                // HTTP_HOST'tan domain tespit et
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

                // Port numarasını çıkar
                $domain = explode(':', $host)[0];

                // Localhost veya IP adresi ise boş bırak
                if (
                    $domain === 'localhost' ||
                    filter_var($domain, FILTER_VALIDATE_IP) ||
                    strpos($domain, '.') === false
                ) {
                    $domain = '';
                }
            }
        }

        return $domain;
    }

    /**
     * İstekleri işler
     *
     * @param string|null $method HTTP metodu
     * @param string|null $uri URI
     * @return mixed İşlem sonucu
     */
    public static function routerDispatch($method = null, $uri = null)
    {
        // URI'dan base path'i çıkar
        if ($uri === null) {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            // Base path'i otomatik tespit et ve çıkar
            $basePath = self::getBasePath();
            if (!empty($basePath) && strpos($uri, $basePath) === 0) {
                $uri = substr($uri, strlen($basePath));
            }
            // URI'yi normalize et
            $uri = '/' . trim($uri, '/');
            if ($uri !== '/') {
                $uri = rtrim($uri, '/');
            }
            if (empty($uri)) {
                $uri = '/';
            }

            // Hata ayıklama bilgisi
            if (false) {
                echo "<div style='background:#f8f9fa;border:1px solid #ddd;padding:10px;margin:10px 0;font-family:monospace;'>";
                echo "<h3>Route Debug Info</h3>";
                echo "<p><strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
                echo "<p><strong>SCRIPT_NAME:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
                echo "<p><strong>Base Path:</strong> " . $basePath . "</p>";
                echo "<p><strong>Processed URI:</strong> " . $uri . "</p>";

                // Kayıtlı rotaları göster
                $router = self::router();
                if (method_exists($router, 'getRoutes')) {
                    $routes = $router->getRoutes();
                    echo "<h4>Registered Routes:</h4>";
                    if (empty($routes)) {
                        echo "<p><strong>No routes registered!</strong></p>";

                        // Router sınıfını kontrol et
                        echo "<h4>Router Instance:</h4>";
                        echo "<pre>";
                        var_dump($router);
                        echo "</pre>";
                    } else {
                        echo "<ul>";
                        foreach ($routes as $route) {
                            echo "<li>" . $route['method'] . " " . $route['pattern'] . "</li>";
                        }
                        echo "</ul>";
                    }
                } else {
                    echo "<p><strong>Router getRoutes method not available</strong></p>";
                }

                echo "</div>";
            }
        }

        return self::router()->dispatch($method, $uri);
    }

    /**
     * Bir çerçeve metodundan önce çalışacak filtre ekler
     *
     * @param string $name Metod adı
     * @param callable $callback Geri çağırma fonksiyonu
     * @return void
     */
    public static function before($name, $callback)
    {
        if (!isset(self::$filters['before'][$name])) {
            self::$filters['before'][$name] = [];
        }
        self::$filters['before'][$name][] = $callback;
    }

    /**
     * Bir çerçeve metodundan sonra çalışacak filtre ekler
     *
     * @param string $name Metod adı
     * @param callable $callback Geri çağırma fonksiyonu
     * @return void
     */
    public static function after($name, $callback)
    {
        if (!isset(self::$filters['after'][$name])) {
            self::$filters['after'][$name] = [];
        }
        self::$filters['after'][$name][] = $callback;
    }

    /**
     * Sınıf yükleme yollarını ekler
     *
     * @param string $path Yüklenecek sınıfların bulunduğu dizin
     * @return void
     */
    public static function path($path)
    {
        if (!in_array($path, self::$paths)) {
            self::$paths[] = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;

            // Loader sınıfına da ekle
            if (class_exists('Sword\\Loader')) {
                \Sword\Loader::addPath($path);
            }
        }
    }

    /**
     * Bir değişkeni ayarlar
     *
     * @param string $key Anahtar
     * @param mixed $value Değer
     * @return void
     */
    public static function setData($key, $value)
    {
        self::$data[$key] = $value;
    }

    /**
     * Bir değişkeni döndürür
     *
     * @param string $key Anahtar
     * @param mixed $default Varsayılan değer
     * @return mixed Değer
     */
    public static function getData($key, $default = null)
    {
        return isset(self::$data[$key]) ? self::$data[$key] : $default;
    }

    /**
     * Tüm değişkenleri döndürür
     *
     * @return array Tüm değişkenler
     */
    public static function getDatas()
    {
        return self::$data;
    }

    /**
     * Dizin yolunu döndürür
     *
     * @param string $key Dizin anahtarı (cache, views, logs, sessions, uploads)
     * @param bool $createIfNotExists Dizin yoksa oluştur
     * @return string Dizin yolu
     */
    public static function getPath($key, $createIfNotExists = true)
    {
        // Önce Sword::getData'dan kontrol et
        $path = self::getData($key . '_path');

        // Yoksa Constants sınıfından al
        if ($path === null && class_exists('\\Sword\\Config\\Constants')) {
            $path = \Sword\Config\Constants::getPath($key, defined('BASE_PATH') ? BASE_PATH : '');
        }

        // Hala yoksa varsayılan yolu oluştur
        if ($path === null && defined('BASE_PATH')) {
            switch ($key) {
                case 'cache':
                    $path = BASE_PATH . '/content/storage/cache';
                    break;
                case 'views':
                    $path = BASE_PATH . '/app/views';
                    break;
                case 'logs':
                    $path = BASE_PATH . '/content/storage/logs';
                    break;
                case 'plugins':
                    $path = BASE_PATH . '/content/plugins';
                    break;
                case 'sessions':
                    $path = BASE_PATH . '/content/storage/sessions';
                    break;
                case 'uploads':
                    $path = BASE_PATH . '/content/uploads';
                    break;
                case 'storage':
                    $path = BASE_PATH . '/content/storage';
                    break;
                default:
                    $path = BASE_PATH . '/content/storage/' . $key;
            }
        }

        // Dizin yoksa ve oluşturulması isteniyorsa oluştur
        if ($path !== null && $createIfNotExists && !is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }

    /**
     * Bilinmeyen statik metod çağrılarını yönlendirir
     *
     * @param string $name Metod adı
     * @param array $params Parametreler
     * @return mixed
     * @throws Exception Metod bulunamazsa
     */
    public static function __callStatic($name, $params)
    {
        // Özel metodları kontrol et
        if (isset(self::$methods[$name])) {
            $method = self::$methods[$name];

            // Önceki filtreleri çalıştır
            if (isset(self::$filters['before'][$name])) {
                foreach (self::$filters['before'][$name] as $callback) {
                    $callback();
                }
            }

            // Metodu çalıştır
            $result = call_user_func_array($method['callback'], $params);

            // Sonraki filtreleri çalıştır
            if (isset(self::$filters['after'][$name])) {
                foreach (self::$filters['after'][$name] as $callback) {
                    $callback();
                }
            }

            return $result;
        }

        // Sınıf_metod formatını kontrol et
        $parts = explode('_', $name, 2);

        if (count($parts) > 1) {
            $class_name = $parts[0];
            $method_name = $parts[1];

            if (isset(self::$classes[$class_name])) {
                $class = self::$classes[$class_name];

                if (method_exists($class, $method_name)) {
                    return call_user_func_array([$class, $method_name], $params);
                }
            }
        }

        // Theme ve Lang için özel işleme
        if (strpos($name, 'theme_') === 0) {
            $method = substr($name, 6); // 'theme_' kısmını çıkar
            if (method_exists('Theme', $method)) {
                return call_user_func_array(['Theme', $method], $params);
            }
        }

        if (strpos($name, 'lang_') === 0) {
            $method = substr($name, 5); // 'lang_' kısmını çıkar
            if (method_exists('Lang', $method)) {
                return call_user_func_array(['Lang', $method], $params);
            }
        }

        // Doğrudan kayıtlı sınıflarda metod arama
        foreach (self::$classes as $class) {
            if (method_exists($class, $name)) {
                return call_user_func_array([$class, $name], $params);
            }
        }

        throw new Exception("Çağrılan metod bulunamadı: $name");
    }

    /**
     * HTTP başlığı ekler
     *
     * @param string $name Başlık adı
     * @param string $value Başlık değeri
     * @return Response
     */
    public static function header($name, $value)
    {
        return self::response()->header($name, $value);
    }

    /**
     * Birden çok HTTP başlığı ekler
     *
     * @param array $headers Başlıklar
     * @return Response
     */
    public static function headers(array $headers)
    {
        return self::response()->headers($headers);
    }

    /**
     * İçerik türünü ayarlar
     *
     * @param string $contentType İçerik türü
     * @return Response
     */
    public static function contentType($contentType)
    {
        return self::response()->contentType($contentType);
    }

    /**
     * Security sınıfını döndürür
     *
     * @return Security
     */
    public static function security()
    {
        // Security sınıfını yükle
        if (!class_exists('Security')) {
            require_once __DIR__ . '/Security.php';
        }
        return new Security();
    }

    /**
     * Logger sınıfını döndürür
     *
     * @return Logger
     */
    public static function logger()
    {
        // Logger sınıfını yükle
        if (!class_exists('Logger')) {
            require_once __DIR__ . '/Logger.php';
        }
        return new Logger();
    }

    /**
     * Events sınıfını döndürür
     *
     * @return Events
     */
    public static function events()
    {
        // Events sınıfını yükle
        if (!class_exists('Events')) {
            require_once __DIR__ . '/Events.php';
        }
        return new Events();
    }

    /**
     * Cryptor sınıfını döndürür
     *
     * @return Cryptor
     */
    public static function cryptor()
    {
        // Singleton pattern - her zaman aynı Cryptor örneğini döndür
        static $cryptor = null;

        if ($cryptor === null) {
            // Cryptor sınıfını yükle
            if (!class_exists('Cryptor')) {
                require_once __DIR__ . '/Cryptor.php';
            }
            $cryptor = new Cryptor();
        }

        return $cryptor;
    }

    /**
     * Cache sınıfını döndürür
     *
     * @param string|null $driver Sürücü adı
     * @return CacheInterface
     */
    public static function cache($driver = null)
    {
        // Cache sınıfını yükle
        if (!class_exists('Cache')) {
            require_once __DIR__ . '/Cache/Cache.php';
        }

        // Cryptor sınıfını ayarla (tutarlı anahtar kullanımı için)
        static $cryptor = null;
        if ($cryptor === null) {
            $cryptor = self::cryptor();
            Cache::setCryptor($cryptor);
        }

        return Cache::driver($driver);
    }

    /**
     * Database sınıfını döndürür
     *
     * @param array $config Veritabanı yapılandırması
     * @return \Sword\ORM\Database\Connection
     */
    public static function db($config = [])
    {
        // Singleton pattern - her zaman aynı Database örneğini döndür
        static $db = null;

        if ($db === null) {
            // ORM'in bağlantı sınıfını kullan
            if (!class_exists('\Sword\ORM\Database\Connection')) {
                require_once __DIR__ . '/ORM/Database/Connection.php';
            }

            // Bağlantı fabrikasını kullan
            if (!class_exists('\Sword\ORM\Database\ConnectionFactory')) {
                require_once __DIR__ . '/ORM/Database/ConnectionFactory.php';
            }

            try {
                $db = \Sword\ORM\Database\ConnectionFactory::make($config);
            } catch (\Exception $e) {
                // Eski Database sınıfını kullan (geriye dönük uyumluluk için)
                if (!class_exists('Database')) {
                    require_once __DIR__ . '/Database.php';
                }
                $db = new Database($config);
            }
        }

        return $db;
    }


    /**
     * Model sınıfını döndürür
     *
     * @param string $modelName Model adı
     * @return Model
     */
    public static function model($modelName)
    {
        // Model sınıfını yükle
        if (!class_exists('Model')) {
            require_once __DIR__ . '/Model.php';
        }

        // Model sınıfı adını oluştur
        if (strpos($modelName, 'Model') === false) {
            $modelName .= 'Model';
        }

        // Model sınıfını kontrol et
        if (!class_exists($modelName)) {
            // Uygulama dizininde model dosyasını ara
            $modelFile = defined('BASE_PATH') ? BASE_PATH . '/app/models/' . $modelName . '.php' : null;
            if ($modelFile && file_exists($modelFile)) {
                require_once $modelFile;
            } else {
                throw new Exception("Model sınıfı bulunamadı: {$modelName}");
            }
        }

        return new $modelName();
    }

    /**
     * Form verilerini doğrular
     *
     * @param array $data Doğrulanacak veriler
     * @param array $rules Doğrulama kuralları
     * @param array $messages Özel hata mesajları
     * @return Validation
     */
    public static function validate($data = [], $rules = [], $messages = [])
    {
        // Validation sınıfını yükle
        if (!class_exists('Validation')) {
            require_once __DIR__ . '/Validation.php';
        }

        $validation = new Validation($data);

        // Kuralları ekle
        if (!empty($rules)) {
            foreach ($rules as $field => $rule) {
                if (is_array($rule)) {
                    $label = $rule['label'] ?? $field;
                    $ruleStr = $rule['rules'] ?? '';
                } else {
                    $label = $field;
                    $ruleStr = $rule;
                }

                $validation->rule($field, $label, $ruleStr);
            }
        }

        // Özel mesajları ekle
        if (!empty($messages)) {
            foreach ($messages as $rule => $message) {
                $validation->setMessage($rule, $message);
            }
        }

        return $validation;
    }

    /**
     * Uygulamayı çalıştırır
     */
    public static function start()
    {
        // Gerekli dizinleri oluştur
        self::createDirectories();

        // Varsayılan yapılandırma değerlerini ayarla
        self::setDefaultConfigurations();

        // Events sınıfını yükle
        if (!class_exists('Events')) {
            require_once __DIR__ . '/Events.php';
        }

        // Pre-system olayını tetikle
        Events::triggerPreSystem();

        // Routes dosyasını yükle
        $routesFile = defined('BASE_PATH') ? BASE_PATH . '/app/Routes.php' : null;
        if ($routesFile && file_exists($routesFile)) {
            require_once $routesFile;
        }

        // Rotaları işle
        self::routerDispatch();
    }

    /**
     * Varsayılan yapılandırma değerlerini ayarlar
     */
    private static function setDefaultConfigurations()
    {
        // Uygulama temel bilgileri
        if (self::getData('app_name') === null) {
            self::setData('app_name', 'Sword Framework');
        }

        if (self::getData('version') === null) {
            self::setData('version', '1.0.0');
        }

        // Zaman dilimi ve dil ayarları
        if (self::getData('timezone') === null && class_exists('\\Sword\\Config\\Constants')) {
            self::setData('timezone', \Sword\Config\Constants::DEFAULT_TIMEZONE);
        }

        if (self::getData('locale') === null && class_exists('\\Sword\\Config\\Constants')) {
            self::setData('locale', \Sword\Config\Constants::DEFAULT_LOCALE);
        }

        // Önbellek ayarları
        if (self::getData('cache_driver') === null && class_exists('\\Sword\\Config\\Constants')) {
            self::setData('cache_driver', \Sword\Config\Constants::DEFAULT_CACHE_DRIVER);
        }

        if (self::getData('cache_ttl') === null && class_exists('\\Sword\\Config\\Constants')) {
            self::setData('cache_ttl', \Sword\Config\Constants::DEFAULT_CACHE_TTL);
        }

        // Oturum ayarları
        if (self::getData('session_lifetime') === null && class_exists('\\Sword\\Config\\Constants')) {
            self::setData('session_lifetime', \Sword\Config\Constants::DEFAULT_SESSION_LIFETIME);
        }

        // Session ayarları
        self::setData('session_name', 'SWORD_SESSION');

        // Çerez ayarları
        self::setData('cookie_path', '/');
        self::setData('cookie_domain', self::getCurrentDomain());
        self::setData('cookie_secure', isset($_SERVER['HTTPS']));
        self::setData('cookie_httponly', true);
        self::setData('cookie_samesite', 'Lax');

        if (self::getData('cookie_prefix') === null && class_exists('\\Sword\\Config\\Constants')) {
            self::setData('cookie_prefix', \Sword\Config\Constants::DEFAULT_COOKIE_PREFIX);
        }

        if (self::getData('cookie_path') === null && class_exists('\\Sword\\Config\\Constants')) {
            self::setData('cookie_path', \Sword\Config\Constants::DEFAULT_COOKIE_PATH);
        }

        if (self::getData('cookie_samesite') === null && class_exists('\\Sword\\Config\\Constants')) {
            self::setData('cookie_samesite', \Sword\Config\Constants::DEFAULT_COOKIE_SAMESITE);
        }

        // Görüntü işleme ayarları
        self::setData('upload_image_quality', 90);
        self::setData('upload_image_width', 1024);
        self::setData('upload_image_height', 1024);
        self::setData('upload_image_thumbnail_method', 'crop');

        // Filigran ayarları
        self::setData('upload_watermark_path', '');
        self::setData('upload_watermark_position', 'bottom-right');
        self::setData('upload_watermark_opacity', 50);

        // Küçük resim boyutları
        self::setData('upload_image_xs_width', 150);
        self::setData('upload_image_xs_height', 150);
        self::setData('upload_image_sm_width', 300);
        self::setData('upload_image_sm_height', 300);
        self::setData('upload_image_md_width', 600);
        self::setData('upload_image_md_height', 600);
        self::setData('upload_image_lg_width', 800);
        self::setData('upload_image_lg_height', 800);
    }

    /**
     * Gerekli dizinleri oluşturur
     */
    private static function createDirectories()
    {
        if (!defined('BASE_PATH')) {
            return;
        }

        // Ana dizinleri oluştur
        self::getPath('cache');
        self::getPath('views');
        self::getPath('logs');
        self::getPath('sessions');
        self::getPath('uploads');

        // Alt dizinleri oluştur
        $cachePath = self::getPath('cache');
        $viewsPath = $cachePath . DIRECTORY_SEPARATOR . 'views';
        $modelsPath = $cachePath . DIRECTORY_SEPARATOR . 'models';

        if (!is_dir($viewsPath)) {
            mkdir($viewsPath, 0755, true);
        }

        if (!is_dir($modelsPath)) {
            mkdir($modelsPath, 0755, true);
        }
    }

    /**
     * Kullanılabilir tüm metodları ve sınıfları listeler
     *
     * @return array Metodlar ve sınıflar
     */
    public static function getAvailableMethods()
    {
        $methods = [];

        // Özel metodlar
        foreach (array_keys(self::$methods) as $method) {
            $methods[] = "Sword::{$method}()";
        }

        // Statik metodlar
        $reflection = new ReflectionClass(__CLASS__);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_STATIC) as $method) {
            if (!$method->isConstructor() && !$method->isDestructor() && $method->getName() !== '__callStatic') {
                $methods[] = "Sword::{$method->getName()}()";
            }
        }

        // Router metodları
        $methods[] = "Sword::routerGet()";
        $methods[] = "Sword::routerPost()";
        $methods[] = "Sword::routerPut()";
        $methods[] = "Sword::routerDelete()";
        $methods[] = "Sword::routerAny()";
        $methods[] = "Sword::routerGroup()";
        $methods[] = "Sword::routerPlaceholder()";
        $methods[] = "Sword::routerPattern()";
        $methods[] = "Sword::routerNotFound()";
        $methods[] = "Sword::routerRoute()";
        $methods[] = "Sword::routerDispatch()";

        // Kayıtlı sınıflar
        foreach (array_keys(self::$classes) as $class) {
            $methods[] = "Sword::{$class}()";
        }

        sort($methods);
        return $methods;
    }

    /**
     * Bir olaya dinleyici ekler
     *
     * @param string $event Olay adı
     * @param callable $callback Geri çağırma fonksiyonu
     * @return void
     */
    public static function on($event, callable $callback)
    {
        return self::events()->on($event, $callback);
    }

    /**
     * Bir olayı tetikler
     *
     * @param string $event Olay adı
     * @param mixed ...$params Parametreler
     * @return mixed Sonuç
     */
    public static function trigger($event, ...$params)
    {
        return self::events()->trigger($event, ...$params);
    }

    /**
     * URL oluşturur
     *
     * @param string $path Yol
     * @param array $params Parametreler
     * @param bool $absolute Tam URL mi?
     * @return string URL
     */
    public static function url($path = '', array $params = [], $absolute = true)
    {
        // Başındaki / işaretini kaldır
        $path = ltrim($path, '/');

        // Temel URL'yi al
        $baseUrl = '';
        if ($absolute) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $protocol . '://' . $host;
        }

        // Temel yolu ekle
        $basePath = self::getBasePath();
        if (!empty($basePath)) {
            $baseUrl .= $basePath;
        }

        // Yolu ekle
        $url = $baseUrl . '/' . $path;

        // Parametreleri ekle
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Yönlendirme yapar
     *
     * @param string $url Yönlendirilecek URL veya rota adı
     * @param array $params Rota parametreleri (rota adı kullanılıyorsa)
     * @param int $statusCode Durum kodu
     * @return void
     */
    public static function redirect($url, $params = [], $statusCode = 302)
    {
        // URL bir rota adı mı?
        if (strpos($url, '/') === false && strpos($url, '://') === false) {
            try {
                $url = self::routerRoute($url, is_array($params) ? $params : []);
            } catch (Exception $e) {
                // Rota bulunamadıysa normal URL olarak kullan
                $url = self::url($url);
            }
        }
        // URL bir path mi?
        else if (strpos($url, '://') === false) {
            $url = self::url($url, is_array($params) ? $params : []);
        }

        // Yönlendirme başlığını ayarla
        header('Location: ' . $url, true, $statusCode);
        exit;
    }

    /**
     * Önceki sayfaya yönlendirir
     *
     * @param string $fallback Geri dönüş URL'si
     * @param int $statusCode Durum kodu
     * @return void
     */
    public static function back($fallback = '/', $statusCode = 302)
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        if ($referer) {
            self::redirect($referer, $statusCode);
        } else {
            self::redirect($fallback, $statusCode);
        }
    }

    /**
     * Yardım bilgisini gösterir
     *
     * @return string Yardım bilgisi
     */
    public static function help()
    {
        $methods = self::getAvailableMethods();

        $help = "Sword Framework Yardım\n\n";
        $help .= "Kullanılabilir metodlar:\n";

        foreach ($methods as $method) {
            $help .= "- {$method}\n";
        }

        $help .= "\nYapılandırma değerleri:\n";
        foreach (self::getDatas() as $key => $value) {
            if (is_scalar($value) || is_null($value)) {
                $help .= "- {$key}: " . var_export($value, true) . "\n";
            } else {
                $help .= "- {$key}: " . gettype($value) . "\n";
            }
        }

        return $help;
    }

    /**
     * Helper dosyalarını yükler
     *
     * @return void
     */
    private static function loadHelpers()
    {
        $helperDir = __DIR__ . '/Helpers';

        if (is_dir($helperDir)) {
            $helpers = ['utility.php', 'string.php', 'response.php', 'view.php'];

            foreach ($helpers as $helper) {
                $helperFile = $helperDir . '/' . $helper;
                if (file_exists($helperFile)) {
                    require_once $helperFile;
                }
            }
        }
    }

    /**
     * Decorator'ları yükler
     *
     * @return void
     */
    private static function loadDecorators()
    {
        if (class_exists('\\Sword\\View\\Decorator')) {
            \Sword\View\Decorator::registerCommon();
        }
    }

    /**
     * Upload sınıfını döndürür veya dosya yükler
     *
     * @param array|null $file Yüklenecek dosya ($_FILES dizisinden)
     * @param string|null $customName Özel dosya adı
     * @param string|null $subDir Alt dizin
     * @return Upload|array|bool Upload sınıfı veya yükleme sonucu
     */
    public static function upload($file = null, $customName = null, $subDir = null)
    {
        // Upload sınıfını yükle
        if (!class_exists('Upload')) {
            require_once __DIR__ . '/Upload.php';
        }

        $upload = new Upload();

        // Dosya belirtilmişse yükle
        if ($file !== null) {
            return $upload->upload($file, $customName, $subDir);
        }

        return $upload;
    }

    /**
     * Çoklu dosya yükler
     *
     * @param array $files Yüklenecek dosyalar ($_FILES dizisinden)
     * @param string|null $subDir Alt dizin
     * @return array Yükleme sonuçları
     */
    public static function uploadMultiple($files, $subDir = null)
    {
        // Upload sınıfını yükle
        if (!class_exists('Upload')) {
            require_once __DIR__ . '/Upload.php';
        }

        $upload = new Upload();
        return $upload->uploadMultiple($files, $subDir);
    }

    /**
     * Image sınıfını döndürür
     *
     * @param string|null $path Görüntü dosya yolu
     * @return Image
     */
    public static function image($path = null)
    {
        // Image sınıfını yükle
        if (!class_exists('Image')) {
            require_once __DIR__ . '/Image.php';
        }

        $image = new Image($path);

        // Varsayılan ayarları uygula
        if ($path !== null) {
            $quality = self::getData('upload_image_quality', 90);
            $maxWidth = self::getData('upload_image_width', 1024);
            $maxHeight = self::getData('upload_image_height', 1024);

            // Kaliteyi ayarla
            $image->setQuality($quality);

            // Görüntü boyutlarını kontrol et ve gerekirse yeniden boyutlandır
            if ($image->getWidth() > $maxWidth || $image->getHeight() > $maxHeight) {
                $image->resize($maxWidth, $maxHeight, true);
            }

            // Filigran ekle
            $watermarkPath = self::getData('upload_watermark_path');
            if (!empty($watermarkPath) && file_exists($watermarkPath)) {
                $position = self::getData('upload_watermark_position', 'bottom-right');
                $opacity = self::getData('upload_watermark_opacity', 50);
                $image->watermark($watermarkPath, $position, $opacity);
            }
        }

        return $image;
    }

    /**
     * View verilerini döndürür
     *
     * @param string|null $key Anahtar
     * @param mixed $default Varsayılan değer
     * @return mixed View verileri
     */
    public static function raw($key = null, $default = null)
    {
        static $viewData = [];

        // View sınıfını kontrol et
        if (class_exists('View') && method_exists('View', 'getData')) {
            $viewData = View::getData();
        }

        // Belirli bir anahtar istendiyse
        if ($key !== null) {
            return isset($viewData[$key]) ? $viewData[$key] : $default;
        }

        return $viewData;
    }

    /**
     * Thumbnails sınıfını döndürür ve küçük resimler oluşturur
     *
     * @param string|null $imagePath Görüntü dosya yolu
     * @param array $sizes Oluşturulacak boyutlar (xs, sm, md, lg)
     * @return array|Thumbnails Küçük resim yolları veya Thumbnails sınıfı
     */
    public static function thumbnails($imagePath = null, $sizes = ['xs', 'sm', 'md', 'lg'])
    {
        // Thumbnails sınıfını yükle
        if (!class_exists('Thumbnails')) {
            require_once __DIR__ . '/Thumbnails.php';
        }

        $thumbnails = new Thumbnails();

        // Görüntü yolu belirtilmişse küçük resimler oluştur
        if ($imagePath !== null) {
            return $thumbnails->generate($imagePath, $sizes);
        }

        return $thumbnails;
    }

    /**
     * Config sınıfını döndürür
     *
     * @param string|null $key Yapılandırma anahtarı
     * @param mixed $default Varsayılan değer
     * @return mixed Config sınıfı veya değer
     */
    public static function config($key = null, $default = null)
    {
        // Config sınıfını yükle
        if (!class_exists('\\Sword\\Config\\Config')) {
            require_once __DIR__ . '/Config/Config.php';
        }

        if ($key === null) {
            return '\\Sword\\Config\\Config';
        }

        return \Sword\Config\Config::get($key, $default);
    }

    /**
     * Session işlemlerini yönetir
     *
     * @param string|null $key Anahtar
     * @param mixed $value Değer (set işlemi için)
     * @return mixed Session sınıfı veya değer
     */
    public static function session($key = null, $value = null)
    {
        // Session sınıfını yükle
        if (!class_exists('Session')) {
            require_once __DIR__ . '/Session.php';
        }

        if ($key === null) {
            return 'Session';
        }

        if ($value !== null) {
            return Session::set($key, $value);
        }

        return Session::get($key);
    }

    /**
     * Theme sınıfını döndürür
     *
     * @return Theme
     */
    public static function theme()
    {
        // Theme sınıfını yükle
        if (!class_exists('Theme')) {
            require_once __DIR__ . '/Theme.php';
        }
        return new Theme();
    }

    /**
     * Lang sınıfını döndürür
     *
     * @return Lang
     */
    public static function lang()
    {
        // Lang sınıfını yükle
        if (!class_exists('Lang')) {
            require_once __DIR__ . '/Lang.php';
        }
        return new Lang();
    }

    /**
     * Shortcode ekler
     *
     * @param string $tag Shortcode etiketi
     * @param callable $callback Callback fonksiyonu
     * @return void
     */
    public static function shortcode($tag, $callback)
    {
        // Shortcode sınıfını yükle
        if (!class_exists('Shortcode')) {
            require_once __DIR__ . '/Shortcode.php';
        }
        Shortcode::add($tag, $callback);
    }

    /**
     * Shortcode'u kaldırır
     *
     * @param string $tag Shortcode etiketi
     * @return void
     */
    public static function removeShortcode($tag)
    {
        if (class_exists('Shortcode')) {
            Shortcode::remove($tag);
        }
    }

    /**
     * Mailer sınıfını döndürür
     *
     * @return Mailer
     */
    public static function mailer()
    {
        // Mailer sınıfını yükle
        if (!class_exists('Mailer')) {
            require_once __DIR__ . '/Mailer.php';
        }
        return new Mailer();
    }

    /**
     * Validation sınıfını döndürür
     *
     * @param array $data Doğrulanacak veriler
     * @return Validation
     */
    public static function validation($data = [])
    {
        if (!class_exists('Validation')) {
            require_once __DIR__ . '/Validation.php';
        }
        return new Validation($data);
    }

    /**
     * Throttle sınıfını döndürür
     *
     * @return Throttle
     */
    public static function throttle()
    {
        if (!class_exists('Throttle')) {
            require_once __DIR__ . '/Throttle.php';
        }
        return new Throttle();
    }

    /**
     * Permalink sınıfını döndürür
     *
     * @return Permalink
     */
    public static function permalink()
    {
        if (!class_exists('Permalink')) {
            require_once __DIR__ . '/Permalink.php';
        }
        return new Permalink();
    }

    /**
     * QueryBuilder sınıfını döndürür
     *
     * @return QueryBuilder
     */
    public static function query()
    {
        if (!class_exists('QueryBuilder')) {
            require_once __DIR__ . '/QueryBuilder.php';
        }
        return new QueryBuilder();
    }

    /**
     * MemoryManager sınıfını döndürür
     *
     * @return string
     */
    public static function memory()
    {
        return 'MemoryManager';
    }

    /**
     * Cookie işlemlerini yönetir
     *
     * @param string|null $name Cookie adı
     * @param mixed $value Cookie değeri (set işlemi için)
     * @param int $expire Geçerlilik süresi
     * @param array $options Ek seçenekler
     * @return mixed Cookie sınıfı veya değer
     */
    public static function cookie($name = null, $value = null, $expire = 0, array $options = [])
    {
        // Cookie sınıfını yükle
        if (!class_exists('Cookie')) {
            require_once __DIR__ . '/Cookie.php';
        }

        if ($name === null) {
            return 'Cookie';
        }

        if ($value !== null) {
            return Cookie::set($name, $value, $expire, $options);
        }

        return Cookie::get($name);
    }

    /**
     * DbTabler sistemini döndürür
     *
     * @return DbTabler
     */
    public static function dbTable()
    {
        // DbTabler sınıfını yükle
        if (!class_exists('DbTabler')) {
            require_once __DIR__ . '/DbTabler.php';
        }
        return 'DbTabler';
    }
}
