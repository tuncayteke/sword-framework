<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * MySqlConnection sınıfı - MySQL veritabanı bağlantısı
 */

namespace Sword\ORM\Database;

class MySqlConnection extends Connection
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
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
        ];

        $options = array_merge($defaultOptions, $options);

        try {
            $this->pdo = new \PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            throw new \Exception('MySQL bağlantı hatası: ' . $e->getMessage());
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
        $port = $this->config['port'] ?? 3306;
        $database = $this->config['database'] ?? '';
        $charset = $this->config['charset'] ?? 'utf8mb4';

        return "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
    }
}
