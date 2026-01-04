# Plugin - Sword Framework

**Basit ve etkili eklenti sistemi**  
Keskin. Hızlı. Ölümsüz.

## Özellikler

- ✅ **Basit Plugin Yapısı** - Minimal kod, maksimum işlev
- ✅ **Otomatik Yükleme** - Aktif plugin'ler otomatik yüklenir
- ✅ **Event Entegrasyonu** - Framework event'lerine hook
- ✅ **Route Ekleme** - Plugin'ler kendi route'larını ekler
- ✅ **Menü Entegrasyonu** - Admin ve frontend menülere ekleme
- ✅ **Aktivasyon/Deaktivasyon** - Hook sistemi

## Plugin Yapısı

### Dizin Yapısı
```
content/plugins/
├── active.json                    # Aktif plugin listesi
├── seo-optimizer/
│   ├── plugin.json               # Plugin bilgileri
│   ├── seo-optimizer.php         # Ana plugin dosyası
│   ├── views/                    # Plugin view'ları
│   └── assets/                   # CSS, JS dosyaları
└── payment-gateway/
    ├── plugin.json
    ├── payment-gateway.php
    └── config.json
```

### Plugin Bilgi Dosyası (plugin.json)
```json
{
    "title": "SEO Optimizer",
    "description": "Otomatik SEO optimizasyonu ve meta tag yönetimi",
    "version": "1.0.0",
    "author": "Tuncay TEKE",
    "website": "https://www.tuncayteke.com.tr",
    "requires": "1.0.0",
    "category": "SEO",
    "tags": ["seo", "meta", "optimization"]
}
```

## Plugin Oluşturma

### 1. Temel Plugin Sınıfı
```php
<?php

class SeoOptimizerPlugin extends BasePlugin
{
    public function init()
    {
        // Plugin başlatma kodları
        $this->addAction('before_render', [$this, 'addMetaTags']);
        $this->addRoute('GET', '/admin/seo', [$this, 'adminPage']);
        $this->addMenu('admin', 'SEO Ayarları', '/admin/seo');
    }

    public function activate()
    {
        // Plugin aktifleştirilirken çalışır
        $this->createTables();
    }

    public function deactivate()
    {
        // Plugin deaktifleştirilirken çalışır
        $this->cleanup();
    }
}
```

### 2. Event Hook'ları
```php
public function init()
{
    // Framework event'lerine hook
    $this->addAction('before_render', [$this, 'addMetaTags']);
    $this->addAction('order.created', [$this, 'processOrder']);
    $this->addAction('user.login', [$this, 'trackLogin']);
}

public function addMetaTags($viewData)
{
    // Meta tag'leri ekle
    $viewData['meta_title'] = 'SEO Başlık';
    return $viewData;
}
```

### 3. Route Ekleme
```php
public function init()
{
    // Admin sayfaları
    $this->addRoute('GET', '/admin/seo', [$this, 'adminPage']);
    $this->addRoute('POST', '/admin/seo/save', [$this, 'saveSettings']);
    
    // Frontend sayfaları
    $this->addRoute('GET', '/sitemap.xml', [$this, 'generateSitemap']);
}
```

### 4. Menü Ekleme
```php
public function init()
{
    // Admin menüye ekle
    $this->addMenu('admin', 'SEO Ayarları', '/admin/seo', [
        'icon' => 'search',
        'order' => 10
    ]);
    
    // Footer menüye ekle
    $this->addMenu('footer', 'Sitemap', '/sitemap.xml');
}
```

## Plugin Örnekleri

