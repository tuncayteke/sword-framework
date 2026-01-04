# Events Class

Olay yönetimini sağlar. Observer pattern ile gevşek bağlı sistem mimarisi oluşturur.

## Temel Kullanım

```php
// Olay dinleyicisi ekle
Events::on('user.login', function($user) {
    Logger::info('User logged in: ' . $user->email);
});

// Olay tetikle
Events::trigger('user.login', $user);
```

## Olay Dinleyicisi Ekleme

### on($event, $callback, $priority = 10)
Bir olaya dinleyici ekler.

```php
// Basit dinleyici
Events::on('user.created', function($user) {
    Mail::send('welcome', $user->email);
});

// Öncelikli dinleyici (düşük sayı = yüksek öncelik)
Events::on('user.login', 'AuthLogger::log', 5);
Events::on('user.login', 'SessionManager::update', 10);
```

### Callback Türleri

#### Closure
```php
Events::on('order.completed', function($order) {
    // İşlem
});
```

#### Sınıf Metodu
```php
Events::on('user.registered', 'EmailService::sendWelcome');
```

#### Callable Array
```php
Events::on('payment.failed', [$paymentService, 'handleFailure']);
```

## Olay Tetikleme

### trigger($event, $data = null)
Bir olayı tetikler.

```php
// Basit tetikleme
Events::trigger('app.started');

// Veri ile tetikleme
Events::trigger('user.updated', $user);
Events::trigger('order.created', ['order' => $order, 'customer' => $customer]);
```

### triggerAll($event, $data = null)
Tüm dinleyicilerin sonuçlarını döndürür.

```php
$results = Events::triggerAll('validation.rules', $data);
foreach ($results as $priority => $result) {
    // Her dinleyicinin sonucu
}
```

## Dinleyici Kaldırma

### off($event, $callback = null)
Dinleyici kaldırır.

```php
// Belirli dinleyiciyi kaldır
$callback = function($user) { /* ... */ };
Events::on('user.login', $callback);
Events::off('user.login', $callback);

// Tüm dinleyicileri kaldır
Events::off('user.login');
```

## Sistem Olayları

Framework'ün önceden tanımlanmış olayları:

### PRE_SYSTEM
Sistem başlamadan önce.

```php
Events::on(Events::PRE_SYSTEM, function() {
    // Sistem başlangıç işlemleri
});

// Veya
Events::triggerPreSystem();
```

### BEFORE_CONTROLLER_CONSTRUCTOR
Controller yapıcısından önce.

```php
Events::on(Events::BEFORE_CONTROLLER_CONSTRUCTOR, function($controller) {
    // Controller başlatılmadan önce
});
```

### BEFORE_CONTROLLER_METHOD
Controller metodu çalışmadan önce.

```php
Events::on(Events::BEFORE_CONTROLLER_METHOD, function($data) {
    // Method çalışmadan önce
    // $data = ['controller' => $controller, 'method' => $method, 'params' => $params]
});
```

### AFTER_CONTROLLER_METHOD
Controller metodu çalıştıktan sonra.

```php
Events::on(Events::AFTER_CONTROLLER_METHOD, function($data) {
    // Method çalıştıktan sonra
});
```

### POST_SYSTEM
Sistem bittikten sonra.

```php
Events::on(Events::POST_SYSTEM, function() {
    // Sistem bitiş işlemleri
});
```

## Bilgi Metodları

### getListeners($event)
Bir olayın dinleyicilerini döndürür.

```php
$listeners = Events::getListeners('user.login');
```

### countListeners($event)
Dinleyici sayısını döndürür.

```php
$count = Events::countListeners('user.login');
```

### getEvents()
Tüm olayları döndürür.

```php
$events = Events::getEvents();
```

### clear()
Tüm dinleyicileri temizler.

```php
Events::clear();
```

## Örnek Kullanımlar

### Kullanıcı Sistemi
```php
// Kullanıcı olayları
Events::on('user.registered', function($user) {
    // Hoş geldin e-postası gönder
    Mail::send('welcome', [
        'to' => $user->email,
        'name' => $user->name
    ]);
});

Events::on('user.registered', function($user) {
    // Varsayılan rol ata
    $user->assignRole('user');
});

Events::on('user.login', function($user) {
    // Son giriş zamanını güncelle
    $user->update(['last_login' => now()]);
});

Events::on('user.login', function($user) {
    // Giriş logla
    Logger::info("User {$user->id} logged in from " . $_SERVER['REMOTE_ADDR']);
});

// Controller'da kullanım
class AuthController extends Controller
{
    public function register()
    {
        $user = User::create($this->request->post());
        
        // Olay tetikle
        Events::trigger('user.registered', $user);
        
        return $this->success('Kayıt başarılı');
    }
    
    public function login()
    {
        if ($this->auth->attempt($credentials)) {
            $user = $this->auth->user();
            
            // Olay tetikle
            Events::trigger('user.login', $user);
            
            return $this->redirect('/dashboard');
        }
    }
}
```

