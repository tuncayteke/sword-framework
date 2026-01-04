# Helpers

Bu dizin yardımcı sınıfları içerir.

## Örnek Kullanım:

```php
class StringHelper {
    public static function slugify($text) {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
    }
}
```