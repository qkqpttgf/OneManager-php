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
    $p = strpos($_SERVER['REQUEST_URI'],'?');
    if ($p>0) {
        $getstr = substr($_SERVER['REQUEST_URI'], $p+1);
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
            setConfig([ 'refresh_token' => $tmptoken, 'token_expires' => time()+30*24*60*60 ]);
            savecache('access_token', $ret['access_token'], $ret['expires_in'] - 60);
            $str .= '
            <meta http-equiv="refresh" content="5;URL=' . $url . '">';
            return message($str, getconstStr('WaitJumpIndex'));
        }
        return message('<pre>' . $tmp['body'] . '</pre>', $tmp['stat']);
        //return message('<pre>' . json_encode($ret, JSON_PRETTY_PRINT) . '</pre>', 500);
    }
    if ($_GET['install3']) {
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
    if ($_GET['install2']) {
        // echo $_POST['Onedrive_ver'];
        if ($_POST['Onedrive_ver']=='MS' || $_POST['Onedrive_ver']=='CN' || $_POST['Onedrive_ver']=='MSC') {
            $tmp['Onedrive_ver'] = $_POST['Onedrive_ver'];
            if ($_POST['Onedrive_ver']=='MSC') {
                $tmp['client_id'] = $_POST['client_id'];
                $tmp['client_secret'] = $_POST['client_secret'];
            }
            $response = setConfig($tmp);
            $title = getconstStr('MayinEnv');
            $html = getconstStr('Wait') . ' 3s<meta http-equiv="refresh" content="3;URL=' . $url . '?install3">';
            if (!$response) {
                $html = $response . '<br>
Can not write config to file.<br>
<button onclick="location.href = location.href;">'.getconstStr('Reflesh').'</button>';
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
        <label><input type="radio" name="Onedrive_ver" value="MS" checked>MS: '.getconstStr('OndriveVerMS').'</label><br>
        <label><input type="radio" name="Onedrive_ver" value="CN">CN: '.getconstStr('OndriveVerCN').'</label><br>
        <label><input type="radio" name="Onedrive_ver" value="MSC" onclick="document.getElementById(\'secret\').style.display=\'\';">MSC: '.getconstStr('OndriveVerMSC').'
            <div id="secret" style="display:none">
                <a href="'.$app_url.'" target="_blank">'.getconstStr('GetSecretIDandKEY').'</a><br>
                client_secret:<input type="text" name="client_secret"><br>
                client_id(12345678-90ab-cdef-ghij-klmnopqrstuv):<input type="text" name="client_id"><br>
            </div>
        </label><br>
        <input type="submit" value="'.getconstStr('Submit').'">
    </form>';
            $title = 'Install';
        } else {
            $html = $response . '<br>
Can not write config to file.<br>
<button onclick="location.href = location.href;">'.getconstStr('Reflesh').'</button>';
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
language:<br>';
        foreach ($constStr['languages'] as $key1 => $value1) {
            $html .= '
        <label><input type="radio" name="language" value="'.$key1.'" '.($key1==$constStr['language']?'checked':'').' onclick="changelanguage(\''.$key1.'\')">'.$value1.'</label><br>';
        }
        $html .= '<br>
        <label>admin:<input name="admin" type="password" placeholder="' . getconstStr('EnvironmentsDescription')['admin'] . '" size="' . strlen(getconstStr('EnvironmentsDescription')['admin']) . '"></label><br>
        <input type="submit" value="'.getconstStr('Submit').'">
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
        $title = getconstStr('SelectLanguage');
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
    $tmpurl = $http_type . $_SERVER['SERVER_NAME'];
    //if $_SERVER['SERVER_PORT']
    $tmpurl .= path_format($_SERVER['base_path'] . '/config.php');
    $tmp = curl_request($tmpurl);
    if ($tmp['stat']==200) return false;
    if ($tmp['stat']==201) return true; //when install return 201, after installed return 404 or 200;
    return false;
}

function getConfig($str)
{
    //include 'config.php';
    $s = file_get_contents('config.php');
    $configs = substr($s, 18, -2);
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

function setConfig($arr)
{
    //include 'config.php';
    $s = file_get_contents('config.php');
    $configs = substr($s, 18, -2);
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

function EnvOpt($function_name, $needUpdate = 0)
{
    global $constStr;
    $constEnv = [
        //'admin',
        'adminloginpage', 'domain_path', 'guestup_path', 'passfile',
        //'private_path', 
        'public_path', 'sitename', 'language', 'theme'
    ];
    asort($constEnv);
    $html = '<title>OneManager '.getconstStr('Setup').'</title>';
    /*if ($_POST['updateProgram']==getconstStr('updateProgram')) {
        $response = json_decode(updataProgram($function_name, $Region, $namespace), true)['Response'];
        if (isset($response['Error'])) {
            $html = $response['Error']['Code'] . '<br>
' . $response['Error']['Message'] . '<br><br>
function_name:' . $_SERVER['function_name'] . '<br>
Region:' . $_SERVER['Region'] . '<br>
namespace:' . $namespace . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Reflesh').'</button>';
            $title = 'Error';
        } else {
            $html .= getconstStr('UpdateSuccess') . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Reflesh').'</button>';
            $title = getconstStr('Setup');
        }
        return message($html, $title);
    }*/
    if ($_POST['submit1']) {
        foreach ($_POST as $k => $v) {
            if (in_array($k, $constEnv)) {
                //if (!(getConfig($k)==''&&$v=='')) 
                $tmp[$k] = $v;
            }
        }
        if ($tmp['domain_path']!='') {
            $tmp1 = explode("|",$tmp['domain_path']);
            $tmparr = [];
            foreach ($tmp1 as $multidomain_paths){
                $pos = strpos($multidomain_paths,":");
                if ($pos>0) $tmparr[substr($multidomain_paths, 0, $pos)] = path_format(substr($multidomain_paths, $pos+1));
            }
            $tmp['domain_path'] = $tmparr;
        }
        $response = setConfig($tmp);
        if (!$response) {
            $html = $response . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Reflesh').'</button>';
            $title = 'Error';
        } else {
            $html .= '<script>location.href=location.href</script>';
        }
    }
    if ($_GET['preview']) {
        $preurl = $_SERVER['PHP_SELF'] . '?preview';
    } else {
        $preurl = path_format($_SERVER['PHP_SELF'] . '/');
    }
    $html .= '
        <a href="'.$preurl.'">'.getconstStr('Back').'</a>&nbsp;&nbsp;&nbsp;
        <a href="https://github.com/qkqpttgf/OneManager-php">Github</a><br>';
    /*if ($needUpdate) {
        $html .= '<pre>' . $_SERVER['github_version'] . '</pre>
        <form action="" method="post">
            <input type="submit" name="updateProgram" value="'.getconstStr('updateProgram').'">
        </form>';
    } else {
        $html .= getconstStr('NotNeedUpdate');
    }*/
    $html .= '
    <form action="" method="post">
    <table border=1 width=100%>';
    foreach ($constEnv as $key) {
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
    $html .= '</table>
    <input type="submit" name="submit1" value="'.getconstStr('Setup').'">
    </form>';
    return message($html, getconstStr('Setup'));
}
