<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Utility Helper Functions
 */

if (!function_exists('dd')) {
    /**
     * Dump and die (Laravel style)
     *
     * @param mixed ...$vars Variables to dump
     * @return void
     */
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        die();
    }
}

if (!function_exists('dump')) {
    /**
     * Dump variable (Laravel style)
     *
     * @param mixed ...$vars Variables to dump
     * @return void
     */
    function dump(...$vars): void
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
    }
}

if (!function_exists('env')) {
    /**
     * Get environment variable
     *
     * @param string $key Key
     * @param mixed $default Default value
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        // Convert string booleans
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
        }

        return $value;
    }
}

if (!function_exists('config')) {
    /**
     * Get config value
     *
     * @param string $key Config key
     * @param mixed $default Default value
     * @return mixed
     */
    function config(string $key, $default = null)
    {
        return \Sword::config($key, $default);
    }
}

if (!function_exists('raw')) {
    /**
     * Raw content wrapper - escaping'den kaçırır
     *
     * @param mixed $content İçerik
     * @return object Raw wrapper
     */
    function raw($content)
    {
        return new class($content) {
            private $content;
            public function __construct($content)
            {
                $this->content = $content;
            }
            public function __toString()
            {
                return (string) $this->content;
            }
            public function isRaw()
            {
                return true;
            }
        };
    }
}

if (!function_exists('trust')) {
    /**
     * Güvenilir içerik işaretleyici
     * Sadece senin kontrolünde üretilen HTML'ler için kullanılır
     *
     * @param string $content Güvenilir içerik
     * @return object Trust wrapper
     */
    function trust(string $content): object
    {
        return new class($content) {
            private $content;
            public function __construct(string $content)
            {
                $this->content = $content;
            }
            public function __toString(): string
            {
                return $this->content;
            }
            public function isTrusted(): bool
            {
                return true;
            }
        };
    }
}

if (!function_exists('cryptor')) {
    /**
     * Cryptor instance
     *
     * @return \Cryptor
     */
    function cryptor(): \Cryptor
    {
        static $instance = null;
        return $instance ??= new \Cryptor();
    }
}

if (!function_exists('encrypt')) {
    /**
     * Encrypt data
     *
     * @param mixed $data Data to encrypt
     * @return string Encrypted data
     */
    function encrypt($data): string
    {
        return cryptor()->encrypt($data);
    }
}

if (!function_exists('decrypt')) {
    /**
     * Decrypt data
     *
     * @param string $encrypted Encrypted data
     * @param bool $unserialize Unserialize data
     * @return mixed Decrypted data
     */
    function decrypt(string $encrypted, bool $unserialize = true)
    {
        return cryptor()->decrypt($encrypted, $unserialize);
    }
}

if (!function_exists('event')) {
    /**
     * Trigger event
     *
     * @param string $name Event name
     * @param mixed $payload Event payload
     * @return mixed Event result
     */
    function event(string $name, $payload = null)
    {
        return \Events::trigger($name, $payload);
    }
}

if (!function_exists('on')) {
    /**
     * Add event listener
     *
     * @param string $event Event name
     * @param callable $callback Callback function
     * @param int $priority Priority
     * @return void
     */
    function on(string $event, callable $callback, int $priority = 10): void
    {
        \Events::on($event, $callback, $priority);
    }
}

if (!function_exists('off')) {
    /**
     * Remove event listener
     *
     * @param string $event Event name
     * @param callable|null $callback Callback function
     * @return void
     */
    function off(string $event, callable $callback = null): void
    {
        \Events::off($event, $callback);
    }
}

if (!function_exists('image')) {
    /**
     * Image factory
     *
     * @param string|null $path Image path
     * @return \Image
     */
    function image(string $path = null): \Image
    {
        return new \Image($path);
    }
}

if (!function_exists('log')) {
    /**
     * Log message
     *
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Context data
     * @return bool Success
     */
    function log(string $level, string $message, array $context = []): bool
    {
        return \Logger::log($level, $message, $context);
    }
}

if (!function_exists('csrf_meta')) {
    /**
     * Generate CSRF meta tag
     *
     * @return string CSRF meta tag
     */
    function csrf_meta(): string
    {
        return \Security::csrfMeta();
    }
}

if (!function_exists('xss')) {
    /**
     * Clean XSS from data
     *
     * @param mixed $data Data to clean
     * @param bool $allowHtml Allow HTML tags
     * @return mixed Cleaned data
     */
    function xss($data, bool $allowHtml = false)
    {
        return \Security::xssClean($data, $allowHtml);
    }
}

