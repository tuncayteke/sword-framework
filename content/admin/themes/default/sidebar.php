<div class="admin-sidebar-extra">
    <div class="sidebar-widget">
        <h4>Hızlı İşlemler</h4>
        <ul class="quick-actions">
            <li><a href="<?= Sword::url('/admin/users/create') ?>">+ Yeni Kullanıcı</a></li>
            <li><a href="<?= Sword::url('/admin/posts/create') ?>">+ Yeni İçerik</a></li>
            <li><a href="<?= Sword::url('/admin/settings') ?>">⚙️ Ayarlar</a></li>
        </ul>
    </div>
    
    <div class="sidebar-widget">
        <h4>Sistem Durumu</h4>
        <div class="system-status">
            <div class="status-item">
                <span class="status-label">PHP:</span>
                <span class="status-value"><?= PHP_VERSION ?></span>
            </div>
            <div class="status-item">
                <span class="status-label">Bellek:</span>
                <span class="status-value"><?= ini_get('memory_limit') ?></span>
            </div>
        </div>
    </div>
</div>

<style>
.admin-sidebar-extra { margin-top: 20px; }
.sidebar-widget { background: #4a5568; padding: 15px; border-radius: 6px; margin-bottom: 15px; }
.sidebar-widget h4 { color: #e2e8f0; margin: 0 0 10px 0; font-size: 14px; }
.quick-actions { list-style: none; padding: 0; margin: 0; }
.quick-actions li { margin: 8px 0; }
.quick-actions a { color: #cbd5e0; text-decoration: none; font-size: 13px; }
.quick-actions a:hover { color: white; }
.system-status { font-size: 12px; }
.status-item { display: flex; justify-content: space-between; margin: 5px 0; }
.status-label { color: #a0aec0; }
.status-value { color: #e2e8f0; font-weight: bold; }
</style>