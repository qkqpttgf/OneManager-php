<?php
include 'vendor/autoload.php';
include 'conststr.php';
include 'function/common.php';

//echo '<pre>'. json_encode($_SERVER, JSON_PRETTY_PRINT).'</pre>';
if ($_SERVER['USER']==='qcloud') {
    include 'function/scf.php';
} elseif ($_SERVER['HEROKU_APP_DIR']==='/app') {
    include 'function/heroku.php';
    $path = getpath();
    //echo 'path:'. $path;
    $_GET = getGET();
    //echo '<pre>'. json_encode($_GET, JSON_PRETTY_PRINT).'</pre>';
    $re = main($path);
    $sendHeaders = array();
    foreach ($re['headers'] as $headerName => $headerVal) {
        header($headerName . ': ' . $headerVal, true);
    }
    http_response_code($re['statusCode']);
    echo $re['body'];
} else {
    include 'function/normal.php';
    $path = getpath();
    //echo 'path:'. $path;
    $_GET = getGET();
    //echo '<pre>'. json_encode($_GET, JSON_PRETTY_PRINT).'</pre>';

    $re = main($path);
    $sendHeaders = array();
    foreach ($re['headers'] as $headerName => $headerVal) {
        header($headerName . ': ' . $headerVal, true);
    }
    http_response_code($re['statusCode']);
    echo $re['body'];
}

function main_handler($event, $context)
{
    $event = json_decode(json_encode($event), true);
    $context = json_decode(json_encode($context), true);
    printInput($event, $context);
    unset($_POST);
    unset($_GET);
    unset($_COOKIE);
    unset($_SERVER);
    GetGlobalVariable($event);
    //echo '<pre>'. json_encode($_COOKIE, JSON_PRETTY_PRINT).'</pre>';
    $path = GetPathSetting($event, $context);

    return main($path);
}
