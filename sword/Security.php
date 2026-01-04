<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Security sınıfı - Güvenlik işlemlerini yönetir
 */

class Security
{
    /**
     * CSRF token anahtarı
     */
    private static $csrfTokenName = 'sword_csrf_token';

    /**
     * CSRF token değeri
     */
    private static $csrfTokenValue = null;

    /**
     * CSRF token'ın geçerlilik süresi (saniye)
     */
    private static $csrfExpire = 7200; // 2 saat

    /**
     * XSS temizleme için izin verilen HTML etiketleri
     */
    private static $allowedHtmlTags = '<p><a><b><i><strong><em><u><h1><h2><h3><h4><h5><h6><pre><code><ul><ol><li><br><hr>';

    /**
     * CSRF token'ı oluşturur veya mevcut token'ı döndürür
     *
     * @param bool $new Yeni token oluştur
     * @return string CSRF token
     */
    public static function getCsrfToken($new = false)
    {
        if (self::$csrfTokenValue === null || $new) {
            // Oturum başlatılmamışsa başlat
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Token oluştur
            if (!isset($_SESSION[self::$csrfTokenName]) || $new) {
                $_SESSION[self::$csrfTokenName] = bin2hex(random_bytes(32));
                $_SESSION[self::$csrfTokenName . '_time'] = time();
            }

            self::$csrfTokenValue = $_SESSION[self::$csrfTokenName];
        }

        return self::$csrfTokenValue;
    }

    /**
     * CSRF token'ını doğrular
     *
     * @param string|null $token Doğrulanacak token
     * @return bool Token geçerli mi?
     */
    public static function validateCsrfToken($token = null)
    {
        // Oturum başlatılmamışsa başlat
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Token belirtilmemişse POST veya GET'ten al
        if ($token === null) {
            $token = isset($_POST[self::$csrfTokenName]) ? $_POST[self::$csrfTokenName] : (isset($_GET[self::$csrfTokenName]) ? $_GET[self::$csrfTokenName] : null);
        }

        // Token yoksa veya oturumda token yoksa geçersiz
        if ($token === null || !isset($_SESSION[self::$csrfTokenName])) {
            return false;
        }

        // Token süresi dolmuşsa geçersiz
        if (
            isset($_SESSION[self::$csrfTokenName . '_time']) &&
            time() - $_SESSION[self::$csrfTokenName . '_time'] > self::$csrfExpire
        ) {
            unset($_SESSION[self::$csrfTokenName]);
            unset($_SESSION[self::$csrfTokenName . '_time']);
            return false;
        }

        // Token eşleşmiyorsa geçersiz
        if ($token !== $_SESSION[self::$csrfTokenName]) {
            return false;
        }

        return true;
    }

    /**
     * CSRF token'ını HTML form için hazırlar
     *
     * @return string HTML input elementi
     */
    public static function csrfField()
    {
        $token = self::getCsrfToken();
        return '<input type="hidden" name="' . self::$csrfTokenName . '" value="' . $token . '">';
    }

    /**
     * CSRF token'ını meta etiketi olarak hazırlar
     *
     * @return string HTML meta etiketi
     */
    public static function csrfMeta()
    {
        $token = self::getCsrfToken();
        return '<meta name="' . self::$csrfTokenName . '" content="' . $token . '">';
    }

    /**
     * XSS saldırılarına karşı veriyi temizler
     *
     * @param string|array $data Temizlenecek veri
     * @param bool $allowHtml HTML etiketlerine izin ver
     * @return string|array Temizlenmiş veri
     */
    public static function xssClean($data, $allowHtml = false)
    {
        // Dizi ise her elemanı temizle
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::xssClean($value, $allowHtml);
            }
            return $data;
        }

        // String değilse dönüştür
        if (!is_string($data)) {
            return $data;
        }

        // HTML etiketlerine izin verilmiyorsa tamamen temizle
        if (!$allowHtml) {
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        } else {
            // İzin verilen HTML etiketleri dışındakileri temizle
            $data = strip_tags($data, self::$allowedHtmlTags);

            // HTML özniteliklerindeki tehlikeli içeriği temizle
            $data = preg_replace('/(on\w+)=".*?"/i', '', $data); // onclick, onload vb. kaldır
            $data = preg_replace('/javascript:[^\s]*/i', '', $data); // javascript: protokolünü kaldır
        }

        return $data;
    }

    /**
     * SQL enjeksiyonuna karşı veriyi temizler
     *
     * @param string|array $data Temizlenecek veri
     * @return string|array Temizlenmiş veri
     */
    public static function sqlEscape($data)
    {
        // Dizi ise her elemanı temizle
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sqlEscape($value);
            }
            return $data;
        }

        // String değilse dönüştür
        if (!is_string($data)) {
            return $data;
        }

        // Bağlantı varsa mysqli_real_escape_string kullan
        if (function_exists('mysqli_real_escape_string') && isset($GLOBALS['db_connection'])) {
            return mysqli_real_escape_string($GLOBALS['db_connection'], $data);
        }

        // Yoksa addslashes kullan (daha az güvenli)
        return addslashes($data);
    }

    /**
     * Güvenli şifre hash'i oluşturur
     *
     * @param string $password Şifre
     * @return string Hash
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Şifre hash'ini doğrular
     *
     * @param string $password Şifre
     * @param string $hash Hash
     * @return bool Şifre doğru mu?
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Güvenli rastgele token oluşturur
     *
     * @param int $length Token uzunluğu
     * @return string Token
     */
    public static function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * CSRF token'ının geçerlilik süresini ayarlar
     *
     * @param int $seconds Saniye
     * @return void
     */
    public static function setCsrfExpire($seconds)
    {
        self::$csrfExpire = $seconds;
    }

    /**
     * XSS temizleme için izin verilen HTML etiketlerini ayarlar
     *
     * @param string $tags İzin verilen etiketler
     * @return void
     */
    public static function setAllowedHtmlTags($tags)
    {
        self::$allowedHtmlTags = $tags;
    }

    /**
     * CSRF token adını ayarlar
     *
     * @param string $name Token adı
     * @return void
     */
    public static function setCsrfTokenName($name)
    {
        self::$csrfTokenName = $name;
    }
}
