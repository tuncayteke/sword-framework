<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 * 
 * ORM Model sınıfı - Eloquent benzeri ORM modeli
 */

namespace Sword\ORM;

use Sword\ORM\Query\Builder;
use Sword\ORM\Relations\HasOne;
use Sword\ORM\Relations\HasMany;
use Sword\ORM\Relations\BelongsTo;
use Sword\ORM\Relations\BelongsToMany;
use Sword\ORM\ModelEvents;
use Sword\ORM\TransactionManager;
use Sword\ORM\ModelUpdate;

class Model
{
    use ModelEvents, TransactionManager, ModelUpdate;

    /**
     * Veritabanı bağlantısı
     */
    protected $connection;

    /**
     * Tablo adı
     */
    protected $table;

    /**
     * Birincil anahtar
     */
    protected $primaryKey = 'id';

    /**
     * Birincil anahtarın otomatik artıp artmadığı
     */
    protected $incrementing = true;

    /**
     * Birincil anahtarın veri tipi
     */
    protected $keyType = 'int';

    /**
     * Zaman damgalarını kullan
     */
    protected $timestamps = true;

    /**
     * Oluşturulma zamanı sütunu
     */
    const CREATED_AT = 'created_at';

    /**
     * Güncellenme zamanı sütunu
     */
    const UPDATED_AT = 'updated_at';

    /**
     * Silinme zamanı sütunu (soft delete için)
     */
    const DELETED_AT = 'deleted_at';

    /**
     * Soft delete kullanılsın mı?
     */
    protected $softDelete = false;

    /**
     * Doldurulabilir alanlar
     */
    protected $fillable = [];

    /**
     * Korunan alanlar
     */
    protected $guarded = ['id'];

    /**
     * Gizli alanlar
     */
    protected $hidden = [];

    /**
     * Görünür alanlar
     */
    protected $visible = [];

    /**
     * Otomatik döküm yapılacak alanlar
     */
    protected $casts = [];

    /**
     * Tarih alanları
     */
    protected $dates = [];

    /**
     * İlişkiler
     */
    protected $relations = [];

    /**
     * Orijinal öznitelikler
     */
    protected $original = [];

    /**
     * Öznitelikler
     */
    protected $attributes = [];

    /**
     * Değişen öznitelikler
     */
    protected $changes = [];

    /**
     * Sorgu oluşturucu
     */
    protected $query;

    /**
     * Son hata
     */
    protected $lastError;

    /**
     * Boot edilmiş modeller
     */
    protected static $booted = [];

