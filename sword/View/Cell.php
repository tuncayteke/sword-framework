<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * View Cell sınıfı - Yeniden kullanılabilir görünüm bileşenleri
 */

namespace Sword\View;

class Cell
{
    /**
     * Hücre adı
     */
    private $name;

    /**
     * Hücre şablonu
     */
    private $template;

    /**
     * Hücre verileri
     */
    private $data = [];

    /**
     * Hücre önbelleği etkin mi?
     */
    private $cacheEnabled = false;

    /**
     * Hücre önbellek süresi
     */
    private $cacheTtl = 60;

    /**
     * Hücre önbellek anahtarı
     */
    private $cacheKey = '';

    /**
     * Hücre önbellek dizini
     */
    private $cacheDir = 'cache/cells';

    /**
     * Yapılandırıcı
     *
     * @param string $name Hücre adı
     * @param string $template Hücre şablonu
     * @param array $data Hücre verileri
     */
    public function __construct($name, $template = null, $data = [])
    {
        $this->name = $name;
        $this->template = $template ?: $name;
        $this->data = $data;
    }

    /**
     * Hücre şablonunu ayarlar
     *
     * @param string $template Hücre şablonu
     * @return Cell
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Hücre verilerini ayarlar
     *
     * @param array $data Hücre verileri
     * @return Cell
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Hücre verisini ayarlar
     *
     * @param string $key Anahtar
     * @param mixed $value Değer
     * @return Cell
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Hücre önbelleğini etkinleştirir
     *
     * @param int $ttl Önbellek süresi (saniye)
     * @param string $key Önbellek anahtarı
     * @return Cell
     */
    public function cache($ttl = 60, $key = '')
    {
        $this->cacheEnabled = true;
        $this->cacheTtl = $ttl;
        $this->cacheKey = $key ?: $this->name;
        return $this;
    }

    /**
     * Hücre önbellek dizinini ayarlar
     *
     * @param string $dir Önbellek dizini
     * @return Cell
     */
    public function setCacheDir($dir)
    {
        $this->cacheDir = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
        return $this;
    }

    /**
     * Hücreyi işler
     *
     * @param array $data Ek veriler
     * @return string İşlenmiş hücre içeriği
     */
    public function render($data = [])
    {
        // Verileri birleştir
        $cellData = array_merge($this->data, $data);

        // Önbellekten kontrol et
        if ($this->cacheEnabled) {
            $cacheFile = $this->getCacheFilename();

            // Önbellek geçerli mi?
            if ($this->isCacheValid($cacheFile)) {
                return file_get_contents($cacheFile);
            }
        }

        // Hücreyi işle
        $content = view()
            ->assignMultiple($cellData)
            ->render('cells/' . $this->template);

        // Önbelleğe kaydet
        if ($this->cacheEnabled) {
            $this->saveCache($cacheFile, $content);
        }

        return $content;
    }

    /**
     * Önbellek dosya adını oluşturur
     *
     * @return string Önbellek dosya adı
     */
    private function getCacheFilename()
    {
        $cacheDir = $this->cacheDir;

        // Önbellek dizinini oluştur
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        // Önbellek anahtarını oluştur
        $key = $this->cacheKey . '_' . md5(serialize($this->data));

        return $cacheDir . $key . '.cache';
    }

    /**
     * Önbelleğin geçerli olup olmadığını kontrol eder
     *
     * @param string $cacheFile Önbellek dosyası
     * @return bool Geçerli mi?
     */
    private function isCacheValid($cacheFile)
    {
        if (!file_exists($cacheFile)) {
            return false;
        }

        $cacheTime = filemtime($cacheFile);

        // Önbellek süresi dolmuş mu?
        if (time() - $cacheTime > $this->cacheTtl) {
            return false;
        }

        return true;
    }

    /**
     * İçeriği önbelleğe kaydeder
     *
     * @param string $cacheFile Önbellek dosyası
     * @param string $content İçerik
     * @return bool Başarılı mı?
     */
    private function saveCache($cacheFile, $content)
    {
        return file_put_contents($cacheFile, $content);
    }

    /**
     * Hücreyi işler ve ekrana basar
     *
     * @param array $data Ek veriler
     * @return void
     */
    public function display($data = [])
    {
        echo $this->render($data);
    }

    /**
     * Statik hücre oluşturur ve işler
     *
     * @param string $name Hücre adı
     * @param array $data Hücre verileri
     * @return string İşlenmiş hücre içeriği
     */
    public static function make($name, $data = [])
    {
        $cell = new self($name, null, $data);
        return $cell->render();
    }

    /**
     * String'e dönüştürür - echo için
     *
     * @return string İşlenmiş hücre içeriği
     */
    public function __toString()
    {
        return $this->render();
    }
}
