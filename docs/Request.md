# Request - Sword Framework

**Basit ve etkili HTTP request yönetimi**  
Keskin. Hızlı. Ölümsüz.

## Özellikler

- ✅ **HTTP Method Detection** - GET, POST, PUT, DELETE
- ✅ **Input Handling** - get(), post(), json(), input()
- ✅ **File Upload** - hasFile(), file()
- ✅ **Headers** - header(), bearerToken()
- ✅ **URL/Path** - url(), path(), segments()
- ✅ **Security** - isSecure(), ip()
- ✅ **Content Type** - isJson(), isAjax(), wantsJson()
- ✅ **Utility** - has(), only(), except()

## Temel Kullanım

### Request Instance

```php
$request = new Request();

// Sword Framework'de
$request = Sword::request();
```

### Input Alma

```php
// Belirli input
$name = $request->input('name');

// Varsayılan değer ile
$name = $request->input('name', 'Misafir');

// Tüm input
$all = $request->input();
$all = $request->all();
```

## HTTP Method'ları

### Method Kontrolü

```php
if ($request->isGet()) {
    // GET request
}

if ($request->isPost()) {
    // POST request
}

if ($request->isPut()) {
    // PUT request
}

if ($request->isDelete()) {
    // DELETE request
}

// Method adını al
$method = $request->method(); // GET, POST, PUT, DELETE
```

### Method Spoofing

```html
<!-- HTML Form -->
<form method="POST" action="/users/123">
    <input type="hidden" name="_method" value="PUT">
    <input type="text" name="name" value="John">
    <button type="submit">Güncelle</button>
</form>
```

```php
// PUT olarak algılanır
if ($request->isPut()) {
    $name = $request->input('name');
    // Güncelleme işlemi
}
```

### Method-Specific Data

```php
// GET verisi
$search = $request->get('search');
$page = $request->get('page', 1);

// POST verisi
$username = $request->post('username');
$password = $request->post('password');

// JSON verisi
$data = $request->json('data');
$user = $request->json('user');
```

## Input İşlemleri

### Temel Input

```php
// Tek değer
$email = $request->input('email');

// Varsayılan değer
$role = $request->input('role', 'user');

// Tüm input (GET + POST + JSON)
$all = $request->input();
```

### Seçici Input

```php
// Sadece belirli alanlar
$credentials = $request->only('email', 'password');
// ['email' => '...', 'password' => '...']

// Belirli alanlar hariç
$data = $request->except('_token', 'password_confirmation');
```

### Varlık Kontrolü

```php
// Input var mı ve boş değil mi?
if ($request->has('email')) {
    $email = $request->input('email');
}

// Birden fazla kontrol
if ($request->has('name') && $request->has('email')) {
    // Her ikisi de mevcut
}
```

## File Upload

### Dosya Kontrolü

```php
if ($request->hasFile('avatar')) {
    // Dosya yüklendi
    $file = $request->file('avatar');
}
```

### Dosya Bilgileri

```php
$file = $request->file('document');

if ($file) {
    // Dosya bilgileri (basit array)
    $name = $file['name'];        // Orijinal dosya adı
    $tmpName = $file['tmp_name']; // Geçici dosya yolu
    $size = $file['size'];        // Dosya boyutu
    $type = $file['type'];        // MIME type
    $error = $file['error'];      // Upload hatası
}
```

### Dosya Taşıma

```php
$file = $request->file('image');

if ($file && $file['error'] === UPLOAD_ERR_OK) {
    $destination = 'uploads/' . uniqid() . '_' . $file['name'];
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        echo "Dosya başarıyla yüklendi";
    }
}
```

### Dosya Doğrulama Örneği

```php
$file = $request->file('document');

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    die('Dosya yükleme hatası');
}

// Boyut kontrolü (max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    die('Dosya çok büyük');
}

// Uzantı kontrolü
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

if (!in_array(strtolower($extension), $allowedExtensions)) {
    die('Geçersiz dosya türü');
}

// Güvenli dosya adı oluştur
$safeName = uniqid() . '.' . $extension;
$destination = 'uploads/' . $safeName;

move_uploaded_file($file['tmp_name'], $destination);
```

## Headers

### Header Alma

