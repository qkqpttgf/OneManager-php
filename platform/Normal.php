<?php

function getpath()
{
    $_SERVER['firstacceptlanguage'] = strtolower(splitfirst(splitfirst($_SERVER['HTTP_ACCEPT_LANGUAGE'],';')[0],',')[0]);
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
    if (isset($_SERVER['DOCUMENT_ROOT'])&&$_SERVER['DOCUMENT_ROOT']==='/app') $_SERVER['base_path'] = '/';
    else $_SERVER['base_path'] = path_format(substr($_SERVER['SCRIPT_NAME'], 0, -10) . '/');
    if (isset($_SERVER['UNENCODED_URL'])) $_SERVER['REQUEST_URI'] = $_SERVER['UNENCODED_URL'];
    $p = strpos($_SERVER['REQUEST_URI'],'?');
    if ($p>0) $path = substr($_SERVER['REQUEST_URI'], 0, $p);
    else $path = $_SERVER['REQUEST_URI'];
    $path = path_format( substr($path, strlen($_SERVER['base_path'])) );
    return $path;
    //return substr($path, 1);
    //return spurlencode($path, '/');
}

function getGET()
{
    if (!$_POST) {
        if (!!$HTTP_RAW_POST_DATA) {
            $tmpdata = $HTTP_RAW_POST_DATA;
        } else {
            $tmpdata = file_get_contents('php://input');
        }
        if (!!$tmpdata) {
            $postbody = explode("&", $tmpdata);
            foreach ($postbody as $postvalues) {
                $pos = strpos($postvalues,"=");
                $_POST[urldecode(substr($postvalues,0,$pos))]=urldecode(substr($postvalues,$pos+1));
            }
        }
    }
    if (isset($_SERVER['UNENCODED_URL'])) $_SERVER['REQUEST_URI'] = $_SERVER['UNENCODED_URL'];
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
    global $slash;
    if ($disktag=='') $disktag = $_SERVER['disktag'];
    $projectPath = splitlast(__DIR__, $slash)[0];
    $configPath = $projectPath . $slash . '.data' . $slash . 'config.php';
    $s = file_get_contents($configPath);
    $configs = '{' . splitlast(splitfirst($s, '{')[1], '}')[0] . '}';
    if ($configs!='') $envs = json_decode($configs, true);
    $disktags = explode("|", getConfig('disktag'));
    $indisk = 0;
    $operatedisk = 0;
    foreach ($arr as $k => $v) {
        if (isInnerEnv($k)) {
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
            if (isBase64Env($k)) $envs[$k] = base64y_encode($v);
            else $envs[$k] = $v;
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
            unset($envs[$arr['disktag_rename']]);
        } else {
            $disktags = array_unique($disktags);
            foreach ($disktags as $disktag) if ($disktag!='') $disktag_s .= $disktag . '|';
            if ($disktag_s!='') $envs['disktag'] = substr($disktag_s, 0, -1);
            else $envs['disktag'] = '';
        }
    }
    $envs = array_filter($envs, 'array_value_isnot_null');
    //ksort($envs);
    sortConfig($envs);
    
    //echo '<pre>'. json_encode($envs, JSON_PRETTY_PRINT).'</pre>';
    $prestr = '<?php $configs = \'' . PHP_EOL;
    $aftstr = PHP_EOL . '\';';
    $response = file_put_contents($configPath, $prestr . json_encode($envs, JSON_PRETTY_PRINT) . $aftstr);
    if ($response>0) return json_encode( [ 'response' => 'success' ] );
    return json_encode( [ 'message' => 'Failed to write config.', 'code' => 'failed' ] );
}

