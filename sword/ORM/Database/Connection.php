<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Connection sınıfı - Veritabanı bağlantısı
 */

namespace Sword\ORM\Database;

abstract class Connection
{
    /**
     * Bağlantı yapılandırması
     */
    protected $config;

    /**
     * PDO bağlantısı
     */
    protected $pdo;

    /**
     * Yapılandırıcı
     *
     * @param array $config Bağlantı yapılandırması
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * Veritabanına bağlanır
     *
     * @return void
     */
    abstract protected function connect();

    /**
     * Sorgu çalıştırır
     *
     * @param string $query Sorgu
     * @param array $bindings Bağlamalar
     * @return \PDOStatement
     */
    public function query($query, $bindings = [])
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($bindings);

        return $statement;
    }

    /**
     * Tek bir satır döndürür
     *
     * @param string $query Sorgu
     * @param array $bindings Bağlamalar
     * @param int $fetchMode Getirme modu
     * @return mixed
     */
    public function fetchOne($query, $bindings = [], $fetchMode = \PDO::FETCH_ASSOC)
    {
        return $this->query($query, $bindings)->fetch($fetchMode);
    }

    /**
     * Tüm sonuçları döndürür
     *
     * @param string $query Sorgu
     * @param array $bindings Bağlamalar
     * @param int $fetchMode Getirme modu
     * @return array
     */
    public function fetchAll($query, $bindings = [], $fetchMode = \PDO::FETCH_ASSOC)
    {
        return $this->query($query, $bindings)->fetchAll($fetchMode);
    }

    /**
     * Son eklenen kaydın ID'sini döndürür
     *
     * @param string|null $sequence Sıra adı
     * @return string
     */
    public function lastInsertId($sequence = null)
    {
        return $this->pdo->lastInsertId($sequence);
    }

    /**
     * İşlem başlatır
     *
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * İşlemi onaylar
     *
     * @return bool
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * İşlemi geri alır
     *
     * @return bool
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    /**
     * İşlem durumunu döndürür
     *
     * @return bool
     */
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    /**
     * PDO bağlantısını döndürür
     *
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Bağlantı yapılandırmasını döndürür
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}
