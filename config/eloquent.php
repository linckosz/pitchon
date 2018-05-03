<?php

namespace config;

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule();

foreach($app->bruno->databases as $key => $database) {
	$capsule->addConnection(array(
		'driver' => $database['driver'],
		'host' => $database['host'],
		'database' => $database['database'],
		'username' => $database['username'],
		'password' => $database['password'],
		'charset'   => 'utf8mb4',
		'collation' => 'utf8mb4_unicode_ci',
		'prefix' => '',
	), $key);
}

//Erase connection information to limit hacking
foreach($app->bruno->databases as $key => $database) {
	$app->bruno->databases[$key] = true;
}

$capsule->setAsGlobal();
$capsule->bootEloquent();
