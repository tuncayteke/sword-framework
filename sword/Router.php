<?php

/**
 * Sword Framework - Geliştirilmiş Router
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Router sınıfı - URL yönlendirme işlemlerini yönetir
 * 
 * Yeni Özellikler:
 * - Route caching sistemi (performans optimizasyonu)
 * - Gelişmiş middleware pipeline
 * - Route model binding
 * - Subdomain routing
 * - Rate limiting
 * - CORS desteği
 * - API versioning
 * - Gelişmiş hata yönetimi
 */

class Router
{
    /**
     * Kayıtlı rotalar
     */
    private $routes = [];

    /**
     * Statik rotalar (cache için)
     */
    private $staticRoutes = [];

    /**
     * Dinamik rotalar (parametreli)
     */
    private $dynamicRoutes = [];

    /**
     * İsimlendirilmiş rotalar
     */
    private $namedRoutes = [];

    /**
     * Placeholder değerleri
     */
    private $placeholders = [];

    /**
     * Önceden tanımlanmış placeholder desenleri
     */
    private $patterns = [
        ':num' => '[0-9]+',
        ':alpha' => '[a-zA-Z]+',
        ':alphanum' => '[a-zA-Z0-9]+',
        ':any' => '[^/]+',
        ':segment' => '[^/]+',
        ':all' => '.*',
        ':year' => '[12][0-9]{3}',
        ':month' => '0[1-9]|1[0-2]',
        ':day' => '0[1-9]|[12][0-9]|3[01]',
        ':id' => '[1-9][0-9]*',
        ':slug' => '[a-z0-9-]+',
        ':uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'
    ];

    /**
     * Middleware'ler
     */
    private $middlewares = [];

    /**
     * Global middleware'ler (her route için)
     */
    private $globalMiddlewares = [];

    /**
     * Middleware priority sıralaması
     */
    private $middlewarePriority = [];

    /**
     * Geçerli grup öneki
     */
    private $groupPrefix = '';

    /**
     * Geçerli grup middleware'leri
     */
    private $groupMiddlewares = [];

    /**
     * Geçerli grup namespace'i
     */
    private $groupNamespace = '';

    /**
     * Geçerli grup name prefix'i
     */
    private $groupNamePrefix = '';

    /**
     * 404 hata işleyicisi
     */
    private $notFoundHandler = null;

    /**
     * Rota parametreleri
     */
    private $params = [];

    /**
     * Eşleşen rota
     */
    private $matchedRoute = null;

    /**
     * Cache dizini
     */
    private $cacheDir = 'cache';

    /**
     * Cache dosya adı
     */
    private $cacheFile = 'routes.php';

    /**
     * Cache aktif mi?
     */
    private $cacheEnabled = false;

    /**
     * Model binding callbacks
     */
    private $modelBindings = [];

    /**
     * Subdomain patterns
     */
    private $subdomainPattern = null;

    /**
     * CORS ayarları
     */
    private $corsEnabled = false;
    private $corsOptions = [
        'origins' => ['*'],
        'methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        'headers' => ['*'],
        'credentials' => false,
        'max_age' => 86400
    ];

    /**
     * Rate limiting
     */
    private $rateLimits = [];

    /**
     * Error handlers
     */
    private $errorHandlers = [];

