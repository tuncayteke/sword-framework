# ExceptionHandler Sınıfı

ExceptionHandler sınıfı, Sword Framework'te hata yönetimini ve exception handling işlemlerini yönetir.

## Temel Kullanım

```php
// Exception handler'ı kaydet
ExceptionHandler::register();

// Özel hata işleyici
ExceptionHandler::setHandler(function($exception) {
    Logger::error($exception->getMessage());
    Response::error('Bir hata oluştu', 500)->send();
});
```

## Özellikler

- **Global Exception Handling**: Tüm yakalanmamış hataları yakalar
- **Custom Handlers**: Özel hata işleyicileri
- **Error Logging**: Otomatik hata kayıtları
- **Development/Production**: Ortam bazlı hata gösterimi

## Metodlar

### Temel Metodlar

```php
// Handler kaydı
ExceptionHandler::register();

// Özel handler ayarlama
ExceptionHandler::setHandler(callable $handler);

// Error handler ayarlama
ExceptionHandler::setErrorHandler(callable $handler);

// Shutdown handler ayarlama
ExceptionHandler::setShutdownHandler(callable $handler);
```

### Hata Türleri

```php
// Exception türlerine göre işleme
ExceptionHandler::handle(DatabaseException::class, function($e) {
    Logger::error('DB Error: ' . $e->getMessage());
});

ExceptionHandler::handle(ValidationException::class, function($e) {
    Response::validationError($e->getErrors())->send();
});
```

## Yapılandırma

```php
// Development ortamı
if (ENVIRONMENT === 'development') {
    ExceptionHandler::showErrors(true);
    ExceptionHandler::logErrors(true);
}

// Production ortamı
if (ENVIRONMENT === 'production') {
    ExceptionHandler::showErrors(false);
    ExceptionHandler::logErrors(true);
    ExceptionHandler::setDefaultHandler(function($e) {
        Response::error('Sistem hatası', 500)->send();
    });
}
```

## Özel Exception Sınıfları

```php
// DatabaseException
try {
    $db->query("INVALID SQL");
} catch (DatabaseException $e) {
    // Veritabanı hatası
}

// ValidationException
try {
    $validation->validate();
} catch (ValidationException $e) {
    // Doğrulama hatası
    $errors = $e->getErrors();
}

// SwordException
try {
    // Framework işlemi
} catch (SwordException $e) {
    // Framework hatası
}
```

## Hata Raporlama

```php
// Hata seviyelerini ayarla
ExceptionHandler::setErrorReporting(E_ALL);

// Belirli hataları yoksay
ExceptionHandler::ignoreErrors([E_NOTICE, E_WARNING]);

// Hata loglarını ayarla
ExceptionHandler::setLogPath(Sword::getPath('logs'));
```

## AJAX Hata Yönetimi

```php
ExceptionHandler::setAjaxHandler(function($exception) {
    if (Request::isAjax()) {
        Response::json([
            'error' => true,
            'message' => $exception->getMessage()
        ], 500)->send();
    }
});
```

## İlgili Sınıflar

- [Logger](Logger.md) - Hata kayıtları
- [Response](Response.md) - Hata yanıtları
- [Security](Security.md) - Güvenlik kontrolleri