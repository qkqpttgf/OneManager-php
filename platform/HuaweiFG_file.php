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
    //$_SERVER['PHP_SELF'] = path_format($_SERVER['base_path'] . $path);
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
    $_SERVER['referhost'] = explode('/', $event['headers']['referer'])[2];
    $_SERVER['HTTP_TRANSLATE'] = $event['headers']['translate'];//'f'
    $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $event['headers']['if-modified-since'];
    $_SERVER['_APP_SHARE_DIR'] = '/var/share/CFF/processrouter';
    return $path;
}

function getConfig($str, $disktag = '')
{

    global $slash;
    $projectPath = splitlast(__DIR__, $slash)[0];
    $configPath = $projectPath . $slash . '.data' . $slash . 'config.php';
    $s = file_get_contents($configPath);
    $configs = '{' . splitlast(splitfirst($s, '{')[1], '}')[0] . '}';
    if ($configs!='') {
        $envs = json_decode($configs, true);
        if (isInnerEnv($str)) {
            if ($disktag=='') $disktag = $_SERVER['disktag'];
            if (isset($envs[$disktag][$str])) {
                if (isBase64Env($str)) return base64y_decode($envs[$disktag][$str]);
                else return $envs[$disktag][$str];
            }
        } else {
            if (isset($envs[$str])) {
                if (isBase64Env($str)) return base64y_decode($envs[$str]);
                else return $envs[$str];
            }
        }
    }
    return '';
}