    /**
     * Yapılandırıcı
     *
     * @param array $attributes Öznitelikler
     */
    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();
        $this->initializeAttributes($attributes);
        $this->syncOriginal();
    }

    /**
     * Model boot metodu - Laravel tarzı
     *
     * @return void
     */
    protected static function boot()
    {
        // Alt sınıflar tarafından override edilebilir
        // Global scope, events vs. burada tanımlanır
    }

    /**
     * Model başlatılmamışsa başlatır
     *
     * @return void
     */
    protected function bootIfNotBooted()
    {
        $class = static::class;

        if (!isset(static::$booted[$class])) {
            static::boot();
            static::$booted[$class] = true;
        }

        // Tablo adını belirle
        if (empty($this->table)) {
            $this->table = $this->getDefaultTableName();
        }

        // Veritabanı bağlantısını al
        $this->connection = $this->getConnection();

        // Sorgu oluşturucuyu başlat
        $this->query = $this->newQuery();
    }

    /**
     * Varsayılan tablo adını döndürür
     *
     * @return string
     */
    protected function getDefaultTableName()
    {
        $className = get_class($this);
        $parts = explode('\\', $className);
        $className = end($parts);

        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)) . 's';
    }

    /**
     * Veritabanı bağlantısını döndürür
     *
     * @return mixed
     */
    public function getConnection()
    {
        return \Sword::db();
    }

    /**
     * Öznitelikleri başlatır
     *
     * @param array $attributes Öznitelikler
     * @return void
     */
    protected function initializeAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    /**
     * Orijinal öznitelikleri senkronize eder
     *
     * @return $this
     */
    public function syncOriginal()
    {
        $this->original = $this->attributes;

        return $this;
    }

    /**
     * Yeni bir sorgu oluşturucu döndürür
     *
     * @return Builder
     */
    public function newQuery()
    {
        return new Builder($this);
    }

    /**
     * Tüm kayıtları döndürür
     *
     * @param array|string $columns Sütunlar
     * @return array
     */
    public static function all($columns = ['*'])
    {
        return (new static)->newQuery()->get($columns);
    }

    /**
     * Belirtilen ID'ye sahip kaydı döndürür
     *
     * @param mixed $id ID
     * @param array|string $columns Sütunlar
     * @return static|null
     */
    public static function find($id, $columns = ['*'])
    {
        if (is_array($id)) {
            return static::findMany($id, $columns);
        }

        return (new static)->newQuery()->find($id, $columns);
    }

    /**
     * Belirtilen ID'lere sahip kayıtları döndürür
     *
     * @param array $ids ID'ler
     * @param array|string $columns Sütunlar
     * @return array
     */
    public static function findMany($ids, $columns = ['*'])
    {
        if (empty($ids)) {
            return [];
        }

        return (new static)->newQuery()->whereIn((new static)->getKeyName(), $ids)->get($columns);
    }

    /**
     * Belirtilen ID'ye sahip kaydı döndürür veya hata fırlatır
     *
     * @param mixed $id ID
     * @param array|string $columns Sütunlar
     * @return static
     * @throws \Exception
     */
    public static function findOrFail($id, $columns = ['*'])
    {
        $result = static::find($id, $columns);

        if (!$result) {
            throw new \Exception("Model bulunamadı: " . static::class . " #{$id}");
        }

        return $result;
    }

    /**
     * İlk kaydı döndürür
     *
     * @param array|string $columns Sütunlar
     * @return static|null
     */
    public static function first($columns = ['*'])
    {
        return (new static)->newQuery()->first($columns);
    }

    /**
     * İlk kaydı döndürür veya hata fırlatır
     *
     * @param array|string $columns Sütunlar
     * @return static
     * @throws \Exception
     */
    public static function firstOrFail($columns = ['*'])
    {
        $result = static::first($columns);

        if (!$result) {
            throw new \Exception("Model bulunamadı: " . static::class);
        }

        return $result;
    }

    /**
     * Belirtilen koşula göre sorgu oluşturur
     *
     * @param string $column Sütun
     * @param mixed $operator Operatör veya değer
     * @param mixed $value Değer
     * @return Builder
     */
    public static function where($column, $operator = null, $value = null)
    {
        return (new static)->newQuery()->where($column, $operator, $value);
    }

    /**
     * Belirtilen koşula göre ilk kaydı döndürür
     *
     * @param string $column Sütun
     * @param mixed $operator Operatör veya değer
     * @param mixed $value Değer
     * @param array|string $columns Sütunlar
     * @return static|null
     */
    public static function firstWhere($column, $operator = null, $value = null, $columns = ['*'])
    {
        return static::where($column, $operator, $value)->first($columns);
    }

    /**
     * Yeni bir kayıt oluşturur
     *
     * @param array $attributes Öznitelikler
     * @return static
     */
    public static function create(array $attributes)
    {
        $model = new static($attributes);
        $model->save();

        return $model;
    }

    /**
     * Kaydı günceller veya oluşturur
     *
     * @param array $attributes Arama öznitelikleri
     * @param array $values Değerler
     * @return static
     */
    public static function updateOrCreate(array $attributes, array $values = [])
    {
        $instance = static::firstWhere($attributes);

        if ($instance) {
            $instance->fill($values)->save();
            return $instance;
        }

        return static::create(array_merge($attributes, $values));
    }

    /**
     * Öznitelikleri doldurur
     *
     * @param array $attributes Öznitelikler
     * @return $this
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }

        return $this;
    }

    /**
     * Alanın doldurulabilir olup olmadığını kontrol eder
     *
     * @param string $key Alan
     * @return bool
     */
    public function isFillable($key)
    {
        if (in_array($key, $this->guarded) || $this->guarded == ['*']) {
            return false;
        }

        return empty($this->fillable) || in_array($key, $this->fillable);
    }

    /**
     * Kaydı kaydeder
     *
     * @return bool
     */
    public function save()
    {
        if ($this->exists()) {
            return $this->update();
        }

        return $this->insert();
    }

    /**
     * Kaydın var olup olmadığını kontrol eder
     *
     * @return bool
     */
    public function exists()
    {
        return isset($this->attributes[$this->primaryKey]);
    }

    /**
     * Zaman damgalarını günceller
     *
     * @return void
     */
    protected function updateTimestamps()
    {
        $time = date('Y-m-d H:i:s');

        if (!$this->exists()) {
            $this->setAttribute(static::CREATED_AT, $time);
        }

        $this->setAttribute(static::UPDATED_AT, $time);

        // Soft delete için silinme zamanını null olarak ayarla
        if ($this->softDelete && !$this->exists()) {
            $this->setAttribute(static::DELETED_AT, null);
        }
    }

    /**
     * Değişen öznitelikleri döndürür
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Birincil anahtar değerini döndürür
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->getAttribute($this->primaryKey);
    }

    /**
     * Birincil anahtar adını döndürür
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Tablo adını döndürür
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Özniteliği ayarlar
     *
     * @param string $key Anahtar
     * @param mixed $value Değer
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        // Mutator kontrolü
        $method = 'set' . $this->studly($key) . 'Attribute';

        if (method_exists($this, $method)) {
            $value = $this->$method($value);
        } elseif (isset($this->casts[$key])) {
            $value = $this->castToType($value, $this->casts[$key]);
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Özniteliği döndürür
     *
     * @param string $key Anahtar
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (!isset($this->attributes[$key])) {
            // İlişki kontrolü
            if (method_exists($this, $key)) {
                return $this->getRelationValue($key);
            }

            return null;
        }

        $value = $this->attributes[$key];

        // Accessor kontrolü
        $method = 'get' . $this->studly($key) . 'Attribute';

        if (method_exists($this, $method)) {
            return $this->$method($value);
        }

        // Cast kontrolü
        if (isset($this->casts[$key])) {
            return $this->castFromType($value, $this->casts[$key]);
        }

        // Tarih kontrolü
        if (in_array($key, $this->dates)) {
            return $this->asDate($value);
        }

        return $value;
    }

    /**
     * İlişki değerini döndürür
     *
     * @param string $key İlişki adı
     * @return mixed
     */
    protected function getRelationValue($key)
    {
        if (isset($this->relations[$key])) {
            return $this->relations[$key];
        }

        $relation = $this->$key();

        if ($relation instanceof Relation) {
            $this->relations[$key] = $relation->getResults();
            return $this->relations[$key];
        }

        return null;
    }

    /**
     * Tüm öznitelikleri döndürür
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Değeri belirtilen tipe dönüştürür
     *
     * @param mixed $value Değer
     * @param string $type Tip
     * @return mixed
     */
    protected function castToType($value, $type)
    {
        switch ($type) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'array':
                return is_array($value) ? $value : json_decode($value, true);
            case 'json':
                return json_encode($value);
            case 'date':
                return $this->asDate($value)->format('Y-m-d');
            case 'datetime':
                return $this->asDate($value)->format('Y-m-d H:i:s');
            case 'timestamp':
                return strtotime($value);
            default:
                return $value;
        }
    }

    /**
     * Değeri belirtilen tipten dönüştürür
     *
     * @param mixed $value Değer
     * @param string $type Tip
     * @return mixed
     */
    protected function castFromType($value, $type)
    {
        switch ($type) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'array':
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'date':
            case 'datetime':
                return $this->asDate($value);
            case 'timestamp':
                return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
            default:
                return $value;
        }
    }

    /**
     * Değeri tarih olarak döndürür
     *
     * @param mixed $value Değer
     * @return \DateTime
     */
    protected function asDate($value)
    {
        if ($value instanceof \DateTime) {
            return $value;
        }

        if (is_numeric($value)) {
            return new \DateTime('@' . $value);
        }

        return new \DateTime($value);
    }

    /**
     * Metni studly case'e dönüştürür
     *
     * @param string $value Değer
     * @return string
     */
    protected function studly($value)
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        return str_replace(' ', '', $value);
    }

    /**
     * Bir ilişki tanımlar
     *
     * @param string $related İlişkili model
     * @param string $foreignKey Yabancı anahtar
     * @param string $localKey Yerel anahtar
     * @return HasOne
     */
    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $instance = new $related;

        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->getKeyName();

        return new HasOne($instance->newQuery(), $this, $instance->getTable() . '.' . $foreignKey, $localKey);
    }

    /**
     * Bir ilişki tanımlar
     *
     * @param string $related İlişkili model
     * @param string $foreignKey Yabancı anahtar
     * @param string $localKey Yerel anahtar
     * @return HasMany
     */
    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $instance = new $related;

        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->getKeyName();

        return new HasMany($instance->newQuery(), $this, $instance->getTable() . '.' . $foreignKey, $localKey);
    }

    /**
     * Bir ilişki tanımlar
     *
     * @param string $related İlişkili model
     * @param string $foreignKey Yabancı anahtar
     * @param string $ownerKey Sahip anahtar
     * @return BelongsTo
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null)
    {
        $instance = new $related;

        $foreignKey = $foreignKey ?: $instance->getForeignKey();
        $ownerKey = $ownerKey ?: $instance->getKeyName();

        return new BelongsTo($instance->newQuery(), $this, $foreignKey, $ownerKey);
    }

    /**
     * Bir ilişki tanımlar
     *
     * @param string $related İlişkili model
     * @param string $table Ara tablo
     * @param string $foreignPivotKey Yabancı pivot anahtar
     * @param string $relatedPivotKey İlişkili pivot anahtar
     * @param string $parentKey Ebeveyn anahtar
     * @param string $relatedKey İlişkili anahtar
     * @return BelongsToMany
     */
    public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null)
    {
        $instance = new $related;

        $table = $table ?: $this->joiningTable($instance);

        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();
        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        $parentKey = $parentKey ?: $this->getKeyName();
        $relatedKey = $relatedKey ?: $instance->getKeyName();

        return new BelongsToMany($instance->newQuery(), $this, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey);
    }

    /**
     * Ara tablo adını döndürür
     *
     * @param Model $related İlişkili model
     * @return string
     */
    protected function joiningTable($related)
    {
        $models = [
            $this->getTable(),
            $related->getTable()
        ];

        sort($models);

        return implode('_', $models);
    }

    /**
     * Yabancı anahtar adını döndürür
     *
     * @return string
     */
    public function getForeignKey()
    {
        $class = get_class($this);
        $parts = explode('\\', $class);
        $class = end($parts);

        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class)) . '_' . $this->primaryKey;
    }

    /**
     * Son hatayı döndürür
     *
     * @return string|null
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Özniteliğe erişim
     *
     * @param string $key Anahtar
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Özniteliği ayarlama
     *
     * @param string $key Anahtar
     * @param mixed $value Değer
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Özniteliğin var olup olmadığını kontrol eder
     *
     * @param string $key Anahtar
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]) ||
            isset($this->relations[$key]) ||
            method_exists($this, $key);
    }

    /**
     * Özniteliği kaldırır
     *
     * @param string $key Anahtar
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key], $this->relations[$key]);
    }

    /**
     * Modeli diziye dönüştürür
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = $this->attributesToArray();
        $relations = $this->relationsToArray();

        return array_merge($attributes, $relations);
    }

    /**
     * Öznitelikleri diziye dönüştürür
     *
     * @return array
     */
    protected function attributesToArray()
    {
        $attributes = $this->attributes;

        // Gizli alanları kaldır
        foreach ($this->hidden as $key) {
            unset($attributes[$key]);
        }

        // Sadece görünür alanları dahil et
        if (!empty($this->visible)) {
            $attributes = array_intersect_key($attributes, array_flip($this->visible));
        }

        // Cast işlemlerini uygula
        foreach ($attributes as $key => $value) {
            if (isset($this->casts[$key])) {
                $attributes[$key] = $this->castFromType($value, $this->casts[$key]);
            }

            if (in_array($key, $this->dates) && !is_null($value)) {
                $attributes[$key] = $this->asDate($value)->format('Y-m-d H:i:s');
            }
        }

        return $attributes;
    }

    /**
     * İlişkileri diziye dönüştürür
     *
     * @return array
     */
    protected function relationsToArray()
    {
        $attributes = [];

        foreach ($this->relations as $key => $value) {
            if (in_array($key, $this->hidden)) {
                continue;
            }

            if (!empty($this->visible) && !in_array($key, $this->visible)) {
                continue;
            }

            if ($value instanceof Model) {
                $attributes[$key] = $value->toArray();
            } elseif (is_array($value)) {
                $attributes[$key] = [];

                foreach ($value as $item) {
                    if ($item instanceof Model) {
                        $attributes[$key][] = $item->toArray();
                    } else {
                        $attributes[$key][] = $item;
                    }
                }
            }
        }

        return $attributes;
    }

    /**
     * Modeli JSON'a dönüştürür
     *
     * @param int $options JSON seçenekleri
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Modeli diziye dönüştürür
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Modeli string'e dönüştürür
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
