<?php

namespace Vdhoangson\LaravelRepository\Repositories\Criteria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class OffsetCriteria.
 *
 */
class OffsetCriteria extends BaseCriteria
{
    /**
     * @var int
     */
    protected $offset;

    /**
     * OffsetCriteria constructor.
     *
     * @param int $offset
     */
    public function __construct(int $offset)
    {
        $this->offset = $offset;
    }

    /**
     * Apply criteria on entity.
     *
     * @param Model|Builder $entity
     *
     * @return Model|Builder
     */
    public function apply($entity)
    {
        return $entity->offset($this->offset);
    }
}