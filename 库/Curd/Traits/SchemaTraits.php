<?php declare(strict_types=1);
/**
 *
 * @author apple
 * @date 2020-05-01
 */

namespace MeiquickLib\Lib\Curd\Traits;

use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;

trait SchemaTraits
{
    /**
     * truncate
     */
    protected function customTruncate() : void
    {
        make(static::class)->truncate();
    }

    /**
     * 新建表
     *
     * @param \Closure $callback 回调
     */
    protected function customCreateTable(\Closure $callback) : void
    {
        if(!Schema::hasTable($this->customTableName())){
            Schema::create($this->customTableName(),function (Blueprint $table) use($callback){
                $callback($table);
            });
        }
    }

    /**
     * 删除表
     */
    protected function customDropTable() : void
    {
        Schema::dropIfExists($this->customTableName());
    }

    /**
     * 增加索引
     *
     * @param array $index
     */
    protected function customAddIndex(array $index=[]) : void
    {
        foreach($index as $name=>$config){
            if($this->customHasColumn($name)){
                Schema::table($this->customTableName(),function(Blueprint $table) use($name,$config){
                    $config['type']=array_key_exists('type',$config) ? $config['type'] : 'index';/*类型*/
                    switch($config['type']){
                        case 'index':
                            $table->index($config['field'],$name);
                            break;
                        case 'unique':
                            $table->unique($config['field'],$name);
                            break;
                    }
                });
            }
        }
    }

    /**
     * 删除索引
     *
     * @param array $index 索引
     */
    protected function customDropIndex(array $index=[]) : void
    {
        foreach($index as $name=>$config){
            Schema::table($this->customTableName(),function(Blueprint $table) use($name,$config){
                $config['type']=array_key_exists('type',$config) ? $config['type'] : 'index';/*类型*/
                switch($config['type']){
                    case 'index':
                        $table->dropIndex($name);
                        break;
                    case 'unique':
                        $table->dropUnique($name);
                        break;
                }
            });
        }
    }

    /**
     * 增加字段
     *
     * @param array $column 字段
     */
    protected function customAddColumn(array $column=[]) : void
    {
        foreach($column as $name=>$config){
            $callback=$config['callback'];
            Schema::table($this->customTableName(),function(Blueprint $table) use ($name,$callback){
                is_callable($callback) && $callback($name,$table);
            });
        }
    }

    /**
     * 改变字段
     *
     * @param array $column 字段
     */
    protected function customChangeColumn(array $column=[]) : void
    {
        foreach($column as $name=>$config){
            if($this->customHasColumn($name)){
                Schema::table(static::customTableName(),function(Blueprint $table) use ($name,$config){
                    if(array_key_exists('sql',$config)){
                        $config['sql']=trim($config['sql']);
                        $e=';';
                        Db::statement('ALTER TABLE `'.$this->customTableName(true).'` CHANGE `'.$name.'` `'.$name.'` '.$config['sql'].(strrpos($config['sql'],$e)!==false ? '' :$e));
                    }else{
                        is_callable($config['callback']) && $config['callback']($name,$table)->change();
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
    protected function customDropColumn(array $column=[]) : void
    {
        foreach($column as $name){
            if($this->customHasColumn($name)){
                Schema::table($this->customTableName(),function(Blueprint $table) use ($name){
                    $table->dropColumn($name);
                });
            }
        }
    }

    /**
     * 字段是否存在
     *
     * @param string $column 字段名
     * @return bool
     */
    protected function customHasColumn(string $column) : bool
    {
        //return Schema::hasColumn($this->customTableName(),$column);
        return true;
    }

    /**
     * 多个字段是否存在
     *
     * @param array $column 字段
     * @return bool
     */
    protected function customHasMultiColumn(array $column)
    {
        foreach($column as $v){
            if(!$this->customHasColumn($v)){
                return false;
                break;
            }
        }
        return true;
    }
}
