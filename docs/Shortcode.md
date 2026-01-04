# Shortcode Class

WordPress benzeri shortcode sistemi. İçerikte kısa kodlar kullanarak dinamik içerik oluşturur.

## Temel Kullanım

```php
// Shortcode ekle
Shortcode::add('button', function($atts) {
    $text = $atts['text'] ?? 'Tıkla';
    $url = $atts['url'] ?? '#';
    return "<a href='{$url}' class='btn'>{$text}</a>";
});

// İçeriği işle
$content = 'Bu bir [button text="Kaydet" url="/save"] shortcode örneği';
$processed = Shortcode::process($content);
```

## Shortcode Ekleme

### add($tag, $callback)
Yeni shortcode ekler.

```php
// Basit shortcode
Shortcode::add('date', function($atts) {
    return date('d.m.Y');
});

// Parametreli shortcode
Shortcode::add('user', function($atts) {
    $id = $atts['id'] ?? 1;
    $user = User::find($id);
    return $user ? $user->name : 'Kullanıcı bulunamadı';
});

// Karmaşık shortcode
Shortcode::add('gallery', function($atts) {
    $id = $atts['id'] ?? null;
    $size = $atts['size'] ?? 'medium';
    
    if (!$id) return '';
    
    $images = Gallery::find($id)->images;
    $html = '<div class="gallery">';
    
    foreach ($images as $image) {
        $html .= "<img src='{$image->url}' class='gallery-{$size}'>";
    }
    
    $html .= '</div>';
    return $html;
});
```

## İçerik İşleme

### process($content)
İçerikteki shortcode'ları işler.

```php
$content = "
Merhaba! Bugün [date] tarihinde 
[user id='5'] kullanıcısı 
[button text='Profil' url='/profile'] 
sayfasını ziyaret etti.
";

$processed = Shortcode::process($content);
echo $processed;
```

## Shortcode Yönetimi

### remove($tag)
Shortcode'u kaldırır.

```php
Shortcode::remove('button');
```

### exists($tag)
Shortcode var mı kontrol eder.

```php
if (Shortcode::exists('gallery')) {
    echo 'Galeri shortcode mevcut';
}
```

### getAll()
Tüm shortcode'ları döndürür.

```php
$shortcodes = Shortcode::getAll();
foreach ($shortcodes as $tag => $callback) {
    echo "Shortcode: {$tag}\n";
}
```

## Shortcode Formatları

### Basit Shortcode
```
[date]
[current_user]
[site_name]
```

### Parametreli Shortcode
```
[button text="Kaydet" url="/save"]
[image id="123" size="large"]
[user id="5" field="email"]
```

### Boolean Parametreler
```
[gallery autoplay responsive]
[video controls muted]
```

## Örnek Kullanımlar

### Blog Shortcode'ları
```php
// Son yazılar
Shortcode::add('recent_posts', function($atts) {
    $limit = $atts['limit'] ?? 5;
    $category = $atts['category'] ?? null;
    
    $query = Post::published()->latest();
    
    if ($category) {
        $query->where('category', $category);
    }
    
    $posts = $query->limit($limit)->get();
    
    $html = '<div class="recent-posts">';
    foreach ($posts as $post) {
        $html .= "
        <article>
            <h3><a href='{$post->url}'>{$post->title}</a></h3>
            <p>{$post->excerpt}</p>
        </article>";
    }
    $html .= '</div>';
    
    return $html;
});

// Kullanım: [recent_posts limit="3" category="teknoloji"]
```

### Medya Shortcode'ları
```php
// Video embed
Shortcode::add('video', function($atts) {
    $url = $atts['url'] ?? '';
    $width = $atts['width'] ?? '100%';
    $height = $atts['height'] ?? '315';
    
    if (strpos($url, 'youtube.com') !== false) {
        $videoId = parse_url($url, PHP_URL_QUERY);
        parse_str($videoId, $params);
        $videoId = $params['v'] ?? '';
        
        return "
        <iframe width='{$width}' height='{$height}' 
                src='https://www.youtube.com/embed/{$videoId}' 
                frameborder='0' allowfullscreen>
        </iframe>";
    }
    
    return "<video width='{$width}' height='{$height}' controls><source src='{$url}'></video>";
});

// Kullanım: [video url="https://youtube.com/watch?v=abc123" width="800"]
```

### Form Shortcode'ları
```php
// İletişim formu
Shortcode::add('contact_form', function($atts) {
    $action = $atts['action'] ?? '/contact';
    $method = $atts['method'] ?? 'POST';
    
    return "
    <form action='{$action}' method='{$method}' class='contact-form'>
        " . Security::csrfField() . "
        <div class='form-group'>
            <label>Ad Soyad</label>
            <input type='text' name='name' required>
        </div>
        <div class='form-group'>
            <label>E-posta</label>
            <input type='email' name='email' required>
        </div>
        <div class='form-group'>
            <label>Mesaj</label>
            <textarea name='message' required></textarea>
        </div>
        <button type='submit'>Gönder</button>
    </form>";
});

// Kullanım: [contact_form action="/send-message"]
```

