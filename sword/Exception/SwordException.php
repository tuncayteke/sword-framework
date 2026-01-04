<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * SwordException - Framework ana exception sınıfı
 */

class SwordException extends Exception
{
    protected $context = [];

    public function __construct($message = "", $code = 0, Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;

        // Otomatik loglama
        $this->logException();
    }

    public function getContext()
    {
        return $this->context;
    }

    private function logException()
    {
        if (class_exists('Logger')) {
            Logger::error($this->getMessage(), [
                'exception' => get_class($this),
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'context' => $this->context
            ]);
        }
    }
}
