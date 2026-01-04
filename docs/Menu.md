# Menu - Sword Framework

**Basit ve etkili menü yönetimi**  
Keskin. Hızlı. Ölümsüz.

## Özellikler

- ✅ **Merkezi Menü Yönetimi** - Tüm menüler tek yerden
- ✅ **Çoklu Konum** - Admin, ana menü, footer, user menü
- ✅ **Otomatik Rendering** - Farklı template'ler
- ✅ **İzin Kontrolü** - Kullanıcı bazlı menü gösterimi
- ✅ **Plugin Desteği** - Plugin'ler menü ekleyebilir
- ✅ **Sıralama** - Order ile menü sıralaması

## Temel Kullanım

### Menü Ekleme

```php
// Basit menü ekleme
Menu::add('main', 'Ana Sayfa', '/');
Menu::add('main', 'Ürünler', '/products');
Menu::add('main', 'İletişim', '/contact');

// Seçeneklerle menü ekleme
Menu::add('admin', 'Dashboard', '/admin', [
    'icon' => 'dashboard',
    'order' => 1,
    'permission' => 'admin'
]);
```

### Menü Gösterimi

```php
// Layout dosyalarında
<?= Menu::render('main', 'navbar') ?>
<?= Menu::render('admin', 'admin') ?>
<?= Menu::render('footer', 'footer') ?>
```

## Menü Konumları

### Ana Menü
```php
Menu::add('main', 'Ana Sayfa', '/', ['order' => 1]);
Menu::add('main', 'Ürünler', '/products', ['order' => 2]);
Menu::add('main', 'Kategoriler', '/categories', ['order' => 3]);
Menu::add('main', 'Hakkımızda', '/about', ['order' => 4]);
Menu::add('main', 'İletişim', '/contact', ['order' => 5]);
```

### Admin Menü
```php
Menu::add('admin', 'Dashboard', '/admin', [
    'id' => 'dashboard',
    'icon' => 'dashboard',
    'order' => 1
]);

Menu::add('admin', 'Ürünler', '#', [
    'id' => 'products',
    'icon' => 'box',
    'order' => 2
]);

// Submenu'lar
Menu::add('admin', 'Tüm Ürünler', '/admin/products', [
    'parent_id' => 'products'
]);

Menu::add('admin', 'Yeni Ürün', '/admin/products/create', [
    'parent_id' => 'products'
]);

Menu::add('admin', 'Kategoriler', '/admin/categories', [
    'parent_id' => 'products'
]);
```

### Footer Menü
```php
Menu::add('footer', 'Gizlilik Politikası', '/privacy');
Menu::add('footer', 'Kullanım Şartları', '/terms');
Menu::add('footer', 'Sitemap', '/sitemap.xml');
Menu::add('footer', 'İletişim', '/contact');
```

### Kullanıcı Menüsü
```php
Menu::add('user', 'Profilim', '/profile', [
    'permission' => 'user',
    'icon' => 'user'
]);

Menu::add('user', 'Siparişlerim', '/orders', [
    'permission' => 'user',
    'icon' => 'shopping-bag'
]);

Menu::add('user', 'Çıkış', '/logout', [
    'permission' => 'user',
    'icon' => 'logout'
]);
```

## Menü Seçenekleri

### Temel Seçenekler
```php
Menu::add('location', 'Başlık', '/url', [
    'id' => 'unique-id',          // Benzersiz ID
    'icon' => 'icon-name',        // İkon adı
    'order' => 10,                // Sıralama (düşük önce)
    'permission' => 'admin',      // İzin kontrolü
    'active' => true,             // Aktif/pasif
    'parent_id' => 'parent-id'    // Üst menü ID'si
]);
```

### Submenu Sistemi
```php
// Ana menü
Menu::add('admin', 'E-ticaret', '#', [
    'id' => 'ecommerce',
    'icon' => 'shopping-bag'
]);

// Submenu'lar
Menu::add('admin', 'Ürün Yönetimi', '/admin/products', [
    'parent_id' => 'ecommerce'
]);

Menu::add('admin', 'Sipariş Takibi', '/admin/orders', [
    'parent_id' => 'ecommerce'
]);
```

### İzin Seviyeleri
```php
// Herkese açık
Menu::add('main', 'Ana Sayfa', '/');

// Sadece giriş yapmış kullanıcılar
Menu::add('user', 'Profilim', '/profile', ['permission' => 'user']);

// Sadece admin'ler
Menu::add('admin', 'Ayarlar', '/admin/settings', ['permission' => 'admin']);
```

## Template'ler

### Admin Template
```php
<?= Menu::render('admin', 'admin') ?>
```

**Çıktı:**
```html
<nav class="admin-menu">
    <ul>
        <li class="active">
            <a href="/admin">
                <i class="icon-dashboard"></i>Dashboard
            </a>
        </li>
        <li>
            <a href="/admin/products">
                <i class="icon-box"></i>Ürünler
            </a>
        </li>
    </ul>
</nav>
```

### Navbar Template
```php
<?= Menu::render('main', 'navbar') ?>
```

**Çıktı:**
```html
<nav class="navbar">
    <ul>
        <li class="active"><a href="/">Ana Sayfa</a></li>
        <li><a href="/products">Ürünler</a></li>
        <li><a href="/contact">İletişim</a></li>
    </ul>
</nav>
```

### Footer Template
```php
<?= Menu::render('footer', 'footer') ?>
```

