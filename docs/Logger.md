# Logger Class

Log kayıtlarını yönetir. PSR-3 uyumlu log seviyeleri ve esnek yapılandırma sunar.

## Temel Kullanım

```php
// Basit log
Logger::info('Uygulama başlatıldı');
Logger::error('Veritabanı bağlantı hatası');

// Bağlam verileri ile
Logger::warning('Disk alanı azalıyor', ['free_space' => '500MB']);
```

## Log Seviyeleri

### Seviye Sabitleri
- `Logger::EMERGENCY` - Sistem kullanılamaz
- `Logger::ALERT` - Acil müdahale gerekli
- `Logger::CRITICAL` - Kritik durumlar
- `Logger::ERROR` - Hata durumları
- `Logger::WARNING` - Uyarı durumları
- `Logger::NOTICE` - Normal ama önemli olaylar
- `Logger::INFO` - Bilgilendirme mesajları
- `Logger::DEBUG` - Hata ayıklama bilgileri

### Seviye Metodları

#### emergency($message, $context = [])
Acil durum log kaydı.

```php
Logger::emergency('Sistem çöktü', ['error_code' => 500]);
```

#### alert($message, $context = [])
Uyarı log kaydı.

```php
Logger::alert('Güvenlik ihlali tespit edildi', ['ip' => '192.168.1.1']);
```

#### critical($message, $context = [])
Kritik log kaydı.

```php
Logger::critical('Veritabanı bağlantısı kesildi');
```

#### error($message, $context = [])
Hata log kaydı.

```php
Logger::error('Dosya yüklenemedi', ['file' => 'image.jpg']);
```

#### warning($message, $context = [])
Uyarı log kaydı.

```php
Logger::warning('Yavaş sorgu tespit edildi', ['query_time' => 2.5]);
```

#### notice($message, $context = [])
Bildirim log kaydı.

```php
Logger::notice('Kullanıcı giriş yaptı', ['user_id' => 123]);
```

#### info($message, $context = [])
Bilgi log kaydı.

```php
Logger::info('E-posta gönderildi', ['to' => 'user@example.com']);
```

#### debug($message, $context = [])
Hata ayıklama log kaydı.

```php
Logger::debug('Değişken değeri', ['variable' => $value]);
```

## Genel Log Metodu

### log($level, $message, $context = [])
Belirtilen seviyede log kaydı ekler.

```php
Logger::log(Logger::INFO, 'Özel mesaj');
Logger::log('error', 'Hata mesajı', ['details' => 'Detaylar']);
```

## Yapılandırma

### setLogPath($path)
Log dosyası yolunu ayarlar.

```php
Logger::setLogPath('/var/log/myapp');
Logger::setLogPath('C:\\logs\\myapp');
```

### setLogFormat($format)
Log dosyası adı formatını ayarlar.

```php
Logger::setLogFormat('Y-m-d'); // log-2023-12-25.log (varsayılan)
Logger::setLogFormat('Y-m'); // log-2023-12.log
Logger::setLogFormat('Y-W'); // log-2023-52.log (haftalık)
```

### setMessageFormat($format)
Log mesajı formatını ayarlar.

```php
Logger::setMessageFormat('[%datetime%] [%level%] %message%'); // Varsayılan
Logger::setMessageFormat('%datetime% - %level%: %message%');
Logger::setMessageFormat('[%level%] %message% (%datetime%)');
```

### setEnabledLevels($levels)
Aktif log seviyelerini ayarlar.

```php
// Sadece hata ve kritik loglar
Logger::setEnabledLevels([Logger::ERROR, Logger::CRITICAL]);

// Üretim ortamı için
Logger::setEnabledLevels([
    Logger::EMERGENCY,
    Logger::ALERT,
    Logger::CRITICAL,
    Logger::ERROR,
    Logger::WARNING
]);

// Geliştirme ortamı için (tümü)
Logger::setEnabledLevels([
    Logger::EMERGENCY,
    Logger::ALERT,
    Logger::CRITICAL,
    Logger::ERROR,
    Logger::WARNING,
    Logger::NOTICE,
    Logger::INFO,
    Logger::DEBUG
]);
```

