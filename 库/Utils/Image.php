<?php declare(strict_types=1);
/**
 *
 * @author apple
 * @date 2020-05-02
 */

namespace MeiquickLib\Lib\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Image
{
    const IMAGEFILE_PATH = BASE_PATH .  '/runtime/imagecompress/';
    const ERROR_CODE = [
        'read_info' => ['msg' => '读取图片信息异常','code' => 101],
        'not_support' => ['msg' => '%s 图片类型不支持处理','code' => 102],
        'read' => ['msg' => '读取图片异常,请检查图片是否有损坏','code' => 103],
    ];

    protected static function checkDir()
    {
        if(!is_dir(self::IMAGEFILE_PATH)){
            mkdir(self::IMAGEFILE_PATH);
        }
    }

    /**
     * 压缩
     * @param $url
     * @param $outputFile
     */
    public static function compress($image,$fileName)
    {
        self::checkDir();
        imagejpeg($image, self::IMAGEFILE_PATH.$fileName,75);
        imagedestroy($image);

        return true;
    }

    /**
     * 等比例压缩
     * @param $url
     * @param $scaleSize
     * @param $outputFile
     */
    public static function scale($url,$scaleSize,$fileName,$params = [])
    {
        self::checkDir();
        $outputFile = self::IMAGEFILE_PATH.$fileName;

        $file = file_get_contents($url);
        if(!$file){
            throw new \Exception(self::ERROR_CODE['read']['msg'],self::ERROR_CODE['read']['code']);
        }

        file_put_contents($outputFile,$file);
        $image = imagecreatefromstring($file);
        if(!$image){
            throw new \Exception(self::ERROR_CODE['read']['msg'],self::ERROR_CODE['read']['code']);
        }

        if(!empty($params['width']) && !empty($params['height'])){
            $width = (int)ceil($params['width']);
            $height = (int)ceil($params['height']);
        }else{
            list($width,$height) = self::getQiniuImageInfo($url);
        }

        if(empty($width) || empty($height)){
            list($width,$height,$type)=getimagesize($outputFile);
        }

        if($width > $height){
            $rate = ($width/$scaleSize);
            $newWidth = $scaleSize;
            $newHeight = ($height/$rate);
        }else{
            $rate = ($height/$scaleSize);
            $newHeight = $scaleSize;
            $newWidth = ($width/$rate);
        }

        if($rate < 1){
            return self::compress($image,$fileName);
        }

        $newWidth = (int)ceil($newWidth);
        $newHeight = (int)ceil($newHeight);

        $imageWp=imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($imageWp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        imagejpeg($imageWp, $outputFile,75);
        imagedestroy($image);
        imagedestroy($imageWp);

        return true;
    }

    /**
     * 获取七牛图片信息
     * @param $imageUrl
     */
    public static function getQiniuImageInfo($imageUrl){
        $matches = [];
        preg_match("/http\:\/\/(.+)\/(meiquick\/idcard\/.+\.jpg)/",$imageUrl,$matches);
        if(count($matches) != 3){
            return [0,0];
        }

        $domain = $matches[1];
        $cover = $matches[2];
        $requestImageUrl = privatePictureUrl($cover.'?imageInfo', $domain);

        try{
            $response = make(Client::class)->get($requestImageUrl);
        }catch (\Exception $e){
            return [0,0];
        }
        if($response->getStatusCode() != 200){
            return [0,0];
        }

        $imageInfo = json_decode($response->getBody()->getContents(),true);
        return [
            $imageInfo['width'],
            $imageInfo['height']
        ];
    }

    /**
     * 删除临时图片文件
     * @param $fileName
     */
    public static function runtimeImageClear($fileName)
    {
        $filePath = self::IMAGEFILE_PATH;
        if(file_exists($filePath)){
            //删除临时文件
            @unlink($filePath.$fileName);
        }
    }
}
