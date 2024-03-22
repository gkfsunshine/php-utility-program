<?php
$filename='xxx.txt';
$content = file_exists($filename) ? file($filename) : [];
$data=[];
foreach ($content as $value){
    if(!empty($val=preg_replace("/[\r\n]/",'',$value))){
        array_push($data,$val);
    }
}