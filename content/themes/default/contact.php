<?php get_header(); ?>

<div class="container">
    <h1><?= htmlspecialchars($title ?? 'İletişim') ?></h1>
    <div class="theme-info">
        <strong>🎨 Tema Sistemi!</strong> Bu sayfa <code>content/themes/default/contact.php</code> dosyasından yükleniyor.
    </div>

    <div class="contact-form">
        <form method="POST" action="<?= Sword::url('/contact') ?>">
            <div class="form-group">
                <label for="name">Adınız:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="email">E-posta:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="message">Mesajınız:</label>
                <textarea id="message" name="message" rows="5" required></textarea>
            </div>

            <button type="submit">Gönder</button>
        </form>
    </div>

    <div class="nav">
        <a href="<?= Sword::url('/') ?>">← Ana Sayfa</a>
        <a href="<?= Sword::url('/about') ?>">Hakkımızda</a>
    </div>
</div>

<style>
    .contact-form {
        margin: 30px 0;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    button {
        background: #3498db;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background: #2980b9;
    }
</style>

<?php get_footer(); ?>