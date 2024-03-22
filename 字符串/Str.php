<?php

namespace App\Helpers;

/**
 * 字符
 */
class Str
{
    /**
     * 统计字数
     *
     * @param string $str
     * @return int
     */
    final public static function wordCount($str='')
    {
        return mb_strlen($str);
    }

    /**
     * 获取bom字符
     *
     * @return string
     */
    final public static function bom()
    {
        return chr(239).chr(187).chr(191);
    }

    /**
     * 清除bom
     *
     * @param string $content 内容
     * @return string
     */
    final public static function clearBom($content='')
    {
        return str_replace(static::bom(),'',$content);
    }

    /**
     * 是否有中文
     *
     * @param string $content 内容
     * @return bool
     */
    final public static function hasZw($content='')
    {
        return $content && preg_match('/[\x{4e00}-\x{9fa5}]/u',$content)===1;
    }

    /**
     * 唯一标识
     *
     * @return string
     * @throws \Exception
     */
    final public static function uuid()
    {
        return uniqid(bin2hex(random_bytes(10)), true);
    }
}