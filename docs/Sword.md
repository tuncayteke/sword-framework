# Sword Class

Ana framework sınıfı. Tüm framework işlevlerine erişim sağlar.

## Temel Kullanım

```php
// Framework'ü başlat
Sword::bootstrap();

// Uygulama örneğini başlat
Sword::init();

// Uygulamayı çalıştır
Sword::start();
```

## Sword:: Kullanımı

Tüm framework sınıfları Sword:: üzerinden erişilebilir:

```php
// View işlemleri
Sword::view('home/index', ['title' => 'Ana Sayfa']);

// Database işlemleri
$users = Sword::db()->table('users')->get();

// Cache işlemleri
Sword::cache()->set('key', 'value', 3600);

// Session işlemleri
Sword::session('user_id', 123);

// Cookie işlemleri
Sword::cookie('remember_token', $token, 86400);

// Validation
$validation = Sword::validate($_POST, $rules);

// Upload
$result = Sword::upload($_FILES['file']);

// Image işleme
Sword::image('path/to/image.jpg')->resize(300, 200);

// Mail gönderme
Sword::mailer()->send('user@example.com', 'Subject', 'Body');

// Security
$token = Sword::security()->getCsrfToken();

// Logger
Sword::logger()->info('Log message');

// Events
Sword::on('user.created', function($user) {
    // Event handler
});
```

## Dinamik Metod Sistemi

### map($name, $callback, $options = [])
Özel metod ekler.

```php
// Özel metod tanımla
Sword::map('customMethod', function($param) {
    return "Custom: " . $param;
});

// Kullanım
$result = Sword::customMethod('test'); // "Custom: test"

// Seçeneklerle
Sword::map('apiCall', function($endpoint) {
    return file_get_contents('https://api.example.com/' . $endpoint);
}, ['cache' => true]);
```

### before($name, $callback)
Metod öncesi filtre ekler.

```php
Sword::before('routerDispatch', function() {
    // Routing öncesi çalışır
    Logger::info('Route dispatching started');
});
```

### after($name, $callback)
Metod sonrası filtre ekler.

```php
Sword::after('routerDispatch', function() {
    // Routing sonrası çalışır
    Logger::info('Route dispatching completed');
});
```

## Routing Metodları

### routerGet($pattern, $callback, $name = null)
GET isteği için rota tanımlar.

```php
Sword::routerGet('/', 'HomeController@index');
Sword::routerGet('/user/:id', function($id) {
    echo "User ID: " . $id;
});
```

### routerPost($pattern, $callback, $name = null)
POST isteği için rota tanımlar.

```php
Sword::routerPost('/login', 'AuthController@login');
```

### routerGroup($prefix, $callback)
Rota grubu oluşturur.

```php
Sword::routerGroup('admin', function($router) {
    $router->get('/', 'AdminController@index');
    $router->get('/users', 'AdminController@users');
});
```

## Sınıf Erişim Metodları

### view($template = null, $data = [])
View sınıfını döndürür veya render eder.

```php
// View sınıfını al
$view = Sword::view();

// Direkt render
echo Sword::view('home/index', ['title' => 'Ana Sayfa']);
```

### theme()
Theme sınıfını döndürür.

```php
$theme = Sword::theme();
$theme->load('default');
```

### lang()
Lang sınıfını döndürür.

```php
$lang = Sword::lang();
echo Sword::lang()->get('welcome.message');
```

### security()
Security sınıfını döndürür.

```php
$security = Sword::security();
$token = Sword::security()->getCsrfToken();
```

### logger()
Logger sınıfını döndürür.

```php
$logger = Sword::logger();
Sword::logger()->error('Hata mesajı');
```

### events()
Events sınıfını döndürür.

```php
$events = Sword::events();
Sword::events()->trigger('user.login', $user);
```

### cryptor()
Cryptor sınıfını döndürür.

```php
$cryptor = Sword::cryptor();
$encrypted = Sword::cryptor()->encrypt('data');
```

### cache($driver = null)
Cache sınıfını döndürür.

```php
$cache = Sword::cache();
Sword::cache()->set('key', 'value', 3600);

// Belirli driver
$fileCache = Sword::cache('file');
```

### db($config = [])
Database sınıfını döndürür.

```php
$db = Sword::db();
$users = Sword::db()->table('users')->get();
```

### model($modelName)
Model sınıfını döndürür.

```php
$userModel = Sword::model('User');
$user = Sword::model('User')->find(1);
```

### validate($data = [], $rules = [], $messages = [])
Validation sınıfını döndürür.

```php
$validation = Sword::validate($_POST, [
    'email' => 'required|email',
    'name' => 'required|min:2'
]);
```

### upload($file = null, $customName = null, $subDir = null)
Upload sınıfını döndürür veya dosya yükler.

```php
// Upload sınıfı
$upload = Sword::upload();

// Direkt yükleme
$result = Sword::upload($_FILES['file'], 'custom_name');
```

### uploadMultiple($files, $subDir = null)
Çoklu dosya yükler.

```php
$results = Sword::uploadMultiple($_FILES['files']);
```

### image($path = null)
Image sınıfını döndürür.

```php
$image = Sword::image('path/to/image.jpg');
$image->resize(300, 200)->save();
```

### thumbnails($imagePath = null, $sizes = ['xs', 'sm', 'md', 'lg'])
Thumbnails sınıfını döndürür.

```php
$thumbnails = Sword::thumbnails('image.jpg');
```

### config($key = null, $default = null)
Config sınıfını döndürür.

```php
$config = Sword::config('app.name', 'Default App');
```

