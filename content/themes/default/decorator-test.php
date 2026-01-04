<?php get_header(); ?>

<div class="container">
    <h1><?= htmlspecialchars($title ?? 'Decorator Test') ?></h1>

    <div class="decorator-content">
        <h2>Otomatik Decorator Test</h2>
        <p><?= $content ?></p>
        
        <div class="decorator-examples">
            <h3>Decorator Örnekleri:</h3>
            <ul>
                <li>Yıl: %year%</li>
                <li>Tarih-Zaman: %datetime%</li>
                <li>Site Adı: %site_name%</li>
                <li>Versiyon: %version%</li>
            </ul>
        </div>
        
        <div class="info">
            <p><strong>Not:</strong> Bu placeholder'lar otomatik olarak Response sınıfı tarafından değiştirilecek.</p>
        </div>
    </div>
</div>

<?php get_footer(); ?>