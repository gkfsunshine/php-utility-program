<?php declare(strict_types=1);
/**
 *
 * @author apple
 * @date 2020-05-02
 */

namespace MeiquickLib\Lib\Utils;

use MeiquickLib\Service\Log\Log;

class Str
{
    /**
     * 下划线转换为驼峰
     *
     * @param $unCamelizeWord
     * @param string $separator
     * @return string
     */
    public static function camelizeToUpperWord(string $unCamelizeWord='',string $separator='_') : string
    {
        $unCamelizeWordArr = explode($separator,$unCamelizeWord);
        $tmp = '';
        foreach ($unCamelizeWordArr as $key=>$word)
        {
            $tmp .= $key == 0 ? $word : ucwords($word);
        }

        return $tmp;
    }

    /**
     * json编码
     * @param $data
     * @return false|string
     */
    final public static function encodeJson($data)
    {
        return json_encode($data,JSON_UNESCAPED_UNICODE);
    }

    /**
     * json解码
     * @param string $string
     * @return mixed
     */
    final public static function decodeJson(string $string)
    {
        return json_decode($string,true);
    }

    /**
     * 图形转base64
     *
     * @param string $img
     * @return string
     */
    final public static function imgToBase64($img='')
    {
        $imgInfo = getimagesize($img);
        return 'data:'.$imgInfo['mime'].';base64,'.base64_encode(file_get_contents($img));
    }

    /**
     * 压缩图形转base64
     * @param string $imageUrl
     * @return string
     */
    final public static function compressImageToBase64($imageUrl = '',$scaleSize = 1000,$params = [])
    {
        try{
            $fileName = md5($imageUrl).'.jpg';
            $result = Image::scale($imageUrl,$scaleSize,$fileName,$params);
            if($result){
                $base64 = 'data:image/jpeg;base64,'.base64_encode(file_get_contents(Image::IMAGEFILE_PATH.$fileName));
                Image::runtimeImageClear($fileName);
                return $base64;
            }
        }catch (\Exception $e){
            Log::error('压缩图片异常：'.urldecode(json_encode([
                    'message' => urlencode($e->getMessage()),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'code' => $e->getCode()
                ])));
            throw new \Exception($e->getMessage());
        }

        return self::imgToBase64($imageUrl);
    }

    /**
     * 随机生成A-Z
     *
     * @param int $length
     * @return string
     */
    final public static function randomAZStr($length=2)
    {
        $tmp=range('A','Z');
        $str='';
        for($i=0;$i<$length;$i++){
            $str.=$tmp[array_rand($tmp,1)];
        }

        return $str.rand(1111,9999);
    }
}
