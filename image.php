<?php
class image{
	
	public function imagebmp($im, $filename = '',$print = false){
		$bit = 1;
		$bits = pow(2, $bit);
		imagefilter($im, IMG_FILTER_GRAYSCALE);
		// 调整调色板
		imagetruecolortopalette($im, true, $bits);
		$width  = imagesx($im);
		$height = imagesy($im);
		$colors_num = imagecolorstotal($im);

		$rgb_quad = '';
		$rgb_quad .= chr(0) . chr(0) . chr(0) . "\0";
		$rgb_quad .= chr(255) . chr(255) . chr(255) . "\0";
		// 位图数据
		$bmp_data = '';
		// 非压缩
		$compression = 0;
		// 每行字节数必须为4的倍数，补齐。
		$extra = '';
		$padding = 4 - ceil($width / (8 / $bit)) % 4;
		if ($padding % 4 != 0){
			$extra = str_repeat("\0", $padding);
		}
		if(!$print){
			for ($j = $height - 1; $j >= 0; $j--){
				$i = 0;
				while ($i < $width){
					$bin = 0;
					$limit = $width - $i < 8 / $bit ? (8 / $bit - $width + $i) * $bit : 0;
	
					for ($k = 8 - $bit; $k >= $limit; $k -= $bit){
						$index = imagecolorat($im, $i, $j);
						$bin |= $index << $k;
						$i ++;
					}
					$bmp_data .= chr($bin);
				}
				$bmp_data .= $extra;
			}
		}else{
			for ($j = 0; $j < $height; $j++){
				$i = 0;
				while ($i < $width){
					$bin = 0;
					$limit = $width - $i < 8 / $bit ? (8 / $bit - $width + $i) * $bit : 0;
	
					for ($k = 8 - $bit; $k >= $limit; $k -= $bit){
						$index = imagecolorat($im, $i, $j);
						$bin |= $index << $k;
						$i ++;
					}
					$bmp_data .= chr($bin);
				}
				$bmp_data .= $extra;
			}
		}

		$bmp_data .= "\0\1";
		$size_quad = strlen($rgb_quad);
		$size_data = strlen($bmp_data);
		
		// 位图文件头
		$file_header = "BM" . pack("V3", 54 + $size_quad + $size_data, 0, 54 + $size_quad);
	 
		// 位图信息头
		$info_header = pack("V3v2V*", 0x28, $width, $height, 1, $bit, $compression, $size_data, 0, 0, $colors_num, 0);
	 
		// 写入文件
		if ($filename){
			$fp = fopen('uploads/img/'.$filename, "wb");
			fwrite($fp, $file_header);
			fwrite($fp, $info_header);
			fwrite($fp, $rgb_quad);
			fwrite($fp, $bmp_data);
			fclose($fp);        
			//return true;
		}
		if($print){
			return $file_header . $info_header.$rgb_quad.$bmp_data;;
		}
		// 浏览器输出
		header("Content-Type: image/bmp");
		echo $file_header . $info_header;
		echo $rgb_quad;
		echo $bmp_data;
		return true;
	}
	
	public function processingAsAPP($im){
		return $this->resizeImage($this->rotateImage($im));
	}
	
	public function rotateImage($im){
		$x = imagesx($im);
		$y = imagesy($im);
		if($x > $y){
			$im = imagerotate($im,-90,0);
		}
		return $im;
	}
	
	function resizeImage($im ,$maxwidth = 384,$maxheight = 0){
		$pic_width = imagesx($im);
		$pic_height = imagesy($im);

		if(($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight)){
		
			$resizewidth_tag =$maxwidth && $pic_width>$maxwidth;
			if($resizewidth_tag){
				$widthratio = $maxwidth/$pic_width;
			}
			
			$resizeheight_tag =$maxheight && $pic_height>$maxheight;
			if($resizeheight_tag){
				$heightratio = $maxheight/$pic_height;
			}
		
			if($resizewidth_tag && $resizeheight_tag){
				if($widthratio<$heightratio){
					$ratio = $widthratio;
				}else{
					$ratio = $heightratio;
				}
			}
		
			if($resizewidth_tag && !$resizeheight_tag){
				$ratio = $widthratio;
			}
			if($resizeheight_tag && !$resizewidth_tag){
				$ratio = $heightratio;
			}
		
			$newwidth = $pic_width * $ratio;
			$newheight = $pic_height * $ratio;
		
			if(function_exists("imagecopyresampled")){
				$newim = imagecreatetruecolor($newwidth,$newheight);
				imagecopyresampled($newim,$im,0,0,0,0,$newwidth,$newheight,$pic_width,$pic_height);
			}else{
				$newim = imagecreate($newwidth,$newheight);
				imagecopyresized($newim,$im,0,0,0,0,$newwidth,$newheight,$pic_width,$pic_height);
			}
			return $newim;
		}else{
			return $im;
		}
	} 
}
