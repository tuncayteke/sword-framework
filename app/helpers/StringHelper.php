<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * String Helper - String işlemleri için yardımcı sınıf
 */

namespace App\Helpers;

class StringHelper
{

    /**
     * Metni slug formatına çevirir
     */
    public static function slugify($text)
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }

    /**
     * Metni kısaltır
     */
    public static function truncate($text, $length = 100, $suffix = '...')
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $suffix;
    }
}
