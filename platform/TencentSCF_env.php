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
    $_GET = $event['queryString'];
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
    $_SERVER['function_name'] = $context['function_name'];
    $_SERVER['namespace'] = $context['namespace'];
    $_SERVER['Region'] = $context['tencentcloud_region'];
    $host_name = $event['headers']['host'];
    $_SERVER['HTTP_HOST'] = $host_name;
    $serviceId = $event['requestContext']['serviceId'];
    if ( $serviceId === substr($host_name,0,strlen($serviceId)) ) {
        $_SERVER['base_path'] = '/'.$event['requestContext']['stage'].'/'.$_SERVER['function_name'].'/';
        $path = substr($event['path'], strlen('/'.$_SERVER['function_name'].'/'));
    } else {
        $_SERVER['base_path'] = $event['requestContext']['path'];
        $path = substr($event['path'], strlen($event['requestContext']['path']));
    }
    if (substr($path,-1)=='/') $path=substr($path,0,-1);
    $_SERVER['is_guestup_path'] = is_guestup_path($path);
    //$_SERVER['PHP_SELF'] = path_format($_SERVER['base_path'] . $path);
    $_SERVER['REMOTE_ADDR'] = $event['requestContext']['sourceIp'];
    $_SERVER['HTTP_X_REQUESTED_WITH'] = $event['headers']['x-requested-with'];
    $_SERVER['HTTP_USER_AGENT'] = $event['headers']['user-agent'];
    if (isset($event['headers']['authorization'])) {
        $basicAuth = splitfirst(base64_decode(splitfirst($event['headers']['authorization'], 'Basic ')[1]), ':');
        $_SERVER['PHP_AUTH_USER'] = $basicAuth[0];
        $_SERVER['PHP_AUTH_PW'] = $basicAuth[1];
    }
    $_SERVER['REQUEST_SCHEME'] = $event['headers']['x-api-scheme'];
    //$_SERVER['REQUEST_SCHEME'] = $event['headers']['x-forwarded-proto'];
    $_SERVER['host'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    $_SERVER['referhost'] = explode('/', $event['headers']['referer'])[2];
    $_SERVER['HTTP_TRANSLATE'] = $event['headers']['translate'];//'f'
    $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $event['headers']['if-modified-since'];
    $_SERVER['USER'] = 'qcloud';
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
    $response = updateEnvironment($tmp, $_SERVER['function_name'], $_SERVER['Region'], $_SERVER['namespace'], getConfig('SecretId'), getConfig('SecretKey'));
    WaitSCFStat();
    return $response;
}

