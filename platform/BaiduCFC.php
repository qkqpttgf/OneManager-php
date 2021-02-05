<?php

function printInput($event, $context)
{
    if (strlen(json_encode($event['body']))>500) $event['body']=substr($event['body'],0,strpos($event['body'],'base64')+30) . '...Too Long!...' . substr($event['body'],-50);
    echo urldecode(json_encode($event, JSON_PRETTY_PRINT)) . '
 
' . urldecode(json_encode($context, JSON_PRETTY_PRINT)) . '
 
';
}

function GetGlobalVariable($event)
{
    $_GET = $event['queryStringParameters'];
    foreach ($_GET as $k => $v) {
        if ($v == '') $_GET[$k] = true;
    }
    $postbody = explode("&",$event['body']);
    foreach ($postbody as $postvalues) {
        $pos = strpos($postvalues,"=");
        $_POST[urldecode(substr($postvalues,0,$pos))]=urldecode(substr($postvalues,$pos+1));
    }
    $cookiebody = explode("; ",$event['headers']['Cookie']);
    foreach ($cookiebody as $cookievalues) {
        $pos = strpos($cookievalues,"=");
        $_COOKIE[urldecode(substr($cookievalues,0,$pos))]=urldecode(substr($cookievalues,$pos+1));
    }
    $_SERVER['HTTP_USER_AGENT'] = $event['headers']['User-Agent'];
    if (isset($event['headers']['authorization'])) {
        $basicAuth = splitfirst(base64_decode(splitfirst($event['headers']['authorization'], 'Basic ')[1]), ':');
        $_SERVER['PHP_AUTH_USER'] = $basicAuth[0];
        $_SERVER['PHP_AUTH_PW'] = $basicAuth[1];
    }
    $_SERVER['HTTP_TRANSLATE'] = $event['headers']['translate'];//'f'
    $_SERVER['BCE_CFC_RUNTIME_NAME'] = 'php7';
}

