<?php
class image{
	
	//生成bmp图片
	//生成bmp前 用imagefilter($image, IMG_FILTER_GRAYSCALE);进行灰度处理
	public function imagebmp($im, $filename = '', $bit = 8, $compression = 0){
		if (!in_array($bit, array(1, 4, 8, 16, 24, 32))){
			$bit = 8;
		}else if ($bit == 32){ // todo:32 bit
			$bit = 24;
		}
		$bits = pow(2, $bit);
	 
		// 调整调色板
		imagetruecolortopalette($im, true, $bits);
		$width  = imagesx($im);
		$height = imagesy($im);
		$colors_num = imagecolorstotal($im);
	 
		if ($bit <= 8){
			// 颜色索引
			$rgb_quad = '';
			for ($i = 0; $i < $colors_num; $i ++){
				$colors = imagecolorsforindex($im, $i);
				$rgb_quad .= chr($colors['blue']) . chr($colors['green']) . chr($colors['red']) . "\0";
			}
	 
			// 位图数据
			$bmp_data = '';
	 
			// 非压缩
			if ($compression == 0 || $bit < 8){
				if (!in_array($bit, array(1, 4, 8))){
					$bit = 8;
				}
				$compression = 0;
				 
				// 每行字节数必须为4的倍数，补齐。
				$extra = '';
				$padding = 4 - ceil($width / (8 / $bit)) % 4;
				if ($padding % 4 != 0){
					$extra = str_repeat("\0", $padding);
				}
	 
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
			}else if ($compression == 1 && $bit == 8){ // RLE8 压缩
				for ($j = $height - 1; $j >= 0; $j--){
					$last_index = "\0";
					$same_num   = 0;
					for ($i = 0; $i <= $width; $i ++){
						$index = imagecolorat($im, $i, $j);
						if ($index !== $last_index || $same_num > 255){
							if ($same_num != 0){
								$bmp_data .= chr($same_num) . chr($last_index);
							}
	 
							$last_index = $index;
							$same_num = 1;
						}else{
							$same_num ++;
						}
					}
					$bmp_data .= "\0\0";
				}
	 
				$bmp_data .= "\0\1";
			}
			$size_quad = strlen($rgb_quad);
			$size_data = strlen($bmp_data);
		}else{
		// 每行字节数必须为4的倍数，补齐。
			$extra = '';
			$padding = 4 - ($width * ($bit / 8)) % 4;
			if ($padding % 4 != 0){
				$extra = str_repeat("\0", $padding);
			}
			// 位图数据
			$bmp_data = '';
			for ($j = 0; $j <= $height - 1; $j --){
				for ($i = 0; $i < $width; $i ++){
					$index  = imagecolorat($im, $i, $j);
					$colors = imagecolorsforindex($im, $index);
					if ($bit == 16){
						$bin = 0 << $bit;
						$bin |= ($colors['red'] >> 3) << 10;
						$bin |= ($colors['green'] >> 3) << 5;
						$bin |= $colors['blue'] >> 3;
						$bmp_data .= pack("v", $bin);
					}else{
						$bmp_data .= pack("c*", $colors['blue'], $colors['green'], $colors['red']);
					}// todo: 32bit;
				}
				$bmp_data .= $extra;
			}
			$size_quad = 0;
			$size_data = strlen($bmp_data);
			$colors_num = 0;
		}
		// 位图文件头
		$file_header = "BM" . pack("V3", 54 + $size_quad + $size_data, 0, 54 + $size_quad);
	 
		// 位图信息头
		$info_header = pack("V3v2V*", 0x28, $width, $height, 1, $bit, $compression, $size_data, 0, 0, $colors_num, 0);
	 
		// 写入文件
		if ($filename != ''){
			$fp = fopen($filename, "wb");
			fwrite($fp, $file_header);
			fwrite($fp, $info_header);
			fwrite($fp, $rgb_quad);
			fwrite($fp, $bmp_data);
			fclose($fp);        
			return true;
		}
	 
		// 浏览器输出
		header("Content-Type: image/bmp");
		echo $file_header . $info_header;
		echo $rgb_quad;
		echo $bmp_data;
		return true;
	}
	
	//等比缩放
	function resizeImage($im,$maxwidth = '',$maxheight = ''){
		$pic_width = imagesx($im);
		$pic_height = imagesy($im);

		if(($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight)){
			if($maxwidth && $pic_width>$maxwidth){
				$widthratio = $maxwidth/$pic_width;
				$resizewidth_tag = true;
			}
		
			if($maxheight && $pic_height>$maxheight){
				$heightratio = $maxheight/$pic_height;
				$resizeheight_tag = true;
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
