<?php
/*
	Feb 8, 2017 / Bruno Martin
	This file help to compare the md5 of folders and files to insure the integrity of which servers
*/

$app = \Slim\Slim::getInstance();
$path = $app->bruno->path;

ob_clean();
flush();
$app->response->headers->set('Content-Type', 'content="text/html; charset=UTF-8"');

echo '<div style=\'font-family:monospace;\'>';
echo "<br />\n";

$folders = array(
	$path.'/bundles',
	$path.'/config',
	$path.'/error',
	$path.'/libs',
	$path.'/param/common.php',
	$path.'/public',
);

function md5_folder($folder){
	$md5_array = array();
	if(is_dir($folder)){
		$files = glob($folder.'/*');
		if (is_array($files) && count($files) > 0) {
			foreach($files as $file) {
				if(!is_dir($file)){
					$md5 = md5_file($file);
					$md5_array[] = $md5;
					echo $md5.' =>  '.basename($file);
					echo "<br />\n";
				} else {
					$md5 = md5_folder($file);
					$md5_array[] = $md5;
					echo $md5.' &nbsp;&nbsp;&nbsp;&nbsp;=>  '.basename($file);
					echo "<br />\n";
				}
			}
		}
	}
	return md5(json_encode($md5_array));
}

foreach ($folders as $folder) {
	echo "<br />\n";
	echo '--------------------------------';
	echo "<br />\n";
	echo md5_folder($folder).' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ====> '.$folder;
	echo "<br />\n";
}
echo "<br />\n";
echo "<br />\n";
echo '</div>';
return exit(0);
