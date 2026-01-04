<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Cryptor sınıfı - OpenSSL ile şifreleme işlemlerini yönetir
 */

class Cryptor
{
    /**
     * Şifreleme anahtarı
     */
    private $key;

    /**
     * Şifreleme metodu
     */
    private $method;

    /**
     * Şifreleme seçenekleri
     */
    private $options;

    /**
     * Yapılandırıcı
     *
     * @param string|null $key Şifreleme anahtarı
     * @param string|null $method Şifreleme metodu
     * @param int $options Şifreleme seçenekleri
     */
    public function __construct($key = null, $method = null, $options = 0)
    {
        // db_config.php'den sabit anahtarı kullan
        if (defined('CRYPTOR_KEY')) {
            $this->key = CRYPTOR_KEY;
        } else {
            // Sabit tanımlı değilse, parametre olarak verilen anahtarı kullan
            $this->key = $key ?: 'kiloseErsede235r4.eR';

            // Üretim ortamında varsayılan anahtar kullanılıyorsa uyarı ver
            if ($this->key === 'kiloseErsede235r4.eR' && (!defined('ENVIRONMENT') || ENVIRONMENT === 'production')) {
                trigger_error('Güvenlik uyarısı: Varsayılan şifreleme anahtarı kullanılıyor. Üretim ortamında db_config.php dosyasında CRYPTOR_KEY tanımlayın.', E_USER_WARNING);
            }
        }

        // Şifreleme metodu her zaman sabit olmalı - Constants sınıfından al
        if (class_exists('\\Sword\\Config\\Constants')) {
            $this->method = \Sword\Config\Constants::CRYPTOR_METHOD;
        } else if ($method !== null) {
            // Constants sınıfı yoksa, parametre olarak verilen metodu kullan
            $this->method = $method;
        } else {
            // Varsayılan metod
            $this->method = 'AES-256-CBC';
        }

        $this->options = $options;

        // OpenSSL kurulu mu kontrol et
        if (!extension_loaded('openssl')) {
            throw new Exception('OpenSSL PHP eklentisi yüklü değil.');
        }
    }

    /**
     * Veriyi şifreler
     *
     * @param mixed $data Şifrelenecek veri
     * @return string Şifrelenmiş veri
     */
    public function encrypt($data)
    {
        // Veriyi serileştir (array, object vb. desteklemek için)
        if (!is_string($data)) {
            $data = serialize($data);
        }

        // Rastgele bir IV (Initialization Vector) oluştur
        $ivSize = openssl_cipher_iv_length($this->method);
        $iv = openssl_random_pseudo_bytes($ivSize);

        // Veriyi şifrele
        $encrypted = openssl_encrypt(
            $data,
            $this->method,
            $this->key,
            (int)$this->options, // options parametresini integer'a dönüştür
            $iv
        );

        if ($encrypted === false) {
            throw new Exception('Veri şifrelenirken bir hata oluştu: ' . openssl_error_string());
        }

        // IV'yi şifrelenmiş verinin başına ekle ve base64 ile kodla
        $result = base64_encode($iv . $encrypted);

        return $result;
    }

    /**
     * Şifrelenmiş veriyi çözer
     *
     * @param string $data Şifrelenmiş veri
     * @param bool $unserialize Serileştirilmiş veriyi çöz
     * @return mixed Çözülmüş veri
     */
    public function decrypt($data, $unserialize = true)
    {
        // Base64 kodlamasını çöz
        $data = base64_decode($data);

        // IV boyutunu al
        $ivSize = openssl_cipher_iv_length($this->method);

        // IV'yi ayır
        $iv = substr($data, 0, $ivSize);
        $encrypted = substr($data, $ivSize);

        // Veriyi çöz
        $decrypted = openssl_decrypt(
            $encrypted,
            $this->method,
            $this->key,
            (int)$this->options, // options parametresini integer'a dönüştür
            $iv
        );

        if ($decrypted === false) {
            throw new Exception('Veri çözülürken bir hata oluştu: ' . openssl_error_string());
        }

        // Serileştirilmiş veriyi çöz
        if ($unserialize && $this->isSerialized($decrypted)) {
            $decrypted = unserialize($decrypted);
        }

        return $decrypted;
    }

    /**
     * Bir string'in serileştirilmiş olup olmadığını kontrol eder
     *
     * @param string $data Kontrol edilecek veri
     * @return bool Serileştirilmiş mi?
     */
    private function isSerialized($data)
    {
        // Boş değilse ve string ise
        if (!is_string($data) || empty($data)) {
            return false;
        }

        // Serileştirilmiş verinin başlangıç karakterlerini kontrol et
        $firstChar = $data[0];
        $lastChar = $data[strlen($data) - 1];

        // Serileştirilmiş veri formatını kontrol et
        if ($firstChar === 'a' && $lastChar === '}') { // array
            return true;
        }

        if ($firstChar === 'O' && $lastChar === '}') { // object
            return true;
        }

        if ($firstChar === 's' && $lastChar === '"') { // string
            return true;
        }

        if ($firstChar === 'i' && $lastChar === ';') { // integer
            return true;
        }

        if ($firstChar === 'd' && $lastChar === ';') { // double/float
            return true;
        }

        if ($firstChar === 'b' && ($lastChar === ';' || $lastChar === '0' || $lastChar === '1')) { // boolean
            return true;
        }

        if ($firstChar === 'N' && $lastChar === ';') { // null
            return true;
        }

        return false;
    }

    /**
     * Şifreleme anahtarını ayarlar
     *
     * @param string $key Şifreleme anahtarı
     * @return Cryptor
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Şifreleme metodunu ayarlar
     *
     * @param string $method Şifreleme metodu
     * @return Cryptor
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Şifreleme seçeneklerini ayarlar
     *
     * @param int $options Şifreleme seçenekleri
     * @return Cryptor
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Desteklenen şifreleme metodlarını döndürür
     *
     * @return array Şifreleme metodları
     */
    public static function getSupportedMethods()
    {
        return openssl_get_cipher_methods();
    }

    /**
     * Güvenli bir şifreleme anahtarı oluşturur
     *
     * @param int $length Anahtar uzunluğu
     * @return string Şifreleme anahtarı
     */
    public static function generateKey($length = 32)
    {
        return bin2hex(openssl_random_pseudo_bytes($length / 2));
    }
}
