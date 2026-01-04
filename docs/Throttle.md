# Throttle Class

İşlem sınırlama (Rate Limiting) sistemi. API endpoint'leri ve form gönderimlerini korur.

## Temel Kullanım

```php
// Giriş denemesi sınırla (5 deneme, 1 dakika)
if (Throttle::attempt('login', 5, 1)) {
    // İşlem yapılabilir
    $this->processLogin();
} else {
    // Limit aşıldı
    return $this->error('Çok fazla deneme. Lütfen bekleyin.');
}
```

## Ana Metodlar

### attempt($key, $maxAttempts = 5, $decayMinutes = 1)
İşlem sınırlaması kontrol eder.

```php
// API endpoint sınırlama
if (Throttle::attempt('api_call', 100, 60)) {
    // API çağrısı yap
} else {
    return $this->error('Rate limit aşıldı', 429);
}

// Form gönderimi sınırlama
if (Throttle::attempt('contact_form', 3, 10)) {
    // Form işle
} else {
    return $this->error('Çok sık form gönderimi');
}
```

### increase($key)
Deneme sayısını artırır.

```php
// Manuel artırma
$attempts = Throttle::increase('failed_login');
echo "Başarısız deneme: {$attempts}";
```

### decrease($key, $amount = 1)
Deneme sayısını azaltır.

```php
// Başarılı işlem sonrası azalt
Throttle::decrease('login_attempts', 2);
```

## Bilgi Metodları

### remaining($key, $maxAttempts)
Kalan deneme sayısını döndürür.

```php
$remaining = Throttle::remaining('api_call', 100);
echo "Kalan API çağrısı: {$remaining}";
```

### availableIn($key)
Sıfırlanma süresini döndürür (saniye).

```php
$seconds = Throttle::availableIn('login');
if ($seconds > 0) {
    echo "Tekrar deneyebilirsiniz: {$seconds} saniye sonra";
}
```

### clear($key)
Throttle verilerini temizler.

```php
// Başarılı giriş sonrası temizle
Throttle::clear('login');
```

## Örnek Kullanımlar

### Login Koruması
```php
class AuthController extends Controller
{
    public function login()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $email = $this->request->post('email');
        
        // IP bazlı sınırlama
        if (!Throttle::attempt("login_ip_{$ip}", 10, 15)) {
            return $this->error('IP adresiniz geçici olarak engellendi', 429);
        }
        
        // Email bazlı sınırlama
        if (!Throttle::attempt("login_email_{$email}", 5, 5)) {
            return $this->error('Bu e-posta için çok fazla deneme', 429);
        }
        
        $user = User::where('email', $email)->first();
        
        if (!$user || !Security::verifyPassword($this->request->post('password'), $user->password)) {
            // Başarısız deneme logla
            Logger::warning('Başarısız giriş', [
                'email' => $email,
                'ip' => $ip,
                'remaining_ip' => Throttle::remaining("login_ip_{$ip}", 10),
                'remaining_email' => Throttle::remaining("login_email_{$email}", 5)
            ]);
            
            return $this->error('Geçersiz bilgiler');
        }
        
        // Başarılı giriş - throttle temizle
        Throttle::clear("login_email_{$email}");
        
        Session::set('user_id', $user->id);
        return $this->redirect('/dashboard');
    }
}
```

### API Rate Limiting
```php
class ApiMiddleware
{
    public function handle()
    {
        $apiKey = $this->getApiKey();
        $endpoint = $_SERVER['REQUEST_URI'];
        
        // API key bazlı sınırlama
        if (!Throttle::attempt("api_{$apiKey}", 1000, 60)) {
            return $this->rateLimitResponse($apiKey, 1000);
        }
        
        // Endpoint bazlı sınırlama
        if (!Throttle::attempt("endpoint_{$endpoint}", 100, 60)) {
            return $this->rateLimitResponse($endpoint, 100);
        }
        
        return true;
    }
    
    private function rateLimitResponse($key, $limit)
    {
        $remaining = Throttle::remaining($key, $limit);
        $resetTime = Throttle::availableIn($key);
        
        header('X-RateLimit-Limit: ' . $limit);
        header('X-RateLimit-Remaining: ' . $remaining);
        header('X-RateLimit-Reset: ' . (time() + $resetTime));
        
        http_response_code(429);
        echo json_encode([
            'error' => 'Rate limit exceeded',
            'retry_after' => $resetTime
        ]);
        exit;
    }
    
    private function getApiKey()
    {
        return $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? 'anonymous';
    }
}
```

