<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * HasMany ilişki sınıfı
 */

namespace Sword\ORM\Relations;

use Sword\ORM\Model;
use Sword\ORM\Query\Builder;

class HasMany extends Relation
{
    /**
     * Yapılandırıcı
     *
     * @param Builder $query Sorgu oluşturucu
     * @param Model $parent Ana model
     * @param string $foreignKey Yabancı anahtar
     * @param string $localKey Yerel anahtar
     */
    public function __construct(Builder $query, Model $parent, $foreignKey, $localKey)
    {
        parent::__construct($query, $parent, $foreignKey, $localKey);
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
        $localKey = $this->parent->getAttribute($this->localKey);

        return $this->query->where($this->foreignKey, $localKey);
    }
}
