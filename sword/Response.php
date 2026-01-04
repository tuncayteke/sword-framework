<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Response – Keskin. Hızlı. Ölümsüz.
 */

class Response
{
    private $statusCode = 200;
    private $headers = [];
    private $content = '';
    private $cookies = [];
    private bool $autoEscape = true;        // Varsayılan: açık (güvenli)
    private array $autoDecorators = ['datetime', 'escape']; // Varsayılan dekoratörler

    private static $statusCodes = [
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        304 => 'Not Modified',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        422 => 'Unprocessable Entity',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
    ];

    /** HTTP durum kodunu ayarlar */
    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /** HTTP başlığı ekler */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /** Birden çok HTTP başlığı ekler */
    public function headers(array $headers): self
    {
        foreach ($headers as $name => $value) $this->header($name, $value);
        return $this;
    }

    /** İçerik türünü ayarlar (otomatik charset ile) */
    public function contentType(string $type): self
    {
        return $this->header('Content-Type', $type . '; charset=utf-8');
    }

    /** Yanıt içeriğini ayarlar */
    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /** Çerez ekler */
    public function cookie(string $name, string $value = '', array $options = []): self
    {
        $this->cookies[$name] = ['value' => $value, 'options' => $options];
        return $this;
    }

    /** Yönlendirme yapar (named route destekli) */
    public function redirect(string $url, int $code = 302): self
    {
        // Named route mu kontrol et
        if (strpos($url, '/') === false && strpos($url, '://') === false) {
            try {
                $url = \Sword::routerRoute($url);
            } catch (\Exception $e) {
                // Named route bulunamazsa normal URL olarak kullan
                $url = \Sword::url($url);
            }
        }
        // Relative path ise tam URL yap
        elseif (strpos($url, '://') === false) {
            $url = \Sword::url($url);
        }

        return $this->status($code)->header('Location', $url);
    }

    /** Önceki sayfaya yönlendirir (named route destekli) */
    public function back(string $fallback = '/', int $code = 302): self
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        if (!$referer) {
            // Fallback named route mu?
            if (strpos($fallback, '/') === false && strpos($fallback, '://') === false) {
                try {
                    $fallback = \Sword::routerRoute($fallback);
                } catch (\Exception $e) {
                    $fallback = \Sword::url($fallback);
                }
            }
        }

