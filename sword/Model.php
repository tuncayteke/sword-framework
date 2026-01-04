<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Model sınıfı - Temel model sınıfı (ORM/Model'i genişletir)
 */

class Model extends \Sword\ORM\Model
{
    /**
     * Yapılandırıcı
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Belirli bir koşula göre tek bir kaydı döndürür (ORM uyumluluğu için)
     *
     * @param string $where Where koşulu
     * @param array $params Parametreler
     * @return array|null Kayıt
     */
    public function findWhere($where, $params = [])
    {
        return $this->where($where, $params)->first();
    }

    /**
     * Belirli bir koşula göre tüm kayıtları döndürür (ORM uyumluluğu için)
     *
     * @param string $where Where koşulu
     * @param array $params Parametreler
     * @param string|null $order Sıralama
     * @param int|null $limit Limit
     * @param int|null $offset Offset
     * @return array Kayıtlar
     */
    public function findAllWhere($where, $params = [], $order = null, $limit = null, $offset = null)
    {
        $query = $this->where($where, $params);

        if ($order !== null) {
            $query->orderBy($order);
        }

        if ($limit !== null) {
            $query->limit($limit);

            if ($offset !== null) {
                $query->offset($offset);
            }
        }

        return $query->get();
    }

    /**
     * Kullanıcıları sorgu oluşturucu ile arar
     *
     * @param string $search Arama terimi
     * @return array Kullanıcılar
     */
    public function search($search)
    {
        return $this->newQuery() // query() yerine newQuery() kullanılabilir
            ->where('username', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->orWhere('full_name', 'LIKE', "%{$search}%")
            ->orderBy('username')
            ->get();
    }
}
