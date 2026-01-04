<?php get_header(); ?>

<div class="container">
    <h1><?= htmlspecialchars($title ?? 'Hakkımızda') ?></h1>

    <div class="content">
        <p><?= htmlspecialchars($content ?? '') ?></p>

        <h2>Sword Framework Özellikleri</h2>
        <div class="theme-info">
            <strong>🎨 Tema Sistemi!</strong> Bu sayfa <code>content/themes/default/about.php</code> dosyasından yükleniyor.
        </div>
        <ul>
            <li>WordPress benzeri tema sistemi</li>
            <li>MVC mimarisi</li>
            <li>Eloquent benzeri ORM</li>
            <li>Middleware desteği</li>
            <li>Session yönetimi</li>
            <li>Routing sistemi</li>
        </ul>
    </div>

    <div class="nav">
        <a href="<?= Sword::url('/') ?>">← Ana Sayfa</a>
        <a href="<?= Sword::url('/contact') ?>">İletişim →</a>
    </div>
</div>

<?php get_footer(); ?>