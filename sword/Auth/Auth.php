<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Auth sınıfı - Kimlik doğrulama yöneticisi
 */

namespace Sword\Auth;

class Auth
{
    /**
     * Guard örnekleri
     */
    private static $guards = [];

    /**
     * Varsayılan guard
     */
    private static $defaultGuard = 'web';

    /**
     * Guard yapılandırmaları
     */
    private static $config = [
        'web' => [
            'driver' => 'session',
            'provider' => 'users'
        ],
        'api' => [
            'driver' => 'token',
            'provider' => 'users'
        ]
    ];

    /**
     * Belirtilen guard'ı döndürür
     *
     * @param string|null $name Guard adı
     * @return Guard
     */
    public static function guard($name = null)
    {
        $name = $name ?: static::$defaultGuard;

        if (!isset(static::$guards[$name])) {
            static::$guards[$name] = static::createGuard($name);
        }

        return static::$guards[$name];
    }

    /**
     * Guard oluşturur
     *
     * @param string $name Guard adı
     * @return Guard
     */
    protected static function createGuard($name)
    {
        $config = static::$config[$name] ?? static::$config['web'];
        return new Guard($name, $config);
    }

    /**
     * Varsayılan guard'ı ayarlar
     *
     * @param string $guard Guard adı
     * @return void
     */
    public static function setDefaultGuard($guard)
    {
        static::$defaultGuard = $guard;
    }

    /**
     * Guard yapılandırmasını ayarlar
     *
     * @param string $name Guard adı
     * @param array $config Yapılandırma
     * @return void
     */
    public static function setGuardConfig($name, array $config)
    {
        static::$config[$name] = $config;
    }

    /**
     * Dinamik method çağrıları
     *
     * @param string $method Method adı
     * @param array $parameters Parametreler
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return static::guard()->{$method}(...$parameters);
    }
}
