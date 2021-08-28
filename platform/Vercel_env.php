<?php
// https://vercel.com/docs/api#endpoints/deployments/create-a-new-deployment

function getpath()
{
    $_SERVER['firstacceptlanguage'] = strtolower(splitfirst(splitfirst($_SERVER['HTTP_ACCEPT_LANGUAGE'],';')[0],',')[0]);
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
    if (isset($_SERVER['HTTP_FLY_CLIENT_IP'])) $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_FLY_CLIENT_IP'];
    if ($_SERVER['REQUEST_SCHEME']!='http'&&$_SERVER['REQUEST_SCHEME']!='https') {
        if ($_SERVER['HTTP_X_FORWARDED_PROTO']!='') {
            $tmp = explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0];
            if ($tmp=='http'||$tmp=='https') $_SERVER['REQUEST_SCHEME'] = $tmp;
        }
        if ($_SERVER['HTTP_FLY_FORWARDED_PROTO']!='') $_SERVER['REQUEST_SCHEME'] = $_SERVER['HTTP_FLY_FORWARDED_PROTO'];
    }
    $_SERVER['host'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    $_SERVER['referhost'] = explode('/', $_SERVER['HTTP_REFERER'])[2];
    $_SERVER['base_path'] = "/";
    if (isset($_SERVER['UNENCODED_URL'])) $_SERVER['REQUEST_URI'] = $_SERVER['UNENCODED_URL'];
    $p = strpos($_SERVER['REQUEST_URI'],'?');
    if ($p>0) $path = substr($_SERVER['REQUEST_URI'], 0, $p);
    else $path = $_SERVER['REQUEST_URI'];
    $path = path_format( substr($path, strlen($_SERVER['base_path'])) );
    $_SERVER['DOCUMENT_ROOT'] = '/var/task/user';
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

function getConfig($str, $disktag = '')
{
    if (isInnerEnv($str)) {
        if ($disktag=='') $disktag = $_SERVER['disktag'];
        $tmp = getenv($disktag);
        if (is_array($tmp)) $env = $tmp;
        else $env = json_decode($tmp, true);
        if (isset($env[$str])) {
            if (isBase64Env($str)) return base64y_decode($env[$str]);
            else return $env[$str];
        }
    } else {
        if (isBase64Env($str)) return base64y_decode(getenv($str));
        else return getenv($str);
    }
    return '';
}

function setConfig($arr, $disktag = '')
{
    if ($disktag=='') $disktag = $_SERVER['disktag'];
    $disktags = explode("|", getenv('disktag'));
    if ($disktag!='') {
        $tmp = getenv($disktag);
        if (is_array($tmp)) $diskconfig = $tmp;
        else $diskconfig = json_decode($tmp, true);
    }
    $tmp = [];
    $indisk = 0;
    $operatedisk = 0;
    foreach ($arr as $k => $v) {
        if (isCommonEnv($k)) {
            if (isBase64Env($k)) $tmp[$k] = base64y_encode($v);
            else $tmp[$k] = $v;
        } elseif (isInnerEnv($k)) {
            if (isBase64Env($k)) $diskconfig[$k] = base64y_encode($v);
            else $diskconfig[$k] = $v;
            $indisk = 1;
        } elseif ($k=='disktag_add') {
            array_push($disktags, $v);
            $operatedisk = 1;
        } elseif ($k=='disktag_del') {
            $disktags = array_diff($disktags, [ $v ]);
            $tmp[$v] = '';
            $operatedisk = 1;
        } elseif ($k=='disktag_copy') {
            $newtag = $v . '_' . date("Ymd_His");
            $tagvalue = getenv($v);
            if (is_array($tagvalue)) $tmp[$newtag] = json_encode($tagvalue);
            else $tmp[$newtag] = $tagvalue;
            array_push($disktags, $newtag);
            $operatedisk = 1;
        } elseif ($k=='disktag_rename' || $k=='disktag_newname') {
            if ($arr['disktag_rename']!=$arr['disktag_newname']) $operatedisk = 1;
        } else {
            $tmp[$k] = json_encode($v);
        }
    }
    if ($indisk) {
        $diskconfig = array_filter($diskconfig, 'array_value_isnot_null');
        ksort($diskconfig);
        $tmp[$disktag] = json_encode($diskconfig);
    }
    if ($operatedisk) {
        if (isset($arr['disktag_newname']) && $arr['disktag_newname']!='') {
            $tags = [];
            foreach ($disktags as $tag) {
                if ($tag==$arr['disktag_rename']) array_push($tags, $arr['disktag_newname']);
                else array_push($tags, $tag);
            }
            $tmp['disktag'] = implode('|', $tags);
            $tagvalue = getenv($arr['disktag_rename']);
            if (is_array($tagvalue)) $tmp[$arr['disktag_newname']] = json_encode($tagvalue);
            else $tmp[$arr['disktag_newname']] = $tagvalue;
            $tmp[$arr['disktag_rename']] = null;
        } else {
            $disktags = array_unique($disktags);
            foreach ($disktags as $disktag) if ($disktag!='') $disktag_s .= $disktag . '|';
            if ($disktag_s!='') $tmp['disktag'] = substr($disktag_s, 0, -1);
            else $tmp['disktag'] = null;
        }
    }
    foreach ($tmp as $key => $val) if ($val=='') $tmp[$key]=null;

    //error_log1(json_encode($arr, JSON_PRETTY_PRINT) . ' => tmp：' . json_encode($tmp, JSON_PRETTY_PRINT));
    //echo json_encode($arr, JSON_PRETTY_PRINT) . ' => tmp：' . json_encode($tmp, JSON_PRETTY_PRINT);
    return setVercelConfig($tmp, getConfig('HerokuappId'), getConfig('APIKey'));
}

function install()
{
    global $constStr;
    if ($_GET['install1']) {
        if ($_POST['admin']!='') {
            $tmp['admin'] = $_POST['admin'];
            //$tmp['language'] = $_POST['language'];
            $tmp['timezone'] = $_COOKIE['timezone'];
            $APIKey = $_POST['APIKey'];
            //if ($APIKey=='') {
            //    $APIKey = getConfig('APIKey');
            //}
            $tmp['APIKey'] = $APIKey;

            $token = $APIKey;
            $header["Authorization"] = "Bearer " . $token;
            $header["Content-Type"] = "application/json";
            $aliases = json_decode(curl("GET", "https://api.vercel.com/v3/now/aliases", "", $header)['body'], true);
            $host = splitfirst($_SERVER["host"], "//")[1];
            foreach ($aliases["aliases"] as $key => $aliase) {
                if ($host==$aliase["alias"]) $projectId = $aliase["projectId"];
            }
            $tmp['HerokuappId'] = $projectId;

            $response = json_decode(setVercelConfig($tmp, $projectId, $APIKey), true);
            if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
                return message($html, $title, 400);
            } else {
                /*$html = '<script>
        var status = "' . $response['DplStatus'] . '";
        var expd = new Date();
        expd.setTime(expd.getTime()+1000);
        var expires = "expires="+expd.toGMTString();
        document.cookie=\'language=; path=/; \'+expires;
    </script>';
                return message($html, $title, 201, 1);*/
                $data["dplId"] = $response['DplStatus'];
                return output(json_encode($data), 201);
            }
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
        //if (getConfig('APIKey')=='') 
        $html .= '<br>
        <a href="https://vercel.com/account/tokens" target="_blank">' . getconstStr('Create') . ' token</a><br>
        <label>Token:<input name="APIKey" type="password" placeholder="" value="' . getConfig('APIKey') . '"></label><br>';
        $html .= '<br>
        <label>Set admin password:<input name="admin" type="password" placeholder="' . getconstStr('EnvironmentsDescription')['admin'] . '" size="' . strlen(getconstStr('EnvironmentsDescription')['admin']) . '"></label><br>';
        $html .= '
        <input type="submit" value="'.getconstStr('Submit').'">
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
            t.style.display = "none";
            errordiv.innerHTML = "' . getconstStr('Wait') . '";
            var xhr = new XMLHttpRequest();
            xhr.open("POST", t.action);
            xhr.onload = function(e) {
                if (xhr.status==201) {
                    var res = JSON.parse(xhr.responseText);
                    getStatus(res.dplId, t.APIKey.value);
                } else {
                    t.style.display = "";
                    errordiv.innerHTML = xhr.status + "<br>" + xhr.responseText;
                }
            }
            xhr.send("admin=" + t.admin.value + "&APIKey=" + t.APIKey.value);

            var x = "";
            var min = 0;
            function getStatus(id, VercelToken) {
                x += ".";
                min++;
                var xhr = new XMLHttpRequest();
                var url = "https://api.vercel.com/v11/now/deployments/" + id;
                xhr.open("GET", url);
                xhr.setRequestHeader("Authorization", "Bearer " + VercelToken);
                xhr.onload = function(e) {
                    if (xhr.status==200) {
                        var deployStat = JSON.parse(xhr.responseText).readyState;
                        if (deployStat=="READY") {
                            x = "";
                            min = 0;
                            errordiv.innerHTML = "Deploy done.";
                            location.href = "/";
                        } else {
                            errordiv.innerHTML = deployStat + ", " + min + ".<br>' . getconstStr('Wait') . ' " + x;
                            if (deployStat!=="ERROR") setTimeout(function() { getStatus(id, VercelToken) }, 1000);
                        }
                    } else {
                        t.style.display = "";
                        console.log(xhr.status);
                        console.log(xhr.responseText);
                    }
                }
                xhr.send(null);
            }

            return false;
        }
    </script>';
        $title = getconstStr('SelectLanguage');
        return message($html, $title, 201);
    }

    if (substr($_SERVER["host"], -10)=="vercel.app") {
        $html .= '<a href="?install0">' . getconstStr('ClickInstall') . '</a>, ' . getconstStr('LogintoBind');
        $html .= "<br>Remember: you MUST wait 30-60s after each operate / do some change, that make sure Vercel has done the building<br>" ;
    } else {
        $html.= "Please visit form *.vercel.app";
    }
    $title = 'Install';
    return message($html, $title, 201);
}

