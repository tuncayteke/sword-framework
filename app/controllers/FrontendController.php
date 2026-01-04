<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Frontend Controller - Ön yüz ana kontrolcüsü
 */

namespace App\Controllers;

use Sword\Controller;
use Sword;

class FrontendController extends Controller
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
        $activeTheme = Sword::theme_get('frontend');
        if ($activeTheme) {
            $themeLangPath = BASE_PATH . '/content/themes/' . $activeTheme . '/langs';
            if (is_dir($themeLangPath)) {
                Sword::lang_addDirectory('theme-' . $activeTheme, $themeLangPath);
            }
        }

        // View path'i tema dizinine ayarla
        $themePath = Sword::theme_getPath('frontend');
        $this->view->setViewPath($themePath);

        // Tema functions.php dosyasını yükle
        $functionsFile = $themePath . '/functions.php';
        if (file_exists($functionsFile)) {
            include_once $functionsFile;
        }
    }
}
