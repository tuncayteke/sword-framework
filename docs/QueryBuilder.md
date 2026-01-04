# QueryBuilder Sınıfı

QueryBuilder sınıfı, Sword Framework'te SQL sorgularını programatik olarak oluşturmak için kullanılır. Modern ORM Query Builder'ı genişletir.

## Temel Kullanım

```php
// QueryBuilder oluştur
$query = Sword::query();

// Tablo seç
$users = $query->table('users')->get();

// Koşullu sorgu
$activeUsers = $query->table('users')
    ->where('active', 1)
    ->get();
```

## Özellikler

- **Fluent Interface**: Zincirleme metod desteği
- **SQL Injection Protection**: Güvenli parametre binding
- **Multiple Database Support**: Çoklu veritabanı desteği
- **Advanced Joins**: Gelişmiş join işlemleri
- **Subquery Support**: Alt sorgu desteği

## Temel Metodlar

### Select İşlemleri

```php
// Tüm kayıtları al
$users = $query->table('users')->get();

// Tek kayıt al
$user = $query->table('users')->where('id', 1)->first();

// Belirli alanları seç
$users = $query->table('users')
    ->select('id', 'name', 'email')
    ->get();

// Distinct seçim
$cities = $query->table('users')
    ->select('city')
    ->distinct()
    ->get();
```

### Where Koşulları

```php
// Basit where
$query->where('name', 'John');
$query->where('age', '>', 18);
$query->where('status', '!=', 'inactive');

// Multiple where
$query->where('active', 1)
      ->where('verified', 1);

// Or where
$query->where('role', 'admin')
      ->orWhere('role', 'moderator');

// Where in
$query->whereIn('id', [1, 2, 3, 4]);

// Where between
$query->whereBetween('age', [18, 65]);

// Where null
$query->whereNull('deleted_at');
$query->whereNotNull('email_verified_at');

// Where like
$query->where('name', 'LIKE', '%john%');
```

### Sıralama ve Gruplama

```php
// Order by
$query->orderBy('created_at', 'DESC');
$query->orderBy('name', 'ASC');

// Multiple order
$query->orderBy('priority', 'DESC')
      ->orderBy('name', 'ASC');

// Group by
$query->groupBy('category_id');
$query->groupBy('category_id', 'status');

// Having
$query->groupBy('category_id')
      ->having('COUNT(*)', '>', 5);
```

### Limit ve Offset

```php
// Limit
$query->limit(10);

// Offset
$query->offset(20);

// Pagination
$query->limit(10)->offset(20); // Sayfa 3, sayfa başına 10

// Take (limit alias)
$query->take(5);

// Skip (offset alias)
$query->skip(10);
```

## Join İşlemleri

```php
// Inner join
$query->table('users')
      ->join('profiles', 'users.id', '=', 'profiles.user_id');

// Left join
$query->table('users')
      ->leftJoin('orders', 'users.id', '=', 'orders.user_id');

// Right join
$query->table('users')
      ->rightJoin('roles', 'users.role_id', '=', 'roles.id');

// Complex join
$query->table('users')
      ->join('orders', function($join) {
          $join->on('users.id', '=', 'orders.user_id')
               ->where('orders.status', '=', 'completed');
      });
```

## Insert İşlemleri

```php
// Tek kayıt ekle
$query->table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'created_at' => date('Y-m-d H:i:s')
]);

// Çoklu kayıt ekle
$query->table('users')->insert([
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com']
]);

// Insert ve ID al
$id = $query->table('users')->insertGetId([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

## Update İşlemleri

```php
// Kayıt güncelle
$query->table('users')
      ->where('id', 1)
      ->update([
          'name' => 'John Smith',
          'updated_at' => date('Y-m-d H:i:s')
      ]);

// Increment/Decrement
$query->table('users')
      ->where('id', 1)
      ->increment('login_count');

$query->table('products')
      ->where('id', 1)
      ->decrement('stock', 5);
```

## Delete İşlemleri

```php
// Kayıt sil
$query->table('users')
      ->where('id', 1)
      ->delete();

// Çoklu silme
$query->table('users')
      ->whereIn('id', [1, 2, 3])
      ->delete();

// Tüm kayıtları sil
$query->table('temp_data')->delete();

// Truncate
$query->table('logs')->truncate();
```

## Aggregate Fonksiyonlar

```php
// Count
$userCount = $query->table('users')->count();
$activeCount = $query->table('users')
    ->where('active', 1)
    ->count();

// Sum
$totalSales = $query->table('orders')->sum('amount');

// Average
$avgAge = $query->table('users')->avg('age');

// Min/Max
$minPrice = $query->table('products')->min('price');
$maxPrice = $query->table('products')->max('price');
```

## Subquery İşlemleri

```php
// Where subquery
$query->table('users')
      ->whereIn('id', function($subquery) {
          $subquery->table('orders')
                   ->select('user_id')
                   ->where('status', 'completed');
      });

// Select subquery
$query->table('users')
      ->select('*')
      ->selectSub(function($subquery) {
          $subquery->table('orders')
                   ->selectRaw('COUNT(*)')
                   ->whereColumn('user_id', 'users.id');
      }, 'order_count');
```

## Raw Queries

```php
// Raw select
$query->selectRaw('COUNT(*) as total, AVG(age) as avg_age');

// Raw where
$query->whereRaw('YEAR(created_at) = ?', [2024]);

// Raw order
$query->orderByRaw('FIELD(status, "pending", "processing", "completed")');

// Tam raw query
$results = $query->raw('SELECT * FROM users WHERE active = ?', [1]);
```

## Transaction Desteği

```php
// Transaction başlat
$query->beginTransaction();

try {
    $query->table('users')->insert($userData);
    $query->table('profiles')->insert($profileData);
    
    $query->commit();
} catch (Exception $e) {
    $query->rollback();
    throw $e;
}
```

## Pagination

```php
// Sayfalama
$page = 1;
$perPage = 15;
$users = $query->table('users')
    ->paginate($page, $perPage);

// Sonuç:
/*
[
    'data' => [...],
    'current_page' => 1,
    'per_page' => 15,
    'total' => 150,
    'last_page' => 10
]
*/
```

## Cache Desteği

```php
// Sorgu sonucunu cache'le
$users = $query->table('users')
    ->where('active', 1)
    ->cache(3600) // 1 saat
    ->get();

// Cache key ile
$users = $query->table('users')
    ->cacheKey('active_users')
    ->cache(3600)
    ->get();
```

## İlgili Sınıflar

- [Database](Database.md) - Veritabanı bağlantısı
- [Model](Model.md) - ORM Model sınıfı
- [Cache](Cache.md) - Sorgu önbellekleme