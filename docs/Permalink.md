# Permalink Class

SEO dostu URL oluşturucu. Türkçe karakter desteği ve benzersiz slug üretimi sunar.

## Temel Kullanım

```php
// Basit slug oluşturma
$slug = Permalink::slug('Merhaba Dünya!');
// 'merhaba-dunya'

// Benzersiz slug (veritabanı kontrolü ile)
$uniqueSlug = Permalink::uniqueSlug('Makale Başlığı', 'posts', 'slug');
// 'makale-basligi' veya 'makale-basligi-2'
```

## Slug Oluşturma

### slug($string, $separator = '-')
String'i SEO dostu slug'a çevirir.

```php
// Türkçe karakterler
$slug = Permalink::slug('Şehir Çiçeği Ğüzel Öykü');
// 'sehir-cicegi-guzel-oyku'

// Özel ayırıcı
$slug = Permalink::slug('Hello World', '_');
// 'hello_world'

// Karmaşık metin
$slug = Permalink::slug('Bu bir "test" metni! @#$%');
// 'bu-bir-test-metni'
```

**Desteklenen özellikler:**
- Türkçe özel karakterler (ğ, ü, ş, ı, ö, ç)
- HTML entity temizleme
- Çok dilli destek (Intl extension ile)
- ASCII transliteration
- Özel karakter temizleme

### uniqueSlug($title, $table, $column = 'slug', $ignoreId = null)
Veritabanında benzersiz slug oluşturur.

```php
// Yeni kayıt için
$slug = Permalink::uniqueSlug('Makale Başlığı', 'posts');
// 'makale-basligi'

// Güncelleme için (mevcut ID hariç)
$slug = Permalink::uniqueSlug('Makale Başlığı', 'posts', 'slug', 5);
// Mevcut kayıt ID=5 hariç kontrol eder

// Özel sütun adı
$slug = Permalink::uniqueSlug('Kategori Adı', 'categories', 'url_slug');
```

## URL İşlemleri

### extractSlug($url)
URL'den slug çıkarır.

```php
$slug = Permalink::extractSlug('/blog/makale-basligi');
// 'makale-basligi'

$slug = Permalink::extractSlug('https://site.com/kategori/alt-kategori/sayfa');
// 'sayfa'
```

### url($slug, $prefix = '')
Permalink URL'si oluşturur.

```php
// Basit URL
$url = Permalink::url('makale-basligi');
// '/makale-basligi'

// Prefix ile
$url = Permalink::url('makale-basligi', 'blog');
// '/blog/makale-basligi'

$url = Permalink::url('urun-adi', 'products');
// '/products/urun-adi'
```

### slugToTitle($slug)
Slug'ı başlığa çevirir.

```php
$title = Permalink::slugToTitle('makale-basligi');
// 'Makale Basligi'

$title = Permalink::slugToTitle('seo_dostu_url');
// 'Seo Dostu Url'
```

## Örnek Kullanımlar

### Blog Post Slug
```php
class Post extends Model
{
    protected $fillable = ['title', 'content', 'slug'];
    
    public static function create($data)
    {
        // Slug otomatik oluştur
        if (empty($data['slug'])) {
            $data['slug'] = Permalink::uniqueSlug(
                $data['title'], 
                'posts', 
                'slug'
            );
        }
        
        return parent::create($data);
    }
    
    public function update($data)
    {
        // Slug güncelle
        if (isset($data['title']) && empty($data['slug'])) {
            $data['slug'] = Permalink::uniqueSlug(
                $data['title'], 
                'posts', 
                'slug', 
                $this->id
            );
        }
        
        return parent::update($data);
    }
    
    public function getUrlAttribute()
    {
        return Permalink::url($this->slug, 'blog');
    }
}
```

### Kategori Yönetimi
```php
class Category extends Model
{
    protected $fillable = ['name', 'slug', 'parent_id'];
    
    public function generateSlug()
    {
        $this->slug = Permalink::uniqueSlug(
            $this->name, 
            'categories', 
            'slug', 
            $this->id
        );
        
        return $this;
    }
    
    public function getFullSlugAttribute()
    {
        $slugs = [$this->slug];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($slugs, $parent->slug);
            $parent = $parent->parent;
        }
        
        return implode('/', $slugs);
    }
    
    public function getUrlAttribute()
    {
        return Permalink::url($this->full_slug, 'category');
    }
}
```

### E-ticaret Ürün URL'leri
```php
class Product extends Model
{
    protected $fillable = ['name', 'slug', 'category_id', 'sku'];
    
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        
        // Otomatik slug oluştur
        if (empty($this->slug)) {
            $this->attributes['slug'] = Permalink::uniqueSlug(
                $value, 
                'products', 
                'slug'
            );
        }
    }
    
    public function getUrlAttribute()
    {
        return Permalink::url($this->slug, 'product');
    }
    
    public function getCategoryUrlAttribute()
    {
        $category = $this->category;
        return Permalink::url($this->slug, 'category/' . $category->slug);
    }
}
```

