# Lang Class

Çok dilli destek sistemi. Dil dosyaları yönetimi, parametre desteği ve kolay kullanım sunar.

## Temel Kullanım

```php
// Dil sistemi başlat
Lang::init();

// Çeviri al
$message = Lang::get('welcome');
$error = Lang::get('user.not_found');

// Veya global fonksiyon ile
$message = __('welcome');
$error = __('user.not_found');
```

## Sistem Başlatma

### init()
Dil sistemini başlatır.

```php
Lang::init();
```

Otomatik olarak:
- Core dil dizinini ayarlar (`/app/langs`)
- Yapılandırmadan aktif dili alır
- Sistem hazır hale gelir

## Dil Yönetimi

### setLanguage($lang)
Aktif dili ayarlar.

```php
Lang::setLanguage('en');
Lang::setLanguage('tr');
Lang::setLanguage('de');
```

### getLanguage()
Aktif dili döndürür.

```php
$currentLang = Lang::getLanguage(); // 'tr'
```

## Dizin Yönetimi

### addDirectory($key, $path)
Dil dizini ekler.

```php
Lang::addDirectory('theme', '/themes/default/langs');
Lang::addDirectory('plugin', '/plugins/gallery/langs');
Lang::addDirectory('admin', '/admin/langs');
```

### getDirectory($key)
Belirli dizini döndürür.

```php
$themeLangPath = Lang::getDirectory('theme');
```

### getDirectories()
Tüm dizinleri döndürür.

```php
$directories = Lang::getDirectories();
```

## Çeviri Alma

### get($key, $params = [], $directory = 'core')
Çeviri döndürür.

```php
// Basit çeviri
$welcome = Lang::get('welcome');

// Parametreli çeviri
$hello = Lang::get('hello_user', ['name' => 'John']);

// Farklı dizinden
$themeText = Lang::get('button.save', [], 'theme');
```

### Nested Key Desteği
```php
// user.not_found -> $lang['user']['not_found']
$error = Lang::get('user.not_found');

// validation.email.required -> $lang['validation']['email']['required']
$validation = Lang::get('validation.email.required');
```

## Global Fonksiyon

### __($key, $params = [], $directory = 'core')
Kısa çeviri fonksiyonu.

```php
echo __('welcome');
echo __('hello_user', ['name' => $user->name]);
echo __('theme.title', [], 'theme');
```

## Dil Dosyası Formatı

### Temel Format
```php
// /app/langs/tr.php
<?php
return [
    'welcome' => 'Hoş geldiniz!',
    'goodbye' => 'Güle güle!',
    'hello_user' => 'Merhaba :name!',
    
    'user' => [
        'not_found' => 'Kullanıcı bulunamadı',
        'created' => 'Kullanıcı oluşturuldu',
        'updated' => 'Kullanıcı güncellendi'
    ],
    
    'validation' => [
        'required' => ':field alanı gereklidir',
        'email' => ':field geçerli bir e-posta olmalıdır',
        'min' => ':field en az :min karakter olmalıdır'
    ]
];
```

### İngilizce Dosya
```php
// /app/langs/en.php
<?php
return [
    'welcome' => 'Welcome!',
    'goodbye' => 'Goodbye!',
    'hello_user' => 'Hello :name!',
    
    'user' => [
        'not_found' => 'User not found',
        'created' => 'User created',
        'updated' => 'User updated'
    ],
    
    'validation' => [
        'required' => 'The :field field is required',
        'email' => 'The :field must be a valid email',
        'min' => 'The :field must be at least :min characters'
    ]
];
```

## Örnek Kullanımlar

### Controller'da Kullanım
```php
class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        
        $this->set('title', __('user.list_title'));
        $this->set('users', $users);
        $this->set('empty_message', __('user.no_users_found'));
        
        $this->render('user/index');
    }
    
    public function store()
    {
        $user = User::create($this->request->post());
        
        if ($user) {
            Session::flash('success', __('user.created_successfully'));
        } else {
            Session::flash('error', __('user.creation_failed'));
        }
        
        return $this->redirect('/users');
    }
    
    public function show($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            Session::flash('error', __('user.not_found'));
            return $this->redirect('/users');
        }
        
        $this->set('title', __('user.profile_title', ['name' => $user->name]));
        $this->set('user', $user);
        
        $this->render('user/show');
    }
}
```

### View'da Kullanım
```php
<!-- user/index.php -->
<h1><?= __('user.list_title') ?></h1>

<?php if (empty($users)): ?>
    <p><?= __('user.no_users_found') ?></p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th><?= __('user.name') ?></th>
                <th><?= __('user.email') ?></th>
                <th><?= __('user.created_at') ?></th>
                <th><?= __('common.actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user->name ?></td>
                <td><?= $user->email ?></td>
                <td><?= $user->created_at ?></td>
                <td>
                    <a href="/users/<?= $user->id ?>"><?= __('common.view') ?></a>
                    <a href="/users/<?= $user->id ?>/edit"><?= __('common.edit') ?></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<a href="/users/create" class="btn"><?= __('user.create_new') ?></a>
```

