<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * View sınıfı - Görünüm işlemlerini yönetir
 */

namespace Sword;

class View
{
    /**
     * Görünüm verileri
     */
    private static $data = [];

    /**
     * Görünüm dosyası
     */
    private $view;

    /**
     * Düzen dosyası
     */
    private $layout;

    /**
     * Extends layout
     */
    private $extends = null;

    /**
     * Sections
     */
    private static $sections = [];

    /**
     * Current section
     */
    private static $currentSection = null;

    /**
     * Görünüm dizini
     */
    private $viewPath;

    /**
     * Yapılandırıcı
     *
     * @param string $view Görünüm dosyası
     * @param array $data Görünüm verileri
     * @param string|null $layout Düzen dosyası
     */
    public function __construct($view, $data = [], $layout = null)
    {
        $this->view = $view;

        // Görünüm verilerini ayarla
        foreach ($data as $key => $value) {
            self::$data[$key] = $value;
        }

        // Düzen dosyasını ayarla
        $this->layout = $layout;

        // Görünüm dizinini ayarla
        if (class_exists('\\Sword') && method_exists('\\Sword', 'getPath')) {
            $this->viewPath = \Sword::getPath('views');
        } else {
            $this->viewPath = defined('BASE_PATH') ? BASE_PATH . '/content/views' : './views';
        }
    }

    /**
     * Görünümü işler
     *
     * @return string İşlenmiş görünüm
     */
    public function render()
    {
        // Görünüm dosyasını işle
        $content = $this->renderView($this->view);

        // Extends layout varsa kullan
        if ($this->extends !== null) {
            self::$data['content'] = $content;
            $content = $this->renderView($this->extends, true);
        }
        // Yoksa eski layout sistemi
        elseif ($this->layout !== null) {
            self::$data['content'] = $content;
            $content = $this->renderView($this->layout, true);
        }

        // Shortcode'ları işle
        $content = $this->processShortcodes($content);

        // Decorator'ları uygula
        $content = $this->processDecorators($content);

        return $content;
    }

    /**
     * Görünüm dosyasını işler
     *
     * @param string $view Görünüm dosyası
     * @param bool $isLayout Düzen dosyası mı?
     * @return string İşlenmiş görünüm
     */
    private function renderView($view, $isLayout = false)
    {
        // Görünüm dosyasının tam yolunu oluştur
        $viewFile = $this->getViewFile($view, $isLayout);

        // Dosya var mı?
        if (!file_exists($viewFile)) {
            throw new \Exception("Görünüm dosyası bulunamadı: {$viewFile}");
        }

        // Görünüm verilerini çıkart
        extract(self::$data);

        // Çıktı tamponlamasını başlat
        ob_start();

        // Görünüm dosyasını dahil et
        include $viewFile;

        // Çıktı tamponlamasını bitir ve içeriği al
        return ob_get_clean();
    }

    /**
     * Görünüm dosyasının tam yolunu döndürür
     *
     * @param string $view Görünüm dosyası
     * @param bool $isLayout Düzen dosyası mı?
     * @return string Tam yol
     */
    private function getViewFile($view, $isLayout = false)
    {
        // Düzen dosyası ise layouts dizininde ara
        $dir = $isLayout ? 'layouts' : '';

        // Dosya uzantısını kontrol et
        $extension = pathinfo($view, PATHINFO_EXTENSION);
        if (empty($extension)) {
            $view .= '.php';
        }

        // Tam yolu oluştur
        return rtrim($this->viewPath, '/\\') . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $view;
    }

    /**
     * Görünüm verisini ayarlar
     *
     * @param string $key Anahtar
     * @param mixed $value Değer
     * @return void
     */
    public static function set($key, $value)
    {
        self::$data[$key] = $value;
    }

    /**
     * Görünüm verilerini ayarlar
     *
     * @param array $data Veriler
     * @return void
     */
    public static function setData($data)
    {
        foreach ($data as $key => $value) {
            self::$data[$key] = $value;
        }
    }

    /**
     * Görünüm verisini döndürür
     *
     * @param string $key Anahtar
     * @param mixed $default Varsayılan değer
     * @return mixed Değer
     */
    public static function get($key, $default = null)
    {
        return isset(self::$data[$key]) ? self::$data[$key] : $default;
    }

    /**
     * Tüm görünüm verilerini döndürür
     *
     * @return array Görünüm verileri
     */
    public static function getData()
    {
        return self::$data;
    }

