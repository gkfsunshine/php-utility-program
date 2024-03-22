<?php
$filePath='';//下载的文件路径
header("Content-type:text/html;charset=utf-8");

if(file_exists($filePath)){
    $fp = fopen($filePath,"r");
    $fileSize = filesize($filePath);

    //下载文件需要用到的头
    Header("Content-type: application/octet-stream");
    Header("Accept-Ranges: bytes");
    Header("Accept-Length:".$fileSize);
    Header("Content-Disposition: attachment; filename=".basename($filePath));

    $buffer=1024;

    $fileCount=0;
    while(!feof($fp) && $fileCount < $fileSize)
    {
        $fileCon=fread($fp,$buffer);
        $fileCount+=$buffer;
        echo $fileCon;
    }
    fclose($fp);    //关闭这个打开的文件
}