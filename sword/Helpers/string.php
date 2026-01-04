<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * String Helper Functions
 */

if (!function_exists('str_limit')) {
    /**
     * Limit string length
     *
     * @param string $text Text
     * @param int $limit Limit
     * @param string $end End string
     * @return string
     */
    function str_limit(string $text, int $limit = 100, string $end = '...'): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit) . $end;
    }
}

if (!function_exists('str_random')) {
    /**
     * Generate random string
     *
     * @param int $length Length
     * @return string
     */
    function str_random(int $length = 16): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 0, $length);
    }
}

if (!function_exists('slug')) {
    /**
     * Generate SEO-friendly slug
     *
     * @param string $string String to slugify
     * @param string $separator Separator
     * @return string
     */
    function slug(string $string, string $separator = '-'): string
    {
        return \Sword\Permalink::slug($string, $separator);
    }
}

if (!function_exists('unique_slug')) {
    /**
     * Generate unique slug
     *
     * @param string $title Title
     * @param string $table Table name
     * @param string $column Column name
     * @param mixed $ignoreId Ignore ID
     * @return string
     */
    function unique_slug(string $title, string $table, string $column = 'slug', $ignoreId = null): string
    {
        return \Sword\Permalink::uniqueSlug($title, $table, $column, $ignoreId);
    }
}
