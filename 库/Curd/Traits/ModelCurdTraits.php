<?php declare(strict_types=1);
/**
 * 数据库常用的操作封装
 *
 * @author apple
 * @date 2020-04-30
 */

namespace MeiquickLib\Lib\Curd\Traits;

use Hyperf\Database\Model\Builder;
use MeiquickLib\Model\Common\BaseModel;
use MeiquickLib\Lib\Curd\Form\FormFactory;
use MeiquickLib\Lib\Curd\Form\Insert;
use MeiquickLib\Lib\Curd\Form\Update;
use MeiquickLib\Lib\Curd\Grid\Find;
use MeiquickLib\Lib\Curd\Grid\GridFactory;
use MeiquickLib\Lib\Curd\Grid\Select;

trait ModelCurdTraits
{

    /*
    |---------------------------------------------------------------------------------------------------------------------------
    | 以上是使用ORM 更多是以对象方式处理数据
    |---------------------------------------------------------------------------------------------------------------------------
    */


    /**
     * 获取详情 能够在本类内支持 ORM 各个子模型都能使用
     *
     * @param array $eloquentBuilder
     * @param array $callback
     * @return Builder|\Hyperf\Database\Model\Model|object|null
     */
    protected function customOrmFind(array $eloquentBuilder = [],$callback=[])
    {
        $queryFactory = new GridFactory(new Find($this->customInstance(),$eloquentBuilder,$callback));
        $queryFactory->setModelBuilder($this->customCreateBuilder());

        return $queryFactory->execute();
    }

    /**
     * 获取列表 能够在本类内支持 ORM 各个子模型都能使用
     *
     * @param array $eloquentBuilder
     * @param array $callback
     * @return Builder|\Hyperf\Database\Model\Model|object|null
     */
    protected function customOrmSelect(array $eloquentBuilder = [],$callback=[])
    {
        $queryFactory = new GridFactory(new Select($this->customInstance(),$eloquentBuilder,$callback));
        $queryFactory->setModelBuilder($this->customCreateBuilder());

        return $queryFactory->execute();
    }

    /**
     * 新增操作 能够在本类内支持 ORM 各个子模型都能使用
     *
     * @param array $eloquentBuilder
     * @param array $callback
     * @return Builder|\Hyperf\Database\Model\Model|object|null
     */
    protected function customOrmInsert($eloquentBuilder = [],$callback=[])
    {
        $formFactory = new FormFactory(new Insert($this->customInstance(),$eloquentBuilder,$callback));
        $formFactory->setModelBuilder($this->customCreateBuilder());

        return $formFactory->execute();
    }

    /**
     * 更新操作 能够在本类内支持 ORM 各个子模型都能使用
     *
     * @param array $eloquentBuilder
     * @param array $callback
     * @return Builder|\Hyperf\Database\Model\Model|object|null
     */
    protected function ormUpdate($eloquentBuilder = [],$callback=[])
    {
        $formFactory = new FormFactory(new Update($this->customInstance(),$eloquentBuilder,$callback));
        $formFactory->setModelBuilder($this->customCreateBuilder());

        return $formFactory->execute();
    }

    /**
     * 创建model builder
     *
     * @return Builder
     */
    private function customCreateBuilder() : Builder
    {
        return $this->customInstance()->query();
    }

    /**
     * 子类model实例化
     *
     * @return BaseModel
     */
    protected function customInstance(array $parameters=[]) : BaseModel
    {
        return make(static::class,$parameters);
    }

    /**
     * 字段信息
     *
     * @return array
     */
    protected static function customFieldInfo() : array
    {
        return [];
    }

    /**
     * 字段信息
     *
     * @param string $name 字段名
     * @return array
     */
    private function customGetFieldInfo(string $name='') : array
    {
        $result=static::customFieldInfo();

        return $result[$name]??[];
    }

    /**
     * 获取 某个字段 值信息
     *
     * @param string $name 字段名
     * @param string $value 指定值
     * @return array
     */
    private function customGetFieldValueInfo(string $name,?string $value=null) : array
    {
        $fieldInfo=$this->customGetFieldInfo($name);
        $result=[];
        $rangeKey = 'range';
        if(!empty($fieldInfo[$rangeKey])){
            foreach($fieldInfo[$rangeKey] as $v1){
                if($value===null){
                    $result[]=$v1;
                }else{/*指定值*/
                    if(!empty($result)){
                        break;
                    }
                    foreach($v1 as $v2){
                        if((string)$v2===(string)$value){/*对应任一值*/
                            $result=$v1;
                            break;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * 转换 某个字段 值
     *
     * @param string $name 字段名
     * @param string $value 指定值
     * @param string $to 成为
     * @return string | int
     */
    protected function customSwitchFieldValue(string $name,?string $value,?string $to='val') : string
    {
        $info=$this->customGetFieldValueInfo($name,$value);

        return  (string)($info[$to]??'');
    }

    /**
     * 转换 某个字段 多个值
     *
     * @param string $name 字段名
     * @param array $value 指定值
     * @param string $to 成为
     * @return array
     */
    protected function customSwitchFieldMultiValue(string $name,array $value,string $to='val') : array
    {
        $result=[];
        foreach($value as $v){
            $result[]=$this->customSwitchFieldValue($name,$v,$to);
        }

        return $result;
    }

    /**
     * 数据表名
     *
     * @return string
     */
    protected function customTableName($prefix=false) : string
    {
        return ($prefix ? env('DB_PREFIX','mk_') : '').$this->customInstance()->getTable();
    }

    /**
     * 获取时间字段值
     *
     * @param null|int $timestamp 时间戳 秒
     * @return string
     */
    protected function customTimeValue(?int $timestamp=null,$format='Y-m-d H:i:s') : string
    {
        $timestamp=($timestamp!==null) ? $timestamp : time();
        return date($format,$timestamp);
    }

    /**
     * where条件 多个like
     *
     * @param Builder $builder 查询构造器
     * @param array $param 参数
     * @return Builder
     */
    final protected static function customWhereMultiLike(Builder $builder,array $param=[]): Builder
    {
        $param['boolean']=array_key_exists('boolean',$param) ? $param['boolean'] : 'and';
        $builder->where(function(Builder $builder) use($param){
            foreach($param['where'] as $v){
                static::customWhereLike($builder,[
                    'boolean'=>$param['boolean'],
                    'field'=>$v['field'],
                    'value'=>$v['value'],
                ]);
            }
        });
        return $builder;
    }

    /**
     * where条件 单个like
     *
     * @param Builder $builder 查询构造器
     * @param array $param 参数
     * @return Builder
     */
    final protected static function customWhereLike(Builder $builder, array $param=[]): Builder
    {
        $param['boolean']=array_key_exists('boolean',$param) ? $param['boolean'] : 'and';
        $sql='INSTR('.$param['field'].',?)>0';
        $bindings=[($param['value'])];
        $builder->whereRaw($sql,$bindings,$param['boolean']);
        return $builder;
    }

}
