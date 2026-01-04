# Theme Class

Tema yönetimi işlemlerini yönetir. Frontend ve admin temaları için ayrı destek sunar.

## Temel Kullanım

```php
// Tema ayarla
Theme::set('frontend', 'my-theme');
Theme::set('admin', 'custom-admin');

// Aktif temayı al
$frontendTheme = Theme::get('frontend');
$adminTheme = Theme::get('admin');
```

## Tema Ayarlama

### set($type, $theme)
Aktif temayı ayarlar.

```php
// Frontend teması
Theme::set('frontend', 'blog-theme');

// Admin teması
Theme::set('admin', 'dark-admin');
```

### get($type)
Aktif temayı döndürür.

```php
$currentTheme = Theme::get('frontend'); // 'blog-theme'
$adminTheme = Theme::get('admin');      // 'dark-admin'

// Tema ayarlanmamışsa 'default' döner
$theme = Theme::get('frontend'); // 'default'
```

## Tema Yolları

### getPath($type, $theme = null)
Tema dizin yolunu döndürür.

```php
// Aktif frontend teması yolu
$path = Theme::getPath('frontend');
// /var/www/content/themes/blog-theme

// Belirli tema yolu
$path = Theme::getPath('frontend', 'custom-theme');
// /var/www/content/themes/custom-theme

// Admin tema yolu
$adminPath = Theme::getPath('admin');
// /var/www/content/admin/themes/dark-admin
```

### getAssetPath($type, $theme = null)
Tema asset yolunu döndürür.

```php
// Frontend tema asset'leri
$assetPath = Theme::getAssetPath('frontend');
// /var/www/content/themes/blog-theme/assets

// Admin tema asset'leri
$adminAssetPath = Theme::getAssetPath('admin');
// /var/www/content/admin/themes/dark-admin/assets
```

## Tema Bilgileri

### exists($type, $theme)
Tema var mı kontrol eder.

```php
if (Theme::exists('frontend', 'my-theme')) {
    Theme::set('frontend', 'my-theme');
} else {
    echo 'Tema bulunamadı';
}
```

### getInfo($type, $theme)
Tema bilgilerini döndürür.

```php
$info = Theme::getInfo('frontend', 'blog-theme');
/*
[
    'name' => 'Blog Theme',
    'version' => '1.0.0',
    'author' => 'John Doe',
    'description' => 'Modern blog teması'
]
*/
```

### getAvailable($type)
Mevcut temaları listeler.

```php
$frontendThemes = Theme::getAvailable('frontend');
// ['default', 'blog-theme', 'portfolio-theme']

$adminThemes = Theme::getAvailable('admin');
// ['default', 'dark-admin', 'light-admin']
```

## Tema Yapısı

### Frontend Tema Dizini
```
content/themes/my-theme/
├── theme.php          # Tema bilgileri
├── index.php          # Ana sayfa şablonu
├── header.php         # Başlık şablonu
├── footer.php         # Alt bilgi şablonu
├── sidebar.php        # Kenar çubuğu
├── functions.php      # Tema fonksiyonları
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
└── langs/
    ├── tr.php
    └── en.php
```

### Admin Tema Dizini
```
content/admin/themes/my-admin/
├── theme.php          # Tema bilgileri
├── dashboard.php      # Dashboard şablonu
├── header.php         # Admin başlık
├── footer.php         # Admin alt bilgi
├── sidebar.php        # Admin menü
├── functions.php      # Admin fonksiyonları
└── assets/
    ├── css/
    ├── js/
    └── images/
```

## Örnek Kullanımlar

