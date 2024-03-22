<?php declare(strict_types=1);
/**
 *
 * @author apple
 * @date 2020-05-02
 */

namespace MeiquickLib\Lib\Curd\Grid;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\SoftDeletingScope;
use Hyperf\Utils\Context;
use MeiquickLib\Lib\Utils\Arr;
use MeiquickLib\Lib\Utils\Str;
use MeiquickLib\Lib\Curd\Column\Columns;
use MeiquickLib\Model\Common\BaseModel;
use Psr\Http\Message\ServerRequestInterface;

/**
 * 查询抽象
 *
 * Class GridAbstract
 * @package MeiquickLib\Lib\Curd\Grid
 */
abstract class GridAbstract
{
    /**
     * @var Builder
     */
    protected $modelBuilder;/*查询构造*/
    protected $model;/*查询model*/
    protected $eloquentBuilder;/*查询条件*/
    protected $callback;/*自定义回调*/
    protected $isJoin = false;/*是否连表*/
    protected $serverRequest=null;/*服务请求数据*/

    public function __construct(BaseModel $model,array $eloquentBuilder = [],$callback=[])
    {
        $this->eloquentBuilder = $eloquentBuilder;
        $this->callback = $callback;
        $this->model = $model;
        $serverContext = Context::get(ServerRequestInterface::class);
        if($serverContext !== null) $this->serverRequest = $serverContext->getParsedBody()[0]??[];

        $this->columns = new Columns($model);
    }

    /**
     * 排序
     *
     * @param string $column
     * @param string $direction
     */
    protected function orderBy(string $column, $direction = 'desc') : void
    {
        $this->modelBuilder->orderBy($column, $direction);
    }

    /**
     * 正序
     * @param string $column
     */
    protected function orderByAsc(string $column) : void
    {
        $this->modelBuilder->orderBy($column, 'asc');
    }

    /**
     * 倒序
     * @param string $column
     */
    protected function orderByDesc(string $column) : void
    {
        $this->modelBuilder->orderByDesc($column);
    }

    /**
     * 自定义多个排序
     *
     * @param array $param 参数
     */
    final protected function customMultiOrderBy(array $param=[]) : void
    {
        foreach($param as $v){
            $this->modelBuilder->orderBy(...$v);
        }
    }

    protected function with(array $withs = [], $cusQuery=null) : void
    {
        foreach ($withs as $relation => $withParams) {
            ($cusQuery ?: $this->modelBuilder)->with([$relation => function ($query) use ($withParams) {
                !empty($withParams['columns']) && $query->select($withParams['columns']);
                !empty($withParams['condition']) && $this->condition($withParams['condition'], $query);
                !empty($withParams['condition_in']) && $this->conditionIn($withParams['condition_in'], $query);
                !empty($withParams['condition_not_in']) && $this->conditionNotIn($withParams['condition_in'], $query);
                !empty($withParams['select_raw']) && $query->selectRaw($withParams['select_raw'], []);
                if (!empty($withParams['with'])) {
                    $this->with($withParams['with'], $query);
                }
            }]);
        }
    }

    /**
     * 获取当前实例
     *
     * @return BaseModel
     */
    protected function getInstance()
    {
        return $this->model;
    }

    /**
     * 查询字段
     *
     * @param array $columns
     */
    protected function columns(array $columns=['*']) : void
    {
        $this->modelBuilder->select($this->columns->customFilterColumns($columns));
    }

    /**
     * 原生查询字段
     *
     * @param string $expression
     * @param array $bindings
     */
    protected function selectRaw(string $expression='',array $bindings = []) : void
    {
        $this->modelBuilder->selectRaw($expression,$bindings);
    }

    /**
     * 查询条件 where
     *
     * @param $condition
     */
    protected function condition(array $condition ,&$builder = null) : void
    {
        if ($builder) {
            !empty($condition) && Arr::isDimensionalArray($condition) ? $builder->where(...$condition) : $builder->where($condition);
        } else {
            !empty($condition) && Arr::isDimensionalArray($condition) ? $this->modelBuilder->where(...$condition) : $this->modelBuilder->where($condition);
        }
    }

    /**
     * 查询条件 whereIn
     *
     * @param array $condition
     */
    protected function conditionIn(array $condition=[],&$builder = null) : void
    {
        if ($builder) {
            !empty($condition) && $builder->whereIn(...$condition);
        } else {
            !empty($condition) && $this->modelBuilder->whereIn(...$condition);
        }
    }

