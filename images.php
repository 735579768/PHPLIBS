<?php
/**
 * 生成缩略图
 * @author yangzhiguo0903@163.com
 * @param string     源图绝对完整地址{带文件名及后缀名}
 * @param string     目标图绝对完整地址{带文件名及后缀名}
 * @param int        缩略图宽{0:此时目标高度不能为0，目标宽度为源图宽*(目标高度/源图高)}
 * @param int        缩略图高{0:此时目标宽度不能为0，目标高度为源图高*(目标宽度/源图宽)}
 * @param bool createimg  true输出到浏览器 false输出到文件
 * @param bool proportion  剪切true  缩放false补充白背景
 * @return boolean
 */
function imgagethumb($src_img, $dst_img, $width = 75, $height = 75,$createimg=true,$proportion = false)
{
    if(!is_file($src_img))
    {
        return false;
    }
	$to='';
	if(!empty($dst_img)){
		$ot =strtolower(pathinfo($dst_img, PATHINFO_EXTENSION));
		$dirname =pathinfo($dst_img, PATHINFO_DIRNAME);
		if(!file_exists($dirname))createFolder($dirname);
	}else{
		$ot =strtolower(pathinfo($src_img, PATHINFO_EXTENSION));
		}
    $otfunc = 'image' . ($ot == 'jpg' ? 'jpeg' : $ot);
    $srcinfo = getimagesize($src_img);
    $src_w = $srcinfo[0];
    $src_h = $srcinfo[1];
	//if($src_h<intval(C('THUMB_HEIGHT')) || $src_w<intval(C('THUMB_WIDTH'))){return false;}
    $type  = strtolower(substr(image_type_to_extension($srcinfo[2]), 1));
    $createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);
 	if(!$createimg)header('content-type:image/'.($type == 'jpg' ? 'jpeg' : $type));
    $dst_h = $height;
    $dst_w = $width;
    $x = $y = 0;

	$zfxbili=0.00;
	//如果缩略图是正方形，算出源图的宽高比例如果接近正方形的话就生成正方形的图片
	if($width=$height){
		$zfxbili=doubleval($src_w)/doubleval($src_h);
	}

    //源图和缩略图一样大小的情况直接复制
    if($src_w==$width && $src_h==$height){
    	return copy($src_img,$dst_img);
    }
	$src_image=$createfun($src_img);
	
	$cropped_image = imagecreatetruecolor($width, $height);
	
	if($width==$height && 2>$zfxbili && $zfxbili>=0.5){
		$white = imagecolorallocate($cropped_image, 255, 255, 255);
		imagefill($cropped_image, 0, 0, $white);
		}else{
		imagesavealpha($src_image,true);//这里很重要;
		$alpha= imagecolorallocatealpha($cropped_image,255,255,255,127);
		imagealphablending($cropped_image,false);//这里很重要,意思是不合并颜色,直接用$img图像颜色替换,包括透明色;
		imagesavealpha($cropped_image,true);//这里很重要,意思是不要丢了$thumb图像的透明色
		imagefill($cropped_image, 0, 0, $alpha);
		}