    /**
     * Görünüm verisi var mı?
     *
     * @param string $key Anahtar
     * @return bool Var mı?
     */
    public static function has($key)
    {
        return isset(self::$data[$key]);
    }

    /**
     * Görünüm verisini siler
     *
     * @param string $key Anahtar
     * @return void
     */
    public static function remove($key)
    {
        unset(self::$data[$key]);
    }

    /**
     * Tüm görünüm verilerini temizler
     *
     * @return void
     */
    public static function clear()
    {
        self::$data = [];
    }

    /**
     * Görünüm dosyasını ayarlar
     *
     * @param string $view Görünüm dosyası
     * @return View
     */
    public function setView($view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Düzen dosyasını ayarlar
     *
     * @param string|null $layout Düzen dosyası
     * @return View
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Görünüm dizinini ayarlar
     *
     * @param string $viewPath Görünüm dizini
     * @return View
     */
    public function setViewPath($viewPath)
    {
        $this->viewPath = $viewPath;
        return $this;
    }

    /**
     * Kısmi görünümü (partial) işler - Cell'ler için
     *
     * @param string $view Görünüm dosyası
     * @param array $data Görünüm verileri
     * @return string İşlenmiş görünüm
     */
    public function renderPartial($view, $data = [])
    {
        $viewFile = $this->getViewFile($view);
        if (!file_exists($viewFile)) {
            throw new \Exception("Partial görünüm bulunamadı: {$viewFile}");
        }

        extract($data);
        ob_start();
        include $viewFile;
        return ob_get_clean();
    }

    /**
     * Veri ekler (zincirleme kullanım için)
     *
     * @param array|string $key Anahtar veya veri dizisi
     * @param mixed $value Değer
     * @return View
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                self::$data[$k] = $v;
            }
        } else {
            self::$data[$key] = $value;
        }

        return $this;
    }

    /**
     * Layout ayarlar (zincirleme kullanım için)
     *
     * @param string|null $layout Layout dosyası
     * @return View
     */
    public function layout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Shortcode'ları işler
     *
     * @param string $content İçerik
     * @return string İşlenmiş içerik
     */
    private function processShortcodes($content)
    {
        // Shortcode sınıfını yükle
        if (!class_exists('\Shortcode')) {
            require_once __DIR__ . '/Shortcode.php';
        }

        return \Shortcode::process($content);
    }

    /**
     * Extends layout ayarlar (CodeIgniter 4 tarzı)
     *
     * @param string $layout Layout dosyası
     * @return View
     */
    public function extend($layout)
    {
        $this->extends = $layout;
        return $this;
    }

    /**
     * Section başlatır
     *
     * @param string $name Section adı
     * @return void
     */
    public static function startSection($name)
    {
        self::$currentSection = $name;
        ob_start();
    }

    /**
     * Section bitirir
     *
     * @return void
     */
    public static function endSection()
    {
        if (self::$currentSection) {
            self::$sections[self::$currentSection] = ob_get_clean();
            self::$currentSection = null;
        }
    }

    /**
     * Section içeriğini döndürür
     *
     * @param string $name Section adı
     * @param string $default Varsayılan içerik
     * @return string Section içeriği
     */
    public static function getSection($name, $default = '')
    {
        return isset(self::$sections[$name]) ? self::$sections[$name] : $default;
    }

    /**
     * Section var mı kontrol eder
     *
     * @param string $name Section adı
     * @return bool
     */
    public static function hasSection($name)
    {
        return isset(self::$sections[$name]);
    }

    /**
     * Decorator'ları işler
     *
     * @param string $content İçerik
     * @return string İşlenmiş içerik
     */
    private function processDecorators($content)
    {
        // Decorator sınıfını yükle
        if (!class_exists('\\Sword\\View\\Decorator')) {
            require_once __DIR__ . '/View/Decorator.php';
        }

        // Temel decorator'ları uygula
        if (class_exists('\\Sword\\View\\Decorator')) {
            $decorators = ['year', 'datetime', 'site_name', 'version'];
            foreach ($decorators as $decorator) {
                if (\Sword\View\Decorator::has($decorator)) {
                    $content = \Sword\View\Decorator::apply($decorator, $content);
                }
            }
        }

        return $content;
    }

    /**
     * String'e dönüştürür - otomatik render için
     *
     * @return string İşlenmiş görünüm
     */
    public function __toString()
    {
        return $this->render();
    }
}
