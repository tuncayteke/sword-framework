<?php

/**
 * Sword Framework
 * 
 *  by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Validation sınıfı - Form verilerini doğrular
 */

class Validation
{
    /**
     * Doğrulama kuralları
     */
    private $rules = [];

    /**
     * Doğrulama hataları
     */
    private $errors = [];

    /**
     * Doğrulama mesajları
     */
    private $messages = [];

    /**
     * Doğrulanacak veriler
     */
    private $data = [];

    /**
     * Varsayılan hata mesajları
     */
    private $defaultMessages = [
        'required' => ':field alanı gereklidir.',
        'email' => ':field geçerli bir e-posta adresi olmalıdır.',
        'min' => ':field en az :param karakter olmalıdır.',
        'max' => ':field en fazla :param karakter olmalıdır.',
        'numeric' => ':field sayısal bir değer olmalıdır.',
        'alpha' => ':field sadece harflerden oluşmalıdır.',
        'alphanumeric' => ':field sadece harf ve rakamlardan oluşmalıdır.',
        'url' => ':field geçerli bir URL olmalıdır.',
        'date' => ':field geçerli bir tarih olmalıdır.',
        'matches' => ':field, :param ile eşleşmelidir.',
        'unique' => ':field zaten kullanılıyor.',
        'in' => ':field geçerli bir değer olmalıdır.',
        'regex' => ':field geçerli bir format olmalıdır.',
        'integer' => ':field tam sayı olmalıdır.',
        'float' => ':field ondalıklı sayı olmalıdır.',
        'boolean' => ':field boolean değer olmalıdır.',
        'ip' => ':field geçerli bir IP adresi olmalıdır.',
        'creditcard' => ':field geçerli bir kredi kartı numarası olmalıdır.'
    ];

    /**
     * Yapılandırıcı
     *
     * @param array $data Doğrulanacak veriler
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * Factory metodu
     *
     * @param array $data Doğrulanacak veriler
     * @return Validation
     */
    public static function make(array $data = []): self
    {
        return new self($data);
    }

    /**
     * Doğrulama kuralı ekler
     *
     * @param string $field Alan adı
     * @param string $label Etiket (hata mesajlarında kullanılır)
     * @param string $rules Kurallar (virgülle ayrılmış)
     * @return Validation
     */
    public function rule($field, $label, $rules)
    {
        $this->rules[$field] = [
            'field' => $field,
            'label' => $label,
            'rules' => $rules
        ];

        return $this;
    }

    /**
     * Hata mesajı ekler
     *
     * @param string $rule Kural adı
     * @param string $message Mesaj
     * @return Validation
     */
    public function setMessage($rule, $message)
    {
        $this->messages[$rule] = $message;

        return $this;
    }

