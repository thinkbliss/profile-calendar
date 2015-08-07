<?php
$pos = strrpos($_SERVER['SERVER_NAME'], "hr-stg");
if ($pos === false) { // note: three equal signs
	$client_id = '310359927956-isun962evb1ecj942ao8rp8oca1a3rov.apps.googleusercontent.com';
	$client_secret = 'G96c_tuoHaDXnr3x2wkYTEiV';
	$redirect_uri = 'http://local.droga5.com/welldays/';
} else {
	$client_id = '794230110390-pcn4beqiudgv0jdh632vdo1e42mbm08a.apps.googleusercontent.com';
	$client_secret = 'XJhs8i8OKVSqKTZusTE2mOhS';
	$redirect_uri = 'https://hr-stg.d5servers.com/welldays/';
}
$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->setAccessType('offline');
$client->addScope("https://www.googleapis.com/auth/plus.me");
$client->addScope("https://www.googleapis.com/auth/userinfo.email");
$client->addScope("https://www.googleapis.com/auth/calendar");
$client->addScope("https://www.googleapis.com/auth/gmail.compose");



//$yt_service = new Google_Service_YouTube($client);
//$dr_service = new Google_Service_Drive($client);
//$email_service = new Google_Service_Plus_PersonEmails($client);
$cal_service = new Google_Service_Calendar($client);
$plus_service = new Google_Service_Plus($client);
$gmail_service = new Google_Service_Gmail($client);

//$message = new Google_Service_Gmail_Message();
//$message->setRaw(strtr(base64_encode($email), '+/=', '-_,'));

if (isset($_REQUEST['logout'])) {
    unset($_SESSION['access_token']);
}
if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);
    $_SESSION['refresh_token'] = $client->getRefreshToken();
} else {
    $authUrl = $client->createAuthUrl();
}

if($client->isAccessTokenExpired()) {
    // Don't think this is required for Analytics API V3
    //$_googleClient->refreshToken($_analytics->dbRefreshToken($_agencyId));
    //echo 'Access Token Expired'; // Debug

    if (isset($_SESSION['refresh_token']) && $_SESSION['refresh_token']) {
        $client->setAccessToken($_SESSION['refresh_token']);
        $_SESSION['refresh_token'] = $client->getRefreshToken();
        $_SESSION['access_token'] = $client->getAccessToken();
    }
}




?>
