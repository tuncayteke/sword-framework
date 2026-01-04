# Model Class

Temel model sınıfı. ORM/Model sınıfını genişletir ve ek yardımcı metodlar sunar.

## Temel Kullanım

```php
class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
}

// Kullanım
$user = new User();
$users = $user->all();
$user = $user->find(1);
```

## Model Tanımlama

### Temel Model
```php
class Post extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'id';
    protected $fillable = ['title', 'content', 'author_id'];
    protected $hidden = ['password', 'remember_token'];
    protected $dates = ['created_at', 'updated_at', 'published_at'];
}
```

### Yapılandırma Özellikleri
- `$table` - Tablo adı
- `$primaryKey` - Birincil anahtar
- `$fillable` - Doldurulabilir alanlar
- `$hidden` - Gizli alanlar (JSON'da görünmez)
- `$dates` - Tarih alanları
- `$timestamps` - Otomatik timestamp'ler

## Temel CRUD İşlemleri

### Kayıt Bulma

#### find($id)
ID ile kayıt bulur.

```php
$user = User::find(1);
$post = Post::find(123);
```

#### findOrFail($id)
Bulunamazsa exception fırlatır.

```php
try {
    $user = User::findOrFail(999);
} catch (ModelNotFoundException $e) {
    // Kayıt bulunamadı
}
```

#### first()
İlk kaydı döndürür.

```php
$firstUser = User::first();
$latestPost = Post::orderBy('created_at', 'desc')->first();
```

#### all()
Tüm kayıtları döndürür.

```php
$users = User::all();
$posts = Post::all();
```

### Kayıt Oluşturma

#### create($data)
Yeni kayıt oluşturur.

```php
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => bcrypt('password')
]);
```

#### save()
Mevcut modeli kaydeder.

```php
$user = new User();
$user->name = 'Jane Doe';
$user->email = 'jane@example.com';
$user->save();
```

### Kayıt Güncelleme

#### update($data)
Kayıt günceller.

```php
$user = User::find(1);
$user->update([
    'name' => 'Updated Name',
    'email' => 'updated@example.com'
]);
```

#### save()
Değişiklikleri kaydeder.

```php
$user = User::find(1);
$user->name = 'New Name';
$user->save();
```

### Kayıt Silme

#### delete()
Kayıt siler.

```php
$user = User::find(1);
$user->delete();
```

#### destroy($ids)
ID'lere göre siler.

```php
User::destroy(1);
User::destroy([1, 2, 3]);
```

## Query Builder Metodları

### where($column, $operator, $value)
WHERE koşulu ekler.

```php
$users = User::where('status', 'active')->get();
$posts = Post::where('views', '>', 1000)->get();
$users = User::where('created_at', '>=', '2023-01-01')->get();
```

### orWhere($column, $operator, $value)
OR WHERE koşulu ekler.

```php
$users = User::where('role', 'admin')
            ->orWhere('role', 'moderator')
            ->get();
```

### whereIn($column, $values)
IN koşulu ekler.

```php
$users = User::whereIn('id', [1, 2, 3, 4])->get();
$posts = Post::whereIn('category_id', [1, 5, 10])->get();
```

### whereBetween($column, $values)
BETWEEN koşulu ekler.

```php
$posts = Post::whereBetween('created_at', ['2023-01-01', '2023-12-31'])->get();
$products = Product::whereBetween('price', [100, 500])->get();
```

### orderBy($column, $direction)
Sıralama ekler.

```php
$users = User::orderBy('name', 'asc')->get();
$posts = Post::orderBy('created_at', 'desc')->get();
```

### limit($count)
Limit ekler.

```php
$latestPosts = Post::orderBy('created_at', 'desc')->limit(10)->get();
```

### offset($count)
Offset ekler.

```php
$posts = Post::offset(20)->limit(10)->get(); // 21-30 arası kayıtlar
```

## Ek Yardımcı Metodlar

### findWhere($where, $params = [])
Koşula göre tek kayıt bulur.

```php
$user = User::findWhere('email = ?', ['john@example.com']);
$post = Post::findWhere('slug = ? AND status = ?', ['my-post', 'published']);
```

### findAllWhere($where, $params = [], $order = null, $limit = null, $offset = null)
Koşula göre tüm kayıtları bulur.

```php
$users = User::findAllWhere('status = ?', ['active']);
$posts = Post::findAllWhere('author_id = ?', [1], 'created_at DESC', 10);
```

### search($search)
Arama yapar (User modeli için örnek).

```php
$users = User::search('john');
// username, email, full_name alanlarında arar
```

## İlişkiler (Relations)

### hasOne($related, $foreignKey = null, $localKey = null)
Bire bir ilişki.

```php
class User extends Model
{
    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id');
    }
}

// Kullanım
$user = User::find(1);
$profile = $user->profile;
```

### hasMany($related, $foreignKey = null, $localKey = null)
Bire çok ilişki.

```php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }
}

// Kullanım
$user = User::find(1);
$posts = $user->posts;
```

### belongsTo($related, $foreignKey = null, $ownerKey = null)
Ters bire bir/çok ilişki.

```php
class Post extends Model
{
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}

// Kullanım
$post = Post::find(1);
$author = $post->author;
```

### belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null)
Çoktan çoğa ilişki.

```php
class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }
}

// Kullanım
$user = User::find(1);
$roles = $user->roles;
```

## Örnek Kullanımlar

### Kullanıcı Modeli
```php
class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password', 'role'];
    protected $hidden = ['password', 'remember_token'];
    
    // İlişkiler
    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }
    
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }
    
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }
    
    // Yardımcı metodlar
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
    
    // Scope'lar
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }
}
```

### Blog Post Modeli
```php
class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = ['title', 'content', 'slug', 'author_id', 'category_id', 'status'];
    protected $dates = ['published_at'];
    
    // İlişkiler
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
    // Yardımcı metodlar
    public function isPublished()
    {
        return $this->status === 'published' && $this->published_at <= now();
    }
    
    public function getExcerptAttribute()
    {
        return substr(strip_tags($this->content), 0, 200) . '...';
    }
    
    public function getUrlAttribute()
    {
        return '/blog/' . $this->slug;
    }
    
    // Scope'lar
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }
    
    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('author_id', $authorId);
    }
    
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
```

### E-ticaret Product Modeli
```php
class Product extends Model
{
    protected $table = 'products';
    protected $fillable = ['name', 'description', 'price', 'stock', 'category_id', 'status'];
    
    // İlişkiler
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    
    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }
    
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    // Yardımcı metodlar
    public function isInStock()
    {
        return $this->stock > 0;
    }
    
    public function isActive()
    {
        return $this->status === 'active';
    }
    
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' TL';
    }
    
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }
    
    public function decreaseStock($quantity)
    {
        if ($this->stock >= $quantity) {
            $this->stock -= $quantity;
            $this->save();
            return true;
        }
        return false;
    }
    
    // Scope'lar
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }
    
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
    
    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }
}
```

### Controller'da Kullanım
```php
class UserController extends Controller
{
    public function index()
    {
        // Tüm aktif kullanıcıları getir
        $users = User::active()->orderBy('name')->get();
        
        $this->set('users', $users);
        $this->render('user/index');
    }
    
    public function show($id)
    {
        // Kullanıcı ve ilişkilerini getir
        $user = User::with(['posts', 'profile'])->findOrFail($id);
        
        $this->set('user', $user);
        $this->render('user/show');
    }
    
    public function store()
    {
        $data = $this->request->post();
        
        // Validation
        $validation = Sword::validate($data, [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
        
        if ($validation->fails()) {
            return $this->error('Validation failed', 422);
        }
        
        // Kullanıcı oluştur
        $user = User::create($validation->getValidData());
        
        return $this->success($user);
    }
    
    public function search()
    {
        $query = $this->request->get('q');
        
        if ($query) {
            $users = User::search($query);
        } else {
            $users = User::all();
        }
        
        return $this->json($users);
    }
}
```

## İpuçları

1. **İlişkiler**: Eager loading kullanarak N+1 problemini önleyin
2. **Scope'lar**: Tekrar eden sorguları scope'lara çevirin
3. **Mutators**: Veri girişinde otomatik dönüşümler yapın
4. **Accessors**: Veri çıkışında hesaplanmış alanlar ekleyin
5. **Validation**: Model seviyesinde validation kuralları tanımlayın