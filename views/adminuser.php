<?php

include_once "layout.php";
require_once 'vendor/autoload.php';
include_once 'controllers/google.php';



if (!empty($_REQUEST['users'])) {
    $id=$_REQUEST['users'];
} else {
    $id = $match['params']['id'];
}

echo pageHeader("ADMIN",$adminstatus,$supervisoruser);
//
if (isset($authUrl)) {
    echo connectMe($authUrl);
} else {
    
        if ($adminstatus==0) {
                require "html/notallowed.html";
        } else {
                require "html/adminuser.html";
        }
       
} 
echo pageFooter();

