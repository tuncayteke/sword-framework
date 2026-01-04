<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * CORS Middleware - Cross-Origin Resource Sharing middleware'i
 */

namespace Sword\Middleware;

class CorsMiddleware implements MiddlewareInterface
{
    /**
     * CORS başlıklarını ayarlar
     *
     * @param array $params Rota parametreleri
     * @return bool Her zaman true döner
     */
    public function handle(array $params = [])
    {
        // CORS başlıklarını ayarla
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');

        // OPTIONS isteği için erken çıkış
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        return true;
    }
}
