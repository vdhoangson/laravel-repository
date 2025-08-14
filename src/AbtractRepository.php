<?php

namespace Vdhoangson\LaravelRepository;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Vdhoangson\LaravelRepository\Contracts\RepositoryInterface;
use Vdhoangson\LaravelRepository\Exceptions\RepositoryEntityException;

abstract class AbtractRepository
{
    public Container $app;

    /**
     * @var Model
     */
    protected $entity;

    protected $searchable = [];

    /**
     * BaseRepository constructor.
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->criteria = new Collection();
        $this->makeEntity();
    }

    /**
     * Get the entity class name.
     *
     * @return string
     */
    abstract public function entity();

    /**
     * Make new entity instance.
     *
     * @throws \Vdhoangson\LaravelRepository\Exceptions\RepositoryEntityException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function makeEntity()
    {
        // Make new model instance.
        $model = app()->make($this->entity());

        // Checking instance.
        if (!$model instanceof Model) {
            throw new RepositoryEntityException($this->entity());
        }

        $this->entity = $model;

        return $model;
    }

    public function __call($method, $parameters)
    {
        if (method_exists($this->entity, 'scope' . ucfirst($method))) {

            $this->entity = $this->entity->{$method}(...$parameters);

            return $this;
        }
        $this->entity = call_user_func_array([$this->entity, $method], $parameters);

        return $this;
    }

    public function __get($name)
    {
        return $this->entity->{$name};
    }

    /**
     * Get entity instance.
     *
     * @return Model|\Illuminate\Database\Eloquent\Builder
     */
    public function getEntity()
    {
        return $this->entity;
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
     * Skip using criteria.
     *
     * @param bool $skip
     *
     * @return RepositoryInterface
     */
    public function skipCriteria(bool $skip): RepositoryInterface
    {
        $this->skipCriteria = $skip;

        return $this;
    }

    /**
     * Clear criteria array.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws RepositoryEntityException
     *
     * @return RepositoryInterface
     */
    public function clearCriteria(): RepositoryInterface
    {
        $this->criteria = new Collection();
        $this->makeEntity();

        return $this;
    }
}
