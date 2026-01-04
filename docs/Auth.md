# Auth Sınıfı

Auth sınıfı, Sword Framework'te kimlik doğrulama işlemlerini yönetir. Guard tabanlı çoklu kimlik doğrulama sistemi sunar.

## Temel Kullanım

```php
// Giriş yapmaya çalış
$success = Auth::attempt(['email' => $email, 'password' => $password]);

// Kullanıcı kontrolü
if (Auth::check()) {
    $user = Auth::user();
}

// Çıkış yap
Auth::logout();
```

## Özellikler

- **Multi-Guard System**: Web ve API için farklı guard'lar
- **Remember Me**: Beni hatırla özelliği
- **Rate Limiting**: Giriş denemesi sınırlama
- **Session Management**: Güvenli oturum yönetimi
- **Event System**: Auth olayları

## Guard Sistemi

### Varsayılan Guard'lar

```php
// Web guard (session tabanlı)
Auth::guard('web')->attempt($credentials);

// API guard (token tabanlı)  
Auth::guard('api')->attempt($credentials);

// Varsayılan guard kullanımı
Auth::attempt($credentials); // 'web' guard kullanır
```

### Guard Yapılandırması

```php
// Guard ayarları
Auth::setGuardConfig('custom', [
    'driver' => 'session',
    'provider' => 'users'
]);

// Varsayılan guard değiştir
Auth::setDefaultGuard('api');
```

## Metodlar

### attempt($credentials, $remember = false)
Giriş yapmaya çalışır.

```php
$success = Auth::attempt([
    'email' => 'user@example.com',
    'password' => 'password123'
], true); // Remember me

if ($success) {
    // Giriş başarılı
} else {
    // Giriş başarısız
}
```

### login($user, $remember = false)
Kullanıcıyı doğrudan giriş yapar.

```php
$user = User::find(1);
Auth::login($user, true);
```

### loginUsingId($id, $remember = false)
ID ile kullanıcıyı giriş yapar.

```php
Auth::loginUsingId(123, false);
```

### user()
Aktif kullanıcıyı döndürür.

```php
$user = Auth::user();
if ($user) {
    echo "Hoş geldin " . $user->name;
}
```

### check()
Kullanıcı giriş yapmış mı kontrol eder.

```php
if (Auth::check()) {
    // Kullanıcı giriş yapmış
} else {
    // Misafir kullanıcı
}
```

### guest()
Kullanıcı misafir mi kontrol eder.

```php
if (Auth::guest()) {
    // Giriş sayfasına yönlendir
    Sword::redirect('/login');
}
```

### id()
Kullanıcı ID'sini döndürür.

```php
$userId = Auth::id();
```

### logout()
Çıkış yapar.

```php
Auth::logout();
Sword::redirect('/');
```

## Helper Fonksiyonları

```php
// Auth helper
$guard = auth(); // Varsayılan guard
$apiGuard = auth('api'); // API guard

// Kullanıcı helper'ları
$user = user(); // Aktif kullanıcı
$userId = user_id(); // Kullanıcı ID

// Giriş/Çıkış helper'ları
$success = login('user@example.com', 'password', true);
logout();
```

## Middleware Kullanımı

```php
// Auth middleware
Sword::before('routerDispatch', function() {
    if (Auth::guest() && in_array($_SERVER['REQUEST_URI'], ['/admin', '/profile'])) {
        Sword::redirect('/login');
    }
});

// Admin middleware
Sword::before('routerDispatch', function() {
    if (Auth::check() && !Auth::user()->isAdmin()) {
        Sword::response()->forbidden()->send();
    }
});
```

## Controller'da Kullanım

```php
class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return $this->redirect('/dashboard');
        }
        
        return $this->render('auth/login');
    }
    
    public function authenticate()
    {
        $validation = Sword::validate($_POST, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (!$validation->passes()) {
            return $this->redirect('/login')->with('errors', $validation->errors());
        }
        
        $remember = isset($_POST['remember']);
        
        if (Auth::attempt($_POST, $remember)) {
            return $this->redirect('/dashboard');
        }
        
        return $this->redirect('/login')->with('error', 'Geçersiz kimlik bilgileri');
    }
    
    public function logout()
    {
        Auth::logout();
        return $this->redirect('/');
    }
}
```

## Rate Limiting

```php
// Guard sınıfında otomatik rate limiting
// Varsayılan: 5 deneme, 10 dakika bekleme

// Özelleştirme
$guard = Auth::guard();
$guard->maxAttempts = 3;
$guard->lockoutTime = 15; // dakika
```

## Remember Me Sistemi

```php
// Remember token ile giriş
Auth::attempt($credentials, true);

// Cookie'den otomatik giriş
// Guard otomatik olarak remember cookie'yi kontrol eder
```

## Events

```php
// Giriş olayı
Events::on('auth.login', function($data) {
    $user = $data['user'];
    $guard = $data['guard'];
    
    Logger::info("User {$user->email} logged in via {$guard}");
});

// Çıkış olayı
Events::on('auth.logout', function($data) {
    $user = $data['user'];
    Logger::info("User {$user->email} logged out");
});
```

## API Authentication

```php
// API guard için token tabanlı auth
class ApiController extends Controller
{
    public function __construct()
    {
        // API guard kullan
        $this->guard = 'api';
    }
    
    public function user()
    {
        $user = Auth::guard('api')->user();
        
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }
        
        return $this->json(['user' => $user]);
    }
}
```

## Özel User Model

```php
class User extends Model
{
    protected $table = 'users';
    
    // Auth için gerekli alanlar
    protected $fillable = ['name', 'email', 'password'];
    
    // Password mutator
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }
    
    // Admin kontrolü
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
```

## Güvenlik Özellikleri

### Session Güvenliği
- Session regeneration giriş sırasında
- Güvenli cookie ayarları
- HttpOnly ve Secure flags

### Password Güvenliği
- PHP'nin password_hash() fonksiyonu
- Otomatik salt oluşturma
- Timing attack koruması

### Rate Limiting
- IP bazlı deneme sınırlaması
- Configurable limits
- Otomatik lockout

## Örnek Kullanımlar

### Basit Login Sistemi

```php
// Routes.php
Sword::routerGet('/login', 'AuthController@showLogin');
Sword::routerPost('/login', 'AuthController@login');
Sword::routerPost('/logout', 'AuthController@logout');

// AuthController.php
class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirect('/dashboard');
        }
        
        return $this->render('auth/login');
    }
    
    public function login()
    {
        $credentials = [
            'email' => $_POST['email'],
            'password' => $_POST['password']
        ];
        
        if (Auth::attempt($credentials, isset($_POST['remember']))) {
            return $this->redirect('/dashboard');
        }
        
        return $this->redirect('/login')->with('error', 'Giriş başarısız');
    }
    
    public function logout()
    {
        Auth::logout();
        return $this->redirect('/');
    }
}
```

### Protected Routes

```php
// Middleware ile korumalı rotalar
Sword::routerGroup('/admin', function() {
    Sword::routerGet('/', 'AdminController@dashboard');
    Sword::routerGet('/users', 'AdminController@users');
})->middleware(function() {
    if (Auth::guest()) {
        Sword::redirect('/login');
    }
    
    if (!Auth::user()->isAdmin()) {
        Sword::response()->forbidden()->send();
    }
});
```

## İlgili Sınıflar

- [Security](Security.md) - Güvenlik işlemleri
- [Session](Session.md) - Oturum yönetimi
- [Cookie](Cookie.md) - Cookie yönetimi
- [Validation](Validation.md) - Form doğrulama