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
    $_SERVER['HTTP_USER_AGENT'] = $event['headers']['user-agent'];
    $_SERVER['HTTP_TRANSLATE'] = $event['headers']['translate'];//'f'
    $_SERVER['_APP_SHARE_DIR'] = '/var/share/CFF/processrouter';
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
    return $path;
}

function getConfig($str, $disktag = '')
{
    global $InnerEnv;
    global $Base64Env;
    global $contextUserData;
    if (in_array($str, $InnerEnv)) {
        if ($disktag=='') $disktag = $_SERVER['disktag'];
        $env = json_decode($contextUserData->getUserData($disktag), true);
        if (isset($env[$str])) {
            if (in_array($str, $Base64Env)) return equal_replace($env[$str],1);
            else return $env[$str];
        }
    } else {
        if (in_array($str, $Base64Env)) return equal_replace($contextUserData->getUserData($str),1);
        else return $contextUserData->getUserData($str);
    }
    return '';
}

function setConfig($arr, $disktag = '')
{
    global $InnerEnv;
    global $Base64Env;
    global $contextUserData;
    if ($disktag=='') $disktag = $_SERVER['disktag'];
    $disktags = explode("|",getConfig('disktag'));
    $diskconfig = json_decode($contextUserData->getUserData($disktag), true);
    $tmp = [];
    $indisk = 0;
    $oparetdisk = 0;
    foreach ($arr as $k => $v) {
        if (in_array($k, $InnerEnv)) {
            if (in_array($k, $Base64Env)) $diskconfig[$k] = equal_replace($v);
            else $diskconfig[$k] = $v;
            $indisk = 1;
        } elseif ($k=='disktag_add') {
            array_push($disktags, $v);
            $oparetdisk = 1;
        } elseif ($k=='disktag_del') {
            $disktags = array_diff($disktags, [ $v ]);
            $tmp[$v] = '';
            $oparetdisk = 1;
        } else {
            if (in_array($k, $Base64Env)) $tmp[$k] = equal_replace($v);
            else $tmp[$k] = $v;
        }
    }
    if ($indisk) {
        $diskconfig = array_filter($diskconfig, 'array_value_isnot_null');
        ksort($diskconfig);
        $tmp[$disktag] = json_encode($diskconfig);
    }
    if ($oparetdisk) {
        $disktags = array_unique($disktags);
        foreach ($disktags as $disktag) if ($disktag!='') $disktag_s .= $disktag . '|';
        if ($disktag_s!='') $tmp['disktag'] = substr($disktag_s, 0, -1);
        else $tmp['disktag'] = '';
    }
//    echo '正式设置：'.json_encode($tmp,JSON_PRETTY_PRINT).'
//';
    $response = updateEnvironment($tmp, getConfig('HW_urn'), getConfig('HW_key'), getConfig('HW_secret'));
    // WaitSCFStat();
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
        setConfig($tmp);
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
            //$response = json_decode(SetbaseConfig($tmp, $HW_urn, $HW_name, $HW_pwd), true)['Response'];
            $response = setConfigResponse( SetbaseConfig($tmp, $tmp['HW_urn'], $tmp['HW_key'], $tmp['HW_secret']) );
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
        if (getConfig('HW_urn')==''||getConfig('HW_key')==''||getConfig('HW_secret')=='') $html .= '
        在函数代码操作页上方找到URN，鼠标放上去后显示URN，复制填入：<br>
        <label>URN:<input name="HW_urn" type="text" placeholder="" size=""></label><br>
        <a href="https://console.huaweicloud.com/iam/#/mine/accessKey" target="_blank">点击链接</a>，新增访问密钥，
        在下载的credentials.csv文件中找到对应信息，填入：<br>
        <label>Access Key Id:<input name="HW_key" type="text" placeholder="" size=""></label><br>
        <label>Secret Access Key:<input name="HW_secret" type="password" placeholder="" size=""></label><br>';
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
    $title = 'Error';
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


function updateEnvironment($Envs, $HW_urn, $HW_key, $HW_secret)
{
    //echo json_encode($Envs,JSON_PRETTY_PRINT);
    global $contextUserData;
    $tmp_env = json_decode(json_decode(getfunctioninfo($HW_urn, $HW_key, $HW_secret),true)['user_data'],true);
    foreach ($Envs as $key1 => $value1) {
        $tmp_env[$key1] = $value1;
    }
    $tmp_env = array_filter($tmp_env, 'array_value_isnot_null'); // remove null. 清除空值
    ksort($tmp_env);

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
    $tmpdata['memory_size'] = $contextUserData->getMemorySize()+1-1;
    $tmpdata['runtime'] = 'PHP7.3';
    $tmpdata['timeout'] = $contextUserData->getRunningTimeInSeconds()+1-1;
    $tmpdata['user_data'] = json_encode($tmp_env);
    $req->body = json_encode($tmpdata);
    $curl = $signer->Sign($req);
    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return $response;
}

function SetbaseConfig($Envs, $HW_urn, $HW_key, $HW_secret)
{
    //echo json_encode($Envs,JSON_PRETTY_PRINT);
    $tmp_env = json_decode(json_decode(getfunctioninfo($HW_urn, $HW_key, $HW_secret),true)['user_data'],true);
    foreach ($Envs as $key1 => $value1) {
        $tmp_env[$key1] = $value1;
    }
    $tmp_env = array_filter($tmp_env, 'array_value_isnot_null'); // remove null. 清除空值
    ksort($tmp_env);

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
    $tmpdata['user_data'] = json_encode($tmp_env);
    $req->body = json_encode($tmpdata);
    $curl = $signer->Sign($req);
    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
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
