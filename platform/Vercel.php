<?php
// https://vercel.com/docs/rest-api/endpoints
// https://vercel.com/docs/rest-api/endpoints/deployments#create-a-new-deployment
// https://github.com/vercel-community/php

function getpath() {
    $_SERVER['firstacceptlanguage'] = strtolower(splitfirst(splitfirst($_SERVER['HTTP_ACCEPT_LANGUAGE'], ';')[0], ',')[0]);
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
    if (isset($_SERVER['HTTP_FLY_CLIENT_IP'])) $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_FLY_CLIENT_IP'];
    if (!isset($_SERVER['REQUEST_SCHEME']) || $_SERVER['REQUEST_SCHEME'] != 'http' && $_SERVER['REQUEST_SCHEME'] != 'https') {
        if ($_SERVER['HTTP_X_FORWARDED_PROTO'] != '') {
            $tmp = explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0];
            if ($tmp == 'http' || $tmp == 'https') $_SERVER['REQUEST_SCHEME'] = $tmp;
        }
        if (isset($_SERVER['HTTP_FLY_FORWARDED_PROTO'])) $_SERVER['REQUEST_SCHEME'] = $_SERVER['HTTP_FLY_FORWARDED_PROTO'];
    }
    $_SERVER['host'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    if (isset($_SERVER['HTTP_REFERER'])) $_SERVER['referhost'] = explode('/', $_SERVER['HTTP_REFERER'])[2];
    $_SERVER['base_path'] = "/";
    if (isset($_SERVER['UNENCODED_URL'])) $_SERVER['REQUEST_URI'] = $_SERVER['UNENCODED_URL'];
    $p = strpos($_SERVER['REQUEST_URI'], '?');
    if ($p > 0) $path = substr($_SERVER['REQUEST_URI'], 0, $p);
    else $path = $_SERVER['REQUEST_URI'];
    $path = path_format(substr($path, strlen($_SERVER['base_path'])));
    fetchVercelPHPVersion(getConfig("APIKey"));
    return $path;
}

function getGET() {
    if (!$_POST) {
        if (!!$HTTP_RAW_POST_DATA) {
            $tmpdata = $HTTP_RAW_POST_DATA;
        } else {
            $tmpdata = file_get_contents('php://input');
        }
        if (!!$tmpdata) {
            $postbody = explode("&", $tmpdata);
            foreach ($postbody as $postvalues) {
                $pos = strpos($postvalues, "=");
                $_POST[urldecode(substr($postvalues, 0, $pos))] = urldecode(substr($postvalues, $pos + 1));
            }
        }
    }
    if (isset($_SERVER['UNENCODED_URL'])) $_SERVER['REQUEST_URI'] = $_SERVER['UNENCODED_URL'];
    $p = strpos($_SERVER['REQUEST_URI'], '?');
    if ($p > 0) {
        $getstr = substr($_SERVER['REQUEST_URI'], $p + 1);
        $getstrarr = explode("&", $getstr);
        foreach ($getstrarr as $getvalues) {
            if ($getvalues != '') {
                $keyvalue = splitfirst($getvalues, "=");
                if ($keyvalue[1] != "") $getarry[$keyvalue[0]] = $keyvalue[1];
                else $getarry[$keyvalue[0]] = true;
            }
        }
    }
    if (isset($getarry)) {
        return $getarry;
    } else {
        return [];
    }
}

