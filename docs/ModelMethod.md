# ModelMethod Sınıfı

ModelMethod sınıfı, Sword Framework'te model metodlarının dinamik olarak yönetilmesi ve genişletilmesi için kullanılır.

## Temel Kullanım

```php
// Model metodunu kaydet
ModelMethod::register('findActive', function($model) {
    return $model->where('active', 1)->get();
});

// Model'de kullan
$users = $userModel->findActive();
```

## Özellikler

- **Dynamic Methods**: Dinamik metod ekleme
- **Method Chaining**: Zincirleme metod desteği
- **Scope Methods**: Model scope metodları
- **Custom Queries**: Özel sorgu metodları

## Metodlar

### Metod Kaydı

```php
// Basit metod kaydı
ModelMethod::register(string $name, callable $callback);

// Scope metod kaydı
ModelMethod::scope(string $name, callable $callback);

// Query metod kaydı
ModelMethod::query(string $name, callable $callback);
```

### Metod Yönetimi

```php
// Metod var mı kontrol et
$exists = ModelMethod::has('findActive');

// Metod sil
ModelMethod::remove('findActive');

// Tüm metodları al
$methods = ModelMethod::all();

// Metodları temizle
ModelMethod::clear();
```

## Scope Metodları

```php
// Active scope
ModelMethod::scope('active', function($query) {
    return $query->where('active', 1);
});

// Published scope
ModelMethod::scope('published', function($query) {
    return $query->where('status', 'published')
                 ->where('published_at', '<=', date('Y-m-d H:i:s'));
});

// Kullanım
$posts = $postModel->active()->published()->get();
```

## Query Metodları

```php
// Özel sorgu metodları
ModelMethod::query('findByEmail', function($model, $email) {
    return $model->where('email', $email)->first();
});

ModelMethod::query('findRecent', function($model, $days = 7) {
    return $model->where('created_at', '>=', 
        date('Y-m-d', strtotime("-{$days} days")))->get();
});

// Kullanım
$user = $userModel->findByEmail('user@example.com');
$recentPosts = $postModel->findRecent(30);
```

## Relationship Metodları

```php
// Has many through
ModelMethod::register('posts', function($model) {
    return $model->hasMany('PostModel', 'user_id');
});

// Belongs to many
ModelMethod::register('roles', function($model) {
    return $model->belongsToMany('RoleModel', 'user_roles', 'user_id', 'role_id');
});
```

## Aggregate Metodları

```php
// Count metodları
ModelMethod::register('countActive', function($model) {
    return $model->where('active', 1)->count();
});

// Sum metodları
ModelMethod::register('totalSales', function($model) {
    return $model->sum('amount');
});

// Average metodları
ModelMethod::register('averageRating', function($model) {
    return $model->avg('rating');
});
```

## Validation Metodları

```php
// Validation scope
ModelMethod::register('validate', function($model, $rules = []) {
    $validation = Sword::validate($model->toArray(), $rules);
    
    if (!$validation->passes()) {
        throw new ValidationException($validation->errors());
    }
    
    return $model;
});

// Kullanım
$user->validate([
    'email' => 'required|email|unique:users',
    'name' => 'required|min:2'
])->save();
```

## Cache Metodları

```php
// Cache'li metodlar
ModelMethod::register('getCached', function($model, $key, $ttl = 3600) {
    return Cache::remember($key, $ttl, function() use ($model) {
        return $model->get();
    });
});

// Cache temizleme
ModelMethod::register('clearCache', function($model, $pattern = null) {
    if ($pattern) {
        Cache::forget($pattern);
    } else {
        Cache::flush();
    }
    return $model;
});
```

## Event Metodları

```php
// Model events
ModelMethod::register('onCreate', function($model, $callback) {
    Events::on('model.creating', $callback);
    return $model;
});

ModelMethod::register('onUpdate', function($model, $callback) {
    Events::on('model.updating', $callback);
    return $model;
});

// Kullanım
$userModel->onCreate(function($user) {
    Logger::info('New user created: ' . $user->email);
});
```

## Batch Metodları

```php
// Toplu işlemler
ModelMethod::register('batchUpdate', function($model, $data, $where = []) {
    $query = $model->newQuery();
    
    foreach ($where as $field => $value) {
        $query->where($field, $value);
    }
    
    return $query->update($data);
});

ModelMethod::register('batchDelete', function($model, $ids) {
    return $model->whereIn('id', $ids)->delete();
});
```

## Macro Metodları

```php
// Macro tanımlama
ModelMethod::macro('search', function($term) {
    return $this->where('name', 'LIKE', "%{$term}%")
                ->orWhere('description', 'LIKE', "%{$term}%");
});

// Kullanım
$results = $productModel->search('laptop')->get();
```

## İlgili Sınıflar

- [Model](Model.md) - Ana model sınıfı
- [QueryBuilder](QueryBuilder.md) - Sorgu oluşturucu
- [Validation](Validation.md) - Doğrulama
- [Cache](Cache.md) - Önbellek yönetimi