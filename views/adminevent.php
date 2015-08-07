<?php

include_once "layout.php";
require_once 'vendor/autoload.php';
include_once 'controllers/google.php';


if ($match['params']) {
    $id = $match['params']['id'];
    //$subid = $match['params']['id'];
} else {
    $id = 'new';
}


echo pageHeader("ADMIN",$adminstatus,$supervisoruser);
if (isset($authUrl)) {
    echo connectMe($authUrl);
} else {
    
        if ($adminstatus==0) {
                require "html/notallowed.html";
        } else {
                require "html/admin_events_new.html";
        }
       
} 
echo pageFooter();

