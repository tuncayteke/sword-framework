<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Router sınıfı - URL yönlendirme işlemlerini yönetir
 */

class Router
{
    /**
     * Kayıtlı rotalar
     */
    private $routes = [];

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
        ':num' => '[0-9]+',              // Sadece sayılar
        ':alpha' => '[a-zA-Z]+',         // Sadece harfler
        ':alphanum' => '[a-zA-Z0-9]+',   // Harfler ve sayılar
        ':any' => '[^/]+',               // / hariç herhangi bir karakter
        ':segment' => '[^/]+',           // / hariç herhangi bir karakter (any ile aynı)
        ':all' => '.*',                  // Tüm karakterler (/ dahil)
        ':year' => '[12][0-9]{3}',       // Yıl formatı (1000-2999)
        ':month' => '0[1-9]|1[0-2]',     // Ay formatı (01-12)
        ':day' => '0[1-9]|[12][0-9]|3[01]', // Gün formatı (01-31)
        ':id' => '[1-9][0-9]*',          // ID formatı (1'den başlayan pozitif tam sayılar)
        ':slug' => '[a-z0-9-]+'          // Slug formatı (küçük harfler, sayılar ve tire)
    ];

    /**
     * Middleware'ler
     */
    private $middlewares = [];

    /**
     * Geçerli grup öneki
     */
    private $groupPrefix = '';

    /**
     * Geçerli grup middleware'leri
     */
    private $groupMiddlewares = [];

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
     * GET isteği için rota ekler
     *
     * @param string $pattern URL deseni
     * @param callable|string $callback Çağrılacak fonksiyon veya Controller@method
     * @param string|null $name Rota adı
     * @return Router
     */
    public function get($pattern, $callback, $name = null)
    {
        // Boş string'i "/" yap
        if ($pattern === '') {
            $pattern = '/';
        }
        // Eğer pattern "/" ile başlamıyorsa ekle
        elseif ($pattern !== '/' && substr($pattern, 0, 1) !== '/') {
            $pattern = '/' . $pattern;
        }
        return $this->addRoute('GET', $pattern, $callback, $name);
    }

    /**
     * POST isteği için rota ekler
     *
     * @param string $pattern URL deseni
     * @param callable|string $callback Çağrılacak fonksiyon veya Controller@method
     * @param string|null $name Rota adı
     * @return Router
     */
    public function post($pattern, $callback, $name = null)
    {
        // Eğer pattern "/" ile başlamıyorsa ekle
        if ($pattern !== '/' && substr($pattern, 0, 1) !== '/') {
            $pattern = '/' . $pattern;
        }
        return $this->addRoute('POST', $pattern, $callback, $name);
    }

    /**
     * PUT isteği için rota ekler
     *
     * @param string $pattern URL deseni
     * @param callable|string $callback Çağrılacak fonksiyon veya Controller@method
     * @param string|null $name Rota adı
     * @return Router
     */
    public function put($pattern, $callback, $name = null)
    {
        // Eğer pattern "/" ile başlamıyorsa ekle
        if ($pattern !== '/' && substr($pattern, 0, 1) !== '/') {
            $pattern = '/' . $pattern;
        }
        return $this->addRoute('PUT', $pattern, $callback, $name);
    }

    /**
     * DELETE isteği için rota ekler
     *
     * @param string $pattern URL deseni
     * @param callable|string $callback Çağrılacak fonksiyon veya Controller@method
     * @param string|null $name Rota adı
     * @return Router
     */
    public function delete($pattern, $callback, $name = null)
    {
        // Eğer pattern "/" ile başlamıyorsa ekle
        if ($pattern !== '/' && substr($pattern, 0, 1) !== '/') {
            $pattern = '/' . $pattern;
        }
        return $this->addRoute('DELETE', $pattern, $callback, $name);
    }

    /**
     * PATCH isteği için rota ekler
     *
     * @param string $pattern URL deseni
     * @param callable|string $callback Çağrılacak fonksiyon veya Controller@method
     * @param string|null $name Rota adı
     * @return Router
     */
    public function patch($pattern, $callback, $name = null)
    {
        // Eğer pattern "/" ile başlamıyorsa ekle
        if ($pattern !== '/' && substr($pattern, 0, 1) !== '/') {
            $pattern = '/' . $pattern;
        }
        return $this->addRoute('PATCH', $pattern, $callback, $name);
    }

    /**
     * Tüm HTTP metodları için rota ekler
     *
     * @param string $pattern URL deseni
     * @param callable|string $callback Çağrılacak fonksiyon veya Controller@method
     * @param string|null $name Rota adı
     * @return Router
     */
    public function any($pattern, $callback, $name = null)
    {
        // Eğer pattern "/" ile başlamıyorsa ekle
        if ($pattern !== '/' && substr($pattern, 0, 1) !== '/') {
            $pattern = '/' . $pattern;
        }

        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
        foreach ($methods as $method) {
            $this->addRoute($method, $pattern, $callback, $name);
        }

        return $this;
    }

    /**
     * Belirtilen HTTP metodları için rota ekler
     *
     * @param array $methods HTTP metodları
     * @param string $pattern URL deseni
     * @param callable|string $callback Çağrılacak fonksiyon veya Controller@method
     * @param string|null $name Rota adı
     * @return Router
     */
    public function match(array $methods, $pattern, $callback, $name = null)
    {
        // Eğer pattern "/" ile başlamıyorsa ekle
        if ($pattern !== '/' && substr($pattern, 0, 1) !== '/') {
            $pattern = '/' . $pattern;
        }

        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $pattern, $callback, $name);
        }

        return $this;
    }

    /**
     * RESTful resource rotaları oluşturur
     *
     * @param string $uri Resource URI (örn: 'users', 'posts')
     * @param string $controller Controller sınıfı
     * @param string|null $name Resource adı (route isimlendirme için)
     * @return Router
     */
    public function resource($uri, $controller, $name = null)
    {
        // Eğer uri "/" ile başlamıyorsa ekle
        if ($uri !== '/' && substr($uri, 0, 1) !== '/') {
            $uri = '/' . $uri;
        }

        $resourceName = $name ?: trim($uri, '/');

        // GET /users - index (tüm kayıtları listele)
        $this->get($uri, $controller . '@index', $resourceName . '.index');

        // GET /users/create - create (yeni kayıt formu)
        $this->get($uri . '/create', $controller . '@create', $resourceName . '.create');

        // POST /users - store (yeni kayıt kaydet)
        $this->post($uri, $controller . '@store', $resourceName . '.store');

        // GET /users/:num - show (tek kayıt göster)
        $this->get($uri . '/:num', $controller . '@show', $resourceName . '.show');

        // GET /users/:num/edit - edit (kayıt düzenleme formu)
        $this->get($uri . '/:num/edit', $controller . '@edit', $resourceName . '.edit');

        // PUT/PATCH /users/:num - update (kayıt güncelle)
        $this->put($uri . '/:num', $controller . '@update', $resourceName . '.update');
        $this->patch($uri . '/:num', $controller . '@update', $resourceName . '.patch');

        // DELETE /users/:num - destroy (kayıt sil)
        $this->delete($uri . '/:num', $controller . '@destroy', $resourceName . '.destroy');

        return $this;
    }

    /**
     * Rota grubu oluşturur
     *
     * @param string $prefix Grup öneki
     * @param callable $callback Grup içeriği
     * @return Router
     */
    public function group($prefix, $callback)
    {
        $previousGroupPrefix = $this->groupPrefix;
        $previousGroupMiddlewares = $this->groupMiddlewares;

        // Placeholder değerlerini prefix'te değiştir (/ eklemeden önce)
        foreach ($this->placeholders as $key => $value) {
            $prefix = str_replace(":$key", $value, $prefix);
        }

        // Eğer prefix "/" ile başlamıyorsa ekle
        if ($prefix !== '/' && substr($prefix, 0, 1) !== '/') {
            $prefix = '/' . $prefix;
        }

        $this->groupPrefix = $previousGroupPrefix . $prefix;
        $this->groupMiddlewares = array_merge($previousGroupMiddlewares, $this->middlewares);

        call_user_func($callback, $this);

        $this->groupPrefix = $previousGroupPrefix;
        $this->groupMiddlewares = $previousGroupMiddlewares;
        $this->middlewares = [];

        return $this;
    }

    /**
     * Middleware ekler
     *
     * @param callable|string $middleware Middleware
     * @return Router
     */
    public function middleware($middleware)
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Placeholder değeri ekler
     *
     * @param string $key Anahtar
     * @param string $value Değer
     * @return Router
     */
    public function placeholder($key, $value)
    {
        $this->placeholders[$key] = $value;
        return $this;
    }

    /**
     * Özel bir placeholder deseni ekler
     *
     * @param string $name Desen adı (:name şeklinde kullanılacak)
     * @param string $pattern Regex deseni
     * @return Router
     */
    public function pattern($name, $pattern)
    {
        $this->patterns[$name] = $pattern;
        return $this;
    }

    /**
     * 404 hata işleyicisini ayarlar
     *
     * @param callable $handler İşleyici fonksiyon
     * @return Router
     */
    public function notFound(callable $handler)
    {
        $this->notFoundHandler = $handler;
        return $this;
    }

    /**
     * İsimlendirilmiş rotaya URL oluşturur
     *
     * @param string $name Rota adı
     * @param array $params Parametreler
     * @return string URL
     * @throws Exception Rota bulunamazsa
     */
    public function route($name, array $params = [])
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new Exception("İsimlendirilmiş rota bulunamadı: $name");
        }

        $url = $this->namedRoutes[$name];

        // Önce pattern'ları geçici olarak değiştir
        $tempReplacements = [];
        foreach ($this->patterns as $placeholder => $regex) {
            if (strpos($url, $placeholder) !== false) {
                $paramName = substr($placeholder, 1);
                $tempKey = '___TEMP_' . $paramName . '___';
                $url = str_replace($placeholder, $tempKey, $url);
                $tempReplacements[$tempKey] = $paramName;
            }
        }

        // Parametreleri yerleştir
        foreach ($params as $key => $value) {
            $url = str_replace(":$key", $value, $url);
        }

        // Geçici değişiklikleri geri al
        foreach ($tempReplacements as $tempKey => $paramName) {
            if (isset($params[$paramName])) {
                $url = str_replace($tempKey, $params[$paramName], $url);
            } else {
                $url = str_replace($tempKey, '', $url);
            }
        }

        // Placeholder değerlerini uygula
        foreach ($this->placeholders as $key => $value) {
            $url = str_replace(":$key", $value, $url);
        }

        return $url;
    }

    /**
     * Rota ekler
     *
     * @param string $method HTTP metodu
     * @param string $pattern URL deseni
     * @param callable|string $callback Çağrılacak fonksiyon veya Controller@method
     * @param string|null $name Rota adı
     * @return Router
     */
    public function addRoute($method, $pattern, $callback, $name = null)
    {
        $originalPattern = $pattern;

        // Placeholder değerlerini pattern'da değiştir
        foreach ($this->placeholders as $key => $value) {
            $pattern = str_replace(":$key", $value, $pattern);
        }

        $pattern = $this->groupPrefix . $pattern;

        // Pattern'i normalize et
        if ($pattern === '' || $pattern === '//') {
            $pattern = '/';
        } else {
            // Çift slash'ları tek slash yap
            $pattern = preg_replace('#/+#', '/', $pattern);
            // Trailing slash kaldır (ana sayfa hariç)
            if ($pattern !== '/') {
                $pattern = rtrim($pattern, '/');
            }
        }

        $middlewares = $this->groupMiddlewares;

        if (!empty($this->middlewares)) {
            $middlewares = array_merge($middlewares, $this->middlewares);
            $this->middlewares = [];
        }

        // Regex pattern oluştur
        $regexPattern = $pattern;

        // Önceden tanımlanmış desenleri işle
        foreach ($this->patterns as $placeholder => $regex) {
            if (strpos($regexPattern, $placeholder) !== false) {
                $paramName = substr($placeholder, 1);
                $regexPattern = str_replace($placeholder, "(?P<{$paramName}>{$regex})", $regexPattern);
            }
        }

        // Basit parametreleri işle (:param)
        $regexPattern = preg_replace('/:(\w+)/', '(?P<$1>[^/]+)', $regexPattern);

        // Regex'i tamamla
        $regex = '#^' . str_replace('/', '\/', $regexPattern) . '$#u';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'regex' => $regex,
            'callback' => $callback,
            'middlewares' => $middlewares
        ];

        if ($name) {
            $this->namedRoutes[$name] = $originalPattern;
        }

        return $this;
    }

    /**
     * İstekleri işler
     *
     * @param string|null $method HTTP metodu
     * @param string|null $uri URI
     * @return mixed İşlem sonucu
     */
    public function dispatch($method = null, $uri = null)
    {
        $method = $method ?: $_SERVER['REQUEST_METHOD'];
        $uri = $uri ?: parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Trailing slash'ı normalize et
        $uri = '/' . trim($uri, '/');
        if ($uri === '/') {
            // Ana sayfa için / kalsın
        } else {
            // Diğer URL'ler için trailing slash kaldır
            $uri = rtrim($uri, '/');
        }

        // PUT, DELETE gibi metodları desteklemek için _method parametresini kontrol et
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        // Rotaları kontrol et
        foreach ($this->routes as $route) {
            if ($route['method'] != $method) {
                continue;
            }

            // Regex ile eşleştirme yap
            if (preg_match($route['regex'], $uri, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_numeric($key)) {
                        $params[$key] = $value;
                    }
                }

                // Placeholder değerlerini uygula
                foreach ($this->placeholders as $key => $value) {
                    if (isset($params[$key])) {
                        $params[$key] = $value;
                    }
                }

                // Eşleşen rotayı ve parametreleri kaydet
                $this->params = $params;
                $this->matchedRoute = $route;

                // Middleware'leri çalıştır
                if (!$this->runMiddlewares($route, $params)) {
                    return false;
                }

                // Callback'i çalıştır
                return $this->executeCallback($route['callback'], $params);
            }
        }

        // Rota bulunamadı
        return $this->handleNotFound();
    }

    /**
     * Middleware'leri çalıştırır
     *
     * @param array $route Rota
     * @param array $params Parametreler
     * @return bool Başarılı mı?
     */
    protected function runMiddlewares($route, $params)
    {
        foreach ($route['middlewares'] as $middleware) {
            if (is_callable($middleware)) {
                $result = call_user_func($middleware, $params);
                if ($result === false) {
                    return false;
                }
            } else {
                // Middleware sınıfını yükle ve çalıştır
                if (class_exists($middleware)) {
                    $middlewareObj = new $middleware();
                    $result = $middlewareObj->handle($params);
                    if ($result === false) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Callback'i çalıştırır
     *
     * @param callable|string $callback Callback
     * @param array $params Parametreler
     * @return mixed Sonuç
     */
    protected function executeCallback($callback, $params)
    {
        // Callback türünü belirle
        if (is_callable($callback)) {
            // Fonksiyon veya closure
            return $this->executeCallableCallback($callback, $params);
        } elseif (is_string($callback) && strpos($callback, '@') !== false) {
            // Controller@method formatı
            return $this->executeControllerCallback($callback, $params);
        } elseif (is_string($callback) && strpos($callback, '::') !== false) {
            // Class::method formatı (statik metod)
            return $this->executeStaticCallback($callback, $params);
        }

        return null;
    }

    /**
     * Çağrılabilir callback'i çalıştırır
     *
     * @param callable $callback Callback
     * @param array $params Parametreler
     * @return mixed Sonuç
     */
    protected function executeCallableCallback($callback, $params)
    {
        try {
            // Reflection kullanarak parametre bilgilerini al
            if (is_array($callback)) {
                $reflection = new \ReflectionMethod($callback[0], $callback[1]);
            } else {
                $reflection = new \ReflectionFunction($callback);
            }

            // Parametreleri hazırla
            $callbackParams = $this->prepareCallbackParameters($reflection, $params);

            // Callback'i çalıştır
            $result = call_user_func_array($callback, $callbackParams);

            // Sonucu echo et (eğer null değilse)
            if ($result !== null) {
                echo $result;
            }

            return $result;
        } catch (\Exception $e) {
            // Hata durumunda, parametreleri doğrudan geçmeyi dene
            $result = call_user_func_array($callback, array_values($params));

            // Sonucu echo et (eğer null değilse)
            if ($result !== null) {
                echo $result;
            }

            return $result;
        }
    }

    /**
     * Controller callback'ini çalıştırır
     *
     * @param string $callback Controller@method formatında callback
     * @param array $params Parametreler
     * @return mixed Sonuç
     */
    protected function executeControllerCallback($callback, $params)
    {
        list($controller, $method) = explode('@', $callback);

        if (class_exists($controller)) {
            $controllerObj = new $controller();
            if (method_exists($controllerObj, $method)) {
                try {
                    // Reflection kullanarak parametre bilgilerini al
                    $reflection = new \ReflectionMethod($controllerObj, $method);

                    // Parametreleri hazırla
                    $callbackParams = $this->prepareCallbackParameters($reflection, $params);

                    // Metodu çalıştır
                    $result = call_user_func_array([$controllerObj, $method], $callbackParams);

                    // Sonucu echo et
                    if ($result !== null) {
                        echo $result;
                    }

                    return $result;
                } catch (\Exception $e) {
                    // Hata durumunda, parametreleri doğrudan geçmeyi dene
                    $result = call_user_func_array([$controllerObj, $method], array_values($params));

                    // Sonucu echo et
                    if ($result !== null) {
                        echo $result;
                    }

                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Statik callback'i çalıştırır
     *
     * @param string $callback Class::method formatında callback
     * @param array $params Parametreler
     * @return mixed Sonuç
     */
    protected function executeStaticCallback($callback, $params)
    {
        list($class, $method) = explode('::', $callback);

        if (class_exists($class)) {
            if (method_exists($class, $method)) {
                try {
                    // Reflection kullanarak parametre bilgilerini al
                    $reflection = new \ReflectionMethod($class, $method);

                    // Parametreleri hazırla
                    $callbackParams = $this->prepareCallbackParameters($reflection, $params);

                    // Statik metodu çalıştır
                    return call_user_func_array([$class, $method], $callbackParams);
                } catch (\Exception $e) {
                    // Hata durumunda, parametreleri doğrudan geçmeyi dene
                    return call_user_func_array([$class, $method], array_values($params));
                }
            }
        }

        return null;
    }

    /**
     * Callback parametrelerini hazırlar
     *
     * @param \ReflectionFunctionAbstract $reflection Reflection
     * @param array $params Parametreler
     * @return array Hazırlanmış parametreler
     */
    protected function prepareCallbackParameters(\ReflectionFunctionAbstract $reflection, $params)
    {
        $parameters = $reflection->getParameters();
        $callbackParams = [];

        foreach ($parameters as $i => $parameter) {
            $paramName = $parameter->getName();

            // İsimle parametre eşleştirme
            if (isset($params[$paramName])) {
                $callbackParams[$i] = $params[$paramName];
            }
            // Sırayla parametre eşleştirme
            elseif (isset(array_values($params)[$i])) {
                $callbackParams[$i] = array_values($params)[$i];
            }
            // Varsayılan değer kullanma
            elseif ($parameter->isDefaultValueAvailable()) {
                $callbackParams[$i] = $parameter->getDefaultValue();
            }
            // Null değer kullanma
            else {
                $callbackParams[$i] = null;
            }
        }

        return $callbackParams;
    }

    /**
     * 404 hatasını işler
     *
     * @return mixed İşlem sonucu
     */
    protected function handleNotFound()
    {
        if ($this->notFoundHandler) {
            $result = call_user_func($this->notFoundHandler);

            // Eğer result null değilse echo et
            if ($result !== null) {
                echo $result;
            }

            return $result;
        }

        // Varsayılan 404 hatası
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
        return false;
    }

    /**
     * Eşleşen rotayı döndürür
     *
     * @return array|null Eşleşen rota
     */
    public function getMatchedRoute()
    {
        return $this->matchedRoute;
    }

    /**
     * Rota parametrelerini döndürür
     *
     * @return array Parametreler
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Tüm rotaları döndürür
     *
     * @return array Rotalar
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * İsimlendirilmiş rotaları döndürür
     *
     * @return array İsimlendirilmiş rotalar
     */
    public function getNamedRoutes()
    {
        return $this->namedRoutes;
    }

    /**
     * Placeholder değerlerini döndürür
     *
     * @return array Placeholder değerleri
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }
}
