# Cryptor Class

OpenSSL ile şifreleme işlemlerini yönetir. Güvenli veri şifreleme ve çözme işlemleri sunar.

## Temel Kullanım

```php
$cryptor = new Cryptor();

// Veri şifrele
$encrypted = $cryptor->encrypt('Gizli veri');

// Veri çöz
$decrypted = $cryptor->decrypt($encrypted);
```

## Yapılandırıcı

### __construct($key = null, $method = null, $options = 0)

```php
// Varsayılan ayarlarla
$cryptor = new Cryptor();

// Özel anahtar ile
$cryptor = new Cryptor('my-secret-key-32-characters-long');

// Özel metod ile
$cryptor = new Cryptor('my-key', 'AES-128-CBC');

// Tüm parametrelerle
$cryptor = new Cryptor('my-key', 'AES-256-CBC', OPENSSL_RAW_DATA);
```

### Yapılandırma Kaynakları
1. **CRYPTOR_KEY sabiti** (db_config.php'den)
2. **Constructor parametresi**
3. **Varsayılan anahtar** (güvenlik uyarısı ile)

## Şifreleme İşlemleri

### encrypt($data)
Veriyi şifreler.

```php
// String şifreleme
$encrypted = $cryptor->encrypt('Merhaba Dünya');

// Array şifreleme
$encrypted = $cryptor->encrypt(['name' => 'John', 'age' => 30]);

// Object şifreleme
$user = new stdClass();
$user->name = 'Jane';
$encrypted = $cryptor->encrypt($user);

// Sayı şifreleme
$encrypted = $cryptor->encrypt(12345);
```

### decrypt($data, $unserialize = true)
Şifrelenmiş veriyi çözer.

```php
// Otomatik unserialize (varsayılan)
$decrypted = $cryptor->decrypt($encrypted);

// Manuel unserialize kontrolü
$decrypted = $cryptor->decrypt($encrypted, false);
if ($cryptor->isSerialized($decrypted)) {
    $decrypted = unserialize($decrypted);
}
```

## Yapılandırma Metodları

### setKey($key)
Şifreleme anahtarını ayarlar.

```php
$cryptor->setKey('new-encryption-key-32-chars-long');
```

### setMethod($method)
Şifreleme metodunu ayarlar.

```php
$cryptor->setMethod('AES-128-CBC');
$cryptor->setMethod('AES-256-GCM');
```

### setOptions($options)
Şifreleme seçeneklerini ayarlar.

```php
$cryptor->setOptions(OPENSSL_RAW_DATA);
$cryptor->setOptions(0); // Varsayılan
```

## Statik Metodlar

### getSupportedMethods()
Desteklenen şifreleme metodlarını döndürür.

```php
$methods = Cryptor::getSupportedMethods();
foreach ($methods as $method) {
    echo $method . "\n";
}
```

### generateKey($length = 32)
Güvenli şifreleme anahtarı oluşturur.

```php
$key = Cryptor::generateKey(); // 32 karakter
$shortKey = Cryptor::generateKey(16); // 16 karakter
$longKey = Cryptor::generateKey(64); // 64 karakter
```

## Örnek Kullanımlar

### Temel Şifreleme
```php
class DataEncryption
{
    private $cryptor;
    
    public function __construct()
    {
        $this->cryptor = new Cryptor();
    }
    
    public function encryptUserData($userData)
    {
        return $this->cryptor->encrypt($userData);
    }
    
    public function decryptUserData($encryptedData)
    {
        try {
            return $this->cryptor->decrypt($encryptedData);
        } catch (Exception $e) {
            Logger::error('Decryption failed: ' . $e->getMessage());
            return null;
        }
    }
}
```

### Session Verisi Şifreleme
```php
class SecureSession
{
    private static $cryptor;
    
    public static function init()
    {
        self::$cryptor = new Cryptor();
    }
    
    public static function set($key, $value)
    {
        $encrypted = self::$cryptor->encrypt($value);
        $_SESSION[$key] = $encrypted;
    }
    
    public static function get($key, $default = null)
    {
        if (!isset($_SESSION[$key])) {
            return $default;
        }
        
        try {
            return self::$cryptor->decrypt($_SESSION[$key]);
        } catch (Exception $e) {
            return $default;
        }
    }
}

// Kullanım
SecureSession::init();
SecureSession::set('user_data', ['id' => 123, 'role' => 'admin']);
$userData = SecureSession::get('user_data');
```

### Cookie Şifreleme
```php
class SecureCookie
{
    private static $cryptor;
    
    public static function init()
    {
        self::$cryptor = new Cryptor();
    }
    
    public static function set($name, $value, $expire = 0)
    {
        $encrypted = self::$cryptor->encrypt($value);
        return setcookie($name, $encrypted, $expire, '/', '', true, true);
    }
    
    public static function get($name, $default = null)
    {
        if (!isset($_COOKIE[$name])) {
            return $default;
        }
        
        try {
            return self::$cryptor->decrypt($_COOKIE[$name]);
        } catch (Exception $e) {
            return $default;
        }
    }
}
```

### Dosya Şifreleme
```php
class FileEncryption
{
    private $cryptor;
    
    public function __construct()
    {
        $this->cryptor = new Cryptor();
    }
    
    public function encryptFile($inputFile, $outputFile)
    {
        if (!file_exists($inputFile)) {
            throw new Exception('Input file not found');
        }
        
        $data = file_get_contents($inputFile);
        $encrypted = $this->cryptor->encrypt($data);
        
        return file_put_contents($outputFile, $encrypted) !== false;
    }
    
    public function decryptFile($inputFile, $outputFile)
    {
        if (!file_exists($inputFile)) {
            throw new Exception('Input file not found');
        }
        
        $encrypted = file_get_contents($inputFile);
        $decrypted = $this->cryptor->decrypt($encrypted, false);
        
        return file_put_contents($outputFile, $decrypted) !== false;
    }
}

// Kullanım
$fileEncryption = new FileEncryption();
$fileEncryption->encryptFile('/path/to/secret.txt', '/path/to/secret.enc');
$fileEncryption->decryptFile('/path/to/secret.enc', '/path/to/secret_decrypted.txt');
```

### API Token Şifreleme
```php
class ApiTokenManager
{
    private $cryptor;
    
    public function __construct()
    {
        $this->cryptor = new Cryptor();
    }
    
    public function generateToken($userId, $expiresAt)
    {
        $tokenData = [
            'user_id' => $userId,
            'expires_at' => $expiresAt,
            'created_at' => time(),
            'random' => bin2hex(random_bytes(16))
        ];
        
        return $this->cryptor->encrypt($tokenData);
    }
    
    public function validateToken($token)
    {
        try {
            $tokenData = $this->cryptor->decrypt($token);
            
            // Süre kontrolü
            if ($tokenData['expires_at'] < time()) {
                return false;
            }
            
            return $tokenData;
        } catch (Exception $e) {
            return false;
        }
    }
}

// Kullanım
$tokenManager = new ApiTokenManager();
$token = $tokenManager->generateToken(123, time() + 3600); // 1 saat
$tokenData = $tokenManager->validateToken($token);
```

### Database Field Encryption
```php
class EncryptedModel extends Model
{
    protected $encrypted = ['ssn', 'credit_card', 'phone'];
    private static $cryptor;
    
    public function __construct()
    {
        parent::__construct();
        
        if (!self::$cryptor) {
            self::$cryptor = new Cryptor();
        }
    }
    
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encrypted) && $value !== null) {
            $value = self::$cryptor->encrypt($value);
        }
        
        return parent::setAttribute($key, $value);
    }
    
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        
        if (in_array($key, $this->encrypted) && $value !== null) {
            try {
                $value = self::$cryptor->decrypt($value);
            } catch (Exception $e) {
                // Decryption failed, return null or original value
                $value = null;
            }
        }
        
        return $value;
    }
}

// Kullanım
class User extends EncryptedModel
{
    protected $table = 'users';
    protected $encrypted = ['ssn', 'phone', 'address'];
}

$user = new User();
$user->name = 'John Doe';
$user->ssn = '123-45-6789'; // Otomatik şifrelenir
$user->save();

$user = User::find(1);
echo $user->ssn; // Otomatik çözülür
```

### Configuration Encryption
```php
class ConfigManager
{
    private static $cryptor;
    
    public static function init()
    {
        self::$cryptor = new Cryptor();
    }
    
    public static function setSecure($key, $value)
    {
        $encrypted = self::$cryptor->encrypt($value);
        return file_put_contents(
            "config/{$key}.enc", 
            $encrypted
        ) !== false;
    }
    
    public static function getSecure($key, $default = null)
    {
        $file = "config/{$key}.enc";
        
        if (!file_exists($file)) {
            return $default;
        }
        
        try {
            $encrypted = file_get_contents($file);
            return self::$cryptor->decrypt($encrypted);
        } catch (Exception $e) {
            return $default;
        }
    }
}

// Kullanım
ConfigManager::init();
ConfigManager::setSecure('database_password', 'super_secret_password');
$password = ConfigManager::getSecure('database_password');
```

### Backup Encryption
```php
class BackupManager
{
    private $cryptor;
    
    public function __construct()
    {
        $this->cryptor = new Cryptor();
    }
    
    public function createEncryptedBackup($data, $filename)
    {
        // Veriyi JSON'a çevir
        $jsonData = json_encode($data);
        
        // Şifrele
        $encrypted = $this->cryptor->encrypt($jsonData);
        
        // Dosyaya kaydet
        $backupPath = "backups/{$filename}.backup";
        return file_put_contents($backupPath, $encrypted) !== false;
    }
    
    public function restoreFromBackup($filename)
    {
        $backupPath = "backups/{$filename}.backup";
        
        if (!file_exists($backupPath)) {
            throw new Exception('Backup file not found');
        }
        
        // Dosyayı oku
        $encrypted = file_get_contents($backupPath);
        
        // Çöz
        $jsonData = $this->cryptor->decrypt($encrypted, false);
        
        // JSON'dan array'e çevir
        return json_decode($jsonData, true);
    }
}
```

## Güvenlik Notları

### Anahtar Yönetimi
```php
// ✅ Doğru: Güvenli anahtar
define('CRYPTOR_KEY', 'your-32-character-secret-key-here');

// ❌ Yanlış: Zayıf anahtar
define('CRYPTOR_KEY', '123456');
```

### Hata Yönetimi
```php
try {
    $decrypted = $cryptor->decrypt($encrypted);
} catch (Exception $e) {
    // Hata logla ama kullanıcıya detay verme
    Logger::error('Decryption failed: ' . $e->getMessage());
    
    // Genel hata mesajı
    throw new Exception('Data could not be processed');
}
```

### IV (Initialization Vector)
- Her şifreleme işleminde otomatik olarak rastgele IV oluşturulur
- IV şifrelenmiş verinin başına eklenir
- Aynı veri farklı şifrelenmiş sonuçlar üretir

## İpuçları

1. **Anahtar**: 32 karakter uzunluğunda güvenli anahtar kullanın
2. **Hata Yönetimi**: Decryption hatalarını yakala ve logla
3. **Performance**: Büyük veriler için dosya şifreleme kullanın
4. **Güvenlik**: Şifreleme anahtarını kod içinde saklamayın
5. **Backup**: Şifreleme anahtarını güvenli yerde yedekleyin