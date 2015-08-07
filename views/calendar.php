<?php

include_once "layout.php";
require_once 'vendor/autoload.php';
include_once 'controllers/google.php';


echo pageHeader("Well Days - CALENDAR",$adminstatus,$supervisoruser);

if (isset($authUrl)) {
    echo connectMe($authUrl);
} else {
 	require_once "html/calendar.html";
}
//require_once "html/footer.html";
//echo "</body>";
echo pageFooter();
