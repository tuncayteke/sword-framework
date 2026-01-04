# Helper Fonksiyonları

Sword Framework, geliştirmeyi kolaylaştıran çok sayıda helper fonksiyonu sunar. Bu fonksiyonlar otomatik olarak yüklenir.

## String Helpers

### str_limit($text, $limit = 100, $end = '...')
Metin uzunluğunu sınırlar.

```php
$text = "Bu çok uzun bir metin örneğidir";
echo str_limit($text, 20); // "Bu çok uzun bir met..."
echo str_limit($text, 15, '>>>'); // "Bu çok uzun bir>>>"
```

### str_random($length = 16)
Rastgele string oluşturur.

```php
$token = str_random(32); // 32 karakter rastgele string
$code = str_random(6);   // 6 karakter kod
```

### slug($string, $separator = '-')
SEO dostu slug oluşturur.

```php
$slug = slug('Türkçe Başlık Örneği'); // "turkce-baslik-ornegi"
$slug = slug('Title Example', '_');    // "title_example"
```

### unique_slug($title, $table, $column = 'slug', $ignoreId = null)
Benzersiz slug oluşturur.

```php
$slug = unique_slug('Makale Başlığı', 'posts'); // "makale-basligi" veya "makale-basligi-2"
$slug = unique_slug('Başlık', 'posts', 'slug', 5); // ID 5'i yoksay
```

## Utility Helpers

### dd(...$vars)
Değişkenleri dump eder ve scripti durdurur (Laravel tarzı).

```php
$user = ['name' => 'John', 'email' => 'john@example.com'];
dd($user); // Değişkeni gösterir ve scripti durdurur
dd($user, $posts, $config); // Birden fazla değişken
```

### dump(...$vars)
Değişkenleri dump eder (scripti durdurmaz).

```php
dump($user); // Değişkeni gösterir, script devam eder
```

### env($key, $default = null)
Environment değişkenini alır.

```php
$dbHost = env('DB_HOST', 'localhost');
$debug = env('APP_DEBUG', false);
$apiKey = env('API_KEY');
```

### config($key, $default = null)
Yapılandırma değerini alır.

```php
$appName = config('app.name', 'Sword Framework');
$cacheDriver = config('cache.driver', 'file');
```

### raw($content)
İçeriği escaping'den kaçırır.

```php
$html = '<strong>Kalın metin</strong>';
echo raw($html); // HTML olarak render edilir
```

### trust($content)
Güvenilir içerik işaretler.

```php
$safeHtml = trust('<p>Bu güvenli HTML</p>');
echo $safeHtml; // Escape edilmez
```

## Encryption Helpers

### cryptor()
Cryptor instance döndürür.

```php
$cryptor = cryptor();
$encrypted = $cryptor->encrypt('secret data');
```

### encrypt($data)
Veriyi şifreler.

```php
$encrypted = encrypt('sensitive information');
$encryptedArray = encrypt(['user_id' => 123, 'role' => 'admin']);
```

### decrypt($encrypted, $unserialize = true)
Şifrelenmiş veriyi çözer.

```php
$decrypted = decrypt($encryptedData);
$array = decrypt($encryptedArray, true);
```

## Event Helpers

### event($name, $payload = null)
Event tetikler.

```php
event('user.created', $user);
event('order.completed', ['order_id' => 123, 'total' => 99.99]);
```

### on($event, $callback, $priority = 10)
Event dinleyici ekler.

```php
on('user.login', function($user) {
    Logger::info('User logged in: ' . $user->email);
});

on('order.created', function($data) {
    // E-posta gönder
}, 5); // Yüksek öncelik
```

### off($event, $callback = null)
Event dinleyici kaldırır.

```php
off('user.login'); // Tüm dinleyicileri kaldır
off('user.login', $specificCallback); // Belirli dinleyiciyi kaldır
```

## Image Helpers

### image($path = null)
Image instance oluşturur.

```php
$image = image('path/to/photo.jpg');
$image->resize(300, 200)->save();

// Zincirleme kullanım
image('photo.jpg')->resize(150, 150)->crop()->save('thumb.jpg');
```

## Logging Helpers

### log($level, $message, $context = [])
Log kaydı oluşturur.

```php
log('info', 'User action performed');
log('error', 'Database connection failed', ['host' => 'localhost']);
log('warning', 'High memory usage detected');
```

## Security Helpers

### csrf_meta()
CSRF meta tag oluşturur.

```php
// HTML head'de
echo csrf_meta(); // <meta name="sword_csrf_token" content="...">
```

### xss($data, $allowHtml = false)
XSS temizleme yapar.

```php
$clean = xss($_POST['content']); // HTML etiketleri temizlenir
$cleanHtml = xss($_POST['content'], true); // Güvenli HTML etiketleri korunur
```

### hash_password($password)
Şifre hash'ler.

```php
$hash = hash_password('user_password_123');
```

### verify_password($password, $hash)
Şifre doğrular.

```php
$isValid = verify_password('user_password_123', $storedHash);
```

## Session Helpers

### session($key = null, $value = null)
Session işlemleri.

```php
// Session sınıfını al
$sessionClass = session();

// Değer set et
session('user_id', 123);

// Değer al
$userId = session('user_id');
```

### flash($key, $value)
Flash mesaj ayarlar.

```php
flash('success', 'İşlem başarıyla tamamlandı');
flash('error', 'Bir hata oluştu');
```

## Upload Helpers