    /**
     * Doğrulama yapar
     *
     * @param array $data Doğrulanacak veriler (opsiyonel)
     * @return bool Doğrulama başarılı mı?
     */
    public function validate($data = null)
    {
        // Veri varsa güncelle
        if ($data !== null) {
            $this->data = $data;
        }

        // Hataları temizle
        $this->errors = [];

        // Kuralları işle
        foreach ($this->rules as $field => $rule) {
            $value = isset($this->data[$field]) ? $this->data[$field] : null;
            $label = $rule['label'] ?: $field;
            $rules = explode('|', $rule['rules']);

            foreach ($rules as $ruleString) {
                // Parametreli kural mı?
                $param = null;
                if (strpos($ruleString, ':') !== false) {
                    list($ruleName, $param) = explode(':', $ruleString, 2);
                } else {
                    $ruleName = $ruleString;
                }

                // Kural metodunu çağır
                $method = 'validate' . ucfirst($ruleName);
                if (method_exists($this, $method)) {
                    $result = $this->$method($field, $value, $param);

                    if ($result === false) {
                        $this->addError($field, $ruleName, $label, $param);
                    }
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Hata ekler
     *
     * @param string $field Alan adı
     * @param string $rule Kural adı
     * @param string $label Etiket
     * @param string $param Parametre
     * @return void
     */
    private function addError($field, $rule, $label, $param = null)
    {
        // Özel mesaj var mı?
        if (isset($this->messages[$field . '.' . $rule])) {
            $message = $this->messages[$field . '.' . $rule];
        } elseif (isset($this->messages[$rule])) {
            $message = $this->messages[$rule];
        } else {
            $message = isset($this->defaultMessages[$rule]) ? $this->defaultMessages[$rule] : ':field geçerli değil.';
        }

        // Yer tutucuları değiştir
        $message = str_replace(':field', $label, $message);
        $message = str_replace(':param', $param, $message);

        // Hatayı ekle
        $this->errors[$field] = $message;
    }

    /**
     * Hataları döndürür
     *
     * @return array Hatalar
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * İlk hatayı döndürür
     *
     * @return string|null İlk hata
     */
    public function getFirstError()
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * Belirli bir alanın hatasını döndürür
     *
     * @param string $field Alan adı
     * @return string|null Hata
     */
    public function getError($field)
    {
        return isset($this->errors[$field]) ? $this->errors[$field] : null;
    }

    /**
     * Doğrulanmış verileri döndürür
     *
     * @return array Doğrulanmış veriler
     */
    public function getValidData()
    {
        $validData = [];

        foreach ($this->rules as $field => $rule) {
            if (!isset($this->errors[$field]) && isset($this->data[$field])) {
                $validData[$field] = $this->data[$field];
            }
        }

        return $validData;
    }

    /**
     * Doğrulama başarısız mı?
     *
     * @return bool Başarısız mı?
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Doğrulama başarılı mı?
     *
     * @return bool Başarılı mı?
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Gereklilik kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @return bool Geçerli mi?
     */
    protected function validateRequired($field, $value)
    {
        if (is_array($value)) {
            return !empty($value);
        }

        return $value !== null && $value !== '';
    }

    /**
     * E-posta kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @return bool Geçerli mi?
     */
    protected function validateEmail($field, $value)
    {
        if (empty($value)) {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Minimum uzunluk kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @param int $param Minimum uzunluk
     * @return bool Geçerli mi?
     */
    protected function validateMin($field, $value, $param)
    {
        if (empty($value)) {
            return true;
        }

        return mb_strlen($value) >= $param;
    }

    /**
     * Maksimum uzunluk kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @param int $param Maksimum uzunluk
     * @return bool Geçerli mi?
     */
    protected function validateMax($field, $value, $param)
    {
        if (empty($value)) {
            return true;
        }

        return mb_strlen($value) <= $param;
    }

    /**
     * Sayısal değer kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @return bool Geçerli mi?
     */
    protected function validateNumeric($field, $value)
    {
        if (empty($value)) {
            return true;
        }

        return is_numeric($value);
    }

    /**
     * Alfabetik değer kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @return bool Geçerli mi?
     */
    protected function validateAlpha($field, $value)
    {
        if (empty($value)) {
            return true;
        }

        return ctype_alpha($value);
    }

    /**
     * Alfanümerik değer kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @return bool Geçerli mi?
     */
    protected function validateAlphanumeric($field, $value)
    {
        if (empty($value)) {
            return true;
        }

        return ctype_alnum($value);
    }

    /**
     * URL kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @return bool Geçerli mi?
     */
    protected function validateUrl($field, $value)
    {
        if (empty($value)) {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Tarih kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @param string $param Tarih formatı
     * @return bool Geçerli mi?
     */
    protected function validateDate($field, $value, $param = 'Y-m-d')
    {
        if (empty($value)) {
            return true;
        }

        $date = DateTime::createFromFormat($param, $value);
        return $date && $date->format($param) === $value;
    }

    /**
     * Eşleşme kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @param string $param Eşleşecek alan
     * @return bool Geçerli mi?
     */
    protected function validateMatches($field, $value, $param)
    {
        return isset($this->data[$param]) && $value === $this->data[$param];
    }

    /**
     * Değer listesi kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @param string $param Virgülle ayrılmış değerler
     * @return bool Geçerli mi?
     */
    protected function validateIn($field, $value, $param)
    {
        if (empty($value)) {
            return true;
        }

        $allowedValues = explode(',', $param);
        return in_array($value, $allowedValues);
    }

    /**
     * Regex kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @param string $param Regex deseni
     * @return bool Geçerli mi?
     */
    protected function validateRegex($field, $value, $param)
    {
        if (empty($value)) {
            return true;
        }

        return preg_match($param, $value) === 1;
    }

    /**
     * Tam sayı kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @return bool Geçerli mi?
     */
    protected function validateInteger($field, $value)
    {
        if (empty($value)) {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Ondalıklı sayı kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @return bool Geçerli mi?
     */
    protected function validateFloat($field, $value)
    {
        if (empty($value)) {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    /**
     * Boolean kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @return bool Geçerli mi?
     */
    protected function validateBoolean($field, $value)
    {
        if (empty($value)) {
            return true;
        }

        $booleanValues = [true, false, 1, 0, '1', '0', 'true', 'false', 'yes', 'no', 'on', 'off'];
        return in_array($value, $booleanValues, true);
    }

    /**
     * IP adresi kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @return bool Geçerli mi?
     */
    protected function validateIp($field, $value)
    {
        if (empty($value)) {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Kredi kartı kuralı
     *
     * @param string $field Alan adı
     * @param mixed $value Değer
     * @return bool Geçerli mi?
     */
    protected function validateCreditcard($field, $value)
    {
        if (empty($value)) {
            return true;
        }

        // Sadece rakamları al
        $value = preg_replace('/[^0-9]/', '', $value);

        // Luhn algoritması
        $sum = 0;
        $length = strlen($value);

        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $value[$length - $i - 1];

            if ($i % 2 == 1) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 == 0;
    }
}