### SEO Optimizer Plugin
```php
<?php

class SeoOptimizerPlugin extends BasePlugin
{
    public function init()
    {
        $this->addAction('before_render', [$this, 'addMetaTags']);
        $this->addMenu('admin', 'SEO Ayarları', '/admin/seo', ['icon' => 'search']);
        $this->addRoute('GET', '/admin/seo', [$this, 'adminPage']);
        $this->addRoute('GET', '/sitemap.xml', [$this, 'generateSitemap']);
    }

    public function activate()
    {
        // SEO tablosu oluştur
        $sql = "CREATE TABLE IF NOT EXISTS seo_meta (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_type VARCHAR(50),
            page_id INT,
            title VARCHAR(255),
            description TEXT,
            keywords VARCHAR(500)
        )";
        
        Sword::db()->query($sql);
    }

    public function addMetaTags($viewData)
    {
        $pageType = $viewData['page_type'] ?? 'page';
        $pageId = $viewData['page_id'] ?? null;
        
        if ($pageId) {
            $seo = Sword::db()->table('seo_meta')
                ->where('page_type', $pageType)
                ->where('page_id', $pageId)
                ->first();
            
            if ($seo) {
                $viewData['meta_title'] = $seo->title;
                $viewData['meta_description'] = $seo->description;
            }
        }
        
        return $viewData;
    }

    public function generateSitemap()
    {
        header('Content-Type: application/xml');
        
        $urls = [
            ['url' => '/', 'priority' => '1.0'],
            ['url' => '/products', 'priority' => '0.8']
        ];
        
        echo $this->buildSitemapXml($urls);
    }
}
```

### Payment Gateway Plugin
```php
<?php

class PaymentGatewayPlugin extends BasePlugin
{
    public function init()
    {
        $this->addAction('order.created', [$this, 'processPayment']);
        $this->addMenu('admin', 'Ödeme Ayarları', '/admin/payment', ['icon' => 'credit-card']);
        $this->addRoute('POST', '/payment/callback', [$this, 'paymentCallback']);
    }

    public function processPayment($order)
    {
        // Ödeme işlemi başlat
        $paymentUrl = $this->createPaymentRequest($order);
        
        // Kullanıcıyı ödeme sayfasına yönlendir
        header("Location: $paymentUrl");
        exit;
    }

    public function paymentCallback()
    {
        $result = $_POST;
        
        if ($result['status'] === 'success') {
            $orderId = $result['order_id'];
            
            // Siparişi güncelle
            Sword::db()->table('orders')
                ->where('id', $orderId)
                ->update(['status' => 'paid']);
            
            // Event tetikle
            Events::dispatch('payment.completed', $orderId);
        }
    }
}
```

### Analytics Plugin
```php
<?php

class AnalyticsPlugin extends BasePlugin
{
    public function init()
    {
        $this->addAction('after_render', [$this, 'addTrackingCode']);
        $this->addAction('user.login', [$this, 'trackLogin']);
        $this->addAction('order.created', [$this, 'trackPurchase']);
        $this->addMenu('admin', 'Analytics', '/admin/analytics', ['icon' => 'bar-chart']);
    }

    public function addTrackingCode($content)
    {
        $trackingId = $this->getSetting('google_analytics_id');
        
        if ($trackingId) {
            $script = "
            <script async src='https://www.googletagmanager.com/gtag/js?id={$trackingId}'></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', '{$trackingId}');
            </script>
            ";
            
            $content = str_replace('</head>', $script . '</head>', $content);
        }
        
        return $content;
    }

    public function trackPurchase($order)
    {
        // E-ticaret tracking
        $this->sendAnalyticsEvent('purchase', [
            'transaction_id' => $order->id,
            'value' => $order->total,
            'currency' => 'TRY'
        ]);
    }
}
```

## Plugin Yönetimi

### Aktifleştirme/Deaktifleştirme
```php
// Plugin aktifleştir
Plugin::activate('seo-optimizer');

// Plugin deaktifleştir
Plugin::deactivate('seo-optimizer');

// Plugin aktif mi kontrol et
if (Plugin::isActive('seo-optimizer')) {
    // Plugin aktif
}
```

### Plugin Listesi
```php
// Mevcut plugin'leri listele
$plugins = Plugin::getAvailablePlugins();

foreach ($plugins as $plugin) {
    echo $plugin['title'] . ' - ' . $plugin['version'];
    echo $plugin['active'] ? ' (Aktif)' : ' (Pasif)';
}
```

