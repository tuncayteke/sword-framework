# Cache Sınıfı

Cache sınıfı, Sword Framework'te önbellek yönetimi işlemlerini yönetir. Farklı cache sürücüleri destekler.

## Temel Kullanım

```php
// Cache'e veri kaydet
Sword::cache()->set('user_1', $userData, 3600);

// Cache'den veri al
$userData = Sword::cache()->get('user_1');

// Cache'de var mı kontrol et
if (Sword::cache()->has('user_1')) {
    // Cache'de var
}
```

## Özellikler

- **Multiple Drivers**: File, Memory, Redis desteği
- **TTL Support**: Zaman bazlı geçerlilik
- **Encryption**: Şifrelenmiş cache desteği
- **Tagging**: Cache etiketleme sistemi
- **Serialization**: Otomatik veri serileştirme

## Cache Sürücüleri

### File Cache

```php
// File cache kullan
$cache = Sword::cache('file');

// Veri kaydet
$cache->set('key', 'value', 3600);

// Veri al
$value = $cache->get('key');
```

### Memory Cache

```php
// Memory cache kullan (APCu)
$cache = Sword::cache('memory');

// Veri kaydet
$cache->set('session_data', $sessionData, 1800);
```

### Model Cache

```php
// Model cache kullan
$cache = Sword::cache('model');

// Model sonuçlarını cache'le
$users = $cache->remember('active_users', 3600, function() {
    return UserModel::where('active', 1)->get();
});
```

## Metodlar

### Temel Metodlar

```php
// Veri kaydet
$cache->set(string $key, mixed $value, int $ttl = 0);

// Veri al
$cache->get(string $key, mixed $default = null);

// Veri var mı
$cache->has(string $key);

// Veri sil
$cache->delete(string $key);

// Tüm cache'i temizle
$cache->flush();
```

### Gelişmiş Metodlar

```php
// Remember pattern
$data = $cache->remember('expensive_query', 3600, function() {
    return Database::query('SELECT * FROM big_table');
});

// Forever cache (süresiz)
$cache->forever('config', $configData);

// Increment/Decrement
$cache->increment('page_views');
$cache->decrement('stock_count', 5);

// Multiple operations
$cache->setMultiple([
    'key1' => 'value1',
    'key2' => 'value2'
], 3600);

$values = $cache->getMultiple(['key1', 'key2']);
```

## Cache Etiketleme

```php
// Etiketli cache
$cache->tags(['users', 'profiles'])->set('user_1', $userData, 3600);

// Etikete göre temizle
$cache->tags(['users'])->flush();

// Çoklu etiket
$cache->tags(['posts', 'categories', 'featured'])
      ->set('featured_posts', $posts, 7200);
```

## Yapılandırma

```php
// Cache ayarları
Sword::setData('cache_driver', 'file');
Sword::setData('cache_ttl', 3600);
Sword::setData('cache_prefix', 'sword_');

// File cache ayarları
Sword::setData('cache_path', BASE_PATH . '/content/storage/cache');

// Encryption ayarları
Sword::setData('cache_encrypt', true);
```

## Cache Stratejileri

### Lazy Loading

```php
// Lazy loading pattern
function getUser($id) {
    return Sword::cache()->remember("user_{$id}", 3600, function() use ($id) {
        return UserModel::find($id);
    });
}
```

### Write-Through

```php
// Write-through pattern
function updateUser($id, $data) {
    $user = UserModel::find($id);
    $user->update($data);
    
    // Cache'i güncelle
    Sword::cache()->set("user_{$id}", $user, 3600);
    
    return $user;
}
```

### Cache Aside

```php
// Cache aside pattern
function getProduct($id) {
    $product = Sword::cache()->get("product_{$id}");
    
    if (!$product) {
        $product = ProductModel::find($id);
        Sword::cache()->set("product_{$id}", $product, 3600);
    }
    
    return $product;
}
```

## Performance İpuçları

```php
// Batch operations kullan
$cache->setMultiple([
    'user_1' => $user1,
    'user_2' => $user2,
    'user_3' => $user3
], 3600);

// Compression kullan
$cache->set('large_data', $largeData, 3600, ['compress' => true]);

// Cache warming
function warmCache() {
    $popularProducts = ProductModel::popular()->get();
    foreach ($popularProducts as $product) {
        $cache->set("product_{$product->id}", $product, 7200);
    }
}
```

## Cache Events

```php
// Cache events
Events::on('cache.hit', function($key, $value) {
    Logger::info("Cache hit: {$key}");
});

Events::on('cache.miss', function($key) {
    Logger::info("Cache miss: {$key}");
});

Events::on('cache.write', function($key, $value, $ttl) {
    Logger::info("Cache write: {$key} (TTL: {$ttl})");
});
```

## Debugging

```php
// Cache istatistikleri
$stats = $cache->getStats();
/*
[
    'hits' => 150,
    'misses' => 25,
    'hit_ratio' => 0.857,
    'memory_usage' => '45MB'
]
*/

// Cache keys listele
$keys = $cache->getKeys();

// Cache boyutu
$size = $cache->getSize();
```

## İlgili Sınıflar

- [Cryptor](Cryptor.md) - Cache şifreleme
- [MemoryManager](MemoryManager.md) - Bellek yönetimi
- [Model](Model.md) - Model cache