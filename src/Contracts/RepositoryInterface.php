<?php

namespace Vdhoangson\LaravelRepository\Contracts;

use Closure;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Vdhoangson\LaravelRepository\Contracts\BaseCriteriaInterface;
use Vdhoangson\LaravelRepository\Exceptions\RepositoryEntityException;

/**
 * Interface RepositoryInterface.
 * */
interface RepositoryInterface
{
    /**
     * Model entity class that will be use in repository.
     *
     * @return RepositoryInterface
     */
    public function entity();

    /**
     * Make new entity instance.
     *
     * @throws RepositoryEntityException
     * @throws BindingResolutionException
     *
     * @return RepositoryInterface
     */
    public function makeEntity();

    /**
     * Get entity instance.
     *
     * @return Model|Builder
     */
    public function getEntity();

    public function setQuery(Builder $query);

    public function getQuery(): Builder;

    /**
     * Push criteria.
     *
     * @param BaseCriteriaInterface|string $criteria
     *
     * @return RepositoryInterface
     */
    public function pushCriteria($criteria);

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
    public function popCriteria($criteria);

    /**
     * Get criteria.
     *
     * @return Collection|null
     */
    public function getCriteria(): Collection|null;

    /**
     * Apply criteria to eloquent query.
     *
     * @return RepositoryInterface
     */
    public function applyCriteria();

    /**
     * Skip using criteria.
     *
     * @param bool $skip
     *
     * @return RepositoryInterface
     */
    public function skipCriteria(bool $skip);

    /**
     * Clear criteria array.
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return RepositoryInterface
     */
    public function clearCriteria();

    /**
     * Query Scope
     *
     * @param Closure $scope
     *
     * @return $this
     */
    public function scopeQuery(Closure $scope);

    /**
     * Apply scope in current Query
     *
     * @return $this
     */
    public function applyScope();

    /**
     * Reset Query Scope
     *
     * @return $this
     */
    public function resetScope();

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
    public function all(array|string $columns = '*'): Collection;

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
    public function get(array|string $columns = '*'): Collection;

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
    public function first(array|string $columns = '*');

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
    public function create(array $parameters = []);

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
    public function updateOrCreate(array $where = [], array $values = []);

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
    public function update(int $id, array $parameters = []);

    /**
     * Delete entity.
     *
     * @param int $id
     *
     * @throws Exception
     *
     * @return RepositoryInterface
     */
    public function delete(int $id);

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
    public function firstOrNew(array $where);

    /**
     * Order by records.
     *
     * @param string $column
     * @param string $direction
     *
     * @return RepositoryInterface
     */
    public function orderBy(string $column, string $direction = 'asc');

    /**
     * Relation sub-query.
     *
     * @param array|string $relations
     *
     * @return RepositoryInterface
     */
    public function with(array|string $relations);

    /**
     * Begin database transaction.
     *
     * @return RepositoryInterface
     */
    public function transactionBegin();

    /**
     * Commit database transaction.
     *
     * @return RepositoryInterface
     */
    public function transactionCommit();

    /**
     * Rollback transaction.
     *
     * @return RepositoryInterface
     */
    public function transactionRollback();

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
    public function findById(int $id, array|string $columns = '*');

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
    public function findWhere(array|string $conditions, array|string $columns = '*'): Collection;

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
     * @return Collection
     */
    public function findWhereIn(string $column, array $where, array|string $columns = '*'): Collection;

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
    public function findWhereNotIn(string $column, array $where, array|string $columns = '*'): Collection|array;

    /**
     * Find data by field and value
     *
     * @param       $field
     * @param       $value
     * @param array|string $columns
     *
     * @return Collection|array
     */
    public function findByField($field, $value = null, $columns = ['*']): Collection|array;

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
    public function chunk(int $limit, callable $callback, array|string $columns = '*'): bool;

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
    public function count(string|null $columns = '*'): int;

