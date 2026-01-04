<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Türkçe Dil Dosyası - Core
 */

return [
    // Genel
    'welcome' => 'Hoş geldiniz',
    'hello' => 'Merhaba',
    'goodbye' => 'Hoşça kal',
    'yes' => 'Evet',
    'no' => 'Hayır',
    'save' => 'Kaydet',
    'cancel' => 'İptal',
    'delete' => 'Sil',
    'edit' => 'Düzenle',
    'view' => 'Görüntüle',
    'back' => 'Geri',
    'next' => 'İleri',
    'loading' => 'Yükleniyor...',

    // Kullanıcı
    'user' => [
        'not_found' => 'Kullanıcı bulunamadı',
        'created' => 'Kullanıcı oluşturuldu',
        'updated' => 'Kullanıcı güncellendi',
        'deleted' => 'Kullanıcı silindi',
        'login_required' => 'Giriş yapmalısınız'
    ],

    // Doğrulama
    'validation' => [
        'required' => ':field alanı gereklidir',
        'email' => ':field geçerli bir e-posta olmalıdır',
        'min' => ':field en az :min karakter olmalıdır',
        'max' => ':field en fazla :max karakter olmalıdır',
        'unique' => ':field zaten kullanılıyor'
    ],

    // Hata mesajları
    'error' => [
        'general' => 'Bir hata oluştu',
        'not_found' => 'Sayfa bulunamadı',
        'unauthorized' => 'Yetkisiz erişim',
        'forbidden' => 'Erişim reddedildi',
        'server_error' => 'Sunucu hatası'
    ],

    // Upload mesajları
    'upload' => [
        'directory_create_failed' => 'Yükleme dizini oluşturulamadı: :path',
        'file_upload_failed' => 'Dosya yüklenemedi: :name',
        'invalid_upload' => 'Geçersiz dosya yükleme işlemi',
        'file_too_large' => 'Dosya boyutu çok büyük. Maksimum boyut: :max MB',
        'unsupported_type' => 'Dosya türü desteklenmiyor: :type',
        'ini_size_exceeded' => 'Dosya boyutu PHP yapılandırmasında izin verilen maksimum boyutu aşıyor',
        'form_size_exceeded' => 'Dosya boyutu formda belirtilen maksimum boyutu aşıyor',
        'partial_upload' => 'Dosya sadece kısmen yüklendi',
        'no_file' => 'Dosya yüklenmedi',
        'no_tmp_dir' => 'Geçici klasör eksik',
        'cant_write' => 'Dosya diske yazılamadı',
        'extension_stopped' => 'Bir PHP uzantısı dosya yüklemesini durdurdu',
        'unknown_error' => 'Bilinmeyen bir hata oluştu'
    ],

    // Model mesajları
    'model' => [
        'admin_cannot_delete' => 'Admin kullanıcılar silinemez',
        'username_taken' => 'Bu kullanıcı adı zaten kullanılıyor',
        'user_created' => 'Kullanıcı eklendi: ID=:id, Username=:username',
        'user_updated' => 'Kullanıcı güncellendi: ID=:id',
        'user_deleted' => 'Kullanıcı silindi: ID=:id'
    ],

    // Form mesajları
    'form' => [
        'all_fields_required' => 'Tüm alanları doldurunuz',
        'message_sent' => 'Mesajınız başarıyla gönderildi',
        'message_failed' => 'Mesaj gönderilemedi'
    ],

    // Başarı mesajları
    'success' => [
        'saved' => 'Başarıyla kaydedildi',
        'updated' => 'Başarıyla güncellendi',
        'deleted' => 'Başarıyla silindi',
        'sent' => 'Başarıyla gönderildi'
    ]
];