// POST /v8/projects/:id/env
function setVercelConfig($envs, $appId, $token)
{
    $url = "https://api.vercel.com/v8/projects/" . $appId . "/env";
    $header["Authorization"] = "Bearer " . $token;
    $header["Content-Type"] = "application/json";
    $response = curl("GET", $url, "", $header);
    $result = json_decode($response['body'], true);
    foreach ($result["envs"] as $key => $value) {
        $existEnvs[$value["key"]] = $value["id"];
    }
    foreach ($envs as $key => $value) {
        $response = null;
        $tmp = null;
        $tmp["type"] = "encrypted";
        $tmp["key"] = $key;
        $tmp["value"] = $value;
        $tmp["target"] = [ "development", "production", "preview" ];
        if (isset($existEnvs[$key])) {
            if ($value) $response = curl("PATCH", $url . "/" . $existEnvs[$key], json_encode($tmp), $header);
            else $response = curl("DELETE", $url . "/" . $existEnvs[$key], "", $header);
        } else {
            if ($value) $response = curl("POST", $url, json_encode($tmp), $header);
        }
        //echo $key . " = " . $value . ", <br>" . $response . json_encode($response, JSON_PRETTY_PRINT) . "<br>";
        if (!!$response && $response['stat']!=200) return $response['body'];
    }
    return VercelUpdate($appId, $token);
}

