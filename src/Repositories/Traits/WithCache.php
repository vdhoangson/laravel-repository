<?php

namespace Vdhoangson\LaravelRepository\Repositories\Traits;

use ReflectionObject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Container\BindingResolutionException;
use Vdhoangson\LaravelRepository\Repositories\Interfaces\BaseInterface;
use Vdhoangson\LaravelRepository\Repositories\Exceptions\RepositoryEntityException;

/**
 * Trait WithCache.
 *
 */
trait WithCache
{
    /**
     * Determine if cache will be skipped in query.
     *
     * @var bool
     */
    protected $skipCache = false;

    /**
     * Determine if use auth user tag.
     *
     * @var bool
     */
    protected $useUserTag = false;

    /**
     * User ID for cache key.
     *
     * @var int|null
     */
    protected $userTag = null;

    protected $cacheKey = null;

    /**
     * Skip cache.
     *
     * @return BaseInterface
     */
    public function skipCache(): BaseInterface
    {
        $this->skipCache = true;

        return $this;
    }

    /**
     * Use auth user tag.
     *
     * @return BaseInterface
     */
    public function useUserTag(): BaseInterface
    {
        $this->useUserTag = true;

        return $this;
    }

    /**
     * Manually set user tag.
     *
     * @param int|string $tag
     *
     * @return BaseInterface
     */
    public function setUserTag(int|string $tag): BaseInterface
    {
        $this->userTag = $tag;

        return $this;
    }

    /**
     * Clear manually set user tag.
     *
     * @return BaseInterface
     */
    public function clearUserTag(): BaseInterface
    {
        $this->userTag = null;

        return $this;
    }

    /**
     * Clear cache.
     *
     * If cache key is provided, clear only that cache.
     * If not, clear all cache for this repository.
     *
     * @param string|null $cacheKey
     * @return $this|BaseInterface
     */
    public function clearCache($cacheKey = null): BaseInterface
    {
        if ($cacheKey) {
            Cache::tags([$this->getTag()])->forget($cacheKey);
            return $this;
        }

        Cache::tags([$this->getTag()])->flush();

        return $this;
    }

    /**
     * Get cache key.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return string
     */
    protected function getCacheKey(string $method, array $parameters): string
    {
        return $this->cacheKey ?? $this->generateCacheKey($method, $parameters);
    }

