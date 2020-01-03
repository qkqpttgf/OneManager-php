<?php

function getGET()
{
    $getstr = substr(urldecode($_SERVER['REQUEST_URI']), strlen(urldecode($_SERVER['REDIRECT_URL'])));
    while (substr($getstr, 0, 1) == '/' || substr($getstr, 0, 1) == '?') $getstr = substr($getstr, 1);
    $getstrarr = explode("&",$getstr);
    foreach ($getstrarr as $getvalues) {
        if ($getvalues != '') {
            $pos = strpos($getvalues, "=");
            //echo $pos;
            if ($pos > 0) {
                $getarry[urldecode(substr($getvalues, 0, $pos))] = urldecode(substr($getvalues, $pos + 1));
            } else {
                $getarry[urldecode($getvalues)] = true;
            }
        }
    }
    if (isset($getarry)) {
        return $getarry;
    } else {
        return [];
    }
}

function config_oauth()
{
    global $constStr;
    $constStr['language'] = $_COOKIE['language'];
    if ($constStr['language']=='') $constStr['language'] = getConfig('language');
    if ($constStr['language']=='') $constStr['language'] = 'en-us';
    $_SERVER['sitename'] = getConfig('sitename');
    if (empty($_SERVER['sitename'])) $_SERVER['sitename'] = $constStr['defaultSitename'][$constStr['language']];
    $_SERVER['redirect_uri'] = 'https://scfonedrive.github.io';

    if (getConfig('Onedrive_ver')=='MS') {
        // MS
        // https://portal.azure.com
        $_SERVER['client_id'] = '4da3e7f2-bf6d-467c-aaf0-578078f0bf7c';
        $_SERVER['client_secret'] = '7/+ykq2xkfx:.DWjacuIRojIaaWL0QI6';
        $_SERVER['oauth_url'] = 'https://login.microsoftonline.com/common/oauth2/v2.0/';
        $_SERVER['api_url'] = 'https://graph.microsoft.com/v1.0/me/drive/root';
        $_SERVER['scope'] = 'https://graph.microsoft.com/Files.ReadWrite.All offline_access';
    }
    if (getConfig('Onedrive_ver')=='CN') {
        // CN
        // https://portal.azure.cn
        $_SERVER['client_id'] = '04c3ca0b-8d07-4773-85ad-98b037d25631';
        $_SERVER['client_secret'] = 'h8@B7kFVOmj0+8HKBWeNTgl@pU/z4yLB';
        $_SERVER['oauth_url'] = 'https://login.partner.microsoftonline.cn/common/oauth2/v2.0/';
        $_SERVER['api_url'] = 'https://microsoftgraph.chinacloudapi.cn/v1.0/me/drive/root';
        $_SERVER['scope'] = 'https://microsoftgraph.chinacloudapi.cn/Files.ReadWrite.All offline_access';
    }
    if (getConfig('Onedrive_ver')=='MSC') {
        // MS Customer
        // https://portal.azure.com
        $_SERVER['client_id'] = getConfig('client_id');
        $_SERVER['client_secret'] = getConfig('client_secret');
        $_SERVER['oauth_url'] = 'https://login.microsoftonline.com/common/oauth2/v2.0/';
        $_SERVER['api_url'] = 'https://graph.microsoft.com/v1.0/me/drive/root';
        $_SERVER['scope'] = 'https://graph.microsoft.com/Files.ReadWrite.All offline_access';
    }

    $_SERVER['client_secret'] = urlencode($_SERVER['client_secret']);
    $_SERVER['scope'] = urlencode($_SERVER['scope']);
}

function getListpath($domain)
{
    $domain_path = getConfig('domain_path');
    /*$tmp_path='';
    if ($domain_path!='') {
        $tmp = explode("|",$domain_path);
        foreach ($tmp as $multidomain_paths){
            $pos = strpos($multidomain_paths,":");
            $tmp_path = path_format(substr($multidomain_paths,$pos+1));
            if (substr($multidomain_paths,0,$pos)==$host_name) $private_path=$tmp_path;
        }
    }*/
    if (isset($domain_path[$domain])) return spurlencode($domain_path[$domain],'/');
    return spurlencode(getConfig('public_path'),'/');
}

function path_format($path)
{
    $path = '/' . $path;
    while (strpos($path, '//') !== FALSE) {
        $path = str_replace('//', '/', $path);
    }
    return $path;
}

