# Sword Framework - Genel Bakış

**Sword Framework** - Keskin. Hızlı. Ölümsüz.

Modern PHP web uygulamaları için tasarlanmış, hafif ve güçlü bir MVC framework'üdür.

## 🚀 Neden Sword Framework?

### Basitlik ve Güç
- **Minimal Kurulum**: Tek dosya ile başlayın
- **Sıfır Yapılandırma**: Anında çalışmaya hazır
- **Maksimum Esneklik**: İhtiyacınıza göre genişletin

### Modern PHP Özellikleri
- **PHP 8+ Desteği**: Modern PHP özelliklerini kullanır
- **PSR-4 Autoloading**: Standart sınıf yükleme
- **Composer Uyumlu**: Paket yönetimi desteği

### Kapsamlı Araç Seti
- **MVC Mimarisi**: Temiz kod organizasyonu
- **ORM Sistemi**: Güçlü veritabanı işlemleri
- **Template Engine**: Esnek görünüm sistemi
- **Security**: Yerleşik güvenlik önlemleri

## 🛠️ Temel Özellikler

### 1. Routing Sistemi
```php
// Basit rotalar
Sword::routerGet('/', 'HomeController@index');
Sword::routerPost('/login', 'AuthController@login');

// Parametreli rotalar
Sword::routerGet('/user/:id', 'UserController@show');

// Rota grupları
Sword::routerGroup('/admin', function() {
    Sword::routerGet('/dashboard', 'AdminController@dashboard');
});

// RESTful rotalar
Sword::routerResource('/users', 'UserController');
```

### 2. Database & ORM
```php
// Query Builder
$users = Sword::db()->table('users')
    ->where('active', 1)
    ->orderBy('name')
    ->get();

// ORM Models
class User extends Model {
    protected $table = 'users';
}

$user = User::find(1);
$activeUsers = User::where('active', 1)->get();
```

### 3. View & Template
```php
// Basit view
echo Sword::view('home/index', ['title' => 'Ana Sayfa']);

// Layout sistemi
$view = new View('user/profile', $data, 'layouts/main');

// Section sistemi (Laravel/CodeIgniter tarzı)
<?php $this->extend('layouts/app'); ?>
<?php $this->startSection('content'); ?>
<h1>İçerik</h1>
<?php $this->endSection(); ?>
```

### 4. Security
```php
// CSRF koruması
echo Sword::security()->csrfField();

// XSS temizleme
$clean = Sword::security()->xssClean($userInput);

// Şifre hash
$hash = Sword::security()->hashPassword($password);

// Şifreleme
$encrypted = Sword::cryptor()->encrypt($data);
```

### 5. Cache Sistemi
```php
// Basit cache
Sword::cache()->set('key', 'value', 3600);
$value = Sword::cache()->get('key');

// Remember pattern
$users = Sword::cache()->remember('active_users', 3600, function() {
    return User::where('active', 1)->get();
});
```

### 6. Validation
```php
$validation = Sword::validate($_POST, [
    'email' => 'required|email|unique:users',
    'password' => 'required|min:6',
    'name' => 'required|min:2|max:50'
]);

if ($validation->passes()) {
    // Geçerli veri
} else {
    $errors = $validation->errors();
}
```

### 7. File Upload & Image Processing
```php
// Dosya yükleme
$result = Sword::upload($_FILES['file'], 'custom_name', 'uploads/images');

// Görüntü işleme
Sword::image('path/to/image.jpg')
    ->resize(300, 200)
    ->watermark('logo.png', 'bottom-right')
    ->save();

// Thumbnail oluşturma
$thumbnails = Sword::thumbnails('image.jpg', ['sm', 'md', 'lg']);
```

### 8. Session & Cookie
```php
// Session
Sword::session('user_id', 123);
$userId = Sword::session('user_id');

// Cookie (güvenli)
Sword::cookie('remember_token', $token, 86400, ['secure' => true]);
$token = Sword::cookie('remember_token');
```

### 9. Mail System
```php
$mailer = Sword::mailer();
$mailer->send('user@example.com', 'Konu', 'İçerik');

// Ek dosya ile
$mailer->attach('/path/to/file.pdf')->send($to, $subject, $body);
```

### 10. Event System
```php
// Event dinleyici
Sword::on('user.created', function($user) {
    Sword::mailer()->send($user->email, 'Hoş Geldiniz', $welcomeMessage);
    Sword::logger()->info('New user: ' . $user->email);
});

// Event tetikleme
Sword::trigger('user.created', $newUser);
```

## 🎯 Kullanım Senaryoları

### Web Uygulamaları
- **E-ticaret siteleri**
- **Blog ve CMS sistemleri**
- **Kurumsal web uygulamaları**
- **API servisleri**

### Proje Türleri
- **Küçük-orta ölçekli projeler**
- **Prototip geliştirme**
- **Mikroservisler**
- **Landing page'ler**

## 🏗️ Mimari Yapı

