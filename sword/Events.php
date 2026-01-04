<?php

/**
 * Sword Framework - Geliştirilmiş Events
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Events sınıfı - Modern event dispatching sistemi
 * PSR-14 Event Dispatcher standardına yakın
 * 
 * Yeni Özellikler:
 * - Event object support
 * - Stoppable events
 * - Event subscribers
 * - Wildcard listeners
 * - Queued events
 * - Event history/replay
 * - Async event support
 * - Event middleware
 */

/**
 * Event Interface
 */
interface EventInterface
{
    /**
     * Event'in propagation'ının durdurulup durdurulmadığını kontrol eder
     */
    public function isPropagationStopped(): bool;
}

/**
 * Stoppable Event Interface
 */
interface StoppableEventInterface extends EventInterface
{
    /**
     * Event propagation'ını durdurur
     */
    public function stopPropagation(): void;
}

/**
 * Event Subscriber Interface
 */
interface EventSubscriberInterface
{
    /**
     * Subscribe edilecek event'leri döndürür
     * 
     * @return array ['event.name' => 'methodName'] veya ['event.name' => ['methodName', priority]]
     */
    public static function getSubscribedEvents(): array;
}

/**
 * Base Event Class
 */
class Event implements StoppableEventInterface
{
    private $stopped = false;
    private $data = [];
    private $timestamp;
    private $name;

