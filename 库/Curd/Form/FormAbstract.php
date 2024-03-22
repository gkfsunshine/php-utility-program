<?php declare(strict_types=1);
/**
 *
 * @author apple
 * @date 2020-05-02
 */

namespace MeiquickLib\Lib\Curd\Form;

use Hyperf\Database\Model\Builder;
use MeiquickLib\Lib\Utils\Arr;
use MeiquickLib\Lib\Utils\Str;
use MeiquickLib\Lib\Curd\Column\Columns;
use MeiquickLib\Model\Common\BaseModel;
use MeiquickLib\Service\Auth\JwtInstance;
use phpseclib\Crypt\Base;

/**
 * 查询抽象
 *
 * Class GridAbstract
 * @package MeiquickLib\Lib\Curd\Grid
 */
abstract class FormAbstract
{
    /**
     * @var Builder
     */
    protected $modelBuilder;/*查询构造*/
    protected $model;/*查询model*/
    protected $eloquentBuilder;/*前端数据*/
    protected $callback;/*自定义回调*/
    protected $columns;/*自定字段类型*/

    public function __construct(BaseModel $model,array $eloquentBuilder = [],$callback=[])
    {
        $this->eloquentBuilder = $eloquentBuilder;
        $this->callback = $callback;
        $this->model = $model;

        $this->columns = new Columns($model);
    }

    /**
     * 获取当前实例
     *
     * @return mixed
     */
    protected function getInstance()
    {
        return $this->model;
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
    protected function customCallback(string $funcName='') : ?Builder
    {
        if($funcName){
            $callback = $this->callback;
            if(!empty($callback)){
                if(is_callable($callback)){
                    $callback($supplement = new FormSupplement($this));
                    $this->callback = $callback = $supplement->getCallback();
                }
                if(is_array($callback)){
                    $callbackMethodName = '';
                    foreach ($callback as $selfMethodName=>$customMethodName){
                        if((string)Str::camelizeToUpperWord($selfMethodName) === $funcName){
                            $callbackMethodName = $customMethodName;
                            break;
                        }
                    }
                    if($callbackMethodName && is_string($callbackMethodName) && method_exists($this->getInstance(),$callbackMethodName)){
                        return $this->callbackFunc($callbackMethodName,$this->eloquentBuilder);
                    }elseif($callbackMethodName && is_array($callbackMethodName)){
                        list($instance,$callbackMethodName) = [key($callbackMethodName),end($callbackMethodName)];
                        return $this->callbackFunc($callbackMethodName,$this->eloquentBuilder,new $instance);
                    }
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
    protected function callbackFunc($callback,&$params,$instance=null) : ?Builder
    {
        $builder = $this->modelBuilder;
        $instance = $instance === null ? $this->getInstance() : $instance;
        $builder = $builder === null ? $instance : $builder;
        is_string($callback) && method_exists($instance,$callback) && $builder = call_user_func_array([$instance,$callback],[&$builder,&$params]);

        return $builder;
    }

    /**
     * 动态改变参数 给回调model使用
     *
     * @param array $data
     * @return array
     */
    protected function setEloquentBuilder($data=[])
    {
        return $this->eloquentBuilder = array_merge($data,$this->eloquentBuilder);
    }

    /**
     * save 时间|操作管理员
     */
    protected function setOperator($update=false, $operateor = true) : void
    {
        $timestamps = BaseModel::timeValue();
        $saveData = $this->eloquentBuilder['data'] ?? [];
        /**@var BaseModel**/
        $operatorLog = [];
        $model = $this->getInstance();
        if($operateor){
            if($model::$_customSavedFieldManagerId && $model::$_customSavedFieldManagerName){
                $operatorLog[$model::$_customSavedFieldManagerId] = JwtInstance::instance()->getId()?:0;
                $operatorLog[$model::$_customSavedFieldManagerName] = JwtInstance::instance()->getUsername()?:'system';
            }
        }

        if(Arr::isDimensionalArray($saveData)){
            $operatorTime = [BaseModel::UPDATED_AT => $timestamps];
            $operatorTime += $update === false ? [BaseModel::CREATED_AT => $timestamps] : [];
            $saveData = array_merge($operatorTime,$saveData,$operatorLog);
        }else{
            foreach ($saveData as $key=>$data){
                if($update === false && empty($saveData[$key][BaseModel::CREATED_AT])) $saveData[$key][BaseModel::CREATED_AT] = $timestamps;
                if(empty($saveData[$key][BaseModel::UPDATED_AT])) $saveData[$key][BaseModel::UPDATED_AT] = $timestamps;
                $saveData[$key] =  array_merge($operatorLog,$saveData[$key]);
            }
        }

        $this->eloquentBuilder['data'] = $saveData;
    }

    /**
     * 获取保存数据
     *
     * @return mixed
     */
    protected function getSaveData()
    {
        $saveData = $this->eloquentBuilder['data']??[];

        return $this->columns->customFilterSaveData($saveData);
    }

    /**
     * 自定义数据处理
     *
     * @param string $code
     * @param bool $save
     * @param string $message
     * @return array
     */
    protected function customReturnHandler($code='data_not_exist', $save=false, $message='') : array
    {
        $handler = [
            'save' => $save,
            'code' => $code,
            'message' => $message
        ];
        switch ($code){
            case 'data_not_exist':
                $handler['message'] = $message.' form data error 数据不存在';
                break;
            case 'insert_data_error':
                $handler['message'] = $message.' insert data error 数据插入失败';
                break;
            case 'update_date_error':
                $handler['message'] = $message.' update data error 数据更新失败';
                break;
            case 'success':
                $handler['save'] = $save === false ? true : $save;//success 就算传false保证为true
                $handler['message'] = $message.' success 数据操作成功';
                break;
        }

        return $handler;
    }
}
