# Decorator Class

View dekoratörleri sistemi. İçeriği otomatik olarak işlemek ve dönüştürmek için kullanılır.

## Temel Kullanım

```php
use Sword\View\Decorator;

// Dekoratör kaydet
Decorator::register('escape', function($content) {
    return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
});

// İçeriği dekore et
$safe = Decorator::apply('escape', $userInput);
```

## Dekoratör Kaydı

### register($name, $callback, $params = [])
Yeni dekoratör kaydeder.

```php
// Basit dekoratör
Decorator::register('uppercase', function($content) {
    return strtoupper($content);
});

// Parametreli dekoratör
Decorator::register('truncate', function($content, $params) {
    $length = $params['length'] ?? 100;
    $suffix = $params['suffix'] ?? '...';
    
    if (mb_strlen($content) <= $length) {
        return $content;
    }
    
    return mb_substr($content, 0, $length) . $suffix;
});
```

## Dekoratör Kullanımı

### apply($name, $content, $data = [])
Dekoratörü uygular.

```php
// Basit kullanım
$result = Decorator::apply('escape', '<script>alert("xss")</script>');

// Parametreli kullanım
$result = Decorator::apply('truncate', $longText, [
    'length' => 150,
    'suffix' => '...'
]);
```

### applyMultiple($names, $content, $data = [])
Birden fazla dekoratörü sırayla uygular.

```php
$content = "  <p>Uzun bir metin...</p>  ";

$result = Decorator::applyMultiple([
    'trim',
    'strip_tags', 
    'truncate'
], $content, ['length' => 50]);
```

## Dekoratör Yönetimi

### get($name)
Dekoratör nesnesini döndürür.

```php
$decorator = Decorator::get('escape');
if ($decorator) {
    $result = $decorator->decorate($content);
}
```

### has($name)
Dekoratör var mı kontrol eder.

```php
if (Decorator::has('markdown')) {
    $html = Decorator::apply('markdown', $markdownText);
}
```

### remove($name)
Dekoratörü kaldırır.

```php
Decorator::remove('old_decorator');
```

### all()
Tüm dekoratörleri döndürür.

```php
$decorators = Decorator::all();
foreach ($decorators as $name => $decorator) {
    echo "Dekoratör: {$name}\n";
}
```

## Yaygın Dekoratörler

### registerCommon()
Yaygın kullanılan dekoratörleri kaydeder.

```php
Decorator::registerCommon();
```

**Kayıtlı dekoratörler:**
- `escape` - HTML karakterlerini güvenli hale getirir
- `strip_tags` - HTML etiketlerini kaldırır
- `markdown` - Basit markdown işleme
- `truncate` - Metni kısaltır
- `date_format` - Tarih formatlar

## Örnek Kullanımlar

### Güvenlik Dekoratörleri
```php
// XSS koruması
Decorator::register('xss_clean', function($content) {
    return Security::xssClean($content);
});

// SQL injection koruması
Decorator::register('sql_escape', function($content) {
    return Security::sqlEscape($content);
});

// Güvenli HTML
Decorator::register('safe_html', function($content) {
    $allowed = '<p><a><b><i><strong><em><ul><ol><li>';
    return strip_tags($content, $allowed);
});
```

### Metin İşleme Dekoratörleri
```php
// Slug oluşturma
Decorator::register('slugify', function($content) {
    return Permalink::slug($content);
});

// Büyük/küçük harf
Decorator::register('title_case', function($content) {
    return ucwords(strtolower($content));
});

// Temizleme
Decorator::register('clean', function($content) {
    return trim(preg_replace('/\s+/', ' ', $content));
});

// Kelime sayısı sınırı
Decorator::register('word_limit', function($content, $params) {
    $limit = $params['limit'] ?? 50;
    $words = explode(' ', $content);
    
    if (count($words) <= $limit) {
        return $content;
    }
    
    return implode(' ', array_slice($words, 0, $limit)) . '...';
});
```

### Formatla Dekoratörleri
```php
// Para formatı
Decorator::register('currency', function($content, $params) {
    $currency = $params['currency'] ?? 'TL';
    $decimals = $params['decimals'] ?? 2;
    
    return number_format($content, $decimals) . ' ' . $currency;
});

// Dosya boyutu
Decorator::register('file_size', function($content) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($content, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
});

// Zaman farkı
Decorator::register('time_ago', function($content) {
    $time = is_numeric($content) ? $content : strtotime($content);
    $diff = time() - $time;
    
    if ($diff < 60) return $diff . ' saniye önce';
    if ($diff < 3600) return floor($diff/60) . ' dakika önce';
    if ($diff < 86400) return floor($diff/3600) . ' saat önce';
    
    return floor($diff/86400) . ' gün önce';
});
```

