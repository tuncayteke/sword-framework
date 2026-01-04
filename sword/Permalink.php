<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Permalink sınıfı - SEO dostu URL oluşturucu
 */

class Permalink
{
    /**
     * String'i SEO dostu slug'a çevirir
     *
     * @param string $string Çevrilecek string
     * @param string $separator Ayırıcı karakter
     * @return string
     */
    public static function slug(string $string, string $separator = '-'): string
    {
        $string = trim($string);

        // 1. HTML entity temizle
        $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');

        // 2. Türkçe özel karakterler (manuel + kesin çözüm)
        $turkce = ['Ğ' => 'G', 'ğ' => 'g', 'Ü' => 'U', 'ü' => 'u', 'Ş' => 'S', 'ş' => 's', 'İ' => 'I', 'ı' => 'i', 'Ö' => 'O', 'ö' => 'o', 'Ç' => 'C', 'ç' => 'c'];
        $string = strtr($string, $turkce);

        // 3. PHP Intl ile CJK desteği (Çince, Japonca, Korece, Arapça, Rusça)
        if (extension_loaded('intl')) {
            $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII; Lower();');
            if ($transliterator) {
                $string = $transliterator->transliterate($string);
            }
        }

        // 4. Yedek: iconv ile ASCII translit
        if (function_exists('iconv') && (!extension_loaded('intl') || empty($string))) {
            $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        }

        // 5. Küçük harfe çevir
        $string = mb_strtolower($string, 'UTF-8');

        // 6. Harf, rakam, boşluk ve tire dışında her şeyi sil
        $string = preg_replace('/[^a-z0-9\s-]/u', ' ', $string);

        // 7. Birden fazla boşluğu tek ayırıcıya çevir
        $string = preg_replace('/[\s-]+/', $separator, $string);

        // 8. Baştaki ve sondaki ayırıcıları temizle
        return trim($string, $separator);
    }

    /**
     * Benzersiz slug oluşturur (DB çakışması olmasın)
     *
     * @param string $title Başlık
     * @param string $table Tablo adı
     * @param string $column Sütun adı
     * @param mixed $ignoreId Göz ardı edilecek ID
     * @return string
     */
    public static function uniqueSlug(string $title, string $table, string $column = 'slug', $ignoreId = null): string
    {
        $slug = self::slug($title);
        $original = $slug;
        $i = 1;

        $query = Sword::db()->table($table)->where($column, $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $original . '-' . $i++;
            $query = Sword::db()->table($table)->where($column, $slug);

            if ($ignoreId !== null) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }

    /**
     * URL'den slug çıkarır
     *
     * @param string $url URL
     * @return string
     */
    public static function extractSlug(string $url): string
    {
        $parts = explode('/', trim($url, '/'));
        return end($parts);
    }

    /**
     * Slug'ı başlığa çevirir
     *
     * @param string $slug Slug
     * @return string
     */
    public static function slugToTitle(string $slug): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $slug));
    }

    /**
     * Permalink URL'si oluşturur
     *
     * @param string $slug Slug
     * @param string $prefix Ön ek (örn: 'blog', 'product')
     * @return string
     */
    public static function url(string $slug, string $prefix = ''): string
    {
        $path = $prefix ? $prefix . '/' . $slug : $slug;
        return Sword::url($path);
    }
}