function GetPathSetting($event, $context)
{
    $_SERVER['firstacceptlanguage'] = strtolower(splitfirst(splitfirst($event['headers']['Accept-Language'],';')[0],',')[0]);
    $_SERVER['functionBrn'] = $context['functionBrn'];
    $_SERVER['base_path'] = '/';
    $path = $event['path'];
    if (substr($path,-1)=='/') $path=substr($path,0,-1);
    $_SERVER['is_guestup_path'] = is_guestup_path($path);
    $_SERVER['PHP_SELF'] = path_format($_SERVER['base_path'] . $path);
    $_SERVER['REMOTE_ADDR'] = $event['requestContext']['sourceIp'];
    $_SERVER['HTTP_X_REQUESTED_WITH'] = $event['headers']['X-Requested-With'];
    return $path;
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
    $disktags = explode("|",getConfig('disktag'));
    $diskconfig = json_decode(getenv($disktag), true);
    $tmp = [];
    $indisk = 0;
    $operatedisk = 0;
    foreach ($arr as $k => $v) {
        if (isInnerEnv($k)) {
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
            if (isBase64Env($k)) $tmp[$k] = base64y_encode($v);
            else $tmp[$k] = $v;
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
    $response = updateEnvironment($tmp, getConfig('SecretId'), getConfig('SecretKey'));
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
        $tmp['timezone'] = $_COOKIE['timezone'];
        $SecretId = getConfig('SecretId');
        if ($SecretId=='') {
            $SecretId = $_POST['SecretId'];
            $tmp['SecretId'] = $SecretId;
        }
        $SecretKey = getConfig('SecretKey');
        if ($SecretKey=='') {
            $SecretKey = $_POST['SecretKey'];
            $tmp['SecretKey'] = $SecretKey;
        }
        $response = setConfigResponse(SetbaseConfig($tmp, $SecretId, $SecretKey));
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
    }
    if ($_GET['install0']) {
        $html .= '
    <form action="?install1" method="post" onsubmit="return notnull(this);">
language:<br>';
        foreach ($constStr['languages'] as $key1 => $value1) {
            $html .= '
        <label><input type="radio" name="language" value="'.$key1.'" '.($key1==$constStr['language']?'checked':'').' onclick="changelanguage(\''.$key1.'\')">'.$value1.'</label><br>';
        }
        if (getConfig('SecretId')==''||getConfig('SecretKey')=='') $html .= '
        <a href="https://console.bce.baidu.com/iam/#/iam/accesslist" target="_blank">'.getconstStr('Create').' Access Key & Secret Key</a><br>
        <label>Access Key:<input name="SecretId" type="text" placeholder="" size=""></label><br>
        <label>Secret Key:<input name="SecretKey" type="text" placeholder="" size=""></label><br>';
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
        if (getConfig('SecretId')==''||getConfig('SecretKey')=='') $html .= '
            if (t.SecretId.value==\'\') {
                alert(\'input Access Key\');
                return false;
            }
            if (t.SecretKey.value==\'\') {
                alert(\'input Secret Key\');
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

function CFCAPIv1($Brn, $AccessKey, $SecretKey, $Method, $End, $data = '')
{
    // brn:bce:cfc:bj:c094b1ca1XXXXXXXXb8dea6ab482:function:fdsa:$LATEST
    $BRN = explode(':', $Brn);
    if ( !($BRN[0]=='brn' && $BRN[1]=='bce' && $BRN[2]=='cfc') ) {
        $tmp['code'] = 'BRN Error';
        $tmp['message'] = 'The BRN expect start with "brn:bce:cfc:", given: ' . $Brn . ' .';
        return json_encode($tmp);
    }
    $Region = $BRN[3];
    //$project_id = $BRN[4];
    $FunctionName = $BRN[6];
    $host = 'cfc.' . $Region . '.baidubce.com';
    date_default_timezone_set('UTC'); // unset last timezone setting
    $timestamp = date('Y-m-d\TH:i:s\Z');
    //date_default_timezone_set(get_timezone($_SERVER['timezone']));
    $authStringPrefix = 'bce-auth-v1/' . $AccessKey . '/' . $timestamp . '/1800' ;
    $path = '/v1/functions/' . $FunctionName . '/' . $End;
    $CanonicalURI = spurlencode($path, '/');
    $CanonicalQueryString = '';
    $CanonicalHeaders = 'host:' . $host;
    $CanonicalRequest = $Method . "\n" . $CanonicalURI . "\n" . $CanonicalQueryString . "\n" . $CanonicalHeaders;
    $SigningKey = hash_hmac('sha256', $authStringPrefix, $SecretKey);
    $Signature = hash_hmac('sha256', $CanonicalRequest, $SigningKey);
    $authorization = $authStringPrefix . '/host/' . $Signature;

    $p = 0;
    while ($response['stat']==0 && $p<3) {
        $response = curl(
            $Method,
            'https://' . $host . $path,
            $data,
            [
                'Authorization' => $authorization,
                'Content-type' => 'application/json'
            ]
        );
        $p++;
    }

    if ($response['stat']==0) {
        $tmp['code'] = 'Network Error';
        $tmp['message'] = 'Can not connect ' . $host;
        return json_encode($tmp);
    }
    if ($response['stat']!=200) {
        $tmp = json_decode($response['body'], true);
        $tmp['message'] .= '<br>' . $response['stat'] . '<br>' . $timestamp . PHP_EOL;
        return json_encode($tmp);
    }
    return $response['body'];
}

function getfunctioninfo($SecretId, $SecretKey)
{
    return CFCAPIv1($_SERVER['functionBrn'], $SecretId, $SecretKey, 'GET', 'configuration');
}

function updateEnvironment($Envs, $SecretId, $SecretKey)
{
    $FunctionConfig = json_decode(getfunctioninfo($SecretId, $SecretKey), true);
    $tmp_env = $FunctionConfig['Environment']['Variables'];
    foreach ($Envs as $key1 => $value1) {
        $tmp_env[$key1] = $value1;
    }
    $tmp_env = array_filter($tmp_env, 'array_value_isnot_null'); // remove null. 清除空值
    //ksort($tmp_env);
    sortConfig($tmp_env);

    $tmp['Environment']['Variables'] = $tmp_env;
    $data = json_encode($tmp);

    return CFCAPIv1($_SERVER['functionBrn'], $SecretId, $SecretKey, 'PUT', 'configuration', $data);
}

function SetbaseConfig($Envs, $SecretId, $SecretKey)
{
    $FunctionConfig = json_decode(getfunctioninfo($SecretId, $SecretKey), true);
    $tmp_env = $FunctionConfig['Environment']['Variables'];
    foreach ($Envs as $key1 => $value1) {
        $tmp_env[$key1] = $value1;
    }
    $tmp_env = array_filter($tmp_env, 'array_value_isnot_null'); // remove null. 清除空值
    ksort($tmp_env);

    $tmp['Timeout'] = 30;
    $tmp['Description'] = 'Onedrive index and manager in Baidu CFC.';
    $tmp['Environment']['Variables'] = $tmp_env;
    $data = json_encode($tmp);

    return CFCAPIv1($_SERVER['functionBrn'], $SecretId, $SecretKey, 'PUT', 'configuration', $data);
}

function updateProgram($SecretId, $SecretKey, $source)
{
    $tmp['ZipFile'] = base64_encode( file_get_contents($source) );
    $data = json_encode($tmp);
    return CFCAPIv1($_SERVER['functionBrn'], $SecretId, $SecretKey, 'PUT', 'code', $data);
}

function api_error($response)
{
    //return isset($response['code']);
    return !(isset($response['FunctionBrn']) && $response['FunctionBrn'] == $_SERVER['functionBrn']);
}

function api_error_msg($response)
{
    if (isset($response['code'])) $html = $response['code'] . '<br>
' . $response['message'];
    else $html = var_dump($response);
    return $html . '<br><br>
BRN: ' . $_SERVER['functionBrn'] . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>';
}

function setConfigResponse($response)
{
    //return $response;
    return json_decode( $response, true );
}

function OnekeyUpate($auth = 'BingoKingo', $project = 'Tfo', $branch = 'master')
{
    $source = '/tmp/code.zip';
    $outPath = '/tmp/';

    // 从github下载对应tar.gz，并解压
    $url = 'https://github.com/' . $auth . '/' . $project . '/tarball/' . urlencode($branch) . '/';
    $tarfile = '/tmp/github.tar.gz';
    file_put_contents($tarfile, file_get_contents($url));
    $phar = new PharData($tarfile);
    $html = $phar->extractTo($outPath, null, true);//路径 要解压的文件 是否覆盖

    // 获取包中目录名
    $tmp = scandir('phar://'.$tarfile);
    $name = $auth.'-'.$project;
    foreach ($tmp as $f) {
        if ( substr($f, 0, strlen($name)) == $name) {
            $outPath .= $f;
            break;
        }
    }
    // 放入配置文件
    //file_put_contents($outPath . '/config.php', file_get_contents(__DIR__.'/../config.php'));

    // 将目录中文件打包成zip
    //$zip=new ZipArchive();
    $zip=new PharData($source);
    //if($zip->open($source, ZipArchive::CREATE)){
        addFileToZip($zip, $outPath); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
    //    $zip->close(); //关闭处理的zip文件
    //}

    return updateProgram(getConfig('SecretId'), getConfig('SecretKey'), $source);
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
                $zip->addEmptyDir($path."/".$filename);
                addFileToZip($zip, $rootpath, $path."/".$filename);
            }else{ //将文件加入zip对象
                $newname = $path."/".$filename;
                if (substr($newname,0,1)=='/') $newname = substr($newname, 1);
                $zip->addFile($nowname, $newname);
                //$zip->renameName($nowname, $newname);
            }
        }
    }
    @closedir($path);
}
