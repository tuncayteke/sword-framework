<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * ModelCache sınıfı - Model önbellek sistemi
 */

require_once __DIR__ . '/CacheInterface.php';

class ModelCache implements CacheInterface
{
    /**
     * Önbellek dizini
     */
    private $cacheDir;

    /**
     * Şifreleme sınıfı
     */
    private $cryptor = null;

    /**
     * Şifreleme kullanılsın mı?
     */
    private $useEncryption = false;

    /**
     * Yapılandırıcı
     *
     * @param string|null $cacheDir Önbellek dizini
     * @param bool $useEncryption Şifreleme kullanılsın mı?
     * @param Cryptor|null $cryptor Şifreleme sınıfı
     */
    public function __construct($cacheDir = null, $useEncryption = false, $cryptor = null)
    {
        // Önbellek dizinini ayarla
        if ($cacheDir === null) {
            $cacheDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'models';
        }

        $this->cacheDir = rtrim($cacheDir, '/\\') . DIRECTORY_SEPARATOR;

        // Dizin yoksa oluştur
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }

        // Şifreleme ayarları
        $this->useEncryption = $useEncryption;

        if ($useEncryption) {
            if ($cryptor === null) {
                require_once dirname(__DIR__) . '/Cryptor.php';
                $this->cryptor = new Cryptor();
            } else {
                $this->cryptor = $cryptor;
            }
        }
    }

    /**
     * Önbelleğe veri ekler
     *
     * @param string $key Anahtar
     * @param mixed $value Değer
     * @param int $ttl Yaşam süresi (saniye)
     * @return bool Başarılı mı?
     */
    public function set($key, $value, $ttl = 3600)
    {
        $filename = $this->getFilename($key);

        $data = [
            'expires' => time() + $ttl,
            'value' => $value
        ];

        // Veriyi serileştir
        $content = serialize($data);

        // Şifreleme kullanılıyorsa şifrele
        if ($this->useEncryption && $this->cryptor !== null) {
            $content = $this->cryptor->encrypt($content);
        }

        return file_put_contents($filename, $content, LOCK_EX) !== false;
    }

    /**
     * Önbellekten veri alır
     *
     * @param string $key Anahtar
     * @param mixed $default Varsayılan değer
     * @return mixed Değer
     */
    public function get($key, $default = null)
    {
        $filename = $this->getFilename($key);

        if (!file_exists($filename)) {
            return $default;
        }

        $content = file_get_contents($filename);

        if ($content === false) {
            return $default;
        }

        // Şifreleme kullanılıyorsa çöz
        if ($this->useEncryption && $this->cryptor !== null) {
            try {
                $content = $this->cryptor->decrypt($content);
            } catch (Exception $e) {
                return $default;
            }
        }

        // Veriyi çöz
        if (!is_string($content)) {
            return $default;
        }

        $data = @unserialize($content);

        // Unserialize başarısız olursa veya geçerli bir veri yapısı değilse
        if ($data === false || !is_array($data) || !isset($data['expires']) || !isset($data['value'])) {
            $this->delete($key);
            return $default;
        }

        // Süre dolmuşsa sil ve varsayılan değeri döndür
        if ($data['expires'] < time()) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Önbellekte anahtar var mı?
     *
     * @param string $key Anahtar
     * @return bool Var mı?
     */
    public function has($key)
    {
        $filename = $this->getFilename($key);

        if (!file_exists($filename)) {
            return false;
        }

        $content = file_get_contents($filename);

        if ($content === false) {
            return false;
        }

        // Şifreleme kullanılıyorsa çöz
        if ($this->useEncryption && $this->cryptor !== null) {
            try {
                $content = $this->cryptor->decrypt($content);
            } catch (Exception $e) {
                return false;
            }
        }

        // Veriyi çöz
        if (!is_string($content)) {
            return false;
        }

        $data = @unserialize($content);

        // Unserialize başarısız olursa veya geçerli bir veri yapısı değilse
        if ($data === false || !is_array($data) || !isset($data['expires'])) {
            $this->delete($key);
            return false;
        }

        // Süre dolmuşsa sil ve false döndür
        if ($data['expires'] < time()) {
            $this->delete($key);
            return false;
        }

        return true;
    }

    /**
     * Önbellekten veri siler
     *
     * @param string $key Anahtar
     * @return bool Başarılı mı?
     */
    public function delete($key)
    {
        $filename = $this->getFilename($key);

        if (file_exists($filename)) {
            return unlink($filename);
        }

        return true;
    }

    /**
     * Tüm önbelleği temizler
     *
     * @return bool Başarılı mı?
     */
    public function clear()
    {
        $files = glob($this->cacheDir . '*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Önbellekte yoksa ekler, varsa alır
     *
     * @param string $key Anahtar
     * @param callable $callback Değer üretecek fonksiyon
     * @param int $ttl Yaşam süresi (saniye)
     * @return mixed Değer
     */
    public function remember($key, callable $callback, $ttl = 3600)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Anahtar için dosya adı oluşturur
     *
     * @param string $key Anahtar
     * @return string Dosya adı
     */
    private function getFilename($key)
    {
        return $this->cacheDir . md5($key) . '.model.cache';
    }

    /**
     * Model sorgu sonucunu önbelleğe alır
     *
     * @param string $model Model adı
     * @param string $method Metod adı
     * @param array $params Parametreler
     * @param mixed $result Sonuç
     * @param int $ttl Yaşam süresi (saniye)
     * @return bool Başarılı mı?
     */
    public function cacheQuery($model, $method, array $params = [], $result, $ttl = 3600)
    {
        $key = $this->generateQueryKey($model, $method, $params);
        return $this->set($key, $result, $ttl);
    }

    /**
     * Model sorgu sonucunu önbellekten alır
     *
     * @param string $model Model adı
     * @param string $method Metod adı
     * @param array $params Parametreler
     * @param mixed $default Varsayılan değer
     * @return mixed Sonuç
     */
    public function getQuery($model, $method, array $params = [], $default = null)
    {
        $key = $this->generateQueryKey($model, $method, $params);
        return $this->get($key, $default);
    }

    /**
     * Model sorgu sonucunu önbellekten siler
     *
     * @param string $model Model adı
     * @param string $method Metod adı
     * @param array $params Parametreler
     * @return bool Başarılı mı?
     */
    public function deleteQuery($model, $method, array $params = [])
    {
        $key = $this->generateQueryKey($model, $method, $params);
        return $this->delete($key);
    }

    /**
     * Model için tüm önbelleği temizler
     *
     * @param string $model Model adı
     * @return bool Başarılı mı?
     */
    public function clearModel($model)
    {
        $prefix = md5($model);
        $files = glob($this->cacheDir . $prefix . '*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Sorgu için anahtar oluşturur
     *
     * @param string $model Model adı
     * @param string $method Metod adı
     * @param array $params Parametreler
     * @return string Anahtar
     */
    private function generateQueryKey($model, $method, array $params = [])
    {
        return md5($model . '::' . $method . '::' . serialize($params));
    }

    /**
     * Şifreleme kullanımını ayarlar
     *
     * @param bool $useEncryption Şifreleme kullanılsın mı?
     * @return ModelCache
     */
    public function setUseEncryption($useEncryption)
    {
        $this->useEncryption = $useEncryption;

        if ($useEncryption && $this->cryptor === null) {
            require_once dirname(__DIR__) . '/Cryptor.php';
            $this->cryptor = new Cryptor();
        }

        return $this;
    }

    /**
     * Şifreleme sınıfını ayarlar
     *
     * @param Cryptor $cryptor Şifreleme sınıfı
     * @return ModelCache
     */
    public function setCryptor($cryptor)
    {
        $this->cryptor = $cryptor;
        return $this;
    }
}
