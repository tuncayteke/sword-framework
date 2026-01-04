<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Events sınıfı - Olay yönetimini sağlar
 */

class Events
{
    /**
     * Kayıtlı olaylar
     */
    private static $events = [];

    /**
     * Sistem olayları
     */
    const PRE_SYSTEM = 'pre_system';
    const BEFORE_CONTROLLER_CONSTRUCTOR = 'before_controller_constructor';
    const BEFORE_CONTROLLER_METHOD = 'before_controller_method';
    const AFTER_CONTROLLER_METHOD = 'after_controller_method';
    const POST_SYSTEM = 'post_system';

    /**
     * Bir olaya dinleyici ekler
     *
     * @param string $event Olay adı
     * @param callable $callback Geri çağırma fonksiyonu
     * @param int $priority Öncelik (düşük değer daha yüksek öncelik)
     * @return void
     */
    public static function on($event, callable $callback, $priority = 10)
    {
        if (!isset(self::$events[$event])) {
            self::$events[$event] = [];
        }

        // Aynı öncelikte başka dinleyiciler varsa, sonuna ekle
        while (isset(self::$events[$event][$priority])) {
            $priority++;
        }

        self::$events[$event][$priority] = $callback;

        // Önceliğe göre sırala
        ksort(self::$events[$event]);
    }

    /**
     * Bir olaydan dinleyici kaldırır
     *
     * @param string $event Olay adı
     * @param callable|null $callback Geri çağırma fonksiyonu (null ise tüm dinleyicileri kaldırır)
     * @return void
     */
    public static function off($event, callable $callback = null)
    {
        if (!isset(self::$events[$event])) {
            return;
        }

        if ($callback === null) {
            // Tüm dinleyicileri kaldır
            unset(self::$events[$event]);
        } else {
            // Belirli dinleyiciyi kaldır
            foreach (self::$events[$event] as $priority => $eventCallback) {
                if ($eventCallback === $callback) {
                    unset(self::$events[$event][$priority]);
                }
            }

            // Boşsa tamamen kaldır
            if (empty(self::$events[$event])) {
                unset(self::$events[$event]);
            }
        }
    }

    /**
     * Bir olayı tetikler
     *
     * @param string $event Olay adı
     * @param mixed $data Olay verileri
     * @return mixed Son dinleyicinin dönüş değeri
     */
    public static function trigger($event, $data = null)
    {
        $result = null;

        if (isset(self::$events[$event])) {
            foreach (self::$events[$event] as $callback) {
                $result = call_user_func($callback, $data);

                // false dönerse tetiklemeyi durdur
                if ($result === false) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Bir olayı tetikler ve tüm dinleyicilerin sonuçlarını döndürür
     *
     * @param string $event Olay adı
     * @param mixed $data Olay verileri
     * @return array Dinleyicilerin sonuçları
     */
    public static function triggerAll($event, $data = null)
    {
        $results = [];

        if (isset(self::$events[$event])) {
            foreach (self::$events[$event] as $priority => $callback) {
                $results[$priority] = call_user_func($callback, $data);

                // false dönerse tetiklemeyi durdur
                if ($results[$priority] === false) {
                    break;
                }
            }
        }

        return $results;
    }

    /**
     * Bir olayın dinleyicilerini döndürür
     *
     * @param string $event Olay adı
     * @return array Dinleyiciler
     */
    public static function getListeners($event)
    {
        return isset(self::$events[$event]) ? self::$events[$event] : [];
    }

    /**
     * Bir olayın dinleyici sayısını döndürür
     *
     * @param string $event Olay adı
     * @return int Dinleyici sayısı
     */
    public static function countListeners($event)
    {
        return isset(self::$events[$event]) ? count(self::$events[$event]) : 0;
    }

    /**
     * Tüm olayları döndürür
     *
     * @return array Olaylar
     */
    public static function getEvents()
    {
        return array_keys(self::$events);
    }

    /**
     * Tüm dinleyicileri temizler
     *
     * @return void
     */
    public static function clear()
    {
        self::$events = [];
    }

    /**
     * Sistem öncesi olayını tetikler
     *
     * @param mixed $data Olay verileri
     * @return mixed Sonuç
     */
    public static function triggerPreSystem($data = null)
    {
        return self::trigger(self::PRE_SYSTEM, $data);
    }

    /**
     * Kontrolcü yapıcısı öncesi olayını tetikler
     *
     * @param mixed $data Olay verileri
     * @return mixed Sonuç
     */
    public static function triggerBeforeControllerConstructor($data = null)
    {
        return self::trigger(self::BEFORE_CONTROLLER_CONSTRUCTOR, $data);
    }

    /**
     * Kontrolcü metodu öncesi olayını tetikler
     *
     * @param mixed $data Olay verileri
     * @return mixed Sonuç
     */
    public static function triggerBeforeControllerMethod($data = null)
    {
        return self::trigger(self::BEFORE_CONTROLLER_METHOD, $data);
    }

    /**
     * Kontrolcü metodu sonrası olayını tetikler
     *
     * @param mixed $data Olay verileri
     * @return mixed Sonuç
     */
    public static function triggerAfterControllerMethod($data = null)
    {
        return self::trigger(self::AFTER_CONTROLLER_METHOD, $data);
    }

    /**
     * Sistem sonrası olayını tetikler
     *
     * @param mixed $data Olay verileri
     * @return mixed Sonuç
     */
    public static function triggerPostSystem($data = null)
    {
        return self::trigger(self::POST_SYSTEM, $data);
    }
}