### Form Spam Koruması
```php
class ContactController extends Controller
{
    public function store()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // IP bazlı form sınırlaması (3 form/10 dakika)
        if (!Throttle::attempt("contact_form_{$ip}", 3, 10)) {
            $waitTime = Throttle::availableIn("contact_form_{$ip}");
            $minutes = ceil($waitTime / 60);
            
            return $this->error("Çok sık mesaj gönderiyorsunuz. {$minutes} dakika sonra tekrar deneyin.");
        }
        
        // Email bazlı sınırlama (1 form/5 dakika)
        $email = $this->request->post('email');
        if (!Throttle::attempt("contact_email_{$email}", 1, 5)) {
            return $this->error('Bu e-posta adresi için çok sık mesaj gönderimi.');
        }
        
        // Form işle
        $this->processContactForm();
        
        return $this->success('Mesajınız gönderildi');
    }
}
```

### Download Sınırlama
```php
class DownloadController extends Controller
{
    public function download($fileId)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $userId = Session::get('user_id');
        
        // Anonim kullanıcılar için IP sınırlaması
        if (!$userId) {
            if (!Throttle::attempt("download_ip_{$ip}", 5, 60)) {
                return $this->error('İndirme limitiniz doldu. 1 saat sonra tekrar deneyin.');
            }
        } else {
            // Kayıtlı kullanıcılar için daha yüksek limit
            if (!Throttle::attempt("download_user_{$userId}", 20, 60)) {
                return $this->error('Saatlik indirme limitiniz doldu.');
            }
        }
        
        $file = File::find($fileId);
        
        if (!$file) {
            return $this->notFound();
        }
        
        // İndirme logla
        Logger::info('Dosya indirildi', [
            'file_id' => $fileId,
            'user_id' => $userId,
            'ip' => $ip,
            'remaining' => $userId 
                ? Throttle::remaining("download_user_{$userId}", 20)
                : Throttle::remaining("download_ip_{$ip}", 5)
        ]);
        
        return $this->response->download($file->path, $file->name);
    }
}
```

### Password Reset Koruması
```php
class PasswordResetController extends Controller
{
    public function sendResetLink()
    {
        $email = $this->request->post('email');
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // Email bazlı sınırlama (3 istek/saat)
        if (!Throttle::attempt("password_reset_{$email}", 3, 60)) {
            return $this->error('Bu e-posta için çok fazla şifre sıfırlama isteği.');
        }
        
        // IP bazlı sınırlama (10 istek/saat)
        if (!Throttle::attempt("password_reset_ip_{$ip}", 10, 60)) {
            return $this->error('IP adresiniz için limit aşıldı.');
        }
        
        $user = User::where('email', $email)->first();
        
        if ($user) {
            $token = Security::generateToken();
            PasswordReset::create([
                'email' => $email,
                'token' => $token,
                'expires_at' => date('Y-m-d H:i:s', time() + 3600)
            ]);
            
            PasswordResetMailer::send($user, $token);
        }
        
        // Güvenlik için her zaman aynı mesaj
        return $this->success('Şifre sıfırlama linki gönderildi (varsa)');
    }
}
```

### Search Throttling
```php
class SearchController extends Controller
{
    public function search()
    {
        $query = $this->request->get('q');
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // Boş sorgu kontrolü
        if (empty($query) || strlen($query) < 3) {
            return $this->error('En az 3 karakter girin');
        }
        
        // Arama sınırlaması (30 arama/dakika)
        if (!Throttle::attempt("search_{$ip}", 30, 1)) {
            $waitTime = Throttle::availableIn("search_{$ip}");
            
            return $this->json([
                'error' => 'Çok fazla arama yaptınız',
                'retry_after' => $waitTime
            ], 429);
        }
        
        $results = $this->performSearch($query);
        
        return $this->json([
            'results' => $results,
            'remaining_searches' => Throttle::remaining("search_{$ip}", 30)
        ]);
    }
}
```

