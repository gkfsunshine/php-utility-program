<?php declare(strict_types=1);
/**
 *
 * @author apple
 * @date 2020-05-02
 */

namespace MeiquickLib\Lib\Curd\Form;

class FormSupplement
{
    protected $scopesType = 'insert';
    protected $callback=[];

    public function __construct(?FormAbstract $abstract)
    {
        $this->scopesType = $abstract instanceof Update ? 'update' : 'insert';
    }

    /**
     * @return mixed
     */
    public function getCallback() : array
    {
        return $this->callback;
    }

    /**
     * before insert
     *
     * @param string $method
     */
    public function setBeforeMethod($method='') : void
    {
        $this->callback['before_'.$this->scopesType] = $method;
    }

    /**
     * after insert
     *
     * @param string $method
     */
    public function setAfterMethod($method='') : void
    {
        $this->callback['after_'.$this->scopesType] = $method;
    }

    /**
     * set
     *
     * @param string $scopes
     */
    public function setScopes(string $scopes='insert') : void
    {
        $scopes = $scopes === 'insert' ? $this->scopesType : $scopes;
        $this->callback[$scopes] = true;
    }

    /**
     * 软删除
     *
     * @param bool $isDeleted
     */
    public function setSoftDelete(bool $isDeleted=false)
    {
        $this->callback['soft_delete'] = $isDeleted;
    }

    /**
     * 硬删除
     *
     * @param bool $isDeleted
     */
    public function setRealDelete(bool $isDeleted=false)
    {
        $this->callback['real_delete'] = $isDeleted;
    }


}
