<?php

// import config
require_once("config.php");

// init cache
use phpFastCache\CacheManager;
use phpFastCache\Core\phpFastCache;

$cache = CacheManager::getInstance('files');
$clientlist = $cache->getItem($CLIENTLIST_CACHE_KEY);

// check cache for clientlist
if (!$clientlist->isHit()) {
    // connect to ts
    $ts3 = new ts3admin($ts3_ip, $ts3_queryport);

    if ($ts3->getElement('success', $ts3->connect())) {
        $ts3->login($ts3_admin_user, $ts3_admin_pass);
        $ts3->selectServer($ts3_port);
        $query = $ts3->clientDbList(0, 10000);
        $clientsOnline = $ts3->clientList("-away")["data"];
        $new_clientlist = [];

        if ($query["success"] == 1) {

            foreach ($query["data"] as $client) {
                if (!in_array($client["cldbid"], $clientBlacklist)) {
                    $online = false;
                    $away = false;

                    // check if client is online, away or offline
                    foreach ($clientsOnline as $onlineClient) {
                        if ($onlineClient["client_database_id"] == $client["cldbid"]) {
                            $online = true;
                            $away = ($onlineClient["client_away"] == 1);
                            break;
                        }
                    }

                    // add client to clientlist
                    $new_clientlist[] = [
                        "online" => $online,
                        "away"   => $away,
                        "dbid"   => (int) $client["cldbid"],
                        "nickname" => str_replace("\\\\", "\\", $client["client_nickname"]),
                        "totalconnections" => (int) $client["client_totalconnections"],
                        "description" => str_replace("\\\\", "\\", $client["client_description"])
                    ];
                }
            }

            $clientlist->set($new_clientlist)->expiresAfter($cacheTime);
            $cache->save($clientlist);
        } else {
            echo("/* TeamSpeak API Error(s) on query:\n\n");

            foreach($query["errors"] as $error) {
                echo $error;
            }
            echo "\n*/\n";
        }
    } else {
        echo("/* TeamSpeak API Error(s) on connect:\n\n");
        foreach($ts3->getDebugLog() as $logEntry) {
            echo "$logEntry\n";
        }
        echo "*/\n";
    }
} else {
    //echo "// cached: true\n";
}

$js = !(isset($_GET["js"]) && $_GET["js"] === "false");

echo json_encode($clientlist->get());