### Middleware Integration
```php
class ThrottleMiddleware
{
    private $limits = [
        'api' => ['attempts' => 60, 'minutes' => 1],
        'web' => ['attempts' => 100, 'minutes' => 1],
        'admin' => ['attempts' => 200, 'minutes' => 1]
    ];
    
    public function handle($type = 'web')
    {
        $config = $this->limits[$type] ?? $this->limits['web'];
        $key = $this->getThrottleKey($type);
        
        if (!Throttle::attempt($key, $config['attempts'], $config['minutes'])) {
            $this->handleRateLimit($key, $config);
            return false;
        }
        
        return true;
    }
    
    private function getThrottleKey($type)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        return "{$type}_{$ip}_" . md5($userAgent);
    }
    
    private function handleRateLimit($key, $config)
    {
        $remaining = Throttle::remaining($key, $config['attempts']);
        $resetTime = Throttle::availableIn($key);
        
        // AJAX isteği ise JSON yanıt
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            http_response_code(429);
            echo json_encode([
                'error' => 'Rate limit exceeded',
                'remaining' => $remaining,
                'reset_in' => $resetTime
            ]);
        } else {
            // Normal istek ise hata sayfası
            http_response_code(429);
            echo 'Rate limit aşıldı. Lütfen bekleyin.';
        }
        
        exit;
    }
}
```

### Advanced Throttling
```php
class AdvancedThrottle
{
    public static function smartThrottle($action, $identifier = null)
    {
        $identifier = $identifier ?: $_SERVER['REMOTE_ADDR'];
        
        $limits = [
            'login' => ['attempts' => 5, 'minutes' => 15],
            'register' => ['attempts' => 3, 'minutes' => 60],
            'api_call' => ['attempts' => 1000, 'minutes' => 60],
            'search' => ['attempts' => 50, 'minutes' => 1],
            'download' => ['attempts' => 10, 'minutes' => 60],
            'contact' => ['attempts' => 2, 'minutes' => 30]
        ];
        
        $config = $limits[$action] ?? ['attempts' => 10, 'minutes' => 1];
        $key = "{$action}_{$identifier}";
        
        return Throttle::attempt($key, $config['attempts'], $config['minutes']);
    }
    
    public static function getThrottleInfo($action, $identifier = null)
    {
        $identifier = $identifier ?: $_SERVER['REMOTE_ADDR'];
        $key = "{$action}_{$identifier}";
        
        $limits = [
            'login' => 5,
            'api_call' => 1000,
            'search' => 50
        ];
        
        $maxAttempts = $limits[$action] ?? 10;
        
        return [
            'remaining' => Throttle::remaining($key, $maxAttempts),
            'reset_in' => Throttle::availableIn($key),
            'max_attempts' => $maxAttempts
        ];
    }
}

// Kullanım
if (AdvancedThrottle::smartThrottle('login')) {
    // Giriş işlemi
} else {
    $info = AdvancedThrottle::getThrottleInfo('login');
    return $this->error("Limit aşıldı. {$info['reset_in']} saniye bekleyin.");
}
```