### Validation Mesajları
```php
class ContactController extends Controller
{
    public function store()
    {
        $validation = Validation::make($this->request->post());
        
        // Validation kuralları
        $validation->rule('name', __('contact.name'), 'required|min:2')
                  ->rule('email', __('contact.email'), 'required|email')
                  ->rule('message', __('contact.message'), 'required|min:10');
        
        // Özel mesajlar
        $validation->setMessage('required', __('validation.required'))
                  ->setMessage('email', __('validation.email'))
                  ->setMessage('min', __('validation.min'));
        
        if ($validation->fails()) {
            Session::flash('error', __('contact.validation_failed'));
            return $this->redirect('/contact');
        }
        
        // İşlem...
        Session::flash('success', __('contact.message_sent'));
        return $this->redirect('/contact');
    }
}
```

### Çok Dilli Rotalar
```php
class LanguageController extends Controller
{
    public function switch($lang)
    {
        // Desteklenen diller
        $supportedLangs = ['tr', 'en', 'de'];
        
        if (in_array($lang, $supportedLangs)) {
            Lang::setLanguage($lang);
            Session::set('language', $lang);
            
            Session::flash('success', __('language.changed_successfully'));
        } else {
            Session::flash('error', __('language.not_supported'));
        }
        
        return $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}
```

### API Yanıtları
```php
class ApiController extends Controller
{
    public function users()
    {
        $users = User::all();
        
        return $this->json([
            'success' => true,
            'message' => __('api.users_retrieved'),
            'data' => $users
        ]);
    }
    
    public function store()
    {
        $validation = $this->validate($this->request->input());
        
        if ($validation->fails()) {
            return $this->json([
                'success' => false,
                'message' => __('api.validation_failed'),
                'errors' => $validation->getErrors()
            ], 422);
        }
        
        $user = User::create($validation->getValidData());
        
        return $this->json([
            'success' => true,
            'message' => __('api.user_created'),
            'data' => $user
        ], 201);
    }
}
```

### Tema Dil Desteği
```php
// Tema dil dizini ekle
Lang::addDirectory('theme', THEME_PATH . '/langs');

// Tema fonksiyonlarında
function theme_text($key, $params = []) {
    return __($key, $params, 'theme');
}

// Tema dosyalarında
<h1><?= theme_text('homepage.welcome') ?></h1>
<p><?= theme_text('homepage.description') ?></p>
```

### Plugin Dil Desteği
```php
// Plugin sınıfında
class GalleryPlugin
{
    public function __construct()
    {
        Lang::addDirectory('gallery', __DIR__ . '/langs');
    }
    
    public function render()
    {
        return '<h3>' . __('gallery.title', [], 'gallery') . '</h3>';
    }
}
```

### E-posta Şablonları
```php
class MailService
{
    public function sendWelcome($user)
    {
        $subject = __('email.welcome.subject', ['name' => $user->name]);
        $body = __('email.welcome.body', [
            'name' => $user->name,
            'site_name' => config('app.name')
        ]);
        
        return Mail::send($user->email, $subject, $body);
    }
    
    public function sendPasswordReset($user, $token)
    {
        $subject = __('email.password_reset.subject');
        $body = __('email.password_reset.body', [
            'name' => $user->name,
            'reset_link' => url('/password/reset/' . $token)
        ]);
        
        return Mail::send($user->email, $subject, $body);
    }
}
```

## Gelişmiş Özellikler

### Dinamik Dil Yükleme
```php
class LanguageMiddleware
{
    public function handle()
    {
        // URL'den dil tespit et
        $segments = explode('/', $_SERVER['REQUEST_URI']);
        $langCode = $segments[1] ?? 'tr';
        
        if (in_array($langCode, ['tr', 'en', 'de'])) {
            Lang::setLanguage($langCode);
        }
        
        // Kullanıcı tercihinden dil al
        if (Session::has('language')) {
            Lang::setLanguage(Session::get('language'));
        }
        
        return true;
    }
}
```

### Fallback Dil Sistemi
```php
// Lang sınıfına ekleme
private static function loadTranslation($key, $directory) {
    $cacheKey = $directory . '.' . self::$currentLang;
    
    if (!isset(self::$translations[$cacheKey])) {
        self::loadDirectory($directory);
        
        // Fallback dil yükle (İngilizce)
        if (self::$currentLang !== 'en') {
            $fallbackKey = $directory . '.en';
            if (!isset(self::$translations[$fallbackKey])) {
                self::loadDirectoryForLang($directory, 'en');
            }
        }
    }
    
    // Çeviriyi bul
    $translation = self::findTranslation($key, $cacheKey);
    
    // Bulunamazsa fallback'e bak
    if ($translation === $key && self::$currentLang !== 'en') {
        $fallbackKey = $directory . '.en';
        $translation = self::findTranslation($key, $fallbackKey);
    }
    
    return $translation;
}
```

## İpuçları

1. **Organizasyon**: Dil dosyalarını kategorilere ayırın
2. **Parametreler**: Dinamik içerik için parametre kullanın
3. **Fallback**: Ana dil için fallback sistemi kurun
4. **Cache**: Büyük projelerde çeviri cache'i kullanın
5. **Validation**: Tüm çevirilerin varlığını kontrol edin