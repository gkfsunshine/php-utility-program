<?php
namespace App\Utils;

use App\Helpers\SingletonInstance;
use App\Utils\Arr;
use App\Utils\Time;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 自定义curd
 *
 * Trait CurdTrait
 * @package App\Helpers\Utils
 */
trait CurdTrait
{
    protected static $_customDeletedFiled='deleted_at';/*软删除时间字段*/
    protected static $_customCreatedFiled='created_at';/*新增时间字段*/
    protected static $_customUpdatedFiled='updated_at';/*更新时间字段*/
    protected static $_customOperationIdFiled=null;/*操作人id字段*/
    protected static $_customOperationNameField=null;/*操作人姓名*/

    /**
     * 自定义新增
     *
     * @param array $params
     * @return array
     */
    public final static function customSimpleInsert($params=[])
    {
        $params['db_operate']='insert';
        return static::customSimpleSave($params);
    }

    /**
     * 自定义新增获取id
     *
     * @param array $params
     * @return array
     */
    public final static function customSimpleInsertGetId($params=[])
    {
        $params['db_operate']='insert_get_id';
        return static::customSimpleSave($params);
    }

    /**
     * 自定义更新
     *
     * @param array $params
     * @return array
     */
    public final static function customSimpleUpdate($params=[])
    {
        $params['db_operate']='update';
        return static::customSimpleSave($params);
    }

    /**
     * 自定义软删除
     *
     * @return array
     */
    public final static function customSimpleSoftDeleted($params=[])
    {
        $params['db_operate']='soft_deleted';
        return static::customSimpleSave($params);
    }

    /**
     * 自定义真删除
     *
     * @return array
     */
    public final static function customSimpleRealDeleted($params=[])
    {
        $params['db_operate']='real_deleted';
        return static::customSimpleSave($params);
    }

    /**
     * 查询多条
     *
     * @param array $params
     * @return array[]
     */
    public final static function customSimpleSelect($params=[])
    {
        $params['db_operate']='select';
        return static::customSimpleQuery($params);
    }

    /**
     * 查询单条
     *
     * @return array[]
     */
    public final static function customSimpleFind($params=[])
    {
        $params['db_operate']='find';
        return static::customSimpleQuery($params);
    }

    /**
     * 查询单条
     *
     * @return array[]
     */
    public final static function customSimpleCount($params=[])
    {
        $params['db_operate']='count';
        return static::customSimpleQuery($params);
    }

    /**
     * 自定查询
     *
     * @param array $params
     * @return array[]
     */
    private final static function customSimpleQuery($params=[])
    {
        $params['select']=array_key_exists('select',$params) ? $params['select'] : '';/*查询字段*/
        $params['db_operate']=array_key_exists('db_operate',$params) ? strtolower($params['db_operate']) : '';/*数据操作*/
        $params['where']=array_key_exists('where',$params) ? $params['where'] : null;/*where查询条件*/
        $params['custom_query_build']=array_key_exists('custom_query_build',$params) ? $params['custom_query_build'] : null;/*自定义回调查询*/
        $params['need_soft_delete_data']=array_key_exists('need_soft_delete_data',$params) ? $params['need_soft_delete_data'] : false;/*是否获取软删除数据*/
        $params['group_by']=array_key_exists('group_by',$params) ? $params['group_by'] : [];/*分组*/
        $params['having']=array_key_exists('having',$params) ? $params['having'] : [];/*having*/
        $params['order_by']=array_key_exists('order_by',$params) ? $params['order_by'] : '';/*排序*/
        $params['need_paginator_info']=array_key_exists('need_paginator_info',$params) ? $params['need_paginator_info'] : false;/*是否分页*/
        $result=[
            'data'=>[],
        ];
        $query=static::customSimpleQueryBuilder();/*DB 模式*/

        if(!empty($params['select'])){
            is_array($params['select']) ? $query->select($params['select']) :  $query->selectRaw($params['select']);
        }
        if(!empty($params['where'])){
            static::customSimpleStandardQuery($query,$params);/*自定义where*/
        }
        if(!empty($params['custom_query_build'])){
            static::customSimpleQueryBuilderCallback($params['custom_query_build'],$params,$query);/*自定义回调构造*/
        }
        if(!empty($params['group_by'])){/*group by*/
            $query->groupBy($params['group_by']);
        }
        if(!empty($params['having'])){/*having*/
            $query->having(...$params['having']);
        }
        if(!empty($params['offset'])){/*offset*/
            $query->offset($params['offset']);
        }
        if(!empty($params['limit'])){/*offset*/
            $query->limit($params['limit']);
        }
        if(!empty($params['order_by'])){/*order by*/
            if(!\App\Utils\Arr::isDimensionalArr($params['order_by'])){
                foreach ($params['order_by'] as $v){
                    $query->orderBy(...$v);
                }
            }else{
                $query->orderBy(...$params['order_by']);
            }
        }
        if(!$params['need_soft_delete_data']&& static::$_customDeletedFiled){/*软删除数据*/
            if(!empty(static::$_customDeletedFiled)) $query->whereNull(static::$_customDeletedFiled);
        }
        switch (strtolower($params['db_operate'])){
            case 'find':/*单条查询*/
                $result['data']=Arr::objToArr($query->first());
                break;
            case 'select':/*多条查询*/
                if($params['need_paginator_info']){
                    $result['data']=static::customSimplePaginatorInfo($query,$params);
                }else{
                    $result['data']=Arr::objToArr($query->get());
                }
                break;
            case 'count':
                $result['data']=$query->count();
                break;
            default:
                return $result['data'];
        }

        return $result['data'];
    }

