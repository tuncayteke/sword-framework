<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Upload sınıfı - Dosya yükleme işlemlerini yönetir
 */

class Upload
{
    /**
     * Yükleme dizini
     */
    private $uploadDir;

    /**
     * İzin verilen MIME türleri
     */
    private $allowedMimes = [
        // Resim dosyaları
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/bmp' => ['bmp'],
        'image/webp' => ['webp'],
        'image/svg+xml' => ['svg'],

        // Belge dosyaları
        'application/pdf' => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'application/vnd.ms-powerpoint' => ['ppt'],
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['pptx'],
        'text/plain' => ['txt'],
        'text/csv' => ['csv'],

        // Arşiv dosyaları
        'application/zip' => ['zip'],
        'application/x-rar-compressed' => ['rar'],
        'application/x-7z-compressed' => ['7z'],
        'application/x-tar' => ['tar'],
        'application/gzip' => ['gz'],

        // Video dosyaları
        'video/mp4' => ['mp4'],
        'video/mpeg' => ['mpeg', 'mpg'],
        'video/quicktime' => ['mov'],
        'video/x-msvideo' => ['avi'],
        'video/x-ms-wmv' => ['wmv'],
        'video/webm' => ['webm'],
        'video/x-flv' => ['flv'],

        // Ses dosyaları
        'audio/mpeg' => ['mp3'],
        'audio/wav' => ['wav'],
        'audio/ogg' => ['ogg'],
        'audio/aac' => ['aac'],
        'audio/flac' => ['flac'],
        'audio/x-ms-wma' => ['wma']
    ];

    /**
     * Maksimum dosya boyutu (byte)
     */
    private $maxSize = 10485760; // 10MB

    /**
     * Yükleme hataları
     */
    private $errors = [];

    /**
     * Yüklenen dosya bilgileri
     */
    private $uploadedFiles = [];

    /**
     * Tarih bazlı dizin kullanılsın mı?
     */
    private $useDateDir = true;

    /**
     * Yapılandırıcı
     *
     * @param string|null $uploadDir Yükleme dizini
     * @param int|null $maxSize Maksimum dosya boyutu
     * @param bool $useDateDir Tarih bazlı dizin kullanılsın mı?
     */
    public function __construct($uploadDir = null, $maxSize = null, $useDateDir = true)
    {
        // Yükleme dizinini ayarla
        if ($uploadDir === null) {
            if (class_exists('Sword') && method_exists('Sword', 'getPath')) {
                $this->uploadDir = Sword::getPath('uploads');
            } else {
                $this->uploadDir = defined('BASE_PATH') ? BASE_PATH . '/content/uploads' : './uploads';
            }
        } else {
            $this->uploadDir = rtrim($uploadDir, '/\\');
        }

        // Maksimum dosya boyutunu ayarla
        if ($maxSize !== null) {
            $this->maxSize = $maxSize;
        }

        // Tarih bazlı dizin kullanımını ayarla
        $this->useDateDir = $useDateDir;
    }

    /**
     * Dosya yükler
     *
     * @param array $file $_FILES dizisinden bir dosya
     * @param string|null $customName Özel dosya adı
     * @param string|null $subDir Alt dizin
     * @return array|bool Yüklenen dosya bilgileri veya başarısızsa false
     */
    public function upload($file, $customName = null, $subDir = null)
    {
        // Dosya kontrolü
        if (!$this->validateFile($file)) {
            return false;
        }

        // Yükleme dizinini oluştur
        $uploadPath = $this->getUploadPath($subDir);
        if (!$this->createDirectory($uploadPath)) {
            $this->errors[] = __('upload.directory_create_failed', ['path' => $uploadPath]);
            return false;
        }

        // Dosya adını oluştur
        $fileName = $this->generateFileName($file, $customName);

        // Dosyayı taşı
        $destination = $uploadPath . DIRECTORY_SEPARATOR . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->errors[] = __('upload.file_upload_failed', ['name' => $file['name']]);
            return false;
        }

        // Yüklenen dosya bilgilerini kaydet
        $fileInfo = [
            'name' => $fileName,
            'original_name' => $file['name'],
            'path' => $destination,
            'url' => $this->getFileUrl($uploadPath, $fileName),
            'size' => $file['size'],
            'type' => $file['type'],
            'extension' => pathinfo($fileName, PATHINFO_EXTENSION)
        ];

        $this->uploadedFiles[] = $fileInfo;

