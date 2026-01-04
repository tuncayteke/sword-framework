<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Template Helper Functions - Tema fonksiyonları
 */

if (!function_exists('get_header')) {
    /**
     * Header dahil eder
     */
    function get_header()
    {
        $themeType = defined('THEME_TYPE') ? THEME_TYPE : 'frontend';
        $themePath = Theme::getPath($themeType);
        $headerFile = $themePath . '/header.php';

        if (file_exists($headerFile)) {
            include $headerFile;
        }
    }
}

if (!function_exists('get_footer')) {
    /**
     * Footer dahil eder
     */
    function get_footer()
    {
        $themeType = defined('THEME_TYPE') ? THEME_TYPE : 'frontend';
        $themePath = Theme::getPath($themeType);
        $footerFile = $themePath . '/footer.php';

        if (file_exists($footerFile)) {
            include $footerFile;
        }
    }
}

if (!function_exists('get_sidebar')) {
    /**
     * Sidebar dahil eder
     */
    function get_sidebar()
    {
        $themeType = defined('THEME_TYPE') ? THEME_TYPE : 'frontend';
        $themePath = Theme::getPath($themeType);
        $sidebarFile = $themePath . '/sidebar.php';

        if (file_exists($sidebarFile)) {
            include $sidebarFile;
        }
    }
}

if (!function_exists('get_template_part')) {
    /**
     * Template part dahil eder
     */
    function get_template_part($slug, $name = null)
    {
        $themeType = defined('THEME_TYPE') ? THEME_TYPE : 'frontend';
        $themePath = Theme::getPath($themeType);
        $template = $themePath . '/' . $slug;

        if ($name) {
            $template .= '-' . $name;
        }

        $template .= '.php';

        if (file_exists($template)) {
            include $template;
        }
    }
}

if (!function_exists('theme_asset')) {
    /**
     * Asset URL döndürür
     */
    function theme_asset($asset)
    {
        $themeType = defined('THEME_TYPE') ? THEME_TYPE : 'frontend';
        $basePath = Sword::getBasePath();
        $themePath = str_replace(BASE_PATH, '', Theme::getAssetPath($themeType));
        return $basePath . $themePath . '/' . ltrim($asset, '/');
    }
}

if (!function_exists('admin_asset')) {
    /**
     * Admin asset URL döndürür
     */
    function admin_asset($asset)
    {
        $basePath = Sword::getBasePath();
        $themePath = str_replace(BASE_PATH, '', Theme::getAssetPath('admin'));
        return $basePath . $themePath . '/' . ltrim($asset, '/');
    }
}
