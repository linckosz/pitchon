<?php
//你好 Léo & Luka

namespace libs;

/**
 * Manipulate IPTC tags
 * 
 * @date 08-20-2010
 * @author ShevAbam
 */
class IptcManager
{
	public $file     = '';
	public $listTags = array();
	
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->listTags();
	}
	
	
	/**
	 * Sets the image file
	 * 
	 * @param  string  $filename  Path of the image file
	 * @return object
	 */
	public function setFile($filename)
	{
		if (!empty($filename) && is_file($filename) && filesize($filename)>=12){
			$size = getimagesize($filename);
				if($size[2]==2){
				$this->file = $filename;
				return $this;
			} else {
				return false;
			}
		} else {
				return false;
		}
	}
	
	/**
	 * Sets the IPTC tags
	 * 
	 * @return void
	 */
	public function listTags()
	{
		// Most common tags. You can redefine this method by extending the class
		$getAll = array(
			array('id' => 1,   'name' => 'Object type',                  'tag' => '2#003'),
			array('id' => 2,   'name' => 'Object attribute',             'tag' => '2#004'),
			array('id' => 3,   'name' => 'Title',                        'tag' => '2#005'),
			array('id' => 4,   'name' => 'Edit status',                  'tag' => '2#007'),
			array('id' => 5,   'name' => 'Editorial update',             'tag' => '2#008'),
			array('id' => 6,   'name' => 'Urgency',                      'tag' => '2#010'),
			array('id' => 7,   'name' => 'Subject reference',            'tag' => '2#012'),
			array('id' => 8,   'name' => 'Category',                     'tag' => '2#015'),
			array('id' => 9,   'name' => 'Supplemental categories',      'tag' => '2#020'),
			array('id' => 10,  'name' => 'Fixture identifier',           'tag' => '2#022'),
			array('id' => 11,  'name' => 'Keywords',                     'tag' => '2#025'),
			array('id' => 12,  'name' => 'Location code',                'tag' => '2#026'),
			array('id' => 13,  'name' => 'Location name',                'tag' => '2#027'),
			array('id' => 14,  'name' => 'Release date',                 'tag' => '2#030'),
			array('id' => 15,  'name' => 'Release time',                 'tag' => '2#035'),
			array('id' => 16,  'name' => 'Expiration date',              'tag' => '2#037'),
			array('id' => 17,  'name' => 'Expiration time',              'tag' => '2#038'),
			array('id' => 18,  'name' => 'Instructions',                 'tag' => '2#040'),
			array('id' => 19,  'name' => 'Action advised',               'tag' => '2#042'),
			array('id' => 20,  'name' => 'Reference service',            'tag' => '2#045'),
			array('id' => 21,  'name' => 'Reference date',               'tag' => '2#047'),
			array('id' => 22,  'name' => 'Reference number',             'tag' => '2#050'),
			array('id' => 23,  'name' => 'Date created',                 'tag' => '2#055'),
			array('id' => 24,  'name' => 'Time created',                 'tag' => '2#060'),
			array('id' => 25,  'name' => 'Digital creation date',        'tag' => '2#062'),
			array('id' => 26,  'name' => 'Digital creation time',        'tag' => '2#063'),
			array('id' => 27,  'name' => 'Originating program',          'tag' => '2#065'),
			array('id' => 28,  'name' => 'Program version',              'tag' => '2#070'),
			array('id' => 29,  'name' => 'Object cycle',                 'tag' => '2#075'),
			array('id' => 30,  'name' => 'Author',                       'tag' => '2#080'),
			array('id' => 31,  'name' => 'AuthorsPosition',              'tag' => '2#085'),
			array('id' => 32,  'name' => 'City',                         'tag' => '2#090'),
			array('id' => 33,  'name' => 'Sublocation',                  'tag' => '2#092'),
			array('id' => 34,  'name' => 'State/Province',               'tag' => '2#095'),
			array('id' => 35,  'name' => 'Country code',                 'tag' => '2#100'),
			array('id' => 36,  'name' => 'Country',                      'tag' => '2#101'),
			array('id' => 37,  'name' => 'Transmission reference',       'tag' => '2#103'),
			array('id' => 38,  'name' => 'Headline',                     'tag' => '2#105'),
			array('id' => 39,  'name' => 'Credit',                       'tag' => '2#110'),
			array('id' => 40,  'name' => 'Source',                       'tag' => '2#115'),
			array('id' => 41,  'name' => 'Copyright notice',             'tag' => '2#116'),
			array('id' => 42,  'name' => 'Contact Information',          'tag' => '2#118'),
			array('id' => 43,  'name' => 'Description',                  'tag' => '2#120'),
			array('id' => 44,  'name' => 'Description writer',           'tag' => '2#122'),
			array('id' => 45,  'name' => 'Image type',                   'tag' => '2#130'),
			array('id' => 46,  'name' => 'Image orientation',            'tag' => '2#131'),
			array('id' => 47,  'name' => 'Language identifier',          'tag' => '2#135'),
			array('id' => 48,  'name' => 'AudioType',                    'tag' => '2#150'),
			array('id' => 49,  'name' => 'AUdio sampling rate',          'tag' => '2#151'),
			array('id' => 50,  'name' => 'Audio sampling resolution',    'tag' => '2#152'),
			array('id' => 51,  'name' => 'Audio duration',               'tag' => '2#153'),
			array('id' => 52,  'name' => 'Audio outcue',                 'tag' => '2#154'),
			array('id' => 53,  'name' => 'Job ID',                       'tag' => '2#184'),
			array('id' => 54,  'name' => 'Master document ID',           'tag' => '2#185'),
			array('id' => 55,  'name' => 'Short document ID',            'tag' => '2#186'),
			array('id' => 56,  'name' => 'Unique document ID',           'tag' => '2#187'),
			array('id' => 57,  'name' => 'Owner ID',                     'tag' => '2#188'),
			array('id' => 58,  'name' => 'Object preview file format',   'tag' => '2#200'),
			array('id' => 59,  'name' => 'Object preview file version',  'tag' => '2#201'),
			array('id' => 60,  'name' => 'Object preview data',          'tag' => '2#202'),
			array('id' => 61,  'name' => 'Prefs',                        'tag' => '2#221'),
			array('id' => 62,  'name' => 'Classify state',               'tag' => '2#225'),
			array('id' => 63,  'name' => 'Similarity index',             'tag' => '2#228'),
			array('id' => 64,  'name' => 'Document notes',               'tag' => '2#230'),
			array('id' => 65,  'name' => 'Document history',             'tag' => '2#231'),
			array('id' => 66,  'name' => 'Exif camera info',             'tag' => '2#232'),
			array('id' => 67,  'name' => 'Catalog sets',                 'tag' => '2#255')
		);
		
		if (count($getAll) > 0)
		{
			foreach ($getAll as $tag)
				$this->listTags[$tag['id']] = $tag['tag'];
		}
	}
	
	
	/**
	 * Returns the content of an IPTC tag. If $tag is not specified, returns all tags.
	 * 
	 * @param  string  $tag  Name of the IPTC tag presents in $this->listTags
	 * @return string
	 */
	public function get($tag = null)
	{
		// If you specify a particular tag, it returns information for this tag
		if (!is_null($tag) && !empty($tag) && in_array($tag, $this->listTags))
		{
			$size = getimagesize($this->file, $info);
			
			if (isset($info['APP13']))
			{
				$iptc = iptcparse($info['APP13']);
				if($iptc[$tag][0]){
									return $iptc[$tag][0];
								} else {
									return false;
								}
			}
		}
		else
		{
			// Otherwise, it returns the complete info
			$size = getimagesize($this->file, $info);
			
			if (isset($info['APP13']))
			{
				return iptcparse($info['APP13']);
			}
		}
	}
	
	
	/**
	 * Add an IPTC tag
	 * 
	 * @param  string  $tag    Name of the IPTC tag presents in $this->listTags
	 * @param  string  $value  IPTC tag value
	 * @param  bool    $save   If save the image with the settings or not
	 * @return mixed
	 */
	public function add($tag, $value, $save = true)
	{
		// If tag is valid
		if (empty($tag) || empty($value) || !in_array($tag, $this->listTags))
			return false;
		
		$tag = substr($tag, 2); 
		
		$datas = $this->_transform_iptc($tag, $value);
		
		if ($save == true)
			$this->save($datas);
		else
			return $datas;
	}
	
	/**
	 * Add severals IPTC tags
	 * 
	 * @param  array  $array  List of IPTC tags and values
	 * @param  bool   $save   If save the image with the settings or not
	 * @return mixed
	 */
	public function addMultiple($array, $save = true)
	{
		// If tag is valid
		if (!is_array($array) || count($array) == 0)
			return false;
		
		$iptcdata = null;
		
		foreach ($array as $tag => $string)
		{
			if (in_array($tag, $this->listTags))
			{
				$tag = substr($tag, 2);
				$iptcdata .= $this->_transform_iptc($tag, $string);
			}
		}
		
		if ($save == true)
			$this->save($iptcdata);
		else
			return $iptcdata;
	}
	
	
	/**
	 * Save IPTC into image
	 * 
	 * @param  string  $datas         IPTC datas     
	 * @param  string  $newImageName  New image name
	 * @return void
	 */
	public function save($datas, $newImageName = '')
	{
		if (!empty($newImageName))
			$imageName = $newImageName;
		else
			$imageName = $this->file;
		
		// Writing datas in the image
		$data_image = iptcembed($datas, $this->file);
		
		$fp = fopen($imageName, "wb");
		
		fwrite($fp, $data_image);
		fclose($fp);
	}
	
	
	
	/**
	 * Format the new IPTC text
	 * 
	 * @param  string  $data   IPTC datas
	 * @param  string  $value  IPTC tag value
	 * @return string
	 */
	private function _transform_iptc($data, $value)
	{
		$length = strlen($value);
		$retval = chr(0x1C).chr(2).chr($data);
		
		if ($length < 0x8000)
		{
			$retval .= chr($length >> 8).chr($length& 0xFF);
		}
		else
		{
			$retval .= chr(0x80).chr(0x04). 
					chr(($length >> 24)& 0xFF). 
					chr(($length >> 16)& 0xFF). 
					chr(($length >> 8)& 0xFF). 
					chr($length& 0xFF);
		}
		
		return $retval.$value;
	}
}
