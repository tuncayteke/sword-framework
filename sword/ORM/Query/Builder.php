<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * Query Builder sınıfı - SQL sorguları oluşturur
 */

namespace Sword\ORM\Query;

use Sword\ORM\Model;

class Builder
{
    /**
     * Model örneği
     */
    protected $model;

    /**
     * Veritabanı bağlantısı
     */
    protected $connection;

    /**
     * Tablo adı
     */
    protected $table;

    /**
     * Seçilecek sütunlar
     */
    protected $columns = ['*'];

    /**
     * WHERE koşulları
     */
    protected $wheres = [];

    /**
     * ORDER BY koşulları
     */
    protected $orders = [];

    /**
     * GROUP BY koşulları
     */
    protected $groups = [];

    /**
     * HAVING koşulları
     */
    protected $havings = [];

    /**
     * JOIN koşulları
     */
    protected $joins = [];

    /**
     * LIMIT değeri
     */
    protected $limit;

    /**
     * OFFSET değeri
     */
    protected $offset;

    /**
     * UNION sorguları
     */
    protected $unions = [];

    /**
     * Yapılandırıcı
     *
     * @param Model $model Model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->connection = $model->getConnection();
        $this->table = $model->getTable();
    }

    /**
     * Seçilecek sütunları ayarlar
     *
     * @param array|string $columns Sütunlar
     * @return $this
     */
    public function select($columns = ['*'])
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();