function install()
{
    global $constStr;
    if ($_GET['install2']) {
        if ($_POST['admin']!='') {
            $tmp['admin'] = $_POST['admin'];
            //$tmp['language'] = $_COOKIE['language'];
            $tmp['timezone'] = $_COOKIE['timezone'];
            $response = setConfigResponse( setConfig($tmp) );
            if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
                return message($html, $title, 201);
            } else {
                return output('Jump
            <script>
                var expd = new Date();
                expd.setTime(expd.getTime()+(2*60*60*1000));
                var expires = "expires="+expd.toGMTString();
                document.cookie=\'language=; path=/; \'+expires;
            </script>
            <meta http-equiv="refresh" content="3;URL=' . path_format($_SERVER['base_path'] . '/') . '">', 302);
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
        var nowtime= new Date();
        var timezone = 0-nowtime.getTimezoneOffset()/60;
        var expd = new Date();
        expd.setTime(expd.getTime()+(2*60*60*1000));
        var expires = "expires="+expd.toGMTString();
        document.cookie="timezone="+timezone+"; path=/; "+expires;
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
            url += "app.json";
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
                    alert("Url: " + url + "\nExpect http code 201, but received " + xhr4.status);
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
            var expd = new Date();
            expd.setTime(expd.getTime()+(2*60*60*1000));
            var expires = "expires="+expd.toGMTString();
            document.cookie=\'language=\'+str+\'; path=/; \'+expires;
            location.href = location.href;
        }
    </script>';
        $title = getconstStr('SelectLanguage');
        return message($html, $title, 201);
    }
    $html .= '<a href="?install0">'.getconstStr('ClickInstall').'</a>, '.getconstStr('LogintoBind');
    $title = 'Install';
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

function api_error($response)
{
    return isset($response['message']);
}

function api_error_msg($response)
{
    return $response['code'] . '<br>
' . $response['message'] . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>';
}

function setConfigResponse($response)
{
    return json_decode($response, true);
}

function OnekeyUpate($auth = 'BingoKingo', $project = 'Tfo', $branch = 'master')
{
    $slash = '/';
    if (strpos(__DIR__, ':')) $slash = '\\';
    // __DIR__ is xxx/platform
    $projectPath = splitlast(__DIR__, $slash)[0];

    // 从github下载对应tar.gz，并解压
    $url = 'https://github.com/' . $auth . '/' . $project . '/tarball/' . urlencode($branch) . '/';
    $tarfile = $projectPath . $slash .'github.tar.gz';
    $githubfile = file_get_contents($url);
    if (!$githubfile) return 0;
    file_put_contents($tarfile, $githubfile);
    if (splitfirst(PHP_VERSION, '.')[0] == '7') {
        $phar = new PharData($tarfile); // need php7
        $phar->extractTo($projectPath, null, true);//路径 要解压的文件 是否覆盖
    } else {
        ob_start();
        passthru('tar -xzvf ' . $tarfile, $stat);
        ob_get_clean();
    }
    unlink($tarfile);

    $outPath = '';
    $tmp = scandir($projectPath);
    $name = $auth . '-' . $project;
    foreach ($tmp as $f) {
        if ( substr($f, 0, strlen($name)) == $name) {
            $outPath = $projectPath . $slash . $f;
            break;
        }
    }
    //error_log1($outPath);
    if ($outPath=='') return 0;

    //unlink($outPath.'/config.php');
    $response = rename($projectPath . $slash . '.data' . $slash . 'config.php', $outPath . $slash . '.data' . $slash . 'config.php');
    if (!$response) {
        $tmp1['code'] = "Move Failed";
        $tmp1['message'] = "Can not move " . $projectPath . $slash . '.data' . $slash . 'config.php' . " to " . $outPath . $slash . '.data' . $slash . 'config.php';
        return json_encode($tmp1);
    }
    return moveFolder($outPath, $projectPath, $slash);
}

function moveFolder($from, $to, $slash)
{
    if (substr($from, -1)==$slash) $from = substr($from, 0, -1);
    if (substr($to, -1)==$slash) $to = substr($to, 0, -1);
    if (!file_exists($to)) mkdir($to, 0777);
    $handler=opendir($from);
    while($filename=readdir($handler)) {
        if($filename != '.' && $filename != '..'){
            $fromfile = $from . $slash . $filename;
            $tofile = $to . $slash . $filename;
            if(is_dir($fromfile)){// 如果读取的某个对象是文件夹，则递归
                $response = moveFolder($fromfile, $tofile, $slash);
                if (api_error(setConfigResponse($response))) return $response;
            }else{
                //if (file_exists($tofile)) unlink($tofile);
                $response = rename($fromfile, $tofile);
                if (!$response) {
                    $tmp['code'] = "Move Failed";
                    $tmp['message'] = "Can not move " . $fromfile . " to " . $tofile;
                    return json_encode($tmp);
                }
                if (file_exists($fromfile)) unlink($fromfile);
            }
        }
    }
    closedir($handler);
    rmdir($from);
    return json_encode( [ 'response' => 'success' ] );
}
