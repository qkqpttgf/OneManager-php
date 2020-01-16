<?php

function getpath()
{
    $_SERVER['base_path'] = path_format(substr($_SERVER['SCRIPT_NAME'], 0, -10) . '/');
    $p = strpos($_SERVER['REQUEST_URI'],'?');
    if ($p>0) $path = substr($_SERVER['REQUEST_URI'], 0, $p);
    else $path = $_SERVER['REQUEST_URI'];
    $path = path_format( substr($path, strlen($_SERVER['base_path'])) );
    return $path;
    //return spurlencode($path, '/');
}

function getGET()
{
    $getstr = urldecode(substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'],'?')));
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
            savecache('access_token', $ret['access_token'], $ret['expires_in'] - 60);
            $str .= '
            <meta http-equiv="refresh" content="5;URL=' . $url . '">';
            return message($str, $constStr['WaitJumpIndex'][$constStr['language']]);
        }
        return message('<pre>' . $tmp['body'] . '</pre>', $tmp['stat']);
        //return message('<pre>' . json_encode($ret, JSON_PRETTY_PRINT) . '</pre>', 500);
    }
    if ($_GET['install3']) {
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
    if ($_GET['install2']) {
        // echo $_POST['Onedrive_ver'];
        if ($_POST['Onedrive_ver']=='MS' || $_POST['Onedrive_ver']=='CN' || $_POST['Onedrive_ver']=='MSC') {
            $tmp['Onedrive_ver'] = $_POST['Onedrive_ver'];
            if ($_POST['Onedrive_ver']=='MSC') {
                $tmp['client_id'] = $_POST['client_id'];
                $tmp['client_secret'] = $_POST['client_secret'];
            }
            $response = setConfig($tmp);
            $title = $constStr['MayinEnv'][$constStr['language']];
            $html = $constStr['Wait'][$constStr['language']] . ' 3s<meta http-equiv="refresh" content="3;URL=' . $url . '?install3">';
            if (!$response) {
                $html = $response . '<br>
Can not write config to file.<br>
<button onclick="location.href = location.href;">'.$constStr['Reflesh'][$constStr['language']].'</button>';
                $title = 'Error';
            }
            return message($html, $title, 201);
        }
    }
    if ($_GET['install1']) {
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
    <form action="?install2" method="post">
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
    if ($_GET['install0']) {
        if (!ConfigWriteable()) {
            $html .= 'Plase make sure the config.php is writeable.
run Writeable.sh.';
            $title = 'Error';
            return message($html, $title, 201);
        }
        if (!RewriteEngineOn()) {
            $html .= 'Plase make sure the RewriteEngine is On.';
            $title = 'Error';
            return message($html, $title, 201);
        }
        $html .= '
    <form action="?install1" method="post" onsubmit="return adminnotnull(this);">
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
    $html .= 'refresh_token not exist, <a href="?install0">click to install.</a>';
    $title = 'Error';
    return message($html, $title, 201);
}

function ConfigWriteable()
{
    $t = md5( md5(time()).rand(1000,9999) );
    setConfig([ 'tmp' => $t ]);
    $tmp = getConfig('tmp');
    setConfig([ 'tmp' => '' ]);
    if ($tmp == $t) return true;
    return false;
}

function RewriteEngineOn()
{
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    $tmpurl=$http_type . $_SERVER['SERVER_NAME'] . path_format($_SERVER['base_path'] . '/config.php');
    $tmp = curl_request($tmpurl);
    if ($tmp['stat']==201) return true; //when install return 201, after installed return 404 or 200;
    return false;
}

function getConfig($str)
{
    include 'config.php';
    //$s = file_get_contents('config.json');
    if ($configs!='') {
        $envs = json_decode($configs, true);
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
    include 'config.php';
    if ($configs!='') $envs = json_decode($configs, true);
    foreach ($arr as $k1 => $v1) {
        $envs[$k1] = $v1;
    }
    $envs = array_filter($envs, 'array_value_isnot_null');
    ksort($envs);
    //echo '<pre>'. json_encode($envs, JSON_PRETTY_PRINT).'</pre>';
    $prestr = '<?php $configs = \'
';
    $aftstr = '
\';';
    return file_put_contents('config.php', $prestr . json_encode($envs, JSON_PRETTY_PRINT) . $aftstr);
}
