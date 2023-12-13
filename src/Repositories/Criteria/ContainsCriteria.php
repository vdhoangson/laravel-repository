<?php
/**
 * ContainsCriteria
 * 
 * @package Vdhoangson\LaravelRepository\Repositories\Criteria
 * @author vdhoangson <vdhoangson@gmail.com>
 * @link https://github.com/vdhoangson/laravel-repository
 */
namespace Vdhoangson\LaravelRepository\Repositories\Criteria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ContainsCriteria.
 *
 */
class ContainsCriteria extends BaseCriteria
{
    /**
     * @var string
     */
    protected string $column;

    /**
     * @var string
     */
    protected string $search;

    /**
     * ContainsCriteria constructor.
     *
     * @param string $column;
     * @param string $search;
     */
    public function __construct(string $column, string $search)
    {
        $this->column = $column;
        $this->search = $search;
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
        return $entity->where($this->column, 'like', '%' . $this->search . '%');
    }
}