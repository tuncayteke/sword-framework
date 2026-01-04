# Request Class

HTTP isteklerini yönetir. GET, POST, PUT, DELETE, dosya yükleme ve AJAX isteklerini destekler.

## Temel Kullanım

```php
$request = new Request();

// Veya Sword üzerinden
$request = Sword::request();
```

## HTTP Metodları

### GET İstekleri

#### isGet()
İstek GET mi kontrol eder.

```php
if ($request->isGet()) {
    // GET isteği
}
```

#### get($key = null, $default = null)
GET verilerini alır.

```php
$search = $request->get('search');
$page = $request->get('page', 1);
$allGet = $request->get(); // Tüm GET verileri
```

#### gets()
Tüm GET verilerini döndürür.

```php
$getData = $request->gets();
```

#### hasGet($key)
GET verisi var mı kontrol eder.

```php
if ($request->hasGet('search')) {
    $search = $request->get('search');
}
```

### POST İstekleri

#### isPost()
İstek POST mu kontrol eder.

```php
if ($request->isPost()) {
    // POST isteği
}
```

#### post($key = null, $default = null)
POST verilerini alır.

```php
$username = $request->post('username');
$password = $request->post('password');
$allPost = $request->post(); // Tüm POST verileri
```

#### posts()
Tüm POST verilerini döndürür.

```php
$postData = $request->posts();
```

#### hasPost($key)
POST verisi var mı kontrol eder.

```php
if ($request->hasPost('username')) {
    $username = $request->post('username');
}
```

### PUT İstekleri

#### isPut()
İstek PUT mu kontrol eder.

```php
if ($request->isPut()) {
    // PUT isteği
}
```

#### put($key = null, $default = null)
PUT verilerini alır.

```php
$name = $request->put('name');
$email = $request->put('email');
$allPut = $request->put(); // Tüm PUT verileri
```

#### puts()
Tüm PUT verilerini döndürür.

#### hasPut($key)
PUT verisi var mı kontrol eder.

### DELETE İstekleri

#### isDelete()
İstek DELETE mi kontrol eder.

#### delete($key = null, $default = null)
DELETE verilerini alır.

#### deletes()
Tüm DELETE verilerini döndürür.

#### hasDelete($key)
DELETE verisi var mı kontrol eder.

## AJAX İstekleri

### isAjax()
İstek AJAX mi kontrol eder.

```php
if ($request->isAjax()) {
    return $this->json($data);
}
```

### isAjaxGet()
İstek AJAX GET mi kontrol eder.

### isAjaxPost()
İstek AJAX POST mu kontrol eder.

### ajax()
AJAX verilerini döndürür.

```php
$ajaxData = $request->ajax();
```

## Dosya İşlemleri

### hasFiles()
Dosya yüklendi mi kontrol eder.

```php
if ($request->hasFiles()) {
    // Dosya var
}
```

### hasFile($key)
Belirtilen dosya var mı kontrol eder.

```php
if ($request->hasFile('avatar')) {
    $file = $request->file('avatar');
}
```

### file($key = null)
Dosya verilerini alır.

```php
$avatar = $request->file('avatar');
$allFiles = $request->file(); // Tüm dosyalar

// Dosya bilgileri
echo $avatar['name'];     // Dosya adı
echo $avatar['type'];     // MIME türü
echo $avatar['size'];     // Dosya boyutu
echo $avatar['tmp_name']; // Geçici dosya yolu
echo $avatar['error'];    // Hata kodu
```

### files()
Tüm dosya verilerini döndürür.

```php
$fileData = $request->files();
```

## Genel Veri Erişimi

### input($key = null, $default = null)
Tüm input verilerini alır (GET, POST, PUT, DELETE).

```php
$username = $request->input('username');
$allInput = $request->input(); // Tüm veriler
```

## URI ve Segment İşlemleri

### segments()
URI segmentlerini döndürür.

```php
// URL: /admin/users/123/edit
$segments = $request->segments();
// ['admin', 'users', '123', 'edit']
```

### hasSegment($segment)
Belirtilen segment var mı kontrol eder.

```php
if ($request->hasSegment('admin')) {
    // Admin panelinde
}
```

## Header İşlemleri

### header()
Tüm header verilerini döndürür.

```php
$headers = $request->header();
```

### hasHeader($key)
Belirtilen header var mı kontrol eder.

```php
if ($request->hasHeader('Authorization')) {
    $token = $request->header()['Authorization'];
}
```

## Server Bilgileri

### server()
Tüm server verilerini döndürür.

```php
$serverData = $request->server();
```

### hasServer($key)
Belirtilen server verisi var mı kontrol eder.

```php
if ($request->hasServer('HTTP_HOST')) {
    $host = $_SERVER['HTTP_HOST'];
}
```

## Kullanıcı Bilgileri

### user($key = null)
Kullanıcı bilgilerini döndürür.

```php
$userInfo = $request->user();
// ['ip' => '127.0.0.1', 'agent' => '...', 'proxy' => [...]]

$ip = $request->user('ip');
```

### userAgent()
Kullanıcı agent bilgisini döndürür.

```php
$userAgent = $request->userAgent();
```

### userIp()
Kullanıcı IP adresini döndürür (proxy desteği ile).

```php
$ip = $request->userIp();
```

### userProxy()
Kullanıcı proxy bilgilerini döndürür.

```php
$proxyInfo = $request->userProxy();
```

## İstek Gövdesi

### getBody()
Ham istek gövdesini döndürür.

```php
$rawBody = $request->getBody();
$jsonData = json_decode($rawBody, true);
```

## Örnek Kullanımlar

### Form İşleme
```php
class ContactController extends Controller
{
    public function store()
    {
        if ($this->request->isPost()) {
            $name = $this->request->post('name');
            $email = $this->request->post('email');
            $message = $this->request->post('message');
            
            // İşlem...
        }
    }
}
```

### API Endpoint
```php
class ApiController extends Controller
{
    public function users()
    {
        if ($this->request->isGet()) {
            // Kullanıcıları listele
            $page = $this->request->get('page', 1);
            $limit = $this->request->get('limit', 10);
        }
        
        if ($this->request->isPost()) {
            // Yeni kullanıcı oluştur
            $data = $this->request->input();
        }
        
        if ($this->request->isPut()) {
            // Kullanıcı güncelle
            $data = $this->request->put();
        }
        
        if ($this->request->isDelete()) {
            // Kullanıcı sil
            $id = $this->request->delete('id');
        }
    }
}
```

### Dosya Yükleme
```php
class UploadController extends Controller
{
    public function avatar()
    {
        if ($this->request->hasFile('avatar')) {
            $file = $this->request->file('avatar');
            
            if ($file['error'] === UPLOAD_ERR_OK) {
                $uploadPath = '/uploads/' . $file['name'];
                move_uploaded_file($file['tmp_name'], $uploadPath);
            }
        }
    }
}
```

### AJAX İşleme
```php
class SearchController extends Controller
{
    public function index()
    {
        if ($this->request->isAjax()) {
            $query = $this->request->input('q');
            $results = $this->search($query);
            
            return $this->json($results);
        }
        
        // Normal sayfa
        $this->render('search/index');
    }
}
```