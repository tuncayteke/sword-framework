# Upload Class

Dosya yükleme işlemlerini yönetir. Güvenli dosya yükleme, MIME türü kontrolü ve çoklu dosya desteği sunar.

## Temel Kullanım

```php
$upload = new Upload();

// Tek dosya yükleme
$result = $upload->upload($_FILES['file']);

// Çoklu dosya yükleme
$results = $upload->uploadMultiple($_FILES['files']);
```

## Yapılandırıcı

### __construct($uploadDir = null, $maxSize = null, $useDateDir = true)

```php
// Varsayılan ayarlarla
$upload = new Upload();

// Özel dizin ile
$upload = new Upload('/custom/upload/path');

// Özel boyut sınırı ile (5MB)
$upload = new Upload(null, 5 * 1024 * 1024);

// Tarih bazlı dizin kapalı
$upload = new Upload(null, null, false);
```

## Dosya Yükleme

### upload($file, $customName = null, $subDir = null)
Tek dosya yükler.

```php
// Basit yükleme
$result = $upload->upload($_FILES['avatar']);

// Özel dosya adı ile
$result = $upload->upload($_FILES['avatar'], 'profile_picture.jpg');

// Alt dizin ile
$result = $upload->upload($_FILES['document'], null, 'documents');

// Hem özel ad hem alt dizin
$result = $upload->upload($_FILES['file'], 'custom_name.pdf', 'pdfs');
```

### uploadMultiple($files, $subDir = null)
Birden fazla dosya yükler.

```php
// Çoklu dosya input'u
$results = $upload->uploadMultiple($_FILES['files']);

// Alt dizin ile
$results = $upload->uploadMultiple($_FILES['images'], 'gallery');

// Ayrı dosya input'ları
$files = [
    'avatar' => $_FILES['avatar'],
    'cover' => $_FILES['cover']
];
$results = $upload->uploadMultiple($files, 'profile');
```

## Yapılandırma Metodları

### setUploadDir($uploadDir)
Yükleme dizinini ayarlar.

```php
$upload->setUploadDir('/var/www/uploads');
```

### setMaxSize($maxSize)
Maksimum dosya boyutunu ayarlar.

```php
$upload->setMaxSize(2 * 1024 * 1024); // 2MB
$upload->setMaxSize(10485760); // 10MB
```

### setUseDateDir($useDateDir)
Tarih bazlı dizin kullanımını ayarlar.

```php
$upload->setUseDateDir(true);  // 2023/12/25 formatında
$upload->setUseDateDir(false); // Doğrudan upload dizinine
```

### addAllowedMimeType($mimeType, $extensions)
İzin verilen MIME türü ekler.

```php
$upload->addAllowedMimeType('application/json', ['json']);
$upload->addAllowedMimeType('text/xml', ['xml']);
```

### setAllowedMimeTypes($mimeTypes)
İzin verilen MIME türlerini ayarlar.

```php
$mimeTypes = [
    'image/jpeg' => ['jpg', 'jpeg'],
    'image/png' => ['png'],
    'application/pdf' => ['pdf']
];
$upload->setAllowedMimeTypes($mimeTypes);
```

## Desteklenen Dosya Türleri

### Resim Dosyaları
- JPEG (jpg, jpeg)
- PNG (png)
- GIF (gif)
- BMP (bmp)
- WebP (webp)
- SVG (svg)

### Belge Dosyaları
- PDF (pdf)
- Word (doc, docx)
- Excel (xls, xlsx)
- PowerPoint (ppt, pptx)
- Text (txt)
- CSV (csv)

### Arşiv Dosyaları
- ZIP (zip)
- RAR (rar)
- 7Z (7z)
- TAR (tar)
- GZIP (gz)

### Medya Dosyaları
- Video: MP4, MPEG, MOV, AVI, WMV, WebM, FLV
- Ses: MP3, WAV, OGG, AAC, FLAC, WMA

## Hata Yönetimi

### getErrors()
Yükleme hatalarını döndürür.

```php
if (!$upload->upload($_FILES['file'])) {
    $errors = $upload->getErrors();
    foreach ($errors as $error) {
        echo $error . "\n";
    }
}
```

