<?php
$dir=$data['dir_path']??'./';/*删除的文件夹路径*/
$days=$data['expire_time']??7;/*删除的文件过期时间*/
$pattern = "/\.".($data['filename_prefix']??'zip')."$/"; // 匹配.zip文件
if(!is_dir($dir)){
    return false;
}
$files = scandir($dir);
foreach ($files as $file) {
    if($file=='.' || $file=='..'){
        continue;
    }
    if (is_file($dir . $file) && preg_match($pattern, $file) && (time() - filemtime($dir . $file)) / 3600 / 24 > $days) {
        @unlink($dir . $file);
    }
}