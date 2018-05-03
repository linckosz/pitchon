<?php

namespace libs;

class Video {

	protected $id = false;

	protected $temp_id = false;

	protected $source = null;

	protected $destination = null;

	protected $thumbnail = null;

	protected $cpu = 3;

	protected $tablo = array();

	protected $info = array(
		'rotate' => '',
		'duration' => 0,
		'frames' => 0,
		'width' => 640,
		'height' => 480,
	);

	// libx264-fast-bruno.ffpreset
	protected static $fast = '-coder ac -cmp chroma -partitions all -me_method hex -subq 6 -me_range 16 -g 60 -keyint_min 25 -sc_threshold 40 -i_qfactor 0.71 -b_strategy 1 -qcomp 0.6 -qmin 10 -qmax 51 -qdiff 4 -bf 3 -refs 2 -direct-pred spatial -trellis 1 -b-pyramid normal -mixed-refs 1 -weightb 1 -8x8dct 1 -fast-pskip 1 -mbtree 1 -psy 1 -wpredp 2 -tune animation -pix_fmt yuv420p';

	protected static $FFPROBE = '/usr/bin/ffprobe';

	protected static $FFMPEG = '/usr/bin/ffmpeg';

	public function __construct($source, $destination, $thumbnail, $txt){
		$result = false;
		$ffprobe = self::$FFPROBE;
		$this->source = $source;
		$this->destination = $destination;
		$this->thumbnail = $thumbnail;
		$this->txt = $txt;

		//Get the rotation information if it's available
		exec("$ffprobe -show_streams \"$source\" 2>&1", $tablo, $result);

		$regcod = "/\b\W*codec_type=(\w+)\b/Ui"; //$matches[1]
		$regrot = "/\b.*(?:\W|:)rotate=(\d+)\b/Ui"; //$matches[1]
		$regdur = "/\b\W*duration=(\d|\d+\.\d+)\b/Ui"; //$matches[1]
		$regfra = "/\b\W*nb_frames=(\d+)\b/Ui"; //$matches[1]
		$regwid = "/\b\W*width=(\d+)\b/Ui"; //$matches[1]
		$reghei = "/\b\W*height=(\d+)\b/Ui"; //$matches[1]

		$checkvideo = 0;
		$invert = false;
		foreach($tablo as $i => $value) {
			unset($matches);
			if(preg_match($regcod, $tablo[$i], $matches)){
				if($matches[1]=='video'){
					$checkvideo = 1;
				} else {
					$checkvideo = 0;
				}
			}
			if($checkvideo==1){
				unset($matches);
				if(preg_match($regrot, $tablo[$i], $matches)){
					switch ($matches[1]) {
						case '90': $this->info['rotate'] = 'transpose=1,'; $invert = true; break;
						case '180': $this->info['rotate'] = 'hflip,vflip,'; break;
						case '270': $this->info['rotate'] = 'transpose=2,'; $invert = true; break;
						default: $this->info['rotate'] = ''; break;
					}
				}
				
				//IMPORTANT: It seems that the current ffmpeg version handle the rotation automaticaly
				$this->info['rotate'] = '';

				unset($matches);
				if($this->info['duration']==0 && preg_match($regdur, $tablo[$i], $matches)){
					$this->info['duration'] = floatval($matches[1]);
				}
				unset($matches);
				if($this->info['frames']<=2 && preg_match($regfra, $tablo[$i], $matches)){
					if(intval($matches[1])>=2){
						$this->info['frames'] = intval($matches[1]);
					}
				}
				unset($matches);
				if(preg_match($regwid, $tablo[$i], $matches)){
					if(intval($matches[1])>=2){
						$this->info['width'] = intval($matches[1]);
					}
				}
				unset($matches);
				if(preg_match($reghei, $tablo[$i], $matches)){
					if(intval($matches[1])>=2){
						$this->info['height'] = intval($matches[1]);
					}
				}
			}
		}
		if($invert){
			$width = $this->info['width'];
			$height = $this->info['height'];
			$this->info['width'] = $height;
			$this->info['height'] = $width;
		}
		return $result;
	}