### User-Based Throttling
```php
class UserThrottle
{
    public static function checkUserAction($action, $userId = null)
    {
        $userId = $userId ?: Session::get('user_id');
        
        if (!$userId) {
            // Anonim kullanıcı için IP bazlı
            return Throttle::attempt($action, 5, 10);
        }
        
        // Kullanıcı rolüne göre farklı limitler
        $user = User::find($userId);
        $limits = self::getLimitsForRole($user->role ?? 'user');
        
        $config = $limits[$action] ?? ['attempts' => 10, 'minutes' => 1];
        
        return Throttle::attempt(
            "{$action}_user_{$userId}", 
            $config['attempts'], 
            $config['minutes']
        );
    }
    
    private static function getLimitsForRole($role)
    {
        return match($role) {
            'admin' => [
                'api_call' => ['attempts' => 10000, 'minutes' => 60],
                'search' => ['attempts' => 1000, 'minutes' => 1]
            ],
            'premium' => [
                'api_call' => ['attempts' => 5000, 'minutes' => 60],
                'search' => ['attempts' => 500, 'minutes' => 1]
            ],
            'user' => [
                'api_call' => ['attempts' => 1000, 'minutes' => 60],
                'search' => ['attempts' => 100, 'minutes' => 1]
            ],
            default => [
                'api_call' => ['attempts' => 100, 'minutes' => 60],
                'search' => ['attempts' => 20, 'minutes' => 1]
            ]
        };
    }
}
```

### Webhook Throttling
```php
class WebhookController extends Controller
{
    public function handle($provider)
    {
        $signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
        
        // Provider bazlı sınırlama
        if (!Throttle::attempt("webhook_{$provider}", 100, 1)) {
            Logger::warning('Webhook rate limit', ['provider' => $provider]);
            return $this->error('Rate limit exceeded', 429);
        }
        
        // Signature kontrolü
        if (!$this->validateSignature($signature, $provider)) {
            // Geçersiz signature için daha sıkı limit
            if (!Throttle::attempt("webhook_invalid_{$provider}", 5, 60)) {
                Logger::alert('Webhook güvenlik ihlali', [
                    'provider' => $provider,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ]);
                return $this->error('Security violation', 403);
            }
            
            return $this->error('Invalid signature', 401);
        }
        
        // Webhook işle
        $this->processWebhook($provider);
        
        return $this->success('Webhook processed');
    }
}
```

### Brute Force Koruması
```php
class BruteForceProtection
{
    public static function checkBruteForce($action, $maxAttempts = 5)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // IP + User Agent kombinasyonu
        $fingerprint = md5($ip . $userAgent);
        $key = "{$action}_brute_{$fingerprint}";
        
        if (!Throttle::attempt($key, $maxAttempts, 60)) {
            // Brute force tespit edildi
            Logger::alert('Brute force saldırısı tespit edildi', [
                'action' => $action,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'attempts' => $maxAttempts
            ]);
            
            // IP'yi geçici olarak engelle
            self::blockIP($ip, 3600); // 1 saat
            
            return false;\n        }\n        \n        return true;\n    }\n    \n    private static function blockIP($ip, $duration)\n    {\n        $key = \"blocked_ip_{$ip}\";\n        Cache::set($key, true, $duration);\n        \n        Logger::info('IP engellendi', [\n            'ip' => $ip,\n            'duration' => $duration . ' saniye'\n        ]);\n    }\n    \n    public static function isIPBlocked($ip)\n    {\n        return Cache::has(\"blocked_ip_{$ip}\");\n    }\n}\n```\n\n### Rate Limit Headers\n```php\nclass RateLimitHeaders\n{\n    public static function addHeaders($key, $maxAttempts, $decayMinutes)\n    {\n        $remaining = Throttle::remaining($key, $maxAttempts);\n        $resetTime = time() + Throttle::availableIn($key);\n        \n        header(\"X-RateLimit-Limit: {$maxAttempts}\");\n        header(\"X-RateLimit-Remaining: {$remaining}\");\n        header(\"X-RateLimit-Reset: {$resetTime}\");\n        \n        // Limit aşıldıysa Retry-After header'ı ekle\n        if ($remaining <= 0) {\n            $retryAfter = Throttle::availableIn($key);\n            header(\"Retry-After: {$retryAfter}\");\n        }\n    }\n}\n```\n\n## İpuçları\n\n1. **Anahtar Seçimi**: Benzersiz ve anlamlı anahtarlar kullanın\n2. **Limit Ayarları**: Kullanıcı deneyimini bozmayacak limitler seçin\n3. **Logging**: Limit aşımlarını logla ve izleyin\n4. **Temizlik**: Başarılı işlemler sonrası throttle temizleyin\n5. **Güvenlik**: Brute force saldırıları için sıkı limitler uygulayın