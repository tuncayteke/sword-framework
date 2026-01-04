# Sword Framework Production Checklist

## 🔒 Güvenlik Kontrolleri

### Zorunlu Adımlar:
- [ ] `db_config.production.php` dosyasını `db_config.php` olarak kopyala
- [ ] `CRYPTOR_KEY` değerini güvenli 64 karakterlik anahtar ile değiştir
- [ ] Veritabanı kullanıcısı için minimum yetki ver
- [ ] `.htaccess.production` dosyasını `.htaccess` olarak kopyala
- [ ] `content/storage/` dizinlerinin yazma izinlerini kontrol et (755)
- [ ] `content/uploads/` dizininin yazma izinlerini kontrol et (755)

### Önerilen Adımlar:
- [ ] SSL sertifikası kur (HTTPS)
- [ ] Firewall kuralları ayarla
- [ ] Fail2ban kur (brute force koruması)
- [ ] Regular backup sistemi kur

## ⚡ Performans Optimizasyonları

### Zorunlu:
- [ ] PHP OPcache aktif et
- [ ] Gzip sıkıştırma aktif et
- [ ] Browser cache başlıkları ayarla

### Önerilen:
- [ ] Redis/Memcached cache sistemi kur
- [ ] CDN kullan (statik dosyalar için)
- [ ] Database indexleri optimize et

## 📊 Monitoring & Logging

### Zorunlu:
- [ ] Error log dosyalarının yazılabilir olduğunu kontrol et
- [ ] Log rotasyonu ayarla (logrotate)
- [ ] Disk alanı monitoring kur

### Önerilen:
- [ ] Application monitoring (New Relic, DataDog vb.)
- [ ] Uptime monitoring
- [ ] Performance monitoring

## 🔧 Sunucu Ayarları

### PHP Ayarları:
```ini
; Production PHP ayarları
display_errors = Off
log_errors = On
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
memory_limit = 256M
max_execution_time = 30
upload_max_filesize = 10M
post_max_size = 10M
session.cookie_secure = 1
session.cookie_httponly = 1
```

### Apache/Nginx:
- [ ] Server signature gizle
- [ ] Directory browsing kapat
- [ ] Rate limiting kur
- [ ] Request size limiti ayarla

## 📁 Dosya İzinleri

```bash
# Önerilen dosya izinleri
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 755 content/storage/
chmod 755 content/uploads/
chmod 600 db_config.php
```

## 🚀 Deployment Adımları

1. **Kod Hazırlığı:**
   - [ ] Tüm debug kodları kaldırıldı
   - [ ] Test verileri temizlendi
   - [ ] Production config dosyaları hazırlandı

2. **Sunucu Hazırlığı:**
   - [ ] PHP 7.4+ kurulu
   - [ ] Required extensions kurulu (mysqli, gd, curl, mbstring)
   - [ ] Web server yapılandırıldı

3. **Deployment:**
   - [ ] Dosyalar upload edildi
   - [ ] Config dosyaları ayarlandı
   - [ ] Database migrate edildi
   - [ ] İzinler ayarlandı

4. **Test:**
   - [ ] Ana sayfa çalışıyor
   - [ ] Database bağlantısı çalışıyor
   - [ ] Error handling çalışıyor
   - [ ] Cache sistemi çalışıyor

## ⚠️ Güvenlik Uyarıları

- **Asla** development config dosyalarını production'da kullanma
- **Asla** default şifreleri kullanma
- **Mutlaka** regular güvenlik güncellemeleri yap
- **Mutlaka** backup stratejin olsun

## 📞 Sorun Giderme

### Log Dosyaları:
- PHP errors: `content/storage/logs/php_errors.log`
- Application logs: `content/storage/logs/log-YYYY-MM-DD.log`
- Web server logs: `/var/log/apache2/` veya `/var/log/nginx/`

### Yaygın Sorunlar:
1. **500 Error:** PHP error loglarını kontrol et
2. **404 Error:** .htaccess ve mod_rewrite kontrol et
3. **Database Error:** db_config.php ve bağlantı bilgilerini kontrol et
4. **Permission Error:** Dosya izinlerini kontrol et