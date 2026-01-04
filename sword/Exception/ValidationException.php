<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * ValidationException - Doğrulama hataları
 */

class ValidationException extends SwordException
{
    protected $errors = [];

    public function __construct($message = "Validation failed", array $errors = [], $code = 422)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, null, ['errors' => $errors]);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
