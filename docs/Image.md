# Image Class

Görüntü işleme işlemlerini yönetir. GD kütüphanesi tabanlı resim düzenleme özellikleri sunar.

## Temel Kullanım

```php
$image = new Image('path/to/image.jpg');

// Yeniden boyutlandır
$image->resize(800, 600)->save('resized.jpg');

// Küçük resim oluştur
$image->thumbnail(150, 150)->save('thumb.jpg');
```

## Yapılandırıcı

### __construct($path = null)

```php
// Dosya ile başlat
$image = new Image('image.jpg');

// Boş başlat, sonra yükle
$image = new Image();
$image->load('image.jpg');
```

## Görüntü Yükleme

### load($path)
Görüntü dosyasını yükler.

```php
$image->load('path/to/image.jpg');
$image->load('image.png');
$image->load('photo.gif');
```

**Desteklenen formatlar:** JPEG, PNG, GIF, WebP, BMP

## Boyutlandırma İşlemleri

### resize($width, $height, $keepAspectRatio = true)
Görüntüyü yeniden boyutlandırır.

```php
// En-boy oranını koruyarak
$image->resize(800, 600, true);

// Tam boyut (oranı bozmadan)
$image->resize(800, 600, false);

// Sadece genişlik (yükseklik otomatik)
$image->resize(800, 0, true);
```

### thumbnail($width, $height, $method = 'fit')
Küçük resim oluşturur.

```php
// Sığdırma (fit) - en-boy oranını korur
$image->thumbnail(150, 150, 'fit');

// Kırpma (crop) - tam boyut, ortadan kırpar
$image->thumbnail(150, 150, 'crop');
```

### crop($x, $y, $width, $height)
Görüntüyü kırpar.

```php
// Sol üst köşeden 200x200 kırp
$image->crop(0, 0, 200, 200);

// Ortadan 300x200 kırp
$image->crop(100, 50, 300, 200);
```

## Dönüştürme İşlemleri

### rotate($angle, $bgColor = 0)
Görüntüyü döndürür.

```php
// 90 derece sağa döndür
$image->rotate(90);

// 45 derece döndür, beyaz arka plan
$image->rotate(45, imagecolorallocate($image->getImage(), 255, 255, 255));
```

## Filigran İşlemleri

### watermark($watermarkPath, $position = 'bottom-right', $opacity = 50)
Görüntüye filigran ekler.

```php
// Sağ alt köşeye filigran
$image->watermark('logo.png', 'bottom-right', 70);

// Ortaya filigran
$image->watermark('watermark.png', 'center', 30);

// Sol üst köşeye filigran
$image->watermark('logo.png', 'top-left', 80);
```

**Pozisyon seçenekleri:** `top-left`, `top-right`, `bottom-left`, `bottom-right`, `center`

### merge($overlay, $x, $y, $opacity = 100)
İki görüntüyü birleştirir.

```php
$overlay = new Image('overlay.png');
$image->merge($overlay->getImage(), 50, 50, 75);
```

## Filtre İşlemleri

### grayscale()
Gri tonlamalı yapar.

```php
$image->grayscale()->save('gray.jpg');
```

### negative()
Negatif filtre uygular.

```php
$image->negative()->save('negative.jpg');
```

### brightness($level)
Parlaklık ayarlar (-255 ile 255 arası).

```php
$image->brightness(50)->save('bright.jpg');   // Parlat
$image->brightness(-50)->save('dark.jpg');    // Karart
```

### contrast($level)
Kontrast ayarlar (-100 ile 100 arası).

```php
$image->contrast(30)->save('high_contrast.jpg');
$image->contrast(-30)->save('low_contrast.jpg');
```

### blur()
Bulanıklık filtresi uygular.

```php
$image->blur()->save('blurred.jpg');
```

### sharpen()
Keskinleştirme filtresi uygular.

```php
$image->sharpen()->save('sharp.jpg');
```

## Kaydetme İşlemleri

### save($path = null, $type = null, $quality = null)
Görüntüyü kaydeder.

```php
// Aynı dosyaya kaydet
$image->save();

// Farklı dosyaya kaydet
$image->save('new_image.jpg');

// Farklı format ile kaydet
$image->save('image.png', IMAGETYPE_PNG);

// Kalite ile kaydet (JPEG için 0-100)
$image->save('image.jpg', IMAGETYPE_JPEG, 85);
```

### output($type = null, $quality = null)
Görüntüyü tarayıcıya gönderir.

```php
// Orijinal format ile
$image->output();

// JPEG olarak
$image->output(IMAGETYPE_JPEG, 90);

// PNG olarak
$image->output(IMAGETYPE_PNG);
```

## Bilgi Metodları

### getWidth()
Görüntü genişliğini döndürür.

```php
$width = $image->getWidth();
```

### getHeight()
Görüntü yüksekliğini döndürür.

```php
$height = $image->getHeight();
```

### getType()
Görüntü türünü döndürür.

```php
$type = $image->getType(); // IMAGETYPE_JPEG, IMAGETYPE_PNG, vb.
```

### getImage()
GD görüntü kaynağını döndürür.

```php
$resource = $image->getImage();
```

## Yapılandırma

### setQuality($quality)
Görüntü kalitesini ayarlar.

```php
$image->setQuality(95); // Yüksek kalite
$image->setQuality(60); // Orta kalite
```

## Örnek Kullanımlar

