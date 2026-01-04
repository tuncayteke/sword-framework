<?php

/**
 * Sword Framework - Menu System
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Basit ve etkili menü sistemi
 * Keskin. Hızlı. Ölümsüz.
 */

class Menu
{
    /**
     * Menü öğeleri
     */
    private static $menus = [];

    /**
     * Menü öğesi ekle
     */
    public static function add($location, $title, $url, $options = [])
    {
        if (!isset(self::$menus[$location])) {
            self::$menus[$location] = [];
        }

        $menuItem = [
            'id' => $options['id'] ?? strtolower(str_replace(' ', '_', $title)),
            'title' => $title,
            'url' => $url,
            'icon' => $options['icon'] ?? null,
            'order' => $options['order'] ?? 10,
            'permission' => $options['permission'] ?? null,
            'active' => $options['active'] ?? true,
            'parent_id' => $options['parent_id'] ?? null,
            'children' => []
        ];

        // Parent menü varsa, child olarak ekle
        if ($menuItem['parent_id']) {
            $parentFound = false;
            foreach (self::$menus[$location] as &$item) {
                if ($item['id'] === $menuItem['parent_id']) {
                    $item['children'][] = $menuItem;
                    $parentFound = true;
                    break;
                }
            }
            
            if (!$parentFound) {
                // Parent bulunamadı, normal menü olarak ekle
                self::$menus[$location][] = $menuItem;
            }
        } else {
            self::$menus[$location][] = $menuItem;
        }
        
        // Order'a göre sırala
        usort(self::$menus[$location], function($a, $b) {
            return $a['order'] <=> $b['order'];
        });
    }

    /**
     * Menü öğelerini getir
     */
    public static function get($location)
    {
        $items = self::$menus[$location] ?? [];
        
        // Aktif ve izinli olanları filtrele
        return array_filter($items, function($item) {
            if (!$item['active']) {
                return false;
            }
            
            if ($item['permission'] && !self::hasPermission($item['permission'])) {
                return false;
            }
            
            return true;
        });
    }

    /**
     * Menü render et
     */
    public static function render($location, $template = 'default')
    {
        $items = self::get($location);
        
        if (empty($items)) {
            return '';
        }

        switch ($template) {
            case 'admin':
                return self::renderAdmin($items);
            case 'navbar':
                return self::renderNavbar($items);
            case 'footer':
                return self::renderFooter($items);
            default:
                return self::renderDefault($items);
        }
    }

    /**
     * Admin menü render
     */
    private static function renderAdmin($items)
    {
        $html = '<nav class="admin-menu"><ul>';
        
        foreach ($items as $item) {
            $active = self::isCurrentUrl($item['url']) ? ' class="active"' : '';
            $icon = $item['icon'] ? '<i class="icon-' . $item['icon'] . '"></i>' : '';
            
            $html .= '<li' . $active . '>';
            $html .= '<a href="' . $item['url'] . '">' . $icon . $item['title'] . '</a>';
            
            // Submenu varsa ekle
            if (!empty($item['children'])) {
                $html .= '<ul class="submenu">';
                foreach ($item['children'] as $child) {
                    $childActive = self::isCurrentUrl($child['url']) ? ' class="active"' : '';
                    $childIcon = $child['icon'] ? '<i class="icon-' . $child['icon'] . '"></i>' : '';
                    
                    $html .= '<li' . $childActive . '>';
                    $html .= '<a href="' . $child['url'] . '">' . $childIcon . $child['title'] . '</a>';
                    $html .= '</li>';
                }
                $html .= '</ul>';
            }
            
            $html .= '</li>';
        }
        
        $html .= '</ul></nav>';
        return $html;
    }

    /**
     * Navbar menü render
     */
    private static function renderNavbar($items)
    {
        $html = '<nav class="navbar"><ul>';
        
        foreach ($items as $item) {
            $active = self::isCurrentUrl($item['url']) ? ' class="active"' : '';
            
            $html .= '<li' . $active . '>';
            $html .= '<a href="' . $item['url'] . '">' . $item['title'] . '</a>';
            $html .= '</li>';
        }
        
        $html .= '</ul></nav>';
        return $html;
    }

    /**
     * Footer menü render
     */
    private static function renderFooter($items)
    {
        $html = '<nav class="footer-menu">';
        
        foreach ($items as $item) {
            $html .= '<a href="' . $item['url'] . '">' . $item['title'] . '</a>';
        }
        
        $html .= '</nav>';
        return $html;
    }

    /**
     * Default menü render
     */
    private static function renderDefault($items)
    {
        $html = '<ul>';
        
        foreach ($items as $item) {
            $html .= '<li><a href="' . $item['url'] . '">' . $item['title'] . '</a></li>';
        }
        
        $html .= '</ul>';
        return $html;
    }

    /**
     * Mevcut URL kontrolü
     */
    private static function isCurrentUrl($url)
    {
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return $currentPath === $url;
    }

    /**
     * İzin kontrolü
     */
    private static function hasPermission($permission)
    {
        // Basit izin kontrolü - genişletilebilir
        if ($permission === 'admin') {
            return Auth::isAdmin();
        }
        
        if ($permission === 'user') {
            return Auth::check();
        }
        
        return true;
    }

    /**
     * Menü temizle
     */
    public static function clear($location = null)
    {
        if ($location) {
            unset(self::$menus[$location]);
        } else {
            self::$menus = [];
        }
    }

    /**
     * Tüm menüleri getir
     */
    public static function all()
    {
        return self::$menus;
    }
}