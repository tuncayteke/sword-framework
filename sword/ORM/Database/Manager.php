<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Manager sınıfı - Veritabanı bağlantılarını yönetir
 */

namespace Sword\ORM\Database;

class Manager
{
    /**
     * Bağlantılar
     */
    protected $connections = [];

    /**
     * Varsayılan bağlantı
     */
    protected $default = 'default';

    /**
     * Yapılandırma
     */
    protected $config = [];

    /**
     * Yapılandırıcı
     *
     * @param array $config Yapılandırma
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Bağlantı döndürür
     *
     * @param string|null $name Bağlantı adı
     * @return Connection
     */
    public function connection($name = null)
    {
        $name = $name ?: $this->default;

        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->makeConnection($name);
        }

        return $this->connections[$name];
    }

    /**
     * Bağlantı oluşturur
     *
     * @param string $name Bağlantı adı
     * @return Connection
     * @throws \Exception
     */
    protected function makeConnection($name)
    {
        $config = $this->getConfig($name);

        if (empty($config)) {
            throw new \Exception("Veritabanı yapılandırması bulunamadı: {$name}");
        }

        return ConnectionFactory::make($config);
    }

    /**
     * Yapılandırma döndürür
     *
     * @param string $name Bağlantı adı
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->config[$name] ?? [];
    }

    /**
     * Varsayılan bağlantıyı ayarlar
     *
     * @param string $name Bağlantı adı
     * @return $this
     */
    public function setDefault($name)
    {
        $this->default = $name;

        return $this;
    }

    /**
     * Varsayılan bağlantıyı döndürür
     *
     * @return string
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Yapılandırma ekler
     *
     * @param string $name Bağlantı adı
     * @param array $config Yapılandırma
     * @return $this
     */
    public function addConfig($name, array $config)
    {
        $this->config[$name] = $config;

        return $this;
    }

    /**
     * Yapılandırmaları döndürür
     *
     * @return array
     */
    public function getConfigs()
    {
        return $this->config;
    }

    /**
     * Bağlantıları döndürür
     *
     * @return array
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * Bağlantıyı kapatır
     *
     * @param string|null $name Bağlantı adı
     * @return $this
     */
    public function disconnect($name = null)
    {
        $name = $name ?: $this->default;

        unset($this->connections[$name]);

        return $this;
    }

    /**
     * Tüm bağlantıları kapatır
     *
     * @return $this
     */
    public function disconnectAll()
    {
        $this->connections = [];

        return $this;
    }

    /**
     * Bilinmeyen metodları varsayılan bağlantıya yönlendirir
     *
     * @param string $method Metod adı
     * @param array $parameters Parametreler
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->connection()->$method(...$parameters);
    }
}