    /**
     * Retrieve the sum of the values of a given column.
     *
     * @param string $column
     *
     * @return mixed
     *
     */
    public function sum(string $column);

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
    public function paginate($perPage = null, $columns = '*', $pageName = 'page', $page = null);

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
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);

    /**
     * Get records with trashed entities.
     *
     * @return RepositoryInterface
     */
    public function withTrashed();

    /**
     * Get only trashed entities.
     *
     * @return RepositoryInterface
     */
    public function onlyTrashed();

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
    public function has($relation, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null);

    /**
     * Or hase relation.
     *
     * @param string $relation
     * @param string $operator
     * @param int    $count
     *
     * @return RepositoryInterface
     */
    public function orHas($relation, $operator = '>=', $count = 1);

    /**
     * Add a relationship count / exists condition to the query.
     *
     * @param $relation
     * @param string       $boolean
     * @param Closure|null $callback
     *
     * @return RepositoryInterface
     */
    public function doesntHave($relation, $boolean = 'and', Closure $callback = null);

    /**
     * Add a relationship count / exists condition to the query with an "or".
     *
     * @param $relation
     *
     * @return RepositoryInterface
     */
    public function orDoesntHave($relation);

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
    public function whereHas($relation, Closure $callback = null, $operator = '>=', $count = 1);

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
    public function orWhereHas($relation, Closure $callback = null, $operator = '>=', $count = 1);

    /**
     * Where doesnt have relation.
     *
     * @param string       $relation
     * @param Closure|null $callback
     *
     * @return RepositoryInterface
     */
    public function whereDoesntHave($relation, Closure $callback = null);

    /**
     * Or where doesnt have relation.
     *
     * @param string       $relation
     * @param Closure|null $callback
     *
     * @return RepositoryInterface
     */
    public function orWhereDoesntHave($relation, Closure $callback = null);

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
     * @return RepositoryInterface
     */
    public function hasMorph($relation, $types, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null);

    /**
     * Add a polymorphic relationship count / exists condition to the query with an "or".
     *
     * @param $relation
     * @param $types
     * @param string $operator
     * @param int    $count
     *
     * @return RepositoryInterface
     */
    public function orHasMorph($relation, $types, $operator = '>=', $count = 1);

    /**
     * Add a polymorphic relationship count / exists condition to the query.
     *
     * @param $relation
     * @param $types
     * @param string       $boolean
     * @param Closure|null $callback
     *
     * @return RepositoryInterface
     */
    public function doesntHaveMorph($relation, $types, $boolean = 'and', Closure $callback = null);

    /**
     * Add a polymorphic relationship count / exists condition to the query with an "or".
     *
     * @param $relation
     * @param $types
     *
     * @return RepositoryInterface
     */
    public function orDoesntHaveMorph($relation, $types);

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses.
     *
     * @param $relation
     * @param $types
     * @param Closure|null $callback
     * @param string       $operator
     * @param int          $count
     *
     * @return RepositoryInterface
     */
    public function whereHasMorph($relation, $types, Closure $callback = null, $operator = '>=', $count = 1);

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param $relation
     * @param $types
     * @param Closure|null $callback
     * @param string       $operator
     * @param int          $count
     *
     * @return RepositoryInterface
     */
    public function orWhereHasMorph($relation, $types, Closure $callback = null, $operator = '>=', $count = 1);

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses.
     *
     * @param $relation
     * @param $types
     * @param Closure|null $callback
     *
     * @return RepositoryInterface
     */
    public function whereDoesntHaveMorph($relation, $types, Closure $callback = null);

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param $relation
     * @param $types
     * @param Closure|null $callback
     *
     * @return RepositoryInterface
     */
    public function orWhereDoesntHaveMorph($relation, $types, Closure $callback = null);

    /**
     * Count given relation.
     *
     * @param string|array $relations
     *
     * @return RepositoryInterface
     */
    public function withCount($relations);
}
