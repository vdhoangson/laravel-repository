<?php

namespace Vdhoangson\LaravelRepository\Repositories\Interfaces;

use Closure;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Vdhoangson\LaravelRepository\Repositories\Exceptions\RepositoryEntityException;
use Vdhoangson\LaravelRepository\Repositories\Criteria\Interfaces\BaseCriteriaInterface;

/**
 * Interface BaseInterface.
 * */
interface BaseInterface
{
    /**
     * Model entity class that will be use in repository.
     *
     * @return BaseInterface
     */
    public function entity(): string;

    /**
     * Make new entity instance.
     *
     * @throws RepositoryEntityException
     * @throws BindingResolutionException
     *
     * @return BaseInterface
     */
    public function makeEntity(): self;

    /**
     * Get entity instance.
     *
     * @return Model|Builder
     */
    public function getEntity();

    /**
     * Set entity instance.
     *
     * @param Model|Builder $entity
     *
     * @return BaseInterface
     */
    public function setEntity($entity): self;

    /**
     * Push criteria.
     *
     * @param BaseCriteriaInterface $criteria
     *
     * @return BaseInterface
     */
    public function pushCriteria(BaseCriteriaInterface $criteria): self;

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
    public function popCriteria(string $criteriaNamespace): self;

    /**
     * Get criteria.
     *
     * @return Collection|null
     */
    public function getCriteria(): Collection|null;

    /**
     * Apply criteria to eloquent query.
     *
     * @return BaseInterface
     */
    public function applyCriteria(): self;

    /**
     * Skip using criteria.
     *
     * @param bool $skip
     *
     * @return BaseInterface
     */
    public function skipCriteria(bool $skip): self;

    /**
     * Clear criteria array.
     *
     * @throws BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return BaseInterface
     */
    public function clearCriteria(): self;

    /**
     * Query Scope
     *
     * @param Closure $scope
     *
     * @return $this
     */
    public function scopeQuery(Closure $scope): self;

    /**
     * Apply scope in current Query
     *
     * @return $this
     */
    public function applyScope(): self;

    /**
     * Reset Query Scope
     *
     * @return $this
     */
    public function resetScope(): self;

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
    public function all(array $columns = ['*']): Collection;

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
    public function get(array $columns = ['*']): Collection;

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
    public function first(array $columns = ['*']);

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
     * @return BaseInterface
     */
    public function delete(int $id): self;

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
     * @return BaseInterface
     */
    public function orderBy(string $column, string $direction = 'asc'): self;

    /**
     * Relation sub-query.
     *
     * @param array|string $relations
     *
     * @return BaseInterface
     */
    public function with(array|string $relations): self;

    /**
     * Begin database transaction.
     *
     * @return BaseInterface
     */
    public function transactionBegin(): self;

    /**
     * Commit database transaction.
     *
     * @return BaseInterface
     */
    public function transactionCommit(): self;

    /**
     * Rollback transaction.
     *
     * @return BaseInterface
     */
    public function transactionRollback(): self;

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
    public function findById(int $id, array $columns = ['*']);

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
    public function findWhere(array $where, array $columns = ['*']): Collection|array;

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
    public function findWhereIn(string $column, array $where, array $columns = ['*']): Collection|array;

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
    public function findWhereNotIn(string $column, array $where, array $columns = ['*']): Collection|array;

    /**
     * Find data by field and value
     *
     * @param       $field
     * @param       $value
     * @param array $columns
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
    public function chunk(int $limit, callable $callback, array $columns = ['*']): bool;

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
    public function count($columns = '*'): int;

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
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);

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
     * @return BaseInterface
     */
    public function withTrashed(): self;

    /**
     * Get only trashed entities.
     *
     * @return BaseInterface
     */
    public function onlyTrashed(): self;

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
    public function has($relation, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null): self;

    /**
     * Or hase relation.
     *
     * @param string $relation
     * @param string $operator
     * @param int    $count
     *
     * @return BaseInterface
     */
    public function orHas($relation, $operator = '>=', $count = 1): self;

    /**
     * Add a relationship count / exists condition to the query.
     *
     * @param $relation
     * @param string       $boolean
     * @param Closure|null $callback
     *
     * @return BaseInterface
     */
    public function doesntHave($relation, $boolean = 'and', Closure $callback = null): self;

    /**
     * Add a relationship count / exists condition to the query with an "or".
     *
     * @param $relation
     *
     * @return BaseInterface
     */
    public function orDoesntHave($relation): self;

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
    public function whereHas($relation, Closure $callback = null, $operator = '>=', $count = 1): self;

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
    public function orWhereHas($relation, Closure $callback = null, $operator = '>=', $count = 1): self;

    /**
     * Where doesnt have relation.
     *
     * @param string       $relation
     * @param Closure|null $callback
     *
     * @return BaseInterface
     */
    public function whereDoesntHave($relation, Closure $callback = null): self;

    /**
     * Or where doesnt have relation.
     *
     * @param string       $relation
     * @param Closure|null $callback
     *
     * @return BaseInterface
     */
    public function orWhereDoesntHave($relation, Closure $callback = null): self;

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
    public function hasMorph($relation, $types, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null): self;

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
    public function orHasMorph($relation, $types, $operator = '>=', $count = 1): self;

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
    public function doesntHaveMorph($relation, $types, $boolean = 'and', Closure $callback = null): self;

    /**
     * Add a polymorphic relationship count / exists condition to the query with an "or".
     *
     * @param $relation
     * @param $types
     *
     * @return BaseInterface
     */
    public function orDoesntHaveMorph($relation, $types): self;

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
    public function whereHasMorph($relation, $types, Closure $callback = null, $operator = '>=', $count = 1): self;

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
    public function orWhereHasMorph($relation, $types, Closure $callback = null, $operator = '>=', $count = 1): self;

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses.
     *
     * @param $relation
     * @param $types
     * @param Closure|null $callback
     *
     * @return BaseInterface
     */
    public function whereDoesntHaveMorph($relation, $types, Closure $callback = null): self;

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param $relation
     * @param $types
     * @param Closure|null $callback
     *
     * @return BaseInterface
     */
    public function orWhereDoesntHaveMorph($relation, $types, Closure $callback = null): self;

    /**
     * Count given relation.
     *
     * @param string|array $relations
     *
     * @return BaseInterface
     */
    public function withCount($relations): self;
}
