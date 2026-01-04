# Cookie Class

Güvenli cookie yönetimi sağlar. Otomatik şifreleme, domain tespiti ve güvenlik özellikleri içerir.

## Temel Kullanım

```php
// Sınıfa erişim
$cookie = Sword::cookie();
// veya
Cookie::set('name', 'value');
```

## Cookie İşlemleri

### set($name, $value, $expire = 0, $options = [])
Cookie ayarlar.

```php
// Basit cookie
Cookie::set('theme', 'dark');

// Süreli cookie (1 saat)
Cookie::set('user_pref', 'compact', 3600);

// Özel seçeneklerle
Cookie::set('secure_data', $data, 86400, [
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
```

### get($name, $default = null, $encrypted = false)
Cookie değeri alır.

```php
$theme = Cookie::get('theme', 'light');
$userPref = Cookie::get('user_pref');
```

### has($name)
Cookie var mı kontrol eder.

```php
if (Cookie::has('user_session')) {
    // Cookie mevcut
}
```

### delete($name, $options = [])
Cookie siler.

```php
Cookie::delete('old_cookie');
```

### clear()
Tüm framework cookie'lerini siler.

```php
Cookie::clear(); // sword_ önekli tüm cookie'ler silinir
```

## Şifrelenmiş Cookie'ler

### setEncrypted($name, $value, $expire = 0, $options = [])
Şifrelenmiş cookie ayarlar.

```php
Cookie::setEncrypted('sensitive_data', [
    'user_id' => 123,
    'permissions' => ['read', 'write']
], 3600);
```

### getEncrypted($name, $default = null)
Şifrelenmiş cookie alır.

```php
$sensitiveData = Cookie::getEncrypted('sensitive_data');
```

## Remember Me Token

### setRememberToken($token, $days = 30)
Remember me token ayarlar.

```php
$token = bin2hex(random_bytes(32));
Cookie::setRememberToken($token, 30); // 30 gün
```

### getRememberToken()
Remember me token alır.

```php
$token = Cookie::getRememberToken();
if ($token) {
    // Token ile kullanıcıyı doğrula
}
```

### deleteRememberToken()
Remember me token siler.

```php
Cookie::deleteRememberToken();
```

## Sword Framework Entegrasyonu

```php
// Kısa kullanım
Sword::cookie('theme', 'dark', 3600);
$theme = Sword::cookie('theme');
```

## Güvenlik Özellikleri

### Otomatik Özellikler
- **Prefix:** Tüm cookie'lere `sword_` öneki eklenir
- **Domain:** Otomatik domain tespiti
- **Secure:** HTTPS'te otomatik secure flag
- **HttpOnly:** XSS koruması için varsayılan aktif
- **SameSite:** CSRF koruması için Lax

### Manuel Güvenlik Ayarları

```php
Cookie::set('secure_cookie', $value, 3600, [
    'secure' => true,      // Sadece HTTPS
    'httponly' => true,    // JavaScript erişimi yok
    'samesite' => 'Strict' // Katı CSRF koruması
]);
```

## Kullanım Örnekleri

### Tema Tercihi

```php
// Tema kaydet
if (isset($_POST['theme'])) {
    Cookie::set('user_theme', $_POST['theme'], 86400 * 30); // 30 gün
}

// Tema al
$theme = Cookie::get('user_theme', 'default');
```

### Dil Tercihi

```php
// Dil kaydet
Cookie::set('language', 'tr', 86400 * 365); // 1 yıl

// Dil al
$lang = Cookie::get('language', 'en');
```

### Kullanıcı Oturumu

```php
// Login işlemi
if ($loginSuccess) {
    $sessionData = [
        'user_id' => $user->id,
        'login_time' => time()
    ];
    Cookie::setEncrypted('user_session', $sessionData, 3600);
    
    // Remember me
    if ($_POST['remember']) {
        $token = generateRememberToken();
        Cookie::setRememberToken($token, 30);
        saveTokenToDatabase($user->id, $token);
    }
}

// Oturum kontrol
$session = Cookie::getEncrypted('user_session');
if ($session && $session['user_id']) {
    $currentUser = getUserById($session['user_id']);
}

// Logout
Cookie::delete('user_session');
Cookie::deleteRememberToken();
```

### Alışveriş Sepeti

```php
// Sepete ürün ekle
$cart = Cookie::get('shopping_cart', []);
$cart[] = ['product_id' => 123, 'quantity' => 2];
Cookie::set('shopping_cart', $cart, 86400 * 7); // 1 hafta

// Sepeti göster
$cart = Cookie::get('shopping_cart', []);
foreach ($cart as $item) {
    echo "Ürün: " . $item['product_id'] . " Adet: " . $item['quantity'];
}
```