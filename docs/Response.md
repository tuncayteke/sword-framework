# Response Class

HTTP yanıtlarını yönetir. JSON, HTML, yönlendirme, dosya indirme ve API yanıtları için gelişmiş özellikler sunar.

## Temel Kullanım

```php
$response = new Response();

// Veya factory metodu ile
$response = Response::make();
```

## HTTP Durum Kodları

### status($code)
HTTP durum kodunu ayarlar.

```php
$response->status(200); // OK
$response->status(404); // Not Found
$response->status(500); // Internal Server Error
```

## Header İşlemleri

### header($name, $value)
HTTP başlığı ekler.

```php
$response->header('Content-Type', 'application/json');
$response->header('Cache-Control', 'no-cache');
```

### headers($headers)
Birden çok HTTP başlığı ekler.

```php
$response->headers([
    'Content-Type' => 'application/json',
    'Cache-Control' => 'no-cache',
    'X-Custom-Header' => 'value'
]);
```

### contentType($type)
İçerik türünü ayarlar (otomatik charset ile).

```php
$response->contentType('application/json');
$response->contentType('text/html');
$response->contentType('text/plain');
```

## İçerik İşlemleri

### content($content)
Yanıt içeriğini ayarlar.

```php
$response->content('<h1>Merhaba Dünya</h1>');
$response->content('Düz metin içerik');
```

## Çerez İşlemleri

### cookie($name, $value = '', $options = [])
Çerez ekler.

```php
$response->cookie('theme', 'dark');
$response->cookie('user_id', '123', [
    'expires' => time() + 3600,
    'path' => '/',
    'secure' => true,
    'httponly' => true
]);
```

## Yönlendirme

### redirect($url, $code = 302)
Yönlendirme yapar (named route destekli).

```php
$response->redirect('/login');
$response->redirect('user.profile'); // Named route
$response->redirect('https://example.com');
```

### back($fallback = '/', $code = 302)
Önceki sayfaya yönlendirir.

```php
$response->back(); // Önceki sayfa
$response->back('/home'); // Fallback URL
$response->back('home'); // Named route fallback
```

## JSON Yanıtları

### json($data, $code = 200)
JSON yanıtı oluşturur.

```php
$response->json(['status' => 'success']);
$response->json($users, 200);
$response->json(['error' => 'Not found'], 404);
```

### jsonp($data, $callback = 'callback', $code = 200)
JSONP yanıtı oluşturur.

```php
$response->jsonp($data, 'myCallback');
```

## HTML ve Metin Yanıtları

### html($html, $code = 200)
HTML yanıtı oluşturur.

```php
$response->html('<h1>Başlık</h1><p>İçerik</p>');
```

### text($text, $code = 200)
Düz metin yanıtı oluşturur.

```php
$response->text('Düz metin içerik');
```

## Dosya İndirme

### download($file, $name = null, $mime = null)
Dosya indirme yanıtı (stream destekli).

```php
$response->download('/path/to/file.pdf');
$response->download('/path/to/file.pdf', 'document.pdf');
$response->download('/path/to/file.pdf', 'document.pdf', 'application/pdf');
```

## Flash Data

### with($key, $value = null)
Flash data ekler (Laravel tarzı).

```php
$response->with('success', 'İşlem başarılı');
$response->with([
    'success' => 'İşlem başarılı',
    'user' => $user
]);
```

## API Yardımcıları

### success($data = null, $message = null)
Başarı yanıtı (200).

```php
$response->success($users);
$response->success($user, 'Kullanıcı bulundu');
```

### created($data = null, $location = null)
Oluşturuldu yanıtı (201).

```php
$response->created($newUser);
$response->created($newUser, '/api/users/123');
```

### noContent()
İçerik yok yanıtı (204).

```php
$response->noContent();
```

### error($message, $code = 400, $data = null)
Hata yanıtı.

