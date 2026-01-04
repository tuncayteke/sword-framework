<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Controller sınıfı - MVC yapısının kontrolcü bileşeni
 */

namespace Sword;


class Controller
{
    /**
     * View nesnesi
     */
    protected $view;

    /**
     * Request nesnesi
     */
    protected $request;

    /**
     * Response nesnesi
     */
    protected $response;

    /**
     * Layout dosyası
     */
    protected $layout = null;

    /**
     * View dizini
     */
    protected $viewPath = 'views';

    /**
     * Tema tipi
     */
    protected $themeType = 'frontend';

    /**
     * Yapılandırıcı
     */
    public function __construct()
    {
        // Core template fonksiyonlarını yükle
        if (!function_exists('get_header')) {
            require_once __DIR__ . '/Helpers/template.php';
        }

        $this->view = new View('', [], $this->layout);
        $this->request = \Sword::request();
        $this->response = \Sword::response();

        // Layout ayarla
        if (method_exists($this->view, 'setLayout')) {
            $this->view->setLayout($this->layout);
        }

        // Başlangıç işlemleri
        $this->initialize();
    }

    /**
     * Başlangıç işlemleri
     * Alt sınıflar tarafından geçersiz kılınabilir
     */
    protected function initialize()
    {
        // Alt sınıflar tarafından geçersiz kılınabilir
    }

    /**
     * View'a değişken atar
     *
     * @param string $name Değişken adı
     * @param mixed $value Değişken değeri
     * @return Controller
     */
    protected function set($name, $value)
    {
        $this->view->set($name, $value);
        return $this;
    }

    /**
     * Birden fazla değişkeni atar
     *
     * @param array $data Değişken dizisi
     * @return Controller
     */
    protected function setData($data)
    {
        $this->view->setData($data);
        return $this;
    }

    /**
     * Layout dosyasını ayarlar
     *
     * @param string $layout Layout dosyası
     * @return Controller
     */
    protected function setLayout($layout)
    {
        $this->layout = $layout;
        $this->view->setLayout($layout);
        return $this;
    }

    /**
     * Tema için public layout ayarlama
     *
     * @param string $layout Layout dosyası
     * @return Controller
     */
    public function setThemeLayout($layout)
    {
        return $this->setLayout($layout);
    }

    /**
     * Public view render metodu
     *
     * @param string $template Şablon dosyası
     * @param array $data Veriler
     * @return string
     */
    public function renderView($template, $data = [])
    {
        if ($data) {
            $this->setData($data);
        }

        $this->view->setView($template);
        return $this->view->render();
    }

    /**
     * View dizinini ayarlar
     *
     * @param string $path View dizini
     * @return Controller
     */
    protected function setViewPath($path)
    {
        $this->viewPath = $path;
        return $this;
    }

    /**
     * View işler
     *
     * @param string $template Şablon dosyası
     * @param array $data Opsiyonel ek veriler
     * @return string İşlenmiş şablon içeriği
     */
    protected function render($template, $data = null)
    {
        if ($data) {
            $this->setData($data);
        }

        $this->view->setView($template);
        $content = $this->view->render();

        echo $content;
        return $content;
    }

    /**
     * View işler ve ekrana basar
     *
     * @param string $template Şablon dosyası
     * @param array $data Opsiyonel ek veriler
     */
    protected function display($template, $data = null)
    {
        $this->view->display($template, $data);
    }

    /**
     * JSON yanıtı döndürür
     *
     * @param mixed $data Veri
     * @param int $code Durum kodu
     * @return Response
     */
    protected function json($data, $code = 200)
    {
        return $this->response->json($data, $code);
    }

    /**
     * Başarı yanıtı döndürür
     *
     * @param mixed $data Veri
     * @return Response
     */
    protected function success($data = null)
    {
        return $this->response->success($data);
    }

    /**
     * Hata yanıtı döndürür
     *
     * @param string $message Hata mesajı
     * @param int $code Durum kodu
     * @return Response
     */
    protected function error($message, $code = 400)
    {
        return $this->response->error($message, $code);
    }

    /**
     * Yönlendirme yapar
     *
     * @param string $url URL
     * @param int $code Durum kodu
     * @return Response
     */
    protected function redirect($url, $code = 302)
    {
        return $this->response->redirect($url, $code);
    }

    /**
     * Bulunamadı yanıtı döndürür
     *
     * @param string $message Hata mesajı
     * @return Response
     */
    protected function notFound($message = 'Not Found')
    {
        return $this->response->notFound($message);
    }

    /**
     * Yetkisiz yanıtı döndürür
     *
     * @param string $message Hata mesajı
     * @return Response
     */
    protected function unauthorized($message = 'Unauthorized')
    {
        return $this->response->unauthorized($message);
    }

    /**
     * Yasak yanıtı döndürür
     *
     * @param string $message Hata mesajı
     * @return Response
     */
    protected function forbidden($message = 'Forbidden')
    {
        return $this->response->forbidden($message);
    }

    /**
     * Sunucu hatası yanıtı döndürür
     *
     * @param string $message Hata mesajı
     * @return Response
     */
    protected function serverError($message = 'Internal Server Error')
    {
        return $this->response->serverError($message);
    }
}
