<?php get_header(); ?>

<div class="container">
    <h1><?= htmlspecialchars($title ?? 'Sword Framework') ?></h1>

    <div class="theme-info">
        <strong>🎨 Tema Sistemi!</strong> Bu sayfa <code>content/themes/default/index.php</code> dosyasından yükleniyor.
    </div>

    <div class="nav">
        <a href="<?= Sword::url('/') ?>">Ana Sayfa</a>
        <a href="<?= Sword::url('/about') ?>">Hakkımızda</a>
        <a href="<?= Sword::url('/contact') ?>">İletişim</a>

        <a href="<?= Sword::url('/shortcode-test') ?>">Shortcode Test</a>

        <a href="<?= Sword::url('/decorator-test') ?>">Decotator Test</a>

        <a href="<?= Sword::url('/decorator-test-api') ?>">Decotator Test Api</a>
        <a href="<?= Sword::url('/admin') ?>">Admin Panel</a>
    </div>
    <p><?= htmlspecialchars($content ?? '') ?></p>

    <p><?= htmlspecialchars($description ?? '') ?></p>

    <div class="features">
        <div class="feature">
            <h3>🎨 WordPress Benzeri Tema</h3>
            <p>Tema dosyaları doğrudan tema dizininde</p>
        </div>
        <div class="feature">
            <h3>🗄️ Veritabanı Entegrasyonu</h3>
            <p>Tema ayarları veritabanından yönetiliyor</p>
        </div>
        <div class="feature">
            <h3>🔧 Kolay Yönetim</h3>
            <p>Admin panelden tema değiştirme</p>
        </div>

    </div>
    <?php get_sidebar(); ?>
</div>


<?php get_footer(); ?>