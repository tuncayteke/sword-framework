<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * View Decorator sınıfı - Görünüm dekoratörleri
 */

namespace Sword\View;

class Decorator
{
    /**
     * Dekoratör adı
     */
    private $name;

    /**
     * Dekoratör fonksiyonu
     */
    private $callback;

    /**
     * Dekoratör parametreleri
     */
    private $params = [];

    /**
     * Kayıtlı dekoratörler
     */
    private static $decorators = [];

    /**
     * Yapılandırıcı
     *
     * @param string $name Dekoratör adı
     * @param callable $callback Dekoratör fonksiyonu
     * @param array $params Dekoratör parametreleri
     */
    public function __construct($name, $callback, $params = [])
    {
        $this->name = $name;
        $this->callback = $callback;
        $this->params = $params;
    }

    /**
     * Dekoratör parametrelerini ayarlar
     *
     * @param array $params Dekoratör parametreleri
     * @return Decorator
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Dekoratör parametresi ekler
     *
     * @param string $key Anahtar
     * @param mixed $value Değer
     * @return Decorator
     */
    public function addParam($key, $value)
    {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * İçeriği dekore eder
     *
     * @param string $content İçerik
     * @param array $data Veriler
     * @return string Dekore edilmiş içerik
     */
    public function decorate($content, $data = [])
    {
        if (is_callable($this->callback)) {
            return call_user_func($this->callback, $content, array_merge($this->params, $data));
        }
        return $content;
    }

    /**
     * Dekoratör kaydeder
     *
     * @param string $name Dekoratör adı
     * @param callable $callback Dekoratör fonksiyonu
     * @param array $params Dekoratör parametreleri
     * @return void
     */
    public static function register($name, $callback, $params = [])
    {
        self::$decorators[$name] = new self($name, $callback, $params);
    }

    /**
     * Dekoratör alır
     *
     * @param string $name Dekoratör adı
     * @return Decorator|null
     */
    public static function get($name)
    {
        return isset(self::$decorators[$name]) ? self::$decorators[$name] : null;
    }

    /**
     * Dekoratör var mı?
     *
     * @param string $name Dekoratör adı
     * @return bool
     */
    public static function has($name)
    {
        return isset(self::$decorators[$name]);
    }

    /**
     * Dekoratör kaldırır
     *
     * @param string $name Dekoratör adı
     * @return bool
     */
    public static function remove($name)
    {
        if (isset(self::$decorators[$name])) {
            unset(self::$decorators[$name]);
            return true;
        }
        return false;
    }

    /**
     * İçeriği dekore eder
     *
     * @param string $name Dekoratör adı
     * @param string $content İçerik
     * @param array $data Veriler
     * @return string Dekore edilmiş içerik
     */
    public static function apply($name, $content, $data = [])
    {
        if (self::has($name)) {
            return self::get($name)->decorate($content, $data);
        }
        return $content;
    }

    /**
     * İçeriği birden fazla dekoratörle dekore eder
     *
     * @param array $names Dekoratör adları
     * @param string $content İçerik
     * @param array $data Veriler
     * @return string Dekore edilmiş içerik
     */
    public static function applyMultiple(array $names, $content, $data = [])
    {
        foreach ($names as $name) {
            $content = self::apply($name, $content, $data);
        }
        return $content;
    }

    /**
     * Tüm dekoratörleri döndürür
     *
     * @return array
     */
    public static function all()
    {
        return self::$decorators;
    }

    /**
     * Bazı yaygın dekoratörleri kaydeder
     *
     * @return void
     */
    public static function registerCommon()
    {
        // HTML temizleme
        self::register('escape', function ($content) {
            return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        });

        // HTML etiketlerini kaldırma
        self::register('strip_tags', function ($content) {
            return strip_tags($content);
        });

        // Markdown işleme
        self::register('markdown', function ($content) {
            // Basit markdown işleme
            $content = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $content);
            $content = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $content);
            $content = preg_replace('/\n\n/', '</p><p>', $content);
            return '<p>' . $content . '</p>';
        });

        // Kısaltma
        self::register('truncate', function ($content, $params) {
            $length = isset($params['length']) ? $params['length'] : 100;
            $suffix = isset($params['suffix']) ? $params['suffix'] : '...';

            if (mb_strlen($content) <= $length) {
                return $content;
            }

            return mb_substr($content, 0, $length) . $suffix;
        });

        // Tarih formatı
        self::register('date_format', function ($content, $params) {
            $format = isset($params['format']) ? $params['format'] : 'd.m.Y H:i';
            $timestamp = is_numeric($content) ? $content : strtotime($content);
            return date($format, $timestamp);
        });

        // Yıl dekoratörü
        self::register('year', function ($content) {
            return str_replace('%year%', date('Y'), $content);
        });

        // Tarih-zaman dekoratörü
        self::register('datetime', function ($content) {
            $content = str_replace('%year%', date('Y'), $content);
            $content = str_replace('%month%', date('m'), $content);
            $content = str_replace('%day%', date('d'), $content);
            $content = str_replace('%time%', date('H:i:s'), $content);
            $content = str_replace('%datetime%', date('Y-m-d H:i:s'), $content);
            return $content;
        });

        // Büyük harf
        self::register('uppercase', function ($content) {
            return mb_strtoupper($content, 'UTF-8');
        });

        // Para formatı
        self::register('currency', function ($content, $params) {
            $symbol = isset($params['symbol']) ? $params['symbol'] : '₺';
            $decimals = isset($params['decimals']) ? $params['decimals'] : 2;
            $formatted = number_format((float)$content, $decimals, ',', '.');
            return $formatted . ' ' . $symbol;
        });
    }
}
