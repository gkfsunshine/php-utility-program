<?php declare(strict_types=1);
/**
 *
 * @author apple
 * @date 2020-05-02
 */

namespace MeiquickLib\Lib\Curd\Form;

use Hyperf\Database\Model\Builder;

class FormFactory
{
    protected $form;

    public function __construct(FormAbstract $query)
    {
        $this->form = $query;
    }

    /**
     * 设置构造
     *
     * @param Builder $builder
     */
    public function setModelBuilder(Builder $builder) : void
    {
        $this->form->setModelBuilder($builder);
    }

    /**
     * 执行
     *
     * @return Builder|\Hyperf\Database\Model\Model|object|null
     */
    public function execute()
    {
        return $this->form->execute();
    }
}
