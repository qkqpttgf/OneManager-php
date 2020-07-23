<?php
error_reporting(E_ALL & ~E_NOTICE);
include 'vendor/autoload.php';
include 'conststr.php';
include 'common.php';

//echo '<pre>'. json_encode($_SERVER, JSON_PRETTY_PRINT).'</pre>';
if (isset($_SERVER['USER'])&&$_SERVER['USER']==='qcloud') {
    include 'platform/TencentSCF.php';
} elseif (isset($_SERVER['FC_SERVER_PATH'])&&$_SERVER['FC_SERVER_PATH']==='/var/fc/runtime/php7.2') {
    include 'platform/AliyunFC.php';
} elseif ($_SERVER['_APP_SHARE_DIR']=='/var/share/CFF/processrouter') {
    include 'platform/HuaweiFG.php';
} elseif (isset($_SERVER['HEROKU_APP_DIR'])&&$_SERVER['HEROKU_APP_DIR']==='/app') {
    include 'platform/Heroku.php';
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
    include 'platform/Normal.php';
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

// Tencent SCF
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

// Aliyun FC & Huawei FG
function handler($event, $context)
{
    if (isset($_SERVER['FC_SERVER_PATH'])&&$_SERVER['FC_SERVER_PATH']==='/var/fc/runtime/php7.2') {
        // Aliyun FC
        set_error_handler("myErrorHandler");
        $tmp = array(
            'method' => $event->getMethod(),
            'clientIP' => $event->getAttribute("clientIP"),
            'eventURI' => $event->getAttribute("eventURI"),
            'path' => spurlencode($event->getAttribute("path"), '/'),
            'queryString' => $event->getQueryParams(),
            'headers' => $event->getHeaders(),
            'body' => $event->getBody()->getContents(),
        );
        $event = $tmp;
        $context = json_decode(json_encode($context), true);
        printInput($event, $context);
        unset($_POST);
        unset($_GET);
        unset($_COOKIE);
        unset($_SERVER);
        GetGlobalVariable($event);
        $path = GetPathSetting($event, $context);

        $re = main($path);

        return new RingCentral\Psr7\Response($re['statusCode'], $re['headers'], $re['body']);

    } elseif ($_SERVER['_APP_SHARE_DIR']=='/var/share/CFF/processrouter') {
        // Huawei FG
        global $contextUserData;
        $contextUserData = $context;
        $event = json_decode(json_encode($event), true);
        if ($event['isBase64Encoded']) $event['body'] = base64_decode($event['body']);

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
}

// used by Aliyun FC
function myErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    switch ($errno) {
    case E_USER_ERROR:
        $errInfo = array(
            "errorMessage" => $errstr,
            "errorType"    => \ServerlessFC\friendly_error_type($errno),
            "stackTrace"   => array(
                "file" => $errfile,
                "line" => $errline,
            ),
        );
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        break;

    default: // E_USER_WARNING | E_USER_NOTICE
        break;
    }

    return true;
}