## Örnek Kullanımlar

### Uygulama Başlatma
```php
class Application
{
    public function __construct()
    {
        // Log yapılandırması
        Logger::setLogPath(BASE_PATH . '/content/storage/logs');
        Logger::setMessageFormat('[%datetime%] [%level%] %message%');
        
        // Ortama göre log seviyeleri
        if (ENVIRONMENT === 'production') {
            Logger::setEnabledLevels([
                Logger::EMERGENCY,
                Logger::ALERT,
                Logger::CRITICAL,
                Logger::ERROR,
                Logger::WARNING
            ]);
        }
        
        Logger::info('Uygulama başlatıldı', ['environment' => ENVIRONMENT]);
    }
}
```

### Hata Yakalama
```php
class ErrorHandler
{
    public static function handleException($exception)
    {
        Logger::error('Yakalanmamış exception', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
    
    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        $level = match($errno) {
            E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR => Logger::ERROR,
            E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING => Logger::WARNING,
            E_NOTICE, E_USER_NOTICE => Logger::NOTICE,
            default => Logger::DEBUG
        };
        
        Logger::log($level, $errstr, [
            'file' => $errfile,
            'line' => $errline,
            'error_code' => $errno
        ]);
    }
}

// Hata yakalayıcıları kaydet
set_exception_handler([ErrorHandler::class, 'handleException']);
set_error_handler([ErrorHandler::class, 'handleError']);
```

### Kullanıcı İşlemleri
```php
class UserController extends Controller
{
    public function login()
    {
        $email = $this->request->post('email');
        
        Logger::info('Giriş denemesi', ['email' => $email]);
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            Logger::warning('Geçersiz e-posta ile giriş denemesi', ['email' => $email]);
            return $this->error('Geçersiz bilgiler');
        }
        
        if (!Security::verifyPassword($this->request->post('password'), $user->password)) {
            Logger::warning('Yanlış şifre ile giriş denemesi', [
                'user_id' => $user->id,
                'email' => $email,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            return $this->error('Geçersiz bilgiler');
        }
        
        Logger::info('Başarılı giriş', [
            'user_id' => $user->id,
            'email' => $email,
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
        
        Session::set('user_id', $user->id);
        return $this->redirect('/dashboard');
    }
    
    public function register()
    {
        $data = $this->request->post();
        
        try {
            $user = User::create($data);
            
            Logger::info('Yeni kullanıcı kaydı', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            
            return $this->success('Kayıt başarılı');
            
        } catch (Exception $e) {
            Logger::error('Kullanıcı kayıt hatası', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->error('Kayıt sırasında hata oluştu');
        }
    }
}
```

### Veritabanı İşlemleri
```php
class DatabaseLogger
{
    public static function logQuery($sql, $bindings = [], $time = 0)
    {
        if ($time > 1000) { // 1 saniyeden uzun sorgular
            Logger::warning('Yavaş sorgu tespit edildi', [
                'sql' => $sql,
                'bindings' => $bindings,
                'execution_time' => $time . 'ms'
            ]);
        } else {
            Logger::debug('Sorgu çalıştırıldı', [
                'sql' => $sql,
                'bindings' => $bindings,
                'execution_time' => $time . 'ms'
            ]);
        }
    }
    
    public static function logConnection($host, $database)
    {
        Logger::info('Veritabanı bağlantısı kuruldu', [
            'host' => $host,
            'database' => $database
        ]);
    }
    
    public static function logConnectionError($error)
    {
        Logger::critical('Veritabanı bağlantı hatası', [
            'error' => $error
        ]);
    }
}
```

