# View Class

Görünüm işlemlerini yönetir. Layout sistemi, section desteği ve shortcode işleme özellikleri sunar.

## Temel Kullanım

```php
// View oluştur
$view = new View('home/index', ['title' => 'Ana Sayfa']);

// Render et
echo $view->render();

// Veya doğrudan
echo $view;
```

## Yapılandırıcı

### __construct($view, $data = [], $layout = null)

```php
$view = new View('user/profile', [
    'user' => $user,
    'title' => 'Profil'
], 'layouts/main');
```

## Veri İşlemleri

### set($key, $value)
Görünüm verisini ayarlar.

```php
View::set('title', 'Sayfa Başlığı');
View::set('user', $userObject);
View::set('items', $itemArray);
```

### setData($data)
Birden fazla veriyi ayarlar.

```php
View::setData([
    'title' => 'Başlık',
    'users' => $users,
    'count' => 10
]);
```

### get($key, $default = null)
Görünüm verisini alır.

```php
$title = View::get('title');
$user = View::get('user', new User());
```

### getData()
Tüm görünüm verilerini döndürür.

```php
$allData = View::getData();
```

### has($key)
Görünüm verisi var mı kontrol eder.

```php
if (View::has('user')) {
    $user = View::get('user');
}
```

### remove($key)
Görünüm verisini siler.

```php
View::remove('temp_data');
```

### clear()
Tüm görünüm verilerini temizler.

```php
View::clear();
```

## View İşlemleri

### setView($view)
Görünüm dosyasını ayarlar.

```php
$view->setView('user/profile');
$view->setView('admin/dashboard');
```

### render()
Görünümü işler ve döndürür.

```php
$html = $view->render();
```

### display($template, $data = null)
Görünümü işler ve ekrana basar.

```php
$view->display('user/profile', ['user' => $user]);
```

### renderView($template, $data = [])
Public view render metodu.

```php
// Controller'da kullanım
$html = $this->renderView('user/profile', ['user' => $user]);
```

### renderPartial($view, $data = [])
Kısmi görünümü (partial) işler.

```php
$sidebar = $view->renderPartial('partials/sidebar', ['menu' => $menu]);
```

## Layout Sistemi

### setLayout($layout)
Layout dosyasını ayarlar.

```php
$view->setLayout('layouts/admin');
$view->setLayout(null); // Layout kullanma
```

### layout($layout)
Layout ayarlar (zincirleme kullanım).

```php
$view->layout('layouts/main')->render();
```

### extend($layout)
Layout'u genişletir (CodeIgniter 4 tarzı).

```php
// View dosyasında
$this->extend('layouts/main');

// Veya View sınıfı ile
$view = new View('user/profile');
$view->extend('layouts/main');
```

### renderSection($name, $default = '')
Section içeriğini render eder (getSection alias).

```php
// Layout dosyasında
<?= $this->renderSection('content') ?>
<?= $this->renderSection('sidebar', 'Varsayılan sidebar') ?>
```

## Section Sistemi

### startSection($name)
Section başlatır.

```php
// View dosyasında
<?php View::startSection('content'); ?>
<h1>İçerik</h1>
<p>Bu section içeriği</p>
<?php View::endSection(); ?>
```

### endSection()
Section bitirir.

```php
<?php View::endSection(); ?>
```

### getSection($name, $default = '')
Section içeriğini döndürür.

```php
// Layout dosyasında
<?= View::getSection('content', 'Varsayılan içerik') ?>
<?= View::getSection('sidebar') ?>

// Veya renderSection kullan
<?= $this->renderSection('content') ?>
```

### yieldSection($name, $default = '')
Section içeriğini yield eder (Laravel tarzı).

```php
// Layout dosyasında
@yield('content')
// Veya PHP'de
<?= $this->yieldSection('content') ?>
```

### hasSection($name)
Section var mı kontrol eder.

```php
<?php if (View::hasSection('sidebar')): ?>
    <div class="sidebar">
        <?= View::getSection('sidebar') ?>
    </div>
<?php endif; ?>
```

## Zincirleme Kullanım

### with($key, $value = null)
Veri ekler (zincirleme).

```php
$view->with('title', 'Başlık')
     ->with('user', $user)
     ->with(['count' => 10, 'active' => true]);
```

## Yapılandırma

### setViewPath($viewPath)
Görünüm dizinini ayarlar.

```php
$view->setViewPath('/custom/views/path');
```

## Örnek Kullanımlar

### Basit View
```php
// Controller'da
class HomeController extends Controller
{
    public function index()
    {
        $view = new View('home/index');
        $view->set('title', 'Ana Sayfa');
        $view->set('message', 'Hoş geldiniz!');
        
        echo $view->render();
    }
}
```

### Layout ile View
```php
// Controller'da
class UserController extends Controller
{
    public function profile($id)
    {
        $user = User::find($id);
        
        $view = new View('user/profile', [
            'user' => $user,
            'title' => $user->name . ' - Profil'
        ], 'layouts/main');
        
        echo $view->render();
    }
}
```

### Zincirleme Kullanım
```php
echo (new View('product/detail'))
    ->with('product', $product)
    ->with('title', $product->name)
    ->with('reviews', $reviews)
    ->layout('layouts/shop')
    ->render();
```

### Section Kullanımı

