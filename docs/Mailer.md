# Mailer Class

E-posta gönderme işlemlerini yönetir. PHPMailer tabanlı güvenli e-posta sistemi sunar.

## Temel Kullanım

```php
$mailer = new Mailer();

// Basit e-posta
$mailer->send('user@example.com', 'Konu', 'Mesaj içeriği');

// HTML e-posta
$mailer->send('user@example.com', 'Konu', '<h1>HTML Mesaj</h1>', true);
```

## Yapılandırıcı

### __construct()
Mailer'ı otomatik yapılandırır.

```php
$mailer = new Mailer();
```

Yapılandırma Sword::getData() metodundan alınır:
- `mail_driver` - E-posta sürücüsü (smtp)
- `mail_host` - SMTP sunucusu
- `mail_port` - SMTP portu (587)
- `mail_username` - SMTP kullanıcı adı
- `mail_password` - SMTP şifresi
- `mail_encryption` - Şifreleme (STARTTLS)
- `mail_from_email` - Gönderen e-posta
- `mail_from_name` - Gönderen adı

## E-posta Gönderme

### send($to, $subject, $body, $isHTML = true)
E-posta gönderir.

```php
// Tek alıcı
$success = $mailer->send('user@example.com', 'Hoş Geldiniz', $htmlContent);

// Birden fazla alıcı (array)
$recipients = [
    'user1@example.com',
    'user2@example.com' => 'John Doe',
    'user3@example.com' => 'Jane Smith'
];
$mailer->send($recipients, 'Toplu Mesaj', $content);

// Düz metin
$mailer->send('user@example.com', 'Konu', 'Düz metin mesaj', false);
```

## Ek Özellikler

### attach($path, $name = '')
Dosya eki ekler.

```php
$mailer->attach('/path/to/file.pdf')
       ->attach('/path/to/image.jpg', 'resim.jpg')
       ->send('user@example.com', 'Ekli Dosya', $content);
```

### cc($email, $name = '')
CC (Carbon Copy) ekler.

```php
$mailer->cc('manager@example.com', 'Yönetici')
       ->send('user@example.com', 'Konu', $content);
```

### bcc($email, $name = '')
BCC (Blind Carbon Copy) ekler.

```php
$mailer->bcc('admin@example.com')
       ->bcc('backup@example.com', 'Yedek')
       ->send('user@example.com', 'Konu', $content);
```

## PHPMailer Erişimi

### getMailer()
PHPMailer örneğini döndürür.

```php
$phpMailer = $mailer->getMailer();

// Gelişmiş ayarlar
$phpMailer->addReplyTo('noreply@example.com', 'No Reply');
$phpMailer->Priority = 1; // Yüksek öncelik
$phpMailer->addCustomHeader('X-Custom-Header', 'Value');
```

## Örnek Kullanımlar

### Hoş Geldin E-postası
```php
class WelcomeMailer
{
    public static function send($user)
    {
        $mailer = new Mailer();
        
        $subject = 'Hoş Geldiniz ' . $user->name;
        
        $body = "
        <h1>Merhaba {$user->name}!</h1>
        <p>Sitemize hoş geldiniz. Hesabınız başarıyla oluşturuldu.</p>
        <p>
            <strong>E-posta:</strong> {$user->email}<br>
            <strong>Kayıt Tarihi:</strong> " . date('d.m.Y H:i') . "
        </p>
        <p>
            <a href='" . Sword::url('profile') . "' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                Profilimi Görüntüle
            </a>
        </p>
        <p>İyi günler!</p>
        ";
        
        return $mailer->send($user->email, $subject, $body);
    }
}

// Kullanım
$user = User::create($userData);
WelcomeMailer::send($user);
```

### Şifre Sıfırlama
```php
class PasswordResetMailer
{
    public static function send($user, $token)
    {
        $mailer = new Mailer();
        
        $resetUrl = Sword::url('password/reset/' . $token);
        
        $subject = 'Şifre Sıfırlama Talebi';
        
        $body = "
        <h2>Şifre Sıfırlama</h2>
        <p>Merhaba {$user->name},</p>
        <p>Hesabınız için şifre sıfırlama talebinde bulundunuz.</p>
        <p>
            <a href='{$resetUrl}' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                Şifremi Sıfırla
            </a>
        </p>
        <p>Bu link 1 saat geçerlidir.</p>
        <p>Eğer bu talebi siz yapmadıysanız, bu e-postayı görmezden gelebilirsiniz.</p>
        ";
        
        return $mailer->send($user->email, $subject, $body);
    }
}
```

