<?php

namespace Vdhoangson\LaravelRepository;

use Closure;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Vdhoangson\LaravelRepository\Contracts\RepositoryInterface;
use Vdhoangson\LaravelRepository\Criteria\BaseCriteria;
use Vdhoangson\LaravelRepository\Exceptions\RepositoryEntityException;

/**
 * Class BaseRepository.
 * */
abstract class BaseRepository extends AbtractRepository implements RepositoryInterface
{
    /**
     * @var Builder
     */
    protected $query;

    /**
     * Criteria collection.
     *
     * @var Collection
     */
    public $criteria;

    /**
     * Determine if criteria will be skipped in query.
     *
     * @var bool
     */
    public $skipCriteria = false;

    /**
     * @var \Closure
     */
    public $scopeQuery = null;

    public function setQuery(Builder $query)
    {
        $this->query = $query;
    }

    public function getQuery(): Builder
    {
        if (!$this->query) {
            $this->query = $this->entity->newQuery();
        }

        return $this->query;
    }

    /**
     * @return Builder
     */
    public function resetQuery()
    {
        return $this->query = $this->entity->newQuery();
    }

    /**
     * Push criteria.
     *
     * @param BaseCriteria|string $criteria
     *
     * @return RepositoryInterface
     */
    public function pushCriteria($criteria): RepositoryInterface
    {
        if (is_string($criteria)) {
            $criteria = new $criteria();
        }
        if (!$criteria instanceof BaseCriteria) {
            throw new \Exception('Class ' . get_class($criteria) . ' must be an instance of Vdhoangson\LaravelRepository\Criteria\BaseCriteria');
        }
        $this->criteria->push($criteria);

        return $this;
    }

    /**
     * Pop criteria.
     *
     * @param  $criteria
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return RepositoryInterface
     */
    public function popCriteria($criteria): RepositoryInterface
    {
        $this->criteria = $this->criteria->reject(function ($item) use ($criteria) {
            if (is_object($item) && is_string($criteria)) {
                return get_class($item) === $criteria;
            }
            if (is_string($item) && is_object($criteria)) {
                return $item === get_class($criteria);
            }

            return get_class($item) === get_class($criteria);
        });

        return $this;
    }

    /**
     * Get criteria.
     *
     * @return Collection|null
     */
    public function getCriteria(): Collection|null
    {
        return $this->criteria;
    }

    /**
     * Apply criteria to eloquent query.
     *
     * @return RepositoryInterface
     */
    public function applyCriteria(): RepositoryInterface
    {
        // Skip criteria
        if ($this->skipCriteria === true) {
            return $this;
        }

        $criteria = $this->getCriteria();

        if ($criteria instanceof Collection) {
            foreach ($criteria as $c) {
                if ($c instanceof BaseCriteria) {
                    $this->entity = $c->apply($this->entity);
                }
            }
        }

        return $this;
    }

    /**
     * Query Scope
     *
     * @param Closure $scope
     *
     * @return $this
     */
    public function scopeQuery(Closure $scope): RepositoryInterface
    {
        $this->scopeQuery = $scope;

        return $this;
    }

    /**
     * Apply scope in current Query
     *
     * @return $this
     */
    public function applyScope(): RepositoryInterface
    {
        if (isset($this->scopeQuery) && is_callable($this->scopeQuery)) {
            $callback = $this->scopeQuery;
            $this->entity = $callback($this->getEntity());
        }

        return $this;
    }

    /**
     * Reset Query Scope
     *
     * @return $this
     */
    public function resetScope(): RepositoryInterface
    {
        $this->scopeQuery = null;

        return $this;
    }

    /**
     * Return eloquent collection of all records of entity
     * Criteria are not apply in this query.
     *
     * @param array|string $columns
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return Collection
     */
    public function all(array|string $columns = '*'): Collection
    {
        $this->applyCriteria();
        $this->applyScope();

        $results = $this->entity->all($columns);

        $this->resetQuery();
        $this->resetScope();

        return $results;
    }

    /**
     * Return eloquent collection of matching records.
     *
     * @param array|string $columns
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return Collection
     */
    public function get(array|string $columns = '*'): Collection
    {
        $this->applyCriteria();
        $this->applyScope();

        $results = $this->entity->get($columns);

        $this->resetQuery();
        $this->resetScope();

        return $results;
    }

    /**
     * Get first record.
     *
     * @param array|string $columns
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return Model|null
     */
    public function first(array|string $columns = '*')
    {
        $this->applyCriteria();

        $this->applyScope();

        $results = $this->entity->first($columns);

        $this->resetEntity();
        $this->resetScope();

        return $results;
    }

