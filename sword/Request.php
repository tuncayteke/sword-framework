<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Request sınıfı - HTTP isteklerini yönetir
 */

class Request
{
    /**
     * POST verileri
     */
    private $postData = [];

    /**
     * GET verileri
     */
    private $getData = [];

    /**
     * PUT verileri
     */
    private $putData = [];

    /**
     * DELETE verileri
     */
    private $deleteData = [];

    /**
     * Dosya verileri
     */
    private $fileData = [];

    /**
     * Header verileri
     */
    private $headerData = [];

    /**
     * Server verileri
     */
    private $serverData = [];

    /**
     * URI segmentleri
     */
    private $segments = [];

    /**
     * İstek gövdesi
     */
    private $body = null;

    /**
     * Yapılandırıcı
     */
    public function __construct()
    {
        $this->postData = $_POST;
        $this->getData = $_GET;
        $this->fileData = $_FILES;
        $this->serverData = $_SERVER;

        // PUT ve DELETE verilerini al (JSON + form-urlencoded + multipart destekli)
        if ($this->isPut() || $this->isDelete()) {
            $input = file_get_contents('php://input');
            $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

            // 1. JSON kontrolü (en yaygın)
            if (stripos($contentType, 'application/json') !== false) {
                $data = json_decode($input, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->isPut() ? $this->putData = $data : $this->deleteData = $data;
                }
            }
            // 2. Form-urlencoded kontrolü (PUT/DELETE form submit)
            elseif (stripos($contentType, 'application/x-www-form-urlencoded') !== false || empty($contentType)) {
                parse_str($input, $data);
                $this->isPut() ? $this->putData = $data : $this->deleteData = $data;
            }
            // 3. Fallback: boşsa bile parse_str dene (bazı sunucular Content-Type göndermez)
            elseif (empty($this->putData) && empty($this->deleteData)) {
                parse_str($input, $data);
                $this->isPut() ? $this->putData = $data : $this->deleteData = $data;
            }
        }

        // Header verilerini al
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        $this->headerData = $headers;

        // URI segmentlerini al
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->segments = explode('/', trim($uri, '/'));
    }

    /**
     * İstek metodu POST mu?
     *
     * @return bool
     */
    public function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * POST verisi var mı?
     *
     * @param string $key Anahtar
     * @return bool
     */
    public function hasPost($key)
    {
        return isset($this->postData[$key]);
    }

    /**
     * Tüm POST verilerini döndürür
     *
     * @return array
     */
    public function posts()
    {
        return $this->postData;
    }

    /**
     * Belirtilen POST verisini döndürür
     *
     * @param string $key Anahtar
     * @param mixed $default Varsayılan değer
     * @return mixed
     */
    public function post($key = null, $default = null)
    {
        if ($key === null) {
            return $this->postData;
        }
        return isset($this->postData[$key]) ? $this->postData[$key] : $default;
    }

    /**
     * İstek metodu GET mi?
     *
     * @return bool
     */
    public function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * GET verisi var mı?
     *
     * @param string $key Anahtar
     * @return bool
     */
    public function hasGet($key)
    {
        return isset($this->getData[$key]);
    }

    /**
     * Tüm GET verilerini döndürür
     *
     * @return array
     */
    public function gets()
    {
        return $this->getData;
    }