### getUploadedFiles()
Yüklenen dosya bilgilerini döndürür.

```php
$uploadedFiles = $upload->getUploadedFiles();
foreach ($uploadedFiles as $file) {
    echo "Dosya: " . $file['name'] . "\n";
    echo "URL: " . $file['url'] . "\n";
}
```

## Örnek Kullanımlar

### Profil Resmi Yükleme
```php
class ProfileController extends Controller
{
    public function uploadAvatar()
    {
        if (!$this->request->hasFile('avatar')) {
            return $this->error('Dosya seçilmedi');
        }
        
        $upload = new Upload();
        $upload->setMaxSize(2 * 1024 * 1024) // 2MB
               ->setAllowedMimeTypes([
                   'image/jpeg' => ['jpg', 'jpeg'],
                   'image/png' => ['png'],
                   'image/gif' => ['gif']
               ]);
        
        $userId = Session::get('user_id');
        $customName = "avatar_{$userId}";
        
        $result = $upload->upload($_FILES['avatar'], $customName, 'avatars');
        
        if ($result) {
            // Veritabanını güncelle
            User::where('id', $userId)->update(['avatar' => $result['url']]);
            
            return $this->success([
                'message' => 'Avatar yüklendi',
                'avatar_url' => $result['url']
            ]);
        } else {
            return $this->error('Yükleme hatası: ' . implode(', ', $upload->getErrors()));
        }
    }
}
```

### Çoklu Dosya Yükleme
```php
class GalleryController extends Controller
{
    public function uploadImages()
    {
        if (!$this->request->hasFiles()) {
            return $this->error('Dosya seçilmedi');
        }
        
        $upload = new Upload();
        $upload->setMaxSize(5 * 1024 * 1024) // 5MB
               ->setAllowedMimeTypes([
                   'image/jpeg' => ['jpg', 'jpeg'],
                   'image/png' => ['png'],
                   'image/webp' => ['webp']
               ]);
        
        $results = $upload->uploadMultiple($_FILES['images'], 'gallery');
        
        if (!empty($results)) {
            // Veritabanına kaydet
            foreach ($results as $result) {
                Gallery::create([
                    'name' => $result['original_name'],
                    'path' => $result['path'],
                    'url' => $result['url'],
                    'size' => $result['size']
                ]);
            }
            
            return $this->success([
                'message' => count($results) . ' dosya yüklendi',
                'files' => $results
            ]);
        } else {
            return $this->error('Yükleme hatası: ' . implode(', ', $upload->getErrors()));
        }
    }
}
```

### Belge Yükleme
```php
class DocumentController extends Controller
{
    public function upload()
    {
        $upload = new Upload();
        $upload->setMaxSize(10 * 1024 * 1024) // 10MB
               ->setAllowedMimeTypes([
                   'application/pdf' => ['pdf'],
                   'application/msword' => ['doc'],
                   'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
                   'text/plain' => ['txt']
               ]);
        
        $result = $upload->upload($_FILES['document'], null, 'documents');
        
        if ($result) {
            // Belge kaydı oluştur
            $document = Document::create([
                'title' => $this->request->post('title'),
                'filename' => $result['name'],
                'original_name' => $result['original_name'],
                'path' => $result['path'],
                'url' => $result['url'],
                'size' => $result['size'],
                'type' => $result['type'],
                'uploaded_by' => Session::get('user_id')
            ]);
            
            return $this->success($document);
        } else {
            return $this->error('Yükleme hatası: ' . implode(', ', $upload->getErrors()));
        }
    }
}
```

### AJAX Dosya Yükleme
```php
class AjaxUploadController extends Controller
{
    public function upload()
    {
        if (!$this->request->isAjax()) {
            return $this->error('Sadece AJAX istekleri kabul edilir');
        }
        
        $upload = new Upload();
        $upload->setUseDateDir(false) // Tarih dizini kullanma
               ->setMaxSize(1 * 1024 * 1024); // 1MB
        
        $result = $upload->upload($_FILES['file'], null, 'temp');
        
        if ($result) {
            return $this->json([
                'success' => true,
                'file' => $result
            ]);
        } else {
            return $this->json([
                'success' => false,
                'errors' => $upload->getErrors()
            ], 400);
        }
    }
}
```

