<?php

namespace Vdhoangson\LaravelRepository\Repositories;

use Closure;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Vdhoangson\LaravelRepository\Repositories\Criteria\BaseCriteria;
use Vdhoangson\LaravelRepository\Repositories\Interfaces\BaseInterface;
use Vdhoangson\LaravelRepository\Repositories\Exceptions\RepositoryEntityException;
use Vdhoangson\LaravelRepository\Repositories\Criteria\Interfaces\BaseCriteriaInterface;

/**
 * Class BaseRepository.
 * */
abstract class BaseRepository implements BaseInterface
{
    /**
     * Application container.
     *
     * @var Container
     */
    public $app;

    /**
     * Entity class that will be use in repository.
     *
     * @var Model
     */
    protected Model $entity;

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

    /**
     * BaseRepository constructor.
     *
     * @param Container $app
     *
     * @throws RepositoryEntityException
     * @throws BindingResolutionException
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->criteria = new Collection();
        $this->makeEntity();
    }

    abstract public function entity(): string;

    /**
     * Make new entity instance.
     *
     * @throws RepositoryEntityException
     * @throws BindingResolutionException
     *
     * @return BaseInterface
     */
    public function makeEntity(): BaseInterface
    {
        // Make new model instance.
        $entity = $this->app->make($this->entity());

        // Checking instance.
        if (!$entity instanceof Model) {
            throw new RepositoryEntityException($this->entity());
        }

        $this->entity = $entity;
        $this->query = $this->entity->newQuery();

        return $this;
    }

    /**
     * Get entity instance.
     *
     * @return Model|Builder
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set entity instance.
     *
     * @param Model|Builder $entity
     *
     * @return BaseInterface
     */
    public function setEntity($entity): BaseInterface
    {
        $this->entity = $entity;

        return $this;
    }

    public function setQuery(Builder $query): void
    {
        $this->query = $query;
    }

