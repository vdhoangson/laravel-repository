<?php
/**
 * LimitCriteria
 * 
 * @package Vdhoangson\LaravelRepository\Criteria
 * @author vdhoangson <vdhoangson@gmail.com>
 * @link https://github.com/vdhoangson/laravel-repository
 */
namespace Vdhoangson\LaravelRepository\Criteria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class LimitCriteria.
 *
 */
class LimitCriteria extends BaseCriteria
{
    /**
     * @var int
     */
    protected $limit;

    /**
     * LimitCriteria constructor.
     *
     * @param int $limit
     */
    public function __construct(int $limit)
    {
        $this->limit = $limit;
    }

    /**
     * Apply criteria on entity.
     *
     * @param Model|Builder $entity
     *
     * @return Model|Builder
     *
     */
    public function apply($entity)
    {
        return $entity->limit($this->limit);
    }
}