        $url = $referer ?: $fallback;
        return $this->redirect($url, $code);
    }

    /** JSON yanıtı oluşturur */
    public function json($data, int $code = 200): self
    {
        // JSON için escape'i kapat
        $this->autoDecorators = array_filter($this->autoDecorators, function ($decorator) {
            return $decorator !== 'escape';
        });

        // JSON verisinde decorator'ları uygula
        if (class_exists('\\Sword\\View\\Decorator')) {
            $data = $this->applyDecoratorsToData($data);
        }

        return $this->status($code)
            ->contentType('application/json')
            ->content(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /** HTML yanıtı oluşturur */
    public function html(string $html, int $code = 200): self
    {
        return $this->status($code)->contentType('text/html')->content($html);
    }

    /** Düz metin yanıtı oluşturur */
    public function text(string $text, int $code = 200): self
    {
        return $this->status($code)->contentType('text/plain')->content($text);
    }

    /** Dosya indirme yanıtı (stream destekli) */
    public function download(string $file, ?string $name = null, ?string $mime = null): self
    {
        if (!file_exists($file)) return $this->status(404)->send();

        $name ??= basename($file);
        $mime ??= mime_content_type($file) ?: 'application/octet-stream';

        $this->headers = [
            'Content-Description' => 'File Transfer',
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
            'Content-Length' => filesize($file),
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'public',
            'Expires' => '0',
        ];

        if (ob_get_level()) ob_end_clean();
        http_response_code($this->statusCode);
        foreach ($this->headers as $h => $v) header("$h: $v");
        readfile($file);
        exit;
    }

    /** Flash data ekler (Laravel tarzı) */
    public function with($key, $value = null): self
    {
        $data = is_array($key) ? $key : [$key => $value];
        foreach ($data as $k => $v) {
            class_exists('Session') ? Session::flash($k, $v) : $_SESSION['flash'][$k] = $v;
        }
        return $this;
    }

    // API Yardımcıları
    /** Başarı yanıtı (200) */
    public function success($data = null, ?string $message = null): self
    {
        return $this->api($data, $message, true, 200);
    }

    /** Oluşturuldu yanıtı (201) - RESTful API için */
    public function created($data = null, ?string $location = null): self
    {
        if ($location) $this->header('Location', $location);
        return $this->success($data, 'Created')->status(201);
    }

    /** İçerik yok yanıtı (204) - API'lerde çok kullanılır */
    public function noContent(): self
    {
        return $this->status(204)->content('');
    }

    /** Hata yanıtı */
    public function error(string $message, int $code = 400, $data = null): self
    {
        return $this->api($data, $message, false, $code);
    }

    /** 404 Not Found yanıtı */
    public function notFound(string $message = 'Not Found'): self
    {
        return $this->error($message, 404);
    }

    /** 401 Unauthorized yanıtı */
    public function unauthorized(string $message = 'Unauthorized'): self
    {
        return $this->error($message, 401);
    }

    /** 403 Forbidden yanıtı */
    public function forbidden(string $message = 'Forbidden'): self
    {
        return $this->error($message, 403);
    }

    /** Validation hata yanıtı (422) */
    public function validationError(array $errors, string $message = 'Validation failed'): self
    {
        return $this->json([
            'message' => $message,
            'errors'  => $errors
        ], 422);
    }

    /** Sayfalama yanıtı */
    public function paginate(array $items, int $total, int $page = 1, int $perPage = 15): self
    {
        return $this->json([
            'data' => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'last_page'    => ceil($total / $perPage),
                'from'         => $total ? ($page - 1) * $perPage + 1 : null,
                'to'           => min($page * $perPage, $total) ?: null,
            ]
        ]);
    }

    /** Universal API yanıtı */
    public function api($data = null, ?string $message = null, bool $success = true, int $code = 200): self
    {
        $response = ['success' => $success];
        if ($message !== null) $response['message'] = $message;
        if ($data !== null)    $response['data']    = $data;
        return $this->json($response, $code);
    }

    /** JSONP yanıtı */
    public function jsonp($data, string $callback = 'callback', int $code = 200): self
    {
        return $this->status($code)
            ->contentType('application/javascript')
            ->content("{$callback}(" . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ");");
    }

    /** Sadece data yanıtı */
    public function data($data, int $code = 200): self
    {
        return $this->json(['data' => $data], $code);
    }

    /** Sadece mesaj yanıtı */
    public function message(string $msg, int $code = 200): self
    {
        return $this->json(['message' => $msg], $code);
    }

    /** Yanıtı gönderir (FastCGI + Auto-Decoration) */
    public function send(): void
    {
        // Eğer içerik varsa ve auto-escape açıksa (HTML ve JSON için)
        if ($this->autoEscape && ($this->content || $this->content === '0')) {
            $content = $this->content;

            // trust() ile işaretlenmiş içeriklere DOKUNMA!
            if (is_object($content) && method_exists($content, 'isTrusted') && $content->isTrusted()) {
                // Güvenilir içerik - dekoratör zincirini atla
                $this->content = (string) $content;
            } else {
                // Tüm otomatik dekoratörleri sırayla uygula
                if (class_exists('\\Sword\\View\\Decorator')) {
                    foreach ($this->autoDecorators as $decorator) {
                        if (is_string($decorator)) {
                            $content = \Sword\View\Decorator::apply($decorator, $content);
                        } elseif (is_array($decorator)) {
                            // ['truncate' => ['length' => 200]]
                            foreach ($decorator as $name => $params) {
                                $content = \Sword\View\Decorator::apply($name, $content, $params);
                            }
                        }
                    }
                }
                $this->content = $content;
            }
        }

        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) header("$name: $value");
        foreach ($this->cookies as $name => $c) setcookie($name, $c['value'], $c['options']);
        echo (string) $this->content;

        // Memory cleanup
        if (class_exists('MemoryManager')) {
            MemoryManager::cleanup();
        }

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        exit;
    }

    /** Response factory */
    public static function make(): self
    {
        return new static();
    }

    /** Otomatik escape'i ayarlar */
    public function autoEscape(bool $enabled = true): self
    {
        $this->autoEscape = $enabled;
        return $this;
    }

    /** Escape'i kapatır */
    public function withoutEscape(): self
    {
        return $this->autoEscape(false);
    }

    /** Özel dekoratör zinciri ayarlar */
    public function withDecorators(array $decorators): self
    {
        $this->autoDecorators = $decorators;
        return $this;
    }

    /** Dekoratör ekler */
    public function addDecorator($decorator): self
    {
        $this->autoDecorators[] = $decorator;
        return $this;
    }

    /** Tüm dekoratörleri kapatır */
    public function withoutDecorators(): self
    {
        $this->autoDecorators = [];
        return $this;
    }

    /** JSON verisine decorator uygular */
    private function applyDecoratorsToData($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    // Tüm decorator'ları uygula
                    $decorators = ['year', 'datetime', 'site_name', 'version'];
                    foreach ($decorators as $decorator) {
                        if (\Sword\View\Decorator::has($decorator)) {
                            $value = \Sword\View\Decorator::apply($decorator, $value);
                        }
                    }
                    $data[$key] = $value;
                } elseif (is_array($value)) {
                    $data[$key] = $this->applyDecoratorsToData($value);
                }
            }
        } elseif (is_string($data)) {
            // String ise direkt uygula
            $decorators = ['year', 'datetime', 'site_name', 'version'];
            foreach ($decorators as $decorator) {
                if (\Sword\View\Decorator::has($decorator)) {
                    $data = \Sword\View\Decorator::apply($decorator, $data);
                }
            }
        }

        return $data;
    }

    /** String'e dönüştürür */
    public function __toString(): string
    {
        return $this->content;
    }
}