	public function setID($id){
		$this->id = (int) $id;
	}

	public function setTempID($temp_id){
		$this->temp_id = (string) $temp_id;
	}

	protected function scale($limit=960){
		$width = $this->info['width'];
		$height = $this->info['height'];
		if($width > $height){
			if($width > $limit){
				$height = floor($height*$limit/$width);
				$width = $limit;
			}
		} else if($height > $width){
			if($height > $limit){
				$width = floor($width*$limit/$height);
				$height = $limit;
			}
		} else {
			if($height > $limit){
				$height = $limit;
				$width = $limit;
			}
		}
		//The division by 4 is a need for video resolution acceptance
		$width = floor($width/4)*4;
		$height = floor($height/4)*4;
		$this->info['width_new'] = $width;
		$this->info['height_new'] = $height;
		$rotate = $this->info['rotate'];
		$this->filter = "-vf \"$rotate scale=$width:$height\"";
	}

	public function thumbnail(){
		$result = false;
		$frames = $this->info['frames'];

		if(function_exists('proc_nice')){proc_nice(10);} //Higer the number is, lower the script priority is
		
		if($frames>=2){ //A video is at leats 2 frames
			if($frames>50){ $frames=50; } //Do not exceed 50, if not the calculation will be too long
			$this->scale(640); //360p
			$cpu = $this->cpu;
			$ffmpeg = self::$FFMPEG;
			$source = $this->source;
			$thumbnail = $this->thumbnail;
			$rotate = $this->info['rotate'];
			$width = $this->info['width_new'];
			$height = $this->info['height_new'];
			$filter = "-vf \"$rotate thumbnail=$frames, scale=$width:$height\"";
			exec("$ffmpeg -i \"$source\" -threads $cpu -frames:v 1 -y $filter -qscale:v 15 \"$thumbnail\" 2>&1", $tablo, $result);
			if($result!==0){
				@unlink($thumbnail);
			}
		}

		if(function_exists('proc_nice')){proc_nice(0);} //Reset the script priority to normal

		return $result;
	}

	public function convert($quality=2){
		$result = false;
		$frames = $this->info['frames'];
		
		if($frames>=2){ //A video is at leats 2 frames
			$cpu = $this->cpu;
			$fast = self::$fast;
			$source = $this->source;
			$destination = $this->destination;
			$ffmpeg = self::$FFMPEG;
			$txt = $this->txt;
			if(rename($source, $source.'.video')){
				$this->source = $source.'.video';
				$source = $this->source;
				//EXEC in running in background
				if($quality==1){//LOW quality (360p)
					$this->scale(640);
					$filter = $this->filter;
					exec("/bin/bash -c '$ffmpeg -i \"$source\" -threads $cpu -vcodec libx264 -profile:v high $fast -crf 32 $filter -acodec aac -ab 36000 -ar 44100 -ac 1 -async 1 -y -f mp4 -movflags faststart \"$destination\" 2>$txt; /bin/rm \"$source\";' > /dev/null 2>/dev/null &", $tablo, $result);
				}
				else if($quality==2){ //MEDIUM quality (540p) (use 856 for 480p) (use 960 for 540p)
					$this->scale(856);
					$filter = $this->filter;
					exec("/bin/bash -c '$ffmpeg -i \"$source\" -threads $cpu -vcodec libx264 -profile:v high $fast -crf 30 $filter -acodec aac -ab 64000 -ar 44100 -async 1 -y -f mp4 -movflags faststart \"$destination\" 2>$txt; /bin/rm \"$source\";' > /dev/null 2>/dev/null &", $tablo, $result);
				}
				else if($quality==3){//HIGH quality (720p)
					$this->scale(1280);
					$filter = $this->filter;
					exec("/bin/bash -c '$ffmpeg -i \"$source\" -threads $cpu -vcodec libx264 -profile:v high $fast -crf 28 $filter -acodec aac -ab 96000 -ar 44100 -async 1 -y -f mp4 -movflags faststart \"$destination\" 2>$txt; /bin/rm \"$source\";' > /dev/null 2>/dev/null &", $tablo, $result);
				}
			}
		}

		return $result;
	}
	
}
