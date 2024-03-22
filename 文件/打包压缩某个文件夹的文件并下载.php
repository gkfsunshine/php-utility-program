<?php
//composer require  chumper/zipper
use Chumper\Zipper\Zipper;

$filePath='';
if(empty($filePath) && !file_exists($filePath)){
    return false;
}
$uuid =  uniqid();
$arr = glob($filePath);
$filename = storage_path('order_'.$uuid.'_'.date('Y-m-d').'.zip');
$zipper=new Zipper();
$zipper->make($filename,'zip')->add($arr)->close();
if(!file_exists($filename)){
    return false;
}

//输出压缩文件提供下载
header("Cache-Control: max-age=0");
header("Content-Description: File Transfer");
header('Content-disposition: attachment; filename=' . basename($filename)); // 文件名
header("Content-Type: application/zip"); // zip格式的
header("Content-Transfer-Encoding: binary"); //
header('Content-Length: ' . filesize($filename)); //
@readfile($filename);//输出文件;
unlink($filename); //删除压缩包临时文件