### E-ticaret Shortcode'ları
```php
// Ürün listesi
Shortcode::add('products', function($atts) {
    $category = $atts['category'] ?? null;
    $limit = $atts['limit'] ?? 12;
    $featured = isset($atts['featured']);
    
    $query = Product::active();
    
    if ($category) {
        $query->where('category_slug', $category);
    }
    
    if ($featured) {
        $query->where('featured', true);
    }
    
    $products = $query->limit($limit)->get();
    
    $html = '<div class="products-grid">';
    foreach ($products as $product) {
        $html .= "
        <div class='product-card'>
            <img src='{$product->image}' alt='{$product->name}'>
            <h3>{$product->name}</h3>
            <p class='price'>{$product->formatted_price}</p>
            <a href='{$product->url}' class='btn'>Detay</a>
        </div>";
    }
    $html .= '</div>';
    
    return $html;
});

// Kullanım: [products category="elektronik" limit="8" featured]
```

### Sosyal Medya Shortcode'ları
```php
// Sosyal paylaşım butonları
Shortcode::add('social_share', function($atts) {
    $url = $atts['url'] ?? $_SERVER['REQUEST_URI'];
    $title = $atts['title'] ?? 'Paylaş';
    
    $encodedUrl = urlencode($url);
    $encodedTitle = urlencode($title);
    
    return "
    <div class='social-share'>
        <a href='https://facebook.com/sharer/sharer.php?u={$encodedUrl}' 
           target='_blank' class='share-facebook'>Facebook</a>
        <a href='https://twitter.com/intent/tweet?url={$encodedUrl}&text={$encodedTitle}' 
           target='_blank' class='share-twitter'>Twitter</a>
        <a href='https://linkedin.com/sharing/share-offsite/?url={$encodedUrl}' 
           target='_blank' class='share-linkedin'>LinkedIn</a>
    </div>";
});

// Kullanım: [social_share url="/blog/my-post" title="Harika Makale"]
```

### Dinamik İçerik Shortcode'ları
```php
// Sayaç
Shortcode::add('counter', function($atts) {
    $start = $atts['start'] ?? 0;
    $end = $atts['end'] ?? 100;
    $duration = $atts['duration'] ?? 2000;
    $suffix = $atts['suffix'] ?? '';
    
    return "
    <span class='counter' 
          data-start='{$start}' 
          data-end='{$end}' 
          data-duration='{$duration}'>
        {$start}{$suffix}
    </span>
    <script>
    // Counter animation code
    </script>";
});

// Kullanım: [counter start="0" end="1000" suffix="+" duration="3000"]
```

### Shortcode Manager
```php
class ShortcodeManager
{
    public static function registerAll()
    {
        // Temel shortcode'lar
        self::registerBasic();
        
        // Medya shortcode'ları
        self::registerMedia();
        
        // Form shortcode'ları
        self::registerForms();
        
        // E-ticaret shortcode'ları
        self::registerEcommerce();
    }
    
    private static function registerBasic()
    {
        Shortcode::add('site_name', function() {
            return Sword::getData('site_name', 'Site Adı');
        });
        
        Shortcode::add('current_year', function() {
            return date('Y');
        });
        
        Shortcode::add('login_url', function() {
            return Sword::url('login');
        });
    }
    
    private static function registerMedia()
    {
        Shortcode::add('image', function($atts) {
            $src = $atts['src'] ?? '';
            $alt = $atts['alt'] ?? '';
            $class = $atts['class'] ?? '';
            
            return "<img src='{$src}' alt='{$alt}' class='{$class}'>";
        });
    }
    
    public static function getShortcodeHelp()
    {
        return [
            'button' => '[button text="Metin" url="/link"]',
            'image' => '[image src="/path/image.jpg" alt="Açıklama"]',
            'video' => '[video url="video.mp4" width="800"]',
            'gallery' => '[gallery id="123" size="medium"]',
            'recent_posts' => '[recent_posts limit="5" category="news"]'
        ];
    }
}

// Başlangıçta kaydet
ShortcodeManager::registerAll();
```

### Shortcode Editor Desteği
```php
class ShortcodeEditor
{
    public static function getAvailableShortcodes()
    {
        $shortcodes = Shortcode::getAll();
        $list = [];
        
        foreach ($shortcodes as $tag => $callback) {
            $list[] = [
                'tag' => $tag,
                'example' => self::getExample($tag),
                'description' => self::getDescription($tag)
            ];
        }
        
        return $list;
    }
    
    private static function getExample($tag)
    {
        $examples = [
            'button' => '[button text="Tıkla" url="/link"]',
            'gallery' => '[gallery id="123"]',
            'video' => '[video url="video.mp4"]',
            'recent_posts' => '[recent_posts limit="5"]'
        ];
        
        return $examples[$tag] ?? "[$tag]";
    }
    
    private static function getDescription($tag)
    {
        $descriptions = [
            'button' => 'Buton oluşturur',
            'gallery' => 'Resim galerisi gösterir',
            'video' => 'Video player ekler',
            'recent_posts' => 'Son yazıları listeler'
        ];
        
        return $descriptions[$tag] ?? 'Özel shortcode';
    }
}
```

## İpuçları

1. **Performans**: Ağır işlemler için cache kullanın
2. **Güvenlik**: Kullanıcı girdilerini her zaman filtreleyin
3. **Hata Yönetimi**: Try-catch kullanarak hataları yakalayın
4. **Dokümantasyon**: Her shortcode için kullanım örneği yazın
5. **Test**: Farklı parametre kombinasyonlarını test edin