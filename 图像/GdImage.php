<?php

namespace App\Helper;

class GdImage{

    /**
     * 图片合成
     * @param $mainOptions 背景板参数[x坐标 y坐标 ]
     * @param $outputPath  输出路径
     * @param $picOptions  图片合成集 [[url地址 x坐标 y坐标 w宽度 h高度]]
     * @param $txtOptions  文字合成集 [[txt文字内容 x坐标 y坐标 size字体大小 box是否箱子 w宽度超出换行 l行数大于等于省略号]]
     * @param null $fontFile 字体文件
     */
    public static function imageSynthesis($mainOptions,$outputPath,$picOptions,$txtOptions,$fontFile = null)
    {
        $fontFile = $fontFile ?: storage_path().'/app/gdfont/simhei.ttf';
        $whole = imagecreatetruecolor($mainOptions['w'],$mainOptions['h']);
        $bgColor = imagecolorallocate($whole,255,255,255);
        imagefill($whole,0,0,$bgColor);
        $txtColor = imagecolorallocate($whole, 51, 51, 51);
        foreach($txtOptions as $to){
            $to['txt'] = mb_convert_encoding($to['txt'],'','UTF-8');
            $curFontColor = $txtColor;
            if(!empty($to['color'])){
                $curFontColor = imagecolorallocate($whole,...$to['color']);
            }
            if(!empty($to['box']) && !empty($to['w'])){
                $letter = mb_str_split($to['txt'],1);
                $boxText = '';
                $boxLine = 1;
                foreach($letter as $clr){
                    $curBoxSize = imagettfbbox($to['size'],0,$fontFile,$boxText.$clr);
                    $curBoxSizeW = $curBoxSize[2]-$curBoxSize[0]; /*右下角x - 左下角x*/
                    if($curBoxSizeW > $to['w']){
                        if(!empty($to['l']) && $boxLine >= $to['l']){
                            $boxText = mb_substr($boxText,0,-2).'...';
                            break;
                        }
                        imagettftext($whole,$to['size'],0,$to['x'],$to['y'],$curFontColor,$fontFile,$boxText);
                        $boxText = '';
                        $to['y'] += $to['size'] + 12;
                        $boxLine ++;
                    }
                    $boxText.= $clr;
                }
                imagettftext($whole,$to['size'],0,$to['x'],$to['y'],$curFontColor,$fontFile,$boxText);
            }else{
                imagettftext($whole,$to['size'],0,$to['x'],$to['y'],$curFontColor,$fontFile,$to['txt']);
            }

            if(isset($to['bold'])){
                imagettftext($whole,$to['size'],0,$to['x']+1,$to['y'],$curFontColor,$fontFile,$to['txt']);
            }
        }

        foreach($picOptions as $po){
            $imageFile = file_get_contents($po['url']);
            $pic = imagecreatefromstring($imageFile);

            list($sw,$sh,$type,$attr) = getimagesize($po['url']);
            $imageThump = imagecreatetruecolor($po['w'],$po['h']);
            imagecopyresampled($imageThump, $pic, 0, 0, 0, 0, $po['w'],$po['h'], $sw, $sh);
            imagedestroy($pic);

            if(isset($po['circle'])){
                self::circleImage($imageThump,$imageThump,$po['w'],$po['y']);
            }

            imagecopymerge($whole,$imageThump,$po['x'],$po['y'],0,0,$po['w'],$po['h'],100);
            imagedestroy($imageThump);

        }

        imagepng($whole,$outputPath);
        imagedestroy($whole);
    }

    /**
     * 圆形头像
     * @param $imageObj
     * @param $arcRec_SX
     * @param $arcRec_SY
     * @param $arcRec_EX
     * @param $arcRec_EY
     * @param $redius
     * @param $color
     */
    public static function circleImage(&$destImg,$srcImg, $w  ,$h)
    {
        $w = $h = min($w, $h);
        $destImg = imagecreatetruecolor($w, $h);
        imagesavealpha($destImg, true);
        // 拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($destImg, 255, 255, 255, 127);
        imagefill($destImg, 0, 0, $bg);

        $r = $w / 2; // 圆的半径
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($srcImg, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r)))
                    imagesetpixel($destImg, $x, $y, $rgbColor);
            }
        }
    }
}
