# Session Class

Güvenli oturum yönetimi sağlar. Flash mesajlar, güvenlik ayarları ve kolay kullanım sunar.

## Temel Kullanım

```php
// Session başlat
Session::start();

// Değer ayarla
Session::set('user_id', 123);

// Değer al
$userId = Session::get('user_id');
```

## Session Başlatma

### start($options = [])
Session'ı başlatır.

```php
Session::start();

// Özel ayarlarla
Session::start([
    'name' => 'MY_SESSION',
    'lifetime' => 7200,
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
```

### Varsayılan Ayarlar
- `name` - Session adı (SWORD_SESSION)
- `lifetime` - Yaşam süresi (3600 saniye)
- `path` - Cookie yolu (/)
- `domain` - Cookie domain'i
- `secure` - HTTPS gerekli mi (otomatik tespit)
- `httponly` - JavaScript erişimi engelle (true)
- `samesite` - SameSite politikası (Lax)

## Veri İşlemleri

### set($key, $value)
Session değeri ayarlar.

```php
Session::set('username', 'john_doe');
Session::set('preferences', ['theme' => 'dark', 'lang' => 'tr']);
Session::set('cart', $cartItems);
```

### get($key, $default = null)
Session değeri alır.

```php
$username = Session::get('username');
$theme = Session::get('theme', 'light'); // Varsayılan değer
$cart = Session::get('cart', []);
```

### has($key)
Session değeri var mı kontrol eder.

```php
if (Session::has('user_id')) {
    $userId = Session::get('user_id');
}
```

### remove($key)
Session değerini siler.

```php
Session::remove('temp_data');
Session::remove('old_cart');
```

### all()
Tüm session verilerini döndürür.

```php
$allData = Session::all();
```

## Session Yönetimi

### clear()
Tüm session verilerini temizler.

```php
Session::clear();
```

### destroy()
Session'ı tamamen yok eder.

```php
Session::destroy();
```

### regenerate($deleteOldSession = true)
Session ID'sini yeniler.

```php
Session::regenerate(); // Güvenlik için
Session::regenerate(false); // Eski session'ı sakla
```

### getId()
Session ID'sini döndürür.

```php
$sessionId = Session::getId();
```

### isStarted()
Session başlatıldı mı kontrol eder.

```php
if (Session::isStarted()) {
    // Session aktif
}
```

## Flash Mesajlar

Flash mesajlar bir sonraki istekte kullanılır ve otomatik olarak silinir.

### flash($key, $value)
Flash mesaj ayarlar.

```php
Session::flash('success', 'İşlem başarılı!');
Session::flash('error', 'Bir hata oluştu!');
Session::flash('info', 'Bilgi mesajı');
Session::flash('warning', 'Uyarı mesajı');
```

### getFlash($key, $default = null)
Flash mesajı alır ve siler.

```php
$success = Session::getFlash('success');
$error = Session::getFlash('error', 'Bilinmeyen hata');
```

## Örnek Kullanımlar

### Kullanıcı Girişi
```php
class AuthController extends Controller
{
    public function login()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        
        if ($this->auth->attempt($username, $password)) {
            // Giriş başarılı
            Session::set('user_id', $user->id);
            Session::set('username', $user->username);
            Session::set('role', $user->role);
            
            // Güvenlik için session ID yenile
            Session::regenerate();
            
            Session::flash('success', 'Giriş başarılı!');
            return $this->redirect('/dashboard');
        } else {
            Session::flash('error', 'Kullanıcı adı veya şifre hatalı!');
            return $this->redirect('/login');
        }
    }
    
    public function logout()
    {
        Session::destroy();
        Session::flash('info', 'Çıkış yapıldı.');
        return $this->redirect('/');
    }
}
```

