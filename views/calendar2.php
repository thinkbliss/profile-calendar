<?php

include_once "layout.php";
require_once 'vendor/autoload.php';
include_once 'controllers/google.php';


echo pageHeader("Well Days - CALENDAR",$adminstatus,$supervisoruser);

?>
<div class="box">
  <div class="request">
<?php
if (isset($authUrl)) {
    echo connectMe($authUrl);
} else {
 
  require "html/calendar2.html";
   //echo logOut();
}
?>
  </div>
</div>


<?php
echo pageFooter();
