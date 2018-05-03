<?php
//你好 Léo & Luka
/**
 * File: SimpleImage.php
 * Author: Simon Jarvis (Modified by Bruno Martin)
 * Date: March 2nd, 2015
 * Modified by: Miguel Fermín
 * Based in: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
 * https://gist.github.com/miguelxt/908143
 * 
 * This program is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License 
 * as published by the Free Software Foundation; either version 2 
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
 * GNU General Public License for more details: 
 * http://www.gnu.org/licenses/gpl.html
 */

namespace libs;

use \Exception;

class SimpleImageOld {

	public $image;
	public $image_type;
	public $image_name;

	public function __construct($filename = null){
		if (!empty($filename)) {
			$this->load($filename);
		}
	}

	public function load($filename) {
		if(filesize($filename)>=12){
			$image_info = getimagesize($filename);
			$this->image_type = $image_info[2];
		} else {
			$this->image_type = IMAGETYPE_JPEG;
		}
		if( $this->image_type == IMAGETYPE_JPEG ) {
			$this->image = imagecreatefromjpeg($filename);
		} else if( $this->image_type == IMAGETYPE_GIF ) {
			$this->image = imagecreatefromgif($filename);
		} else if( $this->image_type == IMAGETYPE_PNG ) {
			$this->image = imagecreatefrompng($filename);
		} else if( $this->image_type == IMAGETYPE_WBMP ) {
			$this->image = imagecreatefromwbmp($filename);
		} else if( $this->image_type == IMAGETYPE_BMP ) {
			$this->image = $this->imagecreatefrombmp($filename);
		} else {
			throw new Exception("The file you're trying to open is not supported");
			return false;
		}
		$this->image_name = $filename;
		imagealphablending($this->image, false);
		imagesavealpha($this->image, true);
	}