### Plugin Controller
```php
class PluginController extends Controller
{
    public function index()
    {
        $plugins = Plugin::getAvailablePlugins();
        
        return $this->render('admin/plugins/index', [
            'plugins' => $plugins
        ]);
    }

    public function activate()
    {
        $pluginName = $this->request->post('plugin');
        
        if (Plugin::activate($pluginName)) {
            $this->setFlash('success', 'Plugin aktifleştirildi');
        }
        
        return $this->redirect('/admin/plugins');
    }

    public function deactivate()
    {
        $pluginName = $this->request->post('plugin');
        
        if (Plugin::deactivate($pluginName)) {
            $this->setFlash('success', 'Plugin deaktifleştirildi');
        }
        
        return $this->redirect('/admin/plugins');
    }
}
```

## BasePlugin Metodları

### Event Metodları
```php
// Event listener ekle
$this->addAction('event_name', [$this, 'methodName'], $priority);

// Örnekler
$this->addAction('before_render', [$this, 'modifyView']);
$this->addAction('order.created', [$this, 'processOrder'], 5);
$this->addAction('user.login', [$this, 'trackLogin'], 10);
```

### Route Metodları
```php
// Route ekle
$this->addRoute('GET', '/path', [$this, 'methodName']);
$this->addRoute('POST', '/path', [$this, 'methodName']);

// Örnekler
$this->addRoute('GET', '/admin/plugin', [$this, 'adminPage']);
$this->addRoute('POST', '/api/webhook', [$this, 'webhook']);
```

### Menü Metodları
```php
// Menü ekle
$this->addMenu($location, $title, $url, $options);

// Örnekler
$this->addMenu('admin', 'Plugin Ayarları', '/admin/plugin');
$this->addMenu('main', 'Blog', '/blog', ['order' => 5]);
$this->addMenu('footer', 'API', '/api/docs');
```

## Plugin Geliştirme İpuçları

### 1. Plugin Adlandırma
```php
// İyi: Açıklayıcı isimler
class SeoOptimizerPlugin extends BasePlugin { }
class PaymentGatewayPlugin extends BasePlugin { }
class SocialLoginPlugin extends BasePlugin { }

// Kötü: Belirsiz isimler
class MyPlugin extends BasePlugin { }
class Plugin1 extends BasePlugin { }
```

### 2. Event Kullanımı
```php
// İyi: Framework event'lerini kullan
$this->addAction('before_render', [$this, 'addMetaTags']);
$this->addAction('order.created', [$this, 'processOrder']);

// Kötü: Doğrudan müdahale
// global $viewData; $viewData['title'] = 'New Title';
```

### 3. Veritabanı İşlemleri
```php
public function activate()
{
    // Aktivasyonda tablo oluştur
    $sql = "CREATE TABLE IF NOT EXISTS plugin_data (...)";
    Sword::db()->query($sql);
}

public function deactivate()
{
    // Deaktivasyonda temizlik yap (isteğe bağlı)
    // Sword::db()->query("DROP TABLE plugin_data");
}
```

### 4. Hata Yönetimi
```php
public function processPayment($order)
{
    try {
        $result = $this->callPaymentAPI($order);
        return $result;
    } catch (Exception $e) {
        error_log("Payment error: " . $e->getMessage());
        return false;
    }
}
```

## Plugin Dağıtımı

### ZIP Paketi Oluşturma
```
seo-optimizer.zip
├── plugin.json
├── seo-optimizer.php
├── views/
│   └── admin.php
└── assets/
    ├── seo.css
    └── seo.js
```

### Kurulum
1. ZIP dosyasını `content/plugins/` dizinine çıkart
2. Admin panelden plugin'i aktifleştir
3. Plugin ayarlarını yapılandır

## Framework Entegrasyonu

### Bootstrap'da Plugin Yükleme
```php
// index.php veya bootstrap
require_once 'sword/Plugin.php';

// Plugin'leri başlat
Plugin::init();
```

### Admin Panelde Plugin Yönetimi
```php
// Admin rotaları
Sword::routerGet('/admin/plugins', 'PluginController@index');
Sword::routerPost('/admin/plugins/activate', 'PluginController@activate');
Sword::routerPost('/admin/plugins/deactivate', 'PluginController@deactivate');
```

---

**Sword Framework** - Keskin. Hızlı. Ölümsüz.