### session($key = null, $value = null)
Session işlemlerini yönetir.

```php
// Session sınıfı
$session = Sword::session();

// Değer set et
Sword::session('user_id', 123);

// Değer al
$userId = Sword::session('user_id');
```

### cookie($name = null, $value = null, $expire = 0, $options = [])
Cookie işlemlerini yönetir.

```php
// Cookie sınıfı
$cookie = Sword::cookie();

// Cookie set et
Sword::cookie('remember_token', $token, 86400);

// Cookie al
$token = Sword::cookie('remember_token');
```

### mailer()
Mailer sınıfını döndürür.

```php
$mailer = Sword::mailer();
$mailer->send('user@example.com', 'Subject', 'Body');
```

### validation($data = [])
Validation sınıfını döndürür.

```php
$validation = Sword::validation($_POST);
$validation->rule('email', 'E-posta', 'required|email');
```

### throttle()
Throttle sınıfını döndürür.

```php
$throttle = Sword::throttle();
$throttle->attempt('login', 5, 300); // 5 deneme, 5 dakika
```

### permalink()
Permalink sınıfını döndürür.

```php
$permalink = Sword::permalink();
$slug = $permalink->create('Başlık Metni');
```

### query()
QueryBuilder sınıfını döndürür.

```php
$query = Sword::query();
$users = $query->table('users')->where('active', 1)->get();
```

### memory()
MemoryManager sınıfını döndürür.

```php
$memory = Sword::memory();
$usage = MemoryManager::getUsage();
```

### dbTable()
DbTabler sınıfını döndürür.

```php
$dbTable = Sword::dbTable();
```

## Shortcode Metodları

### shortcode($tag, $callback)
Shortcode ekler.

```php
Sword::shortcode('button', function($atts, $content) {
    $url = $atts['url'] ?? '#';
    $text = $atts['text'] ?? $content;
    return "<a href='{$url}' class='btn'>{$text}</a>";
});

// Kullanım: [button url='/contact' text='İletişim']
```

### removeShortcode($tag)
Shortcode'u kaldırır.

```php
Sword::removeShortcode('button');
```

## Yardımcı Metodlar

### url($path = '', $params = [], $absolute = true)
URL oluşturur.

```php
$url = Sword::url('admin/users');
$url = Sword::url('search', ['q' => 'test']);
```

### redirect($url, $params = [], $statusCode = 302)
Yönlendirme yapar.

```php
Sword::redirect('/login');
Sword::redirect('user.profile', ['id' => 123]);
```

### back($fallback = '/', $statusCode = 302)
Önceki sayfaya yönlendirir.

```php
Sword::back();
Sword::back('/home');
```

### getAvailableMethods()
Kullanılabilir tüm metodları listeler.

```php
$methods = Sword::getAvailableMethods();
foreach ($methods as $method) {
    echo $method . "\n";
}
```

### raw($key = null, $default = null)
View verilerini döndürür.

```php
// Tüm view verileri
$allData = Sword::raw();

// Belirli veri
$title = Sword::raw('title', 'Varsayılan Başlık');
```

### help()
Yardım bilgisini gösterir.

```php
echo Sword::help();
```

## Yapılandırma Metodları

### setData($key, $value)
Yapılandırma değeri ayarlar.

```php
Sword::setData('app_name', 'My App');
```

### getData($key, $default = null)
Yapılandırma değeri alır.

```php
$appName = Sword::getData('app_name', 'Default App');
```

## Olay Sistemi

### on($event, $callback)
Olay dinleyicisi ekler.

```php
Sword::on('user.login', function($user) {
    Logger::info('User logged in: ' . $user->email);
});
```

### trigger($event, ...$params)
Olay tetikler.

```php
Sword::trigger('user.login', $user);
```

## Magic Method (__callStatic)

Sword sınıfı, bilinmeyen statik metod çağrılarını otomatik olarak yönlendirir:

```php
// theme_load -> Theme::load
Sword::theme_load('default');

// lang_get -> Lang::get  
echo Sword::lang_get('welcome.message');

// Özel metodlar
Sword::map('customHelper', function($data) {
    return strtoupper($data);
});

$result = Sword::customHelper('test'); // "TEST"
```

## Gelişmiş Örnekler

### Özel Metod Tanımlama

```php
// API helper
Sword::map('api', function($endpoint, $data = []) {
    $url = 'https://api.example.com/' . $endpoint;
    $response = file_get_contents($url, false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'content' => json_encode($data),
            'header' => 'Content-Type: application/json'
        ]
    ]));
    return json_decode($response, true);
});

// Kullanım
$result = Sword::api('users', ['name' => 'John']);
```

### Event Driven Architecture

```php
// Event tanımla
Sword::on('order.created', function($order) {
    // E-posta gönder
    Sword::mailer()->send($order->email, 'Sipariş Onayı', 'Siparişiniz alındı');
    
    // Log kaydet
    Sword::logger()->info('New order: ' . $order->id);
    
    // Cache temizle
    Sword::cache()->delete('orders_count');
});

// Event tetikle
Sword::trigger('order.created', $order);
```

### Middleware Sistemi

```php
// Önce filtre
Sword::before('routerDispatch', function() {
    // CSRF kontrolü
    if ($_POST && !Sword::security()->validateCsrfToken()) {
        Sword::response()->error('CSRF token invalid', 403)->send();
    }
});

// Sonra filtre
Sword::after('routerDispatch', function() {
    // Performans log
    $memory = Sword::memory()::getUsage();
    Sword::logger()->info('Memory usage: ' . $memory);
});
```