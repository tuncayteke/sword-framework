# Sword Framework Documentation

## 📚 Sınıf Dokümantasyonu

### Core Classes
- [Sword](Sword.md) - Ana framework sınıfı
- [Controller](Controller.md) - Base controller sınıfı
- [Router](Router.md) - URL routing sistemi
- [Request](Request.md) - HTTP request yönetimi
- [Response](Response.md) - HTTP response yönetimi
- [View](View.md) - Görünüm sistemi
- [Loader](Loader.md) - Otomatik sınıf yükleme
- [ExceptionHandler](ExceptionHandler.md) - Hata yönetimi

### Database & ORM
- [Model](Model.md) - ORM model sınıfı
- [Database](Database.md) - Veritabanı bağlantısı
- [QueryBuilder](QueryBuilder.md) - SQL query builder
- [DbTabler](DbTabler.md) - Dinamik tablo yönetimi
- [ModelMethod](ModelMethod.md) - Dinamik model metodları

### Security & Validation
- [Validation](Validation.md) - Form doğrulama
- [Security](Security.md) - Güvenlik işlemleri
- [Cryptor](Cryptor.md) - Şifreleme işlemleri
- [Auth](Auth.md) - Kimlik doğrulama

### Session & Cookies
- [Session](Session.md) - Oturum yönetimi
- [Cookie](Cookie.md) - Cookie yönetimi

### Localization & Events
- [Lang](Lang.md) - Çoklu dil desteği
- [Events](Events.md) - Olay sistemi

### File & Upload
- [Upload](Upload.md) - Dosya yükleme
- [Image](Image.md) - Görüntü işleme
- [Thumbnails](Thumbnails.md) - Küçük resim oluşturma

### System & Logging
- [Logger](Logger.md) - Log kayıtları
- [Mailer](Mailer.md) - E-posta gönderimi
- [Monitor](Monitor.md) - Sistem izleme
- [MemoryManager](MemoryManager.md) - Bellek yönetimi
- [Helpers](Helpers.md) - Yardımcı fonksiyonlar

### Theme & Content
- [Theme](Theme.md) - Tema yönetimi
- [Shortcode](Shortcode.md) - Kısa kod sistemi
- [Permalink](Permalink.md) - URL yönetimi
- [Decorator](Decorator.md) - View dekoratörleri

### Cache & Performance
- [Cache](Cache.md) - Önbellek sistemi
- [Throttle](Throttle.md) - Rate limiting

## 🚀 Hızlı Başlangıç

```php
// Framework'ü başlat
require_once 'sword/Sword.php';
Sword::bootstrap();

// Rota tanımla
Sword::routerGet('/', function() {
    echo 'Merhaba Dünya!';
});

// Uygulamayı çalıştır
Sword::start();
```

