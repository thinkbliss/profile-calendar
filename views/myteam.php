<?php

include_once "layout.php";
require_once 'vendor/autoload.php';
include_once 'controllers/google.php';

echo pageHeader("Well Days - MY TEAM",$adminstatus,$supervisoruser,$supervisoruser);
if (isset($authUrl)) {
    echo connectMe($authUrl);
} else {
    
	require "html/myteam.html";
	//echo logOut();

} 
echo pageFooter();