### Güvenli Dosya Yükleme
```php
class SecureUploadController extends Controller
{
    public function upload()
    {
        // CSRF kontrolü
        if (!Security::validateCsrfToken()) {
            return $this->error('Güvenlik hatası', 403);
        }
        
        // Kullanıcı kontrolü
        if (!Session::has('user_id')) {
            return $this->error('Giriş gerekli', 401);
        }
        
        $upload = new Upload();
        
        // Sadece resim dosyalarına izin ver
        $upload->setAllowedMimeTypes([
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png']
        ]);
        
        // Küçük dosya boyutu
        $upload->setMaxSize(500 * 1024); // 500KB
        
        $result = $upload->upload($_FILES['image'], null, 'secure');
        
        if ($result) {
            // Dosya adını logla
            Logger::info('Dosya yüklendi', [
                'user_id' => Session::get('user_id'),
                'filename' => $result['name'],
                'size' => $result['size']
            ]);
            
            return $this->success($result);
        } else {
            Logger::warning('Dosya yükleme hatası', [
                'user_id' => Session::get('user_id'),
                'errors' => $upload->getErrors()
            ]);
            
            return $this->error('Yükleme başarısız');
        }
    }
}
```

### Toplu Dosya İşleme
```php
class BatchUploadController extends Controller
{
    public function processBatch()
    {
        $upload = new Upload();
        $upload->setMaxSize(2 * 1024 * 1024);
        
        $successCount = 0;
        $errorCount = 0;
        $results = [];
        
        foreach ($_FILES as $key => $file) {
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                continue; // Boş dosyaları atla
            }
            
            $result = $upload->upload($file, null, 'batch');
            
            if ($result) {
                $successCount++;
                $results[] = $result;
            } else {
                $errorCount++;
            }
        }
        
        return $this->json([
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'files' => $results,
            'errors' => $upload->getErrors()
        ]);
    }
}
```

### Dosya Türü Kısıtlama
```php
class RestrictedUploadController extends Controller
{
    public function uploadByType($type)
    {
        $upload = new Upload();
        
        switch ($type) {
            case 'image':
                $upload->setAllowedMimeTypes([
                    'image/jpeg' => ['jpg', 'jpeg'],
                    'image/png' => ['png'],
                    'image/gif' => ['gif']
                ]);
                $subDir = 'images';
                break;
                
            case 'document':
                $upload->setAllowedMimeTypes([
                    'application/pdf' => ['pdf'],
                    'application/msword' => ['doc'],
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx']
                ]);
                $subDir = 'documents';
                break;
                
            case 'video':
                $upload->setAllowedMimeTypes([
                    'video/mp4' => ['mp4'],
                    'video/webm' => ['webm']
                ]);
                $upload->setMaxSize(50 * 1024 * 1024); // 50MB
                $subDir = 'videos';
                break;
                
            default:
                return $this->error('Geçersiz dosya türü');
        }
        
        $result = $upload->upload($_FILES['file'], null, $subDir);
        
        if ($result) {
            return $this->success($result);
        } else {
            return $this->error('Yükleme hatası: ' . implode(', ', $upload->getErrors()));
        }
    }
}
```

## Güvenlik Özellikleri

### MIME Türü Kontrolü
- Gerçek MIME türü `mime_content_type()` ile kontrol edilir
- Dosya uzantısı ile MIME türü uyumluluğu kontrol edilir

### Zararlı İçerik Kontrolü
- PHP kodu tespiti
- JavaScript kodu tespiti
- Zararlı script etiketleri tespiti

### Dosya Adı Güvenliği
- Tehlikeli karakterler engellenir
- Sistem dosya adları engellenir
- Path traversal saldırıları engellenir

## İpuçları

1. **Boyut Sınırı**: Sunucu PHP ayarlarını da kontrol edin
2. **Dizin İzinleri**: Upload dizininin yazılabilir olduğundan emin olun
3. **Güvenlik**: Yüklenen dosyaları web erişiminin dışında tutun
4. **Performans**: Büyük dosyalar için chunk upload kullanın
5. **Temizlik**: Geçici dosyaları düzenli olarak temizleyin