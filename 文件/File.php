<?php

namespace App\Helper;

/**
 * 文件
 */
class File
{
    /**
     * 创建目录
     *
     * @param string $dir 目录
     * @param int $mode 权限
     * @return bool
     */
    final public static function mkdir($dir,$mode=0777)
    {
        if(($dir===null) || $dir===''){
            return false;
        }
        if(is_dir($dir) || $dir=== '/'){
            return true;
        }
        if(static::mkdir(dirname($dir),$mode)){
            $result=@mkdir($dir,$mode);
            @chmod($dir,$mode);
            return $result;
        }
        return false;
    }
}