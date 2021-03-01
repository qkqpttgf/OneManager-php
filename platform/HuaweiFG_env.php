<?php
global $contextUserData;

function printInput($event, $context)
{
    $tmp['eventID'] = $context->geteventID();
    $tmp['RemainingTimeInMilliSeconds'] = $context->getRemainingTimeInMilliSeconds();
    $tmp['AccessKey'] = $context->getAccessKey();
    $tmp['SecretKey'] = $context->getSecretKey();
    $tmp['UserData']['HW_urn'] = $context->getUserData('HW_urn');
    $tmp['FunctionName'] = $context->getFunctionName();
    $tmp['RunningTimeInSeconds'] = $context->getRunningTimeInSeconds();
    $tmp['Version'] = $context->getVersion();
    $tmp['MemorySize'] = $context->getMemorySize();
    $tmp['CPUNumber'] = $context->getCPUNumber();
    $tmp['ProjectID'] = $context->getProjectID();
    $tmp['Package'] = $context->Package();
    $tmp['Token'] = $context->getToken();
    $tmp['Logger'] = $context->getLogger();

    if (strlen(json_encode($event['body']))>500) $event['body']=substr($event['body'],0,strpos($event['body'],'base64')+30) . '...Too Long!...' . substr($event['body'],-50);
    echo urldecode(json_encode($event, JSON_PRETTY_PRINT)) . '
 
' . urldecode(json_encode($tmp, JSON_PRETTY_PRINT)) . '
 
';
}

function GetGlobalVariable($event)
{
    $_GET = $event['queryStringParameters'];
    $postbody = explode("&",$event['body']);
    foreach ($postbody as $postvalues) {
        $pos = strpos($postvalues,"=");
        $_POST[urldecode(substr($postvalues,0,$pos))]=urldecode(substr($postvalues,$pos+1));
    }
    $cookiebody = explode("; ",$event['headers']['cookie']);
    foreach ($cookiebody as $cookievalues) {
        $pos = strpos($cookievalues,"=");
        $_COOKIE[urldecode(substr($cookievalues,0,$pos))]=urldecode(substr($cookievalues,$pos+1));
    }
}