**Çıktı:**
```html
<nav class="footer-menu">
    <a href="/privacy">Gizlilik Politikası</a>
    <a href="/terms">Kullanım Şartları</a>
    <a href="/contact">İletişim</a>
</nav>
```

## Layout Entegrasyonu

### Admin Layout
```php
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <?= Menu::render('admin', 'admin') ?>
        </aside>
        
        <main class="content">
            <!-- İçerik -->
        </main>
    </div>
</body>
</html>
```

### Frontend Layout
```php
<!DOCTYPE html>
<html>
<head>
    <title>E-ticaret Sitesi</title>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="/">Site Logo</a>
            </div>
            
            <div class="main-menu">
                <?= Menu::render('main', 'navbar') ?>
            </div>
            
            <div class="user-menu">
                <?php if (Auth::check()): ?>
                    <?= Menu::render('user', 'default') ?>
                <?php else: ?>
                    <a href="/login">Giriş</a>
                    <a href="/register">Kayıt</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <main>
        <!-- İçerik -->
    </main>
    
    <footer>
        <div class="container">
            <?= Menu::render('footer', 'footer') ?>
        </div>
    </footer>
</body>
</html>
```

## Plugin'lerde Menü Ekleme

### SEO Plugin Örneği
```php
class SeoOptimizerPlugin extends BasePlugin
{
    public function init()
    {
        // Admin menüye SEO ayarları ekle
        $this->addMenu('admin', 'SEO Ayarları', '/admin/seo', [
            'id' => 'seo',
            'icon' => 'search',
            'order' => 10
        ]);
        
        // Footer'a sitemap ekle
        $this->addMenu('footer', 'Sitemap', '/sitemap.xml', [
            'order' => 20
        ]);
    }
}
```

### E-ticaret Plugin Örneği
```php
class EcommercePlugin extends BasePlugin
{
    public function init()
    {
        // Ana menü
        $this->addMenu('admin', 'E-ticaret', '#', [
            'id' => 'ecommerce',
            'icon' => 'shopping-bag',
            'order' => 5
        ]);
        
        // Submenu'lar
        $this->addMenu('admin', 'Ürünler', '/admin/products', [
            'parent_id' => 'ecommerce'
        ]);
        
        $this->addMenu('admin', 'Siparişler', '/admin/orders', [
            'parent_id' => 'ecommerce'
        ]);
    }
}
```

## Dinamik Menüler

### Veritabanından Menü
```php
// Kategorileri menüye ekle
$categories = Category::where('active', 1)->orderBy('sort_order')->get();

foreach ($categories as $category) {
    Menu::add('main', $category->name, '/category/' . $category->slug, [
        'order' => $category->sort_order
    ]);
}
```

### Koşullu Menüler
```php
// Sadece admin'lere özel menü
if (Auth::isAdmin()) {
    Menu::add('admin', 'Sistem Ayarları', '/admin/system', [
        'icon' => 'settings',
        'order' => 100
    ]);
}

// Sadece e-ticaret aktifse
if (Plugin::isActive('ecommerce')) {
    Menu::add('main', 'Alışveriş Sepeti', '/cart', [
        'icon' => 'shopping-cart',
        'order' => 100
    ]);
}
```

## Utility Metodları

### Menü Kontrolü
```php
// Menü var mı?
$items = Menu::get('admin');
if (!empty($items)) {
    echo Menu::render('admin', 'admin');
}

// Tüm menüleri getir
$allMenus = Menu::all();
```

### Menü Temizleme
```php
// Belirli konumu temizle
Menu::clear('admin');

// Tüm menüleri temizle
Menu::clear();
```

## CSS Örnekleri

### Admin Menü CSS
```css
.admin-menu {
    background: #2c3e50;
    width: 250px;
    height: 100vh;
}

.admin-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.admin-menu li {
    border-bottom: 1px solid #34495e;
}

.admin-menu a {
    display: block;
    padding: 15px 20px;
    color: #ecf0f1;
    text-decoration: none;
    transition: background 0.3s;
}

.admin-menu a:hover,
.admin-menu .active a {
    background: #3498db;
}

.admin-menu i {
    margin-right: 10px;
    width: 16px;
}
```

### Navbar CSS
```css
.navbar ul {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
}

.navbar li {
    margin-right: 30px;
}

.navbar a {
    color: #333;
    text-decoration: none;
    font-weight: 500;
    padding: 10px 0;
    border-bottom: 2px solid transparent;
    transition: border-color 0.3s;
}

.navbar a:hover,
.navbar .active a {
    border-bottom-color: #3498db;
}
```

## Best Practices

### 1. Menü Organizasyonu
```php
// İyi: Mantıklı sıralama
Menu::add('admin', 'Dashboard', '/admin', ['order' => 1]);
Menu::add('admin', 'İçerik', '/admin/content', ['order' => 10]);
Menu::add('admin', 'Kullanıcılar', '/admin/users', ['order' => 20]);
Menu::add('admin', 'Ayarlar', '/admin/settings', ['order' => 100]);
```

### 2. İzin Kontrolü
```php
// İyi: Uygun izin seviyeleri
Menu::add('admin', 'Kullanıcı Yönetimi', '/admin/users', [
    'permission' => 'admin'
]);

Menu::add('user', 'Profilim', '/profile', [
    'permission' => 'user'
]);
```

### 3. Plugin Menüleri
```php
// İyi: Plugin menüleri yüksek order ile
$this->addMenu('admin', 'Plugin Ayarları', '/admin/plugin', [
    'order' => 50  // Ana menülerden sonra
]);
```

---

**Sword Framework** - Keskin. Hızlı. Ölümsüz.