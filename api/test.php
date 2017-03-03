<?php

require("libs/vendor/autoload.php");

use phpFastCache\CacheManager;
use phpFastCache\Core\phpFastCache;

CacheManager::setDefaultConfig([
  "path" => sys_get_temp_dir(),
]);

$InstanceCache = CacheManager::getInstance('files');

$key = "product_page";
$CachedString = $InstanceCache->getItem($key);

if (is_null($CachedString->get())) {
    $CachedString->set("Files Cache --> Cache Enabled --> Well done !")->expiresAfter(5);
    $InstanceCache->save($CachedString);
    echo "FIRST LOAD // WROTE OBJECT TO CACHE // RELOAD THE PAGE AND SEE // ";
    echo $CachedString->get();
} else {
    echo "READ FROM CACHE // ";
    echo $CachedString->getExpirationDate()->format(Datetime::W3C);
    echo $CachedString->get();
}