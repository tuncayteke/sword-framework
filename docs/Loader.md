# Loader Sınıfı

Loader sınıfı, Sword Framework'te otomatik sınıf yükleme (autoloading) işlemlerini yönetir.

## Temel Kullanım

```php
// Loader'ı başlat
Sword\Loader::init();

// Yol ekle
Sword\Loader::addPath('/path/to/classes');

// Namespace ekle
Sword\Loader::addNamespace('MyApp', '/path/to/myapp');
```

## Özellikler

- **PSR-4 Autoloading**: Modern PHP standartları
- **Namespace Mapping**: Namespace'leri dizinlere eşleme
- **Multiple Paths**: Çoklu sınıf dizinleri
- **Caching**: Sınıf yolu önbellekleme

## Metodlar

### Temel Metodlar

```php
// Loader'ı başlat
Loader::init();

// Sınıf yolu ekle
Loader::addPath(string $path);

// Namespace ekle
Loader::addNamespace(string $namespace, string $path);

// Sınıf yükle
Loader::loadClass(string $className);
```

### Gelişmiş Metodlar

```php
// Önbellek temizle
Loader::clearCache();

// Yüklenen sınıfları listele
$classes = Loader::getLoadedClasses();

// Kayıtlı yolları al
$paths = Loader::getPaths();

// Namespace haritasını al
$namespaces = Loader::getNamespaces();
```

## Yapılandırma

```php
// config/autoload.php
return [
    'paths' => [
        BASE_PATH . '/app/controllers',
        BASE_PATH . '/app/models',
        BASE_PATH . '/app/libraries'
    ],
    'namespaces' => [
        'App\\Controllers' => BASE_PATH . '/app/controllers',
        'App\\Models' => BASE_PATH . '/app/models',
        'App\\Libraries' => BASE_PATH . '/app/libraries'
    ],
    'cache' => true
];
```

## PSR-4 Uyumluluğu

```php
// PSR-4 namespace yapısı
Loader::addNamespace('App\\', BASE_PATH . '/app/');

// Sınıf: App\Controllers\UserController
// Dosya: /app/Controllers/UserController.php
```

## Sınıf Adlandırma Kuralları

```php
// Controller sınıfları
// HomeController -> app/controllers/HomeController.php

// Model sınıfları  
// UserModel -> app/models/UserModel.php

// Library sınıfları
// EmailLibrary -> app/libraries/EmailLibrary.php

// Helper sınıfları
// StringHelper -> app/helpers/StringHelper.php
```

## Önbellek Yönetimi

```php
// Önbelleği etkinleştir
Loader::enableCache(true);

// Önbellek dizini ayarla
Loader::setCacheDir(Sword::getPath('cache') . '/classes');

// Önbelleği temizle
Loader::clearCache();
```

## Hata Ayıklama

```php
// Debug modunu aç
Loader::setDebug(true);

// Yükleme loglarını al
$logs = Loader::getLogs();

// Başarısız yüklemeleri kontrol et
$failed = Loader::getFailedLoads();
```

## Performans İpuçları

```php
// Sık kullanılan sınıfları önce yükle
Loader::preload([
    'Database',
    'Session', 
    'Request',
    'Response'
]);

// Lazy loading için
Loader::setLazyLoading(true);
```

## İlgili Sınıflar

- [Sword](Sword.md) - Ana framework sınıfı
- [Controller](Controller.md) - Controller sınıfları
- [Model](Model.md) - Model sınıfları