### Tema Seçici
```php
class ThemeController extends Controller
{
    public function index()
    {
        $frontendThemes = Theme::getAvailable('frontend');
        $currentTheme = Theme::get('frontend');
        
        $themes = [];
        foreach ($frontendThemes as $themeName) {
            $info = Theme::getInfo('frontend', $themeName);
            $themes[] = [
                'name' => $themeName,
                'info' => $info,
                'active' => $themeName === $currentTheme
            ];
        }
        
        $this->set('themes', $themes);
        $this->render('admin/themes');
    }
    
    public function activate($theme)
    {
        if (Theme::exists('frontend', $theme)) {
            Theme::set('frontend', $theme);
            Session::flash('success', 'Tema aktifleştirildi: ' . $theme);
        } else {
            Session::flash('error', 'Tema bulunamadı: ' . $theme);
        }
        
        return $this->redirect('/admin/themes');
    }
}
```

### Tema Bilgi Dosyası
```php
// content/themes/blog-theme/theme.php
<?php
return [
    'name' => 'Blog Theme',
    'version' => '2.1.0',
    'author' => 'John Doe',
    'author_url' => 'https://johndoe.com',
    'description' => 'Modern ve responsive blog teması',
    'screenshot' => 'screenshot.png',
    'tags' => ['blog', 'responsive', 'modern'],
    'requires' => [
        'php' => '7.4',
        'sword' => '1.0'
    ],
    'supports' => [
        'widgets' => true,
        'menus' => true,
        'thumbnails' => true,
        'custom_header' => true
    ]
];
```

### Tema Fonksiyonları
```php
// content/themes/blog-theme/functions.php
<?php

// Tema asset URL'si
function theme_asset($path) {
    $themePath = Theme::getAssetPath('frontend');
    $baseUrl = Sword::url('', [], false);
    return $baseUrl . str_replace(BASE_PATH, '', $themePath) . '/' . $path;
}

// Tema dil desteği
function theme_text($key, $params = []) {
    Lang::addDirectory('theme', Theme::getPath('frontend') . '/langs');
    return Lang::get($key, $params, 'theme');
}

// Tema ayarları
function get_theme_option($key, $default = null) {
    $options = Sword::getData('theme_options', []);
    return $options[$key] ?? $default;
}

function set_theme_option($key, $value) {
    $options = Sword::getData('theme_options', []);
    $options[$key] = $value;
    Sword::setData('theme_options', $options);
}

// Widget alanları
function register_sidebar($id, $name, $description = '') {
    $sidebars = Sword::getData('theme_sidebars', []);
    $sidebars[$id] = [
        'name' => $name,
        'description' => $description
    ];
    Sword::setData('theme_sidebars', $sidebars);
}

// Menü desteği
function register_nav_menu($location, $description) {
    $menus = Sword::getData('theme_menus', []);
    $menus[$location] = $description;
    Sword::setData('theme_menus', $menus);
}
```

### Tema Şablonu
```php
// content/themes/blog-theme/index.php
<?php get_header(); ?>

<main class="main-content">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <?php if (have_posts()): ?>
                    <?php while (have_posts()): the_post(); ?>
                        <article class="post">
                            <h2><a href="<?= get_permalink() ?>"><?= get_title() ?></a></h2>
                            <div class="post-meta">
                                <?= get_date() ?> | <?= get_author() ?>
                            </div>
                            <div class="post-content">
                                <?= get_excerpt() ?>
                            </div>
                            <a href="<?= get_permalink() ?>" class="read-more">
                                <?= theme_text('read_more') ?>
                            </a>
                        </article>
                    <?php endwhile; ?>
                    
                    <?= get_pagination() ?>
                <?php else: ?>
                    <p><?= theme_text('no_posts_found') ?></p>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <?php get_sidebar(); ?>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
```

### Tema Header
```php
// content/themes/blog-theme/header.php
<!DOCTYPE html>
<html lang="<?= get_language() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= get_page_title() ?></title>
    
    <!-- Tema CSS -->
    <link rel="stylesheet" href="<?= theme_asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= theme_asset('css/responsive.css') ?>">
    
    <!-- Tema renkleri -->
    <style>
        :root {
            --primary-color: <?= get_theme_option('primary_color', '#007cba') ?>;
            --secondary-color: <?= get_theme_option('secondary_color', '#666') ?>;
        }
    </style>
    
    <?= get_head() ?>
</head>
<body class="<?= get_body_class() ?>">
    
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="site-branding">
                    <?php if (get_theme_option('logo')): ?>
                        <img src="<?= get_theme_option('logo') ?>" alt="<?= get_site_name() ?>">
                    <?php else: ?>
                        <h1><?= get_site_name() ?></h1>
                    <?php endif; ?>
                </div>
                
                <nav class="main-navigation">
                    <?= get_nav_menu('primary') ?>
                </nav>
            </div>
        </div>
    </header>
```

