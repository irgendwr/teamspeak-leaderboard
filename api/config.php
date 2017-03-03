<?php
// set your TeamSpeak credentials here
$ts3_ip = 'localhost';
$ts3_queryport = 10011;
$ts3_admin_user = 'serveradmin';
$ts3_admin_pass = 'password';
$ts3_port = 9987;

// you can remove clients from the leaderboard by adding teir DBID here:
$clientBlacklist = [

];

/*** you shouldn't need to change the stuff below this line ***/

// cache time in sec
$cacheTime = 30;

// import libs
require_once("libs/ts3admin.class/ts3admin.class.php");
require_once("libs/vendor/autoload.php");

use phpFastCache\CacheManager;
use phpFastCache\Core\phpFastCache;

CacheManager::setDefaultConfig([
  "path" => sys_get_temp_dir(),
]);

$SERVERINFO_CACHE_KEY = "SERVERINFO";
$CLIENTLIST_CACHE_KEY = "CLIENTLIST";
$CLIENTINFO_CACHE_KEY = "CLIENTINFO_";