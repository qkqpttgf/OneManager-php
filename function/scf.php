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
    $_SERVER['function_name'] = $context['function_name'];
    $_SERVER['namespace'] = $context['namespace'];
    $host_name = $event['headers']['host'];
    $_SERVER['HTTP_HOST'] = $host_name;
    $serviceId = $event['requestContext']['serviceId'];
    if ( $serviceId === substr($host_name,0,strlen($serviceId)) ) {
        $_SERVER['base_path'] = '/'.$event['requestContext']['stage'].'/'.$_SERVER['function_name'].'/';
        $_SERVER['Region'] = substr($host_name, strpos($host_name, '.')+1);
        $_SERVER['Region'] = substr($_SERVER['Region'], 0, strpos($_SERVER['Region'], '.'));
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
    if ($disktag=='') $disktag = $_SERVER['disktag'];
    if (in_array($str, $InnerEnv)) {
        return json_decode(getenv($disktag), true)[$str];
    }
    return getenv($str);
}

function setConfig($arr, $disktag = '')
{
    global $InnerEnv;
    if ($disktag=='') $disktag = $_SERVER['disktag'];
    $disktags = explode("|",getConfig('disktag'));
    $diskconfig = json_decode(getenv($disktag), true);
    $tmp = [];
    $indisk = 0;
    $oparetdisk = 0;
    foreach ($arr as $k => $v) {
        if (in_array($k, $InnerEnv)) {
            $diskconfig[$k] = $v;
            $indisk = 1;
        } elseif ($k=='disktag_add') {
            array_push($disktags, $v);
            $oparetdisk = 1;
        } elseif ($k=='disktag_del') {
            $disktags = array_diff($disktags, [ $v ]);
            $tmp[$v] = '';
            $oparetdisk = 1;
        } else {
            $tmp[$k] = $v;
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
    return updateEnvironment($tmp, $_SERVER['function_name'], $_SERVER['Region'], $_SERVER['namespace'], getConfig('SecretId'), getConfig('SecretKey'));
}

function WaitSCFStat()
{
    $trynum = 0;
    while( json_decode(getfunctioninfo($_SERVER['function_name'], $_SERVER['Region'], $_SERVER['namespace'], getConfig('SecretId'), getConfig('SecretKey')),true)['Response']['Status']!='Active' ) echo '
'.++$trynum;
}

function get_refresh_token()
{
    global $constStr;
    global $CommonEnv;
    foreach ($CommonEnv as $env) $envs .= '\'' . $env . '\', ';
    $url = path_format($_SERVER['PHP_SELF'] . '/');
    if ($_GET['authorization_code'] && isset($_GET['code'])) {
        $_SERVER['disktag'] = $_COOKIE['disktag'];
        config_oauth();
        $tmp = curl_request($_SERVER['oauth_url'] . 'token', 'client_id=' . $_SERVER['client_id'] .'&client_secret=' . $_SERVER['client_secret'] . '&grant_type=authorization_code&requested_token_use=on_behalf_of&redirect_uri=' . $_SERVER['redirect_uri'] .'&code=' . $_GET['code']);
        if ($tmp['stat']==200) $ret = json_decode($tmp['body'], true);
        if (isset($ret['refresh_token'])) {
            $tmptoken = $ret['refresh_token'];
            $str = '
        refresh_token :<br>';
            /*for ($i=1;strlen($tmptoken)>0;$i++) {
                $t['t' . $i] = substr($tmptoken,0,128);
                $str .= '
            t' . $i . ':<textarea readonly style="width: 95%">' . $t['t' . $i] . '</textarea><br><br>';
                $tmptoken=substr($tmptoken,128);
            }
            $str .= '
        Add t1-t'.--$i.' to environments.*/
            $str .= '
        <textarea readonly style="width: 95%">' . $tmptoken . '</textarea><br><br>
        '.getconstStr('SavingToken').'
        <script>
            var texta=document.getElementsByTagName(\'textarea\');
            for(i=0;i<texta.length;i++) {
                texta[i].style.height = texta[i].scrollHeight + \'px\';
            }
            document.cookie=\'language=; path=/\';
            document.cookie=\'disktag=; path=/\';
        </script>';
            setConfig([ 'refresh_token' => $tmptoken, 'token_expires' => time()+30*24*60*60 ], $_COOKIE['disktag']);
            savecache('access_token', $ret['access_token'], $ret['expires_in'] - 60);
            WaitSCFStat();
            $str .= '
            <meta http-equiv="refresh" content="2;URL=' . $url . '">';
            return message($str, getconstStr('WaitJumpIndex'));
        }
        return message('<pre>' . json_encode(json_decode($tmp['body']), JSON_PRETTY_PRINT) . '</pre>', $tmp['stat']);
        //return message('<pre>' . json_encode($ret, JSON_PRETTY_PRINT) . '</pre>', 500);
    }
    if ($_GET['install1']) {
        $_SERVER['disk_oprating'] = $_COOKIE['disktag'];
        $_SERVER['disktag'] = $_COOKIE['disktag'];
        config_oauth();
        if (getConfig('Onedrive_ver')=='MS' || getConfig('Onedrive_ver')=='CN' || getConfig('Onedrive_ver')=='MSC') {
            return message('
    <a href="" id="a1">'.getconstStr('JumptoOffice').'</a>
    <script>
        url=location.protocol + "//" + location.host + "'.$url.'";
        url="'. $_SERVER['oauth_url'] .'authorize?scope='. $_SERVER['scope'] .'&response_type=code&client_id='. $_SERVER['client_id'] .'&redirect_uri='. $_SERVER['redirect_uri'] . '&state=' .'"+encodeURIComponent(url);
        document.getElementById(\'a1\').href=url;
        //window.open(url,"_blank");
        location.href = url;
    </script>
    ', getconstStr('Wait').' 1s', 201);
        }
    }
    if ($_GET['install0']) {
        if ($_POST['disktag_add']!='' && ($_POST['Onedrive_ver']=='MS' || $_POST['Onedrive_ver']=='CN' || $_POST['Onedrive_ver']=='MSC')) {
            if (in_array($_COOKIE['disktag'], $CommonEnv)) {
                return message('Do not input ' . $envs . '<br><button onclick="location.href = location.href;">'.getconstStr('Reflesh').'</button><script>document.cookie=\'disktag=; path=/\';</script>', 'Error', 201);
            }
            $_SERVER['disktag'] = $_COOKIE['disktag'];
            $tmp['disktag_add'] = $_POST['disktag_add'];
            $tmp['diskname'] = $_POST['diskname'];
            $tmp['Onedrive_ver'] = $_POST['Onedrive_ver'];
            if ($_POST['Onedrive_ver']=='MSC') {
                $tmp['client_id'] = $_POST['client_id'];
                $tmp['client_secret'] = equal_replace(base64_encode($_POST['client_secret']));
                //$_POST['client_secret'];
            }
            $response = json_decode( setConfig($tmp, $_COOKIE['disktag']), true )['Response'];
            $title = getconstStr('MayinEnv');
            $html = getconstStr('Wait') . ' 3s<meta http-equiv="refresh" content="3;URL=' . $url . '?AddDisk&install1">';
            if (isset($response['Error'])) {
                $html = $response['Error']['Code'] . '<br>
' . $response['Error']['Message'] . '<br><br>
function_name:' . $_SERVER['function_name'] . '<br>
Region:' . $_SERVER['Region'] . '<br>
namespace:' . $_SERVER['namespace'] . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Reflesh').'</button>';
                $title = 'Error';
            }
            return message($html, $title, 201);
        }
    }

    if ($constStr['language']!='zh-cn') {
        $linklang='en-us';
    } else $linklang='zh-cn';
    $ru = "https://developer.microsoft.com/".$linklang."/graph/quick-start?appID=_appId_&appName=_appName_&redirectUrl=".$_SERVER['redirect_uri']."&platform=option-php";
    $deepLink = "/quickstart/graphIO?publicClientSupport=false&appName=OneManager&redirectUrl=".$_SERVER['redirect_uri']."&allowImplicitFlow=false&ru=".urlencode($ru);
    $app_url = "https://apps.dev.microsoft.com/?deepLink=".urlencode($deepLink);
    $html = '
    <form action="?AddDisk&install0" method="post" onsubmit="return notnull(this);">
        '.getconstStr('OnedriveDiskTag').':<input type="text" name="disktag_add" placeholder="' . getconstStr('EnvironmentsDescription')['disktag'] . '" style="width:100%"><br>
        '.getconstStr('OnedriveDiskName').':<input type="text" name="diskname" placeholder="' . getconstStr('EnvironmentsDescription')['diskname'] . '" style="width:100%"><br>
        Onedrive_Ver：<br>
        <label><input type="radio" name="Onedrive_ver" value="MS" checked>MS: '.getconstStr('OndriveVerMS').'</label><br>
        <label><input type="radio" name="Onedrive_ver" value="CN">CN: '.getconstStr('OndriveVerCN').'</label><br>
        <label><input type="radio" name="Onedrive_ver" value="MSC" onclick="document.getElementById(\'secret\').style.display=\'\';">MSC: '.getconstStr('OndriveVerMSC').'
            <div id="secret" style="display:none">
                <a href="'.$app_url.'" target="_blank">'.getconstStr('GetSecretIDandKEY').'</a><br>
                client_secret:<input type="text" name="client_secret"><br>
                client_id:<input type="text" name="client_id" placeholder="12345678-90ab-cdef-ghij-klmnopqrstuv"><br>
            </div>
        </label><br>
        <input type="submit" value="'.getconstStr('Submit').'">
    </form>
    <script>
        function notnull(t)
        {
            if (t.disktag_add.value==\'\') {
                alert(\'Input Disk Tag\');
                return false;
            }
            envs = [' . $envs . '];
            if (envs.indexOf(t.disktag_add.value)>-1) {
                alert(\'' . $envs . '\');
                return false;
            }
            var reg = /^[a-zA-Z]([-_a-zA-Z0-9]{1,20})$/;
            if (!reg.test(t.disktag_add.value)) {
                alert(\''.getconstStr('TagFormatAlert').'\');
                return false;
            }
            document.cookie=\'disktag=\'+t.disktag_add.value+\'; path=/\';
            return true;
        }
    </script>';
    $title = 'Bind Onedrive';
    return message($html, $title, 201);
}

function install()
{
    global $constStr;
    if ($_GET['install1']) {
        if ($_POST['admin']!='') {
            $tmp['admin'] = $_POST['admin'];
            $tmp['language'] = $_POST['language'];
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
            $response = json_decode(SetbaseConfig($tmp, $_SERVER['function_name'], $_SERVER['Region'], $_SERVER['namespace'], $SecretId, $SecretKey), true)['Response'];
            if (isset($response['Error'])) {
                $html = $response['Error']['Code'] . '<br>
' . $response['Error']['Message'] . '<br><br>
function_name:' . $_SERVER['function_name'] . '<br>
Region:' . $_SERVER['Region'] . '<br>
namespace:' . $_SERVER['namespace'] . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Reflesh').'</button>';
                $title = 'Error';
            } else {
                $trynum = 0;
    while( json_decode(getfunctioninfo($_SERVER['function_name'], $_SERVER['Region'], $_SERVER['namespace'], $SecretId, $SecretKey),true)['Response']['Status']!='Active' ) echo '
'.++$trynum;
                //$response = json_decode( updateEnvironment($tmp, $_SERVER['function_name'], $_SERVER['Region'], $_SERVER['namespace'], $SecretId, $SecretKey), true)['Response'];
                if (needUpdate()) {
                    updateProgram($_SERVER['function_name'], $_SERVER['Region'], $_SERVER['namespace'], $SecretId, $SecretKey);
                    return message('update to github version, reinstall.<meta http-equiv="refresh" content="3;URL=' . $url . '">', 'Program updating', 201);
                }
                return output('Jump<meta http-equiv="refresh" content="3;URL=' . path_format($_SERVER['base_path'] . '/') . '">', 302);
            }
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
        <a href="https://console.cloud.tencent.com/cam/capi" target="_blank">'.getconstStr('Create').' SecretId & SecretKey</a><br>
        <label>SecretId:<input name="SecretId" type="text" placeholder="" size=""></label><br>
        <label>SecretKey:<input name="SecretKey" type="text" placeholder="" size=""></label><br>';
        $html .= '
        <label>Set admin password:<input name="admin" type="password" placeholder="' . getconstStr('EnvironmentsDescription')['admin'] . '" size="' . strlen(getconstStr('EnvironmentsDescription')['admin']) . '"></label><br>';
        $html .= '
        <input type="submit" value="'.getconstStr('Submit').'">
    </form>
    <script>
        function changelanguage(str)
        {
            document.cookie=\'language=\'+str+\'; path=/\';
            location.href = location.href;
        }
        function notnull(t)
        {
            if (t.admin.value==\'\') {
                alert(\'input admin\');
                return false;
            }';
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

function updateProgram($function_name, $Region, $Namespace, $SecretId, $SecretKey)
{
    WaitSCFStat();
    $meth = 'POST';
    $host = 'scf.tencentcloudapi.com';
    $tmpdata['Action'] = 'UpdateFunctionCode';
    $tmpdata['Code.GitUrl'] = 'https://github.com/qkqpttgf/OneManager-php';
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


function EnvOpt($function_name, $needUpdate = 0)
{
    global $constStr;
    global $ShowedCommonEnv;
    global $ShowedInnerEnv;
    asort($ShowedCommonEnv);
    asort($ShowedInnerEnv);
    $html = '<title>OneManager '.getconstStr('Setup').'</title>';
    if ($_POST['updateProgram']==getconstStr('updateProgram')) {
        $response = json_decode(updateProgram($function_name, $_SERVER['Region'], $_SERVER['namespace'], getConfig('SecretId'), getConfig('SecretKey')), true)['Response'];
        if (isset($response['Error'])) {
            $html = $response['Error']['Code'] . '<br>
' . $response['Error']['Message'] . '<br><br>
function_name:' . $_SERVER['function_name'] . '<br>
Region:' . $_SERVER['Region'] . '<br>
namespace:' . $namespace . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Reflesh').'</button>';
            $title = 'Error';
        } else {
            WaitSCFStat();
            $html .= getconstStr('UpdateSuccess') . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Reflesh').'</button>';
            $title = getconstStr('Setup');
        }
        return message($html, $title);
    }
    if ($_POST['submit1']) {
        $_SERVER['disk_oprating'] = '';
        foreach ($_POST as $k => $v) {
            if (in_array($k, $ShowedCommonEnv)||in_array($k, $ShowedInnerEnv)||$k=='disktag_del' || $k=='disktag_add') {
                $tmp[$k] = $v;
            }
            if ($k == 'disk') $_SERVER['disk_oprating'] = $v;
        }
        /*if ($tmp['domain_path']!='') {
            $tmp1 = explode("|",$tmp['domain_path']);
            $tmparr = [];
            foreach ($tmp1 as $multidomain_paths){
                $pos = strpos($multidomain_paths,":");
                if ($pos>0) $tmparr[substr($multidomain_paths, 0, $pos)] = path_format(substr($multidomain_paths, $pos+1));
            }
            $tmp['domain_path'] = $tmparr;
        }*/
        $response = json_decode( setConfig($tmp, $_SERVER['disk_oprating']), true )['Response'];
        if (isset($response['Error'])) {
                $html = $response['Error']['Code'] . '<br>
' . $response['Error']['Message'] . '<br><br>
function_name:' . $_SERVER['function_name'] . '<br>
Region:' . $_SERVER['Region'] . '<br>
namespace:' . $_SERVER['namespace'] . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Reflesh').'</button>';
                $title = 'Error';
            } else {
                WaitSCFStat();
                //sleep(3);
            $html .= json_encode($response,JSON_PRETTY_PRINT).'<script>location.href=location.href</script>';
            $title = getconstStr('Setup');
        }
        return message($html, $title);
    }
    if ($_GET['preview']) {
        $preurl = $_SERVER['PHP_SELF'] . '?preview';
    } else {
        $preurl = path_format($_SERVER['PHP_SELF'] . '/');
    }
    $html .= '
<a href="'.$preurl.'">'.getconstStr('Back').'</a>&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['base_path'].'">'.getconstStr('Back').getconstStr('Home').'</a><br>
<a href="https://github.com/qkqpttgf/OneManager-php">Github</a><br>
<form action="" method="post">
';
    if ($needUpdate) {
        $html .= '<pre>' . $_SERVER['github_version'] . '</pre>';
    } else {
        $html .= getconstStr('NotNeedUpdate');
    }
    $html .= '
    <input type="submit" name="updateProgram" value="'.getconstStr('updateProgram').'">
</form>
<table border=1 width=100%>
    <form name="common" action="" method="post">
        <tr>
            <td colspan="2">'.getconstStr('PlatformConfig').'</td>
        </tr>';
    foreach ($ShowedCommonEnv as $key) {
        if ($key=='language') {
            $html .= '
        <tr>
            <td><label>' . $key . '</label></td>
            <td width=100%>
                <select name="' . $key .'">';
            foreach ($constStr['languages'] as $key1 => $value1) {
                $html .= '
                    <option value="'.$key1.'" '.($key1==getConfig($key)?'selected="selected"':'').'>'.$value1.'</option>';
            }
            $html .= '
                </select>
            </td>
        </tr>';
        } elseif ($key=='theme') {
            $theme_arr = scandir('theme');
            $html .= '
        <tr>
            <td><label>' . $key . '</label></td>
            <td width=100%>
                <select name="' . $key .'">
                <option value=""></option>';
            foreach ($theme_arr as $v1) {
                if ($v1!='.' && $v1!='..') $html .= '
                    <option value="'.$v1.'" '.($v1==getConfig($key)?'selected="selected"':'').'>'.$v1.'</option>';
            }
            $html .= '
                </select>
            </td>
        </tr>';
        } /*elseif ($key=='domain_path') {
            $tmp = getConfig($key);
            $domain_path = '';
            foreach ($tmp as $k1 => $v1) {
                $domain_path .= $k1 . ':' . $v1 . '|';
            }
            $domain_path = substr($domain_path, 0, -1);
            $html .= '
        <tr>
            <td><label>' . $key . '</label></td>
            <td width=100%><input type="text" name="' . $key .'" value="' . $domain_path . '" placeholder="' . getconstStr('EnvironmentsDescription')[$key] . '" style="width:100%"></td>
        </tr>';
        }*/ else $html .= '
        <tr>
            <td><label>' . $key . '</label></td>
            <td width=100%><input type="text" name="' . $key .'" value="' . getConfig($key) . '" placeholder="' . getconstStr('EnvironmentsDescription')[$key] . '" style="width:100%"></td>
        </tr>';
    }
    $html .= '
        <tr><td><input type="submit" name="submit1" value="'.getconstStr('Setup').'"></td></tr>
    </form>
</table><br>';
    foreach (explode("|",getConfig('disktag')) as $disktag) {
        if ($disktag!='') {
            $html .= '
<table border=1 width=100%>
    <form action="" method="post">
        <tr>
            <td colspan="2">'.$disktag.'：
            <input type="hidden" name="disktag_del" value="'.$disktag.'">
            <input type="submit" name="submit1" value="'.getconstStr('DelDisk').'">
            </td>
        </tr>
    </form>';
            if (getConfig('refresh_token', $disktag)!='') {
                $html .= '
    <form name="'.$disktag.'" action="" method="post">
        <input type="hidden" name="disk" value="'.$disktag.'">';
                foreach ($ShowedInnerEnv as $key) {
                    $html .= '
        <tr>
            <td><label>' . $key . '</label></td>
            <td width=100%><input type="text" name="' . $key .'" value="' . getConfig($key, $disktag) . '" placeholder="' . getconstStr('EnvironmentsDescription')[$key] . '" style="width:100%"></td>
        </tr>';
                }
                $html .= '
        <tr><td><input type="submit" name="submit1" value="'.getconstStr('Setup').'"></td></tr>
    </form>';
            }
            $html .= '
</table><br>';
        }
    }
    $html .= '
<a href="?AddDisk">'.getconstStr('AddDisk').'</a>';
    return message($html, getconstStr('Setup'));
}
