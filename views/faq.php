<?php

include_once "layout.php";
require_once 'vendor/autoload.php';
include_once 'controllers/google.php';


echo pageHeader("Well Days - FAQ",$adminstatus,$supervisoruser);
require "html/faq.html";
echo pageFooter();

