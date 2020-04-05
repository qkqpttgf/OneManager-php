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
    $_SERVER['USER'] = 'qcloud';
}

function GetPathSetting($event, $context)
{
    $_SERVER['firstacceptlanguage'] = strtolower(splitfirst(splitfirst($event['headers']['accept-language'],';')[0],',')[0]);
    $_SERVER['function_name'] = $context['function_name'];
    $_SERVER['namespace'] = $context['namespace'];
    $host_name = $event['headers']['host'];
    $_SERVER['HTTP_HOST'] = $host_name;
    $serviceId = $event['requestContext']['serviceId'];
    if ( $serviceId === substr($host_name,0,strlen($serviceId)) ) {
        $_SERVER['base_path'] = '/'.$event['requestContext']['stage'].'/'.$_SERVER['function_name'].'/';
        $_SERVER['Region'] = getenv('Region');
        if ($_SERVER['Region'] == '') {
            $_SERVER['Region'] = substr($host_name, strpos($host_name, '.')+1);
            $_SERVER['Region'] = substr($_SERVER['Region'], 0, strpos($_SERVER['Region'], '.'));
        }
        $path = substr($event['path'], strlen('/'.$_SERVER['function_name'].'/'));
    } else {
        $_SERVER['base_path'] = $event['requestContext']['path'];
        $_SERVER['Region'] = getenv('Region');
        $path = substr($event['path'], strlen($event['requestContext']['path']));
    }
    if (substr($path,-1)=='/') $path=substr($path,0,-1);
    $_SERVER['is_guestup_path'] = is_guestup_path($path);
    $_SERVER['PHP_SELF'] = path_format($_SERVER['base_path'] . $path);
    $_SERVER['REMOTE_ADDR'] = $event['requestContext']['sourceIp'];
    $_SERVER['HTTP_X_REQUESTED_WITH'] = $event['headers']['x-requested-with'];
    return $path;
}

function getConfig($str, $disktag = '')
{
    global $InnerEnv;
    global $Base64Env;
    if (in_array($str, $InnerEnv)) {
        if ($disktag=='') $disktag = $_SERVER['disktag'];
        $env = json_decode(getenv($disktag), true);
        if (isset($env[$str])) {
            if (in_array($str, $Base64Env)) return equal_replace($env[$str],1);
            else return $env[$str];
        }
    } else {
        if (in_array($str, $Base64Env)) return equal_replace(getenv($str),1);
        else return getenv($str);
    }
    return '';
}

function setConfig($arr, $disktag = '')
{
    global $InnerEnv;
    global $Base64Env;
    if ($disktag=='') $disktag = $_SERVER['disktag'];
    $disktags = explode("|",getConfig('disktag'));
    $diskconfig = json_decode(getenv($disktag), true);
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
        setConfig($tmp);
        if (needUpdate()) {
            OnekeyUpate();
            return message('update to github version, reinstall.<script>document.cookie=\'language=; path=/\';</script><meta http-equiv="refresh" content="3;URL=' . $url . '">', 'Program updating', 201);
        }
        return output('Jump<script>document.cookie=\'language=; path=/\';</script><meta http-equiv="refresh" content="3;URL=' . path_format($_SERVER['base_path'] . '/') . '">', 302);
    }
    if ($_GET['install1']) {
        //if ($_POST['admin']!='') {
            $tmp['language'] = $_POST['language'];
            $tmp['Region'] = $_POST['Region'];
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
            $response = json_decode(SetbaseConfig($tmp, $_SERVER['function_name'], $_POST['Region'], $_SERVER['namespace'], $SecretId, $SecretKey), true)['Response'];
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
        if (getConfig('SecretId')==''||getConfig('SecretKey')=='') $html .= '
        <a href="https://console.cloud.tencent.com/cam/capi" target="_blank">'.getconstStr('Create').' SecretId & SecretKey</a><br>
        <label>SecretId:<input name="SecretId" type="text" placeholder="" size=""></label><br>
        <label>SecretKey:<input name="SecretKey" type="text" placeholder="" size=""></label><br>';
        $html .= '
        <select class="changelanguage" name="Region">
            <option value="">选择区域</option>
            <option value="ap-beijing">华北地区(北京)</option>
            <option value="ap-chengdu">西南地区(成都)</option>
            <option value="ap-guangzhou">华南地区(广州)</option>
            <option value="ap-guangzhou-open">华南地区(广州Open)</option>
            <option value="ap-hongkong">港澳台地区(中国香港)</option>
            <option value="ap-mumbai">亚太南部(孟买)</option>
            <option value="ap-shanghai">华东地区(上海)</option>
            <option value="ap-shanghai-fsi">华东地区(上海金融)</option>
            <option value="ap-singapore">亚太东南(新加坡)</option>
            <option value="ap-tokyo">亚太东北(东京)</option>
            <option value="na-siliconvalley">美国西部(硅谷)</option>
            <option value="na-toronto">北美地区(多伦多)</option>
        </select>（腾讯几个月了还不做出来，只能先弄选择了）<br>
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
            document.cookie=\'language=\'+str+\'; path=/\';
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
    $title = 'Error';
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
    $tmp_env['Region'] = $Region;
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
    $data = ReorganizeDate($tmpdata);
    $signStr = base64_encode(hash_hmac('sha1', $meth.$host.'/?'.$data, $SecretKey, true));
    //echo urlencode($signStr);
    return post2url('https://'.$host, $data.'&Signature='.urlencode($signStr));
}

function SetbaseConfig($Envs, $function_name, $Region, $Namespace, $SecretId, $SecretKey)
{
    echo json_encode($Envs,JSON_PRETTY_PRINT);
    /*$trynum = 0;
    while( json_decode(getfunctioninfo($_SERVER['function_name'], $_SERVER['Region'], $_SERVER['namespace'], $SecretId, $SecretKey),true)['Response']['Status']!='Active' ) echo '
'.++$trynum;*/
    //json_decode($a,true)['Response']['Environment']['Variables'][0]['Key']
    $tmp = json_decode(getfunctioninfo($function_name, $Region, $Namespace, $SecretId, $SecretKey),true)['Response']['Environment']['Variables'];
    foreach ($tmp as $tmp1) {
        $tmp_env[$tmp1['Key']] = $tmp1['Value'];
    }
    foreach ($Envs as $key1 => $value1) {
        $tmp_env[$key1] = $value1;
    }
    $tmp_env = array_filter($tmp_env, 'array_value_isnot_null'); // remove null. 清除空值
    $tmp_env['Region'] = $Region;
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
    $tmpdata['Description'] = 'Onedrive index and manager in SCF.';
    $tmpdata['MemorySize'] = 64;
    $tmpdata['Timeout'] = 30;
    $data = ReorganizeDate($tmpdata);
    echo $data;
    $signStr = base64_encode(hash_hmac('sha1', $meth.$host.'/?'.$data, $SecretKey, true));
    //echo urlencode($signStr);
    return post2url('https://'.$host, $data.'&Signature='.urlencode($signStr));
}

function updateProgram($function_name, $Region, $Namespace, $SecretId, $SecretKey, $source)
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

function OnekeyUpate($auth = 'qkqpttgf', $project = 'OneManager-php', $branch = 'master')
{
    $source['url'] = 'https://github.com/' . $auth . '/' . $project;
    $source['branch'] = $branch;
    return json_decode(updateProgram($_SERVER['function_name'], $_SERVER['Region'], $_SERVER['namespace'], getConfig('SecretId'), getConfig('SecretKey'), $source), true)['Response'];
}

function setConfigResponse($response)
{
    return json_decode( $response, true )['Response'];
}
