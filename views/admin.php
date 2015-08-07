<?php

include_once "layout.php";
require_once 'vendor/autoload.php';
include_once 'controllers/google.php';




if ($match['params']) {
    $id = $match['params']['section'];
    //$subid = $match['params']['id'];
} else {
    $id = 'employees';
}


echo pageHeader("ADMIN",$adminstatus,$supervisoruser);
if (isset($authUrl)) {
    echo connectMe($authUrl);
} else {
    
        if ($adminstatus==0) {
                require "html/notallowed.html";
        } else {
                require "html/admin_".$id.".html";
        }
       
} 
echo pageFooter();