        return $fileInfo;
    }

    /**
     * Birden fazla dosya yükler
     *
     * @param array $files $_FILES dizisi
     * @param string|null $subDir Alt dizin
     * @return array Yüklenen dosya bilgileri
     */
    public function uploadMultiple($files, $subDir = null)
    {
        $uploadedFiles = [];

        // Dosya dizisi mi yoksa çoklu dosya mı kontrol et
        if (isset($files['name']) && is_array($files['name'])) {
            // Çoklu dosya
            $fileCount = count($files['name']);

            for ($i = 0; $i < $fileCount; $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];

                $result = $this->upload($file, null, $subDir);
                if ($result) {
                    $uploadedFiles[] = $result;
                }
            }
        } else {
            // Dosya dizisi
            foreach ($files as $key => $file) {
                $result = $this->upload($file, null, $subDir);
                if ($result) {
                    $uploadedFiles[$key] = $result;
                }
            }
        }

        return $uploadedFiles;
    }

    /**
     * Dosyayı doğrular
     *
     * @param array $file Dosya
     * @return bool Geçerli mi?
     */
    private function validateFile($file)
    {
        // Yükleme hatası var mı?
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return false;
        }

        // Dosya var mı?
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->errors[] = 'Geçersiz dosya yükleme';
            return false;
        }

        // Dosya boyutu kontrolü
        if ($file['size'] > $this->maxSize) {
            $maxSizeMB = $this->maxSize / 1048576;
            $this->errors[] = "Dosya çok büyük. Maksimum: {$maxSizeMB}MB";
            return false;
        }

        // Gerçek MIME türü kontrolü (güvenlik)
        $realMimeType = mime_content_type($file['tmp_name']);
        if (!$this->isAllowedMimeType($realMimeType)) {
            $this->errors[] = "Desteklenmeyen dosya türü: {$realMimeType}";
            return false;
        }

        // Dosya uzantısı kontrolü
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!$this->isAllowedExtension($extension, $realMimeType)) {
            $this->errors[] = "Geçersiz dosya uzantısı: {$extension}";
            return false;
        }

        // Zararlı içerik kontrolü
        if ($this->containsMaliciousContent($file['tmp_name'])) {
            $this->errors[] = 'Dosya zararlı içerik barındırıyor';
            return false;
        }

        // Dosya adı güvenlik kontrolü
        if ($this->hasUnsafeFileName($file['name'])) {
            $this->errors[] = 'Güvenli olmayan dosya adı';
            return false;
        }

        return true;
    }

    /**
     * Uzantının MIME türü ile uyumlu olup olmadığını kontrol eder
     */
    private function isAllowedExtension($extension, $mimeType)
    {
        if (!isset($this->allowedMimes[$mimeType])) {
            return false;
        }

        return in_array($extension, $this->allowedMimes[$mimeType]);
    }

    /**
     * Zararlı içerik kontrolü
     */
    private function containsMaliciousContent($filePath)
    {
        // PHP kodu kontrolü
        $content = file_get_contents($filePath, false, null, 0, 1024);
        $maliciousPatterns = [
            '/<\?php/i',
            '/<script/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload=/i',
            '/onerror=/i'
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Güvenli olmayan dosya adı kontrolü
     */
    private function hasUnsafeFileName($fileName)
    {
        // Tehlikeli karakterler
        $unsafeChars = ['..', '/', '\\', ':', '*', '?', '"', '<', '>', '|'];

        foreach ($unsafeChars as $char) {
            if (strpos($fileName, $char) !== false) {
                return true;
            }
        }

        // Sistem dosya adları
        $systemNames = ['CON', 'PRN', 'AUX', 'NUL', 'COM1', 'COM2', 'LPT1', 'LPT2'];
        $baseName = strtoupper(pathinfo($fileName, PATHINFO_FILENAME));

        return in_array($baseName, $systemNames);
    }

    /**
     * MIME türünün izin verilip verilmediğini kontrol eder
     *
     * @param string $mimeType MIME türü
     * @return bool İzin veriliyor mu?
     */
    private function isAllowedMimeType($mimeType)
    {
        return isset($this->allowedMimes[$mimeType]);
    }

    /**
     * Yükleme hata mesajını döndürür
     *
     * @param int $errorCode Hata kodu
     * @return string Hata mesajı
     */
    private function getUploadErrorMessage($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return __('upload.ini_size_exceeded');
            case UPLOAD_ERR_FORM_SIZE:
                return __('upload.form_size_exceeded');
            case UPLOAD_ERR_PARTIAL:
                return __('upload.partial_upload');
            case UPLOAD_ERR_NO_FILE:
                return __('upload.no_file');
            case UPLOAD_ERR_NO_TMP_DIR:
                return __('upload.no_tmp_dir');
            case UPLOAD_ERR_CANT_WRITE:
                return __('upload.cant_write');
            case UPLOAD_ERR_EXTENSION:
                return __('upload.extension_stopped');
            default:
                return __('upload.unknown_error');
        }
    }

    /**
     * Yükleme dizinini oluşturur
     *
     * @param string $dir Dizin
     * @return bool Başarılı mı?
     */
    private function createDirectory($dir)
    {
        if (!is_dir($dir)) {
            return mkdir($dir, 0755, true);
        }

        return true;
    }

    /**
     * Yükleme yolunu döndürür
     *
     * @param string|null $subDir Alt dizin
     * @return string Yükleme yolu
     */
    private function getUploadPath($subDir = null)
    {
        $path = $this->uploadDir;

        // Tarih bazlı dizin
        if ($this->useDateDir) {
            $path .= DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d');
        }

        // Alt dizin
        if ($subDir !== null) {
            $path .= DIRECTORY_SEPARATOR . trim($subDir, '/\\');
        }

        return $path;
    }

    /**
     * Dosya adı oluşturur
     *
     * @param array $file Dosya
     * @param string|null $customName Özel dosya adı
     * @return string Dosya adı
     */
    private function generateFileName($file, $customName = null)
    {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

        if ($customName !== null) {
            // Özel dosya adı
            $fileName = $customName;

            // Uzantı ekle
            if (pathinfo($fileName, PATHINFO_EXTENSION) === '') {
                $fileName .= '.' . $extension;
            }
        } else {
            // Benzersiz dosya adı oluştur
            $fileName = uniqid('file_') . '.' . $extension;
        }

        return $fileName;
    }

    /**
     * Dosya URL'sini döndürür
     *
     * @param string $path Dosya yolu
     * @param string $fileName Dosya adı
     * @return string Dosya URL'si
     */
    private function getFileUrl($path, $fileName)
    {
        // Temel URL'yi al
        $baseUrl = '';
        if (class_exists('Sword') && method_exists('Sword', 'url')) {
            $baseUrl = Sword::url('', [], false);
        } else {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $protocol . '://' . $host;

            // Temel yolu ekle
            if (defined('BASE_PATH')) {
                $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
                $scriptDir = dirname($scriptName);
                $baseUrl .= $scriptDir;
            }
        }

        // Yükleme dizinini temel dizinden ayır
        $relativePath = str_replace([BASE_PATH, '\\'], ['', '/'], $path);

        return $baseUrl . $relativePath . '/' . $fileName;
    }

    /**
     * İzin verilen MIME türü ekler
     *
     * @param string $mimeType MIME türü
     * @param array|string $extensions Uzantılar
     * @return Upload
     */
    public function addAllowedMimeType($mimeType, $extensions)
    {
        if (!is_array($extensions)) {
            $extensions = [$extensions];
        }

        $this->allowedMimes[$mimeType] = $extensions;

        return $this;
    }

    /**
     * İzin verilen MIME türlerini ayarlar
     *
     * @param array $mimeTypes MIME türleri
     * @return Upload
     */
    public function setAllowedMimeTypes($mimeTypes)
    {
        $this->allowedMimes = $mimeTypes;

        return $this;
    }

    /**
     * Maksimum dosya boyutunu ayarlar
     *
     * @param int $maxSize Maksimum dosya boyutu (byte)
     * @return Upload
     */
    public function setMaxSize($maxSize)
    {
        $this->maxSize = $maxSize;

        return $this;
    }

    /**
     * Tarih bazlı dizin kullanımını ayarlar
     *
     * @param bool $useDateDir Tarih bazlı dizin kullanılsın mı?
     * @return Upload
     */
    public function setUseDateDir($useDateDir)
    {
        $this->useDateDir = $useDateDir;

        return $this;
    }

    /**
     * Yükleme dizinini ayarlar
     *
     * @param string $uploadDir Yükleme dizini
     * @return Upload
     */
    public function setUploadDir($uploadDir)
    {
        $this->uploadDir = rtrim($uploadDir, '/\\');

        return $this;
    }

    /**
     * Hataları döndürür
     *
     * @return array Hatalar
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Yüklenen dosyaları döndürür
     *
     * @return array Yüklenen dosyalar
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }
}
