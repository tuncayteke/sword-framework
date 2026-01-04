<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * PostgresConnection sınıfı - PostgreSQL veritabanı bağlantısı
 */

namespace Sword\ORM\Database;

class PostgresConnection extends Connection
{
    /**
     * Veritabanına bağlanır
     *
     * @return void
     */
    protected function connect()
    {
        $dsn = $this->getDsn();
        $username = $this->config['username'] ?? null;
        $password = $this->config['password'] ?? null;
        $options = $this->config['options'] ?? [];

        // Varsayılan seçenekler
        $defaultOptions = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false
        ];

        $options = array_merge($defaultOptions, $options);

        try {
            $this->pdo = new \PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            throw new \Exception('PostgreSQL bağlantı hatası: ' . $e->getMessage());
        }
    }

    /**
     * DSN oluşturur
     *
     * @return string
     */
    protected function getDsn()
    {
        $host = $this->config['host'] ?? 'localhost';
        $port = $this->config['port'] ?? 5432;
        $database = $this->config['database'] ?? '';
        $schema = $this->config['schema'] ?? 'public';

        return "pgsql:host={$host};port={$port};dbname={$database};options='--client_encoding=UTF8 --search_path={$schema}'";
    }
}