### MVC Pattern
```
app/
├── controllers/     # İş mantığı
├── models/         # Veri modelleri
├── views/          # Görünüm dosyaları
└── Routes.php      # Rota tanımları

content/
├── themes/         # Tema dosyaları
├── storage/        # Cache, logs, sessions
└── uploads/        # Yüklenen dosyalar

sword/
├── Core sınıfları
├── ORM/            # Veritabanı katmanı
├── Cache/          # Önbellek sistemi
└── View/           # Görünüm motoru
```

### Autoloading
```php
// PSR-4 uyumlu
namespace App\Controllers;
class UserController extends Controller { }

// Otomatik yükleme
$controller = new App\Controllers\UserController();
```

## 🔧 Genişletilebilirlik

### Özel Metodlar
```php
// Framework'e özel metod ekle
Sword::map('apiCall', function($endpoint, $data = []) {
    // API çağrısı mantığı
    return $response;
});

// Kullanım
$result = Sword::apiCall('users', ['name' => 'John']);
```

### Middleware Sistemi
```php
// Öncesi filtre
Sword::before('routerDispatch', function() {
    // Auth kontrolü, CSRF doğrulama vb.
});

// Sonrası filtre
Sword::after('routerDispatch', function() {
    // Logging, cleanup vb.
});
```

### Plugin Sistemi
```php
// Plugin yükleme
Sword::loadPlugin('MyPlugin');

// Theme sistemi
Sword::theme()->load('custom-theme');
```

## 📊 Performans Özellikleri

### Optimizasyon
- **Lazy Loading**: İhtiyaç duyulduğunda yükleme
- **Query Caching**: Veritabanı sorgu önbellekleme
- **View Caching**: Görünüm önbellekleme
- **Memory Management**: Bellek optimizasyonu

### Monitoring
```php
// Performans izleme
$stats = Sword::monitor()->getStats();

// Bellek kullanımı
$memory = Sword::memory()->getUsage();

// Sistem durumu
$health = Sword::monitor()->healthCheck();
```

## 🛡️ Güvenlik Özellikleri

### Yerleşik Koruma
- **CSRF Protection**: Cross-site request forgery koruması
- **XSS Prevention**: Cross-site scripting önleme
- **SQL Injection**: Prepared statements ile koruma
- **Input Validation**: Girdi doğrulama
- **Password Hashing**: Güvenli şifre saklama

### Rate Limiting
```php
// İstek sınırlama
Sword::throttle()->attempt('login', 5, 300); // 5 deneme, 5 dakika
```

## 🌐 Çok Dilli Destek

```php
// Dil dosyaları
// app/langs/tr.php
return [
    'welcome' => 'Hoş geldiniz',
    'user' => [
        'not_found' => 'Kullanıcı bulunamadı'
    ]
];

// Kullanım
echo __('welcome');
echo __('user.not_found');
```

## 📱 API Geliştirme

```php
// RESTful API
Sword::routerGroup('/api/v1', function() {
    Sword::routerResource('/users', 'Api\\UserController');
    
    Sword::routerPost('/auth', function() {
        // Authentication logic
        return Sword::response()->json(['token' => $token]);
    });
});

// JSON Response
Sword::response()->json($data)->send();
Sword::response()->success($data)->send();
Sword::response()->error('Hata mesajı', 400)->send();
```

## 🚀 Hızlı Başlangıç

### 1. Kurulum
```bash
# Composer ile
composer create-project sword/framework my-project

# Veya manuel
git clone https://github.com/sword-framework/sword.git
```

### 2. Temel Yapılandırma
```php
// index.php
require_once 'sword/Sword.php';
Sword::bootstrap();

// Basit rota
Sword::routerGet('/', function() {
    echo 'Merhaba Dünya!';
});

Sword::start();
```

### 3. Veritabanı Bağlantısı
```php
// db_config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'myapp');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. İlk Controller
```php
// app/controllers/HomeController.php
class HomeController extends Controller {
    public function index() {
        $data = ['title' => 'Ana Sayfa'];
        return $this->render('home/index', $data);
    }
}
```

## 📚 Öğrenme Kaynakları

### Dokümantasyon
- [Sword.md](Sword.md) - Ana sınıf referansı
- [Router.md](Router.md) - Routing sistemi
- [Model.md](Model.md) - ORM kullanımı
- [View.md](View.md) - Template sistemi
- [Security.md](Security.md) - Güvenlik özellikleri

### Örnek Projeler
- **Blog Sistemi**: Temel CRUD işlemleri
- **E-ticaret**: Gelişmiş özellikler
- **API Servisi**: RESTful API geliştirme

## 🤝 Topluluk ve Destek

### Katkıda Bulunma
- GitHub üzerinden pull request
- Issue raporlama
- Dokümantasyon geliştirme
- Plugin/tema geliştirme

### Lisans
MIT License - Ticari ve açık kaynak projelerde özgürce kullanılabilir.

---

**Sword Framework** ile modern, güvenli ve performanslı web uygulamaları geliştirin. Keskin kodlar, hızlı geliştirme, ölümsüz projeler!