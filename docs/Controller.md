# Controller Class

MVC yapısının kontrolcü bileşeni. Tüm kontrolcüler bu sınıftan türetilmelidir.

## Temel Kullanım

```php
class HomeController extends Controller
{
    public function index()
    {
        $this->set('title', 'Ana Sayfa');
        $this->render('home/index');
    }
}
```

## Özellikler

### Otomatik Yüklenen Nesneler
- `$this->view` - View nesnesi
- `$this->request` - Request nesnesi  
- `$this->response` - Response nesnesi

### Yapılandırma Özellikleri
- `$layout` - Layout dosyası
- `$viewPath` - View dizini
- `$themeType` - Tema tipi (frontend/backend)

## View Metodları

### set($name, $value)
View'a değişken atar.

```php
$this->set('title', 'Sayfa Başlığı');
$this->set('users', $userList);
```

### setData($data)
Birden fazla değişkeni atar.

```php
$this->setData([
    'title' => 'Başlık',
    'users' => $users,
    'count' => 10
]);
```

### render($template, $data = null)
View işler ve ekrana basar.

```php
$this->render('user/profile');
$this->render('user/list', ['users' => $users]);
```

### renderView($template, $data = [])
View işler ve string döndürür.

```php
$html = $this->renderView('email/template', $data);
```

## Layout Metodları

### setLayout($layout)
Layout dosyasını ayarlar.

```php
$this->setLayout('admin');
$this->setLayout(null); // Layout kullanma
```

### setThemeLayout($layout)
Tema için public layout ayarlama.

```php
$this->setThemeLayout('custom-layout');
```

## Response Metodları

### json($data, $code = 200)
JSON yanıtı döndürür.

```php
return $this->json(['status' => 'success']);
return $this->json($users, 200);
```

### success($data = null)
Başarı yanıtı döndürür.

```php
return $this->success(['message' => 'İşlem başarılı']);
```

### error($message, $code = 400)
Hata yanıtı döndürür.

```php
return $this->error('Geçersiz veri', 400);
```

### redirect($url, $code = 302)
Yönlendirme yapar.

```php
return $this->redirect('/login');
return $this->redirect('user.profile');
```

## HTTP Durum Kodları

### notFound($message = 'Not Found')
404 yanıtı döndürür.

```php
return $this->notFound('Sayfa bulunamadı');
```

### unauthorized($message = 'Unauthorized')
401 yanıtı döndürür.

```php
return $this->unauthorized('Giriş gerekli');
```

### forbidden($message = 'Forbidden')
403 yanıtı döndürür.

```php
return $this->forbidden('Yetki yok');
```

### serverError($message = 'Internal Server Error')
500 yanıtı döndürür.

```php
return $this->serverError('Sunucu hatası');
```

## Lifecycle Metodları

### initialize()
Başlangıç işlemleri. Alt sınıflar tarafından geçersiz kılınabilir.

```php
protected function initialize()
{
    // Kontrolcü başlatıldığında çalışır
    $this->setLayout('admin');
}
```

## Örnek Kontrolcü

```php
class UserController extends Controller
{
    protected $layout = 'admin';
    
    protected function initialize()
    {
        // Auth kontrolü
        if (!Auth::check()) {
            $this->redirect('/login');
        }
    }
    
    public function index()
    {
        $users = User::all();
        
        $this->setData([
            'title' => 'Kullanıcılar',
            'users' => $users
        ]);
        
        $this->render('user/index');
    }
    
    public function show($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return $this->notFound('Kullanıcı bulunamadı');
        }
        
        return $this->json($user);
    }
    
    public function store()
    {
        $data = $this->request->post();
        
        $validation = Sword::validate($data, [
            'name' => 'required|min:3',
            'email' => 'required|email'
        ]);
        
        if (!$validation->passes()) {
            return $this->error('Doğrulama hatası', 422);
        }
        
        $user = User::create($data);
        
        return $this->success($user);
    }
}
```