### Alışveriş Sepeti
```php
class CartController extends Controller
{
    public function add()
    {
        $productId = $this->request->post('product_id');
        $quantity = $this->request->post('quantity', 1);
        
        $cart = Session::get('cart', []);
        
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'added_at' => time()
            ];
        }
        
        Session::set('cart', $cart);
        Session::flash('success', 'Ürün sepete eklendi!');
        
        return $this->json(['status' => 'success', 'cart_count' => count($cart)]);
    }
    
    public function clear()
    {
        Session::remove('cart');
        Session::flash('info', 'Sepet temizlendi.');
        return $this->redirect('/cart');
    }
}
```

### Form Wizard
```php
class WizardController extends Controller
{
    public function step1()
    {
        if ($this->request->isPost()) {
            $step1Data = $this->request->post();
            Session::set('wizard_step1', $step1Data);
            return $this->redirect('/wizard/step2');
        }
        
        $data = Session::get('wizard_step1', []);
        $this->render('wizard/step1', ['data' => $data]);
    }
    
    public function step2()
    {
        if (!Session::has('wizard_step1')) {
            return $this->redirect('/wizard/step1');
        }
        
        if ($this->request->isPost()) {
            $step2Data = $this->request->post();
            Session::set('wizard_step2', $step2Data);
            return $this->redirect('/wizard/step3');
        }
        
        $data = Session::get('wizard_step2', []);
        $this->render('wizard/step2', ['data' => $data]);
    }
    
    public function complete()
    {
        $step1 = Session::get('wizard_step1');
        $step2 = Session::get('wizard_step2');
        $step3 = Session::get('wizard_step3');
        
        if (!$step1 || !$step2 || !$step3) {
            return $this->redirect('/wizard/step1');
        }
        
        // Tüm verileri birleştir ve işle
        $allData = array_merge($step1, $step2, $step3);
        $this->processWizardData($allData);
        
        // Wizard verilerini temizle
        Session::remove('wizard_step1');
        Session::remove('wizard_step2');
        Session::remove('wizard_step3');
        
        Session::flash('success', 'İşlem tamamlandı!');
        return $this->redirect('/dashboard');
    }
}
```

### Kullanıcı Tercihleri
```php
class PreferencesController extends Controller
{
    public function update()
    {
        $preferences = $this->request->post('preferences');
        
        // Mevcut tercihleri al
        $currentPrefs = Session::get('user_preferences', []);
        
        // Yeni tercihlerle birleştir
        $newPrefs = array_merge($currentPrefs, $preferences);
        
        // Session'a kaydet
        Session::set('user_preferences', $newPrefs);
        
        // Veritabanına da kaydet (opsiyonel)
        if (Session::has('user_id')) {
            $userId = Session::get('user_id');
            User::updatePreferences($userId, $newPrefs);
        }
        
        Session::flash('success', 'Tercihler güncellendi!');
        return $this->redirect('/preferences');
    }
}
```

### Güvenlik Kontrolü
```php
class SecurityMiddleware
{
    public function handle()
    {
        // Session hijacking koruması
        if (Session::has('user_ip')) {
            $currentIp = $_SERVER['REMOTE_ADDR'];
            $sessionIp = Session::get('user_ip');
            
            if ($currentIp !== $sessionIp) {
                Session::destroy();
                return redirect('/login?reason=security');
            }
        }
        
        // Session timeout kontrolü
        if (Session::has('last_activity')) {
            $lastActivity = Session::get('last_activity');
            $timeout = 1800; // 30 dakika
            
            if (time() - $lastActivity > $timeout) {
                Session::destroy();
                return redirect('/login?reason=timeout');
            }
        }
        
        // Son aktiviteyi güncelle
        Session::set('last_activity', time());
        
        return true;
    }
}
```

## Güvenlik İpuçları

1. **Session ID Yenileme**: Giriş sonrası `regenerate()` kullanın
2. **HTTPS**: Üretimde `secure: true` ayarlayın
3. **HttpOnly**: JavaScript erişimini engelleyin
4. **SameSite**: CSRF koruması için `Strict` kullanın
5. **Timeout**: Uzun süre inaktif session'ları temizleyin
6. **IP Kontrolü**: Session hijacking koruması ekleyin