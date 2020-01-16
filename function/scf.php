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
    $_SERVER['function_name'] = $context['function_name'];
    $host_name = $event['headers']['host'];
    $serviceId = $event['requestContext']['serviceId'];
    $public_path = path_format(getenv('public_path'));
    $private_path = path_format(getenv('private_path'));
    $domain_path = getenv('domain_path');
    $tmp_path='';
    if ($domain_path!='') {
        $tmp = explode("|",$domain_path);
        foreach ($tmp as $multidomain_paths){
            $pos = strpos($multidomain_paths,":");
            $tmp_path = path_format(substr($multidomain_paths,$pos+1));
            if (substr($multidomain_paths,0,$pos)==$host_name) $private_path=$tmp_path;
        }
    }
    // public_path is not Parent Dir of private_path. public_path 不能是 private_path 的上级目录。
    if ($tmp_path!='') if ($public_path == substr($tmp_path,0,strlen($public_path))) $public_path=$tmp_path;
    if ($public_path == substr($private_path,0,strlen($public_path))) $public_path=$private_path;
    if ( $serviceId === substr($host_name,0,strlen($serviceId)) ) {
        $_SERVER['base_path'] = '/'.$event['requestContext']['stage'].'/'.$_SERVER['function_name'].'/';
        $_SERVER['list_path'] = $public_path;
        $_SERVER['Region'] = substr($host_name, strpos($host_name, '.')+1);
        $_SERVER['Region'] = substr($_SERVER['Region'], 0, strpos($_SERVER['Region'], '.'));
        $path = substr($event['path'], strlen('/'.$_SERVER['function_name'].'/'));
    } else {
        $_SERVER['base_path'] = $event['requestContext']['path'];
        $_SERVER['list_path'] = $private_path;
        $_SERVER['Region'] = getenv('Region');
        $path = substr($event['path'], strlen($event['requestContext']['path']));
    }
    if (substr($path,-1)=='/') $path=substr($path,0,-1);
    if (empty($_SERVER['list_path'])) {
        $_SERVER['list_path'] = '/';
    } else {
        $_SERVER['list_path'] = spurlencode($_SERVER['list_path'],'/') ;
    }
    $_SERVER['is_imgup_path'] = is_imgup_path($path);
    $_SERVER['PHP_SELF'] = path_format($_SERVER['base_path'] . $path);
    $_SERVER['REMOTE_ADDR'] = $event['requestContext']['sourceIp'];
    $_SERVER['ajax']=0;
    if ($event['headers']['x-requested-with']=='XMLHttpRequest') {
        $_SERVER['ajax']=1;
    }
/*
    $referer = $event['headers']['referer'];
    $tmpurl = substr($referer,strpos($referer,'//')+2);
    $refererhost = substr($tmpurl,0,strpos($tmpurl,'/'));
    if ($refererhost==$host_name) {
        // Guest only upload from this site. 仅游客上传用，referer不对就空值，无法上传
        $_SERVER['current_url'] = substr($referer,0,strpos($referer,'//')) . '//' . $host_name.$_SERVER['PHP_SELF'];
    } else {
        $_SERVER['current_url'] = '';
    }
*/
    return $path;
}