### API İstekleri
```php
class ApiLogger
{
    public static function logRequest($method, $uri, $params = [])
    {
        Logger::info('API isteği', [
            'method' => $method,
            'uri' => $uri,
            'params' => $params,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    public static function logResponse($statusCode, $responseTime)
    {
        $level = match(true) {
            $statusCode >= 500 => Logger::ERROR,
            $statusCode >= 400 => Logger::WARNING,
            default => Logger::INFO
        };
        
        Logger::log($level, 'API yanıtı', [
            'status_code' => $statusCode,
            'response_time' => $responseTime . 'ms'
        ]);
    }
    
    public static function logRateLimit($ip, $endpoint)
    {
        Logger::warning('Rate limit aşıldı', [
            'ip' => $ip,
            'endpoint' => $endpoint
        ]);
    }
}
```

### Güvenlik Olayları
```php
class SecurityLogger
{
    public static function logSuspiciousActivity($type, $details = [])
    {
        Logger::alert('Şüpheli aktivite tespit edildi', array_merge([
            'type' => $type,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'timestamp' => time()
        ], $details));
    }
    
    public static function logFailedLogin($email, $attempts)
    {
        Logger::warning('Başarısız giriş denemesi', [
            'email' => $email,
            'attempts' => $attempts,
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
    }
    
    public static function logCsrfAttack($token)
    {
        Logger::alert('CSRF saldırısı tespit edildi', [
            'invalid_token' => $token,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'referer' => $_SERVER['HTTP_REFERER'] ?? null
        ]);
    }
}
```

### Sistem Monitörü
```php
class SystemMonitor
{
    public static function checkDiskSpace()
    {
        $freeBytes = disk_free_space('/');
        $totalBytes = disk_total_space('/');
        $usedPercent = (($totalBytes - $freeBytes) / $totalBytes) * 100;
        
        if ($usedPercent > 90) {
            Logger::critical('Disk alanı kritik seviyede', [
                'used_percent' => round($usedPercent, 2),
                'free_space' => self::formatBytes($freeBytes)
            ]);
        } elseif ($usedPercent > 80) {
            Logger::warning('Disk alanı azalıyor', [
                'used_percent' => round($usedPercent, 2),
                'free_space' => self::formatBytes($freeBytes)
            ]);
        }
    }
    
    public static function checkMemoryUsage()
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        Logger::debug('Bellek kullanımı', [
            'current_usage' => self::formatBytes($memoryUsage),
            'memory_limit' => $memoryLimit,
            'peak_usage' => self::formatBytes(memory_get_peak_usage(true))
        ]);
    }
    
    private static function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
```

### Log Analizi
```php
class LogAnalyzer
{
    public static function getErrorCount($date = null)
    {
        $date = $date ?: date('Y-m-d');
        $logFile = Logger::getLogFile($date);
        
        if (!file_exists($logFile)) {
            return 0;
        }
        
        $content = file_get_contents($logFile);
        return substr_count($content, '[ERROR]');
    }
    
    public static function getRecentErrors($limit = 10)
    {
        $logFile = Logger::getLogFile();
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES);
        $errors = [];
        
        foreach (array_reverse($lines) as $line) {
            if (strpos($line, '[ERROR]') !== false) {
                $errors[] = $line;
                
                if (count($errors) >= $limit) {
                    break;
                }
            }
        }
        
        return $errors;
    }
}
```

## Log Dosyası Formatı

### Varsayılan Format
```
[2023-12-25 14:30:15] [INFO] Uygulama başlatıldı
[2023-12-25 14:30:16] [ERROR] Veritabanı bağlantı hatası
[2023-12-25 14:30:17] [WARNING] Disk alanı azalıyor
```

### Bağlam Verileri ile
```
[2023-12-25 14:30:15] [INFO] Kullanıcı giriş yaptı {"user_id":123,"ip":"192.168.1.1"}
[2023-12-25 14:30:16] [ERROR] Dosya yüklenemedi {"file":"image.jpg","error":"Permission denied"}
```

## İpuçları

1. **Seviyeler**: Üretimde sadece gerekli seviyeleri aktif tutun
2. **Bağlam**: Hata ayıklama için yeterli bağlam verisi ekleyin
3. **Performans**: DEBUG loglarını üretimde kapatın
4. **Güvenlik**: Hassas bilgileri loglara yazmayın
5. **Rotasyon**: Log dosyalarını düzenli olarak temizleyin