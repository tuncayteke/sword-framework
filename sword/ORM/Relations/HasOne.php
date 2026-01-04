<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * HasOne ilişki sınıfı
 */

namespace Sword\ORM\Relations;

use Sword\ORM\Model;
use Sword\ORM\Query\Builder;

class HasOne extends Relation
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
     * @return Model|null
     */
    public function getResults()
    {
        return $this->getRelationQuery()->first();
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
