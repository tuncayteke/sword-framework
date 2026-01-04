<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Image sınıfı - Görüntü işleme işlemlerini yönetir
 */

class Image
{
    /**
     * Görüntü kaynağı
     */
    private $image;

    /**
     * Görüntü türü
     */
    private $type;

    /**
     * Görüntü genişliği
     */
    private $width;

    /**
     * Görüntü yüksekliği
     */
    private $height;

    /**
     * Görüntü dosya yolu
     */
    private $path;

    /**
     * Görüntü kalitesi
     */
    private $quality = 90;

    /**
     * Yapılandırıcı
     *
     * @param string $path Görüntü dosya yolu
     * @throws Exception Görüntü yüklenemezse
     */
    public function __construct($path = null)
    {
        if ($path !== null) {
            $this->load($path);
        }
    }

    /**
     * Yıkıcı
     */
    public function __destruct()
    {
        $this->destroy();
    }

    /**
     * Görüntüyü yükler
     *
     * @param string $path Görüntü dosya yolu
     * @return Image
     * @throws Exception Görüntü yüklenemezse
     */
    public function load($path)
    {
        // Dosya var mı?
        if (!file_exists($path)) {
            throw new Exception("Görüntü dosyası bulunamadı: {$path}");
        }

        // Görüntü bilgilerini al
        $info = getimagesize($path);
        if ($info === false) {
            throw new Exception("Geçersiz görüntü dosyası: {$path}");
        }

        // Görüntü türünü belirle
        $this->type = $info[2];

        // Görüntü kaynağını oluştur
        switch ($this->type) {
            case IMAGETYPE_JPEG:
                $this->image = imagecreatefromjpeg($path);
                break;
            case IMAGETYPE_PNG:
                $this->image = imagecreatefrompng($path);
                break;
            case IMAGETYPE_GIF:
                $this->image = imagecreatefromgif($path);
                break;
            case IMAGETYPE_WEBP:
                $this->image = imagecreatefromwebp($path);
                break;
            case IMAGETYPE_BMP:
                $this->image = imagecreatefrombmp($path);
                break;
            default:
                throw new Exception("Desteklenmeyen görüntü türü: {$info['mime']}");
        }

        if ($this->image === false) {
            throw new Exception("Görüntü yüklenemedi: {$path}");
        }

        // Görüntü boyutlarını al
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);

        // Dosya yolunu kaydet
        $this->path = $path;

        // PNG ve WebP için alfa kanalını koru
        if ($this->type == IMAGETYPE_PNG || $this->type == IMAGETYPE_WEBP) {
            imagealphablending($this->image, false);
            imagesavealpha($this->image, true);
        }

