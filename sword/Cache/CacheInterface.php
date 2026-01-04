<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Cache arayüzü - Tüm cache sınıfları için temel arayüz
 */

interface CacheInterface
{
    /**
     * Önbelleğe veri ekler
     *
     * @param string $key Anahtar
     * @param mixed $value Değer
     * @param int $ttl Yaşam süresi (saniye)
     * @return bool Başarılı mı?
     */
    public function set($key, $value, $ttl = 3600);

    /**
     * Önbellekten veri alır
     *
     * @param string $key Anahtar
     * @param mixed $default Varsayılan değer
     * @return mixed Değer
     */
    public function get($key, $default = null);

    /**
     * Önbellekte anahtar var mı?
     *
     * @param string $key Anahtar
     * @return bool Var mı?
     */
    public function has($key);

    /**
     * Önbellekten veri siler
     *
     * @param string $key Anahtar
     * @return bool Başarılı mı?
     */
    public function delete($key);

    /**
     * Tüm önbelleği temizler
     *
     * @return bool Başarılı mı?
     */
    public function clear();

    /**
     * Önbellekte yoksa ekler, varsa alır
     *
     * @param string $key Anahtar
     * @param callable $callback Değer üretecek fonksiyon
     * @param int $ttl Yaşam süresi (saniye)
     * @return mixed Değer
     */
    public function remember($key, callable $callback, $ttl = 3600);
}
