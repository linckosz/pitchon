<?php

namespace config;

use \libs\Folders;

foreach($app->bruno->bundles as $bundle) {
	//Include (and load) all routes for each bundle registered
	$folder = new Folders($app->bruno->path.'/bundles/'.$bundle.'/routes');
	$folder->includeRecursive();
	
	//Include all hooks for each bundle registered
	$folder = new Folders($app->bruno->path.'/bundles/'.$bundle.'/hooks');
	$folder->includeRecursive();

	//Include public files (create symlink at first launch only)
	if(is_dir($app->bruno->path.'/bundles/'.$bundle.'/public') && !is_dir($app->bruno->publicPath.'/'.$bundle)){
		$folder = new Folders();
		$folder->createSymlink($app->bruno->path.'/bundles/'.$bundle.'/public', $app->bruno->publicPath.'/'.$bundle);
	}
}

foreach($app->bruno->middlewares as $v) {
	//Load each app middleware in order of appearance in the array
	//Classes are autoloaded if file path is correctly done
	$middleware = str_replace('/', '\\', 'bundles\\'.$v[0].'\\middlewares\\'.$v[1]);
	$app->add(new $middleware);
}

foreach($app->bruno->hooks as $v) {
	//Load each hooks in order of appearance in the array
	//['bundle name', 'subfolder\function name', 'the.hook.name', priority value],
	$hook = str_replace('/', '\\', 'bundles\\'.$v[0].'\\hooks\\'.$v[1]);
	$app->hook($v[2], $hook, $v[3]);
}
