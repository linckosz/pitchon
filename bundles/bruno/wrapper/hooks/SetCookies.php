<?php

namespace bundles\bruno\wrapper\hooks;

use \libs\Vanquish;

function SetCookies(){
	Vanquish::setCookies();
}
