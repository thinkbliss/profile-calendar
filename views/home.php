<?php
include_once "layout.php";
require_once 'vendor/autoload.php';
include_once 'controllers/google.php';

echo pageHeader("Well Days - HOME",$adminstatus,$supervisoruser);
    
//if (isset($authUrl)) {
   // echo connectMe($authUrl);
//} else {
	require "html/about.html";

    //echo addVideoPlayer();
//} 
    echo pageFooter();