function VercelUpdate($appId, $token, $sourcePath = "")
{
    $url = "https://api.vercel.com/v12/now/deployments";
    $header["Authorization"] = "Bearer " . $token;
    $header["Content-Type"] = "application/json";
    $data["name"] = "OneManager";
    $data["project"] = $appId;
    $data["target"] = "production";
    $data["routes"][0]["src"] = "/(.*)";
    $data["routes"][0]["dest"] = "/api/index.php";
    $data["functions"]["api/index.php"]["runtime"] = "vercel-php@0.4.0";
    if ($sourcePath=="") $sourcePath = splitlast(splitlast(__DIR__, "/")[0], "/")[0];
    //echo $sourcePath . "<br>";
    getEachFiles($file, $sourcePath);
    $data["files"] = $file;

    //echo json_encode($data, JSON_PRETTY_PRINT) . " ,data<br>";
    $response = curl("POST", $url, json_encode($data), $header);
    //echo json_encode($response, JSON_PRETTY_PRINT) . " ,res<br>";
    $result = json_decode($response["body"], true);
    $result['DplStatus'] = $result['id'];
    return json_encode($result);
}

function getEachFiles(&$file, $base, $path = "")
{
    //if (substr($base, -1)=="/") $base = substr($base, 0, -1);
    //if (substr($path, -1)=="/") $path = substr($path, 0, -1);
    $handler=opendir(path_format($base . "/" . $path));
    while($filename=readdir($handler)) {
        if($filename != '.' && $filename != '..' && $filename != '.git'){
            $fromfile = path_format($base . "/" . $path . "/" . $filename);
        //echo $fromfile . "<br>";
            if(is_dir($fromfile)){// 如果读取的某个对象是文件夹，则递归
                $response = getEachFiles($file, $base, path_format($path . "/" . $filename));
                if (api_error(setConfigResponse($response))) return $response;
            }else{
                $tmp['file'] = path_format($path . "/" . $filename);
                $tmp['data'] = file_get_contents($fromfile);
                $file[] = $tmp;
            }
        }
    }
    closedir($handler);
    
    return json_encode( [ 'response' => 'success' ] );
}

