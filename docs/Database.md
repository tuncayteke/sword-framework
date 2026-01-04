# Database Sınıfı

Database sınıfı, Sword Framework'te veritabanı işlemlerini yönetir ve ORM Query Builder'a yönlendirme yapar.

## Temel Kullanım

```php
// Database bağlantısı
$db = Sword::db();

// Veya yapılandırma ile
$db = Sword::db([
    'host' => 'localhost',
    'database' => 'mydb',
    'username' => 'user',
    'password' => 'pass'
]);
```

## Özellikler

- **ORM Entegrasyonu**: Modern ORM Query Builder ile entegre
- **Çoklu Veritabanı**: MySQL, PostgreSQL, SQLite desteği
- **Connection Factory**: Otomatik bağlantı yönetimi
- **Transaction Manager**: İşlem yönetimi

## Metodlar

### Temel Metodlar

```php
// Bağlantı durumu
$isConnected = $db->isConnected();

// Sorgu çalıştırma
$result = $db->query("SELECT * FROM users");

// Prepared statement
$result = $db->prepare("SELECT * FROM users WHERE id = ?", [1]);
```

## Yapılandırma

```php
// db_config.php
return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => 3306,
            'database' => 'sword_db',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ]
    ]
];
```

## ORM Kullanımı

Database sınıfı, modern ORM Query Builder'ı kullanır:

```php
// Query Builder
$users = $db->table('users')
    ->where('active', 1)
    ->orderBy('name')
    ->get();

// Model ile
$user = new UserModel();
$activeUsers = $user->where('active', 1)->get();
```

## Hata Yönetimi

```php
try {
    $result = $db->query("SELECT * FROM users");
} catch (DatabaseException $e) {
    Logger::error('Database error: ' . $e->getMessage());
}
```

## İlgili Sınıflar

- [Model](Model.md) - ORM Model sınıfı
- [QueryBuilder](QueryBuilder.md) - Sorgu oluşturucu
- [Logger](Logger.md) - Hata kayıtları