        return $this;
    }

    /**
     * Görüntüyü yeniden boyutlandırır
     *
     * @param int $width Yeni genişlik
     * @param int $height Yeni yükseklik
     * @param bool $keepAspectRatio En-boy oranını koru
     * @return Image
     */
    public function resize($width, $height, $keepAspectRatio = true)
    {
        // En-boy oranını koru
        if ($keepAspectRatio) {
            $ratio = $this->width / $this->height;

            if ($width / $height > $ratio) {
                $width = $height * $ratio;
            } else {
                $height = $width / $ratio;
            }
        }

        // Yeni görüntü oluştur
        $newImage = imagecreatetruecolor($width, $height);

        // PNG ve WebP için alfa kanalını koru
        if ($this->type == IMAGETYPE_PNG || $this->type == IMAGETYPE_WEBP) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            imagefilledrectangle($newImage, 0, 0, $width, $height, $transparent);
        }

        // Görüntüyü yeniden boyutlandır
        imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

        // Eski görüntüyü sil
        // PHP 8.0+ için güvenli temizlik
        if (PHP_VERSION_ID < 80000 && is_resource($this->image)) {
            imagedestroy($this->image);
        }
        $this->image = null;

        // Yeni görüntüyü kaydet
        $this->image = $newImage;
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * Görüntüyü kırpar
     *
     * @param int $x X koordinatı
     * @param int $y Y koordinatı
     * @param int $width Genişlik
     * @param int $height Yükseklik
     * @return Image
     */
    public function crop($x, $y, $width, $height)
    {
        // Sınırları kontrol et
        if ($x < 0 || $y < 0 || $width <= 0 || $height <= 0 || $x + $width > $this->width || $y + $height > $this->height) {
            throw new Exception("Geçersiz kırpma koordinatları.");
        }

        // Yeni görüntü oluştur
        $newImage = imagecreatetruecolor($width, $height);

        // PNG ve WebP için alfa kanalını koru
        if ($this->type == IMAGETYPE_PNG || $this->type == IMAGETYPE_WEBP) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            imagefilledrectangle($newImage, 0, 0, $width, $height, $transparent);
        }

        // Görüntüyü kırp
        imagecopy($newImage, $this->image, 0, 0, $x, $y, $width, $height);

        // Eski görüntüyü sil
        imagedestroy($this->image);

        // Yeni görüntüyü kaydet
        $this->image = $newImage;
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * Görüntüyü döndürür
     *
     * @param float $angle Açı (derece)
     * @param int $bgColor Arka plan rengi
     * @return Image
     */
    public function rotate($angle, $bgColor = 0)
    {
        // Görüntüyü döndür
        $this->image = imagerotate($this->image, $angle, $bgColor);

        // Görüntü boyutlarını güncelle
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);

        return $this;
    }

    /**
     * Görüntüye filigran ekler
     *
     * @param string $watermarkPath Filigran görüntü dosya yolu
     * @param string $position Konum (top-left, top-right, bottom-left, bottom-right, center)
     * @param int $opacity Opaklık (0-100)
     * @return Image
     */
    public function watermark($watermarkPath, $position = 'bottom-right', $opacity = 50)
    {
        // Filigran görüntüsünü yükle
        $watermark = new Image($watermarkPath);

        // Konumu belirle
        switch ($position) {
            case 'top-left':
                $x = 10;
                $y = 10;
                break;
            case 'top-right':
                $x = $this->width - $watermark->getWidth() - 10;
                $y = 10;
                break;
            case 'bottom-left':
                $x = 10;
                $y = $this->height - $watermark->getHeight() - 10;
                break;
            case 'center':
                $x = ($this->width - $watermark->getWidth()) / 2;
                $y = ($this->height - $watermark->getHeight()) / 2;
                break;
            case 'bottom-right':
            default:
                $x = $this->width - $watermark->getWidth() - 10;
                $y = $this->height - $watermark->getHeight() - 10;
                break;
        }

        // Filigranı ekle
        $this->merge($watermark->getImage(), $x, $y, $opacity);

        return $this;
    }

    /**
     * İki görüntüyü birleştirir
     *
     * @param resource $overlay Üst görüntü
     * @param int $x X koordinatı
     * @param int $y Y koordinatı
     * @param int $opacity Opaklık (0-100)
     * @return Image
     */
    public function merge($overlay, $x, $y, $opacity = 100)
    {
        // Opaklığı kontrol et
        $opacity = max(0, min(100, $opacity));

        // Görüntüleri birleştir
        imagecopymerge($this->image, $overlay, $x, $y, 0, 0, imagesx($overlay), imagesy($overlay), $opacity);

        return $this;
    }

    /**
     * Görüntüyü kaydeder
     *
     * @param string|null $path Dosya yolu
     * @param int|null $type Görüntü türü
     * @param int|null $quality Kalite (0-100)
     * @return bool Başarılı mı?
     */
    public function save($path = null, $type = null, $quality = null)
    {
        // Dosya yolu belirtilmemişse orijinal yolu kullan
        $path = $path ?: $this->path;

        // Görüntü türü belirtilmemişse orijinal türü kullan
        $type = $type ?: $this->type;

        // Kalite belirtilmemişse varsayılan kaliteyi kullan
        $quality = $quality ?: $this->quality;

        // Görüntüyü kaydet
        switch ($type) {
            case IMAGETYPE_JPEG:
                return imagejpeg($this->image, $path, $quality);
            case IMAGETYPE_PNG:
                // PNG için kalite 0-9 arasında olmalı
                $pngQuality = round(9 - ($quality / 100) * 9);
                return imagepng($this->image, $path, $pngQuality);
            case IMAGETYPE_GIF:
                return imagegif($this->image, $path);
            case IMAGETYPE_WEBP:
                return imagewebp($this->image, $path, $quality);
            case IMAGETYPE_BMP:
                return imagebmp($this->image, $path);
            default:
                throw new Exception("Desteklenmeyen görüntü türü.");
        }
    }

    /**
     * Görüntüyü tarayıcıya gönderir
     *
     * @param int|null $type Görüntü türü
     * @param int|null $quality Kalite (0-100)
     * @return bool Başarılı mı?
     */
    public function output($type = null, $quality = null)
    {
        // Görüntü türü belirtilmemişse orijinal türü kullan
        $type = $type ?: $this->type;

        // Kalite belirtilmemişse varsayılan kaliteyi kullan
        $quality = $quality ?: $this->quality;

        // MIME türünü belirle
        switch ($type) {
            case IMAGETYPE_JPEG:
                header('Content-Type: image/jpeg');
                return imagejpeg($this->image, null, $quality);
            case IMAGETYPE_PNG:
                header('Content-Type: image/png');
                $pngQuality = round(9 - ($quality / 100) * 9);
                return imagepng($this->image, null, $pngQuality);
            case IMAGETYPE_GIF:
                header('Content-Type: image/gif');
                return imagegif($this->image);
            case IMAGETYPE_WEBP:
                header('Content-Type: image/webp');
                return imagewebp($this->image, null, $quality);
            case IMAGETYPE_BMP:
                header('Content-Type: image/bmp');
                return imagebmp($this->image);
            default:
                throw new Exception("Desteklenmeyen görüntü türü.");
        }
    }

    /**
     * Görüntüyü siler
     *
     * @return void
     */
    public function destroy()
    {
        if (is_resource($this->image) || (is_object($this->image) && $this->image instanceof \GdImage)) {
            imagedestroy($this->image);
        }
    }

    /**
     * Görüntü kaynağını döndürür
     *
     * @return resource Görüntü kaynağı
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Görüntü genişliğini döndürür
     *
     * @return int Genişlik
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Görüntü yüksekliğini döndürür
     *
     * @return int Yükseklik
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Görüntü türünü döndürür
     *
     * @return int Tür
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Görüntü kalitesini ayarlar
     *
     * @param int $quality Kalite (0-100)
     * @return Image
     */
    public function setQuality($quality)
    {
        $this->quality = max(0, min(100, $quality));
        return $this;
    }

    /**
     * Küçük resim oluşturur
     *
     * @param int $width Genişlik
     * @param int $height Yükseklik
     * @param string $method Metod (fit, crop)
     * @return Image
     */
    public function thumbnail($width, $height, $method = 'fit')
    {
        if ($method === 'fit') {
            // En-boy oranını koruyarak yeniden boyutlandır
            return $this->resize($width, $height, true);
        } else if ($method === 'crop') {
            // Önce en-boy oranını koruyarak yeniden boyutlandır
            $ratio = $this->width / $this->height;
            $newWidth = $width;
            $newHeight = $height;

            if ($width / $height > $ratio) {
                $newWidth = $height * $ratio;
            } else {
                $newHeight = $width / $ratio;
            }

            $this->resize($newWidth, $newHeight, true);

            // Sonra ortadan kırp
            $x = ($this->width - $width) / 2;
            $y = ($this->height - $height) / 2;

            return $this->crop($x, $y, $width, $height);
        }

        return $this;
    }

    /**
     * Görüntüyü gri tonlamalı yapar
     *
     * @return Image
     */
    public function grayscale()
    {
        imagefilter($this->image, IMG_FILTER_GRAYSCALE);
        return $this;
    }

    /**
     * Görüntüye negatif filtre uygular
     *
     * @return Image
     */
    public function negative()
    {
        imagefilter($this->image, IMG_FILTER_NEGATE);
        return $this;
    }

    /**
     * Görüntüye parlaklık filtresi uygular
     *
     * @param int $level Parlaklık seviyesi (-255 ile 255 arası)
     * @return Image
     */
    public function brightness($level)
    {
        imagefilter($this->image, IMG_FILTER_BRIGHTNESS, $level);
        return $this;
    }

    /**
     * Görüntüye kontrast filtresi uygular
     *
     * @param int $level Kontrast seviyesi (-100 ile 100 arası)
     * @return Image
     */
    public function contrast($level)
    {
        imagefilter($this->image, IMG_FILTER_CONTRAST, $level);
        return $this;
    }

    /**
     * Görüntüye bulanıklık filtresi uygular
     *
     * @return Image
     */
    public function blur()
    {
        imagefilter($this->image, IMG_FILTER_GAUSSIAN_BLUR);
        return $this;
    }

    /**
     * Görüntüye keskinleştirme filtresi uygular
     *
     * @return Image
     */
    public function sharpen()
    {
        $matrix = [
            [-1, -1, -1],
            [-1, 16, -1],
            [-1, -1, -1]
        ];

        $divisor = array_sum(array_map('array_sum', $matrix));
        $offset = 0;

        imageconvolution($this->image, $matrix, $divisor, $offset);

        return $this;
    }
}
