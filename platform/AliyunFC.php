<?php
    // https://help.aliyun.com/document_detail/53252.html
    // https://github.com/aliyun/fc-php-sdk/blob/master/src/AliyunFC/Client.php

function printInput($event, $context)
{
    if (strlen(json_encode($event['body']))>500) $event['body']=substr($event['body'],0,strpos($event['body'],'base64')+30) . '...Too Long!...' . substr($event['body'],-50);
    echo urldecode(json_encode($event, JSON_PRETTY_PRINT)) . '
 
' . urldecode(json_encode($context, JSON_PRETTY_PRINT)) . '
 
';
}

function GetGlobalVariable($event)
{
    $_GET = $event['queryString'];
    foreach ($_GET as $k => $v) {
        if ($v=='') $_GET[$k] = true;
    }
    $postbody = explode("&",$event['body']);
    foreach ($postbody as $postvalues) {
        $pos = strpos($postvalues,"=");
        $_POST[urldecode(substr($postvalues,0,$pos))]=urldecode(substr($postvalues,$pos+1));
    }
    $cookiebody = explode("; ",$event['headers']['Cookie'][0]);
    foreach ($cookiebody as $cookievalues) {
        $pos = strpos($cookievalues,"=");
        $_COOKIE[urldecode(substr($cookievalues,0,$pos))]=urldecode(substr($cookievalues,$pos+1));
    }
}