    /**
     * Set cache key.
     *
     * @param string $cacheKey
     *
     * @return BaseInterface
     */
    protected function setCacheKey(string $cacheKey): BaseInterface
    {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    /**
     * Generate cache key.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return string
     */
    protected function generateCacheKey(string $method, array $parameters): string
    {
        // Get actual repository class name.
        $className = get_class($this);

        // Get serialized criteria.
        $criteria = $this->getSerializedCriteria();
        $query = $this->getSerializedQuery();

        $this->cacheKey = sprintf(
            '%s@%s_%s-%s',
            $method,
            $className,
            $this->getTag(),
            md5(serialize($parameters) . $criteria . $query)
        );

        return $this->cacheKey;
    }

    /**
     * Serialize criteria pushed into repository.
     *
     * @return string
     */
    protected function getSerializedCriteria(): string
    {
        return serialize(
            $this->getCriteria()->map(
                function ($criteria) {
                    $reflectionClass = new ReflectionObject($criteria);

                    $parameters = [];
                    if ($reflectionClass->getConstructor() !== null) {
                        $parameters = $reflectionClass->getConstructor()->getParameters();
                    }

                    try {
                        $hash = serialize($criteria);
                    } catch (\Exception $exception) {
                        $hash = md5((string) $criteria);
                    }

                    return [
                        'hash' => $hash,
                        'criteria' => $reflectionClass->getName(),
                        'properties' => $reflectionClass->getProperties(),
                        'parameters' => $parameters,
                    ];
                }
            )
        );
    }

    protected function getSerializedQuery(): string
    {
        return serialize($this->getQuery()->toRawSql());
    }

    /**
     * Return eloquent collection of all records of entity
     * Criteria are not apply in this query.
     *
     * @param array|string $columns
     *
     * @return Collection
     */
    public function all(array|string $columns = '*'): Collection
    {
        if ($this->skipCache) {
            return parent::all($columns);
        }

        $cacheKey = $this->getCacheKey(__FUNCTION__, request()->all());

        // Store or get from cache.
        return Cache::tags([$this->getTag()])->remember(
            $cacheKey,
            $this->getCacheTime(),
            function () use ($columns) {
                return parent::all($columns);
            }
        );
    }

    /**
     * Return eloquent collection of matching records.
     *
     * @param array|string $columns
     *
     * @return Collection
     */
    public function get(array|string $columns = '*'): Collection
    {
        if ($this->skipCache || !$this->cacheActive()) {
            return parent::get($columns);
        }

        $cacheKey = $this->getCacheKey(__FUNCTION__, request()->all());

        // Store or get from cache.
        return Cache::tags([$this->getTag()])->remember(
            $cacheKey,
            $this->getCacheTime(),
            function () use ($columns) {
                return parent::get($columns);
            }
        );
    }

    /**
     * Get first record.
     *
     * @param array|string $columns
     *
     * @return Model|null
     */
    public function first(array|string $columns = '*')
    {
        if ($this->skipCache || !$this->cacheActive()) {
            return parent::first($columns);
        }

        $cacheKey = $this->getCacheKey(__FUNCTION__, request()->all());

        // Store or get from cache.
        return Cache::tags([$this->getTag()])->remember(
            $cacheKey,
            $this->getCacheTime(),
            function () use ($columns) {
                return parent::first($columns);
            }
        );
    }

    /**
     * Get first entity record or new entity instance.
     *
     * @param array $where
     *
     * @return mixed
     */
    public function firstOrNew(array $where)
    {
        if ($this->skipCache || !$this->cacheActive()) {
            return parent::firstOrNew($where);
        }

        $cacheKey = $this->getCacheKey(__FUNCTION__, request()->all());

        // Store or get from cache.
        return Cache::tags([$this->getTag()])->remember(
            $cacheKey,
            $this->getCacheTime(),
            function () use ($where) {
                return parent::firstOrNew($where);
            }
        );
    }

    /**
     * Find where.
     *
     * @param array|string $conditions
     * @param array|string $columns
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return Collection
     */
    public function findWhere(array|string $conditions, array|string $columns = ['*']): Collection
    {
        if ($this->skipCache || !$this->cacheActive()) {
            return parent::findWhere($conditions, $columns);
        }

        $cacheKey = $this->getCacheKey(__FUNCTION__, request()->all());

        // Store or get from cache.
        return Cache::tags([$this->getTag()])->remember(
            $cacheKey,
            $this->getCacheTime(),
            function () use ($conditions, $columns) {
                return parent::findWhere($conditions, $columns);
            }
        );
    }

    /**
     * Find where In.
     *
     * @param string $column
     * @param array  $where
     * @param array  $columns
     *
     * @return Collection
     */
    public function findWhereIn(string $column, array $where, array|string $columns = '*'): Collection
    {
        if ($this->skipCache || !$this->cacheActive()) {
            return parent::findWhereIn($column, $where, $columns);
        }

        $cacheKey = $this->getCacheKey(__FUNCTION__, request()->all());

        // Store or get from cache.
        return Cache::tags([$this->getTag()])->remember(
            $cacheKey,
            $this->getCacheTime(),
            function () use ($column, $where, $columns) {
                return parent::findWhereIn($column, $where, $columns);
            }
        );
    }

    /**
     * Find where not In.
     *
     * @param string $column
     * @param array  $where
     * @param array  $columns
     *
     * @return Collection
     */
    public function findWhereNotIn(string $column, array $where, array|string $columns = '*'): Collection
    {
        if ($this->skipCache || !$this->cacheActive()) {
            return parent::findWhereNotIn($column, $where, $columns);
        }

        $cacheKey = $this->getCacheKey(__FUNCTION__, request()->all());

        // Store or get from cache.
        return Cache::tags([$this->getTag()])->remember(
            $cacheKey,
            $this->getCacheTime(),
            function () use ($column, $where, $columns) {
                return parent::findWhereNotIn($column, $where, $columns);
            }
        );
    }

    /**
     * Save new entity.
     *
     * @param array $parameters
     *
     * @return mixed
     */
    public function create(array $parameters = [])
    {
        $this->clearCache();

        return parent::create($parameters);
    }

    /**
     * Create new model or update existing.
     *
     * @param array $where
     * @param array $values
     *
     * @return mixed
     */
    public function updateOrCreate(array $where = [], array $values = [])
    {
        $this->clearCache();

        return parent::updateOrCreate($where, $values);
    }

    /**
     * Update entity.
     *
     * @param int   $id
     * @param array $parameters
     *
     * @return mixed
     */
    public function update(int $id, array $parameters = [])
    {
        $this->clearCache();

        return parent::update($id, $parameters);
    }

    /**
     * Delete entity.
     *
     * @param int $id
     *
     * @return BaseInterface
     */
    public function delete(int $id): BaseInterface
    {
        $this->clearCache();

        return parent::delete($id);
    }

    /**
     * Paginate results.
     *
     * @param int|null   $perPage
     * @param array|string  $columns
     * @param string $pageName
     * @param int|null   $page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = '*', $pageName = 'page', $page = null)
    {
        if ($this->skipCache || !$this->cacheActive()) {
            return parent::paginate($perPage, $columns, $pageName, $page);
        }

        $cacheKey = $this->getCacheKey(__FUNCTION__, request()->all());

        // Store or get from cache.
        return Cache::tags([$this->getTag()])->remember(
            $cacheKey,
            $this->getCacheTime(),
            function () use ($perPage, $columns, $pageName, $page) {
                return parent::paginate($perPage, $columns, $pageName, $page);
            }
        );
    }

    /**
     * Paginate results (simple).
     *
     * @param null   $perPage
     * @param array  $columns
     * @param string $pageName
     * @param null   $page
     *
     * @return mixed
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        if ($this->skipCache || !$this->cacheActive()) {
            return parent::simplePaginate($perPage, $columns, $pageName, $page);
        }

        $cacheKey = $this->getCacheKey(__FUNCTION__, request()->all());

        // Store or get from cache.
        return Cache::tags([$this->getTag()])->remember(
            $cacheKey,
            $this->getCacheTime(),
            function () use ($perPage, $columns, $pageName, $page) {
                return parent::simplePaginate($perPage, $columns, $pageName, $page);
            }
        );
    }

    /**
     * Count results.
     *
     * @param array|string $columns
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return int
     */
    public function count(string|null $columns = '*'): int
    {
        if ($this->skipCache || !$this->cacheActive()) {
            return parent::count($columns);
        }

        $cacheKey = $this->getCacheKey(__FUNCTION__, request()->all());

        // Store or get from cache.
        return Cache::tags([$this->getTag()])->remember(
            $cacheKey,
            $this->getCacheTime(),
            function () use ($columns) {
                return parent::count($columns);
            }
        );
    }

    /**
     * Retrieve the sum of the values of a given column.
     *
     * @param string $column
     *
     * @return int
     *
     */
    public function sum(string $column)
    {
        if ($this->skipCache || !$this->cacheActive()) {
            return parent::sum($column);
        }

        $cacheKey = $this->getCacheKey(__FUNCTION__, request()->all());

        // Store or get from cache.
        return Cache::tags([$this->getTag()])->remember(
            $cacheKey,
            $this->getCacheTime(),
            function () use ($column) {
                return parent::sum($column);
            }
        );
    }

    /**
     * Try to get actual authenticated user ID.
     *
     * @return string
     */
    private function getTag(): string
    {
        // If user tag was set manually, user it.
        if ($this->userTag !== null) {
            return class_basename($this) . '_' . $this->userTag;
        }

        if ($this->useUserTag) {
            foreach ($this->getCacheGuards() as $guard) {
                if (auth($guard)->check()) {
                    return class_basename($this) . '_' . auth($guard)->user()->getAuthIdentifier();
                }
            }
        }

        return class_basename($this) . '_0';
    }

    /**
     * Checking if caching is activated in config file.
     *
     * @return bool
     */
    private function cacheActive(): bool
    {
        return config('laravel-repository.repository.cache.active', false);
    }

    /**
     * Get cache time (in seconds).
     *
     * @return int
     */
    private function getCacheTime(): int
    {
        return (int) config('laravel-repository.repository.cache.time', 3600);
    }

    /**
     * Get cache guards to search for auth user ID.
     *
     * @return array
     */
    private function getCacheGuards(): array
    {
        return config('laravel-repository.repository.cache.guards', []);
    }
}
