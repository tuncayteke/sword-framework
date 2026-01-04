<?php get_header(); ?>

<div class="container">
    <h1><?php echo $title ?? 'Shortcode Test'; ?> hh</h1>

    <div class="content">
        <h2>Shortcode Örnekleri</h2>
        <div class="theme-info">
            <strong>🎨 Tema Sistemi!</strong> Bu sayfa <code>content/themes/default/shortcode-test.php</code> dosyasından yükleniyor. Ve <code>functions.php</code> deki shortcode lar ile şekilleniyor.
        </div>

        <h3>Button Shortcode:</h3>
        <p>[button text="Ana Sayfa" url="<?= Sword::url('/') ?>" class="btn btn-success"]</p>
        <p>[button text="Hakkında" url="<?= Sword::url('/about') ?>"]</p>

        <h3>Alert Shortcode:</h3>
        <p>[alert type="success" message="İşlem başarılı!"]</p>
        <p>[alert type="warning" message="Dikkat edilmesi gereken bir durum"]</p>

        <h3>User Info Shortcode:</h3>
        <p>Kullanıcı: [user_info field="username"]</p>
        <p>Email: [user_info field="email"]</p>

        <h3>Date Shortcode:</h3>
        <p>Bugün: [date format="d.m.Y"]</p>
        <p>Şimdi: [date format="d.m.Y H:i:s"]</p>

        <h3>Karışık Kullanım:</h3>
        <div class="mixed-content">
            <p>Merhaba [user_info field="username"], bugün [date format="d.m.Y"] tarihinde sitemizi ziyaret ediyorsunuz.</p>

            [alert type="info" message="Bu bir shortcode test sayfasıdır."]

            <p>Ana sayfaya dönmek için: [button text="Ana Sayfa" url="<?= Sword::url('/') ?>" class="btn btn-primary btn-sm"]</p>
        </div>
    </div>
</div>

<?php get_footer(); ?>