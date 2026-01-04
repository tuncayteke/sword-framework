<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * BelongsTo ilişki sınıfı
 */

namespace Sword\ORM\Relations;

use Sword\ORM\Model;
use Sword\ORM\Query\Builder;

class BelongsTo extends Relation
{
    /**
     * Yapılandırıcı
     *
     * @param Builder $query Sorgu oluşturucu
     * @param Model $child Alt model
     * @param string $foreignKey Yabancı anahtar
     * @param string $ownerKey Sahip anahtar
     */
    public function __construct(Builder $query, Model $child, $foreignKey, $ownerKey)
    {
        parent::__construct($query, $child, $foreignKey, $ownerKey);
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
        $foreignKey = $this->parent->getAttribute($this->foreignKey);

        return $this->query->where($this->localKey, $foreignKey);
    }
}
