<?php
//include_once 'controllers/google.php';
//include_once 'controllers/admin.php';
/* Ad hoc functions to make the examples marginally prettier.*/
function isWebRequest() {
    return isset($_SERVER['HTTP_USER_AGENT']);
}

function pageHeader($title,$adminstatus,$supervisoruser) {
    
    $ret = "";

    if (isWebRequest()) {
        $ret.= "<!doctype html>
    <html>
    <head>
    <meta charset='utf-8' />
      <title>" . $title . "</title>
      <script type='text/javascript' src='https://maps.google.com/maps/api/js?sensor=false&libraries=places'></script>
      <link href='/welldays/assets/css/normalize.css' rel='stylesheet' type='text/css' />
      <link href='/welldays/assets/css/main.css' rel='stylesheet' type='text/css' />
      <link rel='stylesheet' type='text/css' href='/welldays/styles/jquery.datetimepicker.css'/>
      <script src='/welldays/assets/js/jquery-2.1.3.min.js'></script>
      <script src='/welldays/assets/js/responsive-type.js'></script>
      <script src='/welldays/assets/js/jcarousel.js'></script>
      <script src='/welldays/assets/js/moment.js'></script>
      <script src='/welldays/assets/js/jquery.calendario.js'></script>
      <script src='/welldays/assets/js/locationpicker.jquery.min.js'></script>
      <script src='/welldays/assets/js/jquery.datetimepicker.js'></script>
      
      <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1'>
    </head>
    <body>\n";
        $ret .= addVideoPlayer();
        if ($adminstatus==1) {
            $ret .= "<div class='adminbar'>";
            $ret .= "<div id='ctrls'>";
            $ret .= "<a href='/welldays/admin/'>ADMIN AREA</a>";
            if ($supervisoruser>0) {
              $ret .= " |<a href='/welldays/myteam/'>MY TEAM</a>";
            }
            $ret .= " | <a href='?logout'>LOGOUT</a> ";
            
            $ret .= "</div>";
            $ret .= "</div>";
        } else if ($supervisoruser>0) {
            $ret .= "<div class='adminbar'>";
            $ret .= "<div id='ctrls'>";
            $ret .= "<a href='/welldays/myteam/'>MY TEAM</a>";
            $ret .= " | <a href='?logout'>LOGOUT</a> ";
            
            $ret .= "</div>";
            $ret .= "</div>";
          }


          $nav = addNav();//file_get_contents("html/nav.html");
        $ret.= $nav;
        

        //$ret.=$useremail;
        // $ret .= "<header><h1>TITLE! -- " . $title . "</h1></header>";
        
    }
    return $ret;
}


function addVideoPlayer() {
  $retval='';
  $retval.= '<div id="video" class="overlay">';
  $retval.= '<a href="javascript:void(0)" class="overlay-close"></a>';
  $retval.= '<div class="overlay-inner"> ';
  $retval.= '<div class="embed-container">    ';     
  $retval.= '<iframe src="https://player.vimeo.com/video/87180764?color=000000&title=0&byline=0&portrait=0" width="1024" height="576" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
  $retval.= '</div>';
  $retval.= '</div>';
  $retval.= '</div>';
return $retval;

}

function connectMe($authUrl) {
  $ret = '';


   $ret .= '<div class="sectionContainer" id="videoIntro">';
    $ret .= '<div class="sectionContent">';
   $ret .= ' <h2>Don\'t waste a sick day.<br>Make it a well day.</h2>';
     $ret .= '<div class="butn outline large">';
       $ret .= "<a class='login' href='" . $authUrl . "'>CONNECT</a>";
     $ret .= ' </div>';

   $ret .= ' </div>';
 $ret .= ' </div>';




    //$ret .= ' </body>';
    return $ret;
}
function logOut($adminstatus) {
  //$userProfile = $plus_service->people->get('me');
    $ret = "";
    if ($adminstatus==1) {
        $ret = "<a href='?logout'>LOGOUT</a>";
        $ret .= "<a href='/welldays/admin/'>ADMIN AREA</a><br/>";
    }
    
    //show only if admin!!!

    return $ret;
}

function addNav() {
  $pageArray = array('/welldays/','/welldays/calendar/','/welldays/charities/','/welldays/mydays/');
  $pageNameArray = array('About','Calendar','Charities','My Days');
  //  $pageArray = array({'page'=>'About','url'=>'/welldays/'},{'page'=>'Calendar','url'=>'/welldays/calendar/'},{'page'=>'Charities','url'=>'/welldays/charities/'},{'page'=>'My Days','url'=>'/welldays/mydays/'});
  //echo 
  $requestedPage=$_SERVER["REQUEST_URI"];
  if ($requestedPage=="/welldays/index.php") {
    $requestedPage='/welldays/';
  }
  $retval='';
  $retval.= '<div class="navbarContainer" id="nav">';
  $retval.= '<div class="row">';
      $retval.= '<ul class="twelve columns">';
      $length = count($pageArray);
      for ($x = 0; $x < $length; $x++) {
          $page=$pageArray[$x];

        //foreach ($pageArray as & $page) {
          $activeClass='';
          if ($page=='/welldays/charities/') {
              $retval.= '<li><a href="/welldays/"><h1><img src="/welldays/assets/images/logo.png" alt="Well Days" /></h1></a></li>';
          }
          if ($requestedPage==$page) {
              $activeClass='class="active"';
          }
          $retval.= '<li class=""><a href='.$page.' '.$activeClass.'>'.$pageNameArray[$x].'</a></li>';
          /*$retval.= '<li class=""><a href='/welldays/calendar/'>Calendar</a></li>';
          
          $retval.= '<li class=""><a href='/welldays/charities/'>Charities</a></li>';
          $retval.= '<li class=""><a href='/welldays/mydays/'>My Days</a></li>';*/
        }
      $retval.= '</ul>';
    $retval.= '</div>';
$retval.= '</div>';

return $retval;
}



function pageFooter($file = null) {
    $footer = file_get_contents("html/footer.html");
    $ret = $footer;
    $ret.= "<script src='/welldays/assets/js/main.js'></script>";
    $ret.= "</body>\n";
    return $ret;
}

function missingApiKeyWarning() {
    $ret = "";
    if (isWebRequest()) {
        $ret = "
      <h3 class='warn'>
        Warning: You need to set a Simple API Access key from the
        <a href='http://developers.google.com/console'>Google API console</a>
      </h3>";
    } 
    else {
        $ret = "Warning: You need to set a Simple API Access key from the Google API console:";
        $ret.= "\nhttp://developers.google.com/console";
    }
    return $ret;
}

function missingClientSecretsWarning() {
    $ret = "";
    if (isWebRequest()) {
        $ret = "
      <h3 class='warn'>
        Warning: You need to set Client ID, Client Secret and Redirect URI from the
        <a href='http://developers.google.com/console'>Google API console</a>
      </h3>";
    } 
    else {
        $ret = "Warning: You need to set Client ID, Client Secret and Redirect URI from the";
        $ret.= "Google API console:\nhttp://developers.google.com/console";
    }
    return $ret;
}

function missingServiceAccountDetailsWarning() {
    $ret = "";
    if (isWebRequest()) {
        $ret = "
      <h3 class='warn'>
        Warning: You need to set Client ID, Email address and the location of the Key from the
        <a href='http://developers.google.com/console'>Google API console</a>
      </h3>";
    } 
    else {
        $ret = "Warning: You need to set Client ID, Email address and the location of the Key from the";
        $ret.= "Google API console:\nhttp://developers.google.com/console";
    }
    return $ret;
}