### Sipariş Onayı
```php
class OrderConfirmationMailer
{
    public static function send($order)
    {
        $mailer = new Mailer();
        
        $subject = "Sipariş Onayı - #{$order->id}";
        
        // Sipariş detayları
        $itemsHtml = '';
        foreach ($order->items as $item) {
            $itemsHtml .= "
            <tr>
                <td>{$item->product_name}</td>
                <td>{$item->quantity}</td>
                <td>{$item->price} TL</td>
                <td>" . ($item->quantity * $item->price) . " TL</td>
            </tr>";
        }
        
        $body = "
        <h2>Sipariş Onayı</h2>
        <p>Merhaba {$order->customer_name},</p>
        <p>Siparişiniz başarıyla alındı.</p>
        
        <h3>Sipariş Detayları</h3>
        <table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
            <thead>
                <tr style='background: #f8f9fa;'>
                    <th>Ürün</th>
                    <th>Adet</th>
                    <th>Birim Fiyat</th>
                    <th>Toplam</th>
                </tr>
            </thead>
            <tbody>
                {$itemsHtml}
            </tbody>
            <tfoot>
                <tr style='background: #e9ecef; font-weight: bold;'>
                    <td colspan='3'>Genel Toplam</td>
                    <td>{$order->total} TL</td>
                </tr>
            </tfoot>
        </table>
        
        <h3>Teslimat Bilgileri</h3>
        <p>
            <strong>Adres:</strong> {$order->shipping_address}<br>
            <strong>Tahmini Teslimat:</strong> " . date('d.m.Y', strtotime('+3 days')) . "
        </p>
        
        <p>Siparişinizi takip etmek için: <a href='" . Sword::url('orders/' . $order->id) . "'>Buraya tıklayın</a></p>
        ";
        
        return $mailer->send($order->customer_email, $subject, $body);
    }
}
```

### Toplu E-posta
```php
class NewsletterMailer
{
    public static function sendToSubscribers($subject, $content)
    {
        $subscribers = Newsletter::where('active', 1)->get();
        $successCount = 0;
        $failCount = 0;
        
        foreach ($subscribers as $subscriber) {
            $mailer = new Mailer();
            
            // Kişiselleştirme
            $personalizedContent = str_replace(
                ['{{name}}', '{{email}}'],
                [$subscriber->name, $subscriber->email],
                $content
            );
            
            // Unsubscribe linki ekle
            $unsubscribeUrl = Sword::url('newsletter/unsubscribe/' . $subscriber->token);
            $personalizedContent .= "
            <hr>
            <p style='font-size: 12px; color: #666;'>
                Bu e-postayı almak istemiyorsanız 
                <a href='{$unsubscribeUrl}'>buraya tıklayarak</a> 
                aboneliğinizi iptal edebilirsiniz.
            </p>";
            
            if ($mailer->send($subscriber->email, $subject, $personalizedContent)) {
                $successCount++;
            } else {
                $failCount++;
                Logger::error('Newsletter gönderme hatası', [
                    'subscriber_id' => $subscriber->id,
                    'email' => $subscriber->email
                ]);
            }
            
            // Rate limiting için kısa bekleme
            usleep(100000); // 0.1 saniye
        }
        
        Logger::info('Newsletter gönderildi', [
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'total_subscribers' => count($subscribers)
        ]);
        
        return ['success' => $successCount, 'failed' => $failCount];
    }
}
```

