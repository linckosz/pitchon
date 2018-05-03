<?php

namespace config;

use \Exception;

//Load the singleton for translation purpose (only inside run())
$app->container->singleton('trans', function() {
	return new \libs\Translation();
});
