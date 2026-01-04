<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Auth Middleware - Kimlik doğrulama middleware'i
 */

namespace Sword\Middleware;

class AuthMiddleware implements MiddlewareInterface
{
    /**
     * Kimlik doğrulama kontrolü yapar
     *
     * @param array $params Rota parametreleri
     * @return bool İşlem başarılıysa true, başarısızsa false
     */
    public function handle(array $params = [])
    {
        // Session başlatılmamışsa başlat
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Kullanıcı giriş yapmış mı kontrol et
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            // Giriş sayfasına yönlendir
            header('Location: /login');
            exit;
        }

        return true;
    }
}
