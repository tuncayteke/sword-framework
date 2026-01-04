# Thumbnails Class

Küçük resim oluşturma işlemlerini yönetir. Çoklu boyut desteği ve otomatik küçük resim üretimi sunar.

## Temel Kullanım

```php
$thumbnails = new Thumbnails();

// Tüm boyutlarda küçük resim oluştur
$result = $thumbnails->generate('image.jpg');

// Belirli boyutlarda küçük resim oluştur
$result = $thumbnails->generate('image.jpg', ['xs', 'md']);
```

## Varsayılan Boyutlar

- **XS**: 150x150px
- **SM**: 300x300px  
- **MD**: 600x600px
- **LG**: 800x800px

## Küçük Resim Oluşturma

### generate($imagePath, $sizes = ['xs', 'sm', 'md', 'lg'])
Küçük resimleri oluşturur.

```php
// Tüm boyutlar
$result = $thumbnails->generate('photo.jpg');

// Sadece küçük boyutlar
$result = $thumbnails->generate('photo.jpg', ['xs', 'sm']);

// Tek boyut
$result = $thumbnails->generate('photo.jpg', ['md']);
```

**Dönen sonuç:**
```php
[
    'original' => 'photo.jpg',
    'xs' => 'photo_xs.jpg',
    'sm' => 'photo_sm.jpg', 
    'md' => 'photo_md.jpg',
    'lg' => 'photo_lg.jpg'
]
```

### getThumbPath($imagePath, $size)
Küçük resim yolunu döndürür.

```php
$xsPath = $thumbnails->getThumbPath('photo.jpg', 'xs');
// photo_xs.jpg

$mdPath = $thumbnails->getThumbPath('images/gallery.png', 'md');
// images/gallery_md.png
```

## Boyut Yapılandırması

### XS Boyutu Ayarlama

```php
$thumbnails->setXsWidth(100)
           ->setXsHeight(100);
```

### SM Boyutu Ayarlama

```php
$thumbnails->setSmWidth(250)
           ->setSmHeight(250);
```

### MD Boyutu Ayarlama

```php
$thumbnails->setMdWidth(500)
           ->setMdHeight(500);
```

### LG Boyutu Ayarlama

```php
$thumbnails->setLgWidth(1000)
           ->setLgHeight(1000);
```

## Kalite ve Metod Ayarlama

### setQuality($quality)
Küçük resim kalitesini ayarlar.

```php
$thumbnails->setQuality(95); // Yüksek kalite
$thumbnails->setQuality(70); // Orta kalite
```

### setMethod($method)
Küçük resim metodunu ayarlar.

```php
$thumbnails->setMethod('fit');  // En-boy oranını korur
$thumbnails->setMethod('crop'); // Tam boyut, kırpar
```

## Örnek Kullanımlar

### Galeri Küçük Resimleri
```php
class GalleryManager
{
    public function uploadImage($file)
    {
        // Dosyayı yükle
        $upload = new Upload();
        $result = $upload->upload($file, null, 'gallery');
        
        if ($result) {
            // Küçük resimleri oluştur
            $thumbnails = new Thumbnails();
            $thumbs = $thumbnails->generate($result['path']);
            
            // Veritabanına kaydet
            $gallery = Gallery::create([
                'original' => $result['url'],
                'xs_thumb' => str_replace($result['path'], $thumbs['xs'], $result['url']),
                'sm_thumb' => str_replace($result['path'], $thumbs['sm'], $result['url']),
                'md_thumb' => str_replace($result['path'], $thumbs['md'], $result['url']),
                'lg_thumb' => str_replace($result['path'], $thumbs['lg'], $result['url'])
            ]);
            
            return $gallery;
        }
        
        return false;
    }
}
```