### E-posta Şablonları
```php
class EmailTemplate
{
    private static $templates = [
        'welcome' => [
            'subject' => 'Hoş Geldiniz {{name}}!',
            'body' => 'templates/emails/welcome.html'
        ],
        'password_reset' => [
            'subject' => 'Şifre Sıfırlama',
            'body' => 'templates/emails/password_reset.html'
        ],
        'order_confirmation' => [
            'subject' => 'Sipariş Onayı - #{{order_id}}',
            'body' => 'templates/emails/order_confirmation.html'
        ]
    ];
    
    public static function send($templateName, $to, $variables = [])
    {
        if (!isset(self::$templates[$templateName])) {
            throw new Exception("Template not found: $templateName");
        }
        
        $template = self::$templates[$templateName];
        
        // Subject'i işle
        $subject = self::processVariables($template['subject'], $variables);
        
        // Body'yi yükle ve işle
        $bodyPath = BASE_PATH . '/' . $template['body'];
        if (!file_exists($bodyPath)) {
            throw new Exception("Template file not found: $bodyPath");
        }
        
        $body = file_get_contents($bodyPath);
        $body = self::processVariables($body, $variables);
        
        // Gönder
        $mailer = new Mailer();
        return $mailer->send($to, $subject, $body);
    }
    
    private static function processVariables($content, $variables)
    {
        foreach ($variables as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }
        
        return $content;
    }
}

// Kullanım
EmailTemplate::send('welcome', 'user@example.com', [
    'name' => 'John Doe',
    'activation_url' => Sword::url('activate/abc123')
]);
```

### E-posta Kuyruğu
```php
class EmailQueue
{
    public static function add($to, $subject, $body, $priority = 'normal')
    {
        $queueItem = [
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'priority' => $priority,
            'created_at' => date('Y-m-d H:i:s'),
            'attempts' => 0,
            'status' => 'pending'
        ];
        
        // Veritabanına kaydet
        return DB::table('email_queue')->insert($queueItem);
    }
    
    public static function process($limit = 10)
    {
        $emails = DB::table('email_queue')
            ->where('status', 'pending')
            ->where('attempts', '<', 3)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
        
        $processed = 0;
        
        foreach ($emails as $email) {
            $mailer = new Mailer();
            
            // Gönderme denemesi
            DB::table('email_queue')
                ->where('id', $email->id)
                ->update(['attempts' => $email->attempts + 1]);
            
            if ($mailer->send($email->to, $email->subject, $email->body)) {
                // Başarılı
                DB::table('email_queue')
                    ->where('id', $email->id)
                    ->update(['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')]);
                
                $processed++;
            } else {
                // Başarısız
                if ($email->attempts >= 2) {
                    DB::table('email_queue')
                        ->where('id', $email->id)
                        ->update(['status' => 'failed']);
                }
            }
        }
        
        return $processed;
    }
}

// Cron job ile çalıştır
// */5 * * * * php /path/to/process_email_queue.php
```

### Yapılandırma Örneği
```php
// db_config.php veya config dosyasında
Sword::setData('mail_driver', 'smtp');
Sword::setData('mail_host', 'smtp.gmail.com');
Sword::setData('mail_port', 587);
Sword::setData('mail_username', 'your-email@gmail.com');
Sword::setData('mail_password', 'your-app-password');
Sword::setData('mail_encryption', 'tls');
Sword::setData('mail_from_email', 'noreply@yoursite.com');
Sword::setData('mail_from_name', 'Your Site Name');
Sword::setData('mail_charset', 'UTF-8');
```

### Hata Yönetimi
```php
class SafeMailer
{
    public static function send($to, $subject, $body, $retries = 3)
    {
        $attempt = 0;
        
        while ($attempt < $retries) {
            try {
                $mailer = new Mailer();
                
                if ($mailer->send($to, $subject, $body)) {
                    Logger::info('E-posta gönderildi', [
                        'to' => $to,
                        'subject' => $subject,
                        'attempt' => $attempt + 1
                    ]);
                    return true;
                }
                
            } catch (Exception $e) {
                Logger::error('E-posta gönderme hatası', [
                    'to' => $to,
                    'subject' => $subject,
                    'attempt' => $attempt + 1,
                    'error' => $e->getMessage()
                ]);
            }
            
            $attempt++;
            
            if ($attempt < $retries) {
                sleep(2); // 2 saniye bekle
            }
        }
        
        return false;
    }
}
```

## İpuçları

1. **Yapılandırma**: SMTP ayarlarını güvenli şekilde saklayın
2. **Şablonlar**: E-posta şablonları kullanarak tutarlılık sağlayın
3. **Kuyruk**: Yoğun e-posta gönderimi için kuyruk sistemi kullanın
4. **Hata Yönetimi**: Gönderme hatalarını logla ve yeniden dene
5. **Güvenlik**: Spam önleme için rate limiting uygulayın