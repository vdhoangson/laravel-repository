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
    protected $app;

    /**
     * Entity class that will be use in repository.
     *
     * @var Model
     */
    protected $entity;

    /**
     * Criteria collection.
     *
     * @var Collection
     */
    protected $criteria;

    /**
     * Determine if criteria will be skipped in query.
     *
     * @var bool
     */
    protected $skipCriteria = false;

    /**
     * @var \Closure
     */
    protected $scopeQuery = null;

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
     * @param BaseCriteriaInterface $criteria
     *
     * @return BaseInterface
     */
    public function pushCriteria(BaseCriteriaInterface $criteria): BaseInterface
    {
        $this->criteria->push($criteria);

        return $this;
    }

    /**
     * Pop criteria.
     *
     * @param string $criteriaNamespace
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return BaseInterface
     */
    public function popCriteria(string $criteriaNamespace): BaseInterface
    {
        $this->criteria = $this->criteria->reject(function ($item) use ($criteriaNamespace) {
            return get_class($item) === $criteriaNamespace;
        });

        $this->makeEntity();

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
                    $this->entity = $c->apply($this->getEntity());
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
            $this->entity = $callback($this->getEntity());
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
     * @param array $columns
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection
    {
        $this->applyCriteria();

        $this->applyScope();

        $results = $this->getEntity()->all($columns);

        $this->resetEntity();
        $this->resetScope();

        return $results;
    }

    /**
     * Return eloquent collection of matching records.
     *
     * @param array $columns
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return Collection
     */
    public function get(array $columns = ['*']): Collection
    {
        $this->applyCriteria();

        $this->applyScope();

        $results = $this->getEntity()->get($columns);

        $this->resetEntity();
        $this->resetScope();

        return $results;
    }

    /**
     * Get first record.
     *
     * @param array $columns
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return Model|null
     */
    public function first(array $columns = ['*'])
    {
        $this->applyCriteria();

        $this->applyScope();

        $results = $this->getEntity()->first($columns);

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
        $this->entity = $this->getEntity()->newInstance($parameters);
        $this->getEntity()->save();

        $results = $this->getEntity();

        $this->resetEntity();

        return $results;
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
        $this->entity = $this->getEntity()->updateOrCreate($where, $values);

        $results = $this->getEntity();

        $this->resetEntity();

        return $results;
    }

    /**
     * Update entity.
     *
     * @param int   $id
     * @param array $parameters
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return Builder|Model
     */
    public function update(int $id, array $parameters = [])
    {
        $this->entity = $this->getEntity()->findOrFail($id);
        $this->getEntity()->fill($parameters);
        $this->getEntity()->save();

        $results = $this->getEntity();

        $this->resetEntity();

        return $results;
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
        $result = $this->getEntity()->findOrFail($id);
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
        $results = $this->getEntity()->firstOrNew($where);

        $this->resetEntity();

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
        $this->entity = $this->getEntity()->orderBy($column, $direction);

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
        $this->entity = $this->getEntity()->with($relations);

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
     * @param array $columns
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return TFirstDefault|TValue
     */
    public function findById(int $id, array $columns = ['*'])
    {
        $this->applyCriteria();

        $this->applyScope();

        $result = $this->findWhere(['id' => $id], $columns)->first();

        $this->resetEntity();
        $this->resetScope();

        return $result;
    }

    /**
     * Find where.
     *
     * @param array $where
     * @param array $columns
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return Collection|array
     */
    public function findWhere(array $where, array $columns = ['*']): Collection|array
    {
        $this->applyCriteria();

        $this->applyScope();

        $results = $this->getEntity()->where($where)->get($columns);

        $this->resetEntity();
        $this->resetScope();

        return $results;
    }

    /**
     * Find where In.
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
    public function findWhereIn(string $column, array $where, array $columns = ['*']): Collection|array
    {
        $this->applyCriteria();

        $this->applyScope();

        $results = $this->getEntity()->whereIn($column, $where)->get($columns);

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
    public function findWhereNotIn(string $column, array $where, array $columns = ['*']): Collection|array
    {
        $this->applyCriteria();

        $this->applyScope();

        $results = $this->getEntity()->whereNotIn($column, $where)->get($columns);

        $this->resetEntity();
        $this->resetScope();

        return $results;
    }

    /**
     * Find data by field and value
     *
     * @param       $field
     * @param       $value
     * @param array $columns
     *
     * @return Collection|array
     */
    public function findByField($field, $value = null, $columns = ['*']): Collection|array
    {
        $this->applyCriteria();

        $this->applyScope();

        $results = $this->getEntity()->where($field, '=', $value)->get($columns);

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
    public function chunk(int $limit, callable $callback, array $columns = ['*']): bool
    {
        $this->applyCriteria();

        $this->applyScope();

        $results = $this->getEntity()->select($columns)->chunk($limit, $callback);

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
    public function count($columns = '*'): int
    {
        $this->applyCriteria();

        $this->applyScope();

        $result = $this->getEntity()->count($columns);

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

        $result = $this->getEntity()->sum($column);

        $this->resetEntity();
        $this->resetScope();

        return $result;
    }

    /**
     * Paginate results.
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
     *
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->applyCriteria();

        $this->applyScope();

        $results = $this->getEntity()->paginate($perPage, $columns, $pageName, $page);

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
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->applyCriteria();

        $this->applyScope();

        $results = $this->getEntity()->simplePaginate($perPage, $columns, $pageName, $page);

        $this->resetEntity();
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
        $this->entity = $this->getEntity()->withTrashed();

        return $this;
    }

    /**
     * Get only trashed entities.
     *
     * @return BaseInterface
     */
    public function onlyTrashed(): BaseInterface
    {
        $this->entity = $this->getEntity()->onlyTrashed();

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
        $this->entity = $this->getEntity()->has($relation, $operator, $count, $boolean, $callback);

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
        $this->entity = $this->getEntity()->orHas($relation, $operator, $count);

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
        $this->entity = $this->getEntity()->doesntHave($relation, $boolean, $callback);

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
        $this->entity = $this->getEntity()->orDoesntHave($relation);

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
        $this->entity = $this->getEntity()->whereHas($relation, $callback, $operator, $count);

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
        $this->entity = $this->getEntity()->orWhereHas($relation, $callback, $operator, $count);

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
        $this->entity = $this->getEntity()->whereDoesntHave($relation, $callback);

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
        $this->entity = $this->getEntity()->orWhereDoesntHave($relation, $callback);

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
}