### Ürün Resmi İşleme
```php
class ProductImageProcessor
{
    public function processProductImage($imagePath, $productId)
    {
        $thumbnails = new Thumbnails();
        
        // E-ticaret için özel boyutlar
        $thumbnails->setXsWidth(80)   // Liste görünümü
                   ->setXsHeight(80)
                   ->setSmWidth(200)  // Kart görünümü
                   ->setSmHeight(200)
                   ->setMdWidth(400)  // Detay sayfası
                   ->setMdHeight(400)
                   ->setLgWidth(800)  // Zoom görünümü
                   ->setLgHeight(800)
                   ->setQuality(85)
                   ->setMethod('crop');
        
        $thumbs = $thumbnails->generate($imagePath);
        
        // Ürün resimlerini güncelle
        Product::where('id', $productId)->update([
            'image_original' => $thumbs['original'],
            'image_xs' => $thumbs['xs'],
            'image_sm' => $thumbs['sm'],
            'image_md' => $thumbs['md'],
            'image_lg' => $thumbs['lg']
        ]);
        
        return $thumbs;
    }
}
```

### Avatar İşleme
```php
class AvatarProcessor
{
    public function processAvatar($imagePath, $userId)
    {
        $thumbnails = new Thumbnails();
        
        // Avatar için kare boyutlar
        $thumbnails->setXsWidth(32)   // Mini avatar
                   ->setXsHeight(32)
                   ->setSmWidth(64)   // Küçük avatar
                   ->setSmHeight(64)
                   ->setMdWidth(128)  // Orta avatar
                   ->setMdHeight(128)
                   ->setLgWidth(256)  // Büyük avatar
                   ->setLgHeight(256)
                   ->setMethod('crop'); // Kare kırpma
        
        // Sadece gerekli boyutları oluştur
        $thumbs = $thumbnails->generate($imagePath, ['xs', 'sm', 'md']);
        
        // Kullanıcı avatarını güncelle
        User::where('id', $userId)->update([
            'avatar_xs' => $thumbs['xs'],
            'avatar_sm' => $thumbs['sm'],
            'avatar_md' => $thumbs['md']
        ]);
        
        return $thumbs;
    }
}
```

### Blog Post Resimleri
```php
class BlogImageProcessor
{
    public function processFeaturedImage($imagePath, $postId)
    {
        $thumbnails = new Thumbnails();
        
        // Blog için dikdörtgen boyutlar
        $thumbnails->setXsWidth(150)  // Sidebar
                   ->setXsHeight(100)
                   ->setSmWidth(300)  // Kart görünümü
                   ->setSmHeight(200)
                   ->setMdWidth(600)  // Post başlığı
                   ->setMdHeight(400)
                   ->setLgWidth(1200) // Tam genişlik
                   ->setLgHeight(800)
                   ->setQuality(80)
                   ->setMethod('crop');
        
        $thumbs = $thumbnails->generate($imagePath);
        
        // Post'u güncelle
        Post::where('id', $postId)->update([
            'featured_image' => $thumbs['original'],
            'featured_image_xs' => $thumbs['xs'],
            'featured_image_sm' => $thumbs['sm'],
            'featured_image_md' => $thumbs['md'],
            'featured_image_lg' => $thumbs['lg']
        ]);
        
        return $thumbs;
    }
}
```

### Responsive Resim Seti
```php
class ResponsiveThumbnails
{
    public function generateResponsiveSet($imagePath)
    {
        $thumbnails = new Thumbnails();
        
        // Responsive breakpoint'ler için boyutlar
        $thumbnails->setXsWidth(480)   // Mobile
                   ->setXsHeight(320)
                   ->setSmWidth(768)   // Tablet
                   ->setSmHeight(512)
                   ->setMdWidth(1024)  // Desktop
                   ->setMdHeight(683)
                   ->setLgWidth(1920)  // Large desktop
                   ->setLgHeight(1280)
                   ->setMethod('fit'); // En-boy oranını koru
        
        $thumbs = $thumbnails->generate($imagePath);
        
        // Responsive HTML oluştur
        $html = '<picture>';
        $html .= '<source media="(min-width: 1200px)" srcset="' . $thumbs['lg'] . '">';
        $html .= '<source media="(min-width: 768px)" srcset="' . $thumbs['md'] . '">';
        $html .= '<source media="(min-width: 480px)" srcset="' . $thumbs['sm'] . '">';
        $html .= '<img src="' . $thumbs['xs'] . '" alt="Responsive image">';
        $html .= '</picture>';
        
        return [
            'thumbs' => $thumbs,
            'html' => $html
        ];
    }
}
```

