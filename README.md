# Laravel Repository
Repository pattern package for Laravel framework.

![GitHub tag (latest by date)](https://img.shields.io/github/tag-date/vdhoangson/laravel-repository?label=Version)
![GitHub](https://img.shields.io/github/license/vdhoangson/laravel-repository?label=License)
![Packagist](https://img.shields.io/packagist/dt/vdhoangson/laravel-repository?label=Downloads)
![PHP from Packagist](https://img.shields.io/packagist/php-v/vdhoangson/laravel-repository?label=PHP)
[![StyleCI](https://github.styleci.io/repos/212760382/shield?branch=master)](https://github.styleci.io/repos/212760382)
[![Build Status](https://travis-ci.com/vdhoangson/laravel-repository.svg?branch=master)](https://travis-ci.com/vdhoangson/laravel-repository)

### Version compatibility
#### Laravel
Framework | Package | Note
:---------|:--------|:-----
6.0.x     | ^1.x.x  | PHP ^8.0 Supported.
7.x.x     | ^1.x.x  | PHP ^8.0 Supported.
8.x.x     | ^1.x.x  | PHP ^8.0 Supported.
9.x.x     | ^1.x.x  | PHP ^8.0 Supported.

## Installation
You can install this package by composer:

    composer require vdhoangson/laravel-repository
    
For more configuration, you can publish configuration file:
    
    php artisan vendor:publish --provider "Vdhoangson\LaravelRepository\Providers\LaravelRepositoryServiceProvider"

### Implementation
To use Repositories, create repository class that:
- Extend BaseRepository class
- Implements interface that extend BaseInterface

For example, this is implementation of repository for Example entity:

ExampleRepositoryInterface:
```php
interface ExampleRepositoryInterface extends BaseInterface
{}
```

ExampleRepository class. This class has to implement entity() method, that return namespace of entity
that will be used by repository.
```php
class ExampleRepository extends BaseRepository implements ExampleRepositoryInterface
{
    /**
     * Model entity class that will be use in repository
     *
     * @return BaseInterface
     */
    public function entity(): string
    {
        return Example::class;
    }

}
```

### Using repositories
To use Repository in controller or other class you can use dependency injection or Container. Below is sample code of using service in controller.
```php
class ExampleController extends Controller {

    /**
     * @var ExampleRepositoryInterface $exampleRepository
     */
    protected $exampleRepository;

    public function __construct(ExampleRepositorynterface $exampleRepository){
        $this->exampleRepository = $exampleRepository;
    }

    ....
}
```
#### Available methods
* makeEntity() - make new entity instance
* getEntity() - return previously set entity instance
* setEntity() - set entity instance
* pushCriteria() - push new criteria to use in query (passed class must be implementation of BaseCriteria)
* popCriteria() - delete given criteria from use (if exist)
* getCriteria() - return collection of actualy set criteria
* applyCriteria() - apply criteria to use in query
* skipCriteria() - skip criteria in query
* clearCriteria() - clear criteria colleciton - delete all pushed criterias
* all(array $columns) - get all records
* get(array $columns) - get records (with criteria)
* first(array $columns) - get first record (with criteria)
* create(array $parameters) - create new entity record
* updateOrCreate(array $where, array $values) - update existing record, or create new
* update(int $id, array $parameters) - update entity by ID
* delete(int $id) - delete entity record by ID
* firstOrNew(array $where) - return first entity record if found, otherwise return new entity
* orderBy(string $column, string $direction) - order records by column
* with($relations) - add relations sub-query
* transactionBegin() - begin database transaction
* transactionCommit() - commit transaction
* transactionRollback() - rollback transaction
* findWhere(array $where, array $columns) - return all records that match where array
* findWhereIn(string $column, array $where, array $columns)
* findWhereNotIn(string $column, array $where, array $columns)
* chunk(int $limit, callable $callback, array $columns) - chunk query results
* count(array $columns) - count results
* paginate($perPage, $columns, $pageName, $page) - paginate results
* simplePaginate($perPage, $columns, $pageName, $page) - paginate results
* has($relation, $operator, $count, $bolean, $callback) - where has relation
* orHas($relation, $operator, $count) - or where has relation
* whereHas($relation, $callback, $operator, $count)
* orWhereHas($relation, $callback, $operator, $count)
* whereDoesntHave($relation, $callback)
* orWhereDoesntHave($relation, $callback)
* withCount($relations)
* doesntHave($relation, $boolean, $callback)
* orDoesntHave($relation)
* hasMorph($relation, $types, $operator, $count, $boolean, $callback)
* orHasMorph($relation, $types, $operator, $count)
* doesntHaveMorph($relation, $types, $boolean, $callback)
* orDoesntHaveMorph($relation, $types)
* whereHasMorph($relation, $types, $callback, $operator, $count)
* orWhereHasMorph($relation, $types, $callback, $operator, $count)
* whereDoesntHaveMorph($relation, $types, $callback)
* orWhereDoesntHaveMorph($relation, $types, $callback)
* sum($column)

#### Caching
___
Information: In order to use Caching feature in repository, you must use cache driver that
support tags. Actually "file" and "database" drivers are not supported. 
    
More information in [in laravel documentation](https://laravel.com/docs/9.x/cache#cache-tags).
____

You can user criteria with this functions, and results will be cached.

Repository automatically flush cache, when method create(), updateOrCreate(), update(),
delete() is call.

##### Tag by user ID
By default repository cache adding actual authenticated user ID as tag. That provide
possibility to separate cached data among users. That feature is useful for entities
strictly associated to User (i.e. Account operation, Account details), when cached
data will be flushed for each user separately - not for all repository, with save
resources.

But for other entities (.i.e. Articles in CMS system), this solution can be annoying,
so to disable this feature for selected repository call skipUserTag() method in __construct(). Example:
```php
class ExampleRepository extends BaseRepository implements ExampleRepositoryInterface
{
    
    use WithCache;

    /**
     * ExampleRepository constructor.
     *
     * @param Container $app
     * @throws RepositoryEntityException
     * @throws BindingResolutionException
     */
    public function __construct(Container $app)
    {
        parent::__construct($app);
        $this->skipUserTag();
    }

}
```

In some situation (i.e. in Jobs or Commands) you may want to manually update user data, and flush tagged cache for him.
You can use setUserTag () function to manually set user ID for cache tag. To clear it use clearUserTag() function.

##### Skipping cache
To force fetch data from database, skipping cached data, use skipCache() method. Example:

```php
$this-exampleService->getRepository->skipCache()->findWhere(...)
```

##### Disable cache
To quick disable cache i.ex for debugging, set REPOSITORY_CACHE variable to false in .env
```dotenv
REPOSITORY_CACHE=false
```

## Changelog

Go to the [Changelog](CHANGELOG.md) for a full change history of the package.

## License

This package is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