function getConfig($str, $disktag = '') {
    $projectPath = splitlast(__DIR__, '/')[0];
    $configPath = $projectPath . '/.data/config.php';
    $s = file_get_contents($configPath);
    $configs = '{' . splitlast(splitfirst($s, '{')[1], '}')[0] . '}';
    if ($configs != '') {
        $envs = json_decode($configs, true);
        if (isInnerEnv($str)) {
            if ($disktag == '') $disktag = $_SERVER['disktag'];
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

function setConfig($arr, $disktag = '') {
    if ($disktag == '') $disktag = $_SERVER['disktag'];
    $projectPath = splitlast(__DIR__, '/')[0];
    $configPath = $projectPath . '/.data/config.php';
    $s = file_get_contents($configPath);
    $configs = '{' . splitlast(splitfirst($s, '{')[1], '}')[0] . '}';
    if ($configs != '') $envs = json_decode($configs, true);
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
        } elseif ($k == 'disktag_add') {
            array_push($disktags, $v);
            $operatedisk = 1;
        } elseif ($k == 'disktag_del') {
            $disktags = array_diff($disktags, [$v]);
            $envs[$v] = '';
            $operatedisk = 1;
        } elseif ($k == 'disktag_copy') {
            $newtag = $v . '_' . date("Ymd_His");
            $envs[$newtag] = $envs[$v];
            array_push($disktags, $newtag);
            $operatedisk = 1;
        } elseif ($k == 'disktag_rename' || $k == 'disktag_newname') {
            if ($arr['disktag_rename'] != $arr['disktag_newname']) $operatedisk = 1;
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
        if (isset($arr['disktag_newname']) && $arr['disktag_newname'] != '') {
            $tags = [];
            foreach ($disktags as $tag) {
                if ($tag == $arr['disktag_rename']) array_push($tags, $arr['disktag_newname']);
                else array_push($tags, $tag);
            }
            $envs['disktag'] = implode('|', $tags);
            $envs[$arr['disktag_newname']] = $envs[$arr['disktag_rename']];
            $envs[$arr['disktag_rename']] = '';
        } else {
            $disktags = array_unique($disktags);
            $disktag_s = "";
            foreach ($disktags as $disktag) if ($disktag != '') $disktag_s .= $disktag . '|';
            if ($disktag_s != '') $envs['disktag'] = substr($disktag_s, 0, -1);
            else $envs['disktag'] = '';
        }
    }
    $envs = array_filter($envs, 'array_value_isnot_null');
    //ksort($envs);
    //sortConfig($envs);
    //error_log1(json_encode($arr, JSON_PRETTY_PRINT) . ' => tmp：' . json_encode($envs, JSON_PRETTY_PRINT));
    //echo json_encode($arr, JSON_PRETTY_PRINT) . ' => tmp：' . json_encode($envs, JSON_PRETTY_PRINT);
    $token = getConfig('APIKey');
    if (!$token) {
        return json_encode(["error" => ["message" => 'Error, No Vercel token to operate.<br>Please <a href="?setup=auth">set Vercel token</a>!']], JSON_UNESCAPED_SLASHES);
    }
    return setVercelConfig($envs, $token);
}

function install() {
    global $constStr;
    if ($_GET['install1']) {
        if ($_POST['admin'] != '') {
            $tmp['admin'] = $_POST['admin'];
            //$tmp['language'] = $_POST['language'];
            $tmp['timezone'] = $_COOKIE['timezone'];
            $APIKey = $_POST['APIKey'];
            //if ($APIKey=='') {
            //    $APIKey = getConfig('APIKey');
            //}
            $tmp['APIKey'] = $APIKey;

            $response = json_decode(setVercelConfig($tmp,  $APIKey), true);
            if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
                return message($html, $title, 400);
            } else {
                $html = getconstStr('Success') . '
    <script>
        var status = "' . $response['DplStatus'] . '";
        var i = 0;
        var expd = new Date();
        expd.setTime(expd.getTime()+1000);
        var expires = "expires="+expd.toGMTString();
        document.cookie=\'language=; path=/; \'+expires;
        var uploadList = setInterval(function(){
            if (document.getElementById("dis").style.display=="none") {
                console.log(i++);
            } else {
                clearInterval(uploadList);
                location.href = "' . path_format($_SERVER['base_path'] . '/') . '";
            }
        }, 1000);
    </script>';
                $title = "Success";
                return message($html, $title, 201, 1);
            }
        }
    }
    if ($_GET['install0']) {
        $html = '
    <form action="?install1" method="post" onsubmit="return notnull(this);">
language:<br>';
        foreach ($constStr['languages'] as $key1 => $value1) {
            $html .= '
        <label><input type="radio" name="language" value="' . $key1 . '" ' . ($key1 == $constStr['language'] ? 'checked' : '') . ' onclick="changelanguage(\'' . $key1 . '\')">' . $value1 . '</label><br>';
        }
        $html .= '<br>
        <a href="https://vercel.com/account/tokens" target="_blank">' . getconstStr('Create') . ' token</a><br>
        <label>Token:<input name="APIKey" type="password" placeholder="" value=""></label><br>';
        $html .= '<br>
        <label>Set admin password:<input name="admin" type="password" placeholder="' . getconstStr('EnvironmentsDescription')['admin'] . '" size="' . strlen(getconstStr('EnvironmentsDescription')['admin']) . '"></label><br>';
        $html .= '
        <input type="submit" value="' . getconstStr('Submit') . '">
    </form>
    <div id="showerror"></div>
    <script>
        var nowtime= new Date();
        var timezone = 0-nowtime.getTimezoneOffset()/60;
        var expd = new Date();
        expd.setTime(expd.getTime()+(2*60*60*1000));
        var expires = "expires="+expd.toGMTString();
        document.cookie="timezone="+timezone+"; path=/; "+expires;
        var errordiv = document.getElementById("showerror");
        function changelanguage(str)
        {
            var expd = new Date();
            expd.setTime(expd.getTime()+(2*60*60*1000));
            var expires = "expires="+expd.toGMTString();
            document.cookie=\'language=\'+str+\'; path=/; \'+expires;
            location.href = location.href;
        }
        function notnull(t)
        {
            if (t.admin.value==\'\') {
                alert(\'input admin\');
                return false;
            }
            if (t.APIKey.value==\'\') {
                alert(\'input Token\');
                return false;
            }
            return true;
        }
    </script>';
        $title = getconstStr('SelectLanguage');
        return message($html, $title, 201);
    }

    //if (substr($_SERVER["host"], -10)=="vercel.app") {
    $html = '<a href="?install0">' . getconstStr('ClickInstall') . '</a>, ' . getconstStr('LogintoBind');
    $html .= "<br>Remember: you MUST wait 30-60s after each operate / do some change, that make sure Vercel has done the building<br>";
    //} else {
    //    $html.= "Please visit form *.vercel.app";
    //}
    $title = 'Install';
    return message($html, $title, 201);
}

function copyFolder($from, $to) {
    if (substr($from, -1) == '/') $from = substr($from, 0, -1);
    if (substr($to, -1) == '/') $to = substr($to, 0, -1);
    if (!file_exists($to)) mkdir($to, 0777, 1);
    $handler = opendir($from);
    while ($filename = readdir($handler)) {
        if ($filename != '.' && $filename != '..') {
            $fromfile = $from . '/' . $filename;
            $tofile = $to . '/' . $filename;
            if (is_dir($fromfile)) { // 如果读取的某个对象是文件夹，则递归
                copyFolder($fromfile, $tofile);
            } else {
                copy($fromfile, $tofile);
            }
        }
    }
    closedir($handler);
    return 1;
}

function setVercelConfig($envs, $token) {
    sortConfig($envs);
    $outPath = '/tmp/code/';
    $outPath_Api = $outPath . 'api/';
    $coderoot = __DIR__;
    $coderoot = splitlast($coderoot, '/')[0] . '/';
    //echo $outPath_Api . '<br>' . $coderoot . '<br>';
    copyFolder($coderoot, $outPath_Api);
    $prestr = '<?php $configs = \'' . PHP_EOL;
    $aftstr = PHP_EOL . '\';';
    file_put_contents($outPath_Api . '.data/config.php', $prestr . json_encode($envs, JSON_PRETTY_PRINT) . $aftstr);

    return VercelUpdate($token, $outPath);
}

function fetchVercelPHPVersion($token) {
    //if (!($vercelPHPversion = getcache("PHPRuntime")) || !($nodeVersion = getcache("NodeRuntime"))) {
    if (!($vercelPHPversion = getcache("PHPRuntime"))) {
        $url = "https://raw.githubusercontent.com/vercel-community/php/master/package.json";
        $response = curl("GET", $url);
        if ($response['stat'] == 200) {
            $res = json_decode($response['body'], true);
            if ($res) {
                $phpVersion = $res['version'];
                //$nodeVersion = $res['devDependencies']['@types/node'];
                //$nodeVersion = splitfirst($nodeVersion, ".")[0] . ".x";
                savecache("PHPRuntime", $phpVersion);
                //savecache("NodeRuntime", $nodeVersion);
                $vercelPHPversion = $phpVersion;
            }
        }
    }
    /*if ($token) {
        $appId = getProjectInfofromDeployIDInENV($token)['projectId'];
        if ($appId) {
            if (!($vercelNodeVersion = getcache("VercelNodeRuntime"))) {
                $vercelNodeVersion = fetchVercelNodeVersion($appId, $token);
                if ($vercelNodeVersion != "") savecache("VercelNodeRuntime", $vercelNodeVersion);
            }
            //echo "<br>phpNode:" . $nodeVersion . ", vercelNode:" . $vercelNodeVersion;
            if ($nodeVersion != "" && $nodeVersion != $vercelNodeVersion) {
                setNodeVersion($nodeVersion, $appId, $token);
            }
        }
    }*/
    return $vercelPHPversion;
}
function fetchVercelNodeVersion($appId, $token) {
    $url = "https://api.vercel.com/v8/projects/" . $appId;
    $header["Authorization"] = "Bearer " . $token;
    $response = curl("GET", $url, "", $header);
    //echo $url . "<br>\n";
    //var_dump($response);
    if ($response['stat'] == 200) {
        $result = json_decode($response['body'], true);
        return $result['nodeVersion'];
    } else {
        return "";
    }
}
function setNodeVersion($ver, $appId, $token) {
    $url = "https://api.vercel.com/v9/projects/" . $appId;
    $header["Authorization"] = "Bearer " . $token;
    $header["Content-Type"] = "application/json";
    $data["nodeVersion"] = $ver;
    //echo "<br>Set node " . $ver;
    $response = curl("PATCH", $url, json_encode($data), $header);
}

function VercelUpdate($token, $sourcePath = "") {
    $project = getProjectInfofromDeployIDInENV($token);
    $appId = $project['projectId'];
    $name = $project['name'];
    if (!$appId) return json_encode(["error" => ["message" => 'Error in get projectID.']], JSON_UNESCAPED_SLASHES);
    if (checkBuilding($appId, $token)) return json_encode(["error" => ["message" => 'Another building is in progress.']], JSON_UNESCAPED_SLASHES);
    $vercelPHPversion = fetchVercelPHPVersion($token);
    $url = "https://api.vercel.com/v13/deployments";
    $header["Authorization"] = "Bearer " . $token;
    $header["Content-Type"] = "application/json";
    $data["functions"]["api/index.php"]["runtime"] = "vercel-php@" . $vercelPHPversion;
    $data["routes"][0]["src"] = "/(.*)";
    $data["routes"][0]["dest"] = "/api/index.php";
    $verceljson = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $data["name"] = $name;
    $data["project"] = $appId;
    $data["target"] = "production";
    //if (getcache("NodeRuntime")) {
    //    $data["projectSettings"]["nodeVersion"] = getcache("NodeRuntime");
    //    $data["projectSettings"]["framework"] = null;
    //}
    if ($sourcePath == "") $sourcePath = splitlast(splitlast(__DIR__, "/")[0], "/")[0];
    //echo $sourcePath . "<br>";
    getEachFiles($file, $sourcePath);
    $tmp['file'] = "vercel.json";
    $tmp['data'] = $verceljson;
    $file[] = $tmp;
    $data["files"] = $file;

    //echo json_encode($data, JSON_PRETTY_PRINT) . " ,data<br>";
    $response = curl("POST", $url, json_encode($data), $header);
    //echo json_encode($response, JSON_PRETTY_PRINT) . " ,res<br>";
    $result = json_decode($response["body"], true);
    $result['DplStatus'] = $result['id'];
    return json_encode($result);
}

function checkBuilding($projectId, $token) {
    $r = 0;
    $url = "https://api.vercel.com/v6/deployments/?projectId=" . $projectId;
    $header["Authorization"] = "Bearer " . $token;
    $header["Content-Type"] = "application/json";
    $response = curl("GET", $url, '', $header);
    //echo json_encode($response, JSON_PRETTY_PRINT) . " ,res<br>";
    $result = json_decode($response["body"], true);
    foreach ($result['deployments'] as $deployment) {
        if ($deployment['state'] !== "READY" && $deployment['state'] !== "ERROR") $r++;
    }
    return $r;
    //if ($r===0) return true;
    //else return false;
}

function getEachFiles(&$file, $base, $path = "") {
    //if (substr($base, -1)=="/") $base = substr($base, 0, -1);
    //if (substr($path, -1)=="/") $path = substr($path, 0, -1);
    $handler = opendir(path_format($base . "/" . $path));
    while ($filename = readdir($handler)) {
        if ($filename != '.' && $filename != '..' && $filename != '.git') {
            $fromfile = path_format($base . "/" . $path . "/" . $filename);
            //echo $fromfile . "<br>";
            if (is_dir($fromfile)) { // 如果读取的某个对象是文件夹，则递归
                $response = getEachFiles($file, $base, path_format($path . "/" . $filename));
                if (api_error(setConfigResponse($response))) return $response;
            } else {
                $tmp['file'] = substr(path_format($path . "/" . $filename), 1);
                $tmp['data'] = file_get_contents($fromfile);
                $file[] = $tmp;
            }
        }
    }
    closedir($handler);

    return json_encode(['response' => 'success']);
}

function api_error($response) {
    return isset($response['error']);
}

function api_error_msg($response) {
    return $response['error']['code'] . '<br>
' . $response['error']['message'] . '<br>
<button onclick="location.href = location.href;">' . getconstStr('Refresh') . '</button>';
}

function setConfigResponse($response) {
    return json_decode($response, true);
}

function OnekeyUpate($GitSource = 'Github', $auth = 'qkqpttgf', $project = 'OneManager-php', $branch = 'master') {
    $tmppath = '/tmp';

    if ($GitSource == 'Github') {
        // 从github下载对应zip，并解压
        $url = 'https://codeload.github.com/' . $auth . '/' . $project . '/zip/refs/heads/' . urlencode($branch);
    } elseif ($GitSource == 'Gitee') {
        $url = 'https://gitee.com/' . $auth . '/' . $project . '/repository/archive/' . urlencode($branch) . '.zip';
    } else return json_encode(['error' => ['code' => 'Git Source input Error!']]);

    $tarfile = $tmppath . '/github.zip';
    $context_options = array(
        'http' => array(
            'header' => "User-Agent: curl/7.83.1",
        )
    );
    $context = stream_context_create($context_options);
    file_put_contents($tarfile, file_get_contents($url, false, $context));
    $phar = new PharData($tarfile);
    $html = $phar->extractTo($tmppath, null, true); //路径 要解压的文件 是否覆盖
    unlink($tarfile);

    // 获取解压出的目录名
    $outPath = findIndexPath($tmppath);

    if ($outPath == '') return json_encode(["error" => ["message" => 'no outpath.']], JSON_UNESCAPED_SLASHES);
    $name = $project . 'CODE';
    mkdir($tmppath . "/" . $name, 0777, 1);
    rename($outPath, $tmppath . "/" . $name . '/api');
    $outPath = $tmppath . "/" . $name;
    //echo $outPath . "<br>";
    //error_log1($outPath);

    // put in config
    $coderoot = __DIR__;
    $coderoot = splitlast($coderoot, '/')[0] . '/';
    copy($coderoot . '.data/config.php', $outPath . '/api/.data/config.php');

    return VercelUpdate(getConfig('APIKey'), $outPath);
}

function getProjectInfofromDeployIDInENV($token) {
    if ($token == '') {
        error_log1("Not provide token when get projectID");
        return [];
    }
    $header["Authorization"] = "Bearer " . $token;
    $header["Content-Type"] = "application/json";
    $url = "https://api.vercel.com/v13/deployments/" . $_ENV["VERCEL_DEPLOYMENT_ID"];
    $response = curl("GET", $url, "", $header);
    if ($response['stat'] == 200) {
        return json_decode($response['body'], true);
    }
    error_log1($response['body']);
    return [];
}
function WaitFunction($deployid = '') {
    if ($deployid == '1') {
        $tmp['stat'] = 400;
        $tmp['body'] = 'deployID must provided.';
        return $tmp;
    }
    $token = getConfig('APIKey');
    if ($token != '') {
        $header["Authorization"] = "Bearer " . $token;
        $header["Content-Type"] = "application/json";
        $url = "https://api.vercel.com/v13/deployments/" . $deployid;
        $response = curl("GET", $url, "", $header);
        if ($response['stat'] == 200) {
            $result = json_decode($response['body'], true);
            if ($result['readyState'] == "READY") return true;
            if ($result['readyState'] == "ERROR") return $response;
            return false;
        } else {
            $response['body'] .= $url;
            return $response;
        }
    } else {
        return false;
    }
}

function changeAuthKey() {
    if ($_POST['APIKey'] != '') {
        $APIKey = $_POST['APIKey'];
        $tmp['APIKey'] = $APIKey;
        $response = setConfigResponse(setVercelConfig($tmp,  $APIKey));
        if (api_error($response)) {
            $html = api_error_msg($response);
            $title = 'Error';
            return message($html, $title, 400);
        } else {
            $html = getconstStr('Success') . '
    <script>
        var status = "' . $response['DplStatus'] . '";
        var i = 0;
        var uploadList = setInterval(function(){
            if (document.getElementById("dis").style.display=="none") {
                console.log(i++);
            } else {
                clearInterval(uploadList);
                location.href = "' . path_format($_SERVER['base_path'] . '/') . '";
            }
        }, 1000);
    </script>';
            $title = "Success";
            return message($html, $title, 201, 1);
        }
    }
    $html = '
    <form action="" method="post" onsubmit="return notnull(this);">
        <a href="https://vercel.com/account/tokens" target="_blank">' . getconstStr('Create') . ' token</a><br>
        <label>Token:<input name="APIKey" type="password" placeholder="" value=""></label><br>
        <input type="submit" value="' . getconstStr('Submit') . '">
    </form>
    <script>
        function notnull(t)
        {
            if (t.APIKey.value==\'\') {
                alert(\'Input Token\');
                return false;
            }
            return true;
        }
    </script>';
    return message($html, 'Change platform Auth token or key', 200);
}

function smallfileupload($drive, $path) {
    if ($_FILES['file1']['error']) return output($_FILES['file1']['error'], 400);
    if ($_FILES['file1']['size'] > 4 * 1024 * 1024) return output('File too large', 400);
    return $drive->smallfileupload($path, $_FILES['file1']);
}
