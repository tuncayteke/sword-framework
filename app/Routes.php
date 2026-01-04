<?php

/**
 * Sword Framework
 *
 * Routes dosyası - Framework rotalarını tanımlar
 */

// Frontend rotaları
Sword::routerGet('/', 'App\Controllers\HomeController@index');
Sword::routerGet('/about', 'App\Controllers\HomeController@about');
Sword::routerGet('/contact', 'App\Controllers\HomeController@contact');
Sword::routerGet('/shortcode-test', 'App\Controllers\HomeController@shortcodeTest');
Sword::routerGet('/decorator-controller-test', 'App\Controllers\HomeController@decoratorTest');
Sword::routerGet('/decorator-test-api', function () {
    // Tema functions.php dosyasını yükle
    $functionsFile = BASE_PATH . '/content/themes/default/functions.php';
    if (file_exists($functionsFile)) {
        require_once $functionsFile;
    }
    
    // JSON response ile decorator test
    return Sword::response()->json([
        'message' => 'API yanıtı %year% yılında oluşturuldu',
        'timestamp' => '%datetime%',
        'site' => '%site_name%',
        'version' => '%version%'
    ])->send();
});

// Placeholder tanımla
Sword::routerPlaceholder('myadmin', 'admin');

// Admin rotaları - placeholder ile grup
Sword::routerGroup(':myadmin', function ($router) {
    $router->get('/', 'App\Controllers\AdminController@index');  // /admin/ -> /admin eşleşir
    $router->get('/dashboard', 'App\Controllers\AdminController@dashboard');
    $router->get('/themes', 'App\Controllers\AdminController@themes');
    $router->post('/change-theme', 'App\Controllers\AdminController@changeTheme');
});


/*
// Merhaba sayfası
Sword::routerGet('merhaba/:isim', function ($isim = null) {
    echo "Merhaba, " . htmlspecialchars($isim) . "!";
});

// İsimlendirilmiş rota örneği
Sword::routerGet('kullanici/:num', function ($num = null) {
    echo "Kullanıcı ID: " . $num;
}, 'kullanici.goster');

// Placeholder kullanımı örneği
Sword::routerGet(':admin/panel', function () {
    echo "Admin Panel";
});

// Önceden tanımlanmış desenler kullanımı
Sword::routerGet('sayilar/:num', function ($num = null) {
    echo "Sayı: " . $num;
});

Sword::routerGet('harf/:alpha', function ($alpha = null) {
    echo "Harf: " . $alpha;
});

Sword::routerGet('herhangi/:any', function ($any = null) {
    echo "Herhangi: " . $any;
});

// Özel desen kullanımı
Sword::routerGet('uuid/:uuid', function ($uuid = null) {
    echo "UUID: " . $uuid;
});

// UUID çifti örneği - farklı parametre isimleri kullan
Sword::routerGet('uuids/:uuid1/:uuid2', function ($uuid1 = null, $uuid2 = null) {
    echo "UUID 1: " . $uuid1 . "<br>";
    echo "UUID 2: " . $uuid2;
});

// Admin rotaları
Sword::routerGet('/admin', 'AdminController@dashboard');
Sword::routerGet('/admin/dashboard', 'AdminController@dashboard');
Sword::routerGet('/admin/themes', 'AdminController@themes');
Sword::routerPost('/admin/change-theme', 'AdminController@changeTheme');

// Rota grubu örneği
Sword::routerGroup('api', function ($router) {
    $router->get('kullanicilar', function () {
        return Sword::response()->json([
            'kullanicilar' => [
                ['id' => 1, 'ad' => 'Ali'],
                ['id' => 2, 'ad' => 'Veli']
            ]
        ]);
    });
});
*/


// 404 hata işleyicisi
Sword::routerNotFound(function () {
    header("HTTP/1.0 404 Not Found");
    echo '<h1>404 - Sayfa Bulunamadı</h1><p>Aradığınız sayfa bulunamadı.</p>';
});
