<?php

/**
 * Sword Framework - Events
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Basit ve etkili event sistemi
 * Keskin. Hızlı. Ölümsüz.
 */

class Events
{
    /**
     * Kayıtlı event listeners
     */
    private static $listeners = [];

    /**
     * Sistem event constant'ları
     */
    const PRE_SYSTEM = 'pre_system';
    const BEFORE_CONTROLLER = 'before_controller';
    const AFTER_CONTROLLER = 'after_controller';
    const POST_SYSTEM = 'post_system';
    const EXCEPTION_THROWN = 'exception_thrown';
    const BEFORE_RENDER = 'before_render';
    const AFTER_RENDER = 'after_render';
    const USER_LOGIN = 'user_login';
    const USER_LOGOUT = 'user_logout';
    const MODEL_CREATED = 'model_created';
    const MODEL_UPDATED = 'model_updated';
    const MODEL_DELETED = 'model_deleted';

    /**
     * Event listener ekler
     *
     * @param string $event Event adı
     * @param callable|string $listener Listener callback veya class@method
     * @param int $priority Öncelik (düşük sayı önce çalışır, varsayılan: 10)
     * @return void
     */
    public static function listen(string $event, callable|string $listener, int $priority = 10): void
    {
        if (!isset(self::$listeners[$event])) {
            self::$listeners[$event] = [];
        }

        if (!isset(self::$listeners[$event][$priority])) {
            self::$listeners[$event][$priority] = [];
        }

        self::$listeners[$event][$priority][] = $listener;
        ksort(self::$listeners[$event]); // Priority'ye göre sırala
    }

    /**
     * Alias for listen
     */
    public static function on(string $event, callable|string $listener, int $priority = 10): void
    {
        self::listen($event, $listener, $priority);
    }

    /**
     * Event listener kaldırır
     *
     * @param string $event Event adı
     * @param callable|string|null $listener Listener (null ise tüm listeners kaldırılır)
     * @return void
     */
    public static function forget(string $event, callable|string|null $listener = null): void
    {
        if ($listener === null) {
            unset(self::$listeners[$event]);
            return;
        }

        if (isset(self::$listeners[$event])) {
            foreach (self::$listeners[$event] as $priority => $listeners) {
                $key = array_search($listener, $listeners, true);
                if ($key !== false) {
                    unset(self::$listeners[$event][$priority][$key]);
                    
                    // Boş priority grubu varsa kaldır
                    if (empty(self::$listeners[$event][$priority])) {
                        unset(self::$listeners[$event][$priority]);
                    }
                    break;
                }
            }
        }
    }

    /**
     * Alias for forget
     */
    public static function off(string $event, callable|string|null $listener = null): void
    {
        self::forget($event, $listener);
    }

    /**
     * Event dispatch eder
     *
     * @param string $event Event adı
     * @param mixed ...$args Event arguments
     * @return mixed Son listener'ın dönüş değeri
     */
    public static function dispatch(string $event, ...$args): mixed
    {
        $result = null;

        if (isset(self::$listeners[$event])) {
            // Priority'ye göre sıralı çalıştır
            foreach (self::$listeners[$event] as $priority => $listeners) {
                foreach ($listeners as $listener) {
                    try {
                        $result = self::callListener($listener, $args);
                        
                        // false dönerse event'i durdur
                        if ($result === false) {
                            return $result;
                        }
                    } catch (Exception $e) {
                        // Exception'ı logla
                        error_log("Event listener error: " . $e->getMessage());
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Alias for dispatch
     */
    public static function trigger(string $event, ...$args): mixed
    {
        return self::dispatch($event, ...$args);
    }

    /**
     * Listener'ı çağırır
     *
     * @param callable|string $listener Listener
     * @param array $args Event arguments
     * @return mixed Result
     */
    private static function callListener(callable|string $listener, array $args): mixed
    {
        // String formatı: 'ClassName@method'
        if (is_string($listener) && strpos($listener, '@') !== false) {
            list($class, $method) = explode('@', $listener, 2);

            if (!class_exists($class)) {
                throw new RuntimeException("Listener class not found: $class");
            }

            $instance = new $class();
            $listener = [$instance, $method];
        }

        // Callable kontrolü
        if (!is_callable($listener)) {
            throw new RuntimeException('Listener is not callable');
        }

        return call_user_func_array($listener, $args);
    }

    /**
     * Bir event'in listener'larını döndürür
     *
     * @param string $event Event adı
     * @return array Listeners (priority'ye göre sıralı)
     */
    public static function getListeners(string $event): array
    {
        if (!isset(self::$listeners[$event])) {
            return [];
        }
        
        $listeners = [];
        foreach (self::$listeners[$event] as $priority => $priorityListeners) {
            $listeners = array_merge($listeners, $priorityListeners);
        }
        
        return $listeners;
    }

    /**
     * Listener olup olmadığını kontrol eder
     *
     * @param string $event Event adı
     * @return bool Var mı?
     */
    public static function hasListeners(string $event): bool
    {
        return !empty(self::$listeners[$event]);
    }

    /**
     * Tüm listener'ları temizler
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$listeners = [];
    }

    // ========================================
    // Sistem Event Helper Methods
    // ========================================

    public static function triggerPreSystem(mixed $data = null): mixed
    {
        return self::dispatch(self::PRE_SYSTEM, $data);
    }

    public static function triggerBeforeController(mixed $data = null): mixed
    {
        return self::dispatch(self::BEFORE_CONTROLLER, $data);
    }

    public static function triggerAfterController(mixed $data = null): mixed
    {
        return self::dispatch(self::AFTER_CONTROLLER, $data);
    }

    public static function triggerPostSystem(mixed $data = null): mixed
    {
        return self::dispatch(self::POST_SYSTEM, $data);
    }

    public static function triggerExceptionThrown(Exception $exception): mixed
    {
        return self::dispatch(self::EXCEPTION_THROWN, $exception);
    }

    public static function triggerBeforeRender(mixed $data = null): mixed
    {
        return self::dispatch(self::BEFORE_RENDER, $data);
    }

    public static function triggerAfterRender(mixed $data = null): mixed
    {
        return self::dispatch(self::AFTER_RENDER, $data);
    }

    public static function triggerUserLogin(mixed $user): mixed
    {
        return self::dispatch(self::USER_LOGIN, $user);
    }

    public static function triggerUserLogout(mixed $user): mixed
    {
        return self::dispatch(self::USER_LOGOUT, $user);
    }

    public static function triggerModelCreated(mixed $model): mixed
    {
        return self::dispatch(self::MODEL_CREATED, $model);
    }

    public static function triggerModelUpdated(mixed $model, array $changes = []): mixed
    {
        return self::dispatch(self::MODEL_UPDATED, $model, $changes);
    }

    public static function triggerModelDeleted(mixed $model): mixed
    {
        return self::dispatch(self::MODEL_DELETED, $model);
    }
}