    /**
     * Belirtilen GET verisini döndürür
     *
     * @param string $key Anahtar
     * @param mixed $default Varsayılan değer
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        if ($key === null) {
            return $this->getData;
        }
        return isset($this->getData[$key]) ? $this->getData[$key] : $default;
    }

    /**
     * İstek metodu PUT mu?
     *
     * @return bool
     */
    public function isPut()
    {
        return $_SERVER['REQUEST_METHOD'] === 'PUT' ||
            ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && strtoupper($_POST['_method']) === 'PUT');
    }

    /**
     * PUT verisi var mı?
     *
     * @param string $key Anahtar
     * @return bool
     */
    public function hasPut($key)
    {
        return isset($this->putData[$key]);
    }

    /**
     * Tüm PUT verilerini döndürür
     *
     * @return array
     */
    public function puts()
    {
        return $this->putData;
    }

    /**
     * Belirtilen PUT verisini döndürür
     *
     * @param string $key Anahtar
     * @param mixed $default Varsayılan değer
     * @return mixed
     */
    public function put($key = null, $default = null)
    {
        if ($key === null) {
            return $this->putData;
        }
        return isset($this->putData[$key]) ? $this->putData[$key] : $default;
    }

    /**
     * İstek metodu DELETE mi?
     *
     * @return bool
     */
    public function isDelete()
    {
        return $_SERVER['REQUEST_METHOD'] === 'DELETE' ||
            ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && strtoupper($_POST['_method']) === 'DELETE');
    }

    /**
     * DELETE verisi var mı?
     *
     * @param string $key Anahtar
     * @return bool
     */
    public function hasDelete($key)
    {
        return isset($this->deleteData[$key]);
    }

    /**
     * Tüm DELETE verilerini döndürür
     *
     * @return array
     */
    public function deletes()
    {
        return $this->deleteData;
    }

    /**
     * Belirtilen DELETE verisini döndürür
     *
     * @param string $key Anahtar
     * @param mixed $default Varsayılan değer
     * @return mixed
     */
    public function delete($key = null, $default = null)
    {
        if ($key === null) {
            return $this->deleteData;
        }
        return isset($this->deleteData[$key]) ? $this->deleteData[$key] : $default;
    }

    /**
     * İstek AJAX mı?
     *
     * @return bool
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * İstek AJAX GET mi?
     *
     * @return bool
     */
    public function isAjaxGet()
    {
        return $this->isAjax() && $this->isGet();
    }

    /**
     * İstek AJAX POST mu?
     *
     * @return bool
     */
    public function isAjaxPost()
    {
        return $this->isAjax() && $this->isPost();
    }

    /**
     * AJAX verilerini döndürür
     *
     * @return array
     */
    public function ajax()
    {
        if ($this->isAjaxGet()) {
            return $this->getData;
        } elseif ($this->isAjaxPost()) {
            return $this->postData;
        } elseif ($this->isAjax() && $this->isPut()) {
            return $this->putData;
        } elseif ($this->isAjax() && $this->isDelete()) {
            return $this->deleteData;
        }
        return [];
    }

    /**
     * URI segmentlerini döndürür
     *
     * @return array
     */
    public function segments()
    {
        return $this->segments;
    }

    /**
     * Belirtilen segment var mı?
     *
     * @param string $segment Segment
     * @return bool
     */
    public function hasSegment($segment)
    {
        if (strpos($segment, ':') === 0) {
            // Placeholder segment kontrolü
            $placeholderName = substr($segment, 1);
            foreach ($this->segments as $seg) {
                if (strpos($seg, $placeholderName) !== false) {
                    return true;
                }
            }
            return false;
        }
        return in_array($segment, $this->segments);
    }

    /**
     * Dosya var mı?
     *
     * @return bool
     */
    public function hasFiles()
    {
        return !empty($this->fileData);
    }

    /**
     * Belirtilen dosya var mı?
     *
     * @param string $key Anahtar
     * @return bool
     */
    public function hasFile($key)
    {
        return isset($this->fileData[$key]) && $this->fileData[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Tüm dosya verilerini döndürür
     *
     * @return array
     */
    public function files()
    {
        return $this->fileData;
    }

    /**
     * Belirtilen dosya verisini döndürür
     *
     * @param string $key Anahtar
     * @return array|null
     */
    public function file($key = null)
    {
        if ($key === null) {
            return $this->fileData;
        }
        return isset($this->fileData[$key]) ? $this->fileData[$key] : null;
    }

    /**
     * Tüm header verilerini döndürür
     *
     * @return array
     */
    public function header()
    {
        return $this->headerData;
    }

    /**
     * Belirtilen header var mı?
     *
     * @param string $key Anahtar
     * @return bool
     */
    public function hasHeader($key)
    {
        return isset($this->headerData[$key]);
    }

    /**
     * Tüm server verilerini döndürür
     *
     * @return array
     */
    public function server()
    {
        return $this->serverData;
    }

    /**
     * Belirtilen server verisi var mı?
     *
     * @param string $key Anahtar
     * @return bool
     */
    public function hasServer($key)
    {
        return isset($this->serverData[$key]);
    }

    /**
     * Tüm input verilerini döndürür (GET, POST, PUT, DELETE, JSON)
     *
     * @param string|null $key Anahtar
     * @param mixed $default Varsayılan değer
     * @return mixed
     */
    public function input($key = null, $default = null)
    {
        $data = array_merge(
            $this->getData,
            $this->postData,
            $this->putData,
            $this->deleteData
        );

        if ($key === null) return $data;
        return $data[$key] ?? $default;
    }

    /**
     * Kullanıcı bilgilerini döndürür
     *
     * @param string|null $key Anahtar
     * @return mixed
     */
    public function user($key = null)
    {
        $user = [
            'ip' => $this->userIp(),
            'agent' => $this->userAgent(),
            'proxy' => $this->userProxy()
        ];

        if ($key === null) {
            return $user;
        }

        return isset($user[$key]) ? $user[$key] : null;
    }

    /**
     * Kullanıcı agent bilgisini döndürür
     *
     * @return string|null
     */
    public function userAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }

    /**
     * Kullanıcı IP adresini döndürür
     *
     * @return string|null
     */
    public function userIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
        }
    }

    /**
     * Kullanıcı proxy bilgilerini döndürür
     *
     * @return array
     */
    public function userProxy()
    {
        $proxy = [];

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $proxy['HTTP_CLIENT_IP'] = $_SERVER['HTTP_CLIENT_IP'];
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $proxy['HTTP_X_FORWARDED_FOR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $proxy['HTTP_X_FORWARDED'] = $_SERVER['HTTP_X_FORWARDED'];
        }

        if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $proxy['HTTP_FORWARDED_FOR'] = $_SERVER['HTTP_FORWARDED_FOR'];
        }

        if (isset($_SERVER['HTTP_FORWARDED'])) {
            $proxy['HTTP_FORWARDED'] = $_SERVER['HTTP_FORWARDED'];
        }

        if (isset($_SERVER['HTTP_VIA'])) {
            $proxy['HTTP_VIA'] = $_SERVER['HTTP_VIA'];
        }

        return $proxy;
    }

    /**
     * İstek gövdesini döndürür
     *
     * @return string
     */
    public function getBody()
    {
        if ($this->body === null) {
            $this->body = file_get_contents('php://input');
        }
        return $this->body;
    }
}
