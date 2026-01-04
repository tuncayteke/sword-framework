# DbTabler Class

Dinamik tablo ve kolon yönetimi sağlar. Veritabanı şeması oluşturma ve değiştirme işlemleri için kullanılır.

## Temel Kullanım

```php
// Tablo oluştur
DbTabler::createTable('users', [
    'id' => ['type' => 'INT(11)', 'auto_increment' => true, 'null' => false],
    'name' => ['type' => 'VARCHAR(100)', 'null' => false],
    'email' => ['type' => 'VARCHAR(150)', 'null' => false]
], ['primary_key' => 'id']);

// Kolon ekle
DbTabler::addColumn('users', 'phone', ['type' => 'VARCHAR(20)', 'null' => true]);
```

## Tablo İşlemleri

### createTable($tableName, $columns, $options = [])
Yeni tablo oluşturur.

```php
// Basit tablo
DbTabler::createTable('posts', [
    'id' => ['type' => 'INT(11)', 'auto_increment' => true, 'null' => false],
    'title' => ['type' => 'VARCHAR(255)', 'null' => false],
    'content' => ['type' => 'TEXT', 'null' => true],
    'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP']
], [
    'primary_key' => 'id',
    'engine' => 'InnoDB',
    'charset' => 'utf8mb4'
]);

// İndeksli tablo
DbTabler::createTable('products', [
    'id' => ['type' => 'INT(11)', 'auto_increment' => true, 'null' => false],
    'name' => ['type' => 'VARCHAR(200)', 'null' => false],
    'slug' => ['type' => 'VARCHAR(200)', 'null' => false],
    'price' => ['type' => 'DECIMAL(10,2)', 'null' => false],
    'category_id' => ['type' => 'INT(11)', 'null' => true]
], [
    'primary_key' => 'id',
    'unique_keys' => ['slug'],
    'indexes' => ['category_id', 'price']
]);
```

### dropTable($tableName)
Tabloyu siler.

```php
DbTabler::dropTable('old_table');
DbTabler::dropTable('temp_data');
```

### tableExists($tableName)
Tablo var mı kontrol eder.

```php
if (DbTabler::tableExists('users')) {
    echo 'Users tablosu mevcut';
} else {
    // Tablo oluştur
    DbTabler::createTable('users', $columns);
}
```

## Kolon İşlemleri

### addColumn($tableName, $columnName, $definition, $after = null)
Tabloya yeni kolon ekler.

```php
// Basit kolon ekleme
DbTabler::addColumn('users', 'age', [
    'type' => 'INT(3)',
    'null' => true,
    'default' => '0'
]);

// Belirli kolondan sonra ekleme
DbTabler::addColumn('users', 'middle_name', [
    'type' => 'VARCHAR(50)',
    'null' => true
], 'first_name');

// Yorum ile kolon
DbTabler::addColumn('products', 'stock_count', [
    'type' => 'INT(11)',
    'null' => false,
    'default' => '0',
    'comment' => 'Stok miktarı'
]);
```

### dropColumn($tableName, $columnName)
Kolonu siler.

```php
DbTabler::dropColumn('users', 'old_field');
DbTabler::dropColumn('posts', 'deprecated_column');
```

### changeColumn($tableName, $oldName, $newName, $definition)
Kolonu değiştirir.

```php
// Kolon adını değiştir
DbTabler::changeColumn('users', 'user_name', 'username', [
    'type' => 'VARCHAR(50)',
    'null' => false
]);

// Kolon tipini değiştir
DbTabler::changeColumn('products', 'price', 'price', [
    'type' => 'DECIMAL(12,2)',
    'null' => false,
    'default' => '0.00'
]);
```

### columnExists($tableName, $columnName)
Kolon var mı kontrol eder.

```php
if (!DbTabler::columnExists('users', 'phone')) {
    DbTabler::addColumn('users', 'phone', [
        'type' => 'VARCHAR(20)',
        'null' => true
    ]);
}
```

## İndeks İşlemleri

### addIndex($tableName, $indexName, $columns, $type = 'INDEX')
İndeks ekler.

