<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Response Helper Functions
 */

if (!function_exists('response')) {
    /**
     * Response factory
     *
     * @return Response
     */
    function response(): Response
    {
        return Response::make();
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect helper
     *
     * @param string $url URL
     * @param int $code Status code
     * @return Response
     */
    function redirect(string $url, int $code = 302): Response
    {
        return Response::make()->redirect($url, $code);
    }
}

if (!function_exists('json')) {
    /**
     * JSON response helper
     *
     * @param mixed $data Data
     * @param int $code Status code
     * @return Response
     */
    function json($data, int $code = 200): Response
    {
        return Response::make()->json($data, $code);
    }
}

if (!function_exists('back')) {
    /**
     * Redirect back helper
     *
     * @param string $fallback Fallback URL
     * @param int $code Status code
     * @return Response
     */
    function back(string $fallback = '/', int $code = 302): Response
    {
        return Response::make()->back($fallback, $code);
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL
     *
     * @param string $path Path
     * @return string
     */
    function url(string $path = ''): string
    {
        $base = rtrim($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Generate asset URL
     *
     * @param string $path Asset path
     * @return string
     */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF token field
     *
     * @return string
     */
    function csrf_field(): string
    {
        $token = $_SESSION['_token'] ?? bin2hex(random_bytes(32));
        $_SESSION['_token'] = $token;
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }
}