    public function __construct($name = null, array $data = [])
    {
        $this->name = $name ?? static::class;
        $this->data = $data;
        $this->timestamp = microtime(true);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function set($key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    public function stopPropagation(): void
    {
        $this->stopped = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }
}

/**
 * Events Manager
 */
class Events
{
    /**
     * Kayıtlı event listeners
     */
    private static $listeners = [];

    /**
     * Kayıtlı event subscribers
     */
    private static $subscribers = [];

    /**
     * Wildcard listeners
     */
    private static $wildcardListeners = [];

    /**
     * Event history (debugging için)
     */
    private static $history = [];

    /**
     * History enabled mi?
     */
    private static $historyEnabled = false;

    /**
     * Queued events
     */
    private static $queuedEvents = [];

    /**
     * Event middleware'ler
     */
    private static $middlewares = [];

    /**
     * Async handlers
     */
    private static $asyncHandlers = [];

    /**
     * Sistem event constant'ları
     */
    const PRE_SYSTEM = 'pre_system';
    const BEFORE_CONTROLLER_CONSTRUCTOR = 'before_controller_constructor';
    const BEFORE_CONTROLLER_METHOD = 'before_controller_method';
    const AFTER_CONTROLLER_METHOD = 'after_controller_method';
    const POST_SYSTEM = 'post_system';
    const EXCEPTION_THROWN = 'exception_thrown';
    const BEFORE_RENDER = 'before_render';
    const AFTER_RENDER = 'after_render';
    const CACHE_HIT = 'cache_hit';
    const CACHE_MISS = 'cache_miss';
    const DB_QUERY = 'db_query';
    const USER_LOGIN = 'user_login';
    const USER_LOGOUT = 'user_logout';
    const MODEL_CREATED = 'model_created';
    const MODEL_UPDATED = 'model_updated';
    const MODEL_DELETED = 'model_deleted';

    /**
     * Event listener ekler
     *
     * @param string $event Event adı (wildcard destekler: user.*, *.created)
     * @param callable|string $listener Listener callback veya class@method
     * @param int $priority Öncelik (düşük değer önce çalışır)
     * @return void
     */
    public static function listen($event, $listener, $priority = 10)
    {
        // Wildcard kontrolü
        if (strpos($event, '*') !== false) {
            self::$wildcardListeners[$event][$priority][] = $listener;
            ksort(self::$wildcardListeners[$event]);
            return;
        }

        if (!isset(self::$listeners[$event])) {
            self::$listeners[$event] = [];
        }

        if (!isset(self::$listeners[$event][$priority])) {
            self::$listeners[$event][$priority] = [];
        }

        self::$listeners[$event][$priority][] = $listener;
        ksort(self::$listeners[$event]);
    }

    /**
     * Alias for listen
     */
    public static function on($event, $listener, $priority = 10)
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
    public static function forget($event, $listener = null)
    {
        if ($listener === null) {
            unset(self::$listeners[$event]);
            unset(self::$wildcardListeners[$event]);
            return;
        }

        if (isset(self::$listeners[$event])) {
            foreach (self::$listeners[$event] as $priority => $listeners) {
                $key = array_search($listener, $listeners, true);
                if ($key !== false) {
                    unset(self::$listeners[$event][$priority][$key]);
                }
                
                if (empty(self::$listeners[$event][$priority])) {
                    unset(self::$listeners[$event][$priority]);
                }
            }

            if (empty(self::$listeners[$event])) {
                unset(self::$listeners[$event]);
            }
        }
    }

    /**
     * Alias for forget
     */
    public static function off($event, $listener = null)
    {
        self::forget($event, $listener);
    }

    /**
     * Event subscriber ekler
     *
     * @param EventSubscriberInterface|string $subscriber Subscriber instance veya class name
     * @return void
     */
    public static function subscribe($subscriber)
    {
        if (is_string($subscriber)) {
            $subscriber = new $subscriber();
        }

        if (!$subscriber instanceof EventSubscriberInterface) {
            throw new InvalidArgumentException('Subscriber must implement EventSubscriberInterface');
        }

        $subscribedEvents = $subscriber::getSubscribedEvents();

        foreach ($subscribedEvents as $event => $params) {
            if (is_string($params)) {
                // ['event' => 'method']
                self::listen($event, [$subscriber, $params]);
            } elseif (is_array($params)) {
                if (is_string($params[0])) {
                    // ['event' => ['method', priority]]
                    $priority = $params[1] ?? 10;
                    self::listen($event, [$subscriber, $params[0]], $priority);
                } else {
                    // ['event' => [['method1', priority1], ['method2', priority2]]]
                    foreach ($params as $listener) {
                        $method = $listener[0];
                        $priority = $listener[1] ?? 10;
                        self::listen($event, [$subscriber, $method], $priority);
                    }
                }
            }
        }

        self::$subscribers[] = $subscriber;
    }

    /**
     * Event dispatch eder
     *
     * @param string|EventInterface $event Event adı veya Event object
     * @param mixed $payload Event data
     * @return mixed Event object veya payload
     */
    public static function dispatch($event, $payload = null)
    {
        // Event object oluştur
        if (is_string($event)) {
            $eventName = $event;
            $eventObject = new Event($event, is_array($payload) ? $payload : ['data' => $payload]);
        } elseif ($event instanceof EventInterface) {
            $eventObject = $event;
            $eventName = $eventObject->getName();
        } else {
            throw new InvalidArgumentException('Event must be string or EventInterface');
        }

        // History'e ekle
        if (self::$historyEnabled) {
            self::$history[] = [
                'event' => $eventName,
                'timestamp' => microtime(true),
                'payload' => $payload
            ];
        }

        // Middleware'lerden geçir
        $eventObject = self::runMiddlewares($eventObject);

        // Listeners'ı çalıştır
        $listeners = self::getListenersForEvent($eventName);

        foreach ($listeners as $listener) {
            if ($eventObject instanceof StoppableEventInterface && $eventObject->isPropagationStopped()) {
                break;
            }

            $result = self::callListener($listener, $eventObject);

            // false dönerse propagation'ı durdur
            if ($result === false && $eventObject instanceof StoppableEventInterface) {
                $eventObject->stopPropagation();
            }
        }

        return $eventObject;
    }

    /**
     * Alias for dispatch
     */
    public static function trigger($event, $payload = null)
    {
        return self::dispatch($event, $payload);
    }

    /**
     * Event'i queue'a ekler (daha sonra çalıştırılmak üzere)
     *
     * @param string|EventInterface $event Event
     * @param mixed $payload Payload
     * @return void
     */
    public static function queue($event, $payload = null)
    {
        self::$queuedEvents[] = [$event, $payload];
    }

    /**
     * Queue'daki tüm event'leri çalıştırır
     *
     * @return void
     */
    public static function flush()
    {
        while (!empty(self::$queuedEvents)) {
            list($event, $payload) = array_shift(self::$queuedEvents);
            self::dispatch($event, $payload);
        }
    }

    /**
     * Event'i asenkron çalıştırır (background)
     *
     * @param string|EventInterface $event Event
     * @param mixed $payload Payload
     * @return void
     */
    public static function dispatchAsync($event, $payload = null)
    {
        if (is_string($event)) {
            $eventName = $event;
        } else {
            $eventName = $event->getName();
        }

        // Async handler'lar varsa kullan
        if (isset(self::$asyncHandlers[$eventName])) {
            foreach (self::$asyncHandlers[$eventName] as $handler) {
                call_user_func($handler, $event, $payload);
            }
        } else {
            // Default: queue'a ekle
            self::queue($event, $payload);
        }
    }

    /**
     * Async handler ekler
     *
     * @param string $event Event adı
     * @param callable $handler Handler
     * @return void
     */
    public static function asyncHandler($event, callable $handler)
    {
        if (!isset(self::$asyncHandlers[$event])) {
            self::$asyncHandlers[$event] = [];
        }
        self::$asyncHandlers[$event][] = $handler;
    }

    /**
     * Event middleware ekler
     *
     * @param callable $middleware Middleware
     * @param int $priority Priority
     * @return void
     */
    public static function middleware(callable $middleware, $priority = 10)
    {
        if (!isset(self::$middlewares[$priority])) {
            self::$middlewares[$priority] = [];
        }
        self::$middlewares[$priority][] = $middleware;
        ksort(self::$middlewares);
    }

    /**
     * Middleware'leri çalıştırır
     *
     * @param EventInterface $event Event
     * @return EventInterface Modified event
     */
    private static function runMiddlewares(EventInterface $event)
    {
        foreach (self::$middlewares as $priority => $middlewares) {
            foreach ($middlewares as $middleware) {
                $event = call_user_func($middleware, $event);
                
                if (!$event instanceof EventInterface) {
                    throw new RuntimeException('Middleware must return EventInterface');
                }
            }
        }
        return $event;
    }

    /**
     * Listener'ı çağırır
     *
     * @param callable|string|array $listener Listener
     * @param EventInterface $event Event
     * @return mixed Result
     */
    private static function callListener($listener, EventInterface $event)
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

        return call_user_func($listener, $event);
    }

    /**
     * Event için tüm listener'ları döndürür
     *
     * @param string $eventName Event adı
     * @return array Listeners
     */
    private static function getListenersForEvent($eventName)
    {
        $listeners = [];

        // Direct listeners
        if (isset(self::$listeners[$eventName])) {
            foreach (self::$listeners[$eventName] as $priority => $priorityListeners) {
                $listeners = array_merge($listeners, $priorityListeners);
            }
        }

        // Wildcard listeners
        foreach (self::$wildcardListeners as $pattern => $priorityGroups) {
            if (self::matchWildcard($pattern, $eventName)) {
                foreach ($priorityGroups as $priority => $priorityListeners) {
                    $listeners = array_merge($listeners, $priorityListeners);
                }
            }
        }

        return $listeners;
    }

    /**
     * Wildcard pattern match
     *
     * @param string $pattern Pattern (user.*, *.created)
     * @param string $eventName Event adı
     * @return bool Match oldu mu?
     */
    private static function matchWildcard($pattern, $eventName)
    {
        $pattern = str_replace(['.', '*'], ['\.', '.*'], $pattern);
        return preg_match('/^' . $pattern . '$/', $eventName) === 1;
    }

    /**
     * Event history'yi aktifleştirir
     *
     * @param bool $enabled Enabled mi?
     * @return void
     */
    public static function enableHistory($enabled = true)
    {
        self::$historyEnabled = $enabled;
    }

    /**
     * Event history'yi döndürür
     *
     * @param string|null $event Belirli bir event için (null ise tümü)
     * @return array History
     */
    public static function getHistory($event = null)
    {
        if ($event === null) {
            return self::$history;
        }

        return array_filter(self::$history, function($item) use ($event) {
            return $item['event'] === $event;
        });
    }

    /**
     * Event history'yi temizler
     *
     * @return void
     */
    public static function clearHistory()
    {
        self::$history = [];
    }

    /**
     * Event'leri replay eder (history'den)
     *
     * @param string|null $event Belirli bir event (null ise tümü)
     * @return void
     */
    public static function replay($event = null)
    {
        $history = self::getHistory($event);
        
        foreach ($history as $item) {
            self::dispatch($item['event'], $item['payload']);
        }
    }

    /**
     * Bir event'in listener'larını döndürür
     *
     * @param string $event Event adı
     * @return array Listeners
     */
    public static function getListeners($event)
    {
        return self::getListenersForEvent($event);
    }

    /**
     * Bir event'in listener sayısını döndürür
     *
     * @param string $event Event adı
     * @return int Listener sayısı
     */
    public static function countListeners($event)
    {
        return count(self::getListenersForEvent($event));
    }

    /**
     * Listener olup olmadığını kontrol eder
     *
     * @param string $event Event adı
     * @return bool Var mı?
     */
    public static function hasListeners($event)
    {
        return self::countListeners($event) > 0;
    }

    /**
     * Tüm event adlarını döndürür
     *
     * @return array Event adları
     */
    public static function getEvents()
    {
        $events = array_keys(self::$listeners);
        $wildcardEvents = array_keys(self::$wildcardListeners);
        return array_merge($events, $wildcardEvents);
    }

    /**
     * Tüm listener'ları temizler
     *
     * @return void
     */
    public static function clear()
    {
        self::$listeners = [];
        self::$wildcardListeners = [];
        self::$subscribers = [];
        self::$history = [];
        self::$queuedEvents = [];
        self::$middlewares = [];
        self::$asyncHandlers = [];
    }

    /**
     * Event istatistiklerini döndürür
     *
     * @return array Stats
     */
    public static function getStats()
    {
        $totalListeners = 0;
        foreach (self::$listeners as $event => $priorities) {
            foreach ($priorities as $priority => $listeners) {
                $totalListeners += count($listeners);
            }
        }

        return [
            'total_events' => count(self::$listeners),
            'total_listeners' => $totalListeners,
            'wildcard_patterns' => count(self::$wildcardListeners),
            'subscribers' => count(self::$subscribers),
            'queued_events' => count(self::$queuedEvents),
            'history_count' => count(self::$history),
            'history_enabled' => self::$historyEnabled,
            'middlewares' => array_sum(array_map('count', self::$middlewares))
        ];
    }

    // ========================================
    // Sistem Event Helper Methods
    // ========================================

    public static function triggerPreSystem($data = null)
    {
        return self::dispatch(self::PRE_SYSTEM, $data);
    }

    public static function triggerBeforeControllerConstructor($data = null)
    {
        return self::dispatch(self::BEFORE_CONTROLLER_CONSTRUCTOR, $data);
    }

    public static function triggerBeforeControllerMethod($data = null)
    {
        return self::dispatch(self::BEFORE_CONTROLLER_METHOD, $data);
    }

    public static function triggerAfterControllerMethod($data = null)
    {
        return self::dispatch(self::AFTER_CONTROLLER_METHOD, $data);
    }

    public static function triggerPostSystem($data = null)
    {
        return self::dispatch(self::POST_SYSTEM, $data);
    }

    public static function triggerExceptionThrown($exception)
    {
        return self::dispatch(self::EXCEPTION_THROWN, ['exception' => $exception]);
    }

    public static function triggerBeforeRender($data = null)
    {
        return self::dispatch(self::BEFORE_RENDER, $data);
    }

    public static function triggerAfterRender($data = null)
    {
        return self::dispatch(self::AFTER_RENDER, $data);
    }

    public static function triggerCacheHit($key, $value = null)
    {
        return self::dispatch(self::CACHE_HIT, ['key' => $key, 'value' => $value]);
    }

    public static function triggerCacheMiss($key)
    {
        return self::dispatch(self::CACHE_MISS, ['key' => $key]);
    }

    public static function triggerDbQuery($query, $time = null)
    {
        return self::dispatch(self::DB_QUERY, ['query' => $query, 'time' => $time]);
    }

    public static function triggerUserLogin($user)
    {
        return self::dispatch(self::USER_LOGIN, ['user' => $user]);
    }

    public static function triggerUserLogout($user)
    {
        return self::dispatch(self::USER_LOGOUT, ['user' => $user]);
    }

    public static function triggerModelCreated($model)
    {
        return self::dispatch(self::MODEL_CREATED, ['model' => $model]);
    }

    public static function triggerModelUpdated($model, $changes = [])
    {
        return self::dispatch(self::MODEL_UPDATED, ['model' => $model, 'changes' => $changes]);
    }

    public static function triggerModelDeleted($model)
    {
        return self::dispatch(self::MODEL_DELETED, ['model' => $model]);
    }
}