```php
// Normal indeks
DbTabler::addIndex('posts', 'idx_title', ['title']);

// Unique indeks
DbTabler::addIndex('users', 'idx_email', ['email'], 'UNIQUE');

// Çoklu kolon indeksi
DbTabler::addIndex('orders', 'idx_user_date', ['user_id', 'created_at']);

// Fulltext indeks
DbTabler::addIndex('articles', 'idx_content', ['title', 'content'], 'FULLTEXT');
```

## Kolon Tanım Seçenekleri

### Veri Tipleri
```php
// Sayısal tipler
'type' => 'INT(11)'
'type' => 'BIGINT(20)'
'type' => 'DECIMAL(10,2)'
'type' => 'FLOAT'
'type' => 'DOUBLE'

// Metin tipleri
'type' => 'VARCHAR(255)'
'type' => 'TEXT'
'type' => 'LONGTEXT'
'type' => 'CHAR(10)'

// Tarih tipleri
'type' => 'DATE'
'type' => 'DATETIME'
'type' => 'TIMESTAMP'
'type' => 'TIME'

// Diğer tipler
'type' => 'BOOLEAN'
'type' => 'JSON'
'type' => 'ENUM("active","inactive")'
```

### Kolon Özellikleri
```php
[
    'type' => 'VARCHAR(100)',
    'null' => false,                    // NULL değer alabilir mi?
    'default' => 'varsayılan_değer',   // Varsayılan değer
    'auto_increment' => true,           // Otomatik artış
    'comment' => 'Kolon açıklaması'    // Yorum
]
```

## Örnek Kullanımlar

### Blog Sistemi Tabloları
```php
class BlogInstaller
{
    public static function install()
    {
        // Posts tablosu
        DbTabler::createTable('posts', [
            'id' => ['type' => 'INT(11)', 'auto_increment' => true, 'null' => false],
            'title' => ['type' => 'VARCHAR(255)', 'null' => false],
            'slug' => ['type' => 'VARCHAR(255)', 'null' => false],
            'content' => ['type' => 'LONGTEXT', 'null' => true],
            'excerpt' => ['type' => 'TEXT', 'null' => true],
            'author_id' => ['type' => 'INT(11)', 'null' => false],
            'category_id' => ['type' => 'INT(11)', 'null' => true],
            'status' => ['type' => 'ENUM("draft","published","archived")', 'default' => 'draft'],
            'featured_image' => ['type' => 'VARCHAR(255)', 'null' => true],
            'meta_title' => ['type' => 'VARCHAR(255)', 'null' => true],
            'meta_description' => ['type' => 'TEXT', 'null' => true],
            'view_count' => ['type' => 'INT(11)', 'default' => '0'],
            'published_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
            'updated_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP']
        ], [
            'primary_key' => 'id',
            'unique_keys' => ['slug'],
            'indexes' => ['author_id', 'category_id', 'status', 'published_at']
        ]);
        
        // Categories tablosu
        DbTabler::createTable('categories', [
            'id' => ['type' => 'INT(11)', 'auto_increment' => true, 'null' => false],
            'name' => ['type' => 'VARCHAR(100)', 'null' => false],
            'slug' => ['type' => 'VARCHAR(100)', 'null' => false],
            'description' => ['type' => 'TEXT', 'null' => true],
            'parent_id' => ['type' => 'INT(11)', 'null' => true],
            'sort_order' => ['type' => 'INT(11)', 'default' => '0'],
            'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP']
        ], [
            'primary_key' => 'id',
            'unique_keys' => ['slug'],
            'indexes' => ['parent_id', 'sort_order']
        ]);
    }
}
```

