<?php
include_once "layout.php";
require_once 'vendor/autoload.php';


echo pageHeader("Well Days - Charities",$adminstatus,$supervisoruser);
require "html/charities.html";
/*if (isset($authUrl)) {
    echo connectMe($authUrl);
} else {
    
	require "html/charities.html";
	echo logOut();

} */
echo pageFooter();
