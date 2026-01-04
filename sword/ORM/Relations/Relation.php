<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Relation sınıfı - Model ilişkilerinin temel sınıfı
 */

namespace Sword\ORM\Relations;

use Sword\ORM\Model;
use Sword\ORM\Query\Builder;

abstract class Relation
{
    /**
     * Sorgu oluşturucu
     */
    protected $query;

    /**
     * Ana model
     */
    protected $parent;

    /**
     * İlişkili model
     */
    protected $related;

    /**
     * Yabancı anahtar
     */
    protected $foreignKey;

    /**
     * Yerel anahtar
     */
    protected $localKey;

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
        $this->query = $query;
        $this->parent = $parent;
        $this->related = $query->getModel();
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    /**
     * İlişkiyi sorgu olarak döndürür
     *
     * @return Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Ana modeli döndürür
     *
     * @return Model
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * İlişkili modeli döndürür
     *
     * @return Model
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * Yabancı anahtarı döndürür
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * Yerel anahtarı döndürür
     *
     * @return string
     */
    public function getLocalKey()
    {
        return $this->localKey;
    }

    /**
     * İlişki sonuçlarını döndürür
     *
     * @return mixed
     */
    abstract public function getResults();

    /**
     * İlişkiyi sorgu olarak döndürür
     *
     * @return Builder
     */
    abstract public function getRelationQuery();
}
