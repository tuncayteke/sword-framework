<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * SQLiteConnection sınıfı - SQLite veritabanı bağlantısı
 */

namespace Sword\ORM\Database;

class SQLiteConnection extends Connection
{
    /**
     * Veritabanına bağlanır
     *
     * @return void
     */
    protected function connect()
    {
        $dsn = $this->getDsn();
        $options = $this->config['options'] ?? [];

        // Varsayılan seçenekler
        $defaultOptions = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false
        ];

        $options = array_merge($defaultOptions, $options);

        try {
            $this->pdo = new \PDO($dsn, null, null, $options);
        } catch (\PDOException $e) {
            throw new \Exception('SQLite bağlantı hatası: ' . $e->getMessage());
        }
    }

    /**
     * DSN oluşturur
     *
     * @return string
     */
    protected function getDsn()
    {
        $database = $this->config['database'] ?? ':memory:';

        return "sqlite:{$database}";
    }
}