```php
// Belirli header
$userAgent = $request->header('user-agent');
$contentType = $request->header('content-type');

// Varsayılan değer ile
$custom = $request->header('x-custom-header', 'varsayılan');

// Tüm header'lar
$headers = $request->header();
```

### Header Kontrolü

```php
if ($request->hasHeader('authorization')) {
    $auth = $request->header('authorization');
}
```

### Bearer Token

```php
// Authorization: Bearer abc123xyz
$token = $request->bearerToken();

if ($token) {
    // Token doğrula
    $user = Auth::validateToken($token);
}
```

## JSON İstekleri

### JSON Kontrolü

```php
if ($request->isJson()) {
    // Content-Type: application/json
}

if ($request->wantsJson()) {
    // Accept: application/json
}
```

### JSON Verisi Alma

```php
// Tüm JSON verisi
$data = $request->json();

// Belirli alan
$name = $request->json('name');
$email = $request->json('email');

// Varsayılan değer ile
$role = $request->json('role', 'user');
```

### JSON API Örneği

```javascript
// Frontend
fetch('/api/users', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        name: 'John Doe',
        email: 'john@example.com'
    })
});
```

```php
// Backend
$request = new Request();

if ($request->isJson()) {
    $name = $request->json('name');
    $email = $request->json('email');
    
    // Kullanıcı oluştur
    $user = User::create(['name' => $name, 'email' => $email]);
    
    // JSON response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'user' => $user]);
}
```

## AJAX/Fetch Kontrolü

```php
// XMLHttpRequest (jQuery, Axios)
if ($request->isAjax()) {
    echo json_encode($data);
}

// Fetch API veya JSON isteyen client
if ($request->wantsJson()) {
    header('Content-Type: application/json');
    echo json_encode($data);
} else {
    echo view('page', $data);
}
```

## URL ve Path Bilgileri

### URL Bilgileri

```php
// Mevcut URL (query string olmadan)
$url = $request->url();
// https://example.com/users/profile

// Tam URL (query string ile)
$fullUrl = $request->fullUrl();
// https://example.com/users/profile?tab=settings

// Path
$path = $request->path();
// /users/profile
```

### URI Segments

```php
// URL: /users/123/posts/456

$segments = $request->segments();
// ['users', '123', 'posts', '456']

$userId = $request->segment(1);    // '123'
$postId = $request->segment(3);    // '456'
$missing = $request->segment(10, 'default'); // 'default'
```

## Güvenlik

### HTTPS Kontrolü

```php
if ($request->isSecure()) {
    // HTTPS bağlantısı
}

// HTTPS'e yönlendir
if (!$request->isSecure()) {
    $httpsUrl = str_replace('http://', 'https://', $request->fullUrl());
    header("Location: $httpsUrl");
    exit;
}
```

### IP Adresi

```php
// Client IP adresi
$ip = $request->ip();

// Proxy'ler arkasında gerçek IP'yi alır
// X-Forwarded-For header'ını kontrol eder
```

### User Agent

```php
$userAgent = $request->userAgent();

if (strpos($userAgent, 'Mobile') !== false) {
    // Mobil cihaz
    echo view('mobile/page');
} else {
    // Desktop
    echo view('desktop/page');
}
```

## Server Bilgileri

```php
// Server değişkeni
$method = $request->server('REQUEST_METHOD');
$host = $request->server('HTTP_HOST');

// Tüm server değişkenleri
$server = $request->server();
```

## Pratik Örnekler

### Basit Form İşleme

```php
$request = new Request();

if ($request->isPost()) {
    $name = $request->post('name');
    $email = $request->post('email');
    
    if ($name && $email) {
        // Kullanıcı kaydet
        User::create(['name' => $name, 'email' => $email]);
        echo "Kayıt başarılı";
    } else {
        echo "Tüm alanları doldurun";
    }
}
```

### API Endpoint

```php
$request = new Request();

// GET /api/users?search=john
if ($request->isGet()) {
    $search = $request->get('search');
    $users = User::search($search)->get();
    
    header('Content-Type: application/json');
    echo json_encode($users);
}

// POST /api/users
if ($request->isPost()) {
    if ($request->isJson()) {
        $name = $request->json('name');
        $email = $request->json('email');
    } else {
        $name = $request->post('name');
        $email = $request->post('email');
    }
    
    $user = User::create(['name' => $name, 'email' => $email]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'user' => $user]);
}
```

