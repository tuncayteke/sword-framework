<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Grammar sınıfı - SQL dilbilgisi
 */

namespace Sword\ORM\Query;

class Grammar
{
    /**
     * SELECT ifadesini derler
     *
     * @param Builder $query Sorgu oluşturucu
     * @return string
     */
    public function compileSelect(Builder $query)
    {
        $sql = $this->compileComponents($query);

        return $sql;
    }

    /**
     * Sorgu bileşenlerini derler
     *
     * @param Builder $query Sorgu oluşturucu
     * @return string
     */
    protected function compileComponents(Builder $query)
    {
        $sql = [];

        $sql[] = $this->compileSelectStatement($query);

        if (!empty($query->joins)) {
            $sql[] = $this->compileJoins($query);
        }

        if (!empty($query->wheres)) {
            $sql[] = $this->compileWheres($query);
        }

        if (!empty($query->groups)) {
            $sql[] = $this->compileGroups($query);
        }

        if (!empty($query->havings)) {
            $sql[] = $this->compileHavings($query);
        }

        if (!empty($query->orders)) {
            $sql[] = $this->compileOrders($query);
        }

        if ($query->limit !== null) {
            $sql[] = $this->compileLimit($query);
        }

        if ($query->offset !== null) {
            $sql[] = $this->compileOffset($query);
        }

        return implode(' ', $sql);
    }

    /**
     * SELECT ifadesini derler
     *
     * @param Builder $query Sorgu oluşturucu
     * @return string
     */
    protected function compileSelectStatement(Builder $query)
    {
        return 'SELECT ' . $this->compileColumns($query) . ' FROM ' . $query->from;
    }

    /**
     * Sütunları derler
     *
     * @param Builder $query Sorgu oluşturucu
     * @return string
     */
    protected function compileColumns(Builder $query)
    {
        if (empty($query->columns)) {
            return '*';
        }

        return implode(', ', $query->columns);
    }

    /**
     * JOIN ifadelerini derler
     *
     * @param Builder $query Sorgu oluşturucu
     * @return string
     */
    protected function compileJoins(Builder $query)
    {
        $sql = [];

        foreach ($query->joins as $join) {
            if ($join instanceof JoinClause) {
                $sql[] = $join->toSql();
            } else {
                $type = $join['type'] ?? 'INNER';
                $table = $join['table'];

                if (isset($join['on'])) {
                    $sql[] = "{$type} JOIN {$table} ON {$join['on']}";
                } else {
                    $first = $join['first'];
                    $operator = $join['operator'];
                    $second = $join['second'];
                    $sql[] = "{$type} JOIN {$table} ON {$first} {$operator} {$second}";
                }
            }
        }

        return implode(' ', $sql);
    }

    /**
     * WHERE ifadelerini derler
     *
     * @param Builder $query Sorgu oluşturucu
     * @return string
     */
    protected function compileWheres(Builder $query)
    {
        if (empty($query->wheres)) {
            return '';
        }

        $sql = [];

        foreach ($query->wheres as $i => $where) {
            $boolean = $i === 0 ? 'WHERE' : $where['boolean'];

            switch ($where['type']) {
                case 'basic':
                    $sql[] = "{$boolean} {$where['column']} {$where['operator']} ?";
                    break;

                case 'in':
                    $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                    $sql[] = "{$boolean} {$where['column']} IN ({$placeholders})";
                    break;

                case 'notIn':
                    $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                    $sql[] = "{$boolean} {$where['column']} NOT IN ({$placeholders})";
                    break;

                case 'null':
                    $sql[] = "{$boolean} {$where['column']} IS NULL";
                    break;

                case 'notNull':
                    $sql[] = "{$boolean} {$where['column']} IS NOT NULL";
                    break;

                case 'between':
                    $sql[] = "{$boolean} {$where['column']} BETWEEN ? AND ?";
                    break;

                case 'notBetween':
                    $sql[] = "{$boolean} {$where['column']} NOT BETWEEN ? AND ?";
                    break;
            }
        }

        return implode(' ', $sql);
    }

    /**
     * GROUP BY ifadelerini derler
     *
     * @param Builder $query Sorgu oluşturucu
     * @return string
     */
    protected function compileGroups(Builder $query)
    {
        return 'GROUP BY ' . implode(', ', $query->groups);
    }

    /**
     * HAVING ifadelerini derler
     *
     * @param Builder $query Sorgu oluşturucu
     * @return string
     */
    protected function compileHavings(Builder $query)
    {
        if (empty($query->havings)) {
            return '';
        }

        $sql = [];

        foreach ($query->havings as $i => $having) {
            $boolean = $i === 0 ? 'HAVING' : $having['boolean'];
            $sql[] = "{$boolean} {$having['column']} {$having['operator']} ?";
        }

        return implode(' ', $sql);
    }

    /**
     * ORDER BY ifadelerini derler
     *
     * @param Builder $query Sorgu oluşturucu
     * @return string
     */
    protected function compileOrders(Builder $query)
    {
        if (empty($query->orders)) {
            return '';
        }

        $sql = [];

        foreach ($query->orders as $order) {
            $sql[] = "{$order['column']} {$order['direction']}";
        }

        return 'ORDER BY ' . implode(', ', $sql);
    }

    /**
     * LIMIT ifadesini derler
     *
     * @param Builder $query Sorgu oluşturucu
     * @return string
     */
    protected function compileLimit(Builder $query)
    {
        return 'LIMIT ' . $query->limit;
    }

    /**
     * OFFSET ifadesini derler
     *
     * @param Builder $query Sorgu oluşturucu
     * @return string
     */
    protected function compileOffset(Builder $query)
    {
        return 'OFFSET ' . $query->offset;
    }

    /**
     * INSERT ifadesini derler
     *
     * @param Builder $query Sorgu oluşturucu
     * @param array $values Değerler
     * @return string
     */
    public function compileInsert(Builder $query, array $values)
    {
        $table = $query->from;

        $columns = array_keys($values);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        return "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";
    }

    /**
     * UPDATE ifadesini derler
     *
     * @param Builder $query Sorgu oluşturucu
     * @param array $values Değerler
     * @return string
     */
    public function compileUpdate(Builder $query, array $values)
    {
        $table = $query->from;

        $sets = [];
        foreach ($values as $column => $value) {
            $sets[] = "{$column} = ?";
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $sets);

        if (!empty($query->wheres)) {
            $sql .= ' ' . $this->compileWheres($query);
        }

        return $sql;
    }

    /**
     * DELETE ifadesini derler
     *
     * @param Builder $query Sorgu oluşturucu
     * @return string
     */
    public function compileDelete(Builder $query)
    {
        $table = $query->from;

        $sql = "DELETE FROM {$table}";

        if (!empty($query->wheres)) {
            $sql .= ' ' . $this->compileWheres($query);
        }

        return $sql;
    }
}