function spurlencode($str,$splite='')
{
    $str = str_replace(' ', '%20',$str);
    $tmp='';
    if ($splite!='') {
        $tmparr=explode($splite,$str);
        for($x=0;$x<count($tmparr);$x++) {
            if ($tmparr[$x]!='') $tmp .= $splite . urlencode($tmparr[$x]);
        }
    } else {
        $tmp = urlencode($str);
    }
    $tmp = str_replace('%2520', '%20',$tmp);
    return $tmp;
}

function is_guestup_path($path)
{
    if (path_format('/'.path_format(urldecode($_SERVER['list_path'].path_format($path))).'/')==path_format('/'.path_format(getConfig('guestup_path')).'/')&&getConfig('guestup_path')!='') return 1;
    return 0;
}

function curl_request($url, $data = false, $headers = [])
{
    if (!isset($headers['Accept'])) $headers['Accept'] = '*/*';
    if (!isset($headers['Referer'])) $headers['Referer'] = $url;
    if (!isset($headers['Content-Type'])) $headers['Content-Type'] = 'application/x-www-form-urlencoded';
    $sendHeaders = array();
    foreach ($headers as $headerName => $headerVal) {
        $sendHeaders[] = $headerName . ': ' . $headerVal;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($data !== false) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $sendHeaders);
    $response['body'] = curl_exec($ch);
    $response['stat'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $response;
}

function clearbehindvalue($path,$page1,$maxpage,$pageinfocache)
{
    for ($page=$page1+1;$page<$maxpage;$page++) {
        $pageinfocache['nextlink_' . $path . '_page_' . $page] = '';
    }
    return $pageinfocache;
}

function comppass($pass)
{
    if ($_POST['password1'] !== '') if (md5($_POST['password1']) === $pass ) {
        date_default_timezone_set('UTC');
        $_SERVER['Set-Cookie'] = 'password='.$pass.'; expires='.date(DATE_COOKIE,strtotime('+1hour'));
        date_default_timezone_set(get_timezone($_COOKIE['timezone']));
        return 2;
    }
    if ($_COOKIE['password'] !== '') if ($_COOKIE['password'] === $pass ) return 3;
    return 4;
}

function encode_str_replace($str)
{
    $str = str_replace('&','&amp;',$str);
    $str = str_replace('+','%2B',$str);
    $str = str_replace('#','%23',$str);
    return $str;
}

function gethiddenpass($path,$passfile)
{
    $ispassfile = fetch_files(spurlencode(path_format($path . '/' . $passfile),'/'));
    //echo $path . '<pre>' . json_encode($ispassfile, JSON_PRETTY_PRINT) . '</pre>';
    if (isset($ispassfile['file'])) {
        $arr = curl_request($ispassfile['@microsoft.graph.downloadUrl']);
        if ($arr['stat']==200) {
            $passwordf=explode("\n",$arr['body']);
            $password=$passwordf[0];
            $password=md5($password);
            return $password;
        } else {
            //return md5('DefaultP@sswordWhenNetworkError');
            return md5( md5(time()).rand(1000,9999) );
        }
    } else {
        if ($path !== '' ) {
            $path = substr($path,0,strrpos($path,'/'));
            return gethiddenpass($path,$passfile);
        } else {
            return '';
        }
    }
    return '';
}

function get_refresh_token()
{
    global $constStr;
    $url = path_format($_SERVER['PHP_SELF'] . '/');
    if ($_GET['authorization_code'] && isset($_GET['code'])) {
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
        Adding refresh_token to Config.
        <script>
            var texta=document.getElementsByTagName(\'textarea\');
            for(i=0;i<texta.length;i++) {
                texta[i].style.height = texta[i].scrollHeight + \'px\';
            }
            document.cookie=\'language=; path=/\';
        </script>';
            setConfig([ 'refresh_token' => $tmptoken ]);
            $cache = null;
            $cache = new \Doctrine\Common\Cache\FilesystemCache(sys_get_temp_dir(), '.Onedrive');
            $cache->save('access_token', $ret['access_token'], $ret['expires_in'] - 60);
            $str .= '
            <meta http-equiv="refresh" content="5;URL=' . $url . '">';
            return message($str, $constStr['WaitJumpIndex'][$constStr['language']]);
        }
        return message('<pre>' . $tmp['body'] . '</pre>', $tmp['stat']);
        //return message('<pre>' . json_encode($ret, JSON_PRETTY_PRINT) . '</pre>', 500);
    }
    if ($_GET['install2']) {
        if (getConfig('Onedrive_ver')=='MS' || getConfig('Onedrive_ver')=='CN' || getConfig('Onedrive_ver')=='MSC') {
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
            if ($_POST['Onedrive_ver']=='MSC') {
                $tmp['client_id'] = $_POST['client_id'];
                $tmp['client_secret'] = $_POST['client_secret'];
            }
            $response = setConfig($tmp);
            $title = $constStr['MayinEnv'][$constStr['language']];
            $html = $constStr['Wait'][$constStr['language']] . ' 3s<meta http-equiv="refresh" content="3;URL=' . $url . '?install2">';
            if (!$response) {
                $html = $response . '<br>
Can not write config to file.<br>
<button onclick="location.href = location.href;">'.$constStr['Reflesh'][$constStr['language']].'</button>';
                $title = 'Error';
            }
            return message($html, $title, 201);
        }
    }
    if ($_GET['install0']) {
        if ($_POST['admin']!='') {
        $tmp['admin'] = $_POST['admin'];
        $tmp['language'] = $_POST['language'];
        $response = setConfig($tmp);
        if ($response) {
            if ($constStr['language']!='zh-cn') {
                $linklang='en-us';
            } else $linklang='zh-cn';
            $ru = "https://developer.microsoft.com/".$linklang."/graph/quick-start?appID=_appId_&appName=_appName_&redirectUrl=".$_SERVER['redirect_uri']."&platform=option-php";
            $deepLink = "/quickstart/graphIO?publicClientSupport=false&appName=OneManager&redirectUrl=".$_SERVER['redirect_uri']."&allowImplicitFlow=false&ru=".urlencode($ru);
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
        } else {
            $html = $response . '<br>
Can not write config to file.<br>
<button onclick="location.href = location.href;">'.$constStr['Reflesh'][$constStr['language']].'</button>';
            $title = 'Error';
        }
        return message($html, $title, 201);
        }
    }
    $html .= '
    <form action="?install0" method="post" onsubmit="return adminnotnull(this);">
    <label>admin:<input name="admin" type="password" placeholder="' . $constStr['EnvironmentsDescription']['admin'][$constStr['language']] . '" size="' . strlen($constStr['EnvironmentsDescription']['admin'][$constStr['language']]) . '"></label><br>
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
        function adminnotnull(t)
        {
            if (t.admin.value==\'\') {
                alert(\'input admin\');
                return false;
            }
            return true;
        }
    </script>';
    $title = $constStr['SelectLanguage'][$constStr['language']];
    return message($html, $title, 201);
}

function get_timezone($timezone = '8')
{
    $timezones = array( 
        '-12'=>'Pacific/Kwajalein', 
        '-11'=>'Pacific/Samoa', 
        '-10'=>'Pacific/Honolulu', 
        '-9'=>'America/Anchorage', 
        '-8'=>'America/Los_Angeles', 
        '-7'=>'America/Denver', 
        '-6'=>'America/Mexico_City', 
        '-5'=>'America/New_York', 
        '-4'=>'America/Caracas', 
        '-3.5'=>'America/St_Johns', 
        '-3'=>'America/Argentina/Buenos_Aires', 
        '-2'=>'America/Noronha',
        '-1'=>'Atlantic/Azores', 
        '0'=>'UTC', 
        '1'=>'Europe/Paris', 
        '2'=>'Europe/Helsinki', 
        '3'=>'Europe/Moscow', 
        '3.5'=>'Asia/Tehran', 
        '4'=>'Asia/Baku', 
        '4.5'=>'Asia/Kabul', 
        '5'=>'Asia/Karachi', 
        '5.5'=>'Asia/Calcutta', //Asia/Colombo
        '6'=>'Asia/Dhaka',
        '6.5'=>'Asia/Rangoon', 
        '7'=>'Asia/Bangkok', 
        '8'=>'Asia/Shanghai', 
        '9'=>'Asia/Tokyo', 
        '9.5'=>'Australia/Darwin', 
        '10'=>'Pacific/Guam', 
        '11'=>'Asia/Magadan', 
        '12'=>'Asia/Kamchatka'
    );
    if ($timezone=='') $timezone = '8';
    return $timezones[$timezone];
}

function message($message, $title = 'Message', $statusCode = 200)
{
    return output('<html><meta charset=utf-8><body><h1>' . $title . '</h1><p>' . $message . '</p></body></html>', $statusCode);
}

function needUpdate()
{
    if ($_SERVER['admin']) {
        $current_ver = file_get_contents(__DIR__ . '/version');
        $current_ver = substr($current_ver, strpos($current_ver, '.')+1);
        $current_ver = explode(urldecode('%0A'),$current_ver)[0];
        $current_ver = explode(urldecode('%0D'),$current_ver)[0];
        $github_version = file_get_contents('https://raw.githubusercontent.com/qkqpttgf/OneManager-php/master/version');
        $github_ver = substr($github_version, strpos($github_version, '.')+1);
        $github_ver = explode(urldecode('%0A'),$github_ver)[0];
        $github_ver = explode(urldecode('%0D'),$github_ver)[0];
        if ($current_ver != $github_ver) {
            $_SERVER['github_version'] = $github_version;
            return 1;
        }
    }
    return 0;
}

function output($body, $statusCode = 200, $headers = ['Content-Type' => 'text/html'], $isBase64Encoded = false)
{
    return [
        'isBase64Encoded' => $isBase64Encoded,
        'statusCode' => $statusCode,
        'headers' => $headers,
        'body' => $body
    ];
}

function passhidden($path)
{
    $path = str_replace('+','%2B',$path);
    $path = str_replace('&amp;','&', path_format(urldecode($path)));
    if (getConfig('passfile') != '') {
        if (substr($path,-1)=='/') $path=substr($path,0,-1);
        $hiddenpass=gethiddenpass($path,getConfig('passfile'));
        if ($hiddenpass != '') {
            return comppass($hiddenpass);
        } else {
            return 1;
        }
    } else {
        return 0;
    }
    return 4;
}

function size_format($byte)
{
    $i = 0;
    while (abs($byte) >= 1024) {
        $byte = $byte / 1024;
        $i++;
        if ($i == 3) break;
    }
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $ret = round($byte, 2);
    return ($ret . ' ' . $units[$i]);
}

function time_format($ISO)
{
    $ISO = str_replace('T', ' ', $ISO);
    $ISO = str_replace('Z', ' ', $ISO);
    //return $ISO;
    return date('Y-m-d H:i:s',strtotime($ISO . " UTC"));
}

function getConfig($str)
{
    $s = file_get_contents('config.json');
    if ($s!='') {
        $envs = json_decode($s, true);
        if (isset($envs[$str])) return $envs[$str];
    }
    return '';
    /*
    if (!class_exists('mydbreader')) {
        class mydbreader extends SQLite3
        {
            function __construct()
            {
                $this->open( __DIR__ .'/.ht.db');
            }
        }
    }
    $db = new mydbreader();
    if(!$db){
        echo $db->lastErrorMsg();
    } else {
        //echo "Opened database successfully<br>\n";
        $id=rand(1,309);
        $sql="select * from config where id=".$str.";";
        $ret = $db->query($sql);
        if(!$ret){
            echo $db->lastErrorMsg();
        } else {
            $row = $ret->fetchArray(SQLITE3_ASSOC);
            $value1 = $row['value'];
        }
        $db->close();
    }
    return $value1;
    */
}

function array_value_isnot_null($arr)
{
    return $arr!=='';
}

function setConfig($arr)
{
    $envs = json_decode(file_get_contents('config.json'), true);
    foreach ($arr as $k1 => $v1) {
        $envs[$k1] = $v1;
    }
    $envs = array_filter($envs, 'array_value_isnot_null');
    ksort($envs);
    //echo '<pre>'. json_encode($envs, JSON_PRETTY_PRINT).'</pre>';
    return file_put_contents('config.json', json_encode($envs, JSON_PRETTY_PRINT));
}

function get_thumbnails_url($path = '/')
{
    $path1 = path_format($path);
    $path = path_format($_SERVER['list_path'] . path_format($path));
    $url = $_SERVER['api_url'];
    if ($path !== '/') {
        $url .= ':' . $path;
        if (substr($url,-1)=='/') $url=substr($url,0,-1);
    }
    $url .= ':/thumbnails/0/medium';
    $files = json_decode(curl_request($url, false, ['Authorization' => 'Bearer ' . $_SERVER['access_token']])['body'], true);
    if (isset($files['url'])) return output($files['url']);
    return output('', 404);
}
