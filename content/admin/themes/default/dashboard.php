<?php get_header(); ?>

<div class="admin-container">
    <h1><?= htmlspecialchars($title ?? 'Admin Dashboard') ?></h1>
    
    <div class="stats">
        <div class="stat-box">
            <h3>Kullanıcılar</h3>
            <p><?= $user_count ?? 0 ?></p>
        </div>
        
        <div class="stat-box">
            <h3>Yazılar</h3>
            <p><?= $post_count ?? 0 ?></p>
        </div>
    </div>
    
    <div class="admin-nav">
        <a href="<?= Sword::url('/admin/themes') ?>">Tema Ayarları</a>
    </div>
</div>

<style>
.admin-container { padding: 20px; }
.stats { display: flex; gap: 20px; margin: 20px 0; }
.stat-box { background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; }
.admin-nav a { display: inline-block; margin: 10px; padding: 10px 20px; background: #007cba; color: white; text-decoration: none; border-radius: 4px; }
</style>

<?php get_footer(); ?>