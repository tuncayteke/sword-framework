<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * View Layout sınıfı - Sayfa düzeni yönetimi
 */

namespace Sword\View;

class Layout
{
    /**
     * Layout dosyası
     */
    private $file = null;

    /**
     * Layout dizini
     */
    private $layoutDir = 'views/layouts';

    /**
     * İçerik değişkeni
     */
    private $contentVar = 'content';

    /**
     * Layout değişkenleri
     */
    private $data = [];

    /**
     * Layout bölümleri
     */
    private $sections = [];

    /**
     * Geçerli bölüm
     */
    private $currentSection = null;

    /**
     * Bölüm içeriği tamponu
     */
    private $sectionBuffer = '';

    /**
     * Layout dosyasını ayarlar
     *
     * @param string $file Layout dosyası
     * @return Layout
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Layout dizinini ayarlar
     *
     * @param string $dir Layout dizini
     * @return Layout
     */
    public function setLayoutDir($dir)
    {
        $this->layoutDir = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
        return $this;
    }

    /**
     * İçerik değişkenini ayarlar
     *
     * @param string $var İçerik değişkeni
     * @return Layout
     */
    public function setContentVar($var)
    {
        $this->contentVar = $var;
        return $this;
    }

    /**
     * Layout'a değişken atar
     *
     * @param string $name Değişken adı
     * @param mixed $value Değişken değeri
     * @return Layout
     */
    public function set($name, $value)
    {
        $this->data[$name] = $value;
        return $this;
    }

    /**
     * Birden fazla değişkeni atar
     *
     * @param array $data Değişken dizisi
     * @return Layout
     */
    public function setData($data)
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Değişken değerini döndürür
     *
     * @param string $name Değişken adı
     * @param mixed $default Varsayılan değer
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return isset($this->data[$name]) ? $this->data[$name] : $default;
    }

    /**
     * Tüm değişkenleri döndürür
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Bölüm başlatır
     *
     * @param string $name Bölüm adı
     * @return void
     */
    public function startSection($name)
    {
        $this->currentSection = $name;
        ob_start();
    }

    /**
     * Bölüm bitirir
     *
     * @return void
     */
    public function endSection()
    {
        if ($this->currentSection !== null) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }

    /**
     * Bölüm içeriğini ayarlar
     *
     * @param string $name Bölüm adı
     * @param string $content Bölüm içeriği
     * @return Layout
     */
    public function setSection($name, $content)
    {
        $this->sections[$name] = $content;
        return $this;
    }

    /**
     * Bölüm içeriğini döndürür
     *
     * @param string $name Bölüm adı
     * @param string $default Varsayılan içerik
     * @return string
     */
    public function getSection($name, $default = '')
    {
        return isset($this->sections[$name]) ? $this->sections[$name] : $default;
    }

    /**
     * Bölüm var mı?
     *
     * @param string $name Bölüm adı
     * @return bool
     */
    public function hasSection($name)
    {
        return isset($this->sections[$name]);
    }

    /**
     * Bölüm içeriğini ekrana basar
     *
     * @param string $name Bölüm adı
     * @param string $default Varsayılan içerik
     * @return void
     */
    public function renderSection($name, $default = '')
    {
        echo $this->getSection($name, $default);
    }

    /**
     * Layout'u işler
     *
     * @param string $content İçerik
     * @return string İşlenmiş layout içeriği
     */
    public function render($content)
    {
        if ($this->file === null) {
            return $content;
        }

        $this->set($this->contentVar, $content);

        return view()
            ->assignMultiple($this->data)
            ->render('layouts/' . $this->file);
    }
}
