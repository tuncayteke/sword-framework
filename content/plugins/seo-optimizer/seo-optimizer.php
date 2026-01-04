<?php

/**
 * SEO Optimizer Plugin
 */
class SeoOptimizerPlugin extends BasePlugin
{
    public function init()
    {
        // Meta tag'leri otomatik ekle
        $this->addAction('before_render', [$this, 'addMetaTags']);
        
        // Admin menüye ekle
        $this->addMenu('admin', 'SEO Ayarları', '/admin/seo', [
            'icon' => 'search',
            'order' => 10
        ]);
        
        // SEO rotaları ekle
        $this->addRoute('GET', '/admin/seo', [$this, 'adminPage']);
        $this->addRoute('POST', '/admin/seo/save', [$this, 'saveSettings']);
        
        // Sitemap oluştur
        $this->addRoute('GET', '/sitemap.xml', [$this, 'generateSitemap']);
        
        // Footer menüye sitemap ekle
        $this->addMenu('footer', 'Sitemap', '/sitemap.xml', ['order' => 20]);
    }

    public function activate()
    {
        // Aktivasyon sırasında SEO tablosu oluştur
        $this->createSeoTable();
    }

    public function deactivate()
    {
        // Deaktivasyon işlemleri
    }

    public function addMetaTags($viewData)
    {
        // Mevcut sayfa bilgisini al
        $pageType = $viewData['page_type'] ?? 'page';
        $pageId = $viewData['page_id'] ?? null;
        
        $seoData = $this->getSeoData($pageType, $pageId);
        
        if ($seoData) {
            $viewData['meta_title'] = $seoData['title'];
            $viewData['meta_description'] = $seoData['description'];
            $viewData['meta_keywords'] = $seoData['keywords'];
        }
        
        return $viewData;
    }

    public function adminPage()
    {
        $settings = $this->getSettings();
        
        return Sword::view('plugins/seo-optimizer/admin', [
            'title' => 'SEO Ayarları',
            'settings' => $settings
        ]);
    }

    public function saveSettings()
    {
        $settings = [
            'site_title' => $_POST['site_title'] ?? '',
            'site_description' => $_POST['site_description'] ?? '',
            'default_keywords' => $_POST['default_keywords'] ?? '',
            'google_analytics' => $_POST['google_analytics'] ?? ''
        ];
        
        $this->saveSettings($settings);
        
        return Sword::redirect('/admin/seo')->with('success', 'Ayarlar kaydedildi');
    }

    public function generateSitemap()
    {
        header('Content-Type: application/xml');
        
        $urls = [
            ['url' => '/', 'priority' => '1.0'],
            ['url' => '/products', 'priority' => '0.8'],
            ['url' => '/about', 'priority' => '0.6'],
            ['url' => '/contact', 'priority' => '0.5']
        ];
        
        // Dinamik ürün URL'leri ekle
        $products = Sword::db()->table('products')->where('active', 1)->get();
        foreach ($products as $product) {
            $urls[] = [
                'url' => '/product/' . $product->slug,
                'priority' => '0.7'
            ];
        }
        
        echo $this->generateSitemapXml($urls);
    }

    private function createSeoTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS seo_meta (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_type VARCHAR(50),
            page_id INT,
            title VARCHAR(255),
            description TEXT,
            keywords VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_page (page_type, page_id)
        )";
        
        Sword::db()->query($sql);
    }

    private function getSeoData($pageType, $pageId)
    {
        if (!$pageId) {
            return null;
        }
        
        return Sword::db()->table('seo_meta')
            ->where('page_type', $pageType)
            ->where('page_id', $pageId)
            ->first();
    }

    private function getSettings()
    {
        return $this->getSettings();
    }

    private function generateSitemapXml($urls)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . 'https://' . $_SERVER['HTTP_HOST'] . $url['url'] . '</loc>' . "\n";
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
}