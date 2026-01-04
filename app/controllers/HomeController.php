<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Home Controller - Ana sayfa kontrolcüsü
 */

namespace App\Controllers;

use App\Controllers\FrontendController;
use Sword;

class HomeController extends FrontendController
{

    /**
     * Ana sayfa
     */
    public function index()
    {
        $data = [
            'title' => 'Sword Framework',
            'meta_title' => 'Sword Framework Meta Title',
            'description' => 'Sword Framework hakkında bilgiler...',
            'content' => 'Hoş geldiniz!',
            'version' => Sword::config('app.version', '1.0.0')
        ];

        return view('index', $data);
    }

    /**
     * Hakkında sayfası
     */
    public function about()
    {


        $data = [
            'title' => 'Hakkında', // __() fonksiyonu henüz tanımlı değil
            'content' => 'Sword Framework hakkında bilgiler...'
        ];

        return view('about', $data);
    }

    /**
     * İletişim sayfası
     */
    public function contact()
    {
        if ($this->request->isPost()) {
            // Form verilerini al
            $name = $this->request->post('name');
            $email = $this->request->post('email');
            $message = $this->request->post('message');

            // Basit doğrulama
            if (empty($name) || empty($email) || empty($message)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Tüm alanlar zorunludur' // __() fonksiyonu henüz tanımlı değil
                ]);
            }

            // E-posta gönderme işlemi burada yapılabilir

            return $this->json([
                'success' => true,
                'message' => 'Mesajınız gönderildi'
            ]);
        }

        $data = [
            'title' => 'İletişim'
        ];

        return view('contact', $data);
    }

    /**
     * Shortcode test sayfası
     */
    public function shortcodeTest()
    {


        // Test için session'a kullanıcı bilgisi ekle
        Sword::session('user', [
            'username' => 'test_user',
            'email' => 'test@example.com'
        ]);

        $data = [
            'title' => 'Shortcode Test Sayfası'
        ];

        return view('shortcode-test', $data);
    }

    /**
     * Decorator test sayfası
     */
    public function decoratorTest()
    {
        $data = [
            'title' => 'Decorator Test Sayfası',
            'content' => 'Bu sayfa %year% yılında oluşturuldu. Bugün %datetime%. Site: %site_name% v%version%'
        ];

        return view('decorator-test', $data);
    }
}
