<?php

namespace App\Utils;

class HiddenSecurity
{
    /**
     * 隐藏手机号
     * @param $mobile
     * @return false|string
     */
    final static function hiddenMobile($mobile)
    {
        if (empty($mobile)) return '';
        $mobileNum = strlen($mobile);
        if($mobileNum > 6) {
            // 仅保留前三位及后三位
            return static::hideStr($mobile, 3, 3, 4, '*');
        } elseif ($mobileNum > 3) {
            return static::hideStr($mobile, 0, 3, 1, '*');
        } else {
            return static::hideStr($mobile, 0, $mobileNum, 1, '*');
        }
    }

    /**
     * 隐藏身份证
     * @param $mobile
     * @return false|string
     */
    final static function hiddenIdCard($card)
    {
        if (empty($card)) return '';
        $cardNum = strlen($card);
        if($cardNum > 17) {
            // 加密中间第5位至14位
            return static::hideStr($card, 4, 4, 4, '*');
        } elseif ($cardNum > 4) {
            return static::hideStr($card, 0, $cardNum-4, 1, '*');
        } else {
            return static::hideStr($card, 0, $cardNum, 1, '*');
        }
    }

    /**
    +----------------------------------------------------------
     * 将一个字符串部分字符用*替代隐藏
    +----------------------------------------------------------
     * @param string $string 待转换的字符串
     * @param int  $bengin 起始位置，从0开始计数，当$type=4时，表示左侧保留长度
     * @param int  $len  需要转换成*的字符个数，当$type=4时，表示右侧保留长度
     * @param int  $type  转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串
     * @param string $glue  分割符
    +----------------------------------------------------------
     * @return string 处理后的字符串
    +----------------------------------------------------------
     */
    public static function hideStr($string, $bengin = 0, $len = 4, $type = 0, $glue = "@") {
        if (empty($string)) return false;
        $array = array();
        if ($type == 0 || $type == 1 || $type == 4) {
            $strlen = $length = mb_strlen($string);
            while ($strlen) {
                $array[] = mb_substr($string, 0, 1, "utf8");
                $string = mb_substr($string, 1, $strlen, "utf8");
                $strlen = mb_strlen($string);
            }
        }

        if ($type == 0) {
            for ($i = $bengin; $i < ($bengin + $len); $i++) {
                if (isset($array[$i])) $array[$i] = "*";
            }
            $string = implode("", $array);
        } else if ($type == 1) {
            $array = array_reverse($array);
            for ($i = $bengin; $i < ($bengin + $len); $i++) {
                if (isset($array[$i])) $array[$i] = "*";
            }
            $string = implode("", array_reverse($array));
        } else if ($type == 2) {
            $array = explode($glue, $string);
            $array[0] = static::hideStr($array[0], $bengin, $len, 1);
            $string = implode($glue, $array);
        } else if ($type == 3) {
            $array = explode($glue, $string);
            $array[1] = static::hideStr($array[1], $bengin, $len, 0);
            $string = implode($glue, $array);
        } else if ($type == 4) {
            $left = $bengin;
            $right = $len;
            $tem = array();
            for ($i = 0; $i < ($length - $right); $i++) {
                if (isset($array[$i])) $tem[] = $i >= $left ? "*" : $array[$i];
            }
            $array = array_chunk(array_reverse($array), $right);
            $array = array_reverse($array[0]);
            for ($i = 0; $i < $right; $i++) {
                $tem[] = $array[$i];
            }
            $string = implode("", $tem);
        }
        return $string;
    }
}