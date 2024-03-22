<?php declare(strict_types=1);
/**
 *
 * @author apple
 * @date 2020-05-02
 */

namespace MeiquickLib\Lib\Curd\Column;

use Hyperf\Database\Schema\Schema;
use Hyperf\Utils\Context;
use MeiquickLib\Exception\BaseValidateException;
use MeiquickLib\Lib\Utils\Arr;
use MeiquickLib\Model\Common\BaseModel;

class Columns
{
    protected $model;/*各自model的实例*/

    public function __construct(BaseModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取表的所有字段
     *
     * @param string $tableName
     * @return mixed
     */
    protected  function customGetTableColumns(?string $tableName = null) : array
    {
        $customContextTableName = 'custom_columns'.($tableName ? : $this->model->getTable());

        if(Context::has($customContextTableName)){
            $columns = Context::get($customContextTableName);
        }else{
            $schemaColumns = Schema::getColumnTypeListing($this->model->getTable());
            $column = [];
            foreach ($schemaColumns as $tmp){
                $tmp = array_change_key_case($tmp,CASE_LOWER);
                isset($tmp['column_name']) && array_push($column,$tmp['column_name']);
            }
            $columns = Context::set($customContextTableName,$column);
        }

        return $columns;
    }

    /**
     * 过滤表字段 获取到正确的字段
     *
     * @param $columns
     */
    private function customPrepareFilterColumns($columns = ['*']) : array
    {
        $filterColumns = ['*'];
        if(empty($columns) || $columns === ['*']){
            return $filterColumns;
        }
        $filterColumns = array_filter($columns,function($field){
            return  in_array($field,$this->customGetTableColumns());
        });

        return $filterColumns?:['*'];
    }

    /**
     * 查询过滤字段
     *
     * @param array $columns
     * @return array
     */
    public function customFilterColumns(array $columns = ['*'])
    {
        return $this->customPrepareFilterColumns($columns);
    }

    /**
     * 插入跟新字段处理
     *
     * @param array $saveData
     * @return array
     */
    public function customFilterSaveData(array $saveData=[]) : array
    {
        $inserts = [];
        if(Arr::isDimensionalArray($saveData)){
            $inserts = $this->customPrepareSaveColumns($saveData);
        }else{
            foreach ($saveData as $val){
                $inserts[] = $this->customPrepareSaveColumns($val);
            }
        }
        if(empty($inserts)){
            throw new BaseValidateException(10102,'Save data is empty');
        }

        return $inserts;
    }

    /**
     * 表单预处理
     *
     * @param array $singleData
     * @return array
     */
    private function customPrepareSaveColumns(array $singleData =[])
    {
        $prepare = [];
        if(empty($singleData)){
            return $prepare;
        }
        $defaultFilterColumns = [$this->model->getKey()];//过滤默认字段
        $filter = $this->customFilterColumns(array_keys($singleData));
        if($filter != ['*']){
            foreach ($filter as $key)
            {
                if(in_array($key,$defaultFilterColumns,true)){
                    continue;
                }
                $prepare[$key] = array_key_exists($key,$singleData) ? $singleData[$key] : 0;
            }
        }else{
            $prepare = $singleData;
        }

        return $prepare;
    }

}
