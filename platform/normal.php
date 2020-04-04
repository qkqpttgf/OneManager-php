<?php

function getpath()
{
    $_SERVER['firstacceptlanguage'] = strtolower(splitfirst(splitfirst($_SERVER['HTTP_ACCEPT_LANGUAGE'],';')[0],',')[0]);
    $_SERVER['base_path'] = path_format(substr($_SERVER['SCRIPT_NAME'], 0, -10) . '/');
    $p = strpos($_SERVER['REQUEST_URI'],'?');
    if ($p>0) $path = substr($_SERVER['REQUEST_URI'], 0, $p);
    else $path = $_SERVER['REQUEST_URI'];
    $path = path_format( substr($path, strlen($_SERVER['base_path'])) );
    return substr($path, 1);
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

function getConfig($str, $disktag = '')
{
    global $InnerEnv;
    global $Base64Env;
    //include 'config.php';
    $s = file_get_contents('config.php');
    $configs = substr($s, 18, -2);
    if ($configs!='') {
        $envs = json_decode($configs, true);
        if (in_array($str, $InnerEnv)) {
            if ($disktag=='') $disktag = $_SERVER['disktag'];
            if (isset($envs[$disktag][$str])) {
                if (in_array($str, $Base64Env)) return equal_replace($envs[$disktag][$str],1);
                else return $envs[$disktag][$str];
            }
        } else {
            if (isset($envs[$str])) {
                if (in_array($str, $Base64Env)) return equal_replace($envs[$str],1);
                else return $envs[$str];
            }
        }
    }
    return '';
}

function setConfig($arr, $disktag = '')
{
    global $InnerEnv;
    global $Base64Env;
    if ($disktag=='') $disktag = $_SERVER['disktag'];
    //include 'config.php';
    $s = file_get_contents('config.php');
    $configs = substr($s, 18, -2);
    if ($configs!='') $envs = json_decode($configs, true);
    $disktags = explode("|",getConfig('disktag'));
    //$indisk = 0;
    $operatedisk = 0;
    foreach ($arr as $k => $v) {
        if (in_array($k, $InnerEnv)) {
            if (in_array($k, $Base64Env)) $envs[$disktag][$k] = equal_replace($v);
            else $envs[$disktag][$k] = $v;
            /*$diskconfig[$k] = $v;
            $indisk = 1;*/
        } elseif ($k=='disktag_add') {
            array_push($disktags, $v);
            $operatedisk = 1;
        } elseif ($k=='disktag_del') {
            $disktags = array_diff($disktags, [ $v ]);
            $envs[$v] = '';
            $operatedisk = 1;
        } else {
            if (in_array($k, $Base64Env)) $envs[$k] = equal_replace($v);
            else $envs[$k] = $v;
        }
    }
    /*if ($indisk) {
        $diskconfig = array_filter($diskconfig, 'array_value_isnot_null');
        ksort($diskconfig);
        $tmp[$disktag] = json_encode($diskconfig);
    }*/
    if ($operatedisk) {
        $disktags = array_unique($disktags);
        foreach ($disktags as $disktag) if ($disktag!='') $disktag_s .= $disktag . '|';
        if ($disktag_s!='') $envs['disktag'] = substr($disktag_s, 0, -1);
        else $envs['disktag'] = '';
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

function install()
{
    global $constStr;
    if ($_GET['install2']) {
        if ($_POST['admin']!='') {
            $tmp['admin'] = $_POST['admin'];
            $tmp['language'] = $_POST['language'];
            $response = setConfig($tmp);
            if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
                return message($html, $title, 201);
            } else {
                return output('Jump<script>document.cookie=\'language=; path=/\';</script><meta http-equiv="refresh" content="3;URL=' . path_format($_SERVER['base_path'] . '/') . '">', 302);
            }
        }
    }
    if ($_GET['install1']) {
        if (!ConfigWriteable()) {
            $html .= getconstStr('MakesuerWriteable');
            $title = 'Error';
            return message($html, $title, 201);
        }
        /*if (!RewriteEngineOn()) {
            $html .= getconstStr('MakesuerRewriteOn');
            $title = 'Error';
            return message($html, $title, 201);
        }*/
        $html .= '<button id="checkrewritebtn" onclick="checkrewrite();">'.getconstStr('MakesuerRewriteOn').'</button>
<div id="formdiv" style="display: none">
    <form action="?install2" method="post" onsubmit="return notnull(this);">
        <input name="admin" type="password" placeholder="' . getconstStr('EnvironmentsDescription')['admin'] . '" size="' . strlen(getconstStr('EnvironmentsDescription')['admin']) . '"><br>
        <input id="submitbtn" type="submit" value="'.getconstStr('Submit').'" disabled>
    </form>
</div>
    <script>
        function notnull(t)
        {
            if (t.admin.value==\'\') {
                alert(\''.getconstStr('SetAdminPassword').'\');
                return false;
            }
            return true;
        }
        function checkrewrite()
        {
            url=location.protocol + "//" + location.host;
            //if (location.port!="") url += ":" + location.port;
            url += location.pathname;
            if (url.substr(-1)!="/") url += "/";
            url += "config.php";
            //alert(url);
            var xhr4 = new XMLHttpRequest();
            xhr4.open("GET", url);
            xhr4.setRequestHeader("x-requested-with","XMLHttpRequest");
            xhr4.send(null);
            xhr4.onload = function(e){
                console.log(xhr4.responseText+","+xhr4.status);
                if (xhr4.status==201) {
                    document.getElementById("checkrewritebtn").style.display = "none";
                    document.getElementById("submitbtn").disabled = false;
                    document.getElementById("formdiv").style.display = "";
                } else {
                    alert(url+"\n"+xhr4.status);
                }
            }
        }
    </script>';
        $title = getconstStr('SetAdminPassword');
        return message($html, $title, 201);
    }
    if ($_GET['install0']) {
        $html .= '
    <form action="?install1" method="post">
language:<br>';
        foreach ($constStr['languages'] as $key1 => $value1) {
            $html .= '
        <label><input type="radio" name="language" value="'.$key1.'" '.($key1==$constStr['language']?'checked':'').' onclick="changelanguage(\''.$key1.'\')">'.$value1.'</label><br>';
        }
        $html .= '
        <input type="submit" value="'.getconstStr('Submit').'">
    </form>
    <script>
        function changelanguage(str)
        {
            document.cookie=\'language=\'+str+\'; path=/\';
            location.href = location.href;
        }
    </script>';
        $title = getconstStr('SelectLanguage');
        return message($html, $title, 201);
    }
    $html .= '<a href="?install0">'.getconstStr('ClickInstall').'</a>, '.getconstStr('LogintoBind');
    $title = 'Error';
    return message($html, $title, 201);
}

function ConfigWriteable()
{
    $t = md5( md5(time()).rand(1000,9999) );
    $r = setConfig([ 'tmp' => $t ]);
    $tmp = getConfig('tmp');
    setConfig([ 'tmp' => '' ]);
    if ($tmp == $t) return true;
    if ($r) return true;
    return false;
}

function RewriteEngineOn()
{
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    $tmpurl = $http_type . $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'];
    $tmpurl .= path_format($_SERVER['base_path'] . '/config.php');
    $tmp = curl_request($tmpurl);
    if ($tmp['stat']==200) return false;
    if ($tmp['stat']==201) return true; //when install return 201, after installed return 404 or 200;
    return false;
}

function api_error($response)
{
    return !$response;
}

function api_error_msg($response)
{
    return $response . '<br>
Can not write config to file.<br>
<button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>';
}

function OnekeyUpate()
{
    return json_decode(updateHerokuapp(getConfig('function_name'), getConfig('APIKey'))['body'], true);
}

function setConfigResponse($response)
{
    return $response;
}