### Markdown Dekoratörü
```php
Decorator::register('markdown', function($content) {
    // Başlıklar
    $content = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $content);
    $content = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $content);
    $content = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $content);
    
    // Kalın ve italik
    $content = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $content);
    $content = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $content);
    
    // Linkler
    $content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $content);
    
    // Paragraflar
    $content = preg_replace('/\n\n/', '</p><p>', $content);
    $content = '<p>' . $content . '</p>';
    
    return $content;
});
```

### Resim İşleme Dekoratörleri
```php
// Responsive resim
Decorator::register('responsive_image', function($content, $params) {
    $sizes = $params['sizes'] ?? ['sm', 'md', 'lg'];
    $alt = $params['alt'] ?? '';
    
    $srcset = [];
    foreach ($sizes as $size) {
        $url = str_replace('.jpg', "_{$size}.jpg", $content);
        $srcset[] = "{$url} {$size}";
    }
    
    return "<img src='{$content}' srcset='" . implode(', ', $srcset) . "' alt='{$alt}'>";
});

// Lazy loading
Decorator::register('lazy_image', function($content, $params) {
    $alt = $params['alt'] ?? '';
    $placeholder = $params['placeholder'] ?? 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9IiNjY2MiLz48L3N2Zz4=';
    
    return "<img src='{$placeholder}' data-src='{$content}' alt='{$alt}' class='lazy-load'>";
});
```

### Cache Dekoratörü
```php
Decorator::register('cached', function($content, $params) {
    $key = $params['key'] ?? md5($content);
    $ttl = $params['ttl'] ?? 3600;
    
    // Cache'den kontrol et
    if (Cache::has($key)) {
        return Cache::get($key);
    }
    
    // İşle ve cache'le
    $processed = $content; // Burada işleme yapılabilir
    Cache::set($key, $processed, $ttl);
    
    return $processed;
});
```

### Dekoratör Zinciri
```php
class DecoratorChain
{
    private $decorators = [];
    
    public function add($name, $params = [])
    {
        $this->decorators[] = ['name' => $name, 'params' => $params];
        return $this;
    }
    
    public function process($content)
    {
        foreach ($this->decorators as $decorator) {
            $content = Decorator::apply(
                $decorator['name'], 
                $content, 
                $decorator['params']
            );
        }
        
        return $content;
    }
}

// Kullanım
$chain = new DecoratorChain();
$result = $chain->add('escape')
               ->add('truncate', ['length' => 100])
               ->add('markdown')
               ->process($userContent);
```

### Auto Decorator
```php
class AutoDecorator
{
    private static $rules = [];
    
    public static function addRule($pattern, $decorators)
    {
        self::$rules[$pattern] = $decorators;
    }
    
    public static function process($content, $context = [])
    {
        foreach (self::$rules as $pattern => $decorators) {
            if (preg_match($pattern, $context['field'] ?? '')) {
                $content = Decorator::applyMultiple($decorators, $content);
            }
        }
        
        return $content;
    }
}

// Kurallar
AutoDecorator::addRule('/email/', ['escape', 'lowercase']);
AutoDecorator::addRule('/password/', ['hash']);
AutoDecorator::addRule('/content/', ['xss_clean', 'markdown']);

// Kullanım
$email = AutoDecorator::process($input, ['field' => 'email']);
```

### View Integration
```php
// View sınıfında otomatik dekoratör
class EnhancedView extends View
{
    private $autoDecorators = ['escape'];
    
    public function render()
    {
        $content = parent::render();
        
        // Otomatik dekoratörleri uygula
        foreach ($this->autoDecorators as $decorator) {
            $content = Decorator::apply($decorator, $content);
        }
        
        return $content;
    }
    
    public function setAutoDecorators($decorators)
    {
        $this->autoDecorators = $decorators;
        return $this;
    }
}
```

## İpuçları

1. **Performans**: Ağır dekoratörler için cache kullanın
2. **Güvenlik**: Kullanıcı verileri için escape dekoratörü zorunlu
3. **Sıralama**: Dekoratör sırası önemli (önce temizlik, sonra format)
4. **Parametreler**: Esnek parametreler ile yeniden kullanılabilirlik
5. **Test**: Her dekoratörü farklı veri türleri ile test edin