**Layout dosyası (layouts/main.php):**
```php
<!DOCTYPE html>
<html>
<head>
    <title><?= View::get('title', 'Varsayılan Başlık') ?></title>
    <?= View::getSection('head') ?>
</head>
<body>
    <header>
        <?= View::getSection('header', '<h1>Varsayılan Header</h1>') ?>
    </header>
    
    <main>
        <?= View::getSection('content') ?>
    </main>
    
    <?php if (View::hasSection('sidebar')): ?>
    <aside>
        <?= View::getSection('sidebar') ?>
    </aside>
    <?php endif; ?>
    
    <footer>
        <?= View::getSection('footer', '<p>&copy; 2023</p>') ?>
    </footer>
    
    <?= View::getSection('scripts') ?>
</body>
</html>
```

**View dosyası (user/profile.php):**
```php
<?php $this->extend('layouts/main'); ?>

<?php View::startSection('head'); ?>
<link rel="stylesheet" href="/css/profile.css">
<?php View::endSection(); ?>

<?php View::startSection('header'); ?>
<h1>Kullanıcı Profili</h1>
<nav>
    <a href="/users">Geri</a>
    <a href="/users/<?= $user->id ?>/edit">Düzenle</a>
</nav>
<?php View::endSection(); ?>

<?php View::startSection('content'); ?>
<div class="profile">
    <img src="<?= $user->avatar ?>" alt="Avatar">
    <h2><?= $user->name ?></h2>
    <p><?= $user->email ?></p>
    <p><?= $user->bio ?></p>
</div>
<?php View::endSection(); ?>

<?php View::startSection('sidebar'); ?>
<div class="user-stats">
    <h3>İstatistikler</h3>
    <p>Kayıt: <?= $user->created_at ?></p>
    <p>Son Giriş: <?= $user->last_login ?></p>
</div>
<?php View::endSection(); ?>

<?php View::startSection('scripts'); ?>
<script src="/js/profile.js"></script>
<?php View::endSection(); ?>
```

### Extends Layout Örneği

**Layout dosyası (layouts/app.php):**
```php
<!DOCTYPE html>
<html>
<head>
    <title><?= $this->renderSection('title', 'Varsayılan Başlık') ?></title>
    <?= $this->renderSection('head') ?>
</head>
<body>
    <div class="container">
        <?= $this->renderSection('content') ?>
    </div>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
```

**View dosyası (pages/about.php):**
```php
<?php $this->extend('layouts/app'); ?>

<?php $this->startSection('title'); ?>Hakkımızda<?php $this->endSection(); ?>

<?php $this->startSection('head'); ?>
<meta name="description" content="Hakkımızda sayfası">
<?php $this->endSection(); ?>

<?php $this->startSection('content'); ?>
<h1>Hakkımızda</h1>
<p>Bu bir hakkımızda sayfasıdır.</p>
<?php $this->endSection(); ?>
```

### Partial Views
```php
// Controller'da
class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->get();
        
        $view = new View('blog/index');
        $view->set('posts', $posts);
        
        // Her post için partial render
        $postHtml = '';
        foreach ($posts as $post) {
            $postHtml .= $view->renderPartial('blog/post-card', ['post' => $post]);
        }
        
        $view->set('posts_html', $postHtml);
        echo $view->render();
    }
}
```

**Partial dosyası (blog/post-card.php):**
```php
<article class="post-card">
    <h3><a href="/blog/<?= $post->slug ?>"><?= $post->title ?></a></h3>
    <p class="meta">
        <?= $post->author ?> - <?= $post->created_at ?>
    </p>
    <p><?= $post->excerpt ?></p>
    <a href="/blog/<?= $post->slug ?>" class="read-more">Devamını Oku</a>
</article>
```

### Conditional Sections
```php
// View dosyasında
<?php if ($user->isAdmin()): ?>
    <?php View::startSection('admin-panel'); ?>
    <div class="admin-controls">
        <a href="/admin">Admin Panel</a>
        <a href="/admin/users">Kullanıcılar</a>
    </div>
    <?php View::endSection(); ?>
<?php endif; ?>

// Layout'ta
<?php if (View::hasSection('admin-panel')): ?>
    <div class="admin-bar">
        <?= View::getSection('admin-panel') ?>
    </div>
<?php endif; ?>
```

### Dynamic Layout
```php
class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::findBySlug($slug);
        
        // Sayfa tipine göre layout seç
        $layout = match($page->type) {
            'landing' => 'layouts/landing',
            'blog' => 'layouts/blog',
            'product' => 'layouts/shop',
            default => 'layouts/main'
        };
        
        $view = new View('page/show', ['page' => $page]);
        $view->extend($layout); // Extends kullanımı
        echo $view->render();
    }
}
```

### Section Helpers

```php
// View dosyasında kısa kullanım
<?php $this->section('title', 'Sayfa Başlığı'); ?>

// Çok satırlı section
<?php $this->startSection('content'); ?>
<div class="page-content">
    <h1>Başlık</h1>
    <p>İçerik...</p>
</div>
<?php $this->endSection(); ?>

// Layout'ta render
<?= $this->renderSection('title') ?>
<?= $this->renderSection('content') ?>

// Varsayılan değerle
<?= $this->renderSection('sidebar', '<p>Varsayılan sidebar</p>') ?>
```

## Shortcode Desteği

View sınıfı otomatik olarak shortcode'ları işler:

```php
// View dosyasında
<p>Bu bir shortcode: [button text="Tıkla" url="/action"]</p>
<p>Galeri: [gallery id="123"]</p>
```

## İpuçları

1. **Performance**: Büyük veri setleri için partial view kullanın
2. **Caching**: Sık kullanılan view'ları cache'leyin
3. **Security**: View'da kullanıcı verisini her zaman escape edin
4. **Organization**: Karmaşık layout'lar için section sistemini kullanın
5. **Reusability**: Tekrar kullanılabilir bileşenler için partial'ları tercih edin