    /**
     * Constructor
     */
    public function __construct($cacheDir = 'cache')
    {
        $this->cacheDir = $cacheDir;
        
        // Cache dizinini oluştur
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Cache'i aktifleştirir
     */
    public function enableCache($enabled = true)
    {
        $this->cacheEnabled = $enabled;
        return $this;
    }

    /**
     * Route'ları cache'e yazar
     */
    public function cacheRoutes()
    {
        if (!$this->cacheEnabled) {
            return false;
        }

        $cacheData = [
            'static' => $this->staticRoutes,
            'dynamic' => $this->dynamicRoutes,
            'named' => $this->namedRoutes,
            'timestamp' => time()
        ];

        $cacheContent = '<?php return ' . var_export($cacheData, true) . ';';
        $cachePath = $this->cacheDir . '/' . $this->cacheFile;

        return file_put_contents($cachePath, $cacheContent) !== false;
    }

    /**
     * Cache'den route'ları yükler
     */
    public function loadCachedRoutes()
    {
        if (!$this->cacheEnabled) {
            return false;
        }

        $cachePath = $this->cacheDir . '/' . $this->cacheFile;
        
        if (!file_exists($cachePath)) {
            return false;
        }

        $cacheData = include $cachePath;
        
        if (!is_array($cacheData)) {
            return false;
        }

        $this->staticRoutes = $cacheData['static'] ?? [];
        $this->dynamicRoutes = $cacheData['dynamic'] ?? [];
        $this->namedRoutes = $cacheData['named'] ?? [];

        return true;
    }

    /**
     * Cache'i temizler
     */
    public function clearCache()
    {
        $cachePath = $this->cacheDir . '/' . $this->cacheFile;
        
        if (file_exists($cachePath)) {
            return unlink($cachePath);
        }

        return true;
    }

    /**
     * Global middleware ekler
     */
    public function addGlobalMiddleware($middleware, $priority = 50)
    {
        $this->globalMiddlewares[] = $middleware;
        $this->middlewarePriority[$middleware] = $priority;
        return $this;
    }

    /**
     * Model binding ekler
     */
    public function bind($key, $callback)
    {
        $this->modelBindings[$key] = $callback;
        return $this;
    }

    /**
     * CORS'u aktifleştirir
     */
    public function enableCors($options = [])
    {
        $this->corsEnabled = true;
        $this->corsOptions = array_merge($this->corsOptions, $options);
        return $this;
    }

    /**
     * Rate limit ekler
     */
    public function rateLimit($pattern, $maxRequests, $perMinutes = 1)
    {
        $this->rateLimits[$pattern] = [
            'max' => $maxRequests,
            'minutes' => $perMinutes
        ];
        return $this;
    }

    /**
     * Subdomain pattern ayarlar
     */
    public function subdomain($pattern)
    {
        $this->subdomainPattern = $pattern;
        return $this;
    }

    /**
     * GET isteği için rota ekler
     */
    public function get($pattern, $callback, $name = null)
    {
        return $this->addRoute('GET', $this->normalizePattern($pattern), $callback, $name);
    }

    /**
     * POST isteği için rota ekler
     */
    public function post($pattern, $callback, $name = null)
    {
        return $this->addRoute('POST', $this->normalizePattern($pattern), $callback, $name);
    }

    /**
     * PUT isteği için rota ekler
     */
    public function put($pattern, $callback, $name = null)
    {
        return $this->addRoute('PUT', $this->normalizePattern($pattern), $callback, $name);
    }

    /**
     * DELETE isteği için rota ekler
     */
    public function delete($pattern, $callback, $name = null)
    {
        return $this->addRoute('DELETE', $this->normalizePattern($pattern), $callback, $name);
    }

    /**
     * PATCH isteği için rota ekler
     */
    public function patch($pattern, $callback, $name = null)
    {
        return $this->addRoute('PATCH', $this->normalizePattern($pattern), $callback, $name);
    }

    /**
     * OPTIONS isteği için rota ekler
     */
    public function options($pattern, $callback, $name = null)
    {
        return $this->addRoute('OPTIONS', $this->normalizePattern($pattern), $callback, $name);
    }

    /**
     * Tüm HTTP metodları için rota ekler
     */
    public function any($pattern, $callback, $name = null)
    {
        $pattern = $this->normalizePattern($pattern);
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'];
        
        foreach ($methods as $method) {
            $this->addRoute($method, $pattern, $callback, $name);
        }

        return $this;
    }

    /**
     * Belirtilen HTTP metodları için rota ekler
     */
    public function match(array $methods, $pattern, $callback, $name = null)
    {
        $pattern = $this->normalizePattern($pattern);

        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $pattern, $callback, $name);
        }

