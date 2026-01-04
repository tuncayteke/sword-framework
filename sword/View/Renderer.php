<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * View Renderer sınıfı - Şablon işleme motoru
 */

namespace Sword\View;

class Renderer
{
    /**
     * Şablon dizini
     */
    private $templateDir = 'views';

    /**
     * Önbellek dizini
     */
    private $cacheDir = 'cache/views';

    /**
     * Önbellek etkin mi?
     */
    private $cacheEnabled = false;

    /**
     * Önbellek süresi (saniye)
     */
    private $cacheTtl = 3600;

    /**
     * Şablon uzantısı
     */
    private $extension = '.php';

    /**
     * Şablon değişkenleri
     */
    private $data = [];

    /**
     * Şablon yardımcıları
     */
    private $helpers = [];

    /**
     * Şablon fonksiyonları
     */
    private $functions = [];

    /**
     * Şablon dizinini ayarlar
     *
     * @param string $dir Şablon dizini
     * @return Renderer
     */
    public function setTemplateDir($dir)
    {
        $this->templateDir = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
        return $this;
    }

    /**
     * Önbellek dizinini ayarlar
     *
     * @param string $dir Önbellek dizini
     * @return Renderer
     */
    public function setCacheDir($dir)
    {
        $this->cacheDir = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
        return $this;
    }

    /**
     * Önbelleği etkinleştirir veya devre dışı bırakır
     *
     * @param bool $enabled Etkin mi?
     * @return Renderer
     */
    public function enableCache($enabled = true)
    {
        $this->cacheEnabled = $enabled;
        return $this;
    }

    /**
     * Önbellek süresini ayarlar
     *
     * @param int $ttl Süre (saniye)
     * @return Renderer
     */
    public function setCacheTtl($ttl)
    {
        $this->cacheTtl = $ttl;
        return $this;
    }

    /**
     * Şablon uzantısını ayarlar
     *
     * @param string $extension Uzantı
     * @return Renderer
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * Şablona değişken atar
     *
     * @param string $name Değişken adı
     * @param mixed $value Değişken değeri
     * @return Renderer
     */
    public function assign($name, $value)
    {
        $this->data[$name] = $value;
        return $this;
    }

    /**
     * Birden fazla değişkeni atar
     *
     * @param array $data Değişken dizisi
     * @return Renderer
     */
    public function assignMultiple($data)
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Yardımcı ekler
     *
     * @param string $name Yardımcı adı
     * @param callable $callback Yardımcı fonksiyonu
     * @return Renderer
     */
    public function addHelper($name, $callback)
    {
        $this->helpers[$name] = $callback;
        return $this;
    }

    /**
     * Fonksiyon ekler
     *
     * @param string $name Fonksiyon adı
     * @param callable $callback Fonksiyon
     * @return Renderer
     */
    public function addFunction($name, $callback)
    {
        $this->functions[$name] = $callback;
        return $this;
    }

    /**
     * Şablonu işler
     *
     * @param string $template Şablon dosyası
     * @param array $data Opsiyonel ek veriler
     * @return string İşlenmiş şablon içeriği
     */
    public function render($template, $data = null)
    {
        // Ek verileri ekle
        if ($data !== null) {
            $this->assignMultiple($data);
        }

        // Şablon dosyasını hazırla
        $templateFile = $this->templateDir . $template . $this->extension;

        // Önbellekten kontrol et
        if ($this->cacheEnabled) {
            $cacheFile = $this->getCacheFilename($template);

            // Önbellek geçerli mi?
            if ($this->isCacheValid($cacheFile, $templateFile)) {
                return file_get_contents($cacheFile);
            }
        }

        // Şablon dosyasını kontrol et
        if (!file_exists($templateFile)) {
            throw new \Exception("Şablon dosyası bulunamadı: $templateFile");
        }

        // Şablonu işle
        $content = $this->renderTemplate($templateFile);

        // Önbelleğe kaydet
        if ($this->cacheEnabled) {
            $this->saveCache($cacheFile, $content);
        }

        return $content;
    }

    /**
     * Şablonu işler
     *
     * @param string $templateFile Şablon dosyası
     * @return string İşlenmiş şablon içeriği
     */
    private function renderTemplate($templateFile)
    {
        // Değişkenleri çıkart
        extract($this->data);

        // Yardımcıları tanımla
        foreach ($this->helpers as $name => $callback) {
            $$name = $callback;
        }

        // Fonksiyonları tanımla
        foreach ($this->functions as $name => $callback) {
            if (!function_exists($name)) {
                $GLOBALS[$name] = $callback;
            }
        }

        // Çıktı tamponlamasını başlat
        ob_start();

        // Şablon dosyasını dahil et
        include $templateFile;

        // Tamponlanmış çıktıyı al ve döndür
        return ob_get_clean();
    }

    /**
     * Önbellek dosya adını oluşturur
     *
     * @param string $template Şablon adı
     * @return string Önbellek dosya adı
     */
    private function getCacheFilename($template)
    {
        $cacheDir = $this->cacheDir;

        // Önbellek dizinini oluştur
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        return $cacheDir . md5($template) . '.cache';
    }

    /**
     * Önbelleğin geçerli olup olmadığını kontrol eder
     *
     * @param string $cacheFile Önbellek dosyası
     * @param string $templateFile Şablon dosyası
     * @return bool Geçerli mi?
     */
    private function isCacheValid($cacheFile, $templateFile)
    {
        if (!file_exists($cacheFile)) {
            return false;
        }

        $cacheTime = filemtime($cacheFile);
        $templateTime = filemtime($templateFile);

        // Şablon değişmiş mi?
        if ($templateTime > $cacheTime) {
            return false;
        }

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
     * Şablonu işler ve ekrana basar
     *
     * @param string $template Şablon dosyası
     * @param array $data Opsiyonel ek veriler
     */
    public function display($template, $data = null)
    {
        echo $this->render($template, $data);
    }
}
