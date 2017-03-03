<?php

// import config
require_once("config.php");

// init cache
use phpFastCache\CacheManager;
use phpFastCache\Core\phpFastCache;

$cache = CacheManager::getInstance('files');

if (isset($_POST["dbid"])) {
    $dbid = (int) $_POST["dbid"];

    // get clientinfo from cache
    $clientinfo = $cache->getItem($CLIENTINFO_CACHE_KEY . $dbid);

    // check clientinfo
    if (!$clientinfo->isHit()) {
        // connect to ts
        $ts3 = new ts3admin($ts3_ip, $ts3_queryport);

        if ($ts3->getElement('success', $ts3->connect())) {
            $ts3->login($ts3_admin_user, $ts3_admin_pass);
            $ts3->selectServer($ts3_port);
            $query = $ts3->clientDbInfo($dbid);
            $clientsOnline = $ts3->clientList("-away")["data"];

            if ($query["success"] == 1) {
                $client = $query["data"];

                $online = false;
                $away = false;

                // check if client is online, away or offline
                foreach ($clientsOnline as $onlineClient) {
                    if ($onlineClient["client_database_id"] == $client["client_database_id"]) {
                        $online = true;
                        $away = ($onlineClient["client_away"] == 1);
                        break;
                    }
                }

                // get avatar
                $avatar = $ts3->clientAvatar($client["client_unique_identifier"]);
                $avatar = ($avatar["success"] ? $avatar["data"] : "");

                $clientinfo->set([
                    "success"  => true,
                    "online"   => $online,
                    "away"     => $away,
                    "dbid"     => (int) $client["client_database_id"],
                    "nickname" => str_replace("\\\\", "\\", $client["client_nickname"]),
                    "created"  => (int) $client["client_created"],
                    "lastconnected"    => (int) $client["client_lastconnected"],
                    "totalconnections" => (int) $client["client_totalconnections"],
                    "description"      => str_replace("\\\\", "\\", $client["client_description"]),
                    //"client_icon_id" => $client["client_icon_id"],
                    "avatar"           => $avatar,
                    /*"month_bytes_uploaded"   => $client["client_month_bytes_uploaded"],
                    "month_bytes_downloaded" => $client["client_month_bytes_downloaded"],
                    "total_bytes_uploaded"   => $client["client_total_bytes_uploaded"],
                    "total_bytes_downloaded" => $client["client_total_bytes_downloaded"]*/
                ])->expiresAfter($cacheTime)->addTag("client");
                $cache->save($clientinfo);
            } else {
                // clientlist query error
                die(json_encode([
                    "success" => false,
                    "dbid" => $dbid,
                    "error_message" => $query["errors"]
                ]));
            }
        } else {
            // ts connection error
            die(json_encode([
                "success" => false,
                "dbid" => $dbid,
                "error_message" => "connection error"
            ]));
        }
    }

    echo json_encode($clientinfo->get());
} else {
    // error, dbid is not set
    die(json_encode([
        "success" => false,
        "error_message" => "dbid not set"
    ]));
}