function GetPathSetting($event, $context)
{
    $_SERVER['firstacceptlanguage'] = strtolower(splitfirst(splitfirst($event['headers']['Accept-Language'][0],';')[0],',')[0]);
    $_SERVER['accountId'] = $context['accountId'];
    $_SERVER['region'] = $context['region'];
    $_SERVER['service_name'] = $context['service']['name'];
    $_SERVER['function_name'] = $context['function']['name'];
    //$path = str_replace('%5D', ']', str_replace('%5B', '[', $event['path']));//%5B
    //$path = $event['path'];
    $path = $event['requestURI'];
    if (strpos($path, '?')) $path = substr($path, 0, strpos($path, '?'));
    $tmp = urldecode($event['requestURI']);
    if (strpos($tmp, '?')) $tmp = substr($tmp, 0, strpos($tmp, '?'));
    if ($path=='/'||$path=='') {
        $_SERVER['base_path'] = $tmp;
    } else {
        while ($tmp!=urldecode($tmp)) $tmp = urldecode($tmp);
        $tmp1 = urldecode($event['path']);
        while ($tmp1!=urldecode($tmp1)) $tmp1 = urldecode($tmp1);
        $_SERVER['base_path'] = substr($tmp, 0, strlen($tmp)-strlen($tmp1)+1);
        //$_SERVER['base_path'] = substr($tmp, 0, strlen(urldecode($event['path'])));
    }
    $_SERVER['base_path'] = spurlencode($_SERVER['base_path'], '/');

    if (substr($path,-1)=='/') $path=substr($path,0,-1);
    $_SERVER['is_guestup_path'] = is_guestup_path($path);
    //$_SERVER['PHP_SELF'] = path_format($_SERVER['base_path'] . $path);
    $_SERVER['REMOTE_ADDR'] = $event['clientIP'];
    $_SERVER['HTTP_X_REQUESTED_WITH'] = $event['headers']['X-Requested-With'][0];
    if (isset($event['headers']['Authorization'])) {
        $basicAuth = splitfirst(base64_decode(splitfirst($event['headers']['Authorization'][0], 'Basic ')[1]), ':');
        $_SERVER['PHP_AUTH_USER'] = $basicAuth[0];
        $_SERVER['PHP_AUTH_PW'] = $basicAuth[1];
    }
    $_SERVER['HTTP_HOST'] = $event['headers']['Host'][0];
    $_SERVER['REQUEST_SCHEME'] = $event['headers']['X-Forwarded-Proto'][0];
    $_SERVER['host'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    $_SERVER['referhost'] = explode('/', $event['headers']['Referer'][0])[2];
    $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $event['headers']['If-Modified-Since'][0];
    $_SERVER['FC_SERVER_PATH'] = '/var/fc/runtime/php7.2';
    return $path;
    //return spurlencode($path, '/');
}

function getConfig($str, $disktag = '')
{
    if (isInnerEnv($str)) {
        if ($disktag=='') $disktag = $_SERVER['disktag'];
        $env = json_decode(getenv($disktag), true);
        if (isset($env[$str])) {
            if (isBase64Env($str)) return base64y_decode($env[$str]);
            else return $env[$str];
        }
    } else {
        if (isBase64Env($str)) return base64y_decode(getenv($str));
        else return getenv($str);
    }
    return '';
}

function setConfig($arr, $disktag = '')
{
    if ($disktag=='') $disktag = $_SERVER['disktag'];
    $disktags = explode("|", getConfig('disktag'));
    $diskconfig = json_decode(getenv($disktag), true);
    $tmp = [];
    $indisk = 0;
    $operatedisk = 0;
    foreach ($arr as $k => $v) {
        if (isCommonEnv($k)) {
            if (isBase64Env($k)) $tmp[$k] = base64y_encode($v);
            else $tmp[$k] = $v;
        } elseif (isInnerEnv($k)) {
            if (isBase64Env($k)) $diskconfig[$k] = base64y_encode($v);
            else $diskconfig[$k] = $v;
            $indisk = 1;
        } elseif ($k=='disktag_add') {
            array_push($disktags, $v);
            $operatedisk = 1;
        } elseif ($k=='disktag_del') {
            $disktags = array_diff($disktags, [ $v ]);
            $tmp[$v] = '';
            $operatedisk = 1;
        } elseif ($k=='disktag_copy') {
            $newtag = $v . '_' . date("Ymd_His");
            $tmp[$newtag] = getConfig($v);
            array_push($disktags, $newtag);
            $operatedisk = 1;
        } elseif ($k=='disktag_rename' || $k=='disktag_newname') {
            if ($arr['disktag_rename']!=$arr['disktag_newname']) $operatedisk = 1;
        } else {
            $tmp[$k] = json_encode($v);
        }
    }
    if ($indisk) {
        $diskconfig = array_filter($diskconfig, 'array_value_isnot_null');
        ksort($diskconfig);
        $tmp[$disktag] = json_encode($diskconfig);
    }
    if ($operatedisk) {
        if (isset($arr['disktag_newname']) && $arr['disktag_newname']!='') {
            $tags = [];
            foreach ($disktags as $tag) {
                if ($tag==$arr['disktag_rename']) array_push($tags, $arr['disktag_newname']);
                else array_push($tags, $tag);
            }
            $tmp['disktag'] = implode('|', $tags);
            $tmp[$arr['disktag_newname']] = getConfig($arr['disktag_rename']);
            $tmp[$arr['disktag_rename']] = '';
        } else {
            $disktags = array_unique($disktags);
            foreach ($disktags as $disktag) if ($disktag!='') $disktag_s .= $disktag . '|';
            if ($disktag_s!='') $tmp['disktag'] = substr($disktag_s, 0, -1);
            else $tmp['disktag'] = '';
        }
    }
//    echo '正式设置：'.json_encode($tmp,JSON_PRETTY_PRINT).'
//';
    $response = updateEnvironment($tmp, $_SERVER['accountId'], $_SERVER['region'], $_SERVER['service_name'], $_SERVER['function_name'], getConfig('AccessKeyID'), getConfig('AccessKeySecret'));
    //WaitSCFStat();
    return $response;
}

function install()
{
    global $constStr;
    if ($_GET['install2']) {
        $tmp['admin'] = $_POST['admin'];
        $response = setConfigResponse( setConfig($tmp) );
        if (api_error($response)) {
            $html = api_error_msg($response);
            $title = 'Error';
            return message($html, $title, 201);
        }
        if (needUpdate()) {
            OnekeyUpate();
            return message('update to github version, reinstall.
    <script>
        var expd = new Date();
        expd.setTime(expd.getTime()+(2*60*60*1000));
        var expires = "expires="+expd.toGMTString();
        document.cookie=\'language=; path=/; \'+expires;
    </script>
    <meta http-equiv="refresh" content="3;URL=' . $url . '">', 'Program updating', 201);
        }
        return output('Jump
    <script>
        var expd = new Date();
        expd.setTime(expd.getTime()+(2*60*60*1000));
        var expires = "expires="+expd.toGMTString();
        document.cookie=\'language=; path=/; \'+expires;
    </script>
    <meta http-equiv="refresh" content="3;URL=' . path_format($_SERVER['base_path'] . '/') . '">', 302);
    }
    if ($_GET['install1']) {
        //if ($_POST['admin']!='') {
            $tmp['timezone'] = $_COOKIE['timezone'];
            $AccessKeyID = getConfig('AccessKeyID');
            if ($AccessKeyID=='') {
                $AccessKeyID = $_POST['AccessKeyID'];
                $tmp['AccessKeyID'] = $AccessKeyID;
            }
            $AccessKeySecret = getConfig('AccessKeySecret');
            if ($AccessKeySecret=='') {
                $AccessKeySecret = $_POST['AccessKeySecret'];
                $tmp['AccessKeySecret'] = $AccessKeySecret;
            }
            $response = setConfigResponse( SetbaseConfig($tmp, $_SERVER['accountId'], $_SERVER['region'], $_SERVER['service_name'], $_SERVER['function_name'], $AccessKeyID, $AccessKeySecret) );
            if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
                return message($html, $title, 201);
            } else {
                $html .= '
    <form action="?install2" method="post" onsubmit="return notnull(this);">
        <label>'.getconstStr('SetAdminPassword').':<input name="admin" type="password" placeholder="' . getconstStr('EnvironmentsDescription')['admin'] . '" size="' . strlen(getconstStr('EnvironmentsDescription')['admin']) . '"></label><br>
        <input type="submit" value="'.getconstStr('Submit').'">
    </form>
    <script>
        function notnull(t)
        {
            if (t.admin.value==\'\') {
                alert(\''.getconstStr('SetAdminPassword').'\');
                return false;
            }
            return true;
        }
    </script>';
                $title = getconstStr('SetAdminPassword');
                return message($html, $title, 201);
            }
        //}
    }
    if ($_GET['install0']) {
        $html .= '
    <form action="?install1" method="post" onsubmit="return notnull(this);">
language:<br>';
        foreach ($constStr['languages'] as $key1 => $value1) {
            $html .= '
        <label><input type="radio" name="language" value="'.$key1.'" '.($key1==$constStr['language']?'checked':'').' onclick="changelanguage(\''.$key1.'\')">'.$value1.'</label><br>';
        }
        if (getConfig('AccessKeyID')==''||getConfig('AccessKeySecret')=='') $html .= '
        <a href="https://usercenter.console.aliyun.com/?#/manage/ak" target="_blank">'.getconstStr('Create').' AccessKeyID & AccessKeySecret</a><br>
        <label>AccessKeyID:<input name="AccessKeyID" type="text" placeholder="" size=""></label><br>
        <label>AccessKeySecret:<input name="AccessKeySecret" type="text" placeholder="" size=""></label><br>';
        $html .= '
        <input type="submit" value="'.getconstStr('Submit').'">
    </form>
    <script>
        var nowtime= new Date();
        var timezone = 0-nowtime.getTimezoneOffset()/60;
        var expd = new Date();
        expd.setTime(expd.getTime()+(2*60*60*1000));
        var expires = "expires="+expd.toGMTString();
        document.cookie="timezone="+timezone+"; path=/; "+expires;
        function changelanguage(str)
        {
            var expd = new Date();
            expd.setTime(expd.getTime()+(2*60*60*1000));
            var expires = "expires="+expd.toGMTString();
            document.cookie=\'language=\'+str+\'; path=/; \'+expires;
            location.href = location.href;
        }
        function notnull(t)
        {';
        if (getConfig('AccessKeyID')==''||getConfig('AccessKeySecret')=='') $html .= '
            if (t.AccessKeyID.value==\'\') {
                alert(\'input AccessKeyID\');
                return false;
            }
            if (t.AccessKeySecret.value==\'\') {
                alert(\'input SecretKey\');
                return false;
            }';
        $html .= '
            return true;
        }
    </script>';
        $title = getconstStr('SelectLanguage');
        return message($html, $title, 201);
    }
    $html .= '<a href="?install0">'.getconstStr('ClickInstall').'</a>, '.getconstStr('LogintoBind');
    $title = 'Install';
    return message($html, $title, 201);
}

function FCAPI2016($config, $Method, $data = '')
{
    $accountId = $config['accountId'];
    $region = $config['region'];
    $service_name = $config['service_name'];
    $function_name = $config['function_name'];
    $AccessKeyID = $config['AccessKeyID'];
    $AccessKeySecret = $config['AccessKeySecret'];

    $host = $accountId . '.' . $region . '-internal.fc.aliyuncs.com';
    $path = '/2016-08-15/services/' . $service_name . '/functions/' . $function_name;
    $url = 'https://' . $host . $path;

    $ContentMd5 = '';
    $ContentType = 'application/json';
    date_default_timezone_set('UTC'); // unset last timezone setting
    $Date = substr(gmdate("r", time()), 0, -5) . 'GMT';
    $CanonicalizedFCHeaders = '';
    $CanonicalizedResource = $path;

    $signaturestr = $Method . "\n" . $ContentMd5 . "\n" . $ContentType . "\n" . $Date . "\n" . $CanonicalizedFCHeaders . $CanonicalizedResource;
    $signature = base64_encode(hash_hmac('sha256', $signaturestr, $AccessKeySecret, true));

    $header['Host'] = $host;
    $header['Date'] = $Date;
    $header['Content-Type'] = $ContentType;
    $header['Authorization'] = 'FC ' . $AccessKeyID . ':' . $signature;
    $header['Content-Length'] = strlen($data);

    //return curl($Method, $url, $data, $header)['body'];
    $p = 0;
    while ($response['stat']==0 && $p<3) {
        $response = curl($Method, $url, $data, $header);
        $p++;
    }

    if ($response['stat']==0) {
        $tmp['ErrorCode'] = 'Network Error';
        $tmp['ErrorMessage'] = 'Can not connect ' . $host;
        return json_encode($tmp);
    }
    if ($response['stat']!=200) {
        $tmp = json_decode($response['body'], true);
        $tmp['ErrorMessage'] .= '<br>' . $response['stat'] . '<br>' . $signaturestr . '<br>' . json_encode($header) . PHP_EOL;
        return json_encode($tmp);
    }
    return $response['body'];
}

function getfunctioninfo($config)
{
    return FCAPI2016($config, 'GET');
}

function updateEnvironment($Envs, $accountId, $region, $service_name, $function_name, $AccessKeyID, $AccessKeySecret)
{
    //print_r($Envs);
    $config['accountId'] = $accountId;
    $config['region'] = $region;
    $config['service_name'] = $service_name;
    $config['function_name'] = $function_name;
    $config['AccessKeyID'] = $AccessKeyID;
    $config['AccessKeySecret'] = $AccessKeySecret;

    $tmp = json_decode(getfunctioninfo($config), true);
    foreach ($tmp['environmentVariables'] as $key => $value ) {
        $tmp_env[$key] = $value;
    }
    foreach ($Envs as $key1 => $value1) {
        $tmp_env[$key1] = $value1;
    }
    $tmp_env = array_filter($tmp_env, 'array_value_isnot_null'); // remove null. 清除空值
    //ksort($tmp_env);
    sortConfig($tmp_env);

    $tmpdata['environmentVariables'] = $tmp_env;
    return FCAPI2016($config, 'PUT', json_encode($tmpdata));
}

function SetbaseConfig($Envs, $accountId, $region, $service_name, $function_name, $AccessKeyID, $AccessKeySecret)
{
    $config['accountId'] = $accountId;
    $config['region'] = $region;
    $config['service_name'] = $service_name;
    $config['function_name'] = $function_name;
    $config['AccessKeyID'] = $AccessKeyID;
    $config['AccessKeySecret'] = $AccessKeySecret;

    $tmp = json_decode(getfunctioninfo($config), true);
    foreach ($tmp['environmentVariables'] as $key => $value ) {
        $tmp_env[$key] = $value;
    }
    foreach ($Envs as $key1 => $value1) {
        $tmp_env[$key1] = $value1;
    }
    $tmp_env = array_filter($tmp_env, 'array_value_isnot_null'); // remove null. 清除空值
    ksort($tmp_env);

    $tmpdata['description'] = 'Onedrive index and manager in Aliyun FC.';
    $tmpdata['memorySize'] = 128;
    $tmpdata['timeout'] = 30;
    $tmpdata['environmentVariables'] = $tmp_env;

    return FCAPI2016($config, 'PUT', json_encode($tmpdata));
}

function updateProgram($accountId, $region, $service_name, $function_name, $AccessKeyID, $AccessKeySecret, $source)
{
    $config['accountId'] = $accountId;
    $config['region'] = $region;
    $config['service_name'] = $service_name;
    $config['function_name'] = $function_name;
    $config['AccessKeyID'] = $AccessKeyID;
    $config['AccessKeySecret'] = $AccessKeySecret;

    $tmp = json_decode(getfunctioninfo($config), true);

    $tmpdata['code']['zipFile'] = base64_encode( file_get_contents($source) );

    return FCAPI2016($config, 'PUT', json_encode($tmpdata));
}

function api_error($response)
{
    return isset($response['ErrorMessage']);
}

function api_error_msg($response)
{
    return $response['ErrorCode'] . '<br>
' . $response['ErrorMessage'] . '<br><br>

accountId:' . $_SERVER['accountId'] . '<br>
region:' . $_SERVER['region'] . '<br>
service_name:' . $_SERVER['service_name'] . '<br>
function_name:' . $_SERVER['function_name'] . '<br>

<button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>';
}

function setConfigResponse($response)
{
    return json_decode($response, true);
}

function OnekeyUpate($auth = 'qkqpttgf', $project = 'OneManager-php', $branch = 'master')
{
    $source = '/tmp/code.zip';
    $outPath = '/tmp/';

    // 从github下载对应tar.gz，并解压
    $url = 'https://github.com/' . $auth . '/' . $project . '/tarball/' . urlencode($branch) . '/';
    $tarfile = '/tmp/github.tar.gz';
    file_put_contents($tarfile, file_get_contents($url));
    $phar = new PharData($tarfile);
    $html = $phar->extractTo($outPath, null, true);//路径 要解压的文件 是否覆盖

    // 获取解压出的目录名
/*
    @ob_start();
    passthru('ls /tmp | grep '.$auth.'-'.$project.'',$stat);
            $html.='状态：' . $stat . '
    结果：
    ';
    $archivefolder = ob_get_clean();
    if (substr($archivefolder,-1)==PHP_EOL) $archivefolder = substr($archivefolder, 0, -1);
    $outPath .= $archivefolder;
    $html.=htmlspecialchars($archivefolder);
    //return $html;
*/
    $tmp = scandir($outPath);
    $name = $auth.'-'.$project;
    foreach ($tmp as $f) {
        if ( substr($f, 0, strlen($name)) == $name) {
            $outPath .= $f;
            break;
        }
    }

    // 将目录中文件打包成zip
    $zip=new ZipArchive();
    if($zip->open($source, ZipArchive::CREATE)){
        addFileToZip($zip, $outPath); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
        $zip->close(); //关闭处理的zip文件
    }

    return updateProgram($_SERVER['accountId'], $_SERVER['region'], $_SERVER['service_name'], $_SERVER['function_name'], getConfig('AccessKeyID'), getConfig('AccessKeySecret'), $source);
}

function addFileToZip($zip, $rootpath, $path = '')
{
    if (substr($rootpath,-1)=='/') $rootpath = substr($rootpath, 0, -1);
    if (substr($path,0,1)=='/') $path = substr($path, 1);
    $handler=opendir(path_format($rootpath.'/'.$path)); //打开当前文件夹由$path指定。
    while($filename=readdir($handler)){
        if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
            $nowname = path_format($rootpath.'/'.$path."/".$filename);
            if(is_dir($nowname)){// 如果读取的某个对象是文件夹，则递归
                addFileToZip($zip, $rootpath, $path."/".$filename);
            }else{ //将文件加入zip对象
                $zip->addFile($nowname);
                $newname = $path."/".$filename;
                if (substr($newname,0,1)=='/') $newname = substr($newname, 1);
                $zip->renameName($nowname, $newname);
            }
        }
    }
    @closedir($path);
}

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

function WaitFunction() {
    return true;
}
