<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * JoinClause sınıfı - JOIN ifadeleri için yardımcı sınıf
 */

namespace Sword\ORM\Query;

class JoinClause
{
    /**
     * Sorgu oluşturucu
     */
    protected $query;

    /**
     * Tablo adı
     */
    protected $table;

    /**
     * Join tipi
     */
    protected $type;

    /**
     * ON koşulları
     */
    protected $clauses = [];

    /**
     * Yapılandırıcı
     *
     * @param Builder $query Sorgu oluşturucu
     * @param string $type Join tipi
     * @param string $table Tablo adı
     */
    public function __construct(Builder $query, $type, $table)
    {
        $this->query = $query;
        $this->type = $type;
        $this->table = $table;
    }

    /**
     * ON koşulu ekler
     *
     * @param string $first Birinci sütun
     * @param string $operator Operatör
     * @param string $second İkinci sütun
     * @param string $boolean Mantıksal operatör
     * @return $this
     */
    public function on($first, $operator = null, $second = null, $boolean = 'AND')
    {
        // İkinci sütun verilmemişse
        if ($second === null) {
            $second = $operator;
            $operator = '=';
        }

        $this->clauses[] = [
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
            'boolean' => $boolean
        ];

        return $this;
    }

    /**
     * OR ON koşulu ekler
     *
     * @param string $first Birinci sütun
     * @param string $operator Operatör
     * @param string $second İkinci sütun
     * @return $this
     */
    public function orOn($first, $operator = null, $second = null)
    {
        return $this->on($first, $operator, $second, 'OR');
    }

    /**
     * JOIN ifadesini SQL'e dönüştürür
     *
     * @return string
     */
    public function toSql()
    {
        $sql = $this->type . ' JOIN ' . $this->table . ' ON ';

        $clauses = [];

        foreach ($this->clauses as $i => $clause) {
            $boolean = $i === 0 ? '' : $clause['boolean'] . ' ';
            $clauses[] = $boolean . $clause['first'] . ' ' . $clause['operator'] . ' ' . $clause['second'];
        }

        return $sql . implode(' ', $clauses);
    }
}
