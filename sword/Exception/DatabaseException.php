<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * DatabaseException - Veritabanı hataları
 */

class DatabaseException extends SwordException
{
    public function __construct($message = "Database error", $code = 500, Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous, $context);
    }
}