        return $this;
    }

    /**
     * RESTful resource rotaları oluşturur
     */
    public function resource($uri, $controller, $name = null)
    {
        $uri = $this->normalizePattern($uri);
        
        // Group prefix'i dikkate al
        $resourceName = $name ?: $this->groupNamePrefix . str_replace('/', '.', trim($uri, '/'));

        $routes = [
            ['GET', $uri, 'index', '.index'],
            ['GET', $uri . '/create', 'create', '.create'],
            ['POST', $uri, 'store', '.store'],
            ['GET', $uri . '/:id', 'show', '.show'],
            ['GET', $uri . '/:id/edit', 'edit', '.edit'],
            ['PUT', $uri . '/:id', 'update', '.update'],
            ['PATCH', $uri . '/:id', 'update', '.patch'],
            ['DELETE', $uri . '/:id', 'destroy', '.destroy']
        ];

        foreach ($routes as $route) {
            $this->addRoute(
                $route[0],
                $route[1],
                $this->groupNamespace . $controller . '@' . $route[2],
                $resourceName . $route[3]
            );
        }

        return $this;
    }

    /**
     * API resource (create ve edit yok)
     */
    public function apiResource($uri, $controller, $name = null)
    {
        $uri = $this->normalizePattern($uri);
        $resourceName = $name ?: $this->groupNamePrefix . str_replace('/', '.', trim($uri, '/'));

        $routes = [
            ['GET', $uri, 'index', '.index'],
            ['POST', $uri, 'store', '.store'],
            ['GET', $uri . '/:id', 'show', '.show'],
            ['PUT', $uri . '/:id', 'update', '.update'],
            ['PATCH', $uri . '/:id', 'update', '.patch'],
            ['DELETE', $uri . '/:id', 'destroy', '.destroy']
        ];

        foreach ($routes as $route) {
            $this->addRoute(
                $route[0],
                $route[1],
                $this->groupNamespace . $controller . '@' . $route[2],
                $resourceName . $route[3]
            );
        }

        return $this;
    }

    /**
     * Rota grubu oluşturur (geliştirilmiş)
     */
    public function group($attributes, $callback)
    {
        // String ise sadece prefix
        if (is_string($attributes)) {
            $attributes = ['prefix' => $attributes];
        }

        $previousGroupPrefix = $this->groupPrefix;
        $previousGroupMiddlewares = $this->groupMiddlewares;
        $previousGroupNamespace = $this->groupNamespace;
        $previousGroupNamePrefix = $this->groupNamePrefix;

        // Prefix
        if (isset($attributes['prefix'])) {
            $prefix = $this->normalizePattern($attributes['prefix']);
            $this->groupPrefix = $previousGroupPrefix . $prefix;
        }

        // Namespace
        if (isset($attributes['namespace'])) {
            $namespace = trim($attributes['namespace'], '\\') . '\\';
            $this->groupNamespace = $previousGroupNamespace . $namespace;
        }

        // Name prefix
        if (isset($attributes['name'])) {
            $this->groupNamePrefix = $previousGroupNamePrefix . $attributes['name'];
        }

        // Middleware
        if (isset($attributes['middleware'])) {
            $middleware = is_array($attributes['middleware']) 
                ? $attributes['middleware'] 
                : [$attributes['middleware']];
            
            $this->groupMiddlewares = array_merge($previousGroupMiddlewares, $middleware);
        } else {
            $this->groupMiddlewares = array_merge($previousGroupMiddlewares, $this->middlewares);
        }

        call_user_func($callback, $this);

        // Restore
        $this->groupPrefix = $previousGroupPrefix;
        $this->groupMiddlewares = $previousGroupMiddlewares;
        $this->groupNamespace = $previousGroupNamespace;
        $this->groupNamePrefix = $previousGroupNamePrefix;
        $this->middlewares = [];

        return $this;
    }

    /**
     * Middleware ekler
     */
    public function middleware($middleware)
    {
        if (is_array($middleware)) {
            $this->middlewares = array_merge($this->middlewares, $middleware);
        } else {
            $this->middlewares[] = $middleware;
        }
        return $this;
    }

    /**
     * Placeholder değeri ekler
     */
    public function placeholder($key, $value)
    {
        $this->placeholders[$key] = $value;
        return $this;
    }

    /**
     * Özel bir placeholder deseni ekler
     */
    public function pattern($name, $pattern)
    {
        // Eğer : ile başlamıyorsa ekle
        if (substr($name, 0, 1) !== ':') {
            $name = ':' . $name;
        }
        $this->patterns[$name] = $pattern;
        return $this;
    }

    /**
     * 404 hata işleyicisini ayarlar
     */
    public function notFound(callable $handler)
    {
        $this->notFoundHandler = $handler;
        return $this;
    }

    /**
     * Özel error handler ekler
     */
    public function errorHandler($code, callable $handler)
    {
        $this->errorHandlers[$code] = $handler;
        return $this;
    }

    /**
     * İsimlendirilmiş rotaya URL oluşturur
     */
    public function route($name, array $params = [])
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new Exception("Named route not found: $name");
        }

        $url = $this->namedRoutes[$name];

        // Pattern placeholders'ı geçici olarak değiştir
        $tempReplacements = [];
        foreach ($this->patterns as $placeholder => $regex) {
            if (strpos($url, $placeholder) !== false) {
                $paramName = ltrim($placeholder, ':');
                $tempKey = '___TEMP_' . $paramName . '___';
                $url = str_replace($placeholder, $tempKey, $url);
                $tempReplacements[$tempKey] = $paramName;
            }
        }

        // Parametreleri yerleştir
        foreach ($params as $key => $value) {
            $url = str_replace(':' . $key, $value, $url);
        }

        // Geçici replacements'ı geri al
        foreach ($tempReplacements as $tempKey => $paramName) {
            if (isset($params[$paramName])) {
                $url = str_replace($tempKey, $params[$paramName], $url);
            } else {
                $url = str_replace($tempKey, '', $url);
            }
        }

        // Placeholder değerlerini uygula
        foreach ($this->placeholders as $key => $value) {
            $url = str_replace(':' . $key, $value, $url);
        }

        // Çift slash'ları temizle
        $url = preg_replace('#/+#', '/', $url);

        return $url;
    }

    /**
     * Pattern'i normalize eder
     */
    private function normalizePattern($pattern)
    {
        // Boş pattern
        if ($pattern === '' || $pattern === null) {
            return '/';
        }

        // Zaten / ise
        if ($pattern === '/') {
            return '/';
        }

        // Başında / yoksa ekle
        if (substr($pattern, 0, 1) !== '/') {
            $pattern = '/' . $pattern;
        }

        // Çift slash'ları temizle
        $pattern = preg_replace('#/+#', '/', $pattern);

        // Trailing slash kaldır (/ hariç)
        if ($pattern !== '/') {
            $pattern = rtrim($pattern, '/');
        }

        return $pattern;
    }

    /**
     * Rota ekler (geliştirilmiş)
     */
    public function addRoute($method, $pattern, $callback, $name = null)
    {
        $originalPattern = $pattern;

        // Placeholder değerlerini uygula
        foreach ($this->placeholders as $key => $value) {
            $pattern = str_replace(':' . $key, $value, $pattern);
        }

        // Group prefix ekle
        $pattern = $this->groupPrefix . $pattern;
        $pattern = $this->normalizePattern($pattern);

        // Middleware'leri birleştir
        $middlewares = array_merge(
            $this->globalMiddlewares,
            $this->groupMiddlewares,
            $this->middlewares
        );

        // Middleware priority'ye göre sırala
        if (!empty($middlewares) && !empty($this->middlewarePriority)) {
            usort($middlewares, function($a, $b) {
                $priorityA = $this->middlewarePriority[$a] ?? 50;
                $priorityB = $this->middlewarePriority[$b] ?? 50;
                return $priorityA - $priorityB;
            });
        }

        // Regex pattern oluştur
        $regexPattern = $pattern;

        // Önceden tanımlanmış desenleri işle
        foreach ($this->patterns as $placeholder => $regex) {
            if (strpos($regexPattern, $placeholder) !== false) {
                $paramName = ltrim($placeholder, ':');
                $regexPattern = str_replace($placeholder, "(?P<{$paramName}>{$regex})", $regexPattern);
            }
        }

        // Basit parametreleri işle (:param)
        $regexPattern = preg_replace('/:(\w+)/', '(?P<$1>[^/]+)', $regexPattern);

        // Regex'i tamamla
        $regex = '#^' . str_replace('/', '\/', $regexPattern) . '$#u';

        $routeData = [
            'method' => $method,
            'pattern' => $pattern,
            'regex' => $regex,
            'callback' => $callback,
            'middlewares' => $middlewares,
            'namespace' => $this->groupNamespace,
            'subdomain' => $this->subdomainPattern
        ];

        // Statik vs dinamik ayrımı (cache için)
        if (strpos($pattern, ':') === false && strpos($pattern, '(') === false) {
            // Statik route
            $key = $method . ':' . $pattern;
            $this->staticRoutes[$key] = $routeData;
        } else {
            // Dinamik route
            $this->dynamicRoutes[] = $routeData;
        }

        // Tüm route'lara da ekle
        $this->routes[] = $routeData;

        // Named route (collision check)
        if ($name) {
            $fullName = $this->groupNamePrefix . $name;
            
            if (isset($this->namedRoutes[$fullName])) {
                trigger_error("Route name collision: '$fullName' is already defined", E_USER_WARNING);
            }
            
            $this->namedRoutes[$fullName] = $originalPattern;
        }

        // Middleware'leri temizle
        $this->middlewares = [];

        return $this;
    }

    /**
     * İstekleri işler (optimized)
     */
    public function dispatch($method = null, $uri = null)
    {
        $method = $method ?: $_SERVER['REQUEST_METHOD'];
        $uri = $uri ?: parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = $this->normalizePattern($uri);

        // CORS kontrolü
        if ($this->corsEnabled) {
            $this->handleCors($method);
        }

        // OPTIONS request için (CORS preflight)
        if ($method === 'OPTIONS' && $this->corsEnabled) {
            http_response_code(200);
            return true;
        }

        // HTTP Method spoofing
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        // Rate limiting kontrolü
        if (!$this->checkRateLimit($uri)) {
            return $this->handleError(429, 'Too Many Requests');
        }

        try {
            // Önce statik route'ları kontrol et (O(1))
            $staticKey = $method . ':' . $uri;
            if (isset($this->staticRoutes[$staticKey])) {
                return $this->handleRoute($this->staticRoutes[$staticKey], []);
            }

            // Dinamik route'ları kontrol et
            foreach ($this->dynamicRoutes as $route) {
                if ($route['method'] !== $method) {
                    continue;
                }

                // Subdomain kontrolü
                if ($route['subdomain'] && !$this->matchSubdomain($route['subdomain'])) {
                    continue;
                }

                // Regex match
                if (preg_match($route['regex'], $uri, $matches)) {
                    $params = $this->extractParams($matches);
                    return $this->handleRoute($route, $params);
                }
            }

            // Route bulunamadı
            return $this->handleNotFound();
            
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * CORS headers ekler
     */
    private function handleCors($method)
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        
        if ($this->corsOptions['origins'] !== ['*']) {
            if (!in_array($origin, $this->corsOptions['origins'])) {
                $origin = $this->corsOptions['origins'][0];
            }
        }

        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: ' . implode(', ', $this->corsOptions['methods']));
        
        if ($this->corsOptions['headers'] === ['*']) {
            $headers = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? '*';
            header('Access-Control-Allow-Headers: ' . $headers);
        } else {
            header('Access-Control-Allow-Headers: ' . implode(', ', $this->corsOptions['headers']));
        }

        if ($this->corsOptions['credentials']) {
            header('Access-Control-Allow-Credentials: true');
        }

        header('Access-Control-Max-Age: ' . $this->corsOptions['max_age']);
    }

    /**
     * Subdomain match kontrolü
     */
    private function matchSubdomain($pattern)
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $subdomain = explode('.', $host)[0];
        
        return preg_match('#^' . $pattern . '$#', $subdomain);
    }

    /**
     * Rate limit kontrolü
     */
    private function checkRateLimit($uri)
    {
        if (empty($this->rateLimits)) {
            return true;
        }

        $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        foreach ($this->rateLimits as $pattern => $limit) {
            if (preg_match('#^' . $pattern . '$#', $uri)) {
                $key = 'rate_limit:' . $pattern . ':' . $clientIp;
                $cacheFile = $this->cacheDir . '/' . md5($key) . '.txt';
                
                $requests = [];
                if (file_exists($cacheFile)) {
                    $requests = json_decode(file_get_contents($cacheFile), true) ?: [];
                }
                
                $now = time();
                $window = $now - ($limit['minutes'] * 60);
                
                // Eski kayıtları temizle
                $requests = array_filter($requests, fn($t) => $t > $window);
                
                // Limit kontrolü
                if (count($requests) >= $limit['max']) {
                    return false;
                }
                
                // Yeni request ekle
                $requests[] = $now;
                file_put_contents($cacheFile, json_encode($requests));
                
                return true;
            }
        }
        
        return true;
    }

    /**
     * Parametreleri extract eder
     */
    private function extractParams($matches)
    {
        $params = [];
        foreach ($matches as $key => $value) {
            if (!is_numeric($key)) {
                $params[$key] = $value;
            }
        }
        return $params;
    }

    /**
     * Route'u işler
     */
    private function handleRoute($route, $params)
    {
        // Placeholder değerlerini uygula
        foreach ($this->placeholders as $key => $value) {
            if (isset($params[$key])) {
                $params[$key] = $value;
            }
        }

        // Model binding
        $params = $this->applyModelBinding($params);

        // Route bilgilerini kaydet
        $this->params = $params;
        $this->matchedRoute = $route;

        // Middleware pipeline
        $response = $this->runMiddlewarePipeline($route, $params, function($params) use ($route) {
            return $this->executeCallback($route['callback'], $params, $route['namespace']);
        });

        // Response echo
        if ($response !== null && $response !== false) {
            echo $response;
        }

        return $response;
    }

    /**
     * Model binding uygular
     */
    private function applyModelBinding($params)
    {
        foreach ($params as $key => $value) {
            if (isset($this->modelBindings[$key])) {
                $params[$key] = call_user_func($this->modelBindings[$key], $value);
            }
        }
        return $params;
    }

    /**
     * Middleware pipeline (Laravel style)
     */
    private function runMiddlewarePipeline($route, $params, $final)
    {
        $middlewares = $route['middlewares'];
        
        if (empty($middlewares)) {
            return $final($params);
        }

        $pipeline = array_reduce(
            array_reverse($middlewares),
            function($next, $middleware) use ($params) {
                return function() use ($middleware, $next, $params) {
                    return $this->executeMiddleware($middleware, $params, $next);
                };
            },
            $final
        );

        return $pipeline();
    }

    /**
     * Middleware'i çalıştırır
     */
    private function executeMiddleware($middleware, $params, $next)
    {
        if (is_callable($middleware)) {
            return call_user_func($middleware, $params, $next);
        }
        
        if (is_string($middleware) && class_exists($middleware)) {
            $middlewareObj = new $middleware();
            
            if (method_exists($middlewareObj, 'handle')) {
                return $middlewareObj->handle($params, $next);
            }
        }

        return $next($params);
    }

    /**
     * Callback'i çalıştırır
     */
    private function executeCallback($callback, $params, $namespace = '')
    {
        if (is_callable($callback)) {
            return $this->executeCallableCallback($callback, $params);
        }
        
        if (is_string($callback) && strpos($callback, '@') !== false) {
            return $this->executeControllerCallback($callback, $params, $namespace);
        }
        
        if (is_string($callback) && strpos($callback, '::') !== false) {
            return $this->executeStaticCallback($callback, $params);
        }

        throw new Exception("Invalid callback type");
    }

    /**
     * Callable callback çalıştırır
     */
    private function executeCallableCallback($callback, $params)
    {
        if (is_array($callback)) {
            $reflection = new ReflectionMethod($callback[0], $callback[1]);
        } else {
            $reflection = new ReflectionFunction($callback);
        }

        $callbackParams = $this->prepareCallbackParameters($reflection, $params);
        return call_user_func_array($callback, $callbackParams);
    }

    /**
     * Controller callback çalıştırır
     */
    private function executeControllerCallback($callback, $params, $namespace = '')
    {
        list($controller, $method) = explode('@', $callback);
        
        // Namespace ekle
        $controller = $namespace . $controller;

        if (!class_exists($controller)) {
            throw new Exception("Controller not found: $controller");
        }

        try {
            $controllerObj = new $controller();
        } catch (Exception $e) {
            throw new Exception("Failed to instantiate controller: $controller - " . $e->getMessage());
        }

        if (!method_exists($controllerObj, $method)) {
            throw new Exception("Method not found: $controller::$method");
        }

        $reflection = new ReflectionMethod($controllerObj, $method);
        $callbackParams = $this->prepareCallbackParameters($reflection, $params);
        
        return call_user_func_array([$controllerObj, $method], $callbackParams);
    }

    /**
     * Static callback çalıştırır
     */
    private function executeStaticCallback($callback, $params)
    {
        list($class, $method) = explode('::', $callback);

        if (!class_exists($class)) {
            throw new Exception("Class not found: $class");
        }

        if (!method_exists($class, $method)) {
            throw new Exception("Static method not found: $class::$method");
        }

        $reflection = new ReflectionMethod($class, $method);
        $callbackParams = $this->prepareCallbackParameters($reflection, $params);
        
        return call_user_func_array([$class, $method], $callbackParams);
    }

    /**
     * Callback parametrelerini hazırlar
     */
    private function prepareCallbackParameters(ReflectionFunctionAbstract $reflection, $params)
    {
        $parameters = $reflection->getParameters();
        $callbackParams = [];

        foreach ($parameters as $i => $parameter) {
            $paramName = $parameter->getName();

            // Named parameter
            if (isset($params[$paramName])) {
                $callbackParams[$i] = $params[$paramName];
            }
            // Positional parameter
            elseif (isset(array_values($params)[$i])) {
                $callbackParams[$i] = array_values($params)[$i];
            }
            // Default value
            elseif ($parameter->isDefaultValueAvailable()) {
                $callbackParams[$i] = $parameter->getDefaultValue();
            }
            // Nullable
            elseif ($parameter->allowsNull()) {
                $callbackParams[$i] = null;
            }
            // Type hinted class (dependency injection)
            elseif ($parameter->hasType() && !$parameter->getType()->isBuiltin()) {
                $className = $parameter->getType()->getName();
                if (class_exists($className)) {
                    $callbackParams[$i] = new $className();
                } else {
                    $callbackParams[$i] = null;
                }
            }
            // Missing required parameter
            else {
                throw new Exception("Missing required parameter: $paramName");
            }
        }

        return $callbackParams;
    }

    /**
     * 404 hatası işler
     */
    private function handleNotFound()
    {
        if ($this->notFoundHandler) {
            $result = call_user_func($this->notFoundHandler);
            
            if ($result !== null) {
                echo $result;
            }
            
            return $result;
        }

        return $this->handleError(404, 'Not Found');
    }

    /**
     * Hata işler
     */
    private function handleError($code, $message)
    {
        if (isset($this->errorHandlers[$code])) {
            return call_user_func($this->errorHandlers[$code], $message);
        }

        http_response_code($code);
        echo "$code - $message";
        return false;
    }

    /**
     * Exception işler
     */
    private function handleException(Exception $e)
    {
        // Development mode kontrolü
        $isDev = defined('ENVIRONMENT') && ENVIRONMENT === 'development';
        
        if ($isDev) {
            http_response_code(500);
            echo "<h1>500 - Internal Server Error</h1>";
            echo "<pre>" . $e->getMessage() . "</pre>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        } else {
            $this->handleError(500, 'Internal Server Error');
        }

        // Log error
        error_log($e->getMessage() . "\n" . $e->getTraceAsString());

        return false;
    }

    /**
     * Eşleşen rotayı döndürür
     */
    public function getMatchedRoute()
    {
        return $this->matchedRoute;
    }

    /**
     * Rota parametrelerini döndürür
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Tüm rotaları döndürür
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * İsimlendirilmiş rotaları döndürür
     */
    public function getNamedRoutes()
    {
        return $this->namedRoutes;
    }

    /**
     * Placeholder değerlerini döndürür
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     * Statik rotaları döndürür
     */
    public function getStaticRoutes()
    {
        return $this->staticRoutes;
    }

    /**
     * Dinamik rotaları döndürür
     */
    public function getDynamicRoutes()
    {
        return $this->dynamicRoutes;
    }

    /**
     * Route stats döndürür
     */
    public function getStats()
    {
        return [
            'total' => count($this->routes),
            'static' => count($this->staticRoutes),
            'dynamic' => count($this->dynamicRoutes),
            'named' => count($this->namedRoutes),
            'cache_enabled' => $this->cacheEnabled,
            'cache_exists' => file_exists($this->cacheDir . '/' . $this->cacheFile)
        ];
    }
}