    /**
     * Save new entity.
     *
     * @param array $parameters
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return Builder|Model
     */
    public function create(array $parameters = [])
    {
        $result = $this->entity->create($parameters);

        $this->resetEntity();
        $this->resetQuery();

        return $result;
    }

    /**
     * Create new model or update existing.
     *
     * @param array $where
     * @param array $values
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return Builder|Model
     */
    public function updateOrCreate(array $where = [], array $values = [])
    {
        $this->entity = $this->entity->updateOrCreate($where, $values);

        $results = $this->entity;

        $this->resetScope();
        $this->resetEntity();
        $this->resetQuery();

        return $results;
    }

    /**
     * Update entity.
     *
     * @param int   $id
     * @param array $attributes
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return Builder|Model
     */
    public function update(int $id, array $attributes = [])
    {
        $model = $this->entity->findOrFail($id);
        $model->fill($attributes);
        $model->save();

        $this->resetScope();
        $this->resetEntity();

        return $model;
    }

    /**
     * Delete entity.
     *
     * @param int $id
     *
     * @throws Exception
     *
     * @return RepositoryInterface
     */
    public function delete(int $id): RepositoryInterface
    {
        $result = $this->entity->findOrFail($id);
        $result->delete();

        $this->resetEntity();

        return $this;
    }

    /**
     * Get first entity record or new entity instance.
     *
     * @param array $where
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return Builder|Model
     */
    public function firstOrNew(array $where)
    {
        $results = $this->entity->firstOrNew($where);

        $this->resetEntity();

        return $results;
    }

    /**
     * Order by records.
     *
     * @param string $column
     * @param string $direction
     *
     * @return RepositoryInterface
     */
    public function orderBy(string $column, string $direction = 'asc'): RepositoryInterface
    {
        $this->entity = $this->entity->orderBy($column, $direction);

        return $this;
    }

    /**
     * Relation sub-query.
     *
     * @param array|string $relations
     *
     * @return RepositoryInterface
     */
    public function with(array|string $relations): RepositoryInterface
    {
        $this->entity = $this->entity->with($relations);

        return $this;
    }

    /**
     * Begin database transaction.
     *
     * @return RepositoryInterface
     */
    public function transactionBegin(): RepositoryInterface
    {
        DB::beginTransaction();

        return $this;
    }

    /**
     * Commit database transaction.
     *
     * @return RepositoryInterface
     */
    public function transactionCommit(): RepositoryInterface
    {
        DB::commit();

        return $this;
    }

    /**
     * Rollback transaction.
     *
     * @return RepositoryInterface
     */
    public function transactionRollback(): RepositoryInterface
    {
        DB::rollBack();

        return $this;
    }

    /**
     * Find by ID.
     *
     * @param int $id
     * @param array|string $columns
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return Model|null
     */
    public function findById(int $id, array|string $columns = '*')
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->entity->where(['id' => $id])->first($columns);

        $this->resetEntity();
        $this->resetScope();

        return $result;
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
     * @return Collection|null
     */
    public function findWhere(array|string $conditions, array|string $columns = '*'): Collection
    {
        $this->applyCriteria();
        $this->applyScope();
        $this->applyConditions($conditions);

        $results = $this->entity->get($columns);

        $this->resetEntity();
        $this->resetScope();

        return $results;
    }

    /**
     * Find where In.
     *
     * @param string $column
     * @param array  $where
     * @param array|string  $columns
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return Collection
     */
    public function findWhereIn(string $column, array $where, array|string $columns = '*'): Collection
    {
        $this->applyCriteria();
        $this->applyScope();

        $results = $this->entity->whereIn($column, $where)->get($columns);

        $this->resetEntity();
        $this->resetScope();

        return $results;
    }

    /**
     * Find where not In.
     *
     * @param string $column
     * @param array  $where
     * @param array  $columns
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return Collection|array
     */
    public function findWhereNotIn(string $column, array $where, array|string $columns = '*'): Collection|array
    {
        $this->applyCriteria();
        $this->applyScope();

        $results = $this->entity->whereNotIn($column, $where)->get($columns);

        $this->resetEntity();
        $this->resetScope();

        return $results;
    }

    /**
     * Find data by field and value
     *
     * @param       $field
     * @param mixed $value
     * @param array|string $columns
     *
     * @return Collection|array
     */
    public function findByField($field, mixed $value = null, $columns = ['*']): Collection|array
    {
        $this->applyCriteria();

        $this->applyScope();

        $results = $this->entity->where($field, '=', $value)->get($columns);

        $this->resetEntity();
        $this->resetScope();

        return $results;
    }

