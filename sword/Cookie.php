<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Cookie sınıfı - Güvenli cookie yönetimi
 */

class Cookie
{
    /**
     * Cookie ayarlar
     *
     * @param string $name Cookie adı
     * @param mixed $value Cookie değeri
     * @param int $expire Geçerlilik süresi (saniye, 0 = session)
     * @param array $options Ek seçenekler
     * @return bool Başarılı mı?
     */
    public static function set($name, $value, $expire = 0, array $options = [])
    {
        // Varsayılan seçenekleri al
        $defaultOptions = [
            'path' => Sword::getData('cookie_path', '/'),
            'domain' => Sword::getData('cookie_domain') ?: self::getDefaultDomain(),
            'secure' => Sword::getData('cookie_secure', isset($_SERVER['HTTPS'])),
            'httponly' => Sword::getData('cookie_httponly', true),
            'samesite' => Sword::getData('cookie_samesite', 'Lax'),
            'encrypt' => false
        ];

        $options = array_merge($defaultOptions, $options);

        // Cookie adına prefix ekle
        $prefix = Sword::getData('cookie_prefix', 'sword_');
        $cookieName = $prefix . $name;

        // Değeri şifrele (istenirse)
        if ($options['encrypt'] && !is_null($value)) {
            $value = Sword::cryptor()->encrypt(serialize($value));
        } else if (!is_scalar($value) && !is_null($value)) {
            $value = serialize($value);
        }

        // Expire time hesapla
        $expireTime = $expire > 0 ? time() + $expire : 0;

        // PHP 7.3+ için yeni format
        if (PHP_VERSION_ID >= 70300) {
            return setcookie($cookieName, $value, [
                'expires' => $expireTime,
                'path' => $options['path'],
                'domain' => $options['domain'],
                'secure' => $options['secure'],
                'httponly' => $options['httponly'],
                'samesite' => $options['samesite']
            ]);
        } else {
            // Eski PHP sürümleri için
            return setcookie(
                $cookieName,
                $value,
                $expireTime,
                $options['path'],
                $options['domain'],
                $options['secure'],
                $options['httponly']
            );
        }
    }

    /**
     * Cookie değeri alır
     *
     * @param string $name Cookie adı
     * @param mixed $default Varsayılan değer
     * @param bool $encrypted Şifrelenmiş mi?
     * @return mixed Cookie değeri
     */
    public static function get($name, $default = null, $encrypted = false)
    {
        $prefix = Sword::getData('cookie_prefix', 'sword_');
        $cookieName = $prefix . $name;

        if (!isset($_COOKIE[$cookieName])) {
            return $default;
        }

        $value = $_COOKIE[$cookieName];

        // Şifrelenmiş ise çöz
        if ($encrypted) {
            try {
                $decrypted = Sword::cryptor()->decrypt($value);
                return unserialize($decrypted);
            } catch (Exception $e) {
                return $default;
            }
        }

        // Serialize edilmiş veri kontrolü
        $unserialized = @unserialize($value);
        return $unserialized !== false ? $unserialized : $value;
    }

    /**
     * Cookie var mı kontrol eder
     *
     * @param string $name Cookie adı
     * @return bool Var mı?
     */
    public static function has($name)
    {
        $prefix = Sword::getData('cookie_prefix', 'sword_');
        $cookieName = $prefix . $name;
        return isset($_COOKIE[$cookieName]);
    }

    /**
     * Cookie siler
     *
     * @param string $name Cookie adı
     * @param array $options Ek seçenekler
     * @return bool Başarılı mı?
     */
    public static function delete($name, array $options = [])
    {
        $defaultOptions = [
            'path' => Sword::getData('cookie_path', '/'),
            'domain' => Sword::getData('cookie_domain') ?: self::getDefaultDomain(),
            'secure' => Sword::getData('cookie_secure', isset($_SERVER['HTTPS'])),
            'httponly' => Sword::getData('cookie_httponly', true)
        ];

        $options = array_merge($defaultOptions, $options);

        $prefix = Sword::getData('cookie_prefix', 'sword_');
        $cookieName = $prefix . $name;

        // Cookie'yi sil
        unset($_COOKIE[$cookieName]);

        // Tarayıcıdan da sil
        return setcookie(
            $cookieName,
            '',
            time() - 3600,
            $options['path'],
            $options['domain'],
            $options['secure'],
            $options['httponly']
        );
    }

    /**
     * Tüm framework cookie'lerini siler
     *
     * @return void
     */
    public static function clear()
    {
        $prefix = Sword::getData('cookie_prefix', 'sword_');

        foreach ($_COOKIE as $name => $value) {
            if (strpos($name, $prefix) === 0) {
                $cookieName = substr($name, strlen($prefix));
                self::delete($cookieName);
            }
        }
    }

    /**
     * Şifrelenmiş cookie ayarlar
     *
     * @param string $name Cookie adı
     * @param mixed $value Cookie değeri
     * @param int $expire Geçerlilik süresi
     * @param array $options Ek seçenekler
     * @return bool Başarılı mı?
     */
    public static function setEncrypted($name, $value, $expire = 0, array $options = [])
    {
        $options['encrypt'] = true;
        return self::set($name, $value, $expire, $options);
    }

    /**
     * Şifrelenmiş cookie alır
     *
     * @param string $name Cookie adı
     * @param mixed $default Varsayılan değer
     * @return mixed Cookie değeri
     */
    public static function getEncrypted($name, $default = null)
    {
        return self::get($name, $default, true);
    }

    /**
     * Remember me cookie ayarlar
     *
     * @param string $token Remember token
     * @param int $days Gün sayısı
     * @return bool Başarılı mı?
     */
    public static function setRememberToken($token, $days = 30)
    {
        return self::setEncrypted('remember_token', $token, $days * 24 * 3600, [
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }

    /**
     * Remember me token alır
     *
     * @return string|null Token
     */
    public static function getRememberToken()
    {
        return self::getEncrypted('remember_token');
    }

    /**
     * Remember me token siler
     *
     * @return bool Başarılı mı?
     */
    public static function deleteRememberToken()
    {
        return self::delete('remember_token');
    }

    /**
     * Varsayılan domain tespit eder
     *
     * @return string Domain
     */
    private static function getDefaultDomain()
    {
        // Sword sınıfından domain al
        if (class_exists('Sword') && method_exists('Sword', 'getCurrentDomain')) {
            return Sword::getCurrentDomain();
        }

        // Manuel tespit
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $domain = explode(':', $host)[0];

        // Localhost veya IP adresi ise boş bırak
        if (
            $domain === 'localhost' ||
            filter_var($domain, FILTER_VALIDATE_IP) ||
            strpos($domain, '.') === false
        ) {
            return '';
        }

        return $domain;
    }
}
