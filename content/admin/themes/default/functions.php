<?php
/**
 * Admin Tema Özel Fonksiyonları
 * Core fonksiyonlar sword/TemplateFunctions.php dosyasında
 */

/**
 * Admin tema kurulumu
 */
function admin_theme_setup() {
    // Admin tema özel ayarları
}

/**
 * Admin dashboard widget
 */
function admin_dashboard_widget() {
    echo '<div class="dashboard-widget">Admin Özel Widget</div>';
}

/**
 * Admin tema özel CSS
 */
function admin_theme_styles() {
    echo '<link rel="stylesheet" href="' . admin_asset('css/admin-custom.css') . '">';
}