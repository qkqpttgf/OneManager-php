<?php
include 'conststr.php';

echo '<pre>' . json_encode($_SERVER, JSON_PRETTY_PRINT) . '</pre>';
if (!isset($_SERVER['REDIRECT_URL'])) $_SERVER['REDIRECT_URL'] = '/index.php';
if ($_SERVER['REDIRECT_URL']=='') $_SERVER['REDIRECT_URL']='/';
$path = $_SERVER['REDIRECT_URL'];

$getstr = substr(urldecode($_SERVER['REQUEST_URI']), strlen(urldecode($_SERVER['REDIRECT_URL'])));
    while (substr($getstr,0,1)=='/' || substr($getstr,0,1)=='?') $getstr = substr($getstr,1);
    $getstrarr = explode("&",$getstr);
    foreach ($getstrarr as $getvalues) if ($getvalues!='') {
        $pos = strpos($getvalues,"=");
		//echo $pos;
        if ($pos>0) {
            $getarry[urldecode(substr($getvalues,0,$pos))] = urldecode(substr($getvalues,$pos+1));
        } else $getarry[urldecode($getvalues)] = true;
    }
    if (isset($getarry)) {
        $_GET = $getarry;
    } else $_GET = '';
    
    echo '<pre>' . json_encode($_GET, JSON_PRETTY_PRINT) . '</pre>';
    
