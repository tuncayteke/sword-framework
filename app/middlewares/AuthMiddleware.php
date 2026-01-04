<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Auth Middleware - Kimlik doğrulama middleware'i
 */

namespace App\Middlewares;

use Session;
use Sword;

class AuthMiddleware
{

    /**
     * Middleware işleyicisi
     */
    public function handle($params)
    {
        Session::start();

        if (!Session::has('user_id')) {
            Sword::redirect('/login');
            return false;
        }

        return true;
    }
}