        return $this;
    }

    /**
     * WHERE koşulu ekler
     *
     * @param string $column Sütun
     * @param mixed $operator Operatör veya değer
     * @param mixed $value Değer
     * @param string $boolean Mantıksal operatör
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'AND')
    {
        // Değer verilmemişse, operatörü değer olarak kullan
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];

        return $this;
    }

    /**
     * OR WHERE koşulu ekler
     *
     * @param string $column Sütun
     * @param mixed $operator Operatör veya değer
     * @param mixed $value Değer
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * WHERE IN koşulu ekler
     *
     * @param string $column Sütun
     * @param array $values Değerler
     * @param string $boolean Mantıksal operatör
     * @return $this
     */
    public function whereIn($column, array $values, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];

        return $this;
    }

    /**
     * OR WHERE IN koşulu ekler
     *
     * @param string $column Sütun
     * @param array $values Değerler
     * @return $this
     */
    public function orWhereIn($column, array $values)
    {
        return $this->whereIn($column, $values, 'OR');
    }

    /**
     * WHERE NOT IN koşulu ekler
     *
     * @param string $column Sütun
     * @param array $values Değerler
     * @param string $boolean Mantıksal operatör
     * @return $this
     */
    public function whereNotIn($column, array $values, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type' => 'notIn',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];

        return $this;
    }

    /**
     * OR WHERE NOT IN koşulu ekler
     *
     * @param string $column Sütun
     * @param array $values Değerler
     * @return $this
     */
    public function orWhereNotIn($column, array $values)
    {
        return $this->whereNotIn($column, $values, 'OR');
    }

    /**
     * WHERE NULL koşulu ekler
     *
     * @param string $column Sütun
     * @param string $boolean Mantıksal operatör
     * @return $this
     */
    public function whereNull($column, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type' => 'null',
            'column' => $column,
            'boolean' => $boolean
        ];

        return $this;
    }

    /**
     * OR WHERE NULL koşulu ekler
     *
     * @param string $column Sütun
     * @return $this
     */
    public function orWhereNull($column)
    {
        return $this->whereNull($column, 'OR');
    }

    /**
     * WHERE NOT NULL koşulu ekler
     *
     * @param string $column Sütun
     * @param string $boolean Mantıksal operatör
     * @return $this
     */
    public function whereNotNull($column, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type' => 'notNull',
            'column' => $column,
            'boolean' => $boolean
        ];

        return $this;
    }

    /**
     * OR WHERE NOT NULL koşulu ekler
     *
     * @param string $column Sütun
     * @return $this
     */
    public function orWhereNotNull($column)
    {
        return $this->whereNotNull($column, 'OR');
    }

    /**
     * WHERE BETWEEN koşulu ekler
     *
     * @param string $column Sütun
     * @param array $values Değerler
     * @param string $boolean Mantıksal operatör
     * @return $this
     */
    public function whereBetween($column, array $values, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type' => 'between',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];

        return $this;
    }

    /**
     * OR WHERE BETWEEN koşulu ekler
     *
     * @param string $column Sütun
     * @param array $values Değerler
     * @return $this
     */
    public function orWhereBetween($column, array $values)
    {
        return $this->whereBetween($column, $values, 'OR');
    }

    /**
     * WHERE NOT BETWEEN koşulu ekler
     *
     * @param string $column Sütun
     * @param array $values Değerler
     * @param string $boolean Mantıksal operatör
     * @return $this
     */
    public function whereNotBetween($column, array $values, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type' => 'notBetween',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];

        return $this;
    }

    /**
     * OR WHERE NOT BETWEEN koşulu ekler
     *
     * @param string $column Sütun
     * @param array $values Değerler
     * @return $this
     */
    public function orWhereNotBetween($column, array $values)
    {
        return $this->whereNotBetween($column, $values, 'OR');
    }

    /**
     * WHERE LIKE koşulu ekler
     *
     * @param string $column Sütun
     * @param string $value Değer
     * @param string $boolean Mantıksal operatör
     * @return $this
     */
    public function whereLike($column, $value, $boolean = 'AND')
    {
        return $this->where($column, 'LIKE', $value, $boolean);
    }

    /**
     * OR WHERE LIKE koşulu ekler
     *
     * @param string $column Sütun
     * @param string $value Değer
     * @return $this
     */
    public function orWhereLike($column, $value)
    {
        return $this->whereLike($column, $value, 'OR');
    }

    /**
     * ORDER BY koşulu ekler
     *
     * @param string $column Sütun
     * @param string $direction Yön
     * @return $this
     */
    public function orderBy($column, $direction = 'ASC')
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC'
        ];

        return $this;
    }

    /**
     * ORDER BY DESC koşulu ekler
     *
     * @param string $column Sütun
     * @return $this
     */
    public function orderByDesc($column)
    {
        return $this->orderBy($column, 'DESC');
    }

    /**
     * GROUP BY koşulu ekler
     *
     * @param string|array $columns Sütunlar
     * @return $this
     */
    public function groupBy($columns)
    {
        $this->groups = array_merge($this->groups, is_array($columns) ? $columns : func_get_args());

        return $this;
    }

    /**
     * HAVING koşulu ekler
     *
     * @param string $column Sütun
     * @param mixed $operator Operatör veya değer
     * @param mixed $value Değer
     * @param string $boolean Mantıksal operatör
     * @return $this
     */
    public function having($column, $operator = null, $value = null, $boolean = 'AND')
    {
        // Değer verilmemişse, operatörü değer olarak kullan
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->havings[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];

        return $this;
    }

    /**
     * OR HAVING koşulu ekler
     *
     * @param string $column Sütun
     * @param mixed $operator Operatör veya değer
     * @param mixed $value Değer
     * @return $this
     */
    public function orHaving($column, $operator = null, $value = null)
    {
        return $this->having($column, $operator, $value, 'OR');
    }

    /**
     * LIMIT koşulu ekler
     *
     * @param int $value Değer
     * @return $this
     */
    public function limit($value)
    {
        $this->limit = max(0, (int) $value);

        return $this;
    }

    /**
     * OFFSET koşulu ekler
     *
     * @param int $value Değer
     * @return $this
     */
    public function offset($value)
    {
        $this->offset = max(0, (int) $value);

        return $this;
    }

    /**
     * LIMIT ve OFFSET koşullarını ekler
     *
     * @param int $page Sayfa
     * @param int $perPage Sayfa başına kayıt
     * @return $this
     */
    public function forPage($page, $perPage = 15)
    {
        return $this->offset(($page - 1) * $perPage)->limit($perPage);
    }

    /**
     * JOIN koşulu ekler
     *
     * @param string $table Tablo
     * @param string $first Birinci sütun
     * @param string $operator Operatör
     * @param string $second İkinci sütun
     * @param string $type Join tipi
     * @return $this
     */
    public function join($table, $first, $operator = null, $second = null, $type = 'INNER')
    {
        // Closure verilmişse
        if ($first instanceof \Closure) {
            $join = new JoinClause($this, $type, $table);
            $first($join);
            $this->joins[] = $join;
            return $this;
        }

        // Operatör verilmemişse
        if ($operator === null) {
            $this->joins[] = [
                'table' => $table,
                'type' => $type,
                'on' => $first
            ];
            return $this;
        }

        // İkinci sütun verilmemişse
        if ($second === null) {
            $second = $operator;
            $operator = '=';
        }

        $this->joins[] = [
            'table' => $table,
            'type' => $type,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];

        return $this;
    }

    /**
     * LEFT JOIN koşulu ekler
     *
     * @param string $table Tablo
     * @param string $first Birinci sütun
     * @param string $operator Operatör
     * @param string $second İkinci sütun
     * @return $this
     */
    public function leftJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * RIGHT JOIN koşulu ekler
     *
     * @param string $table Tablo
     * @param string $first Birinci sütun
     * @param string $operator Operatör
     * @param string $second İkinci sütun
     * @return $this
     */
    public function rightJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    /**
     * UNION koşulu ekler
     *
     * @param Builder $query Sorgu
     * @param bool $all UNION ALL mi?
     * @return $this
     */
    public function union($query, $all = false)
    {
        $this->unions[] = [
            'query' => $query,
            'all' => $all
        ];

        return $this;
    }

    /**
     * UNION ALL koşulu ekler
     *
     * @param Builder $query Sorgu
     * @return $this
     */
    public function unionAll($query)
    {
        return $this->union($query, true);
    }

    /**
     * Sorguyu çalıştırır ve sonuçları döndürür
     *
     * @param array|string $columns Sütunlar
     * @return array
     */
    public function get($columns = ['*'])
    {
        if (!empty($columns) && $columns !== ['*']) {
            $this->columns = is_array($columns) ? $columns : func_get_args();
        }

        $sql = $this->toSql();
        $bindings = $this->getBindings();

        $statement = $this->connection->query($sql, $bindings);
        $results = $statement->fetchAll();

        return $this->hydrate($results);
    }

    /**
     * İlk sonucu döndürür
     *
     * @param array|string $columns Sütunlar
     * @return Model|null
     */
    public function first($columns = ['*'])
    {
        return $this->limit(1)->get($columns)[0] ?? null;
    }

    /**
     * İlk sonucu döndürür veya hata fırlatır
     *
     * @param array|string $columns Sütunlar
     * @return Model
     * @throws \Exception
     */
    public function firstOrFail($columns = ['*'])
    {
        $result = $this->first($columns);

        if (!$result) {
            throw new \Exception("Kayıt bulunamadı: " . get_class($this->model));
        }

        return $result;
    }

    /**
     * Belirtilen ID'ye sahip kaydı döndürür
     *
     * @param mixed $id ID
     * @param array|string $columns Sütunlar
     * @return Model|null
     */
    public function find($id, $columns = ['*'])
    {
        return $this->where($this->model->getKeyName(), $id)->first($columns);
    }

    /**
     * Kayıt sayısını döndürür
     *
     * @param string $column Sütun
     * @return int
     */
    public function count($column = '*')
    {
        $result = $this->aggregate('COUNT', $column);
        return (int) ($result['aggregate'] ?? 0);
    }

    /**
     * Maksimum değeri döndürür
     *
     * @param string $column Sütun
     * @return mixed
     */
    public function max($column)
    {
        $result = $this->aggregate('MAX', $column);
        return $result['aggregate'] ?? null;
    }

    /**
     * Minimum değeri döndürür
     *
     * @param string $column Sütun
     * @return mixed
     */
    public function min($column)
    {
        $result = $this->aggregate('MIN', $column);
        return $result['aggregate'] ?? null;
    }

    /**
     * Toplam değeri döndürür
     *
     * @param string $column Sütun
     * @return mixed
     */
    public function sum($column)
    {
        $result = $this->aggregate('SUM', $column);
        return $result['aggregate'] ?? null;
    }

    /**
     * Ortalama değeri döndürür
     *
     * @param string $column Sütun
     * @return mixed
     */
    public function avg($column)
    {
        $result = $this->aggregate('AVG', $column);
        return $result['aggregate'] ?? null;
    }

    /**
     * Aggregate fonksiyonu çalıştırır
     *
     * @param string $function Fonksiyon
     * @param string $column Sütun
     * @return mixed
     */
    protected function aggregate($function, $column)
    {
        $this->columns = [$function . '(' . $column . ') as aggregate'];

        $sql = $this->toSql();
        $bindings = $this->getBindings();

        $statement = $this->connection->query($sql, $bindings);
        $result = $statement->fetch();

        return $result;
    }

    /**
     * Kayıt ekler
     *
     * @param array $values Değerler
     * @return bool
     */
    public function insert(array $values)
    {
        if (empty($values)) {
            return true;
        }

        $columns = array_keys($values);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";

        return $this->connection->query($sql, array_values($values))->rowCount() > 0;
    }

    /**
     * Kayıt günceller
     *
     * @param array $values Değerler
     * @return bool
     */
    public function update(array $values)
    {
        if (empty($values)) {
            return true;
        }

        $sets = [];
        foreach ($values as $column => $value) {
            $sets[] = "{$column} = ?";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets);
        $bindings = array_values($values);

        if (!empty($this->wheres)) {
            list($whereSql, $whereBindings) = $this->compileWheres();
            $sql .= ' ' . $whereSql;
            $bindings = array_merge($bindings, $whereBindings);
        }

        return $this->connection->query($sql, $bindings)->rowCount() > 0;
    }

    /**
     * Kayıt siler
     *
     * @return bool
     */
    public function delete()
    {
        $sql = "DELETE FROM {$this->table}";
        $bindings = [];

        if (!empty($this->wheres)) {
            list($whereSql, $whereBindings) = $this->compileWheres();
            $sql .= ' ' . $whereSql;
            $bindings = $whereBindings;
        }

        return $this->connection->query($sql, $bindings)->rowCount() > 0;
    }

    /**
     * Sorguyu SQL'e dönüştürür
     *
     * @return string
     */
    public function toSql()
    {
        $sql = $this->compileSelect();

        if (!empty($this->joins)) {
            $sql .= ' ' . $this->compileJoins();
        }

        if (!empty($this->wheres)) {
            list($whereSql, $whereBindings) = $this->compileWheres();
            $sql .= ' ' . $whereSql;
        }

        if (!empty($this->groups)) {
            $sql .= ' ' . $this->compileGroups();
        }

        if (!empty($this->havings)) {
            $sql .= ' ' . $this->compileHavings();
        }

        if (!empty($this->orders)) {
            $sql .= ' ' . $this->compileOrders();
        }

        if ($this->limit !== null) {
            $sql .= ' ' . $this->compileLimit();
        }

        if ($this->offset !== null) {
            $sql .= ' ' . $this->compileOffset();
        }

        if (!empty($this->unions)) {
            $sql = '(' . $sql . ') ' . $this->compileUnions();
        }

        return $sql;
    }

    /**
     * SELECT ifadesini derler
     *
     * @return string
     */
    protected function compileSelect()
    {
        return 'SELECT ' . $this->compileColumns() . ' FROM ' . $this->table;
    }

    /**
     * Sütunları derler
     *
     * @return string
     */
    protected function compileColumns()
    {
        if (empty($this->columns)) {
            return '*';
        }

        return implode(', ', $this->columns);
    }

    /**
     * JOIN ifadelerini derler
     *
     * @return string
     */
    protected function compileJoins()
    {
        $sql = [];

        foreach ($this->joins as $join) {
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
     * @return array [sql, bindings]
     */
    protected function compileWheres()
    {
        if (empty($this->wheres)) {
            return ['', []];
        }

        $sql = [];
        $bindings = [];

        foreach ($this->wheres as $i => $where) {
            $boolean = $i === 0 ? 'WHERE' : $where['boolean'];

            switch ($where['type']) {
                case 'basic':
                    $sql[] = "{$boolean} {$where['column']} {$where['operator']} ?";
                    $bindings[] = $where['value'];
                    break;

                case 'in':
                    $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                    $sql[] = "{$boolean} {$where['column']} IN ({$placeholders})";
                    $bindings = array_merge($bindings, $where['values']);
                    break;

                case 'notIn':
                    $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                    $sql[] = "{$boolean} {$where['column']} NOT IN ({$placeholders})";
                    $bindings = array_merge($bindings, $where['values']);
                    break;

                case 'null':
                    $sql[] = "{$boolean} {$where['column']} IS NULL";
                    break;

                case 'notNull':
                    $sql[] = "{$boolean} {$where['column']} IS NOT NULL";
                    break;

                case 'between':
                    $sql[] = "{$boolean} {$where['column']} BETWEEN ? AND ?";
                    $bindings = array_merge($bindings, $where['values']);
                    break;

                case 'notBetween':
                    $sql[] = "{$boolean} {$where['column']} NOT BETWEEN ? AND ?";
                    $bindings = array_merge($bindings, $where['values']);
                    break;
            }
        }

        return [implode(' ', $sql), $bindings];
    }

    /**
     * GROUP BY ifadelerini derler
     *
     * @return string
     */
    protected function compileGroups()
    {
        return 'GROUP BY ' . implode(', ', $this->groups);
    }

    /**
     * HAVING ifadelerini derler
     *
     * @return string
     */
    protected function compileHavings()
    {
        if (empty($this->havings)) {
            return '';
        }

        $sql = [];

        foreach ($this->havings as $i => $having) {
            $boolean = $i === 0 ? 'HAVING' : $having['boolean'];
            $sql[] = "{$boolean} {$having['column']} {$having['operator']} ?";
        }

        return implode(' ', $sql);
    }

    /**
     * ORDER BY ifadelerini derler
     *
     * @return string
     */
    protected function compileOrders()
    {
        if (empty($this->orders)) {
            return '';
        }

        $sql = [];

        foreach ($this->orders as $order) {
            $sql[] = "{$order['column']} {$order['direction']}";
        }

        return 'ORDER BY ' . implode(', ', $sql);
    }

    /**
     * LIMIT ifadesini derler
     *
     * @return string
     */
    protected function compileLimit()
    {
        return 'LIMIT ' . $this->limit;
    }

    /**
     * OFFSET ifadesini derler
     *
     * @return string
     */
    protected function compileOffset()
    {
        return 'OFFSET ' . $this->offset;
    }

    /**
     * UNION ifadelerini derler
     *
     * @return string
     */
    protected function compileUnions()
    {
        $sql = [];

        foreach ($this->unions as $union) {
            $sql[] = 'UNION ' . ($union['all'] ? 'ALL ' : '') . '(' . $union['query']->toSql() . ')';
        }

        return implode(' ', $sql);
    }

    /**
     * Bağlamaları döndürür
     *
     * @return array
     */
    public function getBindings()
    {
        $bindings = [];

        // WHERE bağlamaları
        if (!empty($this->wheres)) {
            list($whereSql, $whereBindings) = $this->compileWheres();
            $bindings = array_merge($bindings, $whereBindings);
        }

        // HAVING bağlamaları
        foreach ($this->havings as $having) {
            $bindings[] = $having['value'];
        }

        // UNION bağlamaları
        foreach ($this->unions as $union) {
            $bindings = array_merge($bindings, $union['query']->getBindings());
        }

        return $bindings;
    }

    /**
     * Soft delete'li kayıtları da dahil eder
     *
     * @return $this
     */
    public function withTrashed()
    {
        // Soft delete kontrolü kaldır
        $this->wheres = array_filter($this->wheres, function ($where) {
            return !($where['type'] === 'null' && $where['column'] === $this->model::DELETED_AT);
        });

        return $this;
    }

    /**
     * Sadece soft delete'li kayıtları getirir
     *
     * @return $this
     */
    public function onlyTrashed()
    {
        return $this->whereNotNull($this->model::DELETED_AT);
    }

    /**
     * Sonuçları model nesnelerine dönüştürür
     *
     * @param array $results Sonuçlar
     * @return array
     */
    protected function hydrate(array $results)
    {
        $models = [];

        foreach ($results as $result) {
            $models[] = new $this->model($result);
        }

        return $models;
    }
}
