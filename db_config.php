<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Veritabanı ve temel yapılandırma dosyası
 * Bu dosya değiştirilmemeli ve git vb. sistemlere eklenmemelidir
 * Yerel bir kopyası db_config.example.php olarak saklanabilir
 */

// Veritabanı bağlantı bilgileri
define('DB_HOST', 'localhost');
define('DB_NAME', 'sword_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');
define('DB_PREFIX', 'sw_'); // Tablo öneki

// Cryptor için sabit anahtar
define('CRYPTOR_KEY', 'f2e7c4b8a6d5e3f2c1b0a9d8e7f6c5b4a3d2e1f0c9b8a7d6e5f4c3b2a1d0');

// Uygulama ortamı
define('ENVIRONMENT', 'development'); // 'development', 'testing', 'production'

// Hata ayıklama modu
define('DEBUG', true);

// Rota hata ayıklama modu
define('ROUTE_DEBUG', true);