### Sayfa Yönetimi
```php
class Page extends Model
{
    protected $fillable = ['title', 'content', 'slug', 'template'];
    
    public static function createFromTitle($title, $content = '')
    {
        $slug = Permalink::uniqueSlug($title, 'pages');
        
        return self::create([
            'title' => $title,
            'content' => $content,
            'slug' => $slug
        ]);
    }
    
    public function updateSlug()
    {
        $this->slug = Permalink::uniqueSlug(
            $this->title, 
            'pages', 
            'slug', 
            $this->id
        );
        
        $this->save();
        return $this;
    }
    
    public static function findBySlug($slug)
    {
        return self::where('slug', $slug)->first();
    }
}
```

### URL Routing Integration
```php
class PermalinkRouter
{
    public static function resolve($uri)
    {
        $slug = Permalink::extractSlug($uri);
        
        // Blog post kontrolü
        if (strpos($uri, '/blog/') === 0) {
            $post = Post::where('slug', $slug)->first();
            if ($post) {
                return ['controller' => 'BlogController', 'action' => 'show', 'params' => [$post->id]];
            }
        }
        
        // Sayfa kontrolü
        $page = Page::where('slug', $slug)->first();
        if ($page) {
            return ['controller' => 'PageController', 'action' => 'show', 'params' => [$page->id]];
        }
        
        // Kategori kontrolü
        $category = Category::where('slug', $slug)->first();
        if ($category) {
            return ['controller' => 'CategoryController', 'action' => 'show', 'params' => [$category->id]];
        }
        
        return null;
    }
}

// Router'da kullanım
Router::get('/{slug}', function($slug) {
    $route = PermalinkRouter::resolve('/' . $slug);
    
    if ($route) {
        $controller = new $route['controller']();
        return call_user_func_array([$controller, $route['action']], $route['params']);
    }
    
    return Response::notFound();
});
```

### Bulk Slug Generator
```php
class SlugGenerator
{
    public static function generateForTable($table, $titleColumn, $slugColumn)
    {
        $records = DB::table($table)
            ->whereNull($slugColumn)
            ->orWhere($slugColumn, '')
            ->get();
        
        $updated = 0;
        
        foreach ($records as $record) {
            $slug = Permalink::uniqueSlug(
                $record->$titleColumn, 
                $table, 
                $slugColumn, 
                $record->id
            );
            
            DB::table($table)
                ->where('id', $record->id)
                ->update([$slugColumn => $slug]);
            
            $updated++;
        }
        
        return $updated;
    }
    
    public static function regenerateAll($table, $titleColumn, $slugColumn)
    {
        $records = DB::table($table)->get();
        $updated = 0;
        
        foreach ($records as $record) {
            $slug = Permalink::uniqueSlug(
                $record->$titleColumn, 
                $table, 
                $slugColumn, 
                $record->id
            );
            
            DB::table($table)
                ->where('id', $record->id)
                ->update([$slugColumn => $slug]);
            
            $updated++;
        }
        
        return $updated;
    }
}

// Kullanım
$updated = SlugGenerator::generateForTable('posts', 'title', 'slug');
echo "{$updated} kayıt güncellendi";
```

### Multilingual Slugs
```php
class MultilingualPermalink
{
    public static function generateSlug($text, $language = 'tr')
    {
        // Dil bazlı özel kurallar
        switch ($language) {
            case 'tr':
                return self::turkishSlug($text);
            case 'en':
                return self::englishSlug($text);
            case 'de':
                return self::germanSlug($text);
            default:
                return Permalink::slug($text);
        }
    }
    
    private static function turkishSlug($text)
    {
        // Türkçe özel kurallar
        $text = str_replace(['İ', 'ı'], ['i', 'i'], $text);
        return Permalink::slug($text);
    }
    
    private static function germanSlug($text)
    {
        // Almanca özel karakterler
        $german = ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss'];
        $text = strtr($text, $german);
        return Permalink::slug($text);
    }
    
    private static function englishSlug($text)
    {
        // İngilizce için standart slug
        return Permalink::slug($text);
    }
}
```

### SEO URL Builder
```php
class SEOUrlBuilder
{
    public static function buildProductUrl($product)
    {
        $category = $product->category;
        $brand = $product->brand;
        
        $parts = [];
        
        if ($category) {
            $parts[] = $category->slug;
        }
        
        if ($brand) {
            $parts[] = $brand->slug;
        }
        
        $parts[] = $product->slug;
        
        return '/' . implode('/', $parts);
    }
    
    public static function buildBlogUrl($post)
    {
        $date = date('Y/m', strtotime($post->created_at));
        return "/blog/{$date}/{$post->slug}";
    }
    
    public static function buildUserUrl($user)
    {
        return "/user/{$user->username}";
    }
}
```

## İpuçları

1. **Türkçe Destek**: Intl extension kurulu olduğundan emin olun
2. **Benzersizlik**: uniqueSlug kullanarak çakışmaları önleyin
3. **SEO**: Kısa ve anlamlı slug'lar oluşturun
4. **Performans**: Slug'ları veritabanında indeksleyin
5. **Güncelleme**: Slug değişikliklerinde redirect kuralları ekleyin