### Dosya Upload API

```php
$request = new Request();

if ($request->hasFile('image')) {
    $file = $request->file('image');
    
    // Doğrulama
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Upload hatası']);
        exit;
    }
    
    if ($file['size'] > 2 * 1024 * 1024) { // 2MB
        http_response_code(400);
        echo json_encode(['error' => 'Dosya çok büyük']);
        exit;
    }
    
    // Kaydet
    $filename = uniqid() . '_' . $file['name'];
    $destination = 'uploads/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        echo json_encode(['success' => true, 'filename' => $filename]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Dosya kaydedilemedi']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Dosya bulunamadı']);
}
```

### Authentication Middleware

```php
function requireAuth($request) {
    $token = $request->bearerToken();
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Token gerekli']);
        exit;
    }
    
    $user = User::where('api_token', $token)->first();
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Geçersiz token']);
        exit;
    }
    
    return $user;
}

// Kullanım
$request = new Request();
$user = requireAuth($request);

// Artık authenticated user var
echo "Hoş geldin, " . $user->name;
```

## Best Practices

### 1. Input Doğrulama

```php
// Her zaman input'u doğrula
$email = $request->input('email');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Geçersiz email');
}

$age = $request->input('age');
if (!is_numeric($age) || $age < 18) {
    die('Geçersiz yaş');
}
```

### 2. XSS Koruması

```php
// HTML karakterleri escape et
$name = htmlspecialchars($request->input('name'), ENT_QUOTES, 'UTF-8');

// Veya helper function kullan
function clean($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

$name = clean($request->input('name'));
```

### 3. Dosya Upload Güvenliği

```php
$file = $request->file('document');

if ($file) {
    // Uzantı whitelist
    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    
    if (!in_array(strtolower($extension), $allowed)) {
        die('Geçersiz dosya türü');
    }
    
    // Güvenli dosya adı
    $safeName = uniqid() . '.' . $extension;
    
    // Upload dizini dışında çalıştırılabilir dosyalar
    $destination = 'uploads/' . $safeName;
    move_uploaded_file($file['tmp_name'], $destination);
}
```

### 4. Content Negotiation

```php
$data = ['users' => $users];

if ($request->wantsJson()) {
    header('Content-Type: application/json');
    echo json_encode($data);
} else {
    echo view('users/index', $data);
}
```

### 5. Method Spoofing

```php
// HTML formlarında PUT/DELETE için
if ($request->isPut()) {
    // Update işlemi
} elseif ($request->isDelete()) {
    // Delete işlemi
} elseif ($request->isPost()) {
    // Create işlemi
}
```

## Sword Framework Entegrasyonu

Request sınıfı Sword Framework'e entegre edilmiştir:

```php
// Controller'da
class UserController extends Controller
{
    public function store()
    {
        $request = $this->request; // Otomatik inject
        
        $name = $request->input('name');
        $email = $request->input('email');
        
        // Kullanıcı oluştur
        User::create(['name' => $name, 'email' => $email]);
        
        return $this->redirect('/users');
    }
}

// Veya global olarak
$request = Sword::request();
```

## Troubleshooting

### PUT/DELETE Çalışmıyor

```html
<!-- Method spoofing kullan -->
<form method="POST">
    <input type="hidden" name="_method" value="PUT">
</form>
```

### JSON Parse Edilmiyor

```php
// Content-Type kontrolü
if ($request->isJson()) {
    $data = $request->json();
} else {
    echo "JSON değil";
}
```

### Dosya Upload Hataları

```php
$file = $request->file('document');

if ($file['error'] !== UPLOAD_ERR_OK) {
    switch ($file['error']) {
        case UPLOAD_ERR_INI_SIZE:
            echo "Dosya çok büyük (php.ini)";
            break;
        case UPLOAD_ERR_FORM_SIZE:
            echo "Dosya çok büyük (form)";
            break;
        case UPLOAD_ERR_NO_FILE:
            echo "Dosya seçilmedi";
            break;
    }
}
```

---

**Sword Framework** - Keskin. Hızlı. Ölümsüz.