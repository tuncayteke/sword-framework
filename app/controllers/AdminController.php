<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Admin Controller - Admin panel kontrolcüsü
 */


namespace App\Controllers;

use App\Controllers\BackendController;
use Sword;

class AdminController extends BackendController
{
    /**
     * Admin index
     */
    public function index()
    {
        // Basit test
        echo "Admin Index Çalışıyor!";
        return;
    }

    /**
     * Admin dashboard
     */
    public function dashboard()
    {
        // Basit test
        echo "Admin Dashboard Çalışıyor!";
        return;

        $data = [
            'title' => 'Admin Dashboard',
            'user_count' => 150,
            'post_count' => 45
        ];

        return view('dashboard', $data);
    }

    /**
     * Tema ayarları
     */
    public function themes()
    {
        $data = [
            'title' => 'Tema Ayarları',
            'frontend_themes' => Sword::theme_getAvailable('frontend'),
            'admin_themes' => Sword::theme_getAvailable('admin'),
            'active_frontend' => Sword::theme_get('frontend'),
            'active_admin' => Sword::theme_get('admin')
        ];

        return view('themes', $data);
    }

    /**
     * Tema değiştir
     */
    public function changeTheme()
    {
        if ($this->request->isPost()) {
            $type = $this->request->post('type');
            $theme = $this->request->post('theme');

            if (in_array($type, ['frontend', 'admin']) && Sword::theme_exists($type, $theme)) {
                Sword::theme_set($type, $theme);
                return $this->json(['success' => true, 'message' => 'Tema başarıyla değiştirildi']);
            }

            return $this->json(['success' => false, 'message' => 'Tema değiştirilemedi']);
        }

        return $this->redirect('/admin/themes');
    }
}
