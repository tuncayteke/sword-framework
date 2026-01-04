# Security Class

Güvenlik işlemlerini yönetir. CSRF koruması, XSS temizleme, SQL injection koruması ve şifre güvenliği sunar.

## Temel Kullanım

```php
// CSRF token oluştur
$token = Security::getCsrfToken();

// XSS temizleme
$cleanData = Security::xssClean($_POST['content']);

// Şifre hash'leme
$hash = Security::hashPassword('mypassword');
```

## CSRF Koruması

### getCsrfToken($new = false)
CSRF token'ı oluşturur veya mevcut token'ı döndürür.

```php
$token = Security::getCsrfToken();
$newToken = Security::getCsrfToken(true); // Yeni token zorla
```

### validateCsrfToken($token = null)
CSRF token'ını doğrular.

```php
// Otomatik POST/GET'ten al
if (Security::validateCsrfToken()) {
    // Token geçerli
}

// Manuel token kontrolü
if (Security::validateCsrfToken($_POST['csrf_token'])) {
    // Token geçerli
}
```

### csrfField()
HTML form için gizli input elementi oluşturur.

```php
<form method="POST">
    <?= Security::csrfField() ?>
    <input type="text" name="username">
    <button type="submit">Gönder</button>
</form>
```

### csrfMeta()
Meta etiketi olarak CSRF token'ı oluşturur.

```php
<head>
    <?= Security::csrfMeta() ?>
</head>

<script>
// JavaScript'te kullanım
const token = document.querySelector('meta[name="sword_csrf_token"]').content;
</script>
```

## XSS Koruması

### xssClean($data, $allowHtml = false)
XSS saldırılarına karşı veriyi temizler.

```php
// Basit temizleme (HTML etiketleri kaldırılır)
$clean = Security::xssClean($_POST['comment']);

// HTML etiketlerine izin ver
$clean = Security::xssClean($_POST['content'], true);

// Dizi temizleme
$cleanData = Security::xssClean($_POST);
```

### İzin Verilen HTML Etiketleri
Varsayılan olarak şu etiketlere izin verilir:
- `<p><a><b><i><strong><em><u>`
- `<h1><h2><h3><h4><h5><h6>`
- `<pre><code><ul><ol><li><br><hr>`

```php
// Özel etiket listesi ayarla
Security::setAllowedHtmlTags('<p><a><b><i><strong><em>');
```

## SQL Injection Koruması

### sqlEscape($data)
SQL enjeksiyonuna karşı veriyi temizler.

```php
$username = Security::sqlEscape($_POST['username']);
$email = Security::sqlEscape($_POST['email']);

// Dizi temizleme
$cleanData = Security::sqlEscape($_POST);
```

**Not:** Modern ORM kullanırken bu metoda ihtiyaç duymazsınız. Prepared statements kullanın.

## Şifre Güvenliği

### hashPassword($password)
Güvenli şifre hash'i oluşturur.

```php
$hash = Security::hashPassword('mypassword123');
// $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
```

### verifyPassword($password, $hash)
Şifre hash'ini doğrular.

```php
$password = 'mypassword123';
$hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

if (Security::verifyPassword($password, $hash)) {
    // Şifre doğru
}
```

## Token Oluşturma

### generateToken($length = 32)
Güvenli rastgele token oluşturur.

```php
$token = Security::generateToken(); // 32 karakter
$shortToken = Security::generateToken(16); // 16 karakter

// API key oluşturma
$apiKey = Security::generateToken(64);
```

## Yapılandırma

### setCsrfExpire($seconds)
CSRF token'ının geçerlilik süresini ayarlar.

```php
Security::setCsrfExpire(3600); // 1 saat
Security::setCsrfExpire(7200); // 2 saat (varsayılan)
```

### setCsrfTokenName($name)
CSRF token adını ayarlar.

```php
Security::setCsrfTokenName('my_csrf_token');
```

### setAllowedHtmlTags($tags)
XSS temizleme için izin verilen HTML etiketlerini ayarlar.

```php
Security::setAllowedHtmlTags('<p><a><b><i><strong><em><ul><ol><li>');
```

## Örnek Kullanımlar

### Form İşleme
```php
class ContactController extends Controller
{
    public function store()
    {
        // CSRF kontrolü
        if (!Security::validateCsrfToken()) {
            return $this->error('Güvenlik hatası', 403);
        }
        
        // Veriyi temizle
        $data = Security::xssClean($this->request->post());
        
        // İşlem...
        Contact::create($data);
        
        return $this->success('Mesaj gönderildi');
    }
}
```