	public function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=0604) {
		imagealphablending($this->image, false); //Transparency Step 4/5
		imagesavealpha($this->image, true);//Transparency Step 5/5
		if( $image_type == IMAGETYPE_JPEG ) {
			imagejpeg($this->image,$filename,$compression);
		} else if( $image_type == IMAGETYPE_GIF ) {
			imagegif($this->image,$filename);
		} else if( $image_type == IMAGETYPE_PNG ) {
			imagepng($this->image,$filename);
		} else if( $image_type == IMAGETYPE_WBMP ) {
			imagejpeg($this->image,$filename,$compression);
		} else if( $image_type == IMAGETYPE_BMP ) {
			imagejpeg($this->image,$filename,$compression);
		}
		if( $permissions != null) {
			chmod($filename,$permissions);
		}
		imagedestroy($this->image); //destroy the picture to free some memory
	}

	public function destroy(){
		imagedestroy($this->image); //destroy the picture to free some memory
	}

	public function output($image_type=IMAGETYPE_JPEG, $filename=NULL, $compression=75) {
		$app = \Slim\Slim::getInstance();
		$filename = $this->image_name;
		$app->response->headers->set('Cache-Control', 'no-cache, must-revalidate');
		$app->response->headers->set('Expires', 'Fri, 12 Aug 2011 14:57:00 GMT');
		if( $image_type == IMAGETYPE_JPEG ) {
			$app->response->headers->set('Content-Type', 'image/jpeg');
			imagejpeg($this->image,$filename,$compression);
		} else if( $image_type == IMAGETYPE_GIF ) {
			$app->response->headers->set('Content-Type', 'image/gif');
			imagegif($this->image,$filename);         
		} else if( $image_type == IMAGETYPE_PNG ) {
			$app->response->headers->set('Content-Type', 'image/png');
			imagepng($this->image,$filename);
		} else if( $image_type == IMAGETYPE_WBMP ) {
			$app->response->headers->set('Content-Type', 'image/jpeg');
			imagejpeg($this->image,$filename,$compression);
		} else if( $image_type == IMAGETYPE_BMP ) {
			$app->response->headers->set('Content-Type', 'image/jpeg');
			imagejpeg($this->image,$filename,$compression);
		}
	}

	public function format($filename) {
		if(filesize($filename)>=12){
			$image_info = getimagesize($filename);
			$this->image_type = $image_info[2];
		} else {
			$this->image_type = IMAGETYPE_JPEG;
		}
		if( $this->image_type == IMAGETYPE_JPEG ) {
			return "jpg";
		} else if( $this->image_type == IMAGETYPE_GIF ) {
			return "gif";
		} else if( $this->image_type == IMAGETYPE_PNG ) {
			return "png";
		} else if( $this->image_type == IMAGETYPE_WBMP ) {
			return "wbmp";
		} else if( $this->image_type == IMAGETYPE_BMP ) {
			return "bmp";
		} else {
			return false;
		}
	}
	
	public function type($filename) {
		if(filesize($filename)>=12){
			$image_info = getimagesize($filename);
			return $image_info[2];
		} else {
			return IMAGETYPE_JPEG;
		}
	}

	public function imagecreatefrombmp($src, $dest = false) {
		
		$dest = tempnam("/tmp", "GD");
		
		if(!($src_f = fopen($src, "rb"))) {
			return false;
		}
		if(!($dest_f = fopen($dest, "wb"))) {
			return false;
		}
		$header = unpack("vtype/Vsize/v2reserved/Voffset", fread($src_f,14));
		$info = unpack("Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant", fread($src_f, 40));
		
		extract($info);
		extract($header);
		
		if($type != 0x4D42) { // signature "BM"
			return false;
		}
		
		$palette_size = $offset - 54;
		$ncolor = $palette_size / 4;
		$gd_header = "";
		// true-color vs. palette
		$gd_header .= ($palette_size == 0) ? "\xFF\xFE" : "\xFF\xFF";
		$gd_header .= pack("n2", $width, $height);
		$gd_header .= ($palette_size == 0) ? "\x01" : "\x00";
		if($palette_size) {
			$gd_header .= pack("n", $ncolor);
		}
		// no transparency
		$gd_header .= "\xFF\xFF\xFF\xFF";
		
		fwrite($dest_f, $gd_header);
		
		if($palette_size) {
			$palette = fread($src_f, $palette_size);
			$gd_palette = "";
			$j = 0;
			while($j < $palette_size) {
				$b = $palette{$j++};
				$g = $palette{$j++};
				$r = $palette{$j++};
				$a = $palette{$j++};
				$gd_palette .= "$r$g$b$a";
			}
			$gd_palette .= str_repeat("\x00\x00\x00\x00", 256 - $ncolor);
			fwrite($dest_f, $gd_palette);
		}
		
		$scan_line_size = (($bits * $width) + 7) >> 3;
		$scan_line_align = ($scan_line_size & 0x03) ? 4 - ($scan_line_size & 0x03) : 0;
		
		for($i = 0, $l = $height - 1; $i < $height; $i++, $l--) {
			// BMP stores scan lines starting from bottom
			fseek($src_f, $offset + (($scan_line_size + $scan_line_align) * $l));
			$scan_line = fread($src_f, $scan_line_size);
			if($bits == 24) {
				$gd_scan_line = "";
				$j = 0;
				while($j < $scan_line_size) {
					$b = $scan_line{$j++};
					$g = $scan_line{$j++};
					$r = $scan_line{$j++};
					$gd_scan_line .= "\x00$r$g$b";
				}
			}
			else if($bits == 8) {
				$gd_scan_line = $scan_line;
			}
			else if($bits == 4) {
				$gd_scan_line = "";
				$j = 0;
				while($j < $scan_line_size) {
					$byte = ord($scan_line{$j++});
					$p1 = chr($byte >> 4);
					$p2 = chr($byte & 0x0F);
					$gd_scan_line .= "$p1$p2";
				}
				$gd_scan_line = mb_substr($gd_scan_line, 0, $width);
			}
			else if($bits == 1) {
				$gd_scan_line = "";
				$j = 0;
				while($j < $scan_line_size) {
					$byte = ord($scan_line{$j++});
					$p1 = chr((int) (($byte & 0x80) != 0));
					$p2 = chr((int) (($byte & 0x40) != 0));
					$p3 = chr((int) (($byte & 0x20) != 0));
					$p4 = chr((int) (($byte & 0x10) != 0));
					$p5 = chr((int) (($byte & 0x08) != 0));
					$p6 = chr((int) (($byte & 0x04) != 0));
					$p7 = chr((int) (($byte & 0x02) != 0));
					$p8 = chr((int) (($byte & 0x01) != 0));
					$gd_scan_line .= "$p1$p2$p3$p4$p5$p6$p7$p8";
				}
				$gd_scan_line = mb_substr($gd_scan_line, 0, $width);
			}
			
			fwrite($dest_f, $gd_scan_line);
		}
		fclose($src_f);
		fclose($dest_f);
		
		$this->image = imagecreatefromgd($dest);
		unlink($dest);
	}

	public function getWidth() {
		return imagesx($this->image);
	}

	public function getHeight() {
		return imagesy($this->image);
	}

	public function resizeToHeight($height) {
		$ratio = $height / $this->getHeight();
		$width = round($this->getWidth() * $ratio);
		$this->resize($width,$height);
	}

	public function resizeToWidth($width) {
		$ratio = $width / $this->getWidth();
		$height = round($this->getheight() * $ratio);
		$this->resize($width,$height);
	}

	public function square($size) {
		$new_image = imagecreatetruecolor($size, $size);

		if ($this->getWidth() > $this->getHeight()) {
			$this->resizeToHeight($size);
			
			imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
			imagealphablending($new_image, false);
			imagesavealpha($new_image, true);
			imagecopy($new_image, $this->image, 0, 0, ($this->getWidth() - $size) / 2, 0, $size, $size);

		} else {
			$this->resizeToWidth($size);
			
			imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
			imagealphablending($new_image, false);
			imagesavealpha($new_image, true);
			imagecopy($new_image, $this->image, 0, 0, 0, ($this->getHeight() - $size) / 2, $size, $size);

		}

		$this->image = $new_image;
	}

	public function scale($scale) {
		$width = $this->getWidth() * $scale/100;
		$height = $this->getHeight() * $scale/100; 
		$this->resize($width,$height);
	}

	public function resize($width,$height) {
		$new_image = imagecreatetruecolor($width, $height);
		imagealphablending($new_image, true); //Transparency Step 1/5
		$transparent = imagecolorallocatealpha( $new_image, 0, 0, 0, 127 ); //Transparency Step 2/5
		imagefill( $new_image, 0, 0, $transparent ); //Transparency Step 3/5
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->image = $new_image;  
	}

	public function cut($x, $y, $width, $height) {
		$new_image = imagecreatetruecolor($width, $height);	

		imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
		imagealphablending($new_image, false);
		imagesavealpha($new_image, true);

		imagecopy($new_image, $this->image, 0, 0, $x, $y, $width, $height);

		$this->image = $new_image;
	}

	public function maxarea($width, $height = null)	{
		$height = $height ? $height : $width;
		
		if ($this->getWidth() > $width) {
			$this->resizeToWidth($width);
		}
		if ($this->getHeight() > $height) {
			$this->resizeToheight($height);
		}
	}

	public function minarea($width, $height = null)	{
		$height = $height ? $height : $width;
		
		if ($this->getWidth() < $width) {
			$this->resizeToWidth($width);
		}
		if ($this->getHeight() < $height) {
			$this->resizeToheight($height);
		}
	}

	public function cutFromCenter($width, $height) {
		
		if ($width < $this->getWidth() && $width > $height) {
			$this->resizeToWidth($width);
		}
		if ($height < $this->getHeight() && $width < $height) {
			$this->resizeToHeight($height);
		}
		
		$x = ($this->getWidth() / 2) - ($width / 2);
		$y = ($this->getHeight() / 2) - ($height / 2);
		
		return $this->cut($x, $y, $width, $height);
	}

	public function maxareafill($width, $height, $red = 0, $green = 0, $blue = 0) {
		$this->maxarea($width, $height);
		$new_image = imagecreatetruecolor($width, $height); 
		$color_fill = imagecolorallocate($new_image, $red, $green, $blue);
		imagefill($new_image, 0, 0, $color_fill);
		imagecopyresampled(	$new_image, 
							$this->image, 
							floor(($width - $this->getWidth())/2), 
							floor(($height-$this->getHeight())/2), 
							0, 0, 
							$this->getWidth(), 
							$this->getHeight(), 
							$this->getWidth(), 
							$this->getHeight()
						); 
		$this->image = $new_image;
	}

	//$cap_pic: file of the number to add
	//$num: position number between 1 and the total of the captcha number
	public function addcaptcha($cap_pic, $num, $total=4) {
		if($num>=1 && $num<=$total){
			$spaceW = floor($this->getWidth()/$total);
			$spaceH = $this->getHeight();
			
			$src_im = new SimpleImage();
			$src_im->load($cap_pic);
			
			$degrees = rand(-20,20);
			$src_im->rotation($degrees); //Random rotte
			
			$sizeratio = rand(70,95)/100;
			$maxW = floor($sizeratio*$spaceW);
			$maxH = floor($sizeratio*$spaceH);
			$src_im->resizelimit($maxW,$maxH); //Random scale
			
			$dst_x = rand($spaceW*($num-1),$spaceW*$num-$src_im->getWidth()); //Random X position
			$dst_y = rand(0,$spaceH-$src_im->getHeight()); //Random Y position
			$src_x = 0;
			$src_y = 0;
			$src_w = $src_im->getWidth();
			$src_h = $src_im->getHeight();
			
			// creating a cut resource 
			$cut = imagecreatetruecolor($src_w, $src_h);
			imagealphablending($cut, true); //Transparency Step 1/5
			$transparent = imagecolorallocatealpha( $cut, 0, 0, 0, 127 ); //Transparency Step 2/5
			imagefill( $cut, 0, 0, $transparent ); //Transparency Step 3/5
			// copying relevant section from background to the cut resource 
			imagecopy($cut, $this->image, 0, 0, $dst_x, $dst_y, $src_w, $src_h); 
			// copying relevant section from watermark to the cut resource 
			imagecopy($cut, $src_im->image, 0, 0, $src_x, $src_y, $src_w, $src_h); 
			// insert cut resource to destination image 
			imagecopymerge($this->image, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, 50);
			$src_im->destroy();
			imagedestroy($cut);
		}
	}

	public function resizelimit($width,$height) {
		$ratio_orig = $this->getWidth()/$this->getHeight();
		if ($width/$height > $ratio_orig) {
			$width = floor($height*$ratio_orig);
		} else {
			$height = floor($width/$ratio_orig);
		}
		$this->resize($width,$height);  
	}
	
	public function rotation($degrees) {
		$pngTransparency = imagecolorallocatealpha($this->image , 0, 0, 0, 127);
		$new_image = imagerotate($this->image, $degrees, $pngTransparency); //Le zero permet en principe de concerver la transparence des PNG, mais a tester
		$this->image = $new_image;
	}
	
	//All white (246-255) around the picture become transparency. It doesn't make transparency the pixels isolated in middle of picture
	public function whitebordertotrans() {
		$imgx = $this->getWidth();
		$imgy = $this->getHeight();
		imagealphablending($this->image, false);
		imagesavealpha($this->image, true);
		$transparent = imagecolorallocatealpha($this->image, 255, 255, 255, 127 );
		
		//Initialization of array, we extrapolate borders to avoid any offset
		for($y=-1;$y<$imgy+1;$y++){
			for($x=-1;$x<$imgx+1;$x++){
				if($x>=0 && $x<=$imgx-1 && $y>=0 && $y<=$imgy-1){
					$rgb = imagecolorat($this->image, $x, $y);
					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;
				} else {
					$r = 0;
					$g = 0;
					$b = 0;
				}
				if($r>245 && $g>245 && $b>245){
					$sheet[$x][$y]=0; //Probable transparency cell
				} else {
					$sheet[$x][$y]=-1;
				}
			}
		}
		
		//From left to right
		for($y=0;$y<$imgy;$y++){
			//From top to bottom
			for($x=0;$x<$imgx;$x++){
				$rgb = imagecolorat($this->image, $x, $y);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				if($r>245 && $g>245 && $b>245){
					if($x==0 || $x==$imgx-1 || $y==0 || $y==$imgy-1 || $sheet[$x][$y]==1){ //If we test on border, we valid
						$sheet[$x][$y]=2;
						$tpx=$x;
						$tpy=$y;
						//Initialize the 8 cells adjacent R/L/B/T and diagonal to put in transparency at next loop, T is after L for a priority reason
						if($sheet[$x+1][$y]==0){$sheet[$x+1][$y]=1;} //M-D
						if($sheet[$x-1][$y+1]==0){$sheet[$x-1][$y+1]=1;} //B-G
						if($sheet[$x][$y+1]==0){$sheet[$x][$y+1]=1;} //B-M
						if($sheet[$x+1][$y+1]==0){$sheet[$x+1][$y+1]=1;} //B-D
						if($sheet[$x-1][$y]==0){$sheet[$x-1][$y]=1;$tpx=$x-2;$tpy=$y;} //M-G Priority 4/4
						if($sheet[$x+1][$y-1]==0){$sheet[$x+1][$y-1]=1;$tpx=$x;$tpy=$y-1;} //H-D Priority 3/4
						if($sheet[$x][$y-1]==0){$sheet[$x][$y-1]=1;$tpx=$x-1;$tpy=$y-1;} //H-M Priority 2/4
						if($sheet[$x-1][$y-1]==0){$sheet[$x-1][$y-1]=1;$tpx=$x-2;$tpy=$y-1;} //H-G Priority 1/4
						$x=$tpx;
						$y=$tpy;
					}
				}
			}
		}
		
		//Creation des pixel de transparence a 100% et semi-pixel (antialiasing)
		//Create 100% and 50% (anti-aliasing) transparency pixels
		for($y=0;$y<$imgy;$y++){
			for($x=0;$x<$imgx;$x++){
				if($sheet[$x][$y]==2){ //On a transparency pixel
					imagesetpixel($this->image,$x,$y,$transparent);
				} else if($sheet[$x+1][$y]==2 || $sheet[$x-1][$y]==2 || $sheet[$x][$y+1]==2 || $sheet[$x][$y-1]==2){ //On a transparency pixel border
					$rgb = imagecolorat($this->image, $x, $y);
					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;
					imagesetpixel($this->image,$x,$y,imagecolorallocatealpha($this->image, $r, $g, $b, 40 ));
				} else if($sheet[$x+1][$y+1]==2 || $sheet[$x+1][$y-1]==2 || $sheet[$x-1][$y+1]==2 || $sheet[$x-1][$y-1]==2){ //On a transparency pixel diagonal
					$rgb = imagecolorat($this->image, $x, $y);
					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;
					imagesetpixel($this->image,$x,$y,imagecolorallocatealpha($this->image, $r, $g, $b, 20 ));
				}
			}
		}
	}

	//Apply Transparency mask to picture
	//NOTE: The mask MUST be the same resolution as the source (if nessecery, apply a resize on mask before to operate)
	public function imagemask_alpha($mask_im){
		// Get image width and height 
		$w = $this->getWidth(); 
		$h = $this->getHeight();
		
		//Turn alpha blending off => Not necessary in Class function, it has been done before
		//imagealphablending($src_im, false);
		//imagealphablending($mask_im, false); 
		
		//loop through image pixels and modify alpha for each 
		for($x=0;$x<$w;$x++){
			for($y=0;$y<$h;$y++){
				//get current alpha value
				$colorxy = imagecolorat($this->image,$x,$y);
				$alpha = ( $colorxy >> 24 ) & 0xFF;
				$alpha_mask = ( imagecolorat( $mask_im, $x, $y )  >> 24 ) & 0xFF;
				//Keep the most transparency picture between src and mask
				
				if($alpha_mask>$alpha){
					$alpha=$alpha_mask;
				}
				
				//get the color index with new alpha 
				$alphacolorxy = imagecolorallocatealpha( $this->image, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
				//set pixel with the new color + opacity 
				imagesetpixel($this->image,$x,$y,$alphacolorxy);
			}
		}
		// The image copy  => Not necessary in Class function, it will been done later
		//imagesavealpha($src_im, true);
	}

	//Make a picture with repeated picture
	public function bgtile($tile,$offsetX=0,$offsetY=0){
		//IMPORTANT: Offset can only be negative to get a tile frame bigger than the picture, if not the borders will not be fulfilled by teh tile picture
		
		if(($offsetX<0 || $offsetY<0) && $offsetX<=0 && $offsetY<=0){
			$off_image = imagecreatetruecolor($this->getWidth()-$offsetX, $this->getHeight()-$offsetY);
			imagealphablending($off_image, true); //Transparency Step 1/5
			$transparent = imagecolorallocatealpha( $off_image, 0, 0, 0, 127 ); //Transparency Step 2/5
			imagefill( $off_image, 0, 0, $transparent ); //Transparency Step 3/5
			imagesettile($off_image, $tile); // Set the tile as background
			imagefilledrectangle($off_image, 0, 0, $this->getWidth()-1-$offsetX, $this->getHeight()-1-$offsetY, IMG_COLOR_TILED);	// Make the image repeat
			imagecopy($this->image, $off_image, 0, 0, -$offsetX, -$offsetY, $this->getWidth(), $this->getHeight());
		} else {
			// Set the tile as background
			imagesettile($this->image, $tile);
			// Make the image repeat
			imagefilledrectangle($this->image, $offsetX, $offsetY, $this->getWidth()-1-$offsetX, $this->getHeight()-1-$offsetY, IMG_COLOR_TILED);
		}
	}

	//Fulfill the whole picture with the same color
	public function image_fullcolor($color){
		// Translate to decimal an hexadecimal color (F08F66 => 240,143,102)
		$rgb = hexdec($color);
		$rr = ($rgb >> 16) & 0xFF;
		$gg = ($rgb >> 8) & 0xFF;
		$bb = $rgb & 0xFF;
		// Get image width and height 
		$w = $this->getWidth(); 
		$h = $this->getHeight();
		// Loop through image pixels and modify alpha for each 
		//Cannot use imagefill because it fulfill only the border
		for($x=0;$x<$w;$x++){
			for($y=0;$y<$h;$y++){
				// Get current color (picture, Red, Green, Blue)	
				$colorxy = imagecolorallocate( $this->image, $rr, $gg, $bb);
				// Set pixel with the new color
				imagesetpixel($this->image,$x,$y,$colorxy);
			}
		}
	}

	//Add an alpha to the complete picture
	public function image_addalpha($pct){
		if($pct<100){ //100% = full opacity, so it's useless to make the operation
			// Get image width and height 
			$w = $this->getWidth(); 
			$h = $this->getHeight();
			//loop through image pixels and modify alpha for each 
			for($x=0;$x<$w;$x++){
				for($y=0;$y<$h;$y++){
					//get current alpha value
					$colorxy = imagecolorat($this->image,$x,$y);
					$alpha = ( $colorxy >> 24 ) & 0xFF;
					$alpha = round(127-((127-$alpha)*$pct/100));
					if($alpha<0){$alpha = 0;}
					if($alpha>127){$alpha = 127;}
					
					//get the color index with new alpha 
					$alphacolorxy = imagecolorallocatealpha( $this->image, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
					//set pixel with the new color + opacity 
					imagesetpixel($this->image,$x,$y,$alphacolorxy);
				}
			}
		}
	}

	//Return the average color of a picture
	public function image_average(){
		//Get the grey average and build a greyscale pciture from any color picture
		$total = 0;
		$totrr = 0;
		$totgg = 0;
		$totbb = 0;
		// Get image width and height 
		$w = $this->getWidth(); 
		$h = $this->getHeight();
		// Loop through image pixels and get a table with grayscale level
		for($x=0;$x<$w;$x++){
			for($y=0;$y<$h;$y++){
				$rgb = imagecolorat($this->image, $x, $y); 
				$alpha = ( $rgb >> 24 ) & 0xFF;
				//Alpha is used as coefficient to tell the importance of the color, a transparency color will be less important than a opacity color
				$coef = round(10*(1-($alpha/127)));
				$total = $total+$coef;
				$totrr = $totrr + $coef*(($rgb >> 16) & 0xFF);
				$totgg = $totgg + $coef*(($rgb >> 8) & 0xFF);
				$totbb = $totbb + $coef*($rgb & 0xFF);
			}
		}
		//Setup the color average
		$rr = round($totrr/$total);
		$gg = round($totgg/$total);
		$bb = round($totbb/$total);
		return mb_strtoupper(dechex($rr).dechex($gg).dechex($bb));
	}

	//Fulfill the whole picture with the same color and keep its grayscale difference according to the color level
	public function image_colorscale($color){
		$rgb = hexdec($color);
		$colorrr = ($rgb >> 16) & 0xFF;
		$colorgg = ($rgb >> 8) & 0xFF;
		$colorbb = $rgb & 0xFF;
		
		//Get the grey average and build a greyscale pciture from any color picture
		$greytab = array();
		$total = 0;
		$totgrey = 0;
		$greyimg = array();
		$alphaimg = array();
		// Get image width and height 
		$w = $this->getWidth(); 
		$h = $this->getHeight();
		// Loop through image pixels and get a table with grayscale level
		for($x=0;$x<$w;$x++){
			for($y=0;$y<$h;$y++){
				$rgb = imagecolorat($this->image, $x, $y); 
				$rr = ($rgb >> 16) & 0xFF;
				$gg = ($rgb >> 8) & 0xFF;
				$bb = $rgb & 0xFF;
				$alpha = ( $rgb >> 24 ) & 0xFF;
				$coef = round(10*(1-($alpha/127)));
				$grey = round(($rr + $gg + $bb) / 3);
				if(isset($greytab[$grey])){
					$greytab[$grey] = $greytab[$grey]+$coef;
				} else {
					$greytab[$grey] = $coef;
				}
				//Alpha is used as coefficient to tell the importance of the color, a transparency color will be less important than a opacity color
				$total = $total+$coef;
				$totgrey = $totgrey+($coef*$grey);
				$greyimg[$x][$y] = $grey;
				$alphaimg[$x][$y] = $alpha;
			}
		}
		
		//Setup the grey average
		if($total>0){
			$average = round($totgrey/$total);
		} else {
			$average = 127;
		}
		
		//Setup the average to 123
		for($x=0;$x<$w;$x++){
			for($y=0;$y<$h;$y++){
				$grey = $greyimg[$x][$y];
				$delta = $grey-$average;
				
				$rr = $colorrr+$delta;
				$gg = $colorgg+$delta;
				$bb = $colorbb+$delta;
				
				if($rr<0){$rr=0;}
				else if($rr>255){$rr=255;}
				if($gg<0){$gg=0;}
				else if($gg>255){$gg=255;}
				if($bb<0){$bb=0;}
				else if($bb>255){$bb=255;}
				
				// Get current color (picture, Red, Green, Blue)	
				$colorxy = imagecolorallocatealpha($this->image, $rr, $gg, $bb, $alphaimg[$x][$y]);
				// Set pixel with the new color
				imagesetpixel($this->image,$x,$y,$colorxy);
			}
		}
	}

	//Get the color value from a transparency color with a color background for the background menu
	public function value_menu_bg($color, $bgcolor, $pct=70){
		$rgb = hexdec($color);
		$rr = ($rgb >> 16) & 0xFF;
		$gg = ($rgb >> 8) & 0xFF;
		$bb = $rgb & 0xFF;
		
	$img_color = imagecreatetruecolor(1, 1);
	imagealphablending($img_color, true);
		imagefill($img_color, 0, 0, imagecolorallocatealpha( $img_color, $rr, $gg, $bb, 0 ) );
				
		$rgb = hexdec($bgcolor);
		$rr = ($rgb >> 16) & 0xFF;
		$gg = ($rgb >> 8) & 0xFF;
		$bb = $rgb & 0xFF;
				
	$img_bgcolor = imagecreatetruecolor(1, 1);
	imagealphablending($img_bgcolor, true);
		imagefill($img_bgcolor, 0, 0, imagecolorallocatealpha( $img_bgcolor, $rr, $gg, $bb, 0 ) );
				
		imagecopymerge($img_bgcolor, $img_color, 0, 0, 0, 0, 1, 1, $pct);
				
		$rgb = imagecolorat($img_bgcolor, 0, 0); 
		$rr = ($rgb >> 16) & 0xFF;
		$gg = ($rgb >> 8) & 0xFF;
		$bb = $rgb & 0xFF;
				
		return mb_strtoupper(dechex($rr).dechex($gg).dechex($bb));
	}

}
