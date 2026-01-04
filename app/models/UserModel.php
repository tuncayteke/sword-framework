<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * UserModel sınıfı - Kullanıcı modeli
 */

namespace App\Models;

use Model;

class UserModel extends Model
{
    // Tablo adı
    protected $table = 'users';

    // Doldurulabilir alanlar
    protected $fillable = ['username', 'email', 'password', 'full_name', 'status'];

    // Korunan alanlar
    protected $guarded = ['id', 'created_at', 'updated_at'];

    // Gizli alanlar
    protected $hidden = ['password'];

    // Doğrulama kuralları
    protected $validationRules = [
        'username' => 'required|min:3|max:50|alphanumeric',
        'email' => 'required|email',
        'password' => 'required|min:6',
        'full_name' => 'required|min:3|max:100',
        'status' => 'in:active,inactive,pending'
    ];

    // Doğrulama mesajları
    protected $validationMessages = [
        'username.required' => 'Kullanıcı adı gereklidir.',
        'username.min' => 'Kullanıcı adı en az :param karakter olmalıdır.',
        'email.email' => 'Geçerli bir e-posta adresi giriniz.',
        'password.min' => 'Şifre en az :param karakter olmalıdır.'
    ];

    // Soft delete kullanımı
    protected $softDelete = true;

    /**
     * Kayıt eklemeden önce çalışır
     *
     * @param array $data Eklenecek veriler
     * @return array|bool Veriler veya başarısızsa false
     */
    protected function beforeInsert($data)
    {
        // Şifreyi hashle
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // E-posta adresini küçük harfe çevir
        if (isset($data['email'])) {
            $data['email'] = strtolower($data['email']);
        }

        // Kullanıcı adının benzersiz olduğunu kontrol et
        $existingUser = $this->findWhere('username = :username', ['username' => $data['username']]);
        if ($existingUser) {
            $this->lastError = __('model.username_taken');
            return false;
        }

        return $data;
    }

    /**
     * Kayıt ekledikten sonra çalışır
     *
     * @param int $insertId Eklenen kaydın ID'si
     * @param array $data Eklenen veriler
     * @return bool Başarılı mı?
     */
    protected function afterInsert($insertId, $data)
    {
        // Kullanıcı rolünü ekle
        $roleData = [
            'user_id' => $insertId,
            'role' => 'user'
        ];

        // Rol tablosuna ekle (örnek)
        // $this->db->insert('user_roles', $roleData);

        // Log kaydı oluştur
        if (class_exists('Logger')) {
            Logger::info("Kullanıcı eklendi: ID={$insertId}, Username={$data['username']}");
        }

        return true;
    }

    /**
     * Kayıt güncellemeden önce çalışır
     *
     * @param int $id Güncellenecek kaydın ID'si
     * @param array $data Güncellenecek veriler
     * @return array|bool Veriler veya başarısızsa false
     */
    protected function beforeUpdate($id, $data)
    {
        // Şifre değiştiriliyorsa hashle
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // E-posta adresini küçük harfe çevir
        if (isset($data['email'])) {
            $data['email'] = strtolower($data['email']);
        }

        // Kullanıcı adı değiştiriliyorsa benzersiz olduğunu kontrol et
        if (isset($data['username'])) {
            $existingUser = $this->findWhere('username = :username AND id != :id', [
                'username' => $data['username'],
                'id' => $id
            ]);

            if ($existingUser) {
                $this->lastError = __('model.username_taken');
                return false;
            }
        }

        return $data;
    }

    /**
     * Kayıt güncelledikten sonra çalışır
     *
     * @param int $id Güncellenen kaydın ID'si
     * @param array $data Güncellenen veriler
     * @return bool Başarılı mı?
     */
    protected function afterUpdate($id, $data)
    {
        // Log kaydı oluştur
        if (class_exists('Logger')) {
            Logger::info("Kullanıcı güncellendi: ID={$id}");
        }

        return true;
    }

    /**
     * Kayıt silmeden önce çalışır
     *
     * @param int $id Silinecek kaydın ID'si
     * @return bool Başarılı mı?
     */
    protected function beforeDelete($id)
    {
        // Kullanıcının admin olup olmadığını kontrol et
        $user = $this->find($id);
        if ($user && isset($user['role']) && $user['role'] === 'admin') {
            $this->lastError = __('model.admin_cannot_delete');
            return false;
        }

        return true;
    }

    /**
     * Kayıt sildikten sonra çalışır
     *
     * @param int $id Silinen kaydın ID'si
     * @return bool Başarılı mı?
     */
    protected function afterDelete($id)
    {
        // İlişkili kayıtları sil (örnek)
        // $this->db->delete('user_roles', 'user_id = :user_id', ['user_id' => $id]);

        // Log kaydı oluştur
        if (class_exists('Logger')) {
            Logger::info("Kullanıcı silindi: ID={$id}");
        }

        return true;
    }

    /**
     * Kullanıcıyı e-posta adresine göre bulur
     *
     * @param string $email E-posta adresi
     * @return array|null Kullanıcı
     */
    public function findByEmail($email)
    {
        return $this->findWhere('email = :email', ['email' => strtolower($email)]);
    }

    /**
     * Kullanıcının şifresini doğrular
     *
     * @param string $password Şifre
     * @param string $hash Hash
     * @return bool Doğru mu?
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Aktif kullanıcıları döndürür
     *
     * @return array Aktif kullanıcılar
     */
    public function findActive()
    {
        return $this->findAllWhere('status = :status', ['status' => 'active']);
    }

    /**
     * Kullanıcıları sorgu oluşturucu ile arar
     *
     * @param string $search Arama terimi
     * @return array Kullanıcılar
     */
    public function search($search)
    {
        return $this->newQuery()
            ->where('username', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->orWhere('full_name', 'LIKE', "%{$search}%")
            ->orderBy('username')
            ->get();
    }
}