### Kullanıcı Kaydı
```php
class RegisterController extends Controller
{
    public function store()
    {
        // CSRF kontrolü
        if (!Security::validateCsrfToken()) {
            Session::flash('error', 'Güvenlik hatası');
            return $this->redirect('/register');
        }
        
        $data = $this->request->post();
        
        // Veriyi temizle
        $data = Security::xssClean($data);
        
        // Şifreyi hash'le
        $data['password'] = Security::hashPassword($data['password']);
        
        // Kullanıcı oluştur
        $user = User::create($data);
        
        return $this->redirect('/login');
    }
}
```

### Login İşlemi
```php
class AuthController extends Controller
{
    public function login()
    {
        if (!Security::validateCsrfToken()) {
            return $this->error('Güvenlik hatası', 403);
        }
        
        $email = Security::xssClean($this->request->post('email'));
        $password = $this->request->post('password');
        
        $user = User::where('email', $email)->first();
        
        if ($user && Security::verifyPassword($password, $user->password)) {
            // Giriş başarılı
            Session::set('user_id', $user->id);
            return $this->redirect('/dashboard');
        }
        
        return $this->error('Geçersiz bilgiler', 401);
    }
}
```

### API Endpoint
```php
class ApiController extends Controller
{
    public function store()
    {
        // API için CSRF kontrolü (header'dan)
        $token = $this->request->header()['X-CSRF-Token'] ?? null;
        
        if (!Security::validateCsrfToken($token)) {
            return $this->response->error('CSRF token geçersiz', 403);
        }
        
        // JSON veriyi temizle
        $data = Security::xssClean($this->request->input());
        
        // İşlem...
        $result = Model::create($data);
        
        return $this->response->success($result);
    }
}
```

### Middleware Kullanımı
```php
class CsrfMiddleware
{
    public function handle()
    {
        // POST, PUT, DELETE istekleri için CSRF kontrolü
        $method = $_SERVER['REQUEST_METHOD'];
        
        if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
            if (!Security::validateCsrfToken()) {
                // AJAX isteği ise JSON yanıt
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'CSRF token geçersiz']);
                    exit;
                }
                
                // Normal istek ise yönlendir
                Session::flash('error', 'Güvenlik hatası');
                header('Location: /');
                exit;
            }
        }
        
        return true;
    }
}
```

### Form Helper
```php
function csrf_field()
{
    return Security::csrfField();
}

function csrf_token()
{
    return Security::getCsrfToken();
}

// View'da kullanım
<form method="POST">
    <?= csrf_field() ?>
    <!-- form alanları -->
</form>
```

### AJAX İstekleri
```javascript
// Meta tag'den token al
const token = document.querySelector('meta[name="sword_csrf_token"]').content;

// Fetch ile kullanım
fetch('/api/data', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': token
    },
    body: JSON.stringify(data)
});

// jQuery ile kullanım
$.ajaxSetup({
    headers: {
        'X-CSRF-Token': token
    }
});
```

### Veri Temizleme Yardımcıları
```php
class SecurityHelper
{
    public static function cleanInput($data, $allowHtml = false)
    {
        // XSS temizleme
        $data = Security::xssClean($data, $allowHtml);
        
        // Boşlukları temizle
        if (is_string($data)) {
            $data = trim($data);
        }
        
        return $data;
    }
    
    public static function sanitizeFilename($filename)
    {
        // Dosya adını güvenli hale getir
        $filename = Security::xssClean($filename);
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        return $filename;
    }
}
```

### Password Policy
```php
class PasswordPolicy
{
    public static function validate($password)
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Şifre en az 8 karakter olmalıdır';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Şifre en az bir büyük harf içermelidir';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Şifre en az bir küçük harf içermelidir';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Şifre en az bir rakam içermelidir';
        }
        
        return empty($errors) ? true : $errors;
    }
    
    public static function hash($password)
    {
        $validation = self::validate($password);
        
        if ($validation !== true) {
            throw new Exception('Şifre güvenlik kurallarını karşılamıyor');
        }
        
        return Security::hashPassword($password);
    }
}
```

## Güvenlik İpuçları

1. **CSRF**: Tüm form işlemlerinde CSRF koruması kullanın
2. **XSS**: Kullanıcı girdilerini her zaman temizleyin
3. **Şifreler**: Asla düz metin şifre saklamayın
4. **Token'lar**: API key'ler için güvenli token oluşturun
5. **Validation**: Güvenlik kontrollerini validation ile birleştirin