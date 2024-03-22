<?php declare(strict_types=1);
/**
 *
 * @author apple
 * @date 2020-05-02
 */

namespace MeiquickLib\Lib\Curd\Grid;

use Hyperf\Database\Model\Builder;
use MeiquickLib\Lib\Curd\Column\Columns;

/**
 * @property columns|select_raw|condition|condition_in #能使用的属性值
 *
 * Class Find
 * @package MeiquickLib\Lib\Curd\Grid
 */
class Find extends GridAbstract
{
    /**
     * 查询单个条目
     *
     * @return Builder|\Hyperf\Database\Model\Model|object|null
     */
    public function execute()
    {
        $this->analysisCall();

        $this->customCallback();

        return $this->modelBuilder->first();
    }

    /**
     * 查询条件 orWhere
     * @param array $condition
     * @param mixed|null $builder
     */
    protected function conditionOr(array $condition = [], &$builder = null): void
    {
        if ($builder) {
            !empty($condition) && $builder->where(function($query)use ($condition){
                $query->orWhere(...$condition);
            });
        } else {
            if ($this->isJoin) $condition = $this->splicingTableName($condition, $this->model->getTable());
            !empty($condition) && $this->modelBuilder->where(function($query)use ($condition){
                foreach ($condition as $k => $cond) {
                    if (!$k) {
                        $query->where(...$cond);
                    } else {
                        $query->orWhere(...$cond);
                    }
                }
            });
        }
    }
}
