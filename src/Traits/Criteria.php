<?php

namespace Vdhoangson\LaravelRepository\Traits;

use Illuminate\Database\Eloquent\Collection;
use Vdhoangson\LaravelRepository\Criteria\BaseCriteria;

trait Criteria
{
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
     * Push criteria.
     *
     * @param \Vdhoangson\LaravelRepository\Criteria\BaseCriteria|string $criteria
     *
     * @return \Vdhoangson\LaravelRepository\Contracts\RepositoryInterface
     */
    public function pushCriteria($criteria): static
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
     * @return \Vdhoangson\LaravelRepository\Contracts\RepositoryInterface
     */
    public function popCriteria($criteria): static
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
     * Clear all criteria.
     *
     * @return $this
     */
    public function clearCriteria()
    {
        $this->criteria = new Collection();
        return $this;
    }

    /**
     * Apply criteria to eloquent query.
     *
     * @return \Vdhoangson\LaravelRepository\Contracts\RepositoryInterface
     */
    public function applyCriteria(): static
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
}