//剪切图片(先从源图片中按缩略图的比例剪切出一个区域再缩放到缩略图中)
if($proportion){
	//源图比缩略图大的情况
	if($src_w>$width && $src_h>$height){
		//缩略图宽高一样的情况
		if($width==$height){
	 		//源图类似正方形2:1 比例 宽大于高 
			if($zfxbili>1 && $zfxbili<2){
				//算出缩放的实际高度
				$ah=$src_h*$width/$src_w;
				//算出实际的y轴
				$ay=($height-$ah)/2;
				imagecopyresampled($cropped_image, $src_image,0,$ay, 0, 0, $width,$ah, $src_w, $src_h);
				if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
				imagedestroy($cropped_image);
				imagedestroy($src_image);
				return true;
			}
			//源图类似正方形1:2 比例 高大于宽 
			if($zfxbili>=0.5 && $zfxbili<1){
				//算出缩放的实际宽度
				$aw=$src_w*$height/$src_h;
				//算出实际的x轴
				$ax=($width-$aw)/2;
				imagecopyresampled($cropped_image, $src_image,$ax,0, 0, 0, $aw, $height, $src_w, $src_h);
				if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
				imagedestroy($cropped_image);
				imagedestroy($src_image);
				return true;
			}
	 		//源图宽高一样
			if($src_h==$src_w){
				imagecopyresampled($cropped_image, $src_image,0,0, 0, 0, $width, $height, $src_w, $src_h);
				if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
				imagedestroy($cropped_image);
				imagedestroy($src_image);
				return true;
			}
			//源图宽大于高
			if($src_w>$src_h){
				$ww=$src_h;
				$_x=($src_w-$src_h)/2; 
				imagecopyresampled($cropped_image, $src_image,0,0, $_x, 0, $width, $height, $ww, $ww);
				if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
				imagedestroy($cropped_image);
				imagedestroy($src_image);
				return true;
			}else{
			//源图宽小于等于高
				$ww=$src_w;
				$_y=($src_h-$src_w)/2;
				imagecopyresampled($cropped_image, $src_image,0,0, 0, $_y, $width, $height, $ww, $ww);
				if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
				imagedestroy($cropped_image);
				imagedestroy($src_image);
				return true;
			}
		}else{//缩略图宽高不一样的情况
				//缩略图宽大于高
				if($width>$height){
				 	$bili=($width/$height);
					$hh=$src_w/$bili;
					$_y=($src_h-$hh)/2;
				 	if($src_w>$src_h){
						//查找合适剪切的高
						$tem_w=$src_w;
						$tem_h=0;
						while(true){
							$tem_h=($tem_w/$bili);
							if($tem_h<$src_h){break;}else{$tem_w--;}
						}
						$_x=($src_w-$tem_w)/2;
						$_y=($src_h-$tem_h)/2;
						imagecopyresampled($cropped_image, $src_image,0,0, $_x, $_y, $width, $height, $tem_w, $tem_h);
						if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
						imagedestroy($cropped_image);
						imagedestroy($src_image);
						return true;
					}else{
						imagecopyresampled($cropped_image, $src_image,0,0, 0, $_y, $width, $height, $src_w, $hh);
						if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
						imagedestroy($cropped_image);
						imagedestroy($src_image);
						return true;
					}
				}else{
				//缩略图宽小于等于高
					$bili=($height/$width);
					$ww=$src_h/$bili;
					$_x=($src_w-$ww)/2;
					 	if($src_h>$src_w){
							//查找合适剪切的高
							$tem_h=$src_h;
							$tem_w=0;
							while(true){
								$tem_w=($tem_h/$bili);
								if($tem_w<$src_w){break;}else{$tem_h--;}
							}
							$_x=($src_w-$tem_w)/2;
							$_y=($src_h-$tem_h)/2;
							imagecopyresampled($cropped_image, $src_image,0,0, $_x, 0, $width, $height, $tem_w, $tem_h);
							if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
							imagedestroy($cropped_image);
							imagedestroy($src_image);
							return true;
						}else{
							imagecopyresampled($cropped_image, $src_image,0,0, $_x, 0, $width, $height, $ww, $src_h);
							if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
							imagedestroy($cropped_image);
							imagedestroy($src_image);
							return true;
						}
				}
			}
	}else{
	//源图任意一个长度比缩略图小的情况
		if($src_h<=$height && $src_w<=$width){
			//源图宽高都比缩略图小
			$_x=($width-$src_w)/2;
			$_y=($height-$src_h)/2;
			imagecopyresampled($cropped_image, $src_image,$_x, $_y,0, 0, $src_w, $src_h, $src_w, $src_h);
			if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
			imagedestroy($cropped_image);
			imagedestroy($src_image);
			return true;
		}else if($src_h<=$height){
			//源图高比缩略图小
			$hh=$width/($src_w/$src_h);
			$_y=($height-$hh)/2;
			imagecopyresampled($cropped_image, $src_image,0, $_y,0, 0, $width, $src_h, $src_w, $src_h);
			if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
			imagedestroy($cropped_image);
			imagedestroy($src_image);
			return true;
		}else{
			//源图宽比缩略图小
			$ww=$height/($src_h/$src_w);
			$_x=($width-$ww)/2;
			imagecopyresampled($cropped_image, $src_image,$_x, 0,0, 0, $src_w, $height,$src_w, $src_h);
			if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
			imagedestroy($cropped_image);
			imagedestroy($src_image);
			return true;
		}
	}
}else{
//缩放图片(把源图片完整的缩放到缩略图中)
	//缩略图宽高一样
	if($width==$height){
		//源图宽大于高
		if($src_w>$src_h){
			$ah=$src_h*$width/$src_w;
			$ay=($height-$ah)/2;
			imagecopyresampled($cropped_image, $src_image,0,$ay, 0,0, $width, $ah,$src_w, $src_h);
			if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
			imagedestroy($cropped_image);
			imagedestroy($src_image);
			return true;
		}
		//源图宽小于高
		if($src_w<$src_h){
			$aw=$src_w*$height/$src_h;
			$ax=($width-$aw)/2;
			imagecopyresampled($cropped_image, $src_image,$ax,0, 0,0, $aw, $height,$src_w, $src_h);
			if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
			imagedestroy($cropped_image);
			imagedestroy($src_image);
			return true;
		}
		//源图宽等于高
		if($src_w==$src_h){
			imagecopyresampled($cropped_image, $src_image,0,0,0, 0, $width, $height,$src_w, $src_h);
			if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
			imagedestroy($cropped_image);
			imagedestroy($src_image);
			return true;
		}
	}
	//缩略图宽大于高
	if($width>$height){
		//源图宽大于高
		if($src_w>$src_h){
			$ah=$src_h*$width/$src_w;
			$ay=($height-$ah)/2;
			imagecopyresampled($cropped_image, $src_image,0,$ay, 0,0, $width, $ah,$src_w, $src_h);
			if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
			imagedestroy($cropped_image);
			imagedestroy($src_image);
			return true;
		}
		//源图宽小于高
		if($src_w<=$src_h){
			$aw=$src_w*$height/$src_h;
			$ax=($width-$aw)/2;
			imagecopyresampled($cropped_image, $src_image,$ax,0, 0,0, $aw, $height,$src_w, $src_h);
			if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
			imagedestroy($cropped_image);
			imagedestroy($src_image);
			return true;
		}
		//源图宽等于高
		//if($src_w==$src_h){
		//	imagecopyresampled($cropped_image, $src_image,0,0,0, 0, $width, $height,$src_w, $src_h);
		//}
	}
	//缩略图宽小于高
	if($width<$height){
		//源图宽小于高
		if($src_w<=$src_h){
			$aw=$src_w*$height/$src_h;
			$ax=($width-$aw)/2;
			imagecopyresampled($cropped_image, $src_image,$ax,0, 0,0, $aw, $height,$src_w, $src_h);
			if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
			imagedestroy($cropped_image);
			imagedestroy($src_image);
			return true;
		}
		//源图宽大于高
		if($src_w>$src_h){
			$ah=$src_h*$width/$src_w;
			$ay=($height-$ah)/2;
			imagecopyresampled($cropped_image, $src_image,0,$ay, 0,0, $width, $ah,$src_w, $src_h);
			if($createimg){$otfunc($cropped_image, $dst_img);}else{$otfunc($cropped_image);}
			imagedestroy($cropped_image);
			imagedestroy($src_image);
			return true;
		}

	}
}
	return true;
}



