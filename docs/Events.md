# Events - Sword Framework

**Basit ve etkili event sistemi**  
Keskin. Hızlı. Ölümsüz.

## Özellikler

- ✅ **Basit Event Listening** - listen/on, forget/off
- ✅ **Event Dispatching** - dispatch/trigger  
- ✅ **Priority System** - İsteğe bağlı öncelik
- ✅ **Multiple Parameters** - Esnek parametre geçişi
- ✅ **Exception Handling** - Güvenli listener çalıştırma
- ✅ **Class@method Support** - String listener desteği
- ✅ **System Events** - Framework event'leri

## Temel Kullanım

### Event Listener Ekleme

```php
// Basit listener
Events::listen('user.registered', function($user) {
    echo "Hoş geldin, " . $user->name;
});

// Class@method formatı
Events::listen('user.login', 'UserController@onLogin');

// Closure ile
Events::listen('order.created', function($order, $items) {
    // Sipariş işlemleri
    sendEmail($order->email);
    updateInventory($items);
});
```

### Event Tetikleme

```php
// Tek parametre
Events::dispatch('user.registered', $user);

// Çoklu parametre
Events::dispatch('order.created', $order, $items, $total);

// Alias kullanımı
Events::trigger('user.login', $user);
```

## Priority System

```php
// Düşük sayı önce çalışır (varsayılan: 10)
Events::listen('user.login', 'Security@check', 1);      // İlk
Events::listen('user.login', 'Logger@log');             // İkinci (10)
Events::listen('user.login', 'Email@send', 20);        // Üçüncü

// Çalışma sırası: Security@check -> Logger@log -> Email@send
```

## Event Kaldırma

```php
$listener = function($user) {
    echo "Test";
};

Events::listen('test.event', $listener);

// Belirli listener'ı kaldır
Events::forget('test.event', $listener);

// Tüm listener'ları kaldır
Events::forget('test.event');

// Alias
Events::off('test.event');
```

## Sistem Event'leri

### Mevcut System Events

```php
Events::PRE_SYSTEM              // Sistem başlangıcı
Events::BEFORE_CONTROLLER       // Controller öncesi
Events::AFTER_CONTROLLER        // Controller sonrası
Events::POST_SYSTEM             // Sistem sonu
Events::EXCEPTION_THROWN        // Exception atıldığında
Events::BEFORE_RENDER           // Render öncesi
Events::AFTER_RENDER            // Render sonrası
Events::USER_LOGIN              // Kullanıcı girişi
Events::USER_LOGOUT             // Kullanıcı çıkışı
Events::MODEL_CREATED           // Model oluşturuldu
Events::MODEL_UPDATED           // Model güncellendi
Events::MODEL_DELETED           // Model silindi
```

### System Event Helpers

```php
// Framework tarafından otomatik tetiklenir
Events::triggerPreSystem($data);
Events::triggerBeforeController($controller);
Events::triggerAfterController($result);
Events::triggerExceptionThrown($exception);
Events::triggerUserLogin($user);
Events::triggerModelCreated($model);
Events::triggerModelUpdated($model, $changes);
```

### System Event'leri Dinleme

```php
// Exception'ları logla
Events::listen(Events::EXCEPTION_THROWN, function($exception) {
    error_log($exception->getMessage());
});

// Kullanıcı aktivitelerini takip et
Events::listen(Events::USER_LOGIN, function($user) {
    $user->last_login = time();
    $user->save();
});

// Model değişikliklerini logla
Events::listen(Events::MODEL_UPDATED, function($model, $changes) {
    Logger::info("Model updated", [
        'model' => get_class($model),
        'id' => $model->id,
        'changes' => $changes
    ]);
});
```

## Pratik Örnekler

### E-ticaret Sistemi

```php
// Sipariş oluşturulduğunda
Events::listen('order.created', function($order) {
    // Email gönder
    Mailer::send($order->email, 'Sipariş Onayı', $order);
}, 10);

Events::listen('order.created', function($order) {
    // Stok güncelle
    foreach ($order->items as $item) {
        Product::find($item->product_id)->decrementStock($item->quantity);
    }
}, 20);

Events::listen('order.created', function($order) {
    // Admin'e bildir
    Mailer::send('admin@site.com', 'Yeni Sipariş', $order);
}, 30);

// Sipariş oluştur ve event'i tetikle
$order = Order::create($orderData);
Events::dispatch('order.created', $order);
```

### Blog Sistemi

