<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * BelongsToMany ilişki sınıfı
 */

namespace Sword\ORM\Relations;

use Sword\ORM\Model;
use Sword\ORM\Query\Builder;

class BelongsToMany extends Relation
{
    /**
     * Ara tablo
     */
    protected $table;

    /**
     * Yabancı pivot anahtar
     */
    protected $foreignPivotKey;

    /**
     * İlişkili pivot anahtar
     */
    protected $relatedPivotKey;

    /**
     * Yapılandırıcı
     *
     * @param Builder $query Sorgu oluşturucu
     * @param Model $parent Ana model
     * @param string $table Ara tablo
     * @param string $foreignPivotKey Yabancı pivot anahtar
     * @param string $relatedPivotKey İlişkili pivot anahtar
     * @param string $parentKey Ebeveyn anahtar
     * @param string $relatedKey İlişkili anahtar
     */
    public function __construct(Builder $query, Model $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey)
    {
        $this->table = $table;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;

        parent::__construct($query, $parent, $foreignPivotKey, $parentKey);

        $this->localKey = $relatedKey;
    }

    /**
     * İlişki sonuçlarını döndürür
     *
     * @return array
     */
    public function getResults()
    {
        return $this->getRelationQuery()->get();
    }

    /**
     * İlişkiyi sorgu olarak döndürür
     *
     * @return Builder
     */
    public function getRelationQuery()
    {
        $parentKey = $this->parent->getAttribute($this->getParentKeyName());

        return $this->query
            ->join($this->table, $this->getQualifiedRelatedKeyName(), '=', $this->getQualifiedRelatedPivotKeyName())
            ->where($this->getQualifiedForeignPivotKeyName(), $parentKey);
    }

    /**
     * Ara tabloyu döndürür
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Yabancı pivot anahtarını döndürür
     *
     * @return string
     */
    public function getForeignPivotKey()
    {
        return $this->foreignPivotKey;
    }

    /**
     * İlişkili pivot anahtarını döndürür
     *
     * @return string
     */
    public function getRelatedPivotKey()
    {
        return $this->relatedPivotKey;
    }

    /**
     * Ebeveyn anahtar adını döndürür
     *
     * @return string
     */
    public function getParentKeyName()
    {
        return $this->localKey;
    }

    /**
     * İlişkili anahtar adını döndürür
     *
     * @return string
     */
    public function getRelatedKeyName()
    {
        return $this->related->getKeyName();
    }

    /**
     * Nitelikli yabancı pivot anahtar adını döndürür
     *
     * @return string
     */
    public function getQualifiedForeignPivotKeyName()
    {
        return $this->table . '.' . $this->foreignPivotKey;
    }

    /**
     * Nitelikli ilişkili pivot anahtar adını döndürür
     *
     * @return string
     */
    public function getQualifiedRelatedPivotKeyName()
    {
        return $this->table . '.' . $this->relatedPivotKey;
    }

    /**
     * Nitelikli ilişkili anahtar adını döndürür
     *
     * @return string
     */
    public function getQualifiedRelatedKeyName()
    {
        return $this->related->getTable() . '.' . $this->getRelatedKeyName();
    }
}
