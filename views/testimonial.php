<?php

include_once "layout.php";
require_once 'vendor/autoload.php';
include_once 'controllers/google.php';


echo pageHeader("Well Days - Testimonial",$adminstatus,$supervisoruser);
require "html/testimonial.html";
echo pageFooter();

