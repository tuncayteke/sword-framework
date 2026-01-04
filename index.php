<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Örnek kullanım dosyası
 */

// Kök dizini tanımla
define('BASE_PATH', __DIR__);

// Temel sınıfları dahil et
require_once 'sword/Sword.php';

// Autoloader'ı başlat
Sword::bootstrap();

// Veritabanı yapılandırmasını dahil et (varsa)
if (file_exists(BASE_PATH . '/db_config.php')) {
    require_once BASE_PATH . '/db_config.php';
}

// Proje özel ayarlar (isteğe bağlı)
// Sword::setData('app_name', 'My Custom App');
// Sword::routerPlaceholder('admin', 'myadmin');
// Sword::routerPattern('uuid', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');

// Uygulamayı başlat (rotaları yükler ve işler)
Sword::start();