/**
 *给目标图片添加水印
//使用示例
markimg(array(
	'dst'=>'./images/1.jpg',//原始图像
	'src'=>'./images/ico.png',//水印图像
	'pos'=>'center'//水印位置('left,right,center')
));
 */
function markimg($info=array(
					'det'=>null,
					'src'=>null,
					'pos'=>'right'
					)){
		//原始图像 
       $dst =$info['dst'] ;//"./images/tu.png"; //注意图片路径要正确 
	   //水印图像 
       $src = $info['src'];//"./images/ico.png"; //注意路径要写对
		//水印在原图的位置比例
		$pos=$info['pos'];
       //得到原始图片信息 
       $dst_info = getimagesize($dst);
	   //检查图片大小符合添加水印的条件不
	   $tj=explode('X',C('SHUIYIN_TIAOJIAN'));  
       if(!($dst_info[0]>=intval($tj[0]) && $dst_info[1]>=intval($tj[1])))return false;
	   switch ($dst_info[2]) 
       { 
        case 1: $dst_im =imagecreatefromgif($dst);break;
        case 2: $dst_im =imagecreatefromjpeg($dst);break;
        case 3: $dst_im =imagecreatefrompng($dst);break; 
        case 6: $dst_im =imagecreatefromwbmp($dst);break; 
        default: return("不支持的文件类型1"); 
       } 
 
       $src_info = getimagesize($src); 
       switch ($src_info[2]) 
       { 
        case 1: $src_im =imagecreatefromgif($src);break;    
        case 2: $src_im =imagecreatefromjpeg($src);break;   
        case 3: $src_im =imagecreatefrompng($src);break;
        case 6: $src_im =imagecreatefromwbmp($src);break; 
        default: return("不支持的文件类型1"); 
       } 
       //支持png本身透明度的方式
	   switch($pos){
		   case 'right':
		   //右下角
	  	   imagecopy($dst_im,$src_im,$dst_info[0]-$src_info[0]-10,$dst_info[1]-$src_info[1]-10,0,0,$src_info[0],$src_info[1]);
		   break;
		   case 'center':
		   //正中间
			imagecopy($dst_im,$src_im,($dst_info[0]-$src_info[0])/2,($dst_info[1]-$src_info[1])/2,0,0,$src_info[0],$src_info[1]);
			break; 
		   default :
		   //左下角
			imagecopy($dst_im,$src_im,10,$dst_info[1]-$src_info[1]-10,0,0,$src_info[0],$src_info[1]);
		   }
	   //保存图片 
       switch ($dst_info[2]){ 
        case 1: imagegif($dst_im,$dst);break;
        case 2: imagejpeg($dst_im,$dst);break; 
        case 3: imagepng($dst_im,$dst);break;    
        case 6: imagewbmp($dst_im,$dst);break; 
        default: 
        return("不支持的文件类型2"); 
       } 
       imagedestroy($dst_im); 
       imagedestroy($src_im);   	
	}
