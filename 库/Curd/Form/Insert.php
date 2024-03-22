<?php declare(strict_types=1);
/**
 *
 * @author apple
 * @date 2020-05-02
 */

namespace MeiquickLib\Lib\Curd\Form;

class Insert extends FormAbstract
{
    /**
     * init insert
     */
    private function initInsert() : void {}

    /**
     * before insert
     */
    private function beforeInsert() : void
    {
       $this->customCallback(__FUNCTION__);
    }

    /**
     * after insert
     */
    private function afterInsert() : void
    {
        $this->customCallback(__FUNCTION__);
    }

    /**
     * 新增数据 逻辑处理
     *
     * @return array|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|null
     */
    public function execute()
    {
        if(empty($this->saveData)){
            $this->customReturnHandler();
        }
        $this->initInsert();
        try{
            $this->beforeInsert();
            $saveBool = $this->scopes();
            if($saveBool === false || $saveBool === 0){
                return $this->customReturnHandler('insert_data_error');
            }
            $this->setEloquentBuilder([
                'save' => $saveBool
            ]);
            $this->afterInsert();

            return $this->customReturnHandler('success',$saveBool);
        }catch (\Exception $e){
            return $this->customReturnHandler('insert_data_error',false,$e->getMessage());
        }
    }

    /**
     * 切换新增方式
     *
     * @return bool|int
     */
    private function scopes()
    {
        $dbOperator = 'insert';
        if(isset($this->callback['insert_get_id']) && $this->callback['insert_get_id'] === true){
            $dbOperator = 'insertGetId';
        }elseif(isset($this->callback['entities']) && $this->callback['entities'] === true){
            $dbOperator = 'entities';
        }

        switch ($dbOperator){
            case 'insert':
                $this->setOperator();
                return $this->insert();
                break;
            case 'insertGetId':
                $this->setOperator();
                return $this->insertGetId();
                break;
            case 'entities':
                return $this->setAttribute();
                break;
        }

        return false;
    }

    /**
     * insert case 1 get id
     *
     * @return int
     */
    private function insertGetId() : int
    {
        $res = $this->modelBuilder->insertGetId($this->getSaveData());

        return $res;
    }

    /**
     * insert case 2 bool
     *
     * @return bool
     */
    private function insert() : bool
    {
        return $this->modelBuilder->insert($this->getSaveData());
    }

    /**
     * insert case 3 entities
     *
     * @return bool
     */
    private function setAttribute() : bool
    {
        foreach ($this->getSaveData() as $column => $value) {/*这里污染了model 处理其他数据时要重新实例*/
            $this->model->setAttribute($column, $value);/*设置字段的默认类型 也可以setFieldAttribute*/
        }

        return $this->model->save();
    }
}
