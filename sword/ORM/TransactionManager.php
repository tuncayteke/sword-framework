<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * TransactionManager trait - Transaction yönetimi
 */

namespace Sword\ORM;

trait TransactionManager
{
    /**
     * Transaction başlatır
     *
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Transaction'ı onaylar
     *
     * @return bool
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Transaction'ı geri alır
     *
     * @return bool
     */
    public function rollBack()
    {
        return $this->connection->rollBack();
    }

    /**
     * Transaction içinde olup olmadığını kontrol eder
     *
     * @return bool
     */
    public function inTransaction()
    {
        return $this->connection->inTransaction();
    }

    /**
     * Transaction içinde işlem yapar
     *
     * @param callable $callback İşlem
     * @return mixed
     * @throws \Exception
     */
    public function transaction(callable $callback)
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }
}