### E-ticaret Sistemi
```php
// Sipariş olayları
Events::on('order.created', function($order) {
    // Stok güncelle
    foreach ($order->items as $item) {
        Product::decreaseStock($item->product_id, $item->quantity);
    }
});

Events::on('order.created', function($order) {
    // Müşteriye onay e-postası
    Mail::send('order-confirmation', [
        'to' => $order->customer->email,
        'order' => $order
    ]);
});

Events::on('order.created', function($order) {
    // Admin bilgilendirme
    Mail::send('new-order-admin', [
        'to' => 'admin@site.com',
        'order' => $order
    ]);
});

Events::on('order.paid', function($order) {
    // Ödeme sonrası işlemler
    $order->update(['status' => 'processing']);
    
    // Fatura oluştur
    Invoice::create($order);
});

Events::on('order.shipped', function($order) {
    // Kargo bilgilendirme
    SMS::send($order->customer->phone, "Siparişiniz kargoya verildi: {$order->tracking_code}");
});

// Controller'da
class OrderController extends Controller
{
    public function store()
    {
        $order = Order::create($this->request->post());
        
        Events::trigger('order.created', $order);
        
        return $this->success($order);
    }
    
    public function markAsPaid($id)
    {
        $order = Order::find($id);
        $order->markAsPaid();
        
        Events::trigger('order.paid', $order);
        
        return $this->success('Ödeme kaydedildi');
    }
}
```

### Plugin Sistemi
```php
// Plugin yükleme
Events::on('plugins.loaded', function() {
    // Tüm pluginler yüklendikten sonra
    PluginManager::initializeAll();
});

Events::on('theme.activated', function($theme) {
    // Tema aktifleştirildiğinde
    Cache::clear('theme_assets');
    AssetManager::compile($theme);
});

// Hook sistemi
Events::on('content.before_render', function($content) {
    // İçerik render edilmeden önce
    return ShortcodeProcessor::process($content);
});

Events::on('content.after_render', function($content) {
    // İçerik render edildikten sonra
    return MinifyService::minify($content);
});
```

### Caching Sistemi
```php
// Cache olayları
Events::on('cache.hit', function($key) {
    Logger::debug("Cache hit: $key");
});

Events::on('cache.miss', function($key) {
    Logger::debug("Cache miss: $key");
});

Events::on('model.updated', function($model) {
    // Model güncellendiğinde ilgili cache'leri temizle
    $cacheKeys = [
        "model.{$model->getTable()}.{$model->id}",
        "model.{$model->getTable()}.list"
    ];
    
    foreach ($cacheKeys as $key) {
        Cache::forget($key);
    }
});

Events::on('user.updated', function($user) {
    // Kullanıcı güncellendiğinde session'ı güncelle
    if (Session::get('user_id') == $user->id) {
        Session::set('user_data', $user->toArray());
    }
});
```

### Validation Sistemi
```php
// Özel validation kuralları
Events::on('validation.rules', function($rules) {
    $rules['unique_username'] = function($value) {
        return !User::where('username', $value)->exists();
    };
    
    return $rules;
});

Events::on('validation.messages', function($messages) {
    $messages['unique_username'] = 'Bu kullanıcı adı zaten kullanılıyor.';
    return $messages;
});
```

### Logging Sistemi
```php
// Otomatik loglama
Events::on('user.*', function($data, $event) {
    Logger::info("User event: $event", $data);
});

Events::on('error.*', function($error, $event) {
    Logger::error("Error event: $event", [
        'message' => $error->getMessage(),
        'file' => $error->getFile(),
        'line' => $error->getLine()
    ]);
});

Events::on('security.suspicious_activity', function($data) {
    // Şüpheli aktivite
    Logger::warning('Suspicious activity detected', $data);
    
    // Admin bilgilendirme
    Mail::send('security-alert', [
        'to' => 'security@site.com',
        'data' => $data
    ]);
});
```

### Performance Monitoring
```php
Events::on('query.executed', function($query) {
    if ($query['time'] > 1000) { // 1 saniyeden uzun
        Logger::warning('Slow query detected', [
            'sql' => $query['sql'],
            'time' => $query['time'],
            'bindings' => $query['bindings']
        ]);
    }
});

Events::on('request.completed', function($data) {
    $executionTime = microtime(true) - $data['start_time'];
    
    if ($executionTime > 2.0) { // 2 saniyeden uzun
        Logger::warning('Slow request', [
            'url' => $data['url'],
            'method' => $data['method'],
            'time' => $executionTime
        ]);
    }
});
```

## Gelişmiş Özellikler

### Koşullu Dinleyiciler
```php
Events::on('user.login', function($user) {
    // Sadece admin kullanıcılar için
    if ($user->isAdmin()) {
        Logger::info('Admin login: ' . $user->email);
    }
});
```

### Dinleyici Durdurma
```php
Events::on('user.delete', function($user) {
    if ($user->hasActiveOrders()) {
        // false dönerek diğer dinleyicileri durdur
        return false;
    }
});
```

### Wildcard Olaylar
```php
// Tüm user olaylarını dinle
Events::on('user.*', function($data, $eventName) {
    Logger::info("User event: $eventName");
});
```

## İpuçları

1. **Öncelik**: Kritik işlemler için düşük öncelik değeri kullanın
2. **Performance**: Ağır işlemler için queue sistemi kullanın
3. **Hata Yönetimi**: Dinleyicilerde try-catch kullanın
4. **Debugging**: Event tetikleme loglarını tutun
5. **Modülerlik**: Her modül kendi event'lerini tanımlasın