### E-ticaret Tabloları
```php
class EcommerceInstaller
{
    public static function install()
    {
        // Products tablosu
        DbTabler::createTable('products', [
            'id' => ['type' => 'INT(11)', 'auto_increment' => true, 'null' => false],
            'name' => ['type' => 'VARCHAR(255)', 'null' => false],
            'slug' => ['type' => 'VARCHAR(255)', 'null' => false],
            'sku' => ['type' => 'VARCHAR(50)', 'null' => false],
            'description' => ['type' => 'TEXT', 'null' => true],
            'short_description' => ['type' => 'VARCHAR(500)', 'null' => true],
            'price' => ['type' => 'DECIMAL(10,2)', 'null' => false],
            'sale_price' => ['type' => 'DECIMAL(10,2)', 'null' => true],
            'stock_quantity' => ['type' => 'INT(11)', 'default' => '0'],
            'weight' => ['type' => 'DECIMAL(8,2)', 'null' => true],
            'dimensions' => ['type' => 'VARCHAR(100)', 'null' => true],
            'category_id' => ['type' => 'INT(11)', 'null' => true],
            'brand_id' => ['type' => 'INT(11)', 'null' => true],
            'status' => ['type' => 'ENUM("active","inactive")', 'default' => 'active'],
            'featured' => ['type' => 'BOOLEAN', 'default' => '0'],
            'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP']
        ], [
            'primary_key' => 'id',
            'unique_keys' => ['slug', 'sku'],
            'indexes' => ['category_id', 'brand_id', 'status', 'price']
        ]);
        
        // Orders tablosu
        DbTabler::createTable('orders', [
            'id' => ['type' => 'INT(11)', 'auto_increment' => true, 'null' => false],
            'order_number' => ['type' => 'VARCHAR(50)', 'null' => false],
            'customer_id' => ['type' => 'INT(11)', 'null' => true],
            'customer_email' => ['type' => 'VARCHAR(150)', 'null' => false],
            'customer_phone' => ['type' => 'VARCHAR(20)', 'null' => true],
            'billing_address' => ['type' => 'TEXT', 'null' => false],
            'shipping_address' => ['type' => 'TEXT', 'null' => true],
            'subtotal' => ['type' => 'DECIMAL(10,2)', 'null' => false],
            'tax_amount' => ['type' => 'DECIMAL(10,2)', 'default' => '0.00'],
            'shipping_amount' => ['type' => 'DECIMAL(10,2)', 'default' => '0.00'],
            'total_amount' => ['type' => 'DECIMAL(10,2)', 'null' => false],
            'status' => ['type' => 'ENUM("pending","processing","shipped","delivered","cancelled")', 'default' => 'pending'],
            'payment_status' => ['type' => 'ENUM("pending","paid","failed","refunded")', 'default' => 'pending'],
            'payment_method' => ['type' => 'VARCHAR(50)', 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP']
        ], [
            'primary_key' => 'id',
            'unique_keys' => ['order_number'],
            'indexes' => ['customer_id', 'status', 'payment_status', 'created_at']
        ]);
    }
}
```

### Kullanıcı Sistemi Tabloları
```php
class UserSystemInstaller
{
    public static function install()
    {
        // Users tablosu
        DbTabler::createTable('users', [
            'id' => ['type' => 'INT(11)', 'auto_increment' => true, 'null' => false],
            'username' => ['type' => 'VARCHAR(50)', 'null' => false],
            'email' => ['type' => 'VARCHAR(150)', 'null' => false],
            'password' => ['type' => 'VARCHAR(255)', 'null' => false],
            'first_name' => ['type' => 'VARCHAR(50)', 'null' => true],
            'last_name' => ['type' => 'VARCHAR(50)', 'null' => true],
            'phone' => ['type' => 'VARCHAR(20)', 'null' => true],
            'avatar' => ['type' => 'VARCHAR(255)', 'null' => true],
            'role' => ['type' => 'ENUM("admin","editor","user")', 'default' => 'user'],
            'status' => ['type' => 'ENUM("active","inactive","banned")', 'default' => 'active'],
            'email_verified_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'last_login_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
            'updated_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP']
        ], [
            'primary_key' => 'id',
            'unique_keys' => ['username', 'email'],
            'indexes' => ['role', 'status', 'created_at']
        ]);
        
        // User sessions tablosu
        DbTabler::createTable('user_sessions', [
            'id' => ['type' => 'VARCHAR(128)', 'null' => false],
            'user_id' => ['type' => 'INT(11)', 'null' => true],
            'ip_address' => ['type' => 'VARCHAR(45)', 'null' => true],
            'user_agent' => ['type' => 'TEXT', 'null' => true],
            'payload' => ['type' => 'LONGTEXT', 'null' => false],
            'last_activity' => ['type' => 'INT(11)', 'null' => false]
        ], [
            'primary_key' => 'id',
            'indexes' => ['user_id', 'last_activity']
        ]);
    }
}
```

