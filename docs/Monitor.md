# Monitor Sınıfı

Monitor sınıfı, Sword Framework'te sistem izleme, performans takibi ve uygulama sağlığı kontrolü işlemlerini yönetir.

## Temel Kullanım

```php
// Sistem durumunu kontrol et
$status = Monitor::getSystemStatus();

// Performans metriklerini al
$metrics = Monitor::getMetrics();

// Health check yap
$health = Monitor::healthCheck();
```

## Özellikler

- **System Monitoring**: Sistem kaynak izleme
- **Performance Tracking**: Performans takibi
- **Health Checks**: Sağlık kontrolleri
- **Alert System**: Uyarı sistemi
- **Real-time Stats**: Gerçek zamanlı istatistikler

## Metodlar

### Sistem İzleme

```php
// CPU kullanımı
$cpu = Monitor::getCpuUsage();

// Bellek kullanımı
$memory = Monitor::getMemoryUsage();

// Disk kullanımı
$disk = Monitor::getDiskUsage();

// Network istatistikleri
$network = Monitor::getNetworkStats();
```

### Performans Takibi

```php
// Request süresini ölç
Monitor::startTimer('request');
// ... işlemler ...
$duration = Monitor::stopTimer('request');

// Database sorgu sayısı
$queryCount = Monitor::getQueryCount();

// Cache hit oranı
$hitRatio = Monitor::getCacheHitRatio();
```

### Health Checks

```php
// Veritabanı bağlantısı
$dbHealth = Monitor::checkDatabase();

// Cache sistemi
$cacheHealth = Monitor::checkCache();

// Dosya sistemi
$fsHealth = Monitor::checkFileSystem();

// Tüm kontroller
$overallHealth = Monitor::healthCheck();
```

## Yapılandırma

```php
// Monitor ayarları
Monitor::configure([
    'enabled' => true,
    'interval' => 60, // saniye
    'thresholds' => [
        'cpu' => 80,      // %80
        'memory' => 85,   // %85
        'disk' => 90,     // %90
        'response_time' => 2000 // 2 saniye
    ],
    'alerts' => [
        'email' => 'admin@example.com',
        'webhook' => 'https://hooks.slack.com/...'
    ]
]);
```

## Metrik Toplama

```php
// Custom metrik ekle
Monitor::addMetric('user_registrations', 1);
Monitor::addMetric('order_total', 150.50);

// Metrik değerini al
$registrations = Monitor::getMetric('user_registrations');

// Tüm metrikleri al
$allMetrics = Monitor::getAllMetrics();
```

## Uyarı Sistemi

```php
// Uyarı kuralı ekle
Monitor::addAlert('high_cpu', function($metrics) {
    return $metrics['cpu'] > 80;
}, function($metrics) {
    Mailer::send('admin@example.com', 'High CPU Usage', 
        "CPU usage is {$metrics['cpu']}%");
});

// Uyarıları kontrol et
Monitor::checkAlerts();
```

## Real-time İstatistikler

```php
// Anlık istatistikler
$stats = Monitor::getRealTimeStats();
/*
[
    'active_users' => 45,
    'requests_per_minute' => 120,
    'average_response_time' => 250,
    'error_rate' => 0.02
]
*/

// WebSocket ile canlı veri
Monitor::enableRealTimeUpdates(true);
```

## Performans Profiling

```php
// Profiling başlat
Monitor::startProfiling();

// Code block profiling
Monitor::profile('database_queries', function() {
    // Database işlemleri
});

// Profiling sonuçları
$profile = Monitor::getProfilingResults();
```

## Log Analizi

```php
// Error log analizi
$errorStats = Monitor::analyzeErrorLogs();

// Access log analizi
$accessStats = Monitor::analyzeAccessLogs();

// Slow query analizi
$slowQueries = Monitor::analyzeSlowQueries();
```

## Dashboard Verileri

```php
// Dashboard için veri hazırla
$dashboardData = Monitor::getDashboardData();
/*
[
    'system' => [
        'cpu' => 45,
        'memory' => 62,
        'disk' => 78
    ],
    'application' => [
        'uptime' => '5 days 12 hours',
        'requests_today' => 15420,
        'errors_today' => 23
    ],
    'performance' => [
        'avg_response_time' => 180,
        'cache_hit_ratio' => 0.94,
        'db_query_time' => 45
    ]
]
*/
```

## Raporlama

```php
// Günlük rapor
$dailyReport = Monitor::generateDailyReport();

// Haftalık rapor
$weeklyReport = Monitor::generateWeeklyReport();

// Özel rapor
$customReport = Monitor::generateReport([
    'start_date' => '2024-01-01',
    'end_date' => '2024-01-31',
    'metrics' => ['cpu', 'memory', 'response_time']
]);
```

## Threshold Yönetimi

```php
// Threshold ayarla
Monitor::setThreshold('response_time', 1500); // 1.5 saniye

// Threshold aşıldığında callback
Monitor::onThresholdExceeded('response_time', function($value) {
    Logger::warning("Slow response time: {$value}ms");
});
```

## Maintenance Mode

```php
// Maintenance mode kontrolü
if (Monitor::isMaintenanceMode()) {
    Response::text('Site bakımda', 503)->send();
}

// Maintenance mode ayarla
Monitor::enableMaintenanceMode(true, 'Sistem güncellemesi yapılıyor');
```

## İlgili Sınıflar

- [Logger](Logger.md) - Log yönetimi
- [MemoryManager](MemoryManager.md) - Bellek yönetimi
- [Cache](Cache.md) - Önbellek sistemi
- [Mailer](Mailer.md) - E-posta bildirimleri