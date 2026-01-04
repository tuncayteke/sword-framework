<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Thumbnails sınıfı - Küçük resim oluşturma işlemlerini yönetir
 */

class Thumbnails
{
    /**
     * XS boyutlu küçük resim genişliği
     */
    private $xsWidth;

    /**
     * XS boyutlu küçük resim yüksekliği
     */
    private $xsHeight;

    /**
     * SM boyutlu küçük resim genişliği
     */
    private $smWidth;

    /**
     * SM boyutlu küçük resim yüksekliği
     */
    private $smHeight;

    /**
     * MD boyutlu küçük resim genişliği
     */
    private $mdWidth;

    /**
     * MD boyutlu küçük resim yüksekliği
     */
    private $mdHeight;

    /**
     * LG boyutlu küçük resim genişliği
     */
    private $lgWidth;

    /**
     * LG boyutlu küçük resim yüksekliği
     */
    private $lgHeight;

    /**
     * Küçük resim kalitesi
     */
    private $quality;

    /**
     * Küçük resim metodu (fit, crop)
     */
    private $method;

    /**
     * Yapılandırıcı
     */
    public function __construct()
    {
        // Varsayılan boyutları Sword::getData'dan al veya varsayılan değerleri kullan
        if (class_exists('Sword')) {
            $this->xsWidth = Sword::getData('upload_image_xs_width', 150);
            $this->xsHeight = Sword::getData('upload_image_xs_height', 150);
            $this->smWidth = Sword::getData('upload_image_sm_width', 300);
            $this->smHeight = Sword::getData('upload_image_sm_height', 300);
            $this->mdWidth = Sword::getData('upload_image_md_width', 600);
            $this->mdHeight = Sword::getData('upload_image_md_height', 600);
            $this->lgWidth = Sword::getData('upload_image_lg_width', 800);
            $this->lgHeight = Sword::getData('upload_image_lg_height', 800);
            $this->quality = Sword::getData('upload_image_quality', 90);
            $this->method = Sword::getData('upload_image_thumbnail_method', 'crop');
        } else {
            // Varsayılan değerler
            $this->xsWidth = 150;
            $this->xsHeight = 150;
            $this->smWidth = 300;
            $this->smHeight = 300;
            $this->mdWidth = 600;
            $this->mdHeight = 600;
            $this->lgWidth = 800;
            $this->lgHeight = 800;
            $this->quality = 90;
            $this->method = 'crop';
        }
    }

    /**
     * Küçük resimler oluşturur
     *
     * @param string $imagePath Görüntü dosya yolu
     * @param array $sizes Oluşturulacak boyutlar (xs, sm, md, lg)
     * @return array Küçük resim yolları
     */
    public function generate($imagePath, $sizes = ['xs', 'sm', 'md', 'lg'])
    {
        if (!file_exists($imagePath)) {
            throw new Exception("Görüntü dosyası bulunamadı: {$imagePath}");
        }

        $result = [
            'original' => $imagePath
        ];

        // Image sınıfını yükle
        if (!class_exists('Image')) {
            if (class_exists('Sword') && method_exists('Sword', 'image')) {
                $image = Sword::image($imagePath);
            } else {
                require_once dirname(__FILE__) . '/Image.php';
                $image = new Image($imagePath);
            }
        } else {
            $image = new Image($imagePath);
        }

        // Kaliteyi ayarla
        $image->setQuality($this->quality);

        // XS thumbnail
        if (in_array('xs', $sizes)) {
            $xsPath = $this->getThumbPath($imagePath, 'xs');
            $image->load($imagePath)
                ->thumbnail($this->xsWidth, $this->xsHeight, $this->method)
                ->save($xsPath);
            $result['xs'] = $xsPath;
        }

        // SM thumbnail
        if (in_array('sm', $sizes)) {
            $smPath = $this->getThumbPath($imagePath, 'sm');
            $image->load($imagePath)
                ->thumbnail($this->smWidth, $this->smHeight, $this->method)
                ->save($smPath);
            $result['sm'] = $smPath;
        }

        // MD thumbnail
        if (in_array('md', $sizes)) {
            $mdPath = $this->getThumbPath($imagePath, 'md');
            $image->load($imagePath)
                ->thumbnail($this->mdWidth, $this->mdHeight, $this->method)
                ->save($mdPath);
            $result['md'] = $mdPath;
        }

        // LG thumbnail
        if (in_array('lg', $sizes)) {
            $lgPath = $this->getThumbPath($imagePath, 'lg');
            $image->load($imagePath)
                ->thumbnail($this->lgWidth, $this->lgHeight, $this->method)
                ->save($lgPath);
            $result['lg'] = $lgPath;
        }

        return $result;
    }

    /**
     * Küçük resim yolunu döndürür
     *
     * @param string $imagePath Görüntü dosya yolu
     * @param string $size Boyut (xs, sm, md, lg)
     * @return string Küçük resim yolu
     */
    public function getThumbPath($imagePath, $size)
    {
        $pathInfo = pathinfo($imagePath);
        return $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '_' . $size . '.' . $pathInfo['extension'];
    }

    /**
     * XS boyutlu küçük resim genişliğini ayarlar
     *
     * @param int $width Genişlik
     * @return Thumbnails
     */
    public function setXsWidth($width)
    {
        $this->xsWidth = $width;
        return $this;
    }

    /**
     * XS boyutlu küçük resim yüksekliğini ayarlar
     *
     * @param int $height Yükseklik
     * @return Thumbnails
     */
    public function setXsHeight($height)
    {
        $this->xsHeight = $height;
        return $this;
    }

    /**
     * SM boyutlu küçük resim genişliğini ayarlar
     *
     * @param int $width Genişlik
     * @return Thumbnails
     */
    public function setSmWidth($width)
    {
        $this->smWidth = $width;
        return $this;
    }

    /**
     * SM boyutlu küçük resim yüksekliğini ayarlar
     *
     * @param int $height Yükseklik
     * @return Thumbnails
     */
    public function setSmHeight($height)
    {
        $this->smHeight = $height;
        return $this;
    }

    /**
     * MD boyutlu küçük resim genişliğini ayarlar
     *
     * @param int $width Genişlik
     * @return Thumbnails
     */
    public function setMdWidth($width)
    {
        $this->mdWidth = $width;
        return $this;
    }

    /**
     * MD boyutlu küçük resim yüksekliğini ayarlar
     *
     * @param int $height Yükseklik
     * @return Thumbnails
     */
    public function setMdHeight($height)
    {
        $this->mdHeight = $height;
        return $this;
    }

    /**
     * LG boyutlu küçük resim genişliğini ayarlar
     *
     * @param int $width Genişlik
     * @return Thumbnails
     */
    public function setLgWidth($width)
    {
        $this->lgWidth = $width;
        return $this;
    }

    /**
     * LG boyutlu küçük resim yüksekliğini ayarlar
     *
     * @param int $height Yükseklik
     * @return Thumbnails
     */
    public function setLgHeight($height)
    {
        $this->lgHeight = $height;
        return $this;
    }

    /**
     * Küçük resim kalitesini ayarlar
     *
     * @param int $quality Kalite (0-100)
     * @return Thumbnails
     */
    public function setQuality($quality)
    {
        $this->quality = max(0, min(100, $quality));
        return $this;
    }

    /**
     * Küçük resim metodunu ayarlar
     *
     * @param string $method Metod (fit, crop)
     * @return Thumbnails
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }
}
