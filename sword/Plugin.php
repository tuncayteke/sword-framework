<?php

/**
 * Sword Framework - Plugin System
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Basit ve etkili eklenti sistemi
 * Keskin. Hızlı. Ölümsüz.
 */

class Plugin
{
    /**
     * Aktif eklentiler
     */
    private static $activePlugins = [];
    
    /**
     * Yüklü eklentiler
     */
    private static $loadedPlugins = [];
    
    /**
     * Plugin dizini
     */
    private static $pluginDir = 'content/plugins/';

    /**
     * Eklentileri başlat
     */
    public static function init()
    {
        self::loadActivePlugins();
        self::loadPlugins();
    }

    /**
     * Aktif eklentileri yükle
     */
    private static function loadActivePlugins()
    {
        $activeFile = self::$pluginDir . 'active.json';
        
        if (file_exists($activeFile)) {
            $content = file_get_contents($activeFile);
            self::$activePlugins = json_decode($content, true) ?: [];
        }
    }

    /**
     * Eklentileri yükle ve çalıştır
     */
    private static function loadPlugins()
    {
        foreach (self::$activePlugins as $pluginName) {
            self::loadPlugin($pluginName);
        }
    }

    /**
     * Tek eklenti yükle
     */
    private static function loadPlugin($pluginName)
    {
        $pluginPath = self::$pluginDir . $pluginName . '/';
        $mainFile = $pluginPath . $pluginName . '.php';
        
        if (file_exists($mainFile)) {
            try {
                require_once $mainFile;
                
                // Plugin sınıfını çalıştır
                $className = ucfirst($pluginName) . 'Plugin';
                
                if (class_exists($className)) {
                    $plugin = new $className();
                    
                    if (method_exists($plugin, 'init')) {
                        $plugin->init();
                    }
                    
                    self::$loadedPlugins[$pluginName] = $plugin;
                }
                
            } catch (Exception $e) {
                error_log("Plugin load error ($pluginName): " . $e->getMessage());
            }
        }
    }

    /**
     * Eklenti aktifleştir
     */
    public static function activate($pluginName)
    {
        if (!in_array($pluginName, self::$activePlugins)) {
            self::$activePlugins[] = $pluginName;
            self::saveActivePlugins();
            
            // Eklentiyi hemen yükle
            self::loadPlugin($pluginName);
            
            // Aktivasyon hook'u çalıştır
            if (isset(self::$loadedPlugins[$pluginName])) {
                $plugin = self::$loadedPlugins[$pluginName];
                if (method_exists($plugin, 'activate')) {
                    $plugin->activate();
                }
            }
            
            return true;
        }
        
        return false;
    }

    /**
     * Eklenti deaktifleştir
     */
    public static function deactivate($pluginName)
    {
        $key = array_search($pluginName, self::$activePlugins);
        
        if ($key !== false) {
            // Deaktivasyon hook'u çalıştır
            if (isset(self::$loadedPlugins[$pluginName])) {
                $plugin = self::$loadedPlugins[$pluginName];
                if (method_exists($plugin, 'deactivate')) {
                    $plugin->deactivate();
                }
            }
            
            unset(self::$activePlugins[$key]);
            self::$activePlugins = array_values(self::$activePlugins);
            self::saveActivePlugins();
            
            unset(self::$loadedPlugins[$pluginName]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Aktif eklentileri kaydet
     */
    private static function saveActivePlugins()
    {
        $activeFile = self::$pluginDir . 'active.json';
        file_put_contents($activeFile, json_encode(self::$activePlugins, JSON_PRETTY_PRINT));
    }

    /**
     * Mevcut eklentileri listele
     */
    public static function getAvailablePlugins()
    {
        $plugins = [];
        $pluginDir = self::$pluginDir;
        
        if (is_dir($pluginDir)) {
            $dirs = scandir($pluginDir);
            
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..' || !is_dir($pluginDir . $dir)) {
                    continue;
                }
                
                $infoFile = $pluginDir . $dir . '/plugin.json';
                
                if (file_exists($infoFile)) {
                    $info = json_decode(file_get_contents($infoFile), true);
                    
                    if ($info) {
                        $info['name'] = $dir;
                        $info['active'] = in_array($dir, self::$activePlugins);
                        $plugins[] = $info;
                    }
                }
            }
        }
        
        return $plugins;
    }

    /**
     * Eklenti aktif mi?
     */
    public static function isActive($pluginName)
    {
        return in_array($pluginName, self::$activePlugins);
    }

    /**
     * Yüklü eklenti instance'ını getir
     */
    public static function getPlugin($pluginName)
    {
        return self::$loadedPlugins[$pluginName] ?? null;
    }
}

/**
 * Base Plugin Class - Sadeleştirilmiş
 */
abstract class BasePlugin
{
    /**
     * Plugin başlatılırken çalışır
     */
    abstract public function init();

    /**
     * Plugin aktifleştirilirken çalışır
     */
    public function activate()
    {
        // Override edilebilir
    }

    /**
     * Plugin deaktifleştirilirken çalışır
     */
    public function deactivate()
    {
        // Override edilebilir
    }

    /**
     * Event listener ekle
     */
    protected function addAction($event, $callback, $priority = 10)
    {
        Events::listen($event, $callback, $priority);
    }

    /**
     * Route ekle
     */
    protected function addRoute($method, $path, $callback)
    {
        if (strtoupper($method) === 'GET') {
            Sword::routerGet($path, $callback);
        } elseif (strtoupper($method) === 'POST') {
            Sword::routerPost($path, $callback);
        }
    }
}