<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Session sınıfı - Oturum yönetimi
 */

class Session
{
    /**
     * Session başlatıldı mı?
     */
    private static $started = false;

    /**
     * Session'ı başlatır
     *
     * @param array $options Session seçenekleri
     * @return bool Başlatma başarılı mı?
     */
    public static function start(array $options = [])
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            return true;
        }

        // Varsayılan seçenekleri ayarla
        $defaultOptions = [
            'name' => Sword::getData('session_name', 'SWORD_SESSION'),
            'lifetime' => Sword::getData('session_lifetime', 3600),
            'path' => Sword::getData('cookie_path', '/'),
            'domain' => Sword::getData('cookie_domain', ''),
            'secure' => Sword::getData('cookie_secure', isset($_SERVER['HTTPS'])),
            'httponly' => Sword::getData('cookie_httponly', true),
            'samesite' => Sword::getData('cookie_samesite', 'Lax')
        ];

        $options = array_merge($defaultOptions, $options);

        // Session ayarlarını uygula
        session_name($options['name']);
        session_set_cookie_params([
            'lifetime' => $options['lifetime'],
            'path' => $options['path'],
            'domain' => $options['domain'],
            'secure' => $options['secure'],
            'httponly' => $options['httponly'],
            'samesite' => $options['samesite']
        ]);

        // Session'ı başlat
        $result = session_start();
        self::$started = $result;

        return $result;
    }

    /**
     * Session değeri ayarlar
     *
     * @param string $key Anahtar
     * @param mixed $value Değer
     * @return void
     */
    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Session değeri alır
     *
     * @param string $key Anahtar
     * @param mixed $default Varsayılan değer
     * @return mixed Değer
     */
    public static function get($key, $default = null)
    {
        self::start();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    /**
     * Session değeri var mı?
     *
     * @param string $key Anahtar
     * @return bool Var mı?
     */
    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Session değerini siler
     *
     * @param string $key Anahtar
     * @return void
     */
    public static function remove($key)
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Tüm session verilerini temizler
     *
     * @return void
     */
    public static function clear()
    {
        self::start();
        $_SESSION = [];
    }

    /**
     * Session'ı yok eder
     *
     * @return bool Yok etme başarılı mı?
     */
    public static function destroy()
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];

            // Session çerezini sil
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

            $result = session_destroy();
            self::$started = false;
            return $result;
        }

        return true;
    }

    /**
     * Session ID'sini yeniler
     *
     * @param bool $deleteOldSession Eski session'ı sil
     * @return bool Yenileme başarılı mı?
     */
    public static function regenerate($deleteOldSession = true)
    {
        self::start();
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Flash mesaj ayarlar
     *
     * @param string $key Anahtar
     * @param mixed $value Değer
     * @return void
     */
    public static function flash($key, $value)
    {
        self::set('_flash_' . $key, $value);
    }

    /**
     * Flash mesajı alır ve siler
     *
     * @param string $key Anahtar
     * @param mixed $default Varsayılan değer
     * @return mixed Değer
     */
    public static function getFlash($key, $default = null)
    {
        $flashKey = '_flash_' . $key;
        $value = self::get($flashKey, $default);
        self::remove($flashKey);
        return $value;
    }

    /**
     * Tüm session verilerini döndürür
     *
     * @return array Session verileri
     */
    public static function all()
    {
        self::start();
        return $_SESSION;
    }

    /**
     * Session ID'sini döndürür
     *
     * @return string Session ID
     */
    public static function getId()
    {
        return session_id();
    }

    /**
     * Session başlatıldı mı?
     *
     * @return bool Başlatıldı mı?
     */
    public static function isStarted()
    {
        return self::$started || session_status() === PHP_SESSION_ACTIVE;
    }
}