if (!function_exists('hash_password')) {
    /**
     * Hash password
     *
     * @param string $password Password to hash
     * @return string Hashed password
     */
    function hash_password(string $password): string
    {
        return \Security::hashPassword($password);
    }
}

if (!function_exists('verify_password')) {
    /**
     * Verify password
     *
     * @param string $password Password to verify
     * @param string $hash Hash to verify against
     * @return bool Password is valid
     */
    function verify_password(string $password, string $hash): bool
    {
        return \Security::verifyPassword($password, $hash);
    }
}

if (!function_exists('session')) {
    /**
     * Session helper
     *
     * @param string|null $key Session key
     * @param mixed $value Session value (for set operation)
     * @return mixed Session value or Session class
     */
    function session(string $key = null, $value = null)
    {
        if ($key === null) {
            return '\Session';
        }

        if ($value !== null) {
            return \Session::set($key, $value);
        }

        return \Session::get($key);
    }
}

if (!function_exists('flash')) {
    /**
     * Flash message helper
     *
     * @param string $key Flash key
     * @param mixed $value Flash value
     * @return void
     */
    function flash(string $key, $value): void
    {
        \Session::flash($key, $value);
    }
}

if (!function_exists('upload')) {
    /**
     * Upload helper
     *
     * @param array|null $file File to upload
     * @param string|null $customName Custom filename
     * @param string|null $subDir Subdirectory
     * @return \Upload|array|bool Upload instance or upload result
     */
    function upload(array $file = null, string $customName = null, string $subDir = null)
    {
        $upload = new \Upload();

        if ($file !== null) {
            return $upload->upload($file, $customName, $subDir);
        }

        return $upload;
    }
}

if (!function_exists('auth')) {
    /**
     * Auth helper
     *
     * @param string|null $guard Guard name
     * @return \Sword\Auth\Guard
     */
    function auth(string $guard = null): \Sword\Auth\Guard
    {
        return \Sword\Auth\Auth::guard($guard);
    }
}

if (!function_exists('user')) {
    /**
     * Get current user
     *
     * @param string|null $guard Guard name
     * @return mixed|null
     */
    function user(string $guard = null)
    {
        return auth($guard)->user();
    }
}

if (!function_exists('user_id')) {
    /**
     * Get current user ID
     *
     * @param string|null $guard Guard name
     * @return mixed|null
     */
    function user_id(string $guard = null)
    {
        return auth($guard)->id();
    }
}

if (!function_exists('login')) {
    /**
     * Login user
     *
     * @param string $email Email
     * @param string $password Password
     * @param bool $remember Remember me
     * @param string|null $guard Guard name
     * @return bool
     */
    function login(string $email, string $password, bool $remember = false, string $guard = null): bool
    {
        return auth($guard)->attempt(['email' => $email, 'password' => $password], $remember);
    }
}

if (!function_exists('logout')) {
    /**
     * Logout user
     *
     * @param string|null $guard Guard name
     * @return void
     */
    function logout(string $guard = null): void
    {
        auth($guard)->logout();
    }
}

if (!function_exists('shortcode')) {
    /**
     * Shortcode ekler
     *
     * @param string $tag Shortcode etiketi
     * @param callable $callback Callback fonksiyonu
     * @return void
     */
    function shortcode(string $tag, callable $callback): void
    {
        \Sword::shortcode($tag, $callback);
    }
}

if (!function_exists('do_shortcode')) {
    /**
     * İçerikteki shortcode'ları işler
     *
     * @param string $content İçerik
     * @return string İşlenmiş içerik
     */
    function do_shortcode(string $content): string
    {
        if (!class_exists('Shortcode')) {
            require_once __DIR__ . '/../Shortcode.php';
        }
        return \Shortcode::process($content);
    }
}

if (!function_exists('asset')) {
    /**
     * Asset URL'si döndürür
     *
     * @param string $path Asset yolu
     * @param string $type Tema tipi (frontend/admin)
     * @return string Asset URL'si
     */
    function asset(string $path, string $type = 'frontend'): string
    {
        $basePath = \Sword::getBasePath();
        $themePath = str_replace(defined('BASE_PATH') ? BASE_PATH : '', '', \Theme::getAssetPath($type));
        return $basePath . $themePath . '/' . ltrim($path, '/');
    }
}



if (!function_exists('url')) {
    /**
     * URL oluşturur
     *
     * @param string $path Yol
     * @param array $params Parametreler
     * @return string URL
     */
    function url(string $path = '', array $params = []): string
    {
        return \Sword::url($path, $params);
    }
}
