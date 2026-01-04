# Middlewares

Bu dizin middleware sınıfları içerir.

## Örnek Kullanım:

```php
class AuthMiddleware {
    public function handle($params) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return false;
        }
        return true;
    }
}
```