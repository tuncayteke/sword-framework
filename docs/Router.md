# Router Class

URL yönlendirme işlemlerini yönetir. RESTful rotalar ve gelişmiş özellikler sunar.

## Temel Kullanım

```php
$router = new Router();

$router->get('/', 'HomeController@index');
$router->post('/login', 'AuthController@login');
$router->dispatch();
```

## HTTP Metodları

### get($pattern, $callback, $name = null)
GET isteği için rota tanımlar.

```php
$router->get('/', 'HomeController@index');
$router->get('/user/:id', 'UserController@show');
$router->get('/profile', function() {
    echo 'Profil sayfası';
});
```

### post($pattern, $callback, $name = null)
POST isteği için rota tanımlar.

```php
$router->post('/login', 'AuthController@login');
$router->post('/user', 'UserController@store');
```

### put($pattern, $callback, $name = null)
PUT isteği için rota tanımlar.

```php
$router->put('/user/:id', 'UserController@update');
```

### delete($pattern, $callback, $name = null)
DELETE isteği için rota tanımlar.

```php
$router->delete('/user/:id', 'UserController@destroy');
```

### patch($pattern, $callback, $name = null)
PATCH isteği için rota tanımlar.

```php
$router->patch('/user/:id', 'UserController@patch');
```

### any($pattern, $callback, $name = null)
Tüm HTTP metodları için rota tanımlar.

```php
$router->any('/api/webhook', 'WebhookController@handle');
```

### match(array $methods, $pattern, $callback, $name = null)
Belirtilen HTTP metodları için rota tanımlar.

```php
$router->match(['GET', 'POST'], '/contact', 'ContactController@handle');
```

## Placeholder Desenleri

### Önceden Tanımlanmış Desenler
- `:num` - Sadece sayılar `[0-9]+`
- `:alpha` - Sadece harfler `[a-zA-Z]+`
- `:alphanum` - Harfler ve sayılar `[a-zA-Z0-9]+`
- `:any` - / hariç herhangi bir karakter `[^/]+`
- `:segment` - / hariç herhangi bir karakter `[^/]+`
- `:all` - Tüm karakterler `.*`
- `:year` - Yıl formatı `[12][0-9]{3}`
- `:month` - Ay formatı `0[1-9]|1[0-2]`
- `:day` - Gün formatı `0[1-9]|[12][0-9]|3[01]`
- `:id` - ID formatı `[1-9][0-9]*`
- `:slug` - Slug formatı `[a-z0-9-]+`

```php
$router->get('/user/:id', 'UserController@show');
$router->get('/post/:slug', 'PostController@show');
$router->get('/archive/:year/:month', 'ArchiveController@show');
```

### Özel Desenler

```php
// UUID pattern tanımla
$router->pattern('uuid', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
$router->get('/user/:uuid', 'UserController@show');

// Diğer özel pattern örnekleri
$router->pattern('email', '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}');
$router->pattern('phone', '\d{3}-\d{3}-\d{4}');
$router->pattern('date', '\d{4}-\d{2}-\d{2}');
$router->pattern('version', 'v\d+\.\d+\.\d+');

// Sword Framework ile kullanım
Sword::routerPattern('uuid', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
Sword::routerGet('/user/:uuid', 'UserController@show');
```

## RESTful Resource Rotaları

### resource($uri, $controller, $name = null)
RESTful resource rotaları oluşturur.

```php
$router->resource('/users', 'UserController');
```

Bu şu rotaları oluşturur:
- `GET /users` → `UserController@index` (users.index)
- `GET /users/create` → `UserController@create` (users.create)
- `POST /users` → `UserController@store` (users.store)
- `GET /users/:num` → `UserController@show` (users.show)
- `GET /users/:num/edit` → `UserController@edit` (users.edit)
- `PUT /users/:num` → `UserController@update` (users.update)
- `PATCH /users/:num` → `UserController@update` (users.patch)
- `DELETE /users/:num` → `UserController@destroy` (users.destroy)

## Rota Grupları

### group($prefix, $callback)
Rota grubu oluşturur.

```php
$router->group('/admin', function($router) {
    $router->get('/', 'AdminController@index');
    $router->get('/users', 'AdminController@users');
    $router->resource('/posts', 'AdminPostController');
});
```

## Middleware

### middleware($middleware)
Middleware ekler.

```php
$router->middleware('AuthMiddleware')->get('/profile', 'UserController@profile');

$router->group('/admin', function($router) {
    $router->get('/', 'AdminController@index');
})->middleware('AdminMiddleware');
```

## İsimlendirilmiş Rotalar

### Rota İsimlendirme

```php
$router->get('/user/:id', 'UserController@show', 'user.show');
$router->post('/login', 'AuthController@login', 'auth.login');
```

### route($name, $params = [])
İsimlendirilmiş rotaya URL oluşturur.

```php
$url = $router->route('user.show', ['id' => 123]);
// /user/123

$url = $router->route('archive.show', ['year' => 2023, 'month' => 12]);
// /archive/2023/12
```

## Placeholder Değerleri

### placeholder($key, $value)
Placeholder değeri ekler.

```php
$router->placeholder('lang', 'tr');
$router->get('/:lang/about', 'PageController@about');
// /tr/about

// Sword Framework ile kullanım
Sword::routerPlaceholder('admin', 'myadmin');
Sword::routerGet('/:admin/dashboard', 'AdminController@dashboard');
// /myadmin/dashboard

// Çoklu placeholder
Sword::routerPlaceholder('api_version', 'v1');
Sword::routerPlaceholder('lang', 'tr');
Sword::routerGet('/:lang/api/:api_version/users', 'ApiController@users');
// /tr/api/v1/users
```

## Hata İşleme

### notFound(callable $handler)
404 hata işleyicisini ayarlar.

```php
$router->notFound(function() {
    echo '404 - Sayfa bulunamadı';
});
```

## Rota İşleme

### dispatch($method = null, $uri = null)
İstekleri işler.

```php
$router->dispatch(); // Otomatik
$router->dispatch('GET', '/user/123'); // Manuel
```

## Callback Türleri

### Closure
```php
$router->get('/test', function() {
    echo 'Test sayfası';
});
```

### Controller@method
```php
$router->get('/user', 'UserController@index');
```

### Class::method (Statik)
```php
$router->get('/api/status', 'ApiController::status');
```

## Gelişmiş Örnekler

### API Rotaları
```php
$router->group('/api/v1', function($router) {
    $router->middleware('ApiMiddleware');
    
    $router->resource('/users', 'Api\\UserController');
    $router->resource('/posts', 'Api\\PostController');
    
    $router->get('/stats', 'Api\\StatsController@index');
});
```

### Çok Dilli Rotalar
```php
$router->placeholder('lang', 'tr|en');

$router->group('/:lang', function($router) {
    $router->get('/', 'HomeController@index', 'home');
    $router->get('/about', 'PageController@about', 'about');
    $router->get('/contact', 'PageController@contact', 'contact');
});
```

### Koşullu Rotalar
```php
$router->get('/user/:id', 'UserController@show')
       ->middleware(function($params) {
           if (!is_numeric($params['id'])) {
               return false; // Rota eşleşmez
           }
           return true;
       });
```

## Bilgi Metodları

### getMatchedRoute()
Eşleşen rotayı döndürür.

### getParams()
Rota parametrelerini döndürür.

### getRoutes()
Tüm rotaları döndürür.

### getNamedRoutes()
İsimlendirilmiş rotaları döndürür.