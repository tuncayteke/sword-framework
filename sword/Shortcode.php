<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Shortcode sınıfı - WordPress benzeri shortcode sistemi
 */

class Shortcode
{
    /**
     * Kayıtlı shortcode'lar
     */
    private static $shortcodes = [];

    /**
     * Shortcode regex pattern
     */
    private static $pattern = '/\[(\w+)(?:\s+([^\]]*))?\]/';

    /**
     * Shortcode ekler
     *
     * @param string $tag Shortcode etiketi
     * @param callable $callback Callback fonksiyonu
     * @return void
     */
    public static function add($tag, $callback)
    {
        self::$shortcodes[$tag] = $callback;
    }

    /**
     * Shortcode'u kaldırır
     *
     * @param string $tag Shortcode etiketi
     * @return void
     */
    public static function remove($tag)
    {
        unset(self::$shortcodes[$tag]);
    }

    /**
     * İçerikteki shortcode'ları işler
     *
     * @param string $content İçerik
     * @return string İşlenmiş içerik
     */
    public static function process($content)
    {
        if (empty(self::$shortcodes) || !is_string($content)) {
            return $content;
        }

        return preg_replace_callback(self::$pattern, [self::class, 'processCallback'], $content);
    }

    /**
     * Shortcode callback işleyicisi
     *
     * @param array $matches Regex eşleşmeleri
     * @return string İşlenmiş shortcode
     */
    private static function processCallback($matches)
    {
        $tag = $matches[1];
        $atts = isset($matches[2]) ? self::parseAttributes($matches[2]) : [];

        if (!isset(self::$shortcodes[$tag])) {
            return $matches[0]; // Shortcode bulunamazsa orijinal metni döndür
        }

        try {
            return call_user_func(self::$shortcodes[$tag], $atts);
        } catch (Exception $e) {
            return '<!-- Shortcode Error: ' . $e->getMessage() . ' -->';
        }
    }

    /**
     * Shortcode özniteliklerini parse eder
     *
     * @param string $text Öznitelik metni
     * @return array Parse edilmiş öznitelikler
     */
    private static function parseAttributes($text)
    {
        $atts = [];
        $pattern = '/(\w+)=(["\'])(.*?)\2|(\w+)=(\S+)|(\w+)/';

        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (!empty($match[1])) {
                // key="value" veya key='value'
                $atts[$match[1]] = $match[3];
            } elseif (!empty($match[4])) {
                // key=value
                $atts[$match[4]] = $match[5];
            } elseif (!empty($match[6])) {
                // sadece key
                $atts[$match[6]] = true;
            }
        }

        return $atts;
    }

    /**
     * Tüm shortcode'ları döndürür
     *
     * @return array Shortcode'lar
     */
    public static function getAll()
    {
        return self::$shortcodes;
    }

    /**
     * Shortcode var mı kontrol eder
     *
     * @param string $tag Shortcode etiketi
     * @return bool Var mı?
     */
    public static function exists($tag)
    {
        return isset(self::$shortcodes[$tag]);
    }
}