/**
 * BMP 创建函数
 * @author simon
 * @param string $filename path of bmp file
 * @example who use,who knows
 * @return resource of GD
 */
function imagecreatefrombmp( $filename ){
    if ( !$f1 = fopen( $filename, "rb" ) )
        return FALSE;
     
    $FILE = unpack( "vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread( $f1, 14 ) );
    if ( $FILE['file_type'] != 19778 )
        return FALSE;
     
    $BMP = unpack( 'Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' . '/Vcompression/Vsize_bitmap/Vhoriz_resolution' . '/Vvert_resolution/Vcolors_used/Vcolors_important', fread( $f1, 40 ) );
    $BMP['colors'] = pow( 2, $BMP['bits_per_pixel'] );
    if ( $BMP['size_bitmap'] == 0 )
        $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
    $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel'] / 8;
    $BMP['bytes_per_pixel2'] = ceil( $BMP['bytes_per_pixel'] );
    $BMP['decal'] = ($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
    $BMP['decal'] -= floor( $BMP['width'] * $BMP['bytes_per_pixel'] / 4 );
    $BMP['decal'] = 4 - (4 * $BMP['decal']);
    if ( $BMP['decal'] == 4 )
        $BMP['decal'] = 0;
     
    $PALETTE = array();
    if ( $BMP['colors'] < 16777216 ){
        $PALETTE = unpack( 'V' . $BMP['colors'], fread( $f1, $BMP['colors'] * 4 ) );
    }
     
    $IMG = fread( $f1, $BMP['size_bitmap'] );
    $VIDE = chr( 0 );
     
    $res = imagecreatetruecolor( $BMP['width'], $BMP['height'] );
    $P = 0;
    $Y = $BMP['height'] - 1;
    while( $Y >= 0 ){
        $X = 0;
        while( $X < $BMP['width'] ){
            if ( $BMP['bits_per_pixel'] == 32 ){
                $COLOR = unpack( "V", substr( $IMG, $P, 3 ) );
                $B = ord(substr($IMG, $P,1));
                $G = ord(substr($IMG, $P+1,1));
                $R = ord(substr($IMG, $P+2,1));
                $color = imagecolorexact( $res, $R, $G, $B );
                if ( $color == -1 )
                    $color = imagecolorallocate( $res, $R, $G, $B );
                $COLOR[0] = $R*256*256+$G*256+$B;
                $COLOR[1] = $color;
            }elseif ( $BMP['bits_per_pixel'] == 24 )
                $COLOR = unpack( "V", substr( $IMG, $P, 3 ) . $VIDE );
            elseif ( $BMP['bits_per_pixel'] == 16 ){
                $COLOR = unpack( "n", substr( $IMG, $P, 2 ) );
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            }elseif ( $BMP['bits_per_pixel'] == 8 ){
                $COLOR = unpack( "n", $VIDE . substr( $IMG, $P, 1 ) );
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            }elseif ( $BMP['bits_per_pixel'] == 4 ){
                $COLOR = unpack( "n", $VIDE . substr( $IMG, floor( $P ), 1 ) );
                if ( ($P * 2) % 2 == 0 )
                    $COLOR[1] = ($COLOR[1] >> 4);
                else
                    $COLOR[1] = ($COLOR[1] & 0x0F);
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            }elseif ( $BMP['bits_per_pixel'] == 1 ){
                $COLOR = unpack( "n", $VIDE . substr( $IMG, floor( $P ), 1 ) );
                if ( ($P * 8) % 8 == 0 )
                    $COLOR[1] = $COLOR[1] >> 7;
                elseif ( ($P * 8) % 8 == 1 )
                    $COLOR[1] = ($COLOR[1] & 0x40) >> 6;
                elseif ( ($P * 8) % 8 == 2 )
                    $COLOR[1] = ($COLOR[1] & 0x20) >> 5;
                elseif ( ($P * 8) % 8 == 3 )
                    $COLOR[1] = ($COLOR[1] & 0x10) >> 4;
                elseif ( ($P * 8) % 8 == 4 )
                    $COLOR[1] = ($COLOR[1] & 0x8) >> 3;
                elseif ( ($P * 8) % 8 == 5 )
                    $COLOR[1] = ($COLOR[1] & 0x4) >> 2;
                elseif ( ($P * 8) % 8 == 6 )
                    $COLOR[1] = ($COLOR[1] & 0x2) >> 1;
                elseif ( ($P * 8) % 8 == 7 )
                    $COLOR[1] = ($COLOR[1] & 0x1);
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            }else
                return FALSE;
            imagesetpixel( $res, $X, $Y, $COLOR[1] );
            $X++;
            $P += $BMP['bytes_per_pixel'];
        }
        $Y--;
        $P += $BMP['decal'];
    }
    fclose( $f1 );
     
    return $res;
}
/** 
* 创建bmp格式图片 
* 
* @author: legend(legendsky@hotmail.com) 
* @link: http://www.ugia.cn/?p=96 
* @description: create Bitmap-File with GD library 
* @version: 0.1 
* 
* @param resource $im          图像资源 
* @param string   $filename    如果要另存为文件，请指定文件名，为空则直接在浏览器输出 
* @param integer  $bit         图像质量(1、4、8、16、24、32位) 
* @param integer  $compression 压缩方式，0为不压缩，1使用RLE8压缩算法进行压缩 
* 
* @return integer 
*/ 
function imagebmp(&$im, $filename = '', $bit = 8, $compression = 0) 
{ 
    if (!in_array($bit, array(1, 4, 8, 16, 24, 32))) 
    { 
        $bit = 8; 
    } 
    else if ($bit == 32) // todo:32 bit 
    { 
        $bit = 24; 
    } 
  
    $bits = pow(2, $bit); 
    
    // 调整调色板 
    imagetruecolortopalette($im, true, $bits); 
    $width  = imagesx($im); 
    $height = imagesy($im); 
    $colors_num = imagecolorstotal($im); 
    
    if ($bit <= 8) 
    { 
        // 颜色索引 
        $rgb_quad = ''; 
        for ($i = 0; $i < $colors_num; $i ++) 
        { 
            $colors = imagecolorsforindex($im, $i); 
            $rgb_quad .= chr($colors['blue']) . chr($colors['green']) . chr($colors['red']) . "\0"; 
        } 
        
        // 位图数据 
        $bmp_data = ''; 
  
        // 非压缩 
        if ($compression == 0 || $bit < 8) 
        { 
            if (!in_array($bit, array(1, 4, 8))) 
            { 
                $bit = 8; 
            } 
  
            $compression = 0; 
            
            // 每行字节数必须为4的倍数，补齐。 
            $extra = ''; 
            $padding = 4 - ceil($width / (8 / $bit)) % 4; 
            if ($padding % 4 != 0) 
            { 
                $extra = str_repeat("\0", $padding); 
            } 
            
            for ($j = $height - 1; $j >= 0; $j --) 
            { 
                $i = 0; 
                while ($i < $width) 
                { 
                    $bin = 0; 
                    $limit = $width - $i < 8 / $bit ? (8 / $bit - $width + $i) * $bit : 0; 
  
                    for ($k = 8 - $bit; $k >= $limit; $k -= $bit) 
                    { 
                        $index = imagecolorat($im, $i, $j); 
                        $bin |= $index << $k; 
                        $i ++; 
                    } 
  
                    $bmp_data .= chr($bin); 
                } 
                
                $bmp_data .= $extra; 
            } 
        } 
        // RLE8 压缩 
        else if ($compression == 1 && $bit == 8) 
        { 
            for ($j = $height - 1; $j >= 0; $j --) 
            { 
                $last_index = "\0"; 
                $same_num   = 0; 
                for ($i = 0; $i <= $width; $i ++) 
                { 
                    $index = imagecolorat($im, $i, $j); 
                    if ($index !== $last_index || $same_num > 255) 
                    { 
                        if ($same_num != 0) 
                        { 
                            $bmp_data .= chr($same_num) . chr($last_index); 
                        } 
  
                        $last_index = $index; 
                        $same_num = 1; 
                    } 
                    else 
                    { 
                        $same_num ++; 
                    } 
                } 
  
                $bmp_data .= "\0\0"; 
            } 
            
            $bmp_data .= "\0\1"; 
        } 
  
        $size_quad = strlen($rgb_quad); 
        $size_data = strlen($bmp_data); 
    } 
    else 
    { 
        // 每行字节数必须为4的倍数，补齐。 
        $extra = ''; 
        $padding = 4 - ($width * ($bit / 8)) % 4; 
        if ($padding % 4 != 0) 
        { 
            $extra = str_repeat("\0", $padding); 
        } 
  
        // 位图数据 
        $bmp_data = ''; 
  
        for ($j = $height - 1; $j >= 0; $j --) 
        { 
            for ($i = 0; $i < $width; $i ++) 
            { 
                $index  = imagecolorat($im, $i, $j); 
                $colors = imagecolorsforindex($im, $index); 
                
                if ($bit == 16) 
                { 
                    $bin = 0 << $bit; 
  
                    $bin |= ($colors['red'] >> 3) << 10; 
                    $bin |= ($colors['green'] >> 3) << 5; 
                    $bin |= $colors['blue'] >> 3; 
  
                    $bmp_data .= pack("v", $bin); 
                } 
                else 
                { 
                    $bmp_data .= pack("c*", $colors['blue'], $colors['green'], $colors['red']); 
                } 
                
                // todo: 32bit; 
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
    if ($filename != '') 
    { 
        $fp = fopen("test.bmp", "wb"); 
  
        fwrite($fp, $file_header); 
        fwrite($fp, $info_header); 
        fwrite($fp, $rgb_quad); 
        fwrite($fp, $bmp_data); 
        fclose($fp); 
  
        return 1; 
    } 
    
    // 浏览器输出 
    header("Content-Type: image/bmp"); 
    echo $file_header . $info_header; 
    echo $rgb_quad; 
    echo $bmp_data; 
    
    return 1; 
} 