### Tema Yöneticisi
```php
class ThemeManager
{
    public static function installTheme($zipFile)
    {
        $upload = new Upload();
        $result = $upload->upload($zipFile, null, 'temp');
        
        if ($result) {
            $zip = new ZipArchive();
            if ($zip->open($result['path']) === TRUE) {
                $themesPath = BASE_PATH . '/content/themes/';
                $zip->extractTo($themesPath);
                $zip->close();
                
                // Geçici dosyayı sil
                unlink($result['path']);
                
                return true;
            }
        }
        
        return false;
    }
    
    public static function deleteTheme($theme)
    {
        if ($theme === 'default') {
            return false; // Varsayılan temayı silme
        }
        
        $themePath = Theme::getPath('frontend', $theme);
        
        if (is_dir($themePath)) {
            return self::deleteDirectory($themePath);
        }
        
        return false;
    }
    
    private static function deleteDirectory($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? self::deleteDirectory($path) : unlink($path);
        }
        
        return rmdir($dir);
    }
    
    public static function getThemePreview($theme)
    {
        $themePath = Theme::getPath('frontend', $theme);
        $screenshotPath = $themePath . '/screenshot.png';
        
        if (file_exists($screenshotPath)) {
            return Sword::url(str_replace(BASE_PATH, '', $screenshotPath));
        }
        
        return Sword::url('assets/images/no-preview.png');
    }
}
```

### Tema Özelleştirici
```php
class ThemeCustomizer
{
    public static function getOptions()
    {
        return [
            'colors' => [
                'primary_color' => [
                    'type' => 'color',
                    'label' => 'Ana Renk',
                    'default' => '#007cba'
                ],
                'secondary_color' => [
                    'type' => 'color', 
                    'label' => 'İkincil Renk',
                    'default' => '#666666'
                ]
            ],
            'layout' => [
                'sidebar_position' => [
                    'type' => 'select',
                    'label' => 'Kenar Çubuğu Konumu',
                    'options' => [
                        'right' => 'Sağ',
                        'left' => 'Sol',
                        'none' => 'Yok'
                    ],
                    'default' => 'right'
                ]
            ],
            'typography' => [
                'font_family' => [
                    'type' => 'select',
                    'label' => 'Font Ailesi',
                    'options' => [
                        'Arial' => 'Arial',
                        'Georgia' => 'Georgia',
                        'Times' => 'Times New Roman'
                    ],
                    'default' => 'Arial'
                ]
            ]
        ];
    }
    
    public static function saveOptions($options)
    {
        Sword::setData('theme_options', $options);
        
        // CSS dosyasını yeniden oluştur
        self::generateCustomCSS($options);
    }
    
    private static function generateCustomCSS($options)
    {
        $css = ":root {\n";
        $css .= "  --primary-color: {$options['primary_color']};\n";
        $css .= "  --secondary-color: {$options['secondary_color']};\n";
        $css .= "  --font-family: {$options['font_family']};\n";
        $css .= "}\n";
        
        $customCSSPath = Theme::getAssetPath('frontend') . '/css/custom.css';
        file_put_contents($customCSSPath, $css);
    }
}
```

## Tema Geliştirme İpuçları

1. **Yapı**: Standart tema yapısını takip edin
2. **Uyumluluk**: Farklı ekran boyutları için responsive tasarım
3. **Performans**: CSS ve JS dosyalarını minimize edin
4. **Güvenlik**: Kullanıcı girdilerini her zaman filtreleyin
5. **Dil Desteği**: Çoklu dil desteği ekleyin