function setConfig($arr, $disktag = '')
{

    if ($disktag=='') $disktag = $_SERVER['disktag'];
    global $slash;
    $projectPath = splitlast(__DIR__, $slash)[0];
    $configPath = $projectPath . $slash . '.data' . $slash . 'config.php';
    $s = file_get_contents($configPath);
    $configs = '{' . splitlast(splitfirst($s, '{')[1], '}')[0] . '}';
    if ($configs!='') $envs = json_decode($configs, true);
    $disktags = explode("|",getConfig('disktag'));
    $indisk = 0;
    $operatedisk = 0;
    foreach ($arr as $k => $v) {
        if (isCommonEnv($k)) {
            if (isBase64Env($k)) $envs[$k] = base64y_encode($v);
            else $envs[$k] = $v;
        } elseif (isInnerEnv($k)) {
            if (isBase64Env($k)) $envs[$disktag][$k] = base64y_encode($v);
            else $envs[$disktag][$k] = $v;
            $indisk = 1;
        } elseif ($k=='disktag_add') {
            array_push($disktags, $v);
            $operatedisk = 1;
        } elseif ($k=='disktag_del') {
            $disktags = array_diff($disktags, [ $v ]);
            $envs[$v] = '';
            $operatedisk = 1;
        } elseif ($k=='disktag_copy') {
            $newtag = $v . '_' . date("Ymd_His");
            $envs[$newtag] = $envs[$v];
            array_push($disktags, $newtag);
            $operatedisk = 1;
        } elseif ($k=='disktag_rename' || $k=='disktag_newname') {
            if ($arr['disktag_rename']!=$arr['disktag_newname']) $operatedisk = 1;
        } else {
            $envs[$k] = $v;
        }
    }
    if ($indisk) {
        $diskconfig = $envs[$disktag];
        $diskconfig = array_filter($diskconfig, 'array_value_isnot_null');
        ksort($diskconfig);
        $envs[$disktag] = $diskconfig;
    }
    if ($operatedisk) {
        if (isset($arr['disktag_newname']) && $arr['disktag_newname']!='') {
            $tags = [];
            foreach ($disktags as $tag) {
                if ($tag==$arr['disktag_rename']) array_push($tags, $arr['disktag_newname']);
                else array_push($tags, $tag);
            }
            $envs['disktag'] = implode('|', $tags);
            $envs[$arr['disktag_newname']] = $envs[$arr['disktag_rename']];
            $envs[$arr['disktag_rename']] = '';
        } else {
            $disktags = array_unique($disktags);
            foreach ($disktags as $disktag) if ($disktag!='') $disktag_s .= $disktag . '|';
            if ($disktag_s!='') $envs['disktag'] = substr($disktag_s, 0, -1);
            else $envs['disktag'] = '';
        }
    }
    $envs = array_filter($envs, 'array_value_isnot_null');
    //ksort($envs);
    $response = updateEnvironment($envs, getConfig('HW_urn'), getConfig('HW_key'), getConfig('HW_secret'));
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
            //return message($html, $title, 201);
            $response = setConfigResponse( SetbaseConfig($tmp, $tmp['HW_urn'], $tmp['HW_key'], $tmp['HW_secret']) );
            if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
                return message($html, $title, 201);
            } else {
                if ($tmp['ONEMANAGER_CONFIG_SAVE'] != 'file') {
                    $html = getconstStr('ONEMANAGER_CONFIG_SAVE_ENV') . '<br><a href="' . $_SERVER['base_path'] . '">' . getconstStr('Home') . '</a>';
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
        <label>URN:<input name="HW_urn" type="text" placeholder="urn:fss:ap-XXXXXXXX:XXXXXXXXXXXXXXXXXXXXc01a1e9caXXX:function:default:XXXXX:latest" size=""></label><br>
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

function getfunctioninfo($HW_urn, $HW_key, $HW_secret)
{
    $URN = explode(':', $HW_urn);
    $Region = $URN[2];
    $project_id = $URN[3];
    $url = 'https://functiongraph.' . $Region . '.myhuaweicloud.com/v2/' . $project_id . '/fgs/functions/' . $HW_urn . '/config';
    $signer = new Signer();
    $signer->Key = $HW_key;
    $signer->Secret = $HW_secret;
    $req = new Request('GET', $url);
    $req->headers = array(
        'content-type' => 'application/json;charset=utf8',
    );
    $req->body = '';
    $curl = $signer->Sign($req);
    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return $response;
}

function getfunctioncode($HW_urn, $HW_key, $HW_secret)
{
    $URN = explode(':', $HW_urn);
    $Region = $URN[2];
    $project_id = $URN[3];
    $url = 'https://functiongraph.' . $Region . '.myhuaweicloud.com/v2/' . $project_id . '/fgs/functions/' . $HW_urn . '/code';
    $signer = new Signer();
    $signer->Key = $HW_key;
    $signer->Secret = $HW_secret;
    $req = new Request('GET', $url);
    $req->headers = array(
        'content-type' => 'application/json;charset=utf8',
    );
    $req->body = '';
    $curl = $signer->Sign($req);
    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    //return $response;
    $url = json_decode($response, true)['func_code']['link'];
    // return $url;


    $bucket = splitfirst( splitfirst($url, '//')[1], '.')[0];
    $path = splitfirst( splitfirst($url, '//')[1], '/')[1];
    $date = gmdate('D, d M Y H:i:s') . ' GMT';
    //$date = 'Wed, 05 Aug 2020 06:34:50 GMT';
    $StringToSign = 'GET
' . '
' . '
' . '
' . 'x-obs-date:' . $date . '
' . '/' . $bucket . '/' . $path;

    $signature = base64_encode(hash_hmac('sha1', $StringToSign, $HW_secret, true));
    $response = curl('GET', $url, false, [ 'Authorization' => 'OBS ' . $HW_key . ':' . $signature, 'x-obs-date' => $date, 'Content-Type' => '' ]);
    //if ($response['stat']==200) return $response['body'];
    if ($response['stat']==0) return json_encode( [ 'error_code' => 'Network', 'error_msg' => 'Network error in getting code.' ] );
    else return $response['body'];
}

function copyFolder($from, $to)
{
    if (substr($from, -1)=='/') $from = substr($from, 0, -1);
    if (substr($to, -1)=='/') $to = substr($to, 0, -1);
    if (!file_exists($to)) mkdir($to, 0777);
    $handler=opendir($from);
    while($filename=readdir($handler)) {
        if($filename != '.' && $filename != '..'){
            $fromfile = $from.'/'.$filename;
            $tofile = $to.'/'.$filename;
            if(is_dir($fromfile)){// 如果读取的某个对象是文件夹，则递归
                copyFolder($fromfile, $tofile);
            }else{
                copy($fromfile, $tofile);
            }
        }
    }
    closedir($handler);
    return 1;
}

function updateEnvironment($Envs, $HW_urn, $HW_key, $HW_secret)
{
    sortConfig($Envs);
    //echo json_encode($Envs,JSON_PRETTY_PRINT);
    $source = '/tmp/code.zip';
    $outPath = '/tmp/code/';
    $oldcode = '/tmp/oldcode.zip';

    // 获取当前代码，并解压
    $coderoot = __DIR__ . '/../';

    copyFolder($coderoot, $outPath);

    // 将配置写入
    $prestr = '<?php $configs = \'' . PHP_EOL;
    $aftstr = PHP_EOL . '\';';
    file_put_contents($outPath . '.data/config.php', $prestr . json_encode($Envs, JSON_PRETTY_PRINT) . $aftstr);

    // 将目录中文件打包成zip
    //$zip=new ZipArchive();
    $zip=new PharData($source);
    //if($zip->open($source, ZipArchive::CREATE)){
        addFileToZip($zip, $outPath); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
    //    $zip->close(); //关闭处理的zip文件
    //}

    return updateProgram($HW_urn, $HW_key, $HW_secret, $source);
}

function SetbaseConfig($Envs, $HW_urn, $HW_key, $HW_secret)
{
    global $slash;
    //echo json_encode($Envs,JSON_PRETTY_PRINT);
    if ($Envs['ONEMANAGER_CONFIG_SAVE'] == 'file') $envs = Array( 'ONEMANAGER_CONFIG_SAVE' => 'file' );
    else {
        $Envs['ONEMANAGER_CONFIG_SAVE'] == '';
        $envs = $Envs;
        $tmp_env = json_decode(json_decode(getfunctioninfo($HW_urn, $HW_key, $HW_secret),true)['user_data'],true);
        foreach ($envs as $key1 => $value1) {
            $tmp_env[$key1] = $value1;
        }
        $tmp_env = array_filter($tmp_env, 'array_value_isnot_null'); // remove null. 清除空值
        ksort($tmp_env);
        $envs = $tmp_env;
    }

    // https://functiongraph.cn-north-4.myhuaweicloud.com/v2/{project_id}/fgs/functions/{function_urn}/config
    $URN = explode(':', $HW_urn);
    $Region = $URN[2];
    $project_id = $URN[3];
    $url = 'https://functiongraph.' . $Region . '.myhuaweicloud.com/v2/' . $project_id . '/fgs/functions/' . $HW_urn . '/config';
    $signer = new Signer();
    $signer->Key = $HW_key;
    $signer->Secret = $HW_secret;
    $req = new Request('PUT', $url);
    $req->headers = array(
        'content-type' => 'application/json;charset=utf8',
    );
    $tmpdata['handler'] = 'index.handler';
    $tmpdata['memory_size'] = 128;
    $tmpdata['runtime'] = 'PHP7.3';
    $tmpdata['timeout'] = 30;
    $tmpdata['description'] = 'Onedrive index and manager in Huawei FG.';
    $tmpdata['user_data'] = json_encode($envs);
    $req->body = json_encode($tmpdata);
    $curl = $signer->Sign($req);
    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    //return $response;
    if (api_error(setConfigResponse($response))) {
        return $response;
    }

    $projectPath = splitlast(__DIR__, $slash)[0];
    $configPath = $projectPath . $slash . '.data' . $slash . 'config.php';
    $s = file_get_contents($configPath);
    $configs = '{' . splitlast(splitfirst($s, '{')[1], '}')[0] . '}';
    if ($configs!='') $tmp_env = json_decode($configs, true);
    foreach ($Envs as $k => $v) {
        $tmp_env[$k] = $v;
    }
    $tmp_env = array_filter($tmp_env, 'array_value_isnot_null');
    //ksort($tmp_env);
    $response = updateEnvironment($tmp_env, $HW_urn, $HW_key, $HW_secret);
    return $response;
}

function updateProgram($HW_urn, $HW_key, $HW_secret, $source)
{
    $URN = explode(':', $HW_urn);
    $Region = $URN[2];
    $project_id = $URN[3];
    $url = 'https://functiongraph.' . $Region . '.myhuaweicloud.com/v2/' . $project_id . '/fgs/functions/' . $HW_urn . '/code';
    $signer = new Signer();
    $signer->Key = $HW_key;
    $signer->Secret = $HW_secret;
    $req = new Request('PUT', $url);
    $req->headers = array(
        'content-type' => 'application/json;charset=utf8',
    );
    $tmpdata['code_type'] = 'zip';
    $tmpdata['func_code']['file'] = base64_encode( file_get_contents($source) );
    $req->body = json_encode($tmpdata);
    $curl = $signer->Sign($req);
    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return $response;
}

function api_error($response)
{
    return isset($response['error_code']);
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

    // 放入配置文件
    file_put_contents($outPath . '/.data/config.php', file_get_contents(__DIR__ . '/../.data/config.php'));

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








define("BasicDateFormat", "Ymd\THis\Z");
define("Algorithm", "SDK-HMAC-SHA256");
define("HeaderXDate", "X-Sdk-Date");
define("HeaderHost", "host");
define("HeaderAuthorization", "Authorization");
define("HeaderContentSha256", "X-Sdk-Content-Sha256");

class Request
{
    public $method = '';
    public $scheme = '';
    public $host = '';
    public $uri = '';
    public $query = array();
    public $headers = array();
    public $body = '';

    function __construct()
    {
        $args = func_get_args();
        $i = count($args);
        if ($i == 0) {
            $this->construct(NULL, NULL, NULL, NULL);
        } elseif ($i == 1) {
            $this->construct($args[0], NULL, NULL, NULL);
        } elseif ($i == 2) {
            $this->construct($args[0], $args[1], NULL, NULL);
        } elseif ($i == 3) {
            $this->construct($args[0], $args[1], $args[2], NULL);
        } else {
            $this->construct($args[0], $args[1], $args[2], $args[3]);
        }
    }

    function construct($method, $url, $headers, $body)
    {
        if ($method != NULL) {
            $this->method = $method;
        }
        if ($url != NULL) {
            $spl = explode("://", $url, 2);
            $scheme = 'http';
            if (count($spl) > 1) {
                $scheme = $spl[0];
                $url = $spl[1];
            }
            $spl = explode("?", $url, 2);
            $url = $spl[0];
            $query = array();
            if (count($spl) > 1) {
                foreach (explode("&", $spl[1]) as $kv) {
                    $spl = explode("=", $kv, 2);
                    $key = $spl[0];
                    if (count($spl) == 1) {
                        $value = "";
                    } else {
                        $value = $spl[1];
                    }
                    if ($key != "") {
                        $key = urldecode($key);
                        $value = urldecode($value);
                        if (array_key_exists($key, $query)) {
                            array_push($query[$key], $value);
                        } else {
                            $query[$key] = array($value);
                        }
                    }
                }
            }
            $spl = explode("/", $url, 2);
            $host = $spl[0];
            if (count($spl) == 1) {
                $url = "/";
            } else {
                $url = "/" . $spl[1];
            }
            $this->scheme = $scheme;
            $this->host = $host;
            $this->uri = urldecode($url);
            $this->query = $query;
        }
        if ($headers != NULL) {
            $this->headers = $headers;
        }
        if ($body != NULL) {
            $this->body = $body;
        }
    }
}

class Signer
{
    public $Key = '';
    public $Secret = '';

    function escape($string)
    {
        $entities = array('+', "%7E");
        $replacements = array('%20', "~");
        return str_replace($entities, $replacements, urlencode($string));
    }

    function findHeader($r, $header)
    {
        foreach ($r->headers as $key => $value) {
            if (!strcasecmp($key, $header)) {
                return $value;
            }
        }
        return NULL;
    }

// Build a CanonicalRequest from a regular request string
//
// CanonicalRequest =
//  HTTPRequestMethod + '\n' +
//  CanonicalURI + '\n' +
//  CanonicalQueryString + '\n' +
//  CanonicalHeaders + '\n' +
//  SignedHeaders + '\n' +
//  HexEncode(Hash(RequestPayload))
    function CanonicalRequest($r, $signedHeaders)
    {
        $CanonicalURI = $this->CanonicalURI($r);
        $CanonicalQueryString = $this->CanonicalQueryString($r);
        $canonicalHeaders = $this->CanonicalHeaders($r, $signedHeaders);
        $signedHeadersString = join(";", $signedHeaders);
        $hash = $this->findHeader($r, HeaderContentSha256);
        if (!$hash) {
            $hash = hash("sha256", $r->body);
        }
        return "$r->method\n$CanonicalURI\n$CanonicalQueryString\n$canonicalHeaders\n$signedHeadersString\n$hash";
    }

// CanonicalURI returns request uri
    function CanonicalURI($r)
    {
        $pattens = explode("/", $r->uri);
        $uri = array();
        foreach ($pattens as $v) {
            array_push($uri, $this->escape($v));
        }
        $urlpath = join("/", $uri);
        if (substr($urlpath, -1) != "/") {
            $urlpath = $urlpath . "/";
        }
        return $urlpath;
    }

// CanonicalQueryString
    function CanonicalQueryString($r)
    {
        $keys = array();
        foreach ($r->query as $key => $value) {
            array_push($keys, $key);
        }
        sort($keys);
        $a = array();
        foreach ($keys as $key) {
            $k = $this->escape($key);
            $value = $r->query[$key];
            if (is_array($value)) {
                sort($value);
                foreach ($value as $v) {
                    $kv = "$k=" . $this->escape($v);
                    array_push($a, $kv);
                }
            } else {
                $kv = "$k=" . $this->escape($value);
                array_push($a, $kv);
            }
        }
        return join("&", $a);
    }

// CanonicalHeaders
    function CanonicalHeaders($r, $signedHeaders)
    {
        $headers = array();
        foreach ($r->headers as $key => $value) {
            $headers[strtolower($key)] = trim($value);
        }
        $a = array();
        foreach ($signedHeaders as $key) {
            array_push($a, $key . ':' . $headers[$key]);
        }
        return join("\n", $a) . "\n";
    }

    function curlHeaders($r)
    {
        $header = array();
        foreach ($r->headers as $key => $value) {
            array_push($header, strtolower($key) . ':' . trim($value));
        }
        return $header;
    }

// SignedHeaders
    function SignedHeaders($r)
    {
        $a = array();
        foreach ($r->headers as $key => $value) {
            array_push($a, strtolower($key));
        }
        sort($a);
        return $a;
    }

// Create a "String to Sign".
    function StringToSign($canonicalRequest, $t)
    {
        date_default_timezone_set('UTC');
        $date = date(BasicDateFormat, $t);
        $hash = hash("sha256", $canonicalRequest);
        return "SDK-HMAC-SHA256\n$date\n$hash";
    }

// Create the HWS Signature.
    function SignStringToSign($stringToSign, $signingKey)
    {
        return hash_hmac("sha256", $stringToSign, $signingKey);
    }

// Get the finalized value for the "Authorization" header. The signature parameter is the output from SignStringToSign
    function AuthHeaderValue($signature, $accessKey, $signedHeaders)
    {
        $signedHeadersString = join(";", $signedHeaders);
        return "SDK-HMAC-SHA256 Access=$accessKey, SignedHeaders=$signedHeadersString, Signature=$signature";
    }

    public function Sign($r)
    {
        date_default_timezone_set('UTC');
        $date = $this->findHeader($r, HeaderXDate);
        if ($date) {
            $t = date_timestamp_get(date_create_from_format(BasicDateFormat, $date));
        }
        if (!@$t) {
            $t = time();
            $r->headers[HeaderXDate] = date(BasicDateFormat, $t);
        }
        $queryString = $this->CanonicalQueryString($r);
        if ($queryString != "") {
            $queryString = "?" . $queryString;
        }
        $signedHeaders = $this->SignedHeaders($r);
        $canonicalRequest = $this->CanonicalRequest($r, $signedHeaders);
        $stringToSign = $this->StringToSign($canonicalRequest, $t);
        $signature = $this->SignStringToSign($stringToSign, $this->Secret);
        $authValue = $this->AuthHeaderValue($signature, $this->Key, $signedHeaders);
        $r->headers[HeaderAuthorization] = $authValue;

        $curl = curl_init();
        $uri = str_replace(array("%2F"), array("/"), rawurlencode($r->uri));
        $url = $r->scheme . '://' . $r->host . $uri . $queryString;
        $headers = $this->curlHeaders($r);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $r->method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $r->body);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_NOBODY, FALSE);
        return $curl;
    }
}

function WaitFunction() {
    return true;
}
