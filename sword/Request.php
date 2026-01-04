<?php

/**
 * Sword Framework - Request
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Basit ve etkili HTTP request yönetimi
 * Keskin. Hızlı. Ölümsüz.
 */

class Request
{
    /**
     * Request data
     */
    private $get = [];
    private $post = [];
    private $files = [];
    private $server = [];
    private $headers = [];
    
    /**
     * URI components
     */
    private $uri = null;
    private $path = null;
    private $segments = [];
    
    /**
     * Request method
     */
    private $method = null;
    
    /**
     * JSON data
     */
    private $json = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->server = $_SERVER;
        
        $this->parseHeaders();
        $this->parseMethod();
        $this->parseUri();
        $this->parseJson();
    }

    /**
     * Parse headers
     */
    private function parseHeaders()
    {
        $this->headers = [];
        
        foreach ($this->server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $this->headers[strtolower($header)] = $value;
            }
        }
        
        // Content-Type
        if (isset($this->server['CONTENT_TYPE'])) {
            $this->headers['content-type'] = $this->server['CONTENT_TYPE'];
        }
    }

    /**
     * Parse method
     */
    private function parseMethod()
    {
        $this->method = $this->server['REQUEST_METHOD'] ?? 'GET';
        
        // Method spoofing
        if ($this->method === 'POST' && isset($this->post['_method'])) {
            $this->method = strtoupper($this->post['_method']);
        }
    }

    /**
     * Parse URI
     */
    private function parseUri()
    {
        $this->uri = $this->server['REQUEST_URI'] ?? '/';
        $this->path = parse_url($this->uri, PHP_URL_PATH);
        $this->segments = array_values(array_filter(explode('/', trim($this->path, '/'))));
    }

    /**
     * Parse JSON data
     */
    private function parseJson()
    {
        if ($this->isJson()) {
            $input = file_get_contents('php://input');
            $this->json = json_decode($input, true);
        }
    }

    // ============================================
    // HTTP Method Checks
    // ============================================

    public function isGet()
    {
        return $this->method === 'GET';
    }

    public function isPost()
    {
        return $this->method === 'POST';
    }

    public function isPut()
    {
        return $this->method === 'PUT';
    }

    public function isDelete()
    {
        return $this->method === 'DELETE';
    }

    public function method()
    {
        return $this->method;
    }

    // ============================================
    // Content Type Checks
    // ============================================

    public function isJson()
    {
        $contentType = $this->header('content-type');
        return $contentType && stripos($contentType, 'application/json') !== false;
    }

    public function isAjax()
    {
        return $this->header('x-requested-with') === 'XMLHttpRequest';
    }

    public function wantsJson()
    {
        $accept = $this->header('accept');
        return $accept && stripos($accept, 'application/json') !== false;
    }

    // ============================================
    // Input Methods
    // ============================================

    public function input($key = null, $default = null)
    {
        $all = array_merge($this->get, $this->post);
        
        if ($this->json) {
            $all = array_merge($all, $this->json);
        }
        
        if ($key === null) {
            return $all;
        }
        
        return $all[$key] ?? $default;
    }

    public function get($key = null, $default = null)
    {
        if ($key === null) {
            return $this->get;
        }
        return $this->get[$key] ?? $default;
    }

    public function post($key = null, $default = null)
    {
        if ($key === null) {
            return $this->post;
        }
        return $this->post[$key] ?? $default;
    }

    public function json($key = null, $default = null)
    {
        if ($key === null) {
            return $this->json;
        }
        return $this->json[$key] ?? $default;
    }

    public function has($key)
    {
        $value = $this->input($key);
        return $value !== null && $value !== '';
    }

    public function only(...$keys)
    {
        $data = $this->input();
        $result = [];
        
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $result[$key] = $data[$key];
            }
        }
        
        return $result;
    }

    public function except(...$keys)
    {
        $data = $this->input();
        
        foreach ($keys as $key) {
            unset($data[$key]);
        }
        
        return $data;
    }

    // ============================================
    // File Handling
    // ============================================

    public function hasFile($key)
    {
        return isset($this->files[$key]) && 
               $this->files[$key]['error'] === UPLOAD_ERR_OK &&
               $this->files[$key]['size'] > 0;
    }

    public function file($key)
    {
        if (!$this->hasFile($key)) {
            return null;
        }
        
        return $this->files[$key];
    }

    // ============================================
    // Headers
    // ============================================

    public function header($key = null, $default = null)
    {
        if ($key === null) {
            return $this->headers;
        }
        
        $key = strtolower(str_replace('_', '-', $key));
        return $this->headers[$key] ?? $default;
    }

    public function hasHeader($key)
    {
        $key = strtolower(str_replace('_', '-', $key));
        return isset($this->headers[$key]);
    }

    public function bearerToken()
    {
        $header = $this->header('authorization');
        
        if ($header && strpos($header, 'Bearer ') === 0) {
            return substr($header, 7);
        }
        
        return null;
    }

    // ============================================
    // Server Info
    // ============================================

    public function ip()
    {
        return $this->server['HTTP_X_FORWARDED_FOR'] ??
               $this->server['HTTP_CLIENT_IP'] ??
               $this->server['REMOTE_ADDR'] ??
               '0.0.0.0';
    }

    public function userAgent()
    {
        return $this->server['HTTP_USER_AGENT'] ?? null;
    }

    public function isSecure()
    {
        return (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') ||
               (!empty($this->server['HTTP_X_FORWARDED_PROTO']) && $this->server['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    // ============================================
    // URL Methods
    // ============================================

    public function url()
    {
        $scheme = $this->isSecure() ? 'https' : 'http';
        $host = $this->server['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . $this->path;
    }

    public function fullUrl()
    {
        return $this->url() . ($this->server['QUERY_STRING'] ? '?' . $this->server['QUERY_STRING'] : '');
    }

    public function path()
    {
        return $this->path;
    }

    public function segments()
    {
        return $this->segments;
    }

    public function segment($index, $default = null)
    {
        return $this->segments[$index] ?? $default;
    }

    // ============================================
    // Utility Methods
    // ============================================

    public function all()
    {
        return $this->input();
    }

    public function server($key = null, $default = null)
    {
        if ($key === null) {
            return $this->server;
        }
        return $this->server[$key] ?? $default;
    }
}