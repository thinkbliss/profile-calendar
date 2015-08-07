<?php

include_once "layout.php";
require_once 'vendor/autoload.php';
include_once 'controllers/google.php';

echo pageHeader("Well Days - MY DAYS",$adminstatus,$supervisoruser);
if (isset($authUrl)) {
    echo connectMe($authUrl);
} else {
    
	require "html/mydays.html";
	//echo logOut();

} 
echo pageFooter();

