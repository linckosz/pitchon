<?php

namespace libs;

use \libs\SimpleImage;

class SimpleImageCaptcha extends SimpleImage {

	//$cap_pic: file of the number to add
	//$num: position number between 1 and the total of the captcha number
	public function bruno_addcaptcha($cap_pic, $num, $total=4) {
		if($num>=1 && $num<=$total){
			$spaceW = floor($this->get_width()/$total);
			$spaceH = $this->get_height();
			
			$src_im = new SimpleImageCaptcha();
			$src_im->load($cap_pic);
			
			$degrees = rand(-20,20);
			$src_im->bruno_rotation($degrees); //Random rotte
			
			$sizeratio = rand(70,95)/100;
			$maxW = floor($sizeratio*$spaceW);
			$maxH = floor($sizeratio*$spaceH);
			$src_im->bruno_resizelimit($maxW,$maxH); //Random scale
			
			$dst_x = rand($spaceW*($num-1),$spaceW*$num-$src_im->get_width()); //Random X position
			$dst_y = rand(0,$spaceH-$src_im->get_height()); //Random Y position
			$src_x = 0;
			$src_y = 0;
			$src_w = $src_im->get_width();
			$src_h = $src_im->get_height();
			
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
			unset($src_im);
			imagedestroy($cut);
		}
	}

	public function bruno_resizelimit($width,$height) {
		$ratio_orig = $this->get_width()/$this->get_height();
		if ($width/$height > $ratio_orig) {
			$width = floor($height*$ratio_orig);
		} else {
			$height = floor($width/$ratio_orig);
		}
		$this->resize($width,$height);  
	}
	
	public function bruno_rotation($degrees) {
		$pngTransparency = imagecolorallocatealpha($this->image , 0, 0, 0, 127);
		$new_image = imagerotate($this->image, $degrees, $pngTransparency); //Le zero permet en principe de concerver la transparence des PNG, mais a tester
		$this->image = $new_image;
	}

	public function bruno_get_image(){
		return $this->image;
	}

}