function GetPathSetting($event, $context)
{
    $_SERVER['firstacceptlanguage'] = strtolower(splitfirst(splitfirst($event['headers']['accept-language'],';')[0],',')[0]);
    $_SERVER['function_name'] = $context->getFunctionName();
    $_SERVER['ProjectID'] = $context->getProjectID();
    $host_name = $event['headers']['host'];
    $_SERVER['HTTP_HOST'] = $host_name;
    $path = path_format($event['pathParameters'][''].'/');
    $_SERVER['base_path'] = path_format($event['path'].'/');
    if (  $_SERVER['base_path'] == $path ) {
        $_SERVER['base_path'] = '/';
    } else {
        $_SERVER['base_path'] = substr($_SERVER['base_path'], 0, -strlen($path));
        if ($_SERVER['base_path']=='') $_SERVER['base_path'] = '/';
    }
    if (substr($path,-1)=='/') $path=substr($path,0,-1);
    $_SERVER['is_guestup_path'] = is_guestup_path($path);
    $_SERVER['PHP_SELF'] = path_format($_SERVER['base_path'] . $path);
    $_SERVER['REMOTE_ADDR'] = $event['headers']['x-real-ip'];
    $_SERVER['HTTP_X_REQUESTED_WITH'] = $event['headers']['x-requested-with'];
    $_SERVER['HTTP_USER_AGENT'] = $event['headers']['user-agent'];
    if (isset($event['headers']['authorization'])) {
        $basicAuth = splitfirst(base64_decode(splitfirst($event['headers']['authorization'], 'Basic ')[1]), ':');
        $_SERVER['PHP_AUTH_USER'] = $basicAuth[0];
        $_SERVER['PHP_AUTH_PW'] = $basicAuth[1];
    }
    $_SERVER['REQUEST_SCHEME'] = $event['headers']['x-forwarded-proto'];
    $_SERVER['host'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    //if ($_SERVER['HTTP_REFERER']!='') 
    $_SERVER['referhost'] = explode('/', $event['headers']['referer'])[2];
    $_SERVER['HTTP_TRANSLATE'] = $event['headers']['translate'];//'f'
    $_SERVER['_APP_SHARE_DIR'] = '/var/share/CFF/processrouter';
    return $path;
}

function getConfig($str, $disktag = '')
{
    global $contextUserData;
    if (isInnerEnv($str)) {
        if ($disktag=='') $disktag = $_SERVER['disktag'];
        $env = json_decode($contextUserData->getUserData($disktag), true);
        if (isset($env[$str])) {
            if (isBase64Env($str)) return base64y_decode($env[$str]);
            else return $env[$str];
        }
    } else {
        if (isBase64Env($str)) return base64y_decode($contextUserData->getUserData($str));
        else return $contextUserData->getUserData($str);
    }
    return '';
}

function setConfig($arr, $disktag = '')
{
    global $contextUserData;
    if ($disktag=='') $disktag = $_SERVER['disktag'];
    $disktags = explode("|",getConfig('disktag'));
    $diskconfig = json_decode($contextUserData->getUserData($disktag), true);
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
    $response = updateEnvironment($tmp, getConfig('HW_urn'), getConfig('HW_key'), getConfig('HW_secret'));
    return $response;
}

function install()
{
    global $constStr;
    global $contextUserData;
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
            $tmp['HW_urn'] = getConfig('HW_urn');
            if ($tmp['HW_urn']=='') {
                $tmp['HW_urn'] = $_POST['HW_urn'];
            }
            $tmp['HW_key'] = getConfig('HW_key');
            if ($tmp['HW_key']=='') {
                $tmp['HW_key'] = $_POST['HW_key'];
            }
            $tmp['HW_secret'] = getConfig('HW_secret');
            if ($tmp['HW_secret']=='') {
                $tmp['HW_secret'] = $_POST['HW_secret'];
            }
            $tmp['ONEMANAGER_CONFIG_SAVE'] = $_POST['ONEMANAGER_CONFIG_SAVE'];
            //$response = json_decode(SetbaseConfig($tmp, $HW_urn, $HW_name, $HW_pwd), true)['Response'];
            $response = setConfigResponse( SetbaseConfig($tmp, $tmp['HW_urn'], $tmp['HW_key'], $tmp['HW_secret']) );
            if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
                return message($html, $title, 201);
            } else {
                if ($tmp['ONEMANAGER_CONFIG_SAVE'] == 'file') {
                    $html = getconstStr('ONEMANAGER_CONFIG_SAVE_FILE') . '<br><a href="' . $_SERVER['base_path'] . '">' . getconstStr('Home') . '</a>';
                    $title = 'Reinstall';
                    return message($html, $title, 201);
                }
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
        if (getConfig('HW_urn')==''||getConfig('HW_key')==''||getConfig('HW_secret')=='') $html .= '
        在函数代码操作页上方找到URN，鼠标放上去后显示URN，复制填入：<br>
        <label>URN:<input name="HW_urn" type="text" placeholder="" size=""></label><br>
        <a href="https://console.huaweicloud.com/iam/#/mine/accessKey" target="_blank">点击链接</a>，新增访问密钥，
        在下载的credentials.csv文件中找到对应信息，填入：<br>
        <label>Access Key Id:<input name="HW_key" type="text" placeholder="" size=""></label><br>
        <label>Secret Access Key:<input name="HW_secret" type="text" placeholder="" size=""></label><br>';
        $html .= '
        <label><input type="radio" name="ONEMANAGER_CONFIG_SAVE" value="" ' . ('file'==$contextUserData->getUserData('ONEMANAGER_CONFIG_SAVE')?'':'checked') . '>' . getconstStr('ONEMANAGER_CONFIG_SAVE_ENV') . '</label><br>
        <label><input type="radio" name="ONEMANAGER_CONFIG_SAVE" value="file" ' . ('file'==$contextUserData->getUserData('ONEMANAGER_CONFIG_SAVE')?'checked':'') . '>' . getconstStr('ONEMANAGER_CONFIG_SAVE_FILE') . '</label><br>';
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
        if (getConfig('HW_urn')==''||getConfig('HW_key')==''||getConfig('HW_secret')=='') $html .= '
            if (t.HW_urn.value==\'\') {
                alert(\'input URN\');
                return false;
            }
            if (t.HW_key.value==\'\') {
                alert(\'input name\');
                return false;
            }
            if (t.HW_secret.value==\'\') {
                alert(\'input pwd\');
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

function FGAPIV2($HW_urn, $HW_key, $HW_secret, $Method, $End, $data = '')
{
    if ($HW_urn==''||$HW_key==''||$HW_secret=='') {
        $tmp['error_code'] = 'Config Error';
        $tmp['error_msg'] = 'HW urn or access key id or secret is empty.';
        return json_encode($tmp);
    }

    $URN = explode(':', $HW_urn);
    $Region = $URN[2];
    $project_id = $URN[3];

    $host = 'functiongraph.' . $Region . '.myhuaweicloud.com';
    $path = '/v2/' . $project_id . '/fgs/functions/' . $HW_urn . '/' . $End;
    $url = 'https://' . $host . $path;
    $CanonicalURI = spurlencode($path, '/') . '/';
    $CanonicalQueryString = '';

    date_default_timezone_set('UTC'); // unset last timezone setting
    $timestamp = date('Ymd\THis\Z');
    $header['X-Sdk-Date'] = $timestamp;
    $header['Host'] = $host;
    $header['Content-Type'] = 'application/json;charset=utf8';
    ksort($header);
    $CanonicalHeaders = '';
    $SignedHeaders = '';
    foreach ($header as $key => $value) {
        $CanonicalHeaders .= strtolower($key) . ':' . $value . "\n";
        $SignedHeaders .= strtolower($key) . ';';
    }
    $SignedHeaders = substr($SignedHeaders, 0, -1);
    $Hashedbody = hash("sha256", $data);
    $CanonicalRequest = $Method . "\n" . $CanonicalURI . "\n" . $CanonicalQueryString . "\n" . $CanonicalHeaders . "\n" . $SignedHeaders . "\n" . $Hashedbody;
    $HashedCanonicalRequest = hash("sha256", $CanonicalRequest);
    $Algorithm = 'SDK-HMAC-SHA256';
    $StringToSign = $Algorithm . "\n" . $timestamp . "\n" . $HashedCanonicalRequest;
    $signature = hash_hmac("sha256", $StringToSign, $HW_secret);
    $Authorization = "$Algorithm Access=$HW_key, SignedHeaders=$SignedHeaders, Signature=$signature";
    $header['Authorization'] = $Authorization;

    //return curl($Method, $url, $data, $header)['body']; // . $CanonicalRequest;
    $p = 0;
    while ($response['stat']==0 && $p<3) {
        $response = curl($Method, $url, $data, $header);
        $p++;
    }

    if ($response['stat']==0) {
        $tmp['error_code'] = 'Network Error';
        $tmp['error_msg'] = 'Can not connect ' . $host;
        return json_encode($tmp);
    }
    if ($response['stat']!=200) {
        $tmp = json_decode($response['body'], true);
        $tmp['error_code'] .= '.';
        $tmp['error_msg'] .= '<br>' . $response['stat'] . '<br>' . $CanonicalRequest . '<br>' . json_encode($header) . PHP_EOL;
        return json_encode($tmp);
    }
    return $response['body'];
}

function getfunctioninfo($HW_urn, $HW_key, $HW_secret)
{
    return FGAPIV2($HW_urn, $HW_key, $HW_secret, 'GET', 'config');
}

function updateEnvironment($Envs, $HW_urn, $HW_key, $HW_secret)
{
    //echo json_encode($Envs,JSON_PRETTY_PRINT);
    global $contextUserData;
    $tmp_env = json_decode(json_decode(getfunctioninfo($HW_urn, $HW_key, $HW_secret),true)['user_data'],true);
    foreach ($Envs as $key1 => $value1) {
        $tmp_env[$key1] = $value1;
    }
    $tmp_env = array_filter($tmp_env, 'array_value_isnot_null'); // remove null. 清除空值
    //ksort($tmp_env);
    sortConfig($tmp_env);

    $tmpdata['handler'] = 'index.handler';
    $tmpdata['memory_size'] = $contextUserData->getMemorySize()+1-1;
    $tmpdata['runtime'] = 'PHP7.3';
    $tmpdata['timeout'] = $contextUserData->getRunningTimeInSeconds()+1-1;
    $tmpdata['user_data'] = json_encode($tmp_env);

    return FGAPIV2($HW_urn, $HW_key, $HW_secret, 'PUT', 'config', json_encode($tmpdata));
}

function SetbaseConfig($Envs, $HW_urn, $HW_key, $HW_secret)
{
    //echo json_encode($Envs,JSON_PRETTY_PRINT);
    if ($Envs['ONEMANAGER_CONFIG_SAVE'] == 'file') $Envs = Array( 'ONEMANAGER_CONFIG_SAVE' => 'file' );
    $tmp_env = json_decode(json_decode(getfunctioninfo($HW_urn, $HW_key, $HW_secret),true)['user_data'],true);
    foreach ($Envs as $key1 => $value1) {
        $tmp_env[$key1] = $value1;
    }
    $tmp_env = array_filter($tmp_env, 'array_value_isnot_null'); // remove null. 清除空值
    ksort($tmp_env);

    $tmpdata['handler'] = 'index.handler';
    $tmpdata['memory_size'] = 128;
    $tmpdata['runtime'] = 'PHP7.3';
    $tmpdata['timeout'] = 30;
    $tmpdata['description'] = 'Onedrive index and manager in Huawei FG.';
    $tmpdata['user_data'] = json_encode($tmp_env);

    return FGAPIV2($HW_urn, $HW_key, $HW_secret, 'PUT', 'config', json_encode($tmpdata));
}

function updateProgram($HW_urn, $HW_key, $HW_secret, $source)
{
    $tmpdata['code_type'] = 'zip';
    $tmpdata['func_code']['file'] = base64_encode( file_get_contents($source) );

    return FGAPIV2($HW_urn, $HW_key, $HW_secret, 'PUT', 'code', json_encode($tmpdata));
}

function api_error($response)
{
    return isset($response['error_msg']);
}

function api_error_msg($response)
{
    return $response['error_code'] . '<br>
' . $response['error_msg'] . '<br>
request_id: ' . $response['request_id'] . '<br><br>
function_name: ' . $_SERVER['function_name'] . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>';
}

function setConfigResponse($response)
{
    return json_decode( $response, true );
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
    $tmp = scandir($outPath);
    $name = $auth.'-'.$project;
    foreach ($tmp as $f) {
        if ( substr($f, 0, strlen($name)) == $name) {
            $outPath .= $f;
            break;
        }
    }

    // 将目录中文件打包成zip
    //$zip=new ZipArchive();
    $zip=new PharData($source);
    //if($zip->open($source, ZipArchive::CREATE)){
        addFileToZip($zip, $outPath); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
    //    $zip->close(); //关闭处理的zip文件
    //}

    return updateProgram(getConfig('HW_urn'), getConfig('HW_key'), getConfig('HW_secret'), $source);
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
