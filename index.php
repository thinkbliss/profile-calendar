<?php
session_start();
require_once 'vendor/autoload.php';
require 'controllers/daysController.php';
include_once('db/DBPDO.php');
include_once 'controllers/google.php';


//REMOVE THIS FROM GIT
$pos = strrpos($_SERVER['SERVER_NAME'], "hr-stg");
if ($pos === false) { // note: three equal signs
    //FOR LOCAL AND PROD
    define('DATABASE_NAME', 'wp_hrintra');
    define('DATABASE_USER', 'root');
    define('DATABASE_PASS', 'root');
} else {
    //FOR STAGING
    define('DATABASE_NAME', 'wp_hrstg');
    define('DATABASE_USER', 'hrstage');
    define('DATABASE_PASS', 'cF6z@JIQ&npaSb');
}
    define('DATABASE_HOST', 'localhost');




function curl_file_get_contents($URL){
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents) return $contents;
            else return FALSE;
    }
$DB = new DBPDO();

$router = new AltoRouter();
$router->setBasePath('/welldays');

$router->map( 'GET', '/', 'views/home.php', 'home' );
$router->map( 'GET', '/index.php', 'views/home.php', 'index' );
$router->map( 'GET|POST', '/calendar/', 'views/calendar.php', 'calendar' );
$router->map( 'GET|POST', '/calendar2/', 'views/calendar2.php', 'calendar2' );
$router->map( 'GET', '/charities/', 'views/charities.php', 'charities' );
$router->map( 'GET', '/mydays/', 'views/mydays.php', 'mydays' );
$router->map( 'GET', '/myteam/', 'views/myteam.php', 'myteam' );
$router->map( 'GET', '/faq/', 'views/faq.php', 'faq' );
$router->map( 'GET', '/testimonial/', 'views/testimonial.php', 'testimonial' );
$router->map( 'GET|POST', '/test/', 'views/test.php', 'test' );
$router->map( 'GET', '/admin/', 'views/admin.php', 'admin' );
$router->map( 'GET', '/admin/[:section]', 'views/admin.php', 'adminid' );
$router->map( 'GET', '/adminevent/[:id]', 'views/adminevent.php', 'adminevent' );
$router->map( 'GET', '/adminuser/[:id]', 'views/adminuser.php', 'adminusersection' );
$router->map( 'POST', '/adminuser/', 'views/adminuser.php', 'adminuser' );
$router->map('GET|POST', '/mydays/[:action]/[:id]', 'daysController#updateWellDay', 'welldays_do');
$router->map('GET|POST', '/testimonials/[:action]', 'daysController#updateTestimonial', 'testimonials_do');
$router->map('GET|POST', '/user/[:action]/[:userid]', 'daysController#userInfo', 'user_do');
$router->map('GET|POST', '/charitydays/[:action]/[:id]', 'daysController#updateCharityDay', 'charitydays_do');
$router->map('GET|POST', '/charitydaysbyCharity/[:id]', 'daysController#getCharityDayForCharity', 'charitydayscharity_do');
$router->map('GET|POST', '/charitydaysbyDate/[:year]/[:month]', 'daysController#getCharityDayByDate', 'charitydaysdate_do');
$router->map('GET|POST', '/nextcharitydaysbyCharity/[:id]', 'daysController#getNextCharityDayForCharity', 'nextcharitydayscharity_do');
$router->map('GET|POST', '/prevcharitydaysbyCharity/[:id]', 'daysController#getPrevCharityDayForCharity', 'prevcharitydayscharity_do');
$router->map('GET|POST', '/charity/[:action]', 'daysController#updateCharity', 'charity_do');
$router->map('GET', '/charities/[:action]', 'daysController#getCharities', 'charities_do');
$router->map('GET', '/requests/[:action]', 'daysController#updateRequests', 'requests_do');
$router->map('GET', '/myrequests/[:action]', 'daysController#updateMyTeam', 'myrequests_do');
$router->map('GET', '/users/', 'daysController#getUsers', 'users_do');
$router->map('GET', '/events/get/[:year]/[:month]', 'daysController#getEvents', 'events_do');
$router->map('GET', '/mail', 'daysController#sendEmail', 'email_do');
//$router->map('GET', '/events/getperday/[:year]/[:month]', 'daysController#getEventsPerDay', 'eventsperday_do');
//$router->map('GET|POST', '/charity/add', 'daysController#addCharity', 'charitydayscharity_do');

$match = $router->match();
$useremail=null;
$adminstatus=null;
$supervisoruser=null;
if ($client->getAccessToken()) {

        $userProfile = $plus_service->people->get('me');
        $useremail=$userProfile->emails[0]->value;
        
        //CHECK IF USER IS AN ADMIN
        $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $useremail);
        $adminstatus = $selecteduser["ADMIN"];   
        $supervisoruser = $DB->fetch("SELECT COUNT(*) FROM welldays_users WHERE supervisor = ?", $useremail);
 $supervisoruser = implode("",$supervisoruser );

    $ctrl = new daysController();


    $params = array('db' => $DB,  'email' => $useremail,'gmail_service' => $gmail_service);
    $ctrl->addUser($params);
}
if (strrpos($match["target"], "#") === false) {
   require $match['target'];
} else {
    // get our controller and method in $controller and $method vars
    list($controller, $method) = explode('#', $match['target']);    
    // if controller->action is callable then make the call and pass params
    if (is_callable(array($controller, $method))) {
        $url="https://hr.d5servers.com/directory/data/employees.json";
        $resp_json = curl_file_get_contents($url);
        $resp = json_decode($resp_json, true);
        $obj = new $controller();
        $db_array = array('db' => $DB, 'usersjson' => $resp, 'email' => $useremail, 'cal_service' => $cal_service, 'gmail_service' => $gmail_service);
        $params=array_merge($match['params'], $db_array);
        call_user_func_array(array($obj, $method), array($params));
        // if controller->action is NOT callable then throw an error
    } else {
        echo 'Error: can not call '.$controller.'->'.$method;
        exit;
        
    }
}


?>