function api_error($response)
{
    return isset($response['error']);
}

function api_error_msg($response)
{
    return $response['error']['code'] . '<br>
' . $response['error']['message'] . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>';
}

function setConfigResponse($response)
{
    return json_decode($response, true);
}

function OnekeyUpate($auth = 'qkqpttgf', $project = 'OneManager-php', $branch = 'master')
{
    $tmppath = '/tmp';

    // 从github下载对应tar.gz，并解压
    $url = 'https://github.com/' . $auth . '/' . $project . '/tarball/' . urlencode($branch) . '/';
    $tarfile = $tmppath . '/github.tar.gz';
    $githubfile = file_get_contents($url);
    if (!$githubfile) return '{"error":{"message":"fail to download from github"}}';
    file_put_contents($tarfile, $githubfile);
        $phar = new PharData($tarfile); // need php5.3, 7, 8
        $phar->extractTo($tmppath, null, true);//路径 要解压的文件 是否覆盖
    unlink($tarfile);

    $outPath = '';
    $tmp = scandir($tmppath);
    $name = $auth . '-' . $project;
    mkdir($tmppath . "/" . $name, 0777);
    foreach ($tmp as $f) {
        if ( substr($f, 0, strlen($name)) == $name) {
            rename($tmppath . '/' . $f, $tmppath . "/" . $name . '/api');
            $outPath = $tmppath . "/" . $name;
            break;
        }
    }
    //echo $outPath . "<br>";
    //error_log1($outPath);
    if ($outPath=='') return '{"error":{"message":"no outpath"}}';

    return VercelUpdate(getConfig('HerokuappId'), getConfig('APIKey'), $outPath);
}

function WaitFunction($deployid) {
    if ($buildId=='1') {
        $tmp['stat'] = 400;
        $tmp['body'] = 'id must provided.';
        return $tmp;
    }
    $header["Authorization"] = "Bearer " . getConfig('APIKey');
    $header["Content-Type"] = "application/json";
    $url = "https://api.vercel.com/v11/now/deployments/" . $deployid;
    $response = curl("GET", $url, "", $header);
    if ($response['stat']==200) {
        $result = json_decode($response['body'], true);
        if ($result['readyState']=="READY") return true;
        if ($result['readyState']=="ERROR") return $response;
        return false;
    } else {
        $response['body'] .= $url;
        return $response;
    }
}