    /**
     * 查询条件 whereIn
     *
     * @param array $condition
     */
    protected function conditionIns(array $condition=[],&$builder = null) : void
    {
        if ($builder) {
            foreach($condition as $cond){
                !empty($condition) && $builder->whereIn(...$cond);
            }
        } else {
            foreach($condition as $cond){
                !empty($condition) && $this->modelBuilder->whereIn(...$cond);
            }
        }
    }

    /**
     * 查询条件 orwhere
     *
     * @param array $condition
     */
    protected function conditionOr(array $condition=[]) : void
    {
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

    /**
     * 查询条件 whereNotIn
     *
     * @param array $condition
     */
    protected function conditionNotIn(array $condition=[],&$builder = null) : void
    {
        if($builder){
            !empty($condition) && $builder->whereNotIn(...$condition);
        }else{
            !empty($condition) && $this->modelBuilder->whereNotIn(...$condition);
        }
    }

    /**
     * 设置构造
     *
     * @param Builder $builder
     */
    public function setModelBuilder(Builder $builder) : void
    {
        $this->modelBuilder = $builder;
    }

    /**
     * 获取软删除条目
     *
     * @param bool $bool
     */
    public function softDeleted($bool = true)
    {
        if($bool === false)  $this->modelBuilder->withoutGlobalScope(SoftDeletingScope::class);
    }

    /**
     * 查询单个条目
     *
     * @return Builder|\Hyperf\Database\Model\Model|object|null
     */
    abstract public function execute();

    /**
     * 解析回调函数
     *
     * @param array $eloquentBuilder
     */
    protected function analysisCall() : void
    {
        $eloquentBuilder = $this->eloquentBuilder;
        foreach ($eloquentBuilder as $method=>$parameters){
            if(method_exists($this,$method)){
                $this->{$method}($parameters);
            }else{
                $method = Str::camelizeToUpperWord($method);
                if(method_exists($this,$method)){
                    $this->{$method}($parameters);
                }
            }
        }
    }

    /**
     * 自定义回调查询
     *
     * @return Builder|null
     */
    protected function customCallback() : ?Builder
    {
        $callback = $this->callback;
        if(!empty($callback)){
            if(is_array($callback)){
                list($callbackMethodName,$parameters) = [key($callback),end($callback)];
                if(is_string($callbackMethodName) && method_exists($this->getInstance(),$callbackMethodName)){
                    return $this->callbackFunc($callbackMethodName,$parameters);
                }
            }
            if(is_callable($callback)){
                $callback($supplement = new GridSupplement());
                $callbackMethodName = $supplement->getMethod();
                $parameters = $supplement->getParameters();
                if(is_string($callbackMethodName) && method_exists($this->getInstance(),$callbackMethodName)){
                    return $this->callbackFunc($callbackMethodName,$parameters);
                }
            }
        }

        return null;
    }

    /**
     * 回调instance  目前只支持builder能传引用
     *
     * @param $callback
     * @param $params
     * @return Builder
     */
    protected function callbackFunc($callback,$params) : ?Builder
    {
        $builder = $this->modelBuilder;
        $instance = $this->getInstance();
        $builder = $builder === null ? $instance : $builder;
        is_string($callback) && method_exists($instance,$callback) && $builder = call_user_func_array([$instance,$callback],[&$builder,&$params]);

        return $builder;
    }

    /**
     * 多个callback
     *
     * @param array $callback
     */
    final protected function customMultiCallback(array $callback=[])
    {
        foreach($callback as $k=>$v){
            $this->callbackFunc($k,$v);
        }
    }

    /**
     * 拼接表名
     */
    protected function splicingTableName($field, string $tableName)
    {
        if(is_array($field)) {
            foreach($field as $k => &$v) {
                if(is_array($v)) {
                    foreach($v as  &$val) {
                        $this->checkIsAlreadyAddPrefix($val) or $val = $tableName.'.'.$val;
                        break;
                    }
                } else {
                    $this->checkIsAlreadyAddPrefix($v) or $field[$k] = $tableName.'.'.$v;
                    break;
                }
            }
        } else {
            $this->checkIsAlreadyAddPrefix($field) or $field = $tableName.'.'.$field;
        }
        return $field;
    }

    private function checkIsAlreadyAddPrefix(string $field) : bool
    {
        return count(explode('.', $field)) > 1;
    }

}