### Profil Resmi İşleme
```php
class AvatarProcessor
{
    public static function process($imagePath, $userId)
    {
        $image = new Image($imagePath);
        
        // 400x400 kare avatar oluştur
        $image->thumbnail(400, 400, 'crop')
              ->setQuality(90)
              ->save("avatars/avatar_{$userId}.jpg");
        
        // 150x150 küçük avatar
        $image->load($imagePath)
              ->thumbnail(150, 150, 'crop')
              ->save("avatars/avatar_{$userId}_small.jpg");
        
        // 50x50 mini avatar
        $image->load($imagePath)
              ->thumbnail(50, 50, 'crop')
              ->save("avatars/avatar_{$userId}_mini.jpg");
    }
}
```

### Galeri Resmi İşleme
```php
class GalleryProcessor
{
    public static function processGalleryImage($imagePath, $imageId)
    {
        $image = new Image($imagePath);
        
        // Orijinal boyutu kontrol et ve gerekirse küçült
        if ($image->getWidth() > 1920 || $image->getHeight() > 1080) {
            $image->resize(1920, 1080, true)
                  ->setQuality(85)
                  ->save("gallery/full_{$imageId}.jpg");
        }
        
        // Orta boyut (800x600)
        $image->load($imagePath)
              ->resize(800, 600, true)
              ->setQuality(80)
              ->save("gallery/medium_{$imageId}.jpg");
        
        // Küçük resim (300x200)
        $image->load($imagePath)
              ->thumbnail(300, 200, 'crop')
              ->setQuality(75)
              ->save("gallery/thumb_{$imageId}.jpg");
    }
}
```

### Filigran Ekleme
```php
class WatermarkProcessor
{
    public static function addWatermark($imagePath, $outputPath)
    {
        $image = new Image($imagePath);
        
        // Görüntü boyutuna göre filigran boyutu belirle
        $width = $image->getWidth();
        $watermarkSize = min(200, $width * 0.2); // %20 veya max 200px
        
        // Filigran görüntüsünü hazırla
        $watermark = new Image('assets/watermark.png');
        $watermark->resize($watermarkSize, $watermarkSize, true)
                  ->save('temp_watermark.png');
        
        // Ana görüntüye filigran ekle
        $image->watermark('temp_watermark.png', 'bottom-right', 60)
              ->save($outputPath);
        
        // Geçici dosyayı sil
        unlink('temp_watermark.png');
    }
}
```

### Çoklu Format Dönüştürme
```php
class FormatConverter
{
    public static function convertToFormats($imagePath, $baseName)
    {
        $image = new Image($imagePath);
        
        // JPEG (web için optimize)
        $image->setQuality(85)
              ->save("{$baseName}.jpg", IMAGETYPE_JPEG);
        
        // PNG (şeffaflık korunur)
        $image->save("{$baseName}.png", IMAGETYPE_PNG);
        
        // WebP (modern tarayıcılar için)
        if (function_exists('imagewebp')) {
            $image->save("{$baseName}.webp", IMAGETYPE_WEBP, 80);
        }
        
        return [
            'jpeg' => "{$baseName}.jpg",
            'png' => "{$baseName}.png",
            'webp' => "{$baseName}.webp"
        ];
    }
}
```

### Responsive Resim Seti
```php
class ResponsiveImageGenerator
{
    public static function generateSizes($imagePath, $baseName)
    {
        $image = new Image($imagePath);
        $sizes = [];
        
        // Farklı boyutlar için resim oluştur
        $breakpoints = [
            'xs' => 480,
            'sm' => 768,
            'md' => 1024,
            'lg' => 1200,
            'xl' => 1920
        ];
        
        foreach ($breakpoints as $size => $width) {
            $filename = "{$baseName}_{$size}.jpg";
            
            $image->load($imagePath)
                  ->resize($width, 0, true) // Yükseklik otomatik
                  ->setQuality(80)
                  ->save($filename);
            
            $sizes[$size] = [
                'file' => $filename,
                'width' => $image->getWidth(),
                'height' => $image->getHeight()
            ];
        }
        
        return $sizes;
    }
}
```

### Batch İşleme
```php
class BatchImageProcessor
{
    public static function processDirectory($inputDir, $outputDir, $maxWidth = 1200)
    {
        $files = glob($inputDir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        $processed = 0;
        
        foreach ($files as $file) {
            try {
                $image = new Image($file);
                $filename = basename($file);
                
                // Boyut kontrolü ve yeniden boyutlandırma
                if ($image->getWidth() > $maxWidth) {
                    $image->resize($maxWidth, 0, true);
                }
                
                // Kalite optimizasyonu
                $image->setQuality(85)
                      ->save($outputDir . '/' . $filename);
                
                $processed++;
                
            } catch (Exception $e) {
                Logger::error("Resim işleme hatası: {$file}", ['error' => $e->getMessage()]);
            }
        }
        
        return $processed;
    }
}
```

## Hata Yönetimi

```php
try {
    $image = new Image('nonexistent.jpg');
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}

// Güvenli yükleme
if (file_exists($imagePath)) {
    try {
        $image = new Image($imagePath);
        $image->resize(800, 600)->save('output.jpg');
    } catch (Exception $e) {
        Logger::error('Resim işleme hatası', ['error' => $e->getMessage()]);
    }
}
```

## İpuçları

1. **Bellek**: Büyük resimler çok bellek kullanır
2. **Kalite**: JPEG için 80-90 arası optimal
3. **Format**: PNG şeffaflık, JPEG boyut için ideal
4. **Performans**: Batch işlemlerde bellek sınırını artırın
5. **Güvenlik**: Yüklenen resimleri doğrulayın