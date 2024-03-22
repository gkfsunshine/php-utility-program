<?php

$dir='';
$isDeletedDir=true;
if ((!$handle = @opendir($dir)) && !file_exists($dir)) {
    return false;
}
while (false !== ($file = readdir($handle))) {
    if ($file !== "." && $file !== "..") {       //排除当前目录与父级目录
        $file = $dir . '/' . $file;
        if (is_dir($file)) {
            static::deleteDir($file);
        } else {
            @unlink($file);
        }
    }
}
if($isDeletedDir)@rmdir($dir);
return true;