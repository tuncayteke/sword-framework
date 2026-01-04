# Sword Framework

**Keskin. Hızlı. Ölümsüz.**

Modern PHP web uygulamaları için tasarlanmış, hafif ve güçlü bir MVC framework'üdür. Fındık kırmak için balyoz kullanmak istemeyenlere özel geliştirilmiştir. En iyi ve en güçlü php frameworklerdeki en çok kullanılan özellikler ve wordpress tema yönetimine benzer bir yapıyla, geliştiricilerinin işinin kolaylaştırılması amaçlanmıştır.

## 🚀 Neden Sword Framework?

### Basitlik ve Güç

- **Minimal Kurulum**: Tek dosya ile başlayın
- **Sıfır Yapılandırma**: Anında çalışmaya hazır
- **Maksimum Esneklik**: İhtiyacınıza göre genişletin

### Modern PHP Özellikleri

- **PHP 8+ Desteği**: Modern PHP özelliklerini kullanır
- **PSR-4 Autoloading**: Standart sınıf yükleme
- **Composer Uyumlu**: Paket yönetimi desteği

## 🛠️ Temel Özellikler

### Routing Sistemi

```php
// Basit rotalar
Sword::routerGet('/', 'HomeController@index');
Sword::routerPost('/login', 'AuthController@login');

// Parametreli rotalar
Sword::routerGet('/user/:id', 'UserController@show');

// RESTful rotalar
Sword::routerResource('/users', 'UserController');
```

### Database & ORM

```php
// Query Builder
$users = Sword::db()->table('users')
    ->where('active', 1)
    ->orderBy('name')
    ->get();

// ORM Models
$user = User::find(1);
$activeUsers = User::where('active', 1)->get();
```

### View & Template

```php
// Basit view
echo Sword::view('home/index', ['title' => 'Ana Sayfa']);

// Section sistemi
<?php $this->extend('layouts/app'); ?>
<?php $this->startSection('content'); ?>
<h1>İçerik</h1>
<?php $this->endSection(); ?>
```

### Security

```php
// CSRF koruması
echo Sword::security()->csrfField();

// XSS temizleme
$clean = Sword::security()->xssClean($userInput);

// Şifreleme
$encrypted = Sword::cryptor()->encrypt($data);
```

## 🚀 Hızlı Başlangıç

### 1. Kurulum

```bash
# Download
İndir
Zipten Çkar
docs klasörünü ve readme.md dosyalarını silin

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

### 3. İlk Controller

```php
// app/controllers/HomeController.php
class HomeController extends Controller {
    public function index() {
        $data = ['title' => 'Ana Sayfa'];
        return $this->render('home/index', $data);
    }
}
```

## 🎯 Kullanım Senaryoları

- **E-ticaret siteleri**
- **Blog ve CMS sistemleri**
- **Kurumsal web uygulamaları**
- **API servisleri**
- **Prototip geliştirme**
- **Mikroservisler**

## 🏗️ Mimari Yapı

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

## 🔧 Genişletilebilirlik

### Özel Metodlar

```php
// Framework'e özel metod ekle
Sword::map('apiCall', function($endpoint, $data = []) {
    return $response;
});

// Kullanım
$result = Sword::apiCall('users', ['name' => 'John']);
```

### Event System

```php
// Event dinleyici
Sword::on('user.created', function($user) {
    Sword::mailer()->send($user->email, 'Hoş Geldiniz', $message);
});

// Event tetikleme
Sword::trigger('user.created', $newUser);
```

## 🛡️ Güvenlik Özellikleri

- **CSRF Protection**: Cross-site request forgery koruması
- **XSS Prevention**: Cross-site scripting önleme
- **SQL Injection**: Prepared statements ile koruma
- **Input Validation**: Kapsamlı form doğrulama
- **Password Hashing**: Güvenli şifre saklama
- **Rate Limiting**: İstek sınırlama

## 📊 Performans

- **Lazy Loading**: İhtiyaç duyulduğunda yükleme
- **Query Caching**: Veritabanı sorgu önbellekleme
- **View Caching**: Görünüm önbellekleme
- **Memory Management**: Bellek optimizasyonu

## 📚 Dokümantasyon

### Core Classes

- [Sword](docs/Sword.md) - Ana framework sınıfı
- [Controller](docs/Controller.md) - Base controller sınıfı
- [Router](docs/Router.md) - URL routing sistemi
- [Request](docs/Request.md) - HTTP request yönetimi
- [Response](docs/Response.md) - HTTP response yönetimi
- [View](docs/View.md) - Görünüm sistemi

### Database & ORM

- [Model](docs/Model.md) - ORM model sınıfı
- [Database](docs/Database.md) - Veritabanı bağlantısı
- [QueryBuilder](docs/QueryBuilder.md) - SQL query builder

### Security & Validation

- [Validation](docs/Validation.md) - Form doğrulama
- [Security](docs/Security.md) - Güvenlik işlemleri
- [Cryptor](docs/Cryptor.md) - Şifreleme işlemleri
- [Auth](docs/Auth.md) - Kimlik doğrulama

### System & Tools

- [Cache](docs/Cache.md) - Önbellek sistemi
- [Session](docs/Session.md) - Oturum yönetimi
- [Cookie](docs/Cookie.md) - Cookie yönetimi
- [Upload](docs/Upload.md) - Dosya yükleme
- [Image](docs/Image.md) - Görüntü işleme
- [Mailer](docs/Mailer.md) - E-posta gönderimi
- [Logger](docs/Logger.md) - Log kayıtları
- [Helpers](docs/Helpers.md) - Yardımcı fonksiyonlar

[📖 Tüm Dokümantasyon](docs/README.md)

## 🌟 Örnekler

### Basit Blog

```php
// Makale listesi
Sword::routerGet('/blog', function() {
    $posts = Sword::model('Post')->where('published', 1)->get();
    echo Sword::view('blog/index', ['posts' => $posts]);
});

// Makale detay
Sword::routerGet('/blog/:slug', function($slug) {
    $post = Sword::model('Post')->where('slug', $slug)->first();
    echo Sword::view('blog/post', ['post' => $post]);
});
```

### API Endpoint

```php
Sword::routerPost('/api/users', function() {
    $validation = Sword::validate($_POST, [
        'name' => 'required|min:2',
        'email' => 'required|email|unique:users'
    ]);

    if ($validation->passes()) {
        $user = Sword::model('User')->create($_POST);
        Sword::response()->json(['success' => true, 'user' => $user])->send();
    } else {
        Sword::response()->validationError($validation->errors())->send();
    }
});
```

### Cache Kullanımı

```php
// Pahalı sorguyu cache'le
$popularPosts = Sword::cache()->remember('popular_posts', 3600, function() {
    return Sword::model('Post')
        ->where('views', '>', 1000)
        ->orderBy('views', 'DESC')
        ->limit(10)
        ->get();
});
```

## 🤝 Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit edin (`git commit -m 'Add amazing feature'`)
4. Push edin (`git push origin feature/amazing-feature`)
5. Pull Request açın

## 📄 Lisans

MIT License - Ticari ve açık kaynak projelerde özgürce kullanılabilir.

## 🔗 Bağlantılar

- **GitHub**: [github.com/tuncayteke/sword-framework](https://github.com/tuncayteke/sword-framework)

---

**Sword Framework** ile modern, güvenli ve performanslı web uygulamaları geliştirin.

_Keskin kodlar, hızlı geliştirme, ölümsüz projeler!_
