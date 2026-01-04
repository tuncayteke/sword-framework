<?php
/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Model sınıfını döndürür
 *
 * @param string $modelName Model adı
 * @return Model
 */
public static function model($modelName)
{
    // Model sınıfını yükle
    if (!class_exists('Model')) {
        require_once __DIR__ . '/Model.php';
    }
    
    // Model sınıfı adını oluştur
    if (strpos($modelName, 'Model') === false) {
        $modelName .= 'Model';
    }
    
    // Model sınıfını kontrol et
    if (!class_exists($modelName)) {
        // Uygulama dizininde model dosyasını ara
        $modelFile = defined('BASE_PATH') ? BASE_PATH . '/models/' . $modelName . '.php' : null;
        if ($modelFile && file_exists($modelFile)) {
            require_once $modelFile;
        } else {
            throw new Exception("Model sınıfı bulunamadı: {$modelName}");
        }
    }
    
    return new $modelName();
}