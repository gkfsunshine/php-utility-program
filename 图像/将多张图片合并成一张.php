<?php
	$canvasWidth  = $data['width']??1000;/*合成图片画布长度一张*/
	$canvasHeight = $data['height']??600;/*合成图片画布高度一张*/
	$files  = $data['files']??[];/*需要合成的图片 可以多张*/
	$fileName = $data['file_name']??'./result.jpg';/*输出图片*/
	$imageNum =  count($files);
	$padding = 20;/*边距*/
	$canvas = imagecreatetruecolor($canvasWidth + 2 * $padding, $imageNum * $canvasHeight + ($imageNum + 1) * $padding);/*创建画布*/
	$white  = imagecolorallocate($canvas, 255, 255, 255);/*白底*/
	imagefill($canvas, 0, 0, $white);/*白底*/
	foreach ($files as $k=>$file){
		list($width, $height, $type) = getimagesize($file);
		switch ($type) {
			case IMAGETYPE_JPEG:
				$image = imagecreatefromjpeg($file);
				break;
			case IMAGETYPE_PNG:
				$image = imagecreatefrompng($file);
				break;
			case IMAGETYPE_GIF:
				$image = imagecreatefromgif($file);
				break;
			default:
				die('Unsupported image type');
		}
		$scale = min($canvasWidth / $width,$canvasHeight / $height);
		$newWidth  = $scale * $width;
		$newHeight = $scale * $height;
		$newImage  = imagecreatetruecolor($newWidth, $newHeight);
		imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		imagecopy($canvas, $newImage, ($canvasWidth - $newWidth) / 2 + $padding, ($canvasHeight + $padding) * $k + $padding, 0, 0, $newWidth, $newHeight);
		imagedestroy($newImage);/*销毁画布*/
	}
	imagejpeg($canvas, $fileName);/*输出画布*/
	imagedestroy($canvas);/*销毁画布*/