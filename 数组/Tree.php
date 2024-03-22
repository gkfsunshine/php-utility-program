<?php

namespace App\Helpers;

/**
 * 树
 */
class Tree
{
    protected static $_idKey='id';/*默认id键名*/
    protected static $_pidKey='pid';/*默认父id键名*/
    protected static $_childDataKey='child_data';/*默认字数据键名*/
    /**
     * 生成
     *
     * @param array $param 参数
     * @return array
     */
    final public static function make($param=[])
    {
        $param['data']=isset($param['data']) ? $param['data'] : [];/*数据*/
        $param['id_key']=isset($param['id_key']) ? $param['id_key'] : static::$_idKey;/*id键名*/
        $param['pid_key']=isset($param['pid_key']) ? $param['pid_key'] : static::$_pidKey;/*父id键名*/
        $param['child_data_key']=isset($param['child_data_key']) ? $param['child_data_key'] : static::$_childDataKey;/*字数据键名*/
        $param['root_pid']=isset($param['root_pid']) ? (int)$param['root_pid'] : 0;/*根父id*/
        $param['level_start_at']=isset($param['level_start_at']) ? (int)$param['level_start_at'] : 0;/*级开始于*/
        $param['max_level']=isset($param['max_level']) ? (int)$param['max_level'] : null;/*最大级*/
        $param['tree_structure']=isset($param['tree_structure']) ? $param['tree_structure'] : false;/*是否树形结构*/
        $param['branch_data']=isset($param['branch_data']) ? $param['branch_data'] : null;/*树枝数据*/
        $param['do_init']=isset($param['do_init']) ? $param['do_init'] : true;/*是否初始化*/
        $continue=true;/*是否继续*/
        static $idKeyData;/*id键为key的数据*/
        static $p2cData;/*父对应子数据*/
        if($param['do_init']){/*初始化时*/
            $idKeyData=[];
            $p2cData=[];
            foreach($param['data'] as $v){
                $idKeyData[($v[($param['id_key'])])]=$v;
                if(!isset($p2cData[($v[($param['pid_key'])])])){
                    $p2cData[($v[($param['pid_key'])])]=[];
                }
                $p2cData[($v[($param['pid_key'])])][]=$v;
            }
        }else{
            if(!isset($idKeyData[($param['root_pid'])])){
                $continue=false;
            }
        }
        $result=[];
        if($param['max_level']!==null && $param['level_start_at']>$param['max_level']){/*超过最大级别*/
            $continue=false;
        }
        if(!$continue){
            return $result;
        }
        if(isset($p2cData[($param['root_pid'])])){/*存在子数据*/
            foreach($p2cData[($param['root_pid'])] as $k=>$v){
                $branchData=[/*树枝数据*/
                    'data'=>$v,/*数据*/
                    'level'=>$param['level_start_at'],/*级别*/
                ];
                if($param['branch_data']!==null){
                    $param['branch_data']($branchData);
                }
                if($param['tree_structure']){/*树形结构*/
                    $result[$k]=$branchData;
                }else{
                    $result[]=$branchData;
                }
                if(isset($p2cData[($v[($param['id_key'])])])){
                    $tmpParam=$param;
                    $tmpParam['do_init']=false;
                    $tmpParam['root_pid']=$v[($param['id_key'])];
                    $tmpParam['level_start_at']++;
                    $childData=static::make($tmpParam);
                    if($childData){/*有子数据*/
                        if($param['tree_structure']){/*树形结构*/
                            $result[$k][($param['child_data_key'])]=$childData;
                        }else{
                            $result=array_merge($result,$childData);
                        }
                    }
                }
            }
        }
        return $result;
    }
}