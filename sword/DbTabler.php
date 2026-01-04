<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * DbTabler sınıfı - Dinamik tablo ve kolon yönetimi
 */

class DbTabler
{
    /**
     * Database bağlantısı
     */
    private static $db = null;

    /**
     * Tablo öneki
     */
    private static $prefix = '';

    /**
     * Sınıfı başlatır
     */
    private static function init()
    {
        if (self::$db === null) {
            self::$db = Sword::db();
            self::$prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        }
    }

    /**
     * Tablo oluşturur
     *
     * @param string $tableName Tablo adı
     * @param array $columns Kolonlar
     * @param array $options Ek seçenekler
     * @return bool Başarılı mı?
     */
    public static function createTable($tableName, array $columns, array $options = [])
    {
        self::init();

        $fullTableName = self::$prefix . $tableName;

        // Tablo zaten varsa
        if (self::tableExists($tableName)) {
            return false;
        }

        $sql = "CREATE TABLE `{$fullTableName}` (";

        $columnDefinitions = [];
        foreach ($columns as $name => $definition) {
            $columnDefinitions[] = self::buildColumnDefinition($name, $definition);
        }

        $sql .= implode(', ', $columnDefinitions);

        // Primary key ekle
        if (isset($options['primary_key'])) {
            $sql .= ", PRIMARY KEY (`{$options['primary_key']}`)";
        }

        // Unique keys ekle
        if (isset($options['unique_keys'])) {
            foreach ($options['unique_keys'] as $key) {
                $sql .= ", UNIQUE KEY `{$key}` (`{$key}`)";
            }
        }

        // Indexes ekle
        if (isset($options['indexes'])) {
            foreach ($options['indexes'] as $index) {
                $sql .= ", INDEX `{$index}` (`{$index}`)";
            }
        }

        $sql .= ") ENGINE=" . ($options['engine'] ?? 'InnoDB');
        $sql .= " DEFAULT CHARSET=" . ($options['charset'] ?? 'utf8mb4');

        return self::$db->query($sql) !== false;
    }

    /**
     * Tabloya kolon ekler
     *
     * @param string $tableName Tablo adı
     * @param string $columnName Kolon adı
     * @param array $definition Kolon tanımı
     * @param string $after Hangi kolondan sonra (opsiyonel)
     * @return bool Başarılı mı?
     */
    public static function addColumn($tableName, $columnName, array $definition, $after = null)
    {
        self::init();

        $fullTableName = self::$prefix . $tableName;

        // Kolon zaten varsa
        if (self::columnExists($tableName, $columnName)) {
            return false;
        }

        $columnDef = self::buildColumnDefinition($columnName, $definition);

        $sql = "ALTER TABLE `{$fullTableName}` ADD COLUMN {$columnDef}";

        if ($after) {
            $sql .= " AFTER `{$after}`";
        }

        return self::$db->query($sql) !== false;
    }

    /**
     * Kolondan kolon siler
     *
     * @param string $tableName Tablo adı
     * @param string $columnName Kolon adı
     * @return bool Başarılı mı?
     */
    public static function dropColumn($tableName, $columnName)
    {
        self::init();

        $fullTableName = self::$prefix . $tableName;

        if (!self::columnExists($tableName, $columnName)) {
            return false;
        }

        $sql = "ALTER TABLE `{$fullTableName}` DROP COLUMN `{$columnName}`";

        return self::$db->query($sql) !== false;
    }

    /**
     * Kolonu değiştirir
     *
     * @param string $tableName Tablo adı
     * @param string $oldName Eski kolon adı
     * @param string $newName Yeni kolon adı
     * @param array $definition Yeni kolon tanımı
     * @return bool Başarılı mı?
     */
    public static function changeColumn($tableName, $oldName, $newName, array $definition)
    {
        self::init();

        $fullTableName = self::$prefix . $tableName;

        if (!self::columnExists($tableName, $oldName)) {
            return false;
        }

        $columnDef = self::buildColumnDefinition($newName, $definition);

        $sql = "ALTER TABLE `{$fullTableName}` CHANGE `{$oldName}` {$columnDef}";

        return self::$db->query($sql) !== false;
    }

    /**
     * Index ekler
     *
     * @param string $tableName Tablo adı
     * @param string $indexName Index adı
     * @param array $columns Kolon listesi
     * @param string $type Index tipi (INDEX, UNIQUE, FULLTEXT)
     * @return bool Başarılı mı?
     */
    public static function addIndex($tableName, $indexName, array $columns, $type = 'INDEX')
    {
        self::init();

        $fullTableName = self::$prefix . $tableName;

        $columnList = '`' . implode('`, `', $columns) . '`';

        $sql = "ALTER TABLE `{$fullTableName}` ADD {$type} `{$indexName}` ({$columnList})";

        return self::$db->query($sql) !== false;
    }

    /**
     * Tabloyu siler
     *
     * @param string $tableName Tablo adı
     * @return bool Başarılı mı?
     */
    public static function dropTable($tableName)
    {
        self::init();

        $fullTableName = self::$prefix . $tableName;

        $sql = "DROP TABLE IF EXISTS `{$fullTableName}`";

        return self::$db->query($sql) !== false;
    }

    /**
     * Tablo var mı kontrol eder
     *
     * @param string $tableName Tablo adı
     * @return bool Var mı?
     */
    public static function tableExists($tableName)
    {
        self::init();

        $fullTableName = self::$prefix . $tableName;

        $sql = "SHOW TABLES LIKE '{$fullTableName}'";
        $result = self::$db->query($sql);

        return $result && $result->num_rows > 0;
    }

    /**
     * Kolon var mı kontrol eder
     *
     * @param string $tableName Tablo adı
     * @param string $columnName Kolon adı
     * @return bool Var mı?
     */
    public static function columnExists($tableName, $columnName)
    {
        self::init();

        $fullTableName = self::$prefix . $tableName;

        $sql = "SHOW COLUMNS FROM `{$fullTableName}` LIKE '{$columnName}'";
        $result = self::$db->query($sql);

        return $result && $result->num_rows > 0;
    }

    /**
     * Kolon tanımını oluşturur
     *
     * @param string $name Kolon adı
     * @param array $definition Kolon tanımı
     * @return string SQL kolon tanımı
     */
    private static function buildColumnDefinition($name, array $definition)
    {
        $sql = "`{$name}` ";

        // Veri tipi
        $type = $definition['type'] ?? 'VARCHAR(255)';
        $sql .= strtoupper($type);

        // NULL/NOT NULL
        if (isset($definition['null']) && $definition['null'] === false) {
            $sql .= ' NOT NULL';
        } else {
            $sql .= ' NULL';
        }

        // Default değer
        if (isset($definition['default'])) {
            if ($definition['default'] === 'CURRENT_TIMESTAMP') {
                $sql .= ' DEFAULT CURRENT_TIMESTAMP';
            } else {
                $sql .= " DEFAULT '" . addslashes($definition['default']) . "'";
            }
        }

        // Auto increment
        if (isset($definition['auto_increment']) && $definition['auto_increment']) {
            $sql .= ' AUTO_INCREMENT';
        }

        // Comment
        if (isset($definition['comment'])) {
            $sql .= " COMMENT '" . addslashes($definition['comment']) . "'";
        }

        return $sql;
    }
}