    public function getQuery(): Builder
    {
        if (!isset($this->query)) {
            $this->query = $this->getEntity()->newQuery();
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
     * Reset entity instance.
     *
     * @return void
     */
    public function resetEntity(): void
    {
        $this->makeEntity();
    }

    /**
     * Push criteria.
     *
     * @param BaseCriteriaInterface|string $criteria
     *
     * @return BaseInterface
     */
    public function pushCriteria($criteria): BaseInterface
    {
        if (is_string($criteria)) {
            $criteria = new $criteria();
        }
        if (!$criteria instanceof BaseCriteriaInterface) {
            throw new \Exception('Class ' . get_class($criteria) . ' must be an instance of Vdhoangson\\LaravelRepository\\Repositories\\Criteria\\Interfaces\\BaseCriteriaInterface');
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
     * @return BaseInterface
     */
    public function popCriteria($criteria): BaseInterface
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
     * @return BaseInterface
     */
    public function applyCriteria(): BaseInterface
    {
        if ($this->skipCriteria === true) {
            return $this;
        }

        $criteria = $this->getCriteria();

        if ($criteria instanceof Collection) {
            foreach ($criteria as $c) {
                if (
                    $c instanceof BaseCriteriaInterface
                    && $c instanceof BaseCriteria
                ) {
                    $this->query = $c->apply($this->getQuery());
                }
            }
        }

        return $this;
    }

    /**
     * Skip using criteria.
     *
     * @param bool $skip
     *
     * @return BaseInterface
     */
    public function skipCriteria(bool $skip): BaseInterface
    {
        $this->skipCriteria = $skip;

        return $this;
    }

    /**
     * Clear criteria array.
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return BaseInterface
     */
    public function clearCriteria(): BaseInterface
    {
        $this->criteria = new Collection();
        $this->makeEntity();

        return $this;
    }

    /**
     * Query Scope
     *
     * @param Closure $scope
     *
     * @return $this
     */
    public function scopeQuery(Closure $scope): BaseInterface
    {
        $this->scopeQuery = $scope;

        return $this;
    }

    /**
     * Apply scope in current Query
     *
     * @return $this
     */
    public function applyScope(): BaseInterface
    {
        if (isset($this->scopeQuery) && is_callable($this->scopeQuery)) {
            $callback = $this->scopeQuery;
            $this->query = $callback($this->getQuery());
        }

        return $this;
    }

    /**
     * Reset Query Scope
     *
     * @return $this
     */
    public function resetScope(): BaseInterface
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

        $this->resetQuery();
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

        $results = $this->getEntity();

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
        $model = $this->query->findOrFail($id);
        $model->fill($attributes);
        $model->save();

        $this->resetScope();
        $this->resetQuery();

        return $model;
    }

    /**
     * Delete entity.
     *
     * @param int $id
     *
     * @throws Exception
     *
     * @return BaseInterface
     */
    public function delete(int $id): BaseInterface
    {
        $result = $this->query->findOrFail($id);
        $result->delete();

        $this->resetQuery();

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
        $results = $this->query->firstOrNew($where);

        $this->resetQuery();

        return $results;
    }

    /**
     * Order by records.
     *
     * @param string $column
     * @param string $direction
     *
     * @return BaseInterface
     */
    public function orderBy(string $column, string $direction = 'asc'): BaseInterface
    {
        $this->query = $this->query->orderBy($column, $direction);

        return $this;
    }

    /**
     * Relation sub-query.
     *
     * @param array|string $relations
     *
     * @return BaseInterface
     */
    public function with(array|string $relations): BaseInterface
    {
        $this->query = $this->query->with($relations);

        return $this;
    }

    /**
     * Begin database transaction.
     *
     * @return BaseInterface
     */
    public function transactionBegin(): BaseInterface
    {
        DB::beginTransaction();

        return $this;
    }

    /**
     * Commit database transaction.
     *
     * @return BaseInterface
     */
    public function transactionCommit(): BaseInterface
    {
        DB::commit();

        return $this;
    }

    /**
     * Rollback transaction.
     *
     * @return BaseInterface
     */
    public function transactionRollback(): BaseInterface
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
     * @return TFirstDefault|TValue
     */
    public function findById(int $id, array|string $columns = '*')
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->query->where(['id' => $id])->first($columns);

        $this->resetQuery();
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
     * @return Collection
     */
    public function findWhere(array|string $conditions, array|string $columns = '*'): Collection
    {
        $this->applyCriteria();
        $this->applyScope();
        $this->applyConditions($conditions);

        $results = $this->query->get($columns);

        $this->resetQuery();
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

        $results = $this->query->whereIn($column, $where)->get($columns);

        $this->resetQuery();
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

        $results = $this->getQuery()->whereNotIn($column, $where)->get($columns);

        $this->resetQuery();
        $this->resetScope();

        return $results;
    }

    /**
     * Find data by field and value
     *
     * @param       $field
     * @param       $value
     * @param array|string $columns
     *
     * @return Collection|array
     */
    public function findByField($field, $value = null, $columns = ['*']): Collection|array
    {
        $this->applyCriteria();

        $this->applyScope();

        $results = $this->getQuery()->where($field, '=', $value)->get($columns);

        $this->resetQuery();
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

        $results = $this->getQuery()->select($columns)->chunk($limit, $callback);

        $this->resetQuery();
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

        $result = $this->getQuery()->count($columns);

        $this->resetQuery();
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

        $result = $this->getQuery()->sum($column);

        $this->resetQuery();
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
    public function paginate($perPage = null, $columns = '*', $pageName = 'page', $page = null)
    {
        $this->applyCriteria();
        $this->applyScope();

        $results = $this->query->paginate($perPage, $columns, $pageName, $page);
        $results->appends(app('request')->query());

        $this->resetQuery();
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
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->applyCriteria();
        $this->applyScope();

        $results = $this->getQuery()->simplePaginate($perPage, $columns, $pageName, $page);

        $this->resetQuery();
        $this->resetScope();

        return $results;
    }

    /**
     * Get records with trashed entities.
     *
     * @return BaseInterface
     */
    public function withTrashed(): BaseInterface
    {
        $this->entity = $this->getQuery()->withTrashed();

        return $this;
    }

    /**
     * Get only trashed entities.
     *
     * @return BaseInterface
     */
    public function onlyTrashed(): BaseInterface
    {
        $this->entity = $this->getQuery()->onlyTrashed();

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
     * @return BaseInterface
     */
    public function has($relation, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null): BaseInterface
    {
        $this->entity = $this->getQuery()->has($relation, $operator, $count, $boolean, $callback);

        return $this;
    }

    /**
     * Or hase relation.
     *
     * @param string $relation
     * @param string $operator
     * @param int    $count
     *
     * @return BaseInterface
     */
    public function orHas($relation, $operator = '>=', $count = 1): BaseInterface
    {
        $this->entity = $this->getQuery()->orHas($relation, $operator, $count);

        return $this;
    }

    /**
     * Add a relationship count / exists condition to the query.
     *
     * @param $relation
     * @param string       $boolean
     * @param Closure|null $callback
     *
     * @return BaseInterface
     */
    public function doesntHave($relation, $boolean = 'and', Closure $callback = null): BaseInterface
    {
        $this->entity = $this->getQuery()->doesntHave($relation, $boolean, $callback);

        return $this;
    }

    /**
     * Add a relationship count / exists condition to the query with an "or".
     *
     * @param $relation
     *
     * @return BaseInterface
     */
    public function orDoesntHave($relation): BaseInterface
    {
        $this->entity = $this->getQuery()->orDoesntHave($relation);

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
     * @return BaseInterface
     */
    public function whereHas($relation, Closure $callback = null, $operator = '>=', $count = 1): BaseInterface
    {
        $this->entity = $this->getQuery()->whereHas($relation, $callback, $operator, $count);

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
     * @return BaseInterface
     */
    public function orWhereHas($relation, Closure $callback = null, $operator = '>=', $count = 1): BaseInterface
    {
        $this->entity = $this->getQuery()->orWhereHas($relation, $callback, $operator, $count);

        return $this;
    }

    /**
     * Where doesnt have relation.
     *
     * @param string       $relation
     * @param Closure|null $callback
     *
     * @return BaseInterface
     */
    public function whereDoesntHave($relation, Closure $callback = null): BaseInterface
    {
        $this->entity = $this->getQuery()->whereDoesntHave($relation, $callback);

        return $this;
    }

    /**
     * Or where doesnt have relation.
     *
     * @param string       $relation
     * @param Closure|null $callback
     *
     * @return BaseInterface
     */
    public function orWhereDoesntHave($relation, Closure $callback = null): BaseInterface
    {
        $this->entity = $this->getQuery()->orWhereDoesntHave($relation, $callback);

        return $this;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query.
     *
     * @param $relation
     * @param $types
     * @param string       $operator
     * @param int          $count
     * @param string       $boolean
     * @param Closure|null $callback
     *
     * @return BaseInterface
     */
    public function hasMorph($relation, $types, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null): BaseInterface
    {
        $this->entity = $this->getEntity()->hasMorph($relation, $types, $operator, $count, $boolean, $callback);

        return $this;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with an "or".
     *
     * @param $relation
     * @param $types
     * @param string $operator
     * @param int    $count
     *
     * @return BaseInterface
     */
    public function orHasMorph($relation, $types, $operator = '>=', $count = 1): BaseInterface
    {
        $this->entity = $this->getEntity()->orHasMorph($relation, $types, $operator, $count);

        return $this;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query.
     *
     * @param $relation
     * @param $types
     * @param string       $boolean
     * @param Closure|null $callback
     *
     * @return BaseInterface
     */
    public function doesntHaveMorph($relation, $types, $boolean = 'and', Closure $callback = null): BaseInterface
    {
        $this->entity = $this->getEntity()->doesntHaveMorph($relation, $types, $boolean, $callback);

        return $this;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with an "or".
     *
     * @param $relation
     * @param $types
     *
     * @return BaseInterface
     */
    public function orDoesntHaveMorph($relation, $types): BaseInterface
    {
        $this->entity = $this->getEntity()->orDoesntHaveMorph($relation, $types);

        return $this;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses.
     *
     * @param $relation
     * @param $types
     * @param Closure|null $callback
     * @param string       $operator
     * @param int          $count
     *
     * @return BaseInterface
     */
    public function whereHasMorph($relation, $types, Closure $callback = null, $operator = '>=', $count = 1): BaseInterface
    {
        $this->entity = $this->getEntity()->whereHasMorph($relation, $types, $callback, $operator, $count);

        return $this;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param $relation
     * @param $types
     * @param Closure|null $callback
     * @param string       $operator
     * @param int          $count
     *
     * @return BaseInterface
     */
    public function orWhereHasMorph($relation, $types, Closure $callback = null, $operator = '>=', $count = 1): BaseInterface
    {
        $this->entity = $this->getEntity()->orWhereHasMorph($relation, $types, $callback, $operator, $count);

        return $this;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses.
     *
     * @param $relation
     * @param $types
     * @param Closure|null $callback
     *
     * @return BaseInterface
     */
    public function whereDoesntHaveMorph($relation, $types, Closure $callback = null): BaseInterface
    {
        $this->entity = $this->getEntity()->whereDoesntHaveMorph($relation, $types, $callback);

        return $this;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param $relation
     * @param $types
     * @param Closure|null $callback
     *
     * @return BaseInterface
     */
    public function orWhereDoesntHaveMorph($relation, $types, Closure $callback = null): BaseInterface
    {
        $this->entity = $this->getEntity()->orWhereDoesntHaveMorph($relation, $types, $callback);

        return $this;
    }

    /**
     * Count given relation.
     *
     * @param string|array $relations
     *
     * @return BaseInterface
     */
    public function withCount($relations): BaseInterface
    {
        $this->entity = $this->getEntity()->withCount($relations);

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
                $this->query = $this->query->where($attribute, $condition, $val);
            } else {
                $this->query = $this->query->where($attribute, '=', $value);
            }
        }
    }
}
