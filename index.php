<?php
//error_reporting(E_ALL & ~E_NOTICE);
error_reporting(0);

include 'vendor/autoload.php';
include 'conststr.php';
include 'common.php';

date_default_timezone_set('UTC');
//echo '<pre>'. json_encode($_SERVER, JSON_PRETTY_PRINT).'</pre>';
//echo '<pre>'. json_encode($_ENV, JSON_PRETTY_PRINT).'</pre>';
global $platform;
$platform = checkPlatform();
function checkPlatform() {
    if (isset($_SERVER['USER'])&&$_SERVER['USER']==='qcloud')
        return 'SCF';
    if (isset($_SERVER['FC_FUNC_CODE_PATH']))
        return 'FC';
    if (isset($_SERVER['RUNTIME_LOG_PATH']) && $_SERVER['RUNTIME_LOG_PATH']=='/home/snuser/log')
        return 'FG';
    if (isset($_SERVER['BCE_CFC_RUNTIME_NAME']) && $_SERVER['BCE_CFC_RUNTIME_NAME']=='php7')
        return 'CFC';
    if (isset($_SERVER['HEROKU_APP_DIR'])&&$_SERVER['HEROKU_APP_DIR']==='/app')
        return 'Heroku';
    if (isset($_SERVER['DOCUMENT_ROOT'])&&$_SERVER['DOCUMENT_ROOT']==='/var/task/user')
        return 'Vercel';
    if (isset($_SERVER['DOCUMENT_ROOT'])&&substr($_SERVER['DOCUMENT_ROOT'], 0, 13)==='/home/runner/')
        return 'Replit';
    return 'Normal';
}
function writebackPlatform($p) {
    if ('SCF'==$p) $_SERVER['USER']='qcloud';
    if ('FC'==$p) $_SERVER['FC_FUNC_CODE_PATH']=getenv('FC_FUNC_CODE_PATH');
    if ('FG'==$p) $_SERVER['RUNTIME_LOG_PATH']='/home/snuser/log';
    if ('CFC'==$p) $_SERVER['BCE_CFC_RUNTIME_NAME']='php7';
    //if ('Heroku'==$p) $_SERVER['HEROKU_APP_DIR']='/app';
    if ('Vercel'==$p) $_SERVER['DOCUMENT_ROOT']='/var/task/user';
    //if ('Replit'==$p) $_SERVER['DOCUMENT_ROOT']='/home/runner/';
}
if ('SCF'==$platform) {
    if (getenv('ONEMANAGER_CONFIG_SAVE')=='file') include 'platform/TencentSCF_file.php';
    else include 'platform/TencentSCF_env.php';
} elseif ('FC'==$platform) {
    include 'platform/AliyunFC.php';
} elseif ('FG'==$platform) {
    //if (getenv('ONEMANAGER_CONFIG_SAVE')=='file') include 'platform/HuaweiFG_file.php';
    //else include 'platform/HuaweiFG_env.php';
    echo 'FG' . PHP_EOL;
} elseif ('CFC'==$platform) {
    include 'platform/BaiduCFC.php';
} elseif ('Heroku'==$platform) {
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
    if ($re['isBase64Encoded']) echo base64_decode($re['body']);
    else echo $re['body'];
} elseif ('Vercel'==$platform) {
    if (getenv('ONEMANAGER_CONFIG_SAVE')=='env') include 'platform/Vercel_env.php';
    else include 'platform/Vercel.php';

    writebackPlatform('Vercel');
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
    if ($re['isBase64Encoded']) echo base64_decode($re['body']);
    else echo $re['body'];
} elseif ('Replit'==$platform) {
    include 'platform/Replit.php';

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
    if ($re['isBase64Encoded']) echo base64_decode($re['body']);
    else echo $re['body'];
} else {
    include 'platform/Normal.php';
    if (!function_exists('curl_init')) {
        http_response_code(500);
        echo '<font color="red">Need curl</font>, please install php-curl.';
        exit(1);
    }
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
    if ($re['isBase64Encoded']) echo base64_decode($re['body']);
    else echo $re['body'];
}

// Tencent SCF
function main_handler($event, $context)
{
    $event = json_decode(json_encode($event), true);
    $context = json_decode(json_encode($context), true);
    printInput($event, $context);
    if ( $event['requestContext']['serviceId'] === substr($event['headers']['host'], 0, strlen($event['requestContext']['serviceId'])) ) {
        if ($event['path']==='/' . $context['function_name']) return output('add / at last.', 308, ['Location'=>'/'.$event['requestContext']['stage'].'/'.$context['function_name'].'/']);
    }
    unset($_POST);
    unset($_GET);
    unset($_COOKIE);
    unset($_SERVER);
    writebackPlatform('SCF');
    GetGlobalVariable($event);
    //echo '<pre>'. json_encode($_COOKIE, JSON_PRETTY_PRINT).'</pre>';
    $path = GetPathSetting($event, $context);

    return main($path);
}

// Aliyun FC & Huawei FG & Baidu CFC
function handler($event, $context)
{
    global $platform;
    if ('FC'==$platform) {
        // Aliyun FC
        set_error_handler("myErrorHandler");
        $tmp = array(
            'method' => $event->getMethod(),
            'clientIP' => $event->getAttribute("clientIP"),
            'requestURI' => $event->getAttribute("requestURI"),
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
        writebackPlatform('FC');
        GetGlobalVariable($event);
        $path = GetPathSetting($event, $context);

        $re = main($path);

        return new RingCentral\Psr7\Response($re['statusCode'], $re['headers'], ($re['isBase64Encoded']?base64_decode($re['body']):$re['body']));

    } elseif ('FG'==$platform) {
        // Huawei FG
        global $contextUserData;
        $contextUserData = $context;
        if ($context->getUserData('ONEMANAGER_CONFIG_SAVE')=='file') include_once 'platform/HuaweiFG_file.php';
        else include_once 'platform/HuaweiFG_env.php';

        $event = json_decode(json_encode($event), true);
        if ($event['isBase64Encoded']) $event['body'] = base64_decode($event['body']);

        printInput($event, $context);
        unset($_POST);
        unset($_GET);
        unset($_COOKIE);
        unset($_SERVER);
        writebackPlatform('FG');
        GetGlobalVariable($event);
        //echo '<pre>'. json_encode($_COOKIE, JSON_PRETTY_PRINT).'</pre>';
        $path = GetPathSetting($event, $context);

        return main($path);

    } elseif ('CFC'==$platform) {
        // Baidu CFC
        //$html = '<pre>'. json_encode($event, JSON_PRETTY_PRINT).'</pre>';
        //$html .= '<pre>'. json_encode($context, JSON_PRETTY_PRINT).'</pre>';
        //$html .= '<pre>'. json_encode($_SERVER, JSON_PRETTY_PRINT).'</pre>';
        //$html .= $event['path'];
        //$html .= $context['functionBrn'];
        //return json_encode(output($html), JSON_FORCE_OBJECT);

        printInput($event, $context);
        unset($_POST);
        unset($_GET);
        unset($_COOKIE);
        unset($_SERVER);
        writebackPlatform('CFC');
        GetGlobalVariable($event);
        //echo '<pre>'. json_encode($_COOKIE, JSON_PRETTY_PRINT).'</pre>';
        $path = GetPathSetting($event, $context);

        return json_encode(main($path), JSON_FORCE_OBJECT);

    }
}
