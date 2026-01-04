<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Middleware Interface - Tüm middleware'lerin uygulaması gereken arayüz
 */

namespace Sword\Middleware;

interface MiddlewareInterface
{
    /**
     * Middleware'i çalıştırır
     *
     * @param array $params Rota parametreleri
     * @return bool|mixed İşlem başarılıysa true, başarısızsa false
     */
    public function handle(array $params = []);
}
