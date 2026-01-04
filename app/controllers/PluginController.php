<?php

/**
 * Plugin Yönetim Controller'ı
 */
class PluginController extends Controller
{
    public function index()
    {
        $plugins = Plugin::getAvailablePlugins();
        
        return $this->render('admin/plugins/index', [
            'title' => 'Eklentiler',
            'plugins' => $plugins
        ]);
    }

    public function activate()
    {
        $pluginName = $this->request->post('plugin');
        
        if (Plugin::activate($pluginName)) {
            $this->setFlash('success', 'Eklenti başarıyla aktifleştirildi.');
        } else {
            $this->setFlash('error', 'Eklenti aktifleştirilemedi.');
        }
        
        return $this->redirect('/admin/plugins');
    }

    public function deactivate()
    {
        $pluginName = $this->request->post('plugin');
        
        if (Plugin::deactivate($pluginName)) {
            $this->setFlash('success', 'Eklenti başarıyla deaktifleştirildi.');
        } else {
            $this->setFlash('error', 'Eklenti deaktifleştirilemedi.');
        }
        
        return $this->redirect('/admin/plugins');
    }

    public function install()
    {
        if (!$this->request->hasFile('plugin_file')) {
            $this->setFlash('error', 'Eklenti dosyası seçilmedi.');
            return $this->redirect('/admin/plugins');
        }

        $file = $this->request->file('plugin_file');
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Dosya yükleme hatası.');
            return $this->redirect('/admin/plugins');
        }

        // ZIP dosyası kontrolü
        if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'zip') {
            $this->setFlash('error', 'Sadece ZIP dosyaları kabul edilir.');
            return $this->redirect('/admin/plugins');
        }

        // ZIP'i aç ve kur
        $result = $this->installPluginFromZip($file['tmp_name']);
        
        if ($result['success']) {
            $this->setFlash('success', 'Eklenti başarıyla kuruldu: ' . $result['plugin']);
        } else {
            $this->setFlash('error', 'Eklenti kurulumu başarısız: ' . $result['error']);
        }
        
        return $this->redirect('/admin/plugins');
    }

    private function installPluginFromZip($zipFile)
    {
        $zip = new ZipArchive();
        
        if ($zip->open($zipFile) !== TRUE) {
            return ['success' => false, 'error' => 'ZIP dosyası açılamadı'];
        }

        // Plugin adını bul
        $pluginName = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (strpos($filename, 'plugin.json') !== false) {
                $pluginName = dirname($filename);
                break;
            }
        }

        if (!$pluginName) {
            $zip->close();
            return ['success' => false, 'error' => 'Geçersiz eklenti formatı'];
        }

        // Plugin dizinine çıkart
        $pluginDir = 'content/plugins/' . $pluginName . '/';
        
        if (!is_dir($pluginDir)) {
            mkdir($pluginDir, 0755, true);
        }

        $zip->extractTo('content/plugins/');
        $zip->close();

        return ['success' => true, 'plugin' => $pluginName];
    }

    public function delete()
    {
        $pluginName = $this->request->post('plugin');
        
        // Önce deaktifleştir
        Plugin::deactivate($pluginName);
        
        // Plugin dizinini sil
        $pluginDir = 'content/plugins/' . $pluginName . '/';
        
        if (is_dir($pluginDir)) {
            $this->deleteDirectory($pluginDir);
            $this->setFlash('success', 'Eklenti başarıyla silindi.');
        } else {
            $this->setFlash('error', 'Eklenti bulunamadı.');
        }
        
        return $this->redirect('/admin/plugins');
    }

    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        
        return rmdir($dir);
    }
}