### upload($file = null, $customName = null, $subDir = null)
Dosya yükleme.

```php
// Upload sınıfını al
$uploader = upload();

// Direkt yükleme
$result = upload($_FILES['avatar'], 'user_123', 'avatars');
```

## Auth Helpers

### auth($guard = null)
Auth guard döndürür.

```php
$guard = auth(); // Varsayılan guard
$apiGuard = auth('api'); // API guard
```

### user($guard = null)
Aktif kullanıcıyı döndürür.

```php
$user = user();
if ($user) {
    echo "Hoş geldin " . $user->name;
}
```

### user_id($guard = null)
Kullanıcı ID'sini döndürür.

```php
$userId = user_id();
```

### login($email, $password, $remember = false, $guard = null)
Kullanıcı girişi yapar.

```php
$success = login('user@example.com', 'password123', true);
```

### logout($guard = null)
Kullanıcı çıkışı yapar.

```php
logout();
```

## Shortcode Helpers

### shortcode($tag, $callback)
Shortcode ekler.

```php
shortcode('button', function($atts, $content) {
    $url = $atts['url'] ?? '#';
    $text = $atts['text'] ?? $content;
    return "<a href='{$url}' class='btn'>{$text}</a>";
});
```

### do_shortcode($content)
İçerikteki shortcode'ları işler.

```php
$content = 'Tıklayın: [button url="/contact" text="İletişim"]';
echo do_shortcode($content);
// Çıktı: Tıklayın: <a href='/contact' class='btn'>İletişim</a>
```

## URL Helpers

### asset($path, $type = 'frontend')
Asset URL'si döndürür.

```php
echo asset('css/style.css'); // /content/themes/default/assets/css/style.css
echo asset('js/admin.js', 'admin'); // /content/admin/themes/default/assets/js/admin.js
```

### url($path = '', $params = [])
URL oluşturur.

```php
echo url('admin/users'); // /admin/users
echo url('search', ['q' => 'test']); // /search?q=test
```

## Kullanım Örnekleri

### Form İşleme

```php
// Controller'da
public function store()
{
    // CSRF kontrolü otomatik
    
    // Validation
    $validation = Sword::validate($_POST, [
        'title' => 'required|min:3',
        'content' => 'required'
    ]);
    
    if (!$validation->passes()) {
        flash('error', 'Form hatası var');
        return $this->redirect('/posts/create');
    }
    
    // XSS temizleme
    $data = [
        'title' => xss($_POST['title']),
        'content' => xss($_POST['content'], true), // HTML'e izin ver
        'slug' => unique_slug($_POST['title'], 'posts')
    ];
    
    // Kaydet
    $post = Post::create($data);
    
    // Event tetikle
    event('post.created', $post);
    
    // Flash mesaj
    flash('success', 'Makale oluşturuldu');
    
    return $this->redirect('/posts');
}
```

### Image Upload ve İşleme

```php
public function uploadAvatar()
{
    if (!isset($_FILES['avatar'])) {
        return $this->json(['error' => 'Dosya seçilmedi'], 400);
    }
    
    // Dosya yükle
    $result = upload($_FILES['avatar'], 'avatar_' . user_id(), 'avatars');
    
    if ($result['success']) {
        // Görüntü işle
        $imagePath = $result['path'];
        
        image($imagePath)
            ->resize(200, 200)
            ->crop()
            ->save();
        
        // Thumbnail oluştur
        image($imagePath)
            ->resize(50, 50)
            ->save(str_replace('.jpg', '_thumb.jpg', $imagePath));
        
        // Kullanıcı avatar'ını güncelle
        $user = user();
        $user->avatar = $result['url'];
        $user->save();
        
        return $this->json(['success' => true, 'avatar' => $result['url']]);
    }
    
    return $this->json(['error' => 'Yükleme başarısız'], 500);
}
```

### Event Driven İşlemler

```php
// Event dinleyicileri kaydet
on('user.registered', function($user) {
    // Hoş geldin e-postası
    Sword::mailer()->send($user->email, 'Hoş Geldiniz', 'welcome_template');
    
    // Log kaydet
    log('info', 'New user registered', ['user_id' => $user->id, 'email' => $user->email]);
    
    // Cache temizle
    Sword::cache()->delete('user_count');
});

on('order.completed', function($order) {
    // Fatura e-postası
    Sword::mailer()->send($order->email, 'Siparişiniz Tamamlandı', 'invoice_template');
    
    // Stok güncelle
    foreach ($order->items as $item) {
        $product = Product::find($item->product_id);
        $product->stock -= $item->quantity;
        $product->save();
    }
});

// Event'leri tetikle
$user = User::create($userData);
event('user.registered', $user);

$order = Order::find(123);
event('order.completed', $order);
```

## İpuçları

1. **Performance**: Helper fonksiyonları cache'lenmiş instance'ları kullanır
2. **Security**: XSS ve CSRF helper'larını her zaman kullanın
3. **Debugging**: dd() ve dump() fonksiyonlarını geliştirme sırasında kullanın
4. **Events**: Modüler kod için event sistemini tercih edin
5. **Validation**: Her form işleminde validation helper'larını kullanın

## İlgili Sınıflar

- [Security](Security.md) - Güvenlik işlemleri
- [Upload](Upload.md) - Dosya yükleme
- [Image](Image.md) - Görüntü işleme
- [Auth](Auth.md) - Kimlik doğrulama
- [Events](Events.md) - Olay sistemi