### Tablo Güncelleme Sistemi
```php
class DatabaseMigration
{
    private static $version = '1.0.0';
    
    public static function migrate()
    {
        $currentVersion = self::getCurrentVersion();
        
        if (version_compare($currentVersion, '1.1.0', '<')) {
            self::migrateToV110();
        }
        
        if (version_compare($currentVersion, '1.2.0', '<')) {
            self::migrateToV120();
        }
        
        self::updateVersion(self::$version);
    }
    
    private static function migrateToV110()
    {
        // Users tablosuna yeni kolonlar ekle
        if (!DbTabler::columnExists('users', 'two_factor_enabled')) {
            DbTabler::addColumn('users', 'two_factor_enabled', [
                'type' => 'BOOLEAN',
                'default' => '0',
                'comment' => 'İki faktörlü doğrulama aktif mi?'
            ]);
        }
        
        if (!DbTabler::columnExists('users', 'two_factor_secret')) {
            DbTabler::addColumn('users', 'two_factor_secret', [
                'type' => 'VARCHAR(255)',
                'null' => true,
                'comment' => 'İki faktörlü doğrulama gizli anahtarı'
            ]);
        }
        
        // Posts tablosuna SEO kolonları ekle
        if (!DbTabler::columnExists('posts', 'seo_title')) {
            DbTabler::addColumn('posts', 'seo_title', [
                'type' => 'VARCHAR(255)',
                'null' => true,
                'comment' => 'SEO başlığı'
            ]);
        }
    }
    
    private static function migrateToV120()
    {
        // Yeni tablo: post_views
        if (!DbTabler::tableExists('post_views')) {
            DbTabler::createTable('post_views', [
                'id' => ['type' => 'BIGINT(20)', 'auto_increment' => true, 'null' => false],
                'post_id' => ['type' => 'INT(11)', 'null' => false],
                'user_id' => ['type' => 'INT(11)', 'null' => true],
                'ip_address' => ['type' => 'VARCHAR(45)', 'null' => false],
                'user_agent' => ['type' => 'TEXT', 'null' => true],
                'viewed_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP']
            ], [
                'primary_key' => 'id',
                'indexes' => ['post_id', 'user_id', 'ip_address', 'viewed_at']
            ]);
        }
    }
    
    private static function getCurrentVersion()
    {
        // Veritabanından mevcut versiyonu al
        return '1.0.0'; // Örnek
    }
    
    private static function updateVersion($version)
    {
        // Veritabanında versiyonu güncelle
    }
}
```

### Tablo Yedekleme
```php
class TableBackup
{
    public static function backupTable($tableName)
    {
        $backupName = $tableName . '_backup_' . date('Y_m_d_H_i_s');
        
        // Tablo yapısını kopyala
        $sql = "CREATE TABLE `{$backupName}` LIKE `{$tableName}`";
        Sword::db()->query($sql);
        
        // Verileri kopyala
        $sql = "INSERT INTO `{$backupName}` SELECT * FROM `{$tableName}`";
        Sword::db()->query($sql);
        
        return $backupName;
    }
    
    public static function restoreTable($originalName, $backupName)
    {
        // Orijinal tabloyu sil
        DbTabler::dropTable($originalName);
        
        // Yedek tabloyu orijinal isimle kopyala
        $sql = "CREATE TABLE `{$originalName}` LIKE `{$backupName}`";
        Sword::db()->query($sql);
        
        $sql = "INSERT INTO `{$originalName}` SELECT * FROM `{$backupName}`";
        Sword::db()->query($sql);
        
        return true;
    }
}
```

## İpuçları

1. **Yedekleme**: Önemli değişikliklerden önce tablo yedekleyin
2. **İndeksler**: Sık sorgulanan kolonlara indeks ekleyin
3. **Veri Tipleri**: Uygun veri tiplerini seçin (performans için)
4. **Charset**: UTF8MB4 kullanarak emoji desteği sağlayın
5. **Migration**: Veritabanı değişikliklerini versiyonlayın