```php
$response->error('Geçersiz veri', 400);
$response->error('Sunucu hatası', 500, $errorDetails);
```

### notFound($message = 'Not Found')
404 Not Found yanıtı.

```php
$response->notFound();
$response->notFound('Kullanıcı bulunamadı');
```

### unauthorized($message = 'Unauthorized')
401 Unauthorized yanıtı.

```php
$response->unauthorized();
$response->unauthorized('Giriş gerekli');
```

### forbidden($message = 'Forbidden')
403 Forbidden yanıtı.

```php
$response->forbidden();
$response->forbidden('Bu işlem için yetkiniz yok');
```

### validationError($errors, $message = 'Validation failed')
Validation hata yanıtı (422).

```php
$response->validationError([
    'email' => 'E-posta geçersiz',
    'password' => 'Şifre çok kısa'
]);
```

## Sayfalama

### paginate($items, $total, $page = 1, $perPage = 15)
Sayfalama yanıtı.

```php
$response->paginate($users, 150, 2, 10);
```

Çıktı:
```json
{
    "data": [...],
    "pagination": {
        "current_page": 2,
        "per_page": 10,
        "total": 150,
        "last_page": 15,
        "from": 11,
        "to": 20
    }
}
```

## Universal API Yanıtı

### api($data = null, $message = null, $success = true, $code = 200)
Universal API yanıtı.

```php
$response->api($users, 'Kullanıcılar bulundu', true, 200);
$response->api(null, 'Hata oluştu', false, 400);
```

### data($data, $code = 200)
Sadece data yanıtı.

```php
$response->data($users);
```

### message($message, $code = 200)
Sadece mesaj yanıtı.

```php
$response->message('İşlem başarılı');
```

## Güvenlik Özellikleri

### autoEscape($enabled = true)
Otomatik escape'i ayarlar.

```php
$response->autoEscape(true);  // Açık (varsayılan)
$response->autoEscape(false); // Kapalı
```

### withoutEscape()
Escape'i kapatır.

```php
$response->withoutEscape();
```

### withDecorators($decorators)
Özel dekoratör zinciri ayarlar.

```php
$response->withDecorators(['escape', 'trim']);
```

## Yanıt Gönderme

### send()
Yanıtı gönderir.

```php
$response->json($data)->send();
$response->redirect('/home')->send();
```

## Zincirleme Kullanım

Response sınıfı zincirleme kullanımı destekler:

```php
return Response::make()
    ->status(201)
    ->header('Location', '/api/users/123')
    ->json($newUser)
    ->send();
```

## Örnek Kullanımlar

### API Controller
```php
class ApiController extends Controller
{
    public function users()
    {
        $users = User::all();
        
        return $this->response
            ->success($users, 'Kullanıcılar listelendi')
            ->send();
    }
    
    public function store()
    {
        $validation = $this->validate($this->request->input());
        
        if ($validation->fails()) {
            return $this->response
                ->validationError($validation->getErrors())
                ->send();
        }
        
        $user = User::create($validation->getValidData());
        
        return $this->response
            ->created($user, "/api/users/{$user->id}")
            ->send();
    }
}
```

### Web Controller
```php
class UserController extends Controller
{
    public function store()
    {
        $user = User::create($this->request->post());
        
        return $this->response
            ->with('success', 'Kullanıcı oluşturuldu')
            ->redirect('user.index')
            ->send();
    }
    
    public function export()
    {
        $file = $this->generateUserReport();
        
        return $this->response
            ->download($file, 'users-report.pdf')
            ->send();
    }
}
```

### AJAX Endpoint
```php
class SearchController extends Controller
{
    public function search()
    {
        if (!$this->request->isAjax()) {
            return $this->response->forbidden()->send();
        }
        
        $query = $this->request->get('q');
        $results = $this->searchService->search($query);
        
        return $this->response
            ->json($results)
            ->header('Cache-Control', 'no-cache')
            ->send();
    }
}
```