    /**
     * 自定义操作
     *
     * @param array $params
     * @return array
     */
    private final static function customSimpleSave($params=[])
    {
        $params['db_operate']=array_key_exists('db_operate',$params) ? strtolower($params['db_operate']) : '';/*数据操作*/
        $params['data']=array_key_exists('data',$params) ? $params['data'] : [];/*要保存的数据*/
        $params['where']=array_key_exists('where',$params) ? $params['where'] : null;/*where查询条件*/
        $params['custom_query_build']=array_key_exists('custom_query_build',$params) ? $params['custom_query_build'] : null;/*自定义回调查询*/
        $result=[
            'save'=>$data['save']??null,
            'code'=>$data['code']??'',
            'message'=>$data['message']??'',
            'details'=>[
                'insert_get_id'=>0
            ]
        ];
        $needData=in_array($params['db_operate'],[/*是否需要data*/
            'insert',/*insert*/
            'update',/*update*/
            'insert_get_id'/*insertGetId*/
        ]);
        $needWhere=in_array($params['db_operate'],[/*是否需要where 不支持全量更新*/
            'update',/*update*/
            'delete',/*软delete*/
            'real_delete',/*真delete*/
        ]);
        if($needData&& empty($params['data'])){
            $result['code']='need data';
            $result['message']='操作数据不允许为空';
            return $result;
        }
        if($needWhere&& !($params['where']
                || $params['custom_query_build']
            )){
            $result['code']='need where';
            $result['message']='需要查询构造 where';
            return $result;
        }
        $isDataDimensionalArr=Arr::isDimensionalArr($params['data']);
        if($isDataDimensionalArr){/*一维数据*/
            $params['data']=[$params['data']];
        }
        if(!empty($params['data'])){
            $params['data']=static::customSimpleInitSaveData($params);
        }
        $params['data']=$isDataDimensionalArr ? $params['data'][0] : $params['data'];/*重置数据方便插入*/
        $query=static::customSimpleQueryBuilder();/*DB 模式*/
        if(!empty($params['where'])){
            static::customSimpleStandardQuery($query,$params);/*自定义where*/
        }
        if(!empty($params['custom_query_build'])){
            static::customSimpleQueryBuilderCallback($params['custom_query_build'],$params,$query);/*自定义回调构造*/
        }
        switch (strtolower($params['db_operate'])){
            case 'insert':/*新增*/
                $result['save']=$query->insert($params['data']);
                break;
            case 'insert_get_id':/*新增去自增id*/
                $save=$query->insertGetId($params['data']);
                $result['save']=$save;
                $result['details']['insert_get_id']=$save;
                break;
            case 'update':/*更新*/
            case 'soft_deleted':/*软删除*/
                $result['save']=$query->update($params['data']);
                break;
            case 'real_deleted':/*真删除*/
                $result['save']=$query->delete();
                break;
            default:
                return $result([
                    'code'=>'['.$params['db_operate'].'] not found',
                    'message'=>'['.$params['db_operate'].'方法不存在'
                ]);
        }
        $result['code']='success';
        $result['message']='['.$params['db_operate'].']成功';

        return $result;
    }

