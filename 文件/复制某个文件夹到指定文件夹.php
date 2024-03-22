<?php
$src='./';//源文件夹
$dst='./';//目标文件夹
$dir = opendir($src);
@mkdir($dst);
while(false !== ( $file = readdir($dir)) ) {
    if (( $file != '.' ) && ( $file != '..' )) {
        if ( is_dir($src . '/' . $file) ) {
            recurse_copy($src . '/' . $file,$dst . '/' . $file);
        }
        else {
            copy($src . '/' . $file,$dst . '/' . $file);
        }
    }
}
closedir($dir);