```php
// Makale yayınlandığında
Events::listen('post.published', function($post) {
    // Cache temizle
    Cache::forget('recent_posts');
    Cache::forget('post_' . $post->id);
});

Events::listen('post.published', function($post) {
    // Sosyal medyada paylaş
    SocialMedia::share($post);
});

Events::listen('post.published', function($post) {
    // Abone olanlara bildir
    Newsletter::notify($post);
});

// Makale yayınla
$post->status = 'published';
$post->save();
Events::dispatch('post.published', $post);
```

### Kullanıcı Sistemi

```php
// Kullanıcı kaydı
Events::listen('user.registered', 'UserController@sendWelcomeEmail', 10);
Events::listen('user.registered', 'UserController@createProfile', 20);
Events::listen('user.registered', 'AnalyticsController@trackSignup', 30);

// Kullanıcı girişi
Events::listen('user.login', function($user) {
    $user->last_login = time();
    $user->login_count++;
    $user->save();
});

// Kullanıcı çıkışı
Events::listen('user.logout', function($user) {
    Session::destroy();
    Logger::info("User {$user->id} logged out");
});
```

## Utility Methods

```php
// Listener var mı kontrol et
if (Events::hasListeners('user.registered')) {
    Events::dispatch('user.registered', $user);
}

// Listener'ları getir
$listeners = Events::getListeners('user.login');

// Tüm event'leri temizle
Events::clear();
```

## Class@method Listener

```php
class UserEventHandler
{
    public function onRegistered($user)
    {
        // Hoş geldin emaili gönder
        Mailer::send($user->email, 'Hoş Geldiniz!');
    }
    
    public function onLogin($user)
    {
        // Son giriş zamanını güncelle
        $user->last_login = time();
        $user->save();
    }
}

// Listener'ları kaydet
Events::listen('user.registered', 'UserEventHandler@onRegistered');
Events::listen('user.login', 'UserEventHandler@onLogin');
```

## Exception Handling

```php
Events::listen('risky.operation', function($data) {
    // Bu hata verse bile diğer listener'lar çalışır
    throw new Exception('Something went wrong');
});

Events::listen('risky.operation', function($data) {
    // Bu çalışmaya devam eder
    Logger::info('Operation completed');
});

// Hata loglanır ama uygulama durmuyor
Events::dispatch('risky.operation', $data);
```

## Event Durdurma

```php
Events::listen('payment.process', function($payment) {
    if ($payment->amount > 10000) {
        // Büyük ödemeler için manuel onay gerekli
        return false; // Event'i durdur
    }
});

Events::listen('payment.process', function($payment) {
    // Bu çalışmaz eğer amount > 10000
    processPayment($payment);
});
```

## Best Practices

### 1. Event İsimlendirme

```php
// İyi: entity.action formatı
'user.registered'
'user.login'
'order.created'
'post.published'
'payment.completed'

// Kötü: Tutarsız isimlendirme
'userRegistered'
'new_order'
'PostPublish'
```

### 2. Priority Kullanımı

```php
// Kritik işlemler önce (1-5)
Events::listen('user.login', 'Security@validateIp', 1);
Events::listen('user.login', 'Security@checkBan', 2);

// Normal işlemler (10 - varsayılan)
Events::listen('user.login', 'User@updateLastLogin');
Events::listen('user.login', 'Logger@logActivity');

// Temizlik işlemleri son (15-20)
Events::listen('user.login', 'Session@cleanup', 20);
```

### 3. Basit Tutun

```php
// İyi: Basit ve anlaşılır
Events::listen('order.created', function($order) {
    sendOrderEmail($order);
});

// Kötü: Fazla karmaşık
Events::listen('order.created', function($order) {
    if ($order->total > 100 && $order->user->vip && date('H') < 18) {
        // Karmaşık mantık
    }
});
```

## Performans İpuçları

1. **Gereksiz listener eklemeyin**
2. **Ağır işlemleri queue'ya alın** 
3. **Exception'ları handle edin**
4. **Priority'yi gereksiz kullanmayın**

## Sword Framework Entegrasyonu

Events sınıfı Sword Framework'e entegre edilmiştir:

```php
// Framework başlangıcında
Events::triggerPreSystem();

// Controller çalıştırılmadan önce
Events::triggerBeforeController($controller);

// Controller çalıştırıldıktan sonra
Events::triggerAfterController($result);

// View render edilmeden önce
Events::triggerBeforeRender($viewData);

// Sistem sonunda
Events::triggerPostSystem();
```

Bu sayede framework'ün her aşamasında event'leri dinleyebilir ve özel işlemler yapabilirsiniz.

---

**Sword Framework** - Keskin. Hızlı. Ölümsüz.