    /**
     * 初始数据
     *
     * @param array $params
     * @return array|mixed
     */
    private final static function customSimpleInitSaveData($params=[])
    {
        $data=array_key_exists('data',$params) ? $params['data'] : [];
        $dbOperate=array_key_exists('db_operate',$params) ? $params['db_operate'] : '';
        $time=Time::customTimeDate();
        $operationId=gerUserId();
        $operationName=getUserName();
        foreach ($data as $k=>$v){
            if(static::$_customOperationIdFiled!==null&& $operationId>0){/*保存操作员id*/
                $data[$k][(static::$_customOperationIdFiled)]=$operationId;
            }
            if(static::$_customOperationNameField!==null&& $operationName){/*保存操作员名称*/
                $data[$k][(static::$_customOperationNameField)]=$operationName;
            }
            if(static::$_customUpdatedFiled!==null){/*保存更新时间字段*/
                $data[$k][(static::$_customUpdatedFiled)]=$time;
            }
            switch (strtolower($dbOperate)){
                case 'insert':/*新增*/
                case 'insert_get_id':/*新增去自增id*/
                    $data[$k][(static::$_customCreatedFiled)]=$time;/*保存新增时间字段*/
                    break;
                case 'soft_deleted':/*软删除*/
                    $data[$k][(static::$_customDeletedFiled)]=$time;/*保存删除时间字段*/
                    break;
            }
        }

        return $data;
    }

    /**
     * 常用查询
     *
     * @param Builder $query
     * @param array $params
     * @return Builder
     */
    private final static function customSimpleStandardQuery(Builder &$query,$params=[])
    {
        $conditionWhereFunc=function($data,$condition='where') use ($query){
            $isDimensionalArr=Arr::isDimensionalArr($data);
            if($isDimensionalArr){
                $query->$condition(...$data);
            }else{
                foreach ($data as $k=>$v){
                    $query->$condition(...$v);
                }
            }
        };
        $params['where']=array_key_exists('where',$params) ? $params['where'] : [];/*where查询条件*/
        if(!empty($params['where'])){
            foreach ($params['where'] as $k=>$v){
                if(!empty($v)){
                    switch (strtolower($k)){/*封装常用即可*/
                        case 'condition':/*and 多条件*/
                            $conditionWhereFunc($v,'where');
                            break;
                        case 'condition_or':/*or 多条件*/
                            $conditionWhereFunc($v,'orWhere');
                            break;
                        case 'condition_in':/*and in多条件*/
                            $conditionWhereFunc($v,'whereIn');
                            break;
                        case 'condition_not_in':/*and not in多条件*/
                            $conditionWhereFunc($v,'whereNotIn');
                            break;
                    }
                }
            }
        }

        return $query;
    }

    protected static function customSimpleFieldInfo(){}/*自定义value*/

    /**
     * 获取字段值
     *
     * @param string $name
     * @return array|mixed\
     */
    public final static function customSimpleGetFieldInfo($name=''){
        $result=static::customSimpleFieldInfo();
        if($result==null){
            $result=static::customFieldInfo();
        }
        return $result[$name]??[];
    }

    /**
     * 转换 某个字段 值
     *
     * @param string $name 字段名
     * @param string $value 指定值
     * @param string $to 成为
     * @return string | int
     */
    public final static function customSimpleSwitchFieldValue($name,$value='',$to='en')
    {
        $info=static::customSimpleGetFieldValueInfo($name,$value);
        return ($info && array_key_exists($to,$info)) ? $info[$to] : '';
    }

    /**
     * 转换 某个字段 多个值
     *
     * @param string $name 字段名
     * @param array $value 指定值
     * @param string $to 成为
     * @return array
     */
    public final static function customSimpleSwitchFieldMultiValue($name,$value=[],$to='en')
    {
        $result=[];
        foreach($value as $v){
            $result[]=static::customSimpleSwitchFieldValue($name,$v,$to);
        }
        return array_unique($result);
    }

    /**
     * 获取 某个字段 值信息
     *
     * @param string $name 字段名
     * @param string $value 指定值
     * @return array
     */
    public final static function customSimpleGetFieldValueInfo($name,$value=null)
    {
        $fieldInfo=static::customSimpleGetFieldInfo($name);
        $result=[];
        foreach($fieldInfo['range'] as $v1){
            if($value===null){
                $result[]=$v1;
            }else{/*指定值*/
                foreach($v1 as $v2){
                    if((string)$v2===(string)$value){/*对应任一值*/
                        $result=$v1;
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 获取DB实例
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private final static function customSimpleQueryBuilder()
    {
        return \DB::table(static::customSimpleTableName());
    }

    /**
     * 表名
     *
     * @param string $hasPrefix
     * @return string
     */
    public final static function customSimpleTableName($hasPrefix='')
    {
        return ($hasPrefix ? static::customSimpleConnectInstance()->getTablePrefix() : '').SingletonInstance::getInstance(static::class)->getTable();
    }

    /**
     * model 实例链接数据库
     *
     * @return \Illuminate\Database\Connection
     */
    private final static function customSimpleConnectInstance()
    {
        return SingletonInstance::getInstance(static::class)->getConnection();
    }

    /**
     * 回调instance
     *
     * @param $callback
     * @param $params
     * @param null $builder
     * @return false|\Illuminate\Database\Connection|mixed
     */
    private final static function customSimpleQueryBuilderCallback($callback,$params,$builder = null)
    {
        $instance = SingletonInstance::getInstance(static::class);
        $builder = $builder === null ? $instance : $builder;
        is_string($callback) && method_exists($instance,$callback) && $builder = call_user_func_array([$instance,$callback],[&$builder,$params]);

        return $builder;
    }

    /**
     * 分页
     *
     * @param Builder $query
     * @param array $params
     * @return array
     */
    private final static function customSimplePaginatorInfo(Builder &$query,$params=[])
    {
        $params['page_now']=array_key_exists('page_now',$params) ? $params['page_now'] : ((int)request()->input('page_now',0));/*当前页*/
        $params['page_size']=array_key_exists('page_size',$params) ? $params['page_size'] : ((int)request()->input('page_size',30));/*每页个数*/
        $paginate=$query->paginate($params['page_size'],['*'],'page',$params['page_now']);
        return [
            'current_page'  => (int)$paginate->currentPage(),
            'last_page'     => (int)$paginate->lastPage(),
            'per_page'      => (int)$paginate->perPage(),
            'total'         => (int)$paginate->total(),
            'list'          => $paginate->items(),
        ];
    }

    /**
     * 新建表
     *
     * @param \Closure $callback 回调
     */
    public final  static function customSimpleCreateTable($callback)
    {
        if(!Schema::hasTable(static::customSimpleTableName())){
            Schema::create(static::customSimpleTableName(),function (Blueprint $table) use($callback){
                $callback($table);
            });
        }
    }

    /**
     * 删除表
     */
    public final static function customSimpleDropTable()
    {
        Schema::dropIfExists(static::customSimpleTableName());
    }

    /**
     * 增加字段
     *
     * @param array $column 字段
     */
    public final  static function customSimpleAddColumn($column=[])
    {
        foreach($column as $name=>$config){
            $afterColumn=array_key_exists('after_column',$config) ? $config['after_column'] : '';
            if(!static::customSimpleHasColumn($name,false) && (!$afterColumn || ($afterColumn && static::customSimpleHasColumn($afterColumn,false)))){
                $callback=$config['callback'];
                Schema::table(static::customSimpleTableName(),function(Blueprint $table) use ($name,$afterColumn,$callback){
                    $res=$callback($name,$table);
                    if($afterColumn){
                        $res->after($afterColumn);
                    }
                });
            }
        }
    }

    /**
     * 删除字段
     *
     * @param array $column 字段
     */
    public final  static function customSimpleDropColumn($column=[])
    {
        foreach($column as $name){
            if(static::customSimpleHasColumn($name,false)){
                Schema::table(static::customSimpleTableName(),function(Blueprint $table) use ($name){
                    $table->dropColumn($name);
                });
            }
        }
    }

    /**
     * 字段是否存在
     *
     * @param string $column 字段名
     * @param bool $cache 是否使用缓存
     * @return bool
     */
    public final  static function customSimpleHasColumn($column,$cache=true)
    {
        if(!$cache){
            return static::customSimpleConnectInstance()->getSchemaBuilder()->hasColumn(static::customSimpleTableName(),$column);
        }
        $tableName = static::customSimpleTableName();
        static $staticField;
        if(!isset($staticField[$tableName])){
            $multiFields = $staticField[$tableName] = static::customSimpleConnectInstance()->getSchemaBuilder()->getColumnListing($tableName);
        }else{
            $multiFields = $staticField[$tableName];
        }

        return in_array($column,$multiFields,true);
    }

    /**
     * 加前缀
     *
     * @param array $params
     * @return array|mixed|string|string[]
     */
    public final static function expressSelectFieldPrefix($params=[])
    {
        $alias=array_key_exists('alias',$params) ? explode(',',$params['alias']) : [];/*别名*/
        $select=array_key_exists('select',$params) ? $params['select'] : '';/*查询字段*/
        $tablePrefix=static::customSimpleConnectInstance()->getTablePrefix();
        if($alias){
            $search=$tmp=[];
            foreach ($alias as $v){
                $tmp[]=$tablePrefix.$v.'.';
                $search[]=$v.'.';
            }
            $select=str_replace($search,$tmp,$select);
        }

        return $select;
    }

    /**
     * 获取表前缀
     *
     * @return string
     */
    final public static function customSimpleGetTablePrefix()
    {
        return static::customSimpleConnectInstance()->getTablePrefix();
    }

    /**
     * 默认索引名称
     *
     * @param $column
     * @param string $indexType
     * @return string
     */
    protected static function getDefaultIndexName($column,$indexType='index') : string
    {
        return str_replace(['-', '.'], '_', strtolower(static::customTableName().'_'.$column.'_'.$indexType));
    }

    /**
     * 索引是否存在
     *
     * @param string $index 索引名
     * @return bool
     */
    final public static function customHasIndex($index)
    {
        return static::customGetConnection()->getDoctrineSchemaManager()->listTableDetails(static::customTableName(true))->hasIndex($index);
    }

    /**
     * 添加索引
     *
     * @param $params
     * @return bool
     */
    public static function customSimpleAddIndex($params)
    {
        $indexType = $params['index']??'index';
        $column    = $params['column']??'';
        $tableName = static::customSimpleTableName();
        $indexName = $params['index_name']??static::getDefaultIndexName($column,$indexType);

        if(static::customSimpleHasColumn($column) && !static::customHasIndex($indexName)){
            Schema::table($tableName, function (Blueprint $table) use ($indexType,$column,$indexName){
                switch ($indexType){
                    case 'primary':
                        $table->primary($column,$indexName);break;
                    case 'index':
                        $table->index($column,$indexName);break;
                    case 'unique':
                        $table->unique($column,$indexName);break;
                    case 'foreign':
                        $table->foreign($column,$indexName);break;
                    default:
                        $table->index($column,$indexName);
                }
            });
        }

        return true;
    }

    /**
     * 删除索引
     *
     * @param $params
     * @return bool
     */
    public static function customSimpleDropIndex($params)
    {
        $indexType = $params['index']??'index';
        $column    = $params['column']??'';
        $indexName = $params['index_name']??static::getDefaultIndexName($column,$indexType);
        if(static::customSimpleHasColumn($column) && static::customHasIndex($indexName)){
            Schema::table(static::customSimpleTableName(), function (Blueprint $table) use ($column,$indexType,$indexName){
                switch ($indexType){
                    case 'primary':
                        $table->dropPrimary($indexName);break;
                    case 'index':
                        $table->dropIndex($indexName);break;
                    case 'unique':
                        $table->dropUnique($indexName);break;
                    case 'foreign':
                        $table->dropForeign($indexName);break;
                    default:
                        $table->dropIndex($indexName);
                }
            });
        }

        return true;
    }
}