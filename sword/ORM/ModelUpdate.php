<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * ModelUpdate trait - Model insert ve update metodları
 */

namespace Sword\ORM;

trait ModelUpdate
{
    /**
     * Yeni kayıt ekler
     *
     * @return bool
     */
    protected function insert()
    {
        // Zaman damgalarını ayarla
        if ($this->timestamps) {
            $this->updateTimestamps();
        }

        // Verileri hazırla
        $attributes = $this->getAttributes();

        // beforeInsert olayını çağır
        if (method_exists($this, 'beforeInsert')) {
            $result = $this->beforeInsert($attributes);
            if ($result === false) {
                return false;
            } elseif (is_array($result)) {
                $attributes = $result;
            }
        }

        // Transaction başlat
        $this->connection->beginTransaction();

        try {
            // Sorguyu oluştur
            $query = $this->newQuery();
            $result = $query->insert($attributes);

            if ($result && $this->incrementing) {
                $id = $this->connection->lastInsertId();
                $this->setAttribute($this->primaryKey, $id);
            } else {
                $id = $this->getKey();
            }

            // afterInsert olayını çağır
            if (method_exists($this, 'afterInsert')) {
                $afterResult = $this->afterInsert($id, $attributes);
                if ($afterResult === false) {
                    $this->connection->rollBack();
                    return false;
                }
            }

            // Transaction'ı onayla
            $this->connection->commit();

            // Değişiklikleri senkronize et
            $this->syncOriginal();

            return $result;
        } catch (\Exception $e) {
            // Hata durumunda geri al
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Kaydı günceller
     *
     * @return bool
     */
    protected function update()
    {
        // Zaman damgalarını ayarla
        if ($this->timestamps) {
            $this->updateTimestamps();
        }

        // Değişen öznitelikleri al
        $dirty = $this->getDirty();

        if (empty($dirty)) {
            return true;
        }

        // ID'yi al
        $id = $this->getKey();

        // beforeUpdate olayını çağır
        if (method_exists($this, 'beforeUpdate')) {
            $result = $this->beforeUpdate($id, $dirty);
            if ($result === false) {
                return false;
            } elseif (is_array($result)) {
                $dirty = $result;
            }
        }

        // Transaction başlat
        $this->connection->beginTransaction();

        try {
            // Sorguyu oluştur
            $query = $this->newQuery()->where($this->primaryKey, $id);
            $result = $query->update($dirty);

            // afterUpdate olayını çağır
            if (method_exists($this, 'afterUpdate')) {
                $afterResult = $this->afterUpdate($id, $dirty);
                if ($afterResult === false) {
                    $this->connection->rollBack();
                    return false;
                }
            }

            // Transaction'ı onayla
            $this->connection->commit();

            // Değişiklikleri senkronize et
            $this->syncOriginal();

            return $result;
        } catch (\Exception $e) {
            // Hata durumunda geri al
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Kaydı siler
     *
     * @return bool
     */
    public function delete()
    {
        if (!$this->exists()) {
            return false;
        }

        // ID'yi al
        $id = $this->getKey();

        // beforeDelete olayını çağır
        if (method_exists($this, 'beforeDelete')) {
            $result = $this->beforeDelete($id);
            if ($result === false) {
                return false;
            }
        }

        // Transaction başlat
        $this->connection->beginTransaction();

        try {
            // Sorguyu oluştur
            $query = $this->newQuery()->where($this->primaryKey, $id);
            $result = $query->delete();

            // afterDelete olayını çağır
            if (method_exists($this, 'afterDelete')) {
                $afterResult = $this->afterDelete($id);
                if ($afterResult === false) {
                    $this->connection->rollBack();
                    return false;
                }
            }

            // Transaction'ı onayla
            $this->connection->commit();

            if ($result) {
                $this->attributes = [];
                $this->original = [];
            }

            return $result;
        } catch (\Exception $e) {
            // Hata durumunda geri al
            $this->connection->rollBack();
            throw $e;
        }
    }
}
