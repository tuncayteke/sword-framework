<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * ModelEvents trait - Model olay işleyicileri
 */

namespace Sword\ORM;

trait ModelEvents
{
    /**
     * Kayıt eklemeden önce çalışır
     *
     * @param array $attributes Eklenecek veriler
     * @return array|bool Veriler veya başarısızsa false
     */
    protected function beforeInsert($attributes)
    {
        return $attributes;
    }

    /**
     * Kayıt ekledikten sonra çalışır
     *
     * @param mixed $id Eklenen kaydın ID'si
     * @param array $attributes Eklenen veriler
     * @return bool Başarılı mı?
     */
    protected function afterInsert($id, $attributes)
    {
        return true;
    }

    /**
     * Kayıt güncellemeden önce çalışır
     *
     * @param mixed $id Güncellenecek kaydın ID'si
     * @param array $attributes Güncellenecek veriler
     * @return array|bool Veriler veya başarısızsa false
     */
    protected function beforeUpdate($id, $attributes)
    {
        return $attributes;
    }

    /**
     * Kayıt güncelledikten sonra çalışır
     *
     * @param mixed $id Güncellenen kaydın ID'si
     * @param array $attributes Güncellenen veriler
     * @return bool Başarılı mı?
     */
    protected function afterUpdate($id, $attributes)
    {
        return true;
    }

    /**
     * Kayıt silmeden önce çalışır
     *
     * @param mixed $id Silinecek kaydın ID'si
     * @return bool Başarılı mı?
     */
    protected function beforeDelete($id)
    {
        return true;
    }

    /**
     * Kayıt sildikten sonra çalışır
     *
     * @param mixed $id Silinen kaydın ID'si
     * @return bool Başarılı mı?
     */
    protected function afterDelete($id)
    {
        return true;
    }
}