### Batch Küçük Resim Oluşturma
```php
class BatchThumbnailGenerator
{
    public function generateForDirectory($directory, $sizes = ['xs', 'sm', 'md'])
    {
        $thumbnails = new Thumbnails();
        $thumbnails->setQuality(80);
        
        $imageFiles = glob($directory . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        $processed = [];
        $errors = [];
        
        foreach ($imageFiles as $imagePath) {
            try {
                $thumbs = $thumbnails->generate($imagePath, $sizes);
                $processed[] = [
                    'original' => $imagePath,
                    'thumbs' => $thumbs
                ];
                
            } catch (Exception $e) {
                $errors[] = [
                    'file' => $imagePath,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'processed' => $processed,
            'errors' => $errors,
            'total' => count($imageFiles),
            'success_count' => count($processed),
            'error_count' => count($errors)
        ];
    }
}
```

### Özel Boyut Seti
```php
class CustomThumbnailSizes
{
    public function generateCustomSizes($imagePath, $customSizes)
    {
        $results = ['original' => $imagePath];
        
        foreach ($customSizes as $sizeName => $dimensions) {
            $thumbnails = new Thumbnails();
            
            // Özel boyutları ayarla
            $thumbnails->setXsWidth($dimensions['width'])
                       ->setXsHeight($dimensions['height'])
                       ->setQuality($dimensions['quality'] ?? 80)
                       ->setMethod($dimensions['method'] ?? 'crop');
            
            // Sadece XS boyutunu oluştur (özel boyut olarak)
            $thumbs = $thumbnails->generate($imagePath, ['xs']);
            
            // Dosyayı özel isimle yeniden adlandır
            $customPath = $thumbnails->getThumbPath($imagePath, $sizeName);
            rename($thumbs['xs'], $customPath);
            
            $results[$sizeName] = $customPath;
        }
        
        return $results;
    }
}

// Kullanım
$customSizes = [
    'card' => ['width' => 350, 'height' => 200, 'method' => 'crop'],
    'banner' => ['width' => 1200, 'height' => 300, 'method' => 'crop'],
    'square' => ['width' => 400, 'height' => 400, 'method' => 'crop']
];

$processor = new CustomThumbnailSizes();
$result = $processor->generateCustomSizes('image.jpg', $customSizes);
```

### Lazy Loading Desteği
```php
class LazyLoadThumbnails
{
    public function generateWithPlaceholder($imagePath)
    {
        $thumbnails = new Thumbnails();
        
        // Normal küçük resimleri oluştur
        $thumbs = $thumbnails->generate($imagePath);
        
        // Çok küçük placeholder oluştur (blur için)
        $thumbnails->setXsWidth(20)
                   ->setXsHeight(20)
                   ->setQuality(50);
        
        $placeholder = $thumbnails->generate($imagePath, ['xs']);
        
        return [
            'thumbs' => $thumbs,
            'placeholder' => $placeholder['xs'],
            'lazy_html' => $this->generateLazyHTML($thumbs, $placeholder['xs'])\n        ];\n    }\n    \n    private function generateLazyHTML($thumbs, $placeholder)\n    {\n        return '<img src=\"' . $placeholder . '\" \n                     data-src=\"' . $thumbs['md'] . '\" \n                     data-srcset=\"' . $thumbs['xs'] . ' 150w, \n                                  ' . $thumbs['sm'] . ' 300w, \n                                  ' . $thumbs['md'] . ' 600w\" \n                     class=\"lazy-load\" \n                     alt=\"Lazy loaded image\">';\n    }\n}\n```\n\n## Yapılandırma Dosyası\n\n```php\n// config/thumbnails.php\nreturn [\n    'sizes' => [\n        'xs' => ['width' => 150, 'height' => 150],\n        'sm' => ['width' => 300, 'height' => 300],\n        'md' => ['width' => 600, 'height' => 600],\n        'lg' => ['width' => 800, 'height' => 800]\n    ],\n    'quality' => 85,\n    'method' => 'crop' // 'fit' veya 'crop'\n];\n```\n\n## İpuçları\n\n1. **Performans**: Büyük resimler için batch işleme kullanın\n2. **Depolama**: Gereksiz boyutları oluşturmayın\n3. **Kalite**: Web için 80-85 arası optimal\n4. **Metod**: Kare alanlar için 'crop', esnek boyutlar için 'fit'\n5. **Cache**: Küçük resimleri CDN'de saklayın