<?php declare(strict_types=1);
/**
 *
 * @author apple
 * @date 2020-05-03
 */

namespace MeiquickLib\Lib\Curd\Form;

use Hyperf\Database\Model\SoftDeletingScope;
use MeiquickLib\Lib\Utils\Arr;
use MeiquickLib\Model\Common\BaseModel;

class Update extends FormAbstract
{
    /**
     * init insert
     */
    private function initUpdate() : void {}

    /**
     * before insert
     */
    private function beforeUpdate() : void
    {
        $this->customCallback(__FUNCTION__);
    }

    /**
     * after insert
     */
    private function afterUpdate() : void
    {
        $this->customCallback(__FUNCTION__);
    }

    /**
     * update
     *
     * @return int
     */
    private function update()
    {
        return $this->modelBuilder->update($this->getSaveData());
    }

    /**
     * 软删除
     *
     * @return int
     */
    private function softDelete()
    {
        return $this->modelBuilder->update([
            BaseModel::DELETED_AT => BaseModel::timeValue()
        ]);
    }

    /**
     * 去掉delete_at查询
     * @return \Hyperf\Database\Model\Builder
     */
    protected function withoutSoftDelete()
    {
        return $this->modelBuilder->withoutGlobalScope(SoftDeletingScope::class);
    }

    /**
     * 硬删除
     *
     * @return int|mixed
     */
    private function realDelete()
    {
        return $this->modelBuilder->forceDelete();
    }

    /**
     * 字段自增
     * @return int
     */
    protected function increment()
    {
        return $this->modelBuilder->increment(...$this->eloquentBuilder['increase']);
    }

    /**
     * 字段自减
     * @return int
     */
    protected function decrement()
    {
        return $this->modelBuilder->decrement(...$this->eloquentBuilder['decrease']);
    }

    /**
     * 更新数据 逻辑处理
     *
     * @return array|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|null
     */
    public function execute()
    {
        if(empty($this->saveData)){
            $this->customReturnHandler();
        }
        $this->initUpdate();
        $this->analysisCall();
        try{
            $this->beforeUpdate();
            $saveBool = $this->scopes();
            if($saveBool < 0){
                return $this->customReturnHandler('update_data_error');
            }
            $this->setEloquentBuilder([
                'save' => $saveBool
            ]);
            $this->afterUpdate();

            return $this->customReturnHandler('success',$saveBool);
        }catch (\Exception $e){
            return $this->customReturnHandler('update_data_error', false, $e->getMessage());
        }
    }

    /**
     * 切换新增方式
     *
     * @return bool|int
     */
    private function scopes()
    {
        $dbOperator = 'update';
        $softDeleteKey = 'soft_delete';
        $hardDeleteKey = 'real_delete';
        $increaseKey = 'increase';
        $decreaseKey = 'decrease';
        $updateWithoutOperator = 'update_without_operator';
        if(isset($this->callback[$softDeleteKey]) && $this->callback[$softDeleteKey] === true){
            $dbOperator = $softDeleteKey;
        }elseif (isset($this->callback[$hardDeleteKey]) && $this->callback[$hardDeleteKey] === true){
            $dbOperator = $hardDeleteKey;
        }elseif (isset($this->eloquentBuilder[$increaseKey]) && is_array($this->eloquentBuilder[$increaseKey])){
            $dbOperator = $increaseKey;
        }elseif (isset($this->eloquentBuilder[$decreaseKey]) && is_array($this->eloquentBuilder[$decreaseKey])){
            $dbOperator = $decreaseKey;
        }elseif (isset($this->callback[$updateWithoutOperator]) && $this->callback[$updateWithoutOperator] === true){
            $dbOperator = $updateWithoutOperator;
        }
        switch ($dbOperator){
            case 'update':
                $this->setOperator(true);
                return $this->update();
                break;
            case 'soft_delete':
                return $this->softDelete();
                break;
            case 'real_delete':
                return $this->realDelete();
                break;
            case 'increase':
                return $this->increment();
                break;
            case 'decrease':
                return $this->decrement();
                break;
            case 'update_without_operator':
                $this->setOperator(true, false);
                return $this->update();
                break;
        }

        return false;
    }

    /**
     * 查询条件 where
     *
     * @param $condition
     */
    protected function condition($condition) : void
    {
        Arr::isDimensionalArray($condition) ? $this->modelBuilder->where(...$condition) : $this->modelBuilder->where($condition);
    }

    /**
     * 查询条件 whereIn
     *
     * @param array $condition
     */
    protected function conditionIn(array $condition=[]) : void
    {
        if($condition) $this->modelBuilder->whereIn(...$condition);
    }

    /**
     * 查询条件 whereNotIn
     *
     * @param array $condition
     */
    protected function conditionNotIn(array $condition=[]) : void
    {
        if($condition) $this->modelBuilder->whereNotIn(...$condition);
    }

}
