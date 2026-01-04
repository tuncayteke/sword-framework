<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Backend Controller - Admin ana kontrolcüsü
 */

namespace App\Controllers;

use Sword\Controller;
use Session;
use Sword;
use Theme;

class BackendController extends Controller
{

    /**
     * Yapılandırıcı
     */
    public function __construct()
    {
        parent::__construct();

        // Global controller ayarla
        global $controller;
        $controller = $this;

        // Aktif tema dil dizinini ekle
        $activeTheme = Theme::get('admin');
        if ($activeTheme) {
            $themeLangPath = BASE_PATH . '/content/admin/themes/' . $activeTheme . '/langs';
            if (is_dir($themeLangPath)) {
                Sword::lang_addDirectory('theme-' . $activeTheme, $themeLangPath);
            }
        }

        // View path'i tema dizinine ayarla
        $themePath = Theme::getPath('admin');
        $this->view->setViewPath($themePath);

        // Tema functions.php dosyasını yükle
        $functionsFile = $themePath . '/functions.php';
        if (file_exists($functionsFile)) {
            include_once $functionsFile;
        }
    }
}