function get_refresh_token($function_name, $Region, $Namespace)
{
    global $constStr;
    $url = path_format($_SERVER['PHP_SELF'] . '/');
    if ($_GET['authorization_code'] && isset($_GET['code'])) {
        $ret = json_decode(curl_request($_SERVER['oauth_url'] . 'token', 'client_id=' . $_SERVER['client_id'] .'&client_secret=' . $_SERVER['client_secret'] . '&grant_type=authorization_code&requested_token_use=on_behalf_of&redirect_uri=' . $_SERVER['redirect_uri'] .'&code=' . $_GET['code']), true);
        if (isset($ret['refresh_token'])) {
            $tmptoken=$ret['refresh_token'];
            $str = '
        refresh_token :<br>';
            for ($i=1;strlen($tmptoken)>0;$i++) {
                $t['t' . $i] = substr($tmptoken,0,128);
                $str .= '
            t' . $i . ':<textarea readonly style="width: 95%">' . $t['t' . $i] . '</textarea><br><br>';
                $tmptoken=substr($tmptoken,128);
            }
            $str .= '
        Add t1-t'.--$i.' to environments.
        <script>
            var texta=document.getElementsByTagName(\'textarea\');
            for(i=0;i<texta.length;i++) {
                texta[i].style.height = texta[i].scrollHeight + \'px\';
            }
            document.cookie=\'language=; path=/\';
        </script>';
            if (getenv('SecretId')!='' && getenv('SecretKey')!='') {
                echo updataEnvironment($t, $function_name, $Region, $Namespace);
                $str .= '
            <meta http-equiv="refresh" content="5;URL=' . $url . '">';
            }
            return message($str, $constStr['WaitJumpIndex'][$constStr['language']]);
        }
        return message('<pre>' . json_encode($ret, JSON_PRETTY_PRINT) . '</pre>', 500);
    }
    if ($_GET['install2']) {
        if (getenv('Onedrive_ver')=='MS' || getenv('Onedrive_ver')=='CN' || getenv('Onedrive_ver')=='MSC') {
            return message('
    <a href="" id="a1">'.$constStr['JumptoOffice'][$constStr['language']].'</a>
    <script>
        url=location.protocol + "//" + location.host + "'.$url.'";
        url="'. $_SERVER['oauth_url'] .'authorize?scope='. $_SERVER['scope'] .'&response_type=code&client_id='. $_SERVER['client_id'] .'&redirect_uri='. $_SERVER['redirect_uri'] . '&state=' .'"+encodeURIComponent(url);
        document.getElementById(\'a1\').href=url;
        //window.open(url,"_blank");
        location.href = url;
    </script>
    ', $constStr['Wait'][$constStr['language']].' 1s', 201);
        }
    }
    if ($_GET['install1']) {
        // echo $_POST['Onedrive_ver'];
        if ($_POST['Onedrive_ver']=='MS' || $_POST['Onedrive_ver']=='CN' || $_POST['Onedrive_ver']=='MSC') {
            $tmp['Onedrive_ver'] = $_POST['Onedrive_ver'];
            $tmp['language'] = $_COOKIE['language'];
            $tmp['client_id'] = $_POST['client_id'];
            $tmp['client_secret'] = equal_replace(base64_encode($_POST['client_secret']));
            $response = json_decode(updataEnvironment($tmp, $_SERVER['function_name'], $_SERVER['Region'], $Namespace), true)['Response'];
            sleep(2);
            $title = $constStr['MayinEnv'][$constStr['language']];
            $html = $constStr['Wait'][$constStr['language']] . ' 3s<meta http-equiv="refresh" content="3;URL=' . $url . '?install2">';
            if (isset($response['Error'])) {
                $html = $response['Error']['Code'] . '<br>
' . $response['Error']['Message'] . '<br><br>
function_name:' . $_SERVER['function_name'] . '<br>
Region:' . $_SERVER['Region'] . '<br>
namespace:' . $Namespace . '<br>
<button onclick="location.href = location.href;">'.$constStr['Reflesh'][$constStr['language']].'</button>';
                $title = 'Error';
            }
            return message($html, $title, 201);
        }
    }
    if ($_GET['install0']) {
        if (getenv('SecretId')=='' || getenv('SecretKey')=='') return message($constStr['SetSecretsFirst'][$constStr['language']].'<button onclick="location.href = location.href;">'.$constStr['Reflesh'][$constStr['language']].'</button><br>'.'(<a href="https://console.cloud.tencent.com/cam/capi" target="_blank">'.$constStr['Create'][$constStr['language']].' SecretId & SecretKey</a>)', 'Error', 500);
        $response = json_decode(SetConfig($_SERVER['function_name'], $_SERVER['Region'], $Namespace), true)['Response'];
        if (isset($response['Error'])) {
            $html = $response['Error']['Code'] . '<br>
' . $response['Error']['Message'] . '<br><br>
function_name:' . $_SERVER['function_name'] . '<br>
Region:' . $_SERVER['Region'] . '<br>
namespace:' . $Namespace . '<br>
<button onclick="location.href = location.href;">'.$constStr['Reflesh'][$constStr['language']].'</button>';
            $title = 'Error';
        } else {
            if ($constStr['language']!='zh-cn') {
                $linklang='en-us';
            } else $linklang='zh-cn';
            $ru = "https://developer.microsoft.com/".$linklang."/graph/quick-start?appID=_appId_&appName=_appName_&redirectUrl=".$_SERVER['redirect_uri']."&platform=option-php";
            $deepLink = "/quickstart/graphIO?publicClientSupport=false&appName=one_scf&redirectUrl=".$_SERVER['redirect_uri']."&allowImplicitFlow=false&ru=".urlencode($ru);
            $app_url = "https://apps.dev.microsoft.com/?deepLink=".urlencode($deepLink);
            $html = '
    <form action="?install1" method="post">
        Onedrive_Ver：<br>
        <label><input type="radio" name="Onedrive_ver" value="MS" checked>MS: '.$constStr['OndriveVerMS'][$constStr['language']].'</label><br>
        <label><input type="radio" name="Onedrive_ver" value="CN">CN: '.$constStr['OndriveVerCN'][$constStr['language']].'</label><br>
        <label><input type="radio" name="Onedrive_ver" value="MSC" onclick="document.getElementById(\'secret\').style.display=\'\';">MSC: '.$constStr['OndriveVerMSC'][$constStr['language']].'
            <div id="secret" style="display:none">
                <a href="'.$app_url.'" target="_blank">'.$constStr['GetSecretIDandKEY'][$constStr['language']].'</a><br>
                client_secret:<input type="text" name="client_secret"><br>
                client_id(12345678-90ab-cdef-ghij-klmnopqrstuv):<input type="text" name="client_id"><br>
            </div>
        </label><br>
        <input type="submit" value="'.$constStr['Submit'][$constStr['language']].'">
    </form>';
            $title = 'Install';
        }
        return message($html, $title, 201);
    }
    $html .= '
    <form action="?install0" method="post">
    language:<br>';
    foreach ($constStr['languages'] as $key1 => $value1) {
        $html .= '
    <label><input type="radio" name="language" value="'.$key1.'" '.($key1==$constStr['language']?'checked':'').' onclick="changelanguage(\''.$key1.'\')">'.$value1.'</label><br>';
    }
    $html .= '<br>
    <input type="submit" value="'.$constStr['Submit'][$constStr['language']].'">
    </form>
    <script>
        function changelanguage(str)
        {
            document.cookie=\'language=\'+str+\'; path=/\';
            location.href = location.href;
        }
    </script>';
    $title = $constStr['SelectLanguage'][$constStr['language']];
    return message($html, $title, 201);
}
