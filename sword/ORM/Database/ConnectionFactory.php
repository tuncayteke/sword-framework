<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * ConnectionFactory sınıfı - Veritabanı bağlantısı oluşturur
 */

namespace Sword\ORM\Database;

class ConnectionFactory
{
    /**
     * Bağlantı oluşturur
     *
     * @param array $config Bağlantı yapılandırması
     * @return Connection
     * @throws \Exception
     */
    public static function make(array $config)
    {
        $driver = $config['driver'] ?? 'mysql';

        switch ($driver) {
            case 'mysql':
                return new MySqlConnection($config);

            case 'pgsql':
            case 'postgres':
            case 'postgresql':
                return new PostgresConnection($config);

            case 'sqlite':
                return new SQLiteConnection($config);

            default:
                throw new \Exception("Desteklenmeyen veritabanı sürücüsü: {$driver}");
        }
    }
}
