<?php
include 'conststr.php';

//echo '<pre>' . json_encode($_SERVER, JSON_PRETTY_PRINT) . '</pre>';
if (!isset($_SERVER['REDIRECT_URL'])) $_SERVER['REDIRECT_URL'] = '/index.php';
if ($_SERVER['REDIRECT_URL']=='') $_SERVER['REDIRECT_URL']='/';
$path = $_SERVER['REDIRECT_URL'];
//echo 'path:'.$path;
$_GET = getGET();

function getGET()
{
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
        return $getarry;
    } else return '';
}
    //echo '<pre>' . json_encode($_GET, JSON_PRETTY_PRINT) . '</pre>';
function getconfig($str)
{
	$envs = json_decode(file_get_contents('config.json'));
	return $envs[$str];
}

config_oauth();
function config_oauth()
{
    global $constStr;
    $constStr['language'] = $_COOKIE['language'];
    if ($constStr['language']=='') $constStr['language'] = getconfig('language');
    if ($constStr['language']=='') $constStr['language'] = 'en-us';
    $_SERVER['sitename'] = getconfig('sitename');
    if (empty($_SERVER['sitename'])) $_SERVER['sitename'] = $constStr['defaultSitename'][$constStr['language']];
    $_SERVER['redirect_uri'] = 'https://scfonedrive.github.io';
    if (getconfig('Onedrive_ver')=='MS') {
        // MS
        // https://portal.azure.com
        $_SERVER['client_id'] = '4da3e7f2-bf6d-467c-aaf0-578078f0bf7c';
        $_SERVER['client_secret'] = '7/+ykq2xkfx:.DWjacuIRojIaaWL0QI6';
        $_SERVER['oauth_url'] = 'https://login.microsoftonline.com/common/oauth2/v2.0/';
        $_SERVER['api_url'] = 'https://graph.microsoft.com/v1.0/me/drive/root';
        $_SERVER['scope'] = 'https://graph.microsoft.com/Files.ReadWrite.All offline_access';
    }
    if (getenv('Onedrive_ver')=='CN') {
        // CN
        // https://portal.azure.cn
        $_SERVER['client_id'] = '04c3ca0b-8d07-4773-85ad-98b037d25631';
        $_SERVER['client_secret'] = 'h8@B7kFVOmj0+8HKBWeNTgl@pU/z4yLB';
        $_SERVER['oauth_url'] = 'https://login.partner.microsoftonline.cn/common/oauth2/v2.0/';
        $_SERVER['api_url'] = 'https://microsoftgraph.chinacloudapi.cn/v1.0/me/drive/root';
        $_SERVER['scope'] = 'https://microsoftgraph.chinacloudapi.cn/Files.ReadWrite.All offline_access';
    }
    if (getenv('Onedrive_ver')=='MSC') {
        // MS Customer
        // https://portal.azure.com
        $_SERVER['client_id'] = getconfig('client_id');
        $_SERVER['client_secret'] = getconfig('client_secret');
        $_SERVER['oauth_url'] = 'https://login.microsoftonline.com/common/oauth2/v2.0/';
        $_SERVER['api_url'] = 'https://graph.microsoft.com/v1.0/me/drive/root';
        $_SERVER['scope'] = 'https://graph.microsoft.com/Files.ReadWrite.All offline_access';
    }
    $_SERVER['client_secret'] = urlencode($_SERVER['client_secret']);
    $_SERVER['scope'] = urlencode($_SERVER['scope']);
}
