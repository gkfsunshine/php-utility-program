<?php
namespace App\Utils;

use App\Exceptions\ApiException;

/**
 * 文件打包
 * @author  ouyangwei 2019-12-11
 */
class Pack
{
    /**
     * 打包生成zip文件，返回生成后的zip文件名
     *
     * @param $path 文件路径 \App\Utils\Pack::zip('goods/409')
     * @param $prefix 文件名前缀
     * @return string
    */
    public static function zip($path, $prefix = '')
    {
        try{
            $fileData = self::getDirFile($path);
            $fileName = $prefix.'file_'.date('YmdHis').rand(1111, 9999).'.zip';
            $zip = new \ZipArchive();
            $handle = $zip->open($path.'/'.$fileName, \ZipArchive::CREATE);//打开压缩包
            if($handle == false) throw new ApiException('zip文件创建失败，请重试');
            foreach($fileData as $k => $file){
                $zip->addFile($file, basename($file));//向压缩包中添加文件
            }
            $zip->close();  //关闭压缩包
        }catch (\Exception $e){
            throw new ApiException($e->getMessage());
        }

        return $fileName;
    }

    /**
     *读取指定文件夹所有文件
     *
     * @param $dir 文件目录
     * @return array
    */
    public static function getDirFile($dir)
    {
        if(is_dir($dir) == false) throw new ApiException('目录不存在：'.$dir);
        $dirHandle = opendir($dir);
        while($filename = readdir($dirHandle)){
            if($filename == '.' || $filename == '..') continue;
            $file = $dir.'/'.$filename;
            if(is_file($file)) $fileData[] = $file;
        }
        closedir($dirHandle);

        return $fileData;
    }
}
