<?php

function getpath()
{
    $_SERVER['firstacceptlanguage'] = strtolower(splitfirst(splitfirst($_SERVER['HTTP_ACCEPT_LANGUAGE'],';')[0],',')[0]);
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
    if ($_SERVER['REQUEST_SCHEME']!='http'&&$_SERVER['REQUEST_SCHEME']!='https') {
        if ($_SERVER['HTTP_X_FORWARDED_PROTO']!='') {
            $tmp = explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0];
            if ($tmp=='http'||$tmp=='https') $_SERVER['REQUEST_SCHEME'] = $tmp;
        }
        if ($_SERVER['HTTP_FLY_FORWARDED_PROTO']!='') $_SERVER['REQUEST_SCHEME'] = $_SERVER['HTTP_FLY_FORWARDED_PROTO'];
    }
    $_SERVER['host'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    $_SERVER['referhost'] = explode('/', $_SERVER['HTTP_REFERER'])[2];
    $_SERVER['base_path'] = '/';
    if (isset($_SERVER['UNENCODED_URL'])) $_SERVER['REQUEST_URI'] = $_SERVER['UNENCODED_URL'];
    $p = strpos($_SERVER['REQUEST_URI'],'?');
    if ($p>0) $path = substr($_SERVER['REQUEST_URI'], 0, $p);
    else $path = $_SERVER['REQUEST_URI'];
    $path = path_format( substr($path, strlen($_SERVER['base_path'])) );
    return $path;
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

function ReplitAPI($op, $key, $value = '') {
    //error_log1($op . '_' . $key . '_' . $value);
    $apiurl = getenv('REPLIT_DB_URL');
    //foreach (explode("\n", curl('GET', $apiurl . '?prefix')['body']) as $a) curl('DELETE', $apiurl . '/' . $a);
    if ($op === 'r') {
        if (!($config = getcache('REPLIT_CONFIG'))) {
            $config = json_decode(curl('GET', $apiurl . '/REPLIT_CONFIG')['body'], true);
            savecache('REPLIT_CONFIG', $config);
        }
        return ['stat'=>200, 'body'=>(is_array($config[$key])?json_encode($config[$key]):$config[$key])];
    } elseif ($op === 'w') {
        return curl('POST', $apiurl, 'REPLIT_CONFIG=' . $value, ["Content-Type"=>"application/x-www-form-urlencoded"]);
    } elseif ($op === 'd') {
        // not use
        return curl('DELETE', $apiurl . '/' . $key);
    } else {
        return ['stat'=>500, 'body'=>'error option input to function ReplitAPI().'];
    }
}

function getConfig($str, $disktag = '')
{
    if (isInnerEnv($str)) {
        if ($disktag=='') $disktag = $_SERVER['disktag'];
        $env = json_decode(ReplitAPI('r', $disktag)['body'], true);
        if (isset($env[$str])) {
            if (isBase64Env($str)) return base64y_decode($env[$str]);
            else return $env[$str];
        }
    } else {
        if (isBase64Env($str)) return base64y_decode(ReplitAPI('r', $str)['body']);
        else return ReplitAPI('r', $str)['body'];
    }
    return '';
}

function setConfig($arr, $disktag = '')
{
    if (!($envs = getcache('REPLIT_CONFIG'))) {
        $envs = json_decode(curl('GET', getenv('REPLIT_DB_URL') . '/REPLIT_CONFIG')['body'], true);
        savecache('REPLIT_CONFIG', $envs);
    }
    if ($disktag=='') $disktag = $_SERVER['disktag'];
    $disktags = explode("|", getConfig('disktag'));
    $indisk = 0;
    $operatedisk = 0;
    foreach ($arr as $k => $v) {
        if (isCommonEnv($k)) {
            if (isBase64Env($k)) $envs[$k] = base64y_encode($v);
            else $envs[$k] = $v;
        } elseif (isInnerEnv($k)) {
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
            $envs[$k] = $v;
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
    sortConfig($envs);
    $response = ReplitAPI('w', 'REPLIT_CONFIG', json_encode($envs));
    //error_log1(json_encode($arr, JSON_PRETTY_PRINT) . ' => tmp：' . json_encode($envs, JSON_PRETTY_PRINT));
    savecache('REPLIT_CONFIG', null, '', 0);
    if (api_error($response)) return ['stat'=>$response['stat'], 'body'=>$response['body'] . "<br>\nError in writting " . $key . "=" . $val];
    return $response;
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
        /*if (!ConfigWriteable()) {
            $html .= getconstStr('MakesuerWriteable');
            $title = 'Error';
            return message($html, $title, 201);
        }
        if (!RewriteEngineOn()) {
            $html .= getconstStr('MakesuerRewriteOn');
            $title = 'Error';
            return message($html, $title, 201);
        }*/
        $html .= '
    <form action="?install2" method="post" onsubmit="return notnull(this);">
        <input name="admin" type="password" placeholder="' . getconstStr('EnvironmentsDescription')['admin'] . '" size="' . strlen(getconstStr('EnvironmentsDescription')['admin']) . '"><br>
        <input id="submitbtn" type="submit" value="'.getconstStr('Submit').'">
    </form>
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

    $title = 'Install';
    $html = '<a href="?install0">' . getconstStr('ClickInstall') . '</a>, ' . getconstStr('LogintoBind');
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
    return !($response['stat']==200||$response['stat']==204||$response['stat']==404);
    //return isset($response['message']);
}

function api_error_msg($response)
{
    return '<pre>'. json_encode($response, JSON_PRETTY_PRINT).'</pre>' . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>';
}

function setConfigResponse($response)
{
    return $response;
    //return json_decode($response, true);
}

function OnekeyUpate($GitSource = 'Github', $auth = 'qkqpttgf', $project = 'OneManager-php', $branch = 'master')
{
    // __DIR__ is xxx/platform
    $projectPath = splitlast(__DIR__, '/')[0];

    if ($GitSource=='Github') {
        // 从github下载对应tar.gz，并解压
        $url = 'https://github.com/' . $auth . '/' . $project . '/tarball/' . urlencode($branch) . '/';
    } elseif ($GitSource=='HITGitlab') {
        $url = 'https://git.hit.edu.cn/' . $auth . '/' . $project . '/-/archive/' . urlencode($branch) . '/' . $project . '-' . urlencode($branch) . '.tar.gz';
    } else return ['stat'=>500, 'body'=>'Git Source input Error!'];
    $tarfile = $projectPath . '/github.tar.gz';
    $githubfile = file_get_contents($url);
    if (!$githubfile) return ['stat'=>500, 'body'=>'download error from github.'];
    file_put_contents($tarfile, $githubfile);
    if (splitfirst(PHP_VERSION, '.')[0] > '5') {
        $phar = new PharData($tarfile); // need php5.3, 7, 8
        $phar->extractTo($projectPath, null, true);//路径 要解压的文件 是否覆盖
    } else {
        ob_start();
        passthru('tar -xzvf ' . $tarfile, $stat);
        ob_get_clean();
    }
    unlink($tarfile);

    $outPath = '';
    $outPath = findIndexPath($projectPath);
    //error_log1($outPath);
    if ($outPath=='') return ['stat'=>500, 'body'=>'can\'t find folder after download from github.'];

    return moveFolder($outPath, $projectPath);
}

function moveFolder($from, $to)
{
    if (substr($from, -1)=='/') $from = substr($from, 0, -1);
    if (substr($to, -1)=='/') $to = substr($to, 0, -1);
    if (!file_exists($to)) mkdir($to, 0777);
    $handler=opendir($from);
    while($filename=readdir($handler)) {
        if($filename != '.' && $filename != '..'){
            $fromfile = $from . '/' . $filename;
            $tofile = $to . '/' . $filename;
            if(is_dir($fromfile)){// 如果读取的某个对象是文件夹，则递归
                $response = moveFolder($fromfile, $tofile);
                if (api_error(setConfigResponse($response))) return $response;
            }else{
                if (file_exists($tofile)) unlink($tofile);
                $response = rename($fromfile, $tofile);
                if (!$response) {
                    $tmp['code'] = "Move Failed";
                    $tmp['message'] = "Can not move " . $fromfile . " to " . $tofile;
                    return ['stat'=>500, 'body'=>json_encode($tmp)];
                }
                if (file_exists($fromfile)) unlink($fromfile);
            }
        }
    }
    closedir($handler);
    rmdir($from);
    return ['stat'=>200, 'body'=>'success.'];
}

function WaitFunction() {
    return true;
}

function changeAuthKey() {
    return message("Not need.", 'Change platform Auth token or key', 404);
}
