<?php declare(strict_types=1);
/**
 *
 * @author apple
 * @date 2020-05-02
 */

namespace MeiquickLib\Lib\Curd\Grid;

use Hyperf\Database\Model\Builder;

class GridFactory
{
    protected $query;

    public function __construct(GridAbstract $query)
    {
        $this->query = $query;
    }

    /**
     * 设置构造
     *
     * @param Builder $builder
     */
    public function setModelBuilder(Builder $builder) : void
    {
        $this->query->setModelBuilder($builder);
    }

    /**
     * 执行
     *
     * @return Builder|\Hyperf\Database\Model\Model|object|null
     */
    public function execute()
    {
        return $this->query->execute();
    }
}
