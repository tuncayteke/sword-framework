<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Guard sınıfı - Kimlik doğrulama koruyucusu
 */

namespace Sword\Auth;

class Guard
{
    /**
     * Guard adı
     */
    protected $name;

    /**
     * Guard yapılandırması
     */
    protected $config;

    /**
     * Kullanıcı modeli
     */
    protected $userModel = 'User';

    /**
     * Giriş denemeleri
     */
    protected $attempts = [];

    /**
     * Maksimum deneme sayısı
     */
    protected $maxAttempts = 5;

    /**
     * Bekleme süresi (dakika)
     */
    protected $lockoutTime = 10;

    /**
     * Yapılandırıcı
     *
     * @param string $name Guard adı
     * @param array $config Yapılandırma
     */
    public function __construct($name, array $config = [])
    {
        $this->name = $name;
        $this->config = $config;
    }

    /**
     * Giriş yapmaya çalışır
     *
     * @param array $credentials Kimlik bilgileri
     * @param bool $remember Beni hatırla
     * @return bool
     */
    public function attempt(array $credentials, $remember = false)
    {
        // Rate limiting kontrolü
        if ($this->hasTooManyLoginAttempts()) {
            return false;
        }

        $user = $this->retrieveByCredentials($credentials);

        if ($user && $this->validateCredentials($user, $credentials)) {
            $this->login($user, $remember);
            $this->clearLoginAttempts();
            return true;
        }

        $this->incrementLoginAttempts();
        return false;
    }

    /**
     * Kullanıcıyı giriş yapar
     *
     * @param mixed $user Kullanıcı
     * @param bool $remember Beni hatırla
     * @return void
     */
    public function login($user, $remember = false)
    {
        // Session'ı yenile
        if (function_exists('session')) {
            \Session::regenerate();
            \Session::set('user_id', $user->id);
            \Session::set('guard', $this->name);
        }

        // Remember token
        if ($remember) {
            $token = $this->generateRememberToken();
            $this->setRememberCookie($token);
            $user->remember_token = hash('sha256', $token);
            $user->save();
        }

        // Event tetikle
        if (function_exists('event')) {
            event('auth.login', ['user' => $user, 'guard' => $this->name]);
        }
    }

    /**
     * Kullanıcıyı ID ile giriş yapar
     *
     * @param mixed $id Kullanıcı ID
     * @param bool $remember Beni hatırla
     * @return bool
     */
    public function loginUsingId($id, $remember = false)
    {
        $user = $this->retrieveById($id);

        if ($user) {
            $this->login($user, $remember);
            return true;
        }

        return false;
    }

    /**
     * Aktif kullanıcıyı döndürür
     *
     * @return mixed|null
     */
    public function user()
    {
        static $user = null;

        if ($user !== null) {
            return $user;
        }

        // Session'dan kontrol et
        if (function_exists('session') && \Session::has('user_id')) {
            $user = $this->retrieveById(\Session::get('user_id'));
            if ($user) {
                return $user;
            }
        }

        // Remember token'dan kontrol et
        $user = $this->userFromRememberCookie();
        if ($user) {
            $this->login($user);
            return $user;
        }

        return null;
    }

    /**
     * Kullanıcı giriş yapmış mı?
     *
     * @return bool
     */
    public function check()
    {
        return $this->user() !== null;
    }

    /**
     * Kullanıcı misafir mi?
     *
     * @return bool
     */
    public function guest()
    {
        return !$this->check();
    }

    /**
     * Kullanıcı ID'sini döndürür
     *
     * @return mixed|null
     */
    public function id()
    {
        return $this->user()?->id;
    }

    /**
     * Çıkış yapar
     *
     * @return void
     */
    public function logout()
    {
        $user = $this->user();

        // Session temizle
        if (function_exists('session')) {
            \Session::forget('user_id');
            \Session::forget('guard');
            \Session::regenerate();
        }

        // Remember cookie temizle
        $this->clearRememberCookie();

        // Remember token temizle
        if ($user && isset($user->remember_token)) {
            $user->remember_token = null;
            $user->save();
        }

        // Event tetikle
        if (function_exists('event')) {
            event('auth.logout', ['user' => $user, 'guard' => $this->name]);
        }
    }

    /**
     * Kimlik bilgilerine göre kullanıcı arar
     *
     * @param array $credentials Kimlik bilgileri
     * @return mixed|null
     */
    protected function retrieveByCredentials(array $credentials)
    {
        if (!class_exists($this->userModel)) {
            return null;
        }

        $query = $this->userModel::query();

        foreach ($credentials as $key => $value) {
            if ($key !== 'password') {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }

    /**
     * ID'ye göre kullanıcı arar
     *
     * @param mixed $id Kullanıcı ID
     * @return mixed|null
     */
    protected function retrieveById($id)
    {
        if (!class_exists($this->userModel)) {
            return null;
        }

        return $this->userModel::find($id);
    }

    /**
     * Kimlik bilgilerini doğrular
     *
     * @param mixed $user Kullanıcı
     * @param array $credentials Kimlik bilgileri
     * @return bool
     */
    protected function validateCredentials($user, array $credentials)
    {
        return isset($credentials['password']) &&
            password_verify($credentials['password'], $user->password);
    }

    /**
     * Remember token oluşturur
     *
     * @return string
     */
    protected function generateRememberToken()
    {
        return bin2hex(random_bytes(30));
    }

    /**
     * Remember cookie ayarlar
     *
     * @param string $token Token
     * @return void
     */
    protected function setRememberCookie($token)
    {
        setcookie(
            'remember_' . $this->name,
            $token,
            time() + (30 * 24 * 60 * 60), // 30 gün
            '/',
            '',
            isset($_SERVER['HTTPS']),
            true
        );
    }

    /**
     * Remember cookie'den kullanıcı alır
     *
     * @return mixed|null
     */
    protected function userFromRememberCookie()
    {
        $token = $_COOKIE['remember_' . $this->name] ?? null;

        if (!$token || !class_exists($this->userModel)) {
            return null;
        }

        return $this->userModel::where('remember_token', hash('sha256', $token))->first();
    }

    /**
     * Remember cookie temizler
     *
     * @return void
     */
    protected function clearRememberCookie()
    {
        setcookie(
            'remember_' . $this->name,
            '',
            time() - 3600,
            '/',
            '',
            isset($_SERVER['HTTPS']),
            true
        );
    }

    /**
     * Giriş denemelerini artırır
     *
     * @return void
     */
    protected function incrementLoginAttempts()
    {
        $key = $this->throttleKey();
        $this->attempts[$key] = ($this->attempts[$key] ?? 0) + 1;
    }

    /**
     * Giriş denemelerini temizler
     *
     * @return void
     */
    protected function clearLoginAttempts()
    {
        $key = $this->throttleKey();
        unset($this->attempts[$key]);
    }

    /**
     * Çok fazla giriş denemesi var mı?
     *
     * @return bool
     */
    protected function hasTooManyLoginAttempts()
    {
        $key = $this->throttleKey();
        return ($this->attempts[$key] ?? 0) >= $this->maxAttempts;
    }

    /**
     * Throttle anahtarı oluşturur
     *
     * @return string
     */
    protected function throttleKey()
    {
        return 'login_attempts_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }
}