    /**
     * Chunk query results.
     *
     * @param int      $limit
     * @param callable $callback
     * @param array    $columns
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return bool
     */
    public function chunk(int $limit, callable $callback, array|string $columns = '*'): bool
    {
        $this->applyCriteria();

        $this->applyScope();

        $results = $this->entity->select($columns)->chunk($limit, $callback);

        $this->resetEntity();
        $this->resetScope();

        return $results;
    }

    /**
     * Count results.
     *
     * @param string|null $columns
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return int
     */
    public function count(string|null $columns = '*'): int
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->entity->count($columns);

        $this->resetEntity();
        $this->resetScope();

        return $result;
    }

    /**
     * Retrieve the sum of the values of a given column.
     *
     * @param string $column
     *
     * @return mixed
     */
    public function sum(string $column)
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->entity->sum($column);

        $this->resetEntity();
        $this->resetScope();

        return $result;
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
    public function paginate(?int $perPage = null, $columns = '*', $pageName = 'page', ?int $page = null)
    {
        $this->applyCriteria();
        $this->applyScope();

        $results = $this->entity->paginate($perPage, $columns, $pageName, $page);
        $results->appends(app('request')->query());

        $this->resetEntity();
        $this->resetScope();

        return $results;
    }

    /**
     * Paginate results (simple).
     *
     * @param int|null $perPage
     * @param array    $columns
     * @param string   $pageName
     * @param int|null $page
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return mixed
     */
    public function simplePaginate(?int $perPage = null, $columns = ['*'], $pageName = 'page', ?int $page = null)
    {
        $this->applyCriteria();
        $this->applyScope();

        $results = $this->entity->simplePaginate($perPage, $columns, $pageName, $page);

        $this->resetEntity();
        $this->resetScope();

        return $results;
    }

    /**
     * Get records with trashed entities.
     *
     * @return RepositoryInterface
     */
    public function withTrashed(): RepositoryInterface
    {
        $this->entity = $this->entity->withTrashed();

        return $this;
    }

    /**
     * Get only trashed entities.
     *
     * @return RepositoryInterface
     */
    public function onlyTrashed(): RepositoryInterface
    {
        $this->entity = $this->entity->onlyTrashed();

        return $this;
    }

    /**
     * Has relation.
     *
     * @param string       $relation
     * @param string       $operator
     * @param int          $count
     * @param string       $boolean
     * @param Closure|null $callback
     *
     * @return RepositoryInterface
     */
    public function has($relation, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null): RepositoryInterface
    {
        $this->entity = $this->entity->has($relation, $operator, $count, $boolean, $callback);

        return $this;
    }

    /**
     * Or hase relation.
     *
     * @param string $relation
     * @param string $operator
     * @param int    $count
     *
     * @return RepositoryInterface
     */
    public function orHas($relation, $operator = '>=', $count = 1): RepositoryInterface
    {
        $this->entity = $this->entity->orHas($relation, $operator, $count);

        return $this;
    }

    /**
     * Add a relationship count / exists condition to the entity.
     *
     * @param $relation
     * @param string       $boolean
     * @param Closure|null $callback
     *
     * @return RepositoryInterface
     */
    public function doesntHave($relation, $boolean = 'and', Closure $callback = null): RepositoryInterface
    {
        $this->entity = $this->entity->doesntHave($relation, $boolean, $callback);

        return $this;
    }

    /**
     * Add a relationship count / exists condition to the entity with an "or".
     *
     * @param $relation
     *
     * @return RepositoryInterface
     */
    public function orDoesntHave($relation): RepositoryInterface
    {
        $this->entity = $this->entity->orDoesntHave($relation);

        return $this;
    }

    /**
     * Where has relation.
     *
     * @param string       $relation
     * @param Closure|null $callback
     * @param string       $operator
     * @param int          $count
     *
     * @return RepositoryInterface
     */
    public function whereHas($relation, Closure $callback = null, $operator = '>=', $count = 1): RepositoryInterface
    {
        $this->entity = $this->entity->whereHas($relation, $callback, $operator, $count);

        return $this;
    }

    /**
     * Or where has relation.
     *
     * @param string       $relation
     * @param Closure|null $callback
     * @param string       $operator
     * @param int          $count
     *
     * @return RepositoryInterface
     */
    public function orWhereHas($relation, Closure $callback = null, $operator = '>=', $count = 1): RepositoryInterface
    {
        $this->entity = $this->entity->orWhereHas($relation, $callback, $operator, $count);

        return $this;
    }

    /**
     * Where doesnt have relation.
     *
     * @param string       $relation
     * @param Closure|null $callback
     *
     * @return RepositoryInterface
     */
    public function whereDoesntHave($relation, Closure $callback = null): RepositoryInterface
    {
        $this->entity = $this->entity->whereDoesntHave($relation, $callback);

        return $this;
    }

    /**
     * Or where doesnt have relation.
     *
     * @param string       $relation
     * @param Closure|null $callback
     *
     * @return RepositoryInterface
     */
    public function orWhereDoesntHave($relation, Closure $callback = null): RepositoryInterface
    {
        $this->entity = $this->entity->orWhereDoesntHave($relation, $callback);

        return $this;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the entity.
     *
     * @param $relation
     * @param $types
     * @param string       $operator
     * @param int          $count
     * @param string       $boolean
     * @param Closure|null $callback
     *
     * @return RepositoryInterface
     */
    public function hasMorph($relation, $types, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null): RepositoryInterface
    {
        $this->entity = $this->entity->hasMorph($relation, $types, $operator, $count, $boolean, $callback);

        return $this;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the entity with an "or".
     *
     * @param $relation
     * @param $types
     * @param string $operator
     * @param int    $count
     *
     * @return RepositoryInterface
     */
    public function orHasMorph($relation, $types, $operator = '>=', $count = 1): RepositoryInterface
    {
        $this->entity = $this->entity->orHasMorph($relation, $types, $operator, $count);

        return $this;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the entity.
     *
     * @param $relation
     * @param $types
     * @param string       $boolean
     * @param Closure|null $callback
     *
     * @return RepositoryInterface
     */
    public function doesntHaveMorph($relation, $types, $boolean = 'and', Closure $callback = null): RepositoryInterface
    {
        $this->entity = $this->entity->doesntHaveMorph($relation, $types, $boolean, $callback);

        return $this;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the entity with an "or".
     *
     * @param $relation
     * @param $types
     *
     * @return RepositoryInterface
     */
    public function orDoesntHaveMorph($relation, $types): RepositoryInterface
    {
        $this->entity = $this->entity->orDoesntHaveMorph($relation, $types);

        return $this;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the entity with where clauses.
     *
     * @param $relation
     * @param $types
     * @param Closure|null $callback
     * @param string       $operator
     * @param int          $count
     *
     * @return RepositoryInterface
     */
    public function whereHasMorph($relation, $types, Closure $callback = null, $operator = '>=', $count = 1): RepositoryInterface
    {
        $this->entity = $this->entity->whereHasMorph($relation, $types, $callback, $operator, $count);

        return $this;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the entity with where clauses and an "or".
     *
     * @param $relation
     * @param $types
     * @param Closure|null $callback
     * @param string       $operator
     * @param int          $count
     *
     * @return RepositoryInterface
     */
    public function orWhereHasMorph($relation, $types, Closure $callback = null, $operator = '>=', $count = 1): RepositoryInterface
    {
        $this->entity = $this->entity->orWhereHasMorph($relation, $types, $callback, $operator, $count);

        return $this;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the entity with where clauses.
     *
     * @param $relation
     * @param $types
     * @param Closure|null $callback
     *
     * @return RepositoryInterface
     */
    public function whereDoesntHaveMorph($relation, $types, Closure $callback = null): RepositoryInterface
    {
        $this->entity = $this->entity->whereDoesntHaveMorph($relation, $types, $callback);

        return $this;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the entity with where clauses and an "or".
     *
     * @param $relation
     * @param $types
     * @param Closure|null $callback
     *
     * @return RepositoryInterface
     */
    public function orWhereDoesntHaveMorph($relation, $types, Closure $callback = null): RepositoryInterface
    {
        $this->entity = $this->entity->orWhereDoesntHaveMorph($relation, $types, $callback);

        return $this;
    }

    /**
     * Count given relation.
     *
     * @param string|array $relations
     *
     * @return RepositoryInterface
     */
    public function withCount($relations): RepositoryInterface
    {
        $this->entity = $this->entity->withCount($relations);

        return $this;
    }

    /**
     * Applies the given where conditions to the model.
     *
     * @param array $where
     *
     * @return void
     */
    protected function applyConditions(array $where)
    {
        foreach ($where as $attribute => $value) {
            if (is_array($value)) {
                list($attribute, $condition, $val) = $value;
                $this->entity = $this->entity->where($attribute, $condition, $val);
            } else {
                $this->entity = $this->entity->where($attribute, '=', $value);
            }
        }
    }
}
