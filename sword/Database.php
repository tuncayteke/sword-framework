<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * QueryBuilder sınıfı - ORM QueryBuilder'a yönlendirme yapar
 */

class QueryBuilder extends \Sword\ORM\Query\Builder
{
    /**
     * Yapılandırıcı
     */
    public function __construct($connection, $table)
    {
        parent::__construct(new \Sword\ORM\Model());
        $this->from($table);
    }

    // Eski QueryBuilder sınıfı ile uyumluluk için ek metodlar
    // ...
}
