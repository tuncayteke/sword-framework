# MemoryManager Sınıfı

MemoryManager sınıfı, Sword Framework'te bellek yönetimi ve performans optimizasyonu işlemlerini yönetir.

## Temel Kullanım

```php
// Bellek kullanımını kontrol et
$usage = MemoryManager::getUsage();

// Bellek temizliği yap
MemoryManager::cleanup();

// Bellek limitini kontrol et
$limit = MemoryManager::getLimit();
```

## Özellikler

- **Memory Monitoring**: Bellek kullanım takibi
- **Automatic Cleanup**: Otomatik bellek temizliği
- **Memory Limits**: Bellek limit kontrolü
- **Performance Tracking**: Performans izleme

## Metodlar

### Bellek İzleme

```php
// Mevcut bellek kullanımı
$current = MemoryManager::getUsage();

// Peak bellek kullanımı
$peak = MemoryManager::getPeakUsage();

// Bellek limiti
$limit = MemoryManager::getLimit();

// Kullanılabilir bellek
$available = MemoryManager::getAvailable();
```

### Bellek Yönetimi

```php
// Bellek temizliği
MemoryManager::cleanup();

// Garbage collection zorla
MemoryManager::forceGC();

// Bellek optimizasyonu
MemoryManager::optimize();

// Cache temizle
MemoryManager::clearCache();
```

### Performans İzleme

```php
// Performans başlat
MemoryManager::startProfiling();

// Performans durdur
$stats = MemoryManager::stopProfiling();

// Bellek snapshot al
$snapshot = MemoryManager::takeSnapshot();
```

## Yapılandırma

```php
// Bellek limiti ayarla
MemoryManager::setLimit('256M');

// Otomatik temizlik aralığı
MemoryManager::setCleanupInterval(100); // Her 100 request

// Warning threshold
MemoryManager::setWarningThreshold(0.8); // %80

// Critical threshold  
MemoryManager::setCriticalThreshold(0.9); // %90
```

## Otomatik Temizlik

```php
// Otomatik temizliği etkinleştir
MemoryManager::enableAutoCleanup(true);

// Temizlik kuralları
MemoryManager::addCleanupRule(function() {
    // Cache temizle
    Cache::flush();
    
    // Session garbage collection
    Session::gc();
    
    // Temporary files temizle
    MemoryManager::cleanTempFiles();
});
```

## Bellek Uyarıları

```php
// Bellek uyarı callback'i
MemoryManager::onWarning(function($usage, $limit) {
    Logger::warning("High memory usage: {$usage}/{$limit}");
});

// Critical bellek callback'i
MemoryManager::onCritical(function($usage, $limit) {
    Logger::error("Critical memory usage: {$usage}/{$limit}");
    MemoryManager::emergencyCleanup();
});
```

## Performans Raporları

```php
// Detaylı rapor
$report = MemoryManager::getReport();
/*
[
    'current_usage' => '45MB',
    'peak_usage' => '67MB', 
    'limit' => '256MB',
    'available' => '211MB',
    'gc_runs' => 15,
    'cleanup_runs' => 3
]
*/

// Performans metrikleri
$metrics = MemoryManager::getMetrics();
```

## Cache Yönetimi

```php
// Cache boyutunu kontrol et
$cacheSize = MemoryManager::getCacheSize();

// Cache temizlik stratejisi
MemoryManager::setCacheStrategy('lru'); // LRU, FIFO, Random

// Cache limiti
MemoryManager::setCacheLimit('64M');
```

## Hata Ayıklama

```php
// Debug modunu aç
MemoryManager::setDebug(true);

// Bellek leak tespiti
$leaks = MemoryManager::detectLeaks();

// Bellek profili
$profile = MemoryManager::getProfile();
```

## Emergency Cleanup

```php
// Acil durum temizliği
MemoryManager::emergencyCleanup();

// Bu işlem şunları yapar:
// - Tüm cache'leri temizler
// - Garbage collection çalıştırır  
// - Temporary dosyaları siler
// - Session verilerini optimize eder
```

## İlgili Sınıflar

- [Cache](Cache.md) - Önbellek yönetimi
- [Logger](Logger.md) - Performans logları
- [Session](Session.md) - Session yönetimi