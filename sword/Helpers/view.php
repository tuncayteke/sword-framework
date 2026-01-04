<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * View Helper Functions
 */

if (!function_exists('cell')) {
    /**
     * View Cell oluşturur
     *
     * @param string $name Cell adı
     * @param array $data Cell verileri
     * @return \Sword\View\Cell
     */
    function cell(string $name, array $data = []): \Sword\View\Cell
    {
        return new \Sword\View\Cell($name, null, $data);
    }
}

if (!function_exists('decorate')) {
    /**
     * İçeriği dekore eder
     *
     * @param string $content İçerik
     * @param string $decorator Dekoratör adı
     * @param array $params Parametreler
     * @return string Dekore edilmiş içerik
     */
    function decorate(string $content, string $decorator, array $params = []): string
    {
        return \Sword\View\Decorator::apply($decorator, $content, $params);
    }
}

if (!function_exists('view')) {
    /**
     * View helper - Controller'dan render eder
     *
     * @param string $template Template dosyası
     * @param array $data Template verileri
     * @return string
     */
    function view(string $template, array $data = []): string
    {
        // Global controller'dan render et
        global $controller;
        if ($controller && method_exists($controller, 'renderView')) {
            return $controller->renderView($template, $data);
        }

        // Fallback: yeni View instance
        $view = new \Sword\View($template, $data);
        return $view->render();
    }
}

if (!function_exists('extend')) {
    /**
     * Layout extend eder (CodeIgniter 4 tarzı)
     *
     * @param string $layout Layout dosyası
     * @return void
     */
    function extend(string $layout): void
    {
        global $currentView;
        if ($currentView && method_exists($currentView, 'extend')) {
            $currentView->extend($layout);
        }
    }
}

if (!function_exists('section')) {
    /**
     * Section başlatır
     *
     * @param string $name Section adı
     * @return void
     */
    function section(string $name): void
    {
        \Sword\View::startSection($name);
    }
}

if (!function_exists('endsection')) {
    /**
     * Section bitirir
     *
     * @return void
     */
    function endsection(): void
    {
        \Sword\View::endSection();
    }
}

if (!function_exists('renderSection')) {
    /**
     * Section içeriğini render eder
     *
     * @param string $name Section adı
     * @param string $default Varsayılan içerik
     * @return string
     */
    function renderSection(string $name, string $default = ''): string
    {
        return \Sword\View::getSection($name, $default);
    }
}



if (!function_exists('hasSection')) {
    /**
     * Section var mı kontrol eder
     *
     * @param string $name Section adı
     * @return bool
     */
    function hasSection(string $name): bool
    {
        return \Sword\View::hasSection($name);
    }
}
