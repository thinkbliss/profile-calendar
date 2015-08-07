<?php
include_once "layout.php";
require_once 'vendor/autoload.php';
include_once 'controllers/google.php';


//define('GUSER', 'mmosley@droga5.com');
 // GMail username
//define('GPWD', 'Xmosley420');
 // GMail password
if ($client->getAccessToken()) {
    $_SESSION['access_token'] = $client->getAccessToken();
    $userProfile = $plus_service->people->get('me');
    print_r($userProfile->emails[0]->value);
    if (isset($_POST['message'])) {
        welldaysMailer($_POST["to"], 'mmosley@droga5.com', 'Sent From Name', $_POST["subject"], $_POST["message"], $gmail_service);
    }
}

echo pageHeader("Well Days - TEST",$adminstatus);
if (isset($authUrl)) {
    echo "<a class='login' href='" . $authUrl . "'>Connect Me!</a>";
} else {
    
    require "html/test.html";
}
echo pageFooter();