function WaitSCFStat()
{
    $trynum = 0;
    while( json_decode(getfunctioninfo($_SERVER['function_name'], $_SERVER['Region'], $_SERVER['namespace'], getConfig('SecretId'), getConfig('SecretKey')),true)['Response']['Status']!='Active' ) echo '
'.++$trynum;
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
        $tmp['ONEMANAGER_CONFIG_SAVE'] = $_POST['ONEMANAGER_CONFIG_SAVE'];
        $response = json_decode(SetbaseConfig($tmp, $_SERVER['function_name'], $_SERVER['Region'], $_SERVER['namespace'], $SecretId, $SecretKey), true)['Response'];
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
        <a href="https://console.cloud.tencent.com/cam/capi" target="_blank">' . getconstStr('Create') . ' SecretId & SecretKey</a><br>
        <label>SecretId:<input name="SecretId" type="text" placeholder="" size=""></label><br>
        <label>SecretKey:<input name="SecretKey" type="text" placeholder="" size=""></label><br>';
        $html .= '
        <label><input type="radio" name="ONEMANAGER_CONFIG_SAVE" value="" ' . ('file'==getenv('ONEMANAGER_CONFIG_SAVE')?'':'checked') . '>' . getconstStr('ONEMANAGER_CONFIG_SAVE_ENV') . '</label><br>
        <label><input type="radio" name="ONEMANAGER_CONFIG_SAVE" value="file" ' . ('file'==getenv('ONEMANAGER_CONFIG_SAVE')?'checked':'') . '>' . getconstStr('ONEMANAGER_CONFIG_SAVE_FILE') . '</label><br>';
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
                alert(\'input SecretId\');
                return false;
            }
            if (t.SecretKey.value==\'\') {
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

function post2url($url, $data)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    //echo $response;
    return $response;
}

function ReorganizeDate($arr)
{
    $str = '';
    ksort($arr);
    foreach ($arr as $k1 => $v1) {
        $str .= '&' . $k1 . '=' . $v1;
    }
    $str = substr($str, 1); // remove first '&'. 去掉第一个&
    return $str;
}

function getfunctioninfo($function_name, $Region, $Namespace, $SecretId, $SecretKey)
{
    //$meth = 'GET';
    $meth = 'POST';
    $host = 'scf.tencentcloudapi.com';
    $tmpdata['Action'] = 'GetFunction';
    $tmpdata['FunctionName'] = $function_name;
    $tmpdata['Namespace'] = $Namespace;
    $tmpdata['Nonce'] = time();
    $tmpdata['Region'] = $Region;
    $tmpdata['SecretId'] = $SecretId;
    $tmpdata['Timestamp'] = time();
    $tmpdata['Token'] = '';
    $tmpdata['Version'] = '2018-04-16';
    $data = ReorganizeDate($tmpdata);
    $signStr = base64_encode(hash_hmac('sha1', $meth.$host.'/?'.$data, $SecretKey, true));
    //echo urlencode($signStr);
    //return file_get_contents('https://'.$url.'&Signature='.urlencode($signStr));
    return post2url('https://'.$host, $data.'&Signature='.urlencode($signStr));
}

function updateEnvironment($Envs, $function_name, $Region, $Namespace, $SecretId, $SecretKey)
{
    //print_r($Envs);
    WaitSCFStat();
    //json_decode($a,true)['Response']['Environment']['Variables'][0]['Key']
    $tmp = json_decode(getfunctioninfo($function_name, $Region, $Namespace, $SecretId, $SecretKey),true)['Response']['Environment']['Variables'];
    foreach ($tmp as $tmp1) {
        $tmp_env[$tmp1['Key']] = $tmp1['Value'];
    }
    foreach ($Envs as $key1 => $value1) {
        $tmp_env[$key1] = $value1;
    }
    $tmp_env = array_filter($tmp_env, 'array_value_isnot_null'); // remove null. 清除空值
    //$tmp_env['Region'] = $Region;
    //ksort($tmp_env);
    sortConfig($tmp_env);

    $i = 0;
    foreach ($tmp_env as $key1 => $value1) {
        $tmpdata['Environment.Variables.'.$i.'.Key'] = $key1;
        $tmpdata['Environment.Variables.'.$i.'.Value'] = $value1;
        $i++;
    }
    $meth = 'POST';
    $host = 'scf.tencentcloudapi.com';
    $tmpdata['Action'] = 'UpdateFunctionConfiguration';
    $tmpdata['FunctionName'] = $function_name;
    $tmpdata['Namespace'] = $Namespace;
    $tmpdata['Nonce'] = time();
    $tmpdata['Region'] = $Region;
    $tmpdata['SecretId'] = $SecretId;
    $tmpdata['Timestamp'] = time();
    $tmpdata['Token'] = '';
    $tmpdata['Version'] = '2018-04-16';
    $data = ReorganizeDate($tmpdata);
    $signStr = base64_encode(hash_hmac('sha1', $meth.$host.'/?'.$data, $SecretKey, true));
    //echo urlencode($signStr);
    return post2url('https://'.$host, $data.'&Signature='.urlencode($signStr));
}

function SetbaseConfig($Envs, $function_name, $Region, $Namespace, $SecretId, $SecretKey)
{
    //echo json_encode($Envs,JSON_PRETTY_PRINT);
    if ($Envs['ONEMANAGER_CONFIG_SAVE'] == 'file') $Envs = Array( 'ONEMANAGER_CONFIG_SAVE' => 'file' );
    //json_decode($a,true)['Response']['Environment']['Variables'][0]['Key']
    $tmp = json_decode(getfunctioninfo($function_name, $Region, $Namespace, $SecretId, $SecretKey),true)['Response']['Environment']['Variables'];
    foreach ($tmp as $tmp1) {
        $tmp_env[$tmp1['Key']] = $tmp1['Value'];
    }
    foreach ($Envs as $key1 => $value1) {
        $tmp_env[$key1] = $value1;
    }
    $tmp_env = array_filter($tmp_env, 'array_value_isnot_null'); // remove null. 清除空值
    //$tmp_env['Region'] = $Region;
    ksort($tmp_env);

    $i = 0;
    foreach ($tmp_env as $key1 => $value1) {
        $tmpdata['Environment.Variables.'.$i.'.Key'] = $key1;
        $tmpdata['Environment.Variables.'.$i.'.Value'] = $value1;
        $i++;
    }
    $meth = 'POST';
    $host = 'scf.tencentcloudapi.com';
    $tmpdata['Action'] = 'UpdateFunctionConfiguration';
    $tmpdata['FunctionName'] = $function_name;
    $tmpdata['Namespace'] = $Namespace;
    $tmpdata['Nonce'] = time();
    $tmpdata['Region'] = $Region;
    $tmpdata['SecretId'] = $SecretId;
    $tmpdata['Timestamp'] = time();
    $tmpdata['Token'] = '';
    $tmpdata['Version'] = '2018-04-16';
    $tmpdata['Description'] = 'Onedrive index and manager in Tencent SCF.';
    $tmpdata['MemorySize'] = 64;
    $tmpdata['Timeout'] = 30;
    $data = ReorganizeDate($tmpdata);
    echo $data;
    $signStr = base64_encode(hash_hmac('sha1', $meth.$host.'/?'.$data, $SecretKey, true));
    //echo urlencode($signStr);
    return post2url('https://'.$host, $data.'&Signature='.urlencode($signStr));
}

function updateProgram_OLD($function_name, $Region, $Namespace, $SecretId, $SecretKey, $source)
{
    WaitSCFStat();
    $meth = 'POST';
    $host = 'scf.tencentcloudapi.com';
    $tmpdata['Action'] = 'UpdateFunctionCode';
    $tmpdata['Code.GitUrl'] = $source['url'];
    $tmpdata['Code.GitBranch'] = $source['branch'];
    $tmpdata['CodeSource'] = 'Git';
    $tmpdata['FunctionName'] = $function_name;
    $tmpdata['Handler'] = 'index.main_handler';
    $tmpdata['Namespace'] = $Namespace;
    $tmpdata['Nonce'] = time();
    $tmpdata['Region'] = $Region;
    $tmpdata['SecretId'] = $SecretId;
    $tmpdata['Timestamp'] = time();
    $tmpdata['Token'] = '';
    $tmpdata['Version'] = '2018-04-16';
    $data = ReorganizeDate($tmpdata);
    $signStr = base64_encode(hash_hmac('sha1', $meth.$host.'/?'.$data, $SecretKey, true));
    //echo urlencode($signStr);
    return post2url('https://'.$host, $data.'&Signature='.urlencode($signStr));
}

function api_error($response)
{
    return isset($response['Error']);
}

function api_error_msg($response)
{
    return $response['Error']['Code'] . '<br>
' . $response['Error']['Message'] . '<br><br>
function_name:' . $_SERVER['function_name'] . '<br>
Region:' . $_SERVER['Region'] . '<br>
namespace:' . $_SERVER['namespace'] . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>';
}

function setConfigResponse($response)
{
    return json_decode( $response, true )['Response'];
}

function WaitFunction() {
    //$trynum = 0;
    //while( json_decode(getfunctioninfo($_SERVER['function_name'], $_SERVER['Region'], $_SERVER['namespace'], getConfig('SecretId'), getConfig('SecretKey')),true)['Response']['Status']!='Active' ) echo '
//'.++$trynum;
    if ( json_decode(getfunctioninfo($_SERVER['function_name'], $_SERVER['Region'], $_SERVER['namespace'], getConfig('SecretId'), getConfig('SecretKey')),true)['Response']['Status']=='Active' ) return true;
    else return false;
}

function updateProgram($function_name, $Region, $Namespace, $SecretId, $SecretKey, $source)
{
    $secretId = $SecretId;
    $secretKey = $SecretKey;
    $host = 'scf.tencentcloudapi.com';
    $service = "scf";
    $version = "2018-04-16";
    $action = "UpdateFunctionCode";
    $region = $Region;
    $timestamp = time();
    $algorithm = "TC3-HMAC-SHA256";

    // step 1: build canonical request string
    $httpRequestMethod = "POST";
    $canonicalUri = "/";
    $canonicalQueryString = "";
    $canonicalHeaders = "content-type:application/json; charset=utf-8\n"."host:".$host."\n";
    $signedHeaders = "content-type;host";

    //$tmpdata['Action'] = 'UpdateFunctionCode';
    $tmpdata['Code']['ZipFile'] = base64_encode( file_get_contents($source) );
    $tmpdata['CodeSource'] = 'ZipFile';
    $tmpdata['FunctionName'] = $function_name;
    $tmpdata['Handler'] = 'index.main_handler';
    //$tmpdata['Namespace'] = $Namespace;
    //$tmpdata['Nonce'] = time();
    //$tmpdata['Region'] = $Region;
    //$tmpdata['SecretId'] = $SecretId;
    //$tmpdata['Timestamp'] = time();
    //$tmpdata['Token'] = '';
    //$tmpdata['Version'] = '2018-04-16';
    $payload = json_encode($tmpdata);
    //$payload = '{"Limit": 1, "Filters": [{"Values": ["\u672a\u547d\u540d"], "Name": "instance-name"}]}';
    $hashedRequestPayload = hash("SHA256", $payload);
    $canonicalRequest = $httpRequestMethod."\n"
        .$canonicalUri."\n"
        .$canonicalQueryString."\n"
        .$canonicalHeaders."\n"
        .$signedHeaders."\n"
        .$hashedRequestPayload;
    //echo $canonicalRequest.PHP_EOL;

    // step 2: build string to sign
    $date = gmdate("Y-m-d", $timestamp);
    $credentialScope = $date."/".$service."/tc3_request";
    $hashedCanonicalRequest = hash("SHA256", $canonicalRequest);
    $stringToSign = $algorithm."\n"
        .$timestamp."\n"
        .$credentialScope."\n"
        .$hashedCanonicalRequest;
    //echo $stringToSign.PHP_EOL;

    // step 3: sign string
    $secretDate = hash_hmac("SHA256", $date, "TC3".$secretKey, true);
    $secretService = hash_hmac("SHA256", $service, $secretDate, true);
    $secretSigning = hash_hmac("SHA256", "tc3_request", $secretService, true);
    $signature = hash_hmac("SHA256", $stringToSign, $secretSigning);
    //echo $signature.PHP_EOL;

    // step 4: build authorization
    $authorization = $algorithm
        ." Credential=".$secretId."/".$credentialScope
        .", SignedHeaders=content-type;host, Signature=".$signature;
    //echo $authorization.PHP_EOL;

    //$curl = "curl -X POST https://".$host
    //    .' -H "Authorization: '.$authorization.'"'
    //    .' -H "Content-Type: application/json; charset=utf-8"'
    //    .' -H "Host: '.$host.'"'
    //    .' -H "X-TC-Action: '.$action.'"'
    //    .' -H "X-TC-Timestamp: '.$timestamp.'"'
    //    .' -H "X-TC-Version: '.$version.'"'
    //    .' -H "X-TC-Region: '.$region.'"'
    //    ." -d '".$payload."'";
    //error_log1( $curl.PHP_EOL );
    //return '{"response": {"Error": {"Message":"' . $curl . '"}}}';
    $headers['Authorization'] = $authorization;
    $headers['Content-Type'] = 'application/json; charset=utf-8';
    $headers['Host'] = $host;
    $headers['X-TC-Action'] = $action;
    $headers['X-TC-Timestamp'] = $timestamp;
    $headers['X-TC-Version'] = $version;
    $headers['X-TC-Region'] = $region;
    return curl('POST', 'https://'.$host, $payload, $headers)['body'];
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
    file_put_contents($outPath . '/.data/config.php', file_get_contents(__DIR__ . '/../.data/config.php'));

    // 将目录中文件打包成zip
    //$zip=new ZipArchive();
    $zip=new PharData($source);
    //if($zip->open($source, ZipArchive::CREATE)){
        addFileToZip($zip, $outPath); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
    //    $zip->close(); //关闭处理的zip文件
    //}

    return updateProgram($_SERVER['function_name'], $_SERVER['Region'], $_SERVER['namespace'], getConfig('SecretId'), getConfig('SecretKey'), $source);
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
