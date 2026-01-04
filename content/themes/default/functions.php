<?php

/**
 * Frontend Tema Özel Fonksiyonları
 * Core fonksiyonlar sword/TemplateFunctions.php dosyasında
 */

/**
 * Tema kurulumu
 */
function theme_setup()
{
    // Tema özel ayarları
}

/**
 * Özel widget
 */
function custom_widget()
{
    echo '<div class="custom-widget">Özel Widget İçeriği</div>';
}

/**
 * Tema özel CSS
 */
function theme_styles()
{
    echo '<link rel="stylesheet" href="' . theme_asset('css/custom.css') . '">';
}




// Shortcode'ları tanımla
Sword::shortcode('button', function ($atts) {
    $atts = array_merge([
        'text' => 'Button',
        'class' => 'btn btn-primary',
        'url' => '#'
    ], $atts);

    return '<a href="' . $atts['url'] . '" class="' . $atts['class'] . '">' . $atts['text'] . '</a>';
});

Sword::shortcode('alert', function ($atts) {
    $atts = array_merge([
        'type' => 'info',
        'message' => 'Alert message'
    ], $atts);

    return '<div class="alert alert-' . $atts['type'] . '">' . $atts['message'] . '</div>';
});

Sword::shortcode('user_info', function ($atts) {
    $user = Sword::session('user');
    if (!$user) {
        return '<span>Giriş yapılmamış</span>';
    }

    $field = $atts['field'] ?? 'username';
    return '<span>' . ($user[$field] ?? 'Bilinmiyor') . '</span>';
});

Sword::shortcode('date', function ($atts) {
    $format = $atts['format'] ?? 'Y-m-d H:i:s';
    return date($format);
});





// Decorator'ları kayıt et
if (class_exists('Sword\\View\\Decorator')) {
    // Temel decorator'ları kayıt et
    Sword\View\Decorator::registerCommon();
    
    // Özel decorator'lar
    Sword\View\Decorator::register('site_name', function($content) {
        return str_replace('%site_name%', 'Sword Framework', $content);
    });
    
    Sword\View\Decorator::register('version', function($content) {
        return str_replace('%version%', '1.0.0', $content);
    });
}

// Tema kurulumunu çağır
theme_setup();
