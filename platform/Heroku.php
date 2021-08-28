<?php
// https://devcenter.heroku.com/articles/platform-api-reference#build-create

function getpath()
{
    if (getConfig('function_name') && getConfig('APIKey')) {
        $APIKey = getConfig('APIKey');
        $res = HerokuAPI('GET', 'https://api.heroku.com/apps/' . getConfig('function_name'), '', $APIKey);
        $response = json_decode($res['body'], true);
        if (isset($response['build_stack'])) {
            $tmp['HerokuappId'] = $response['id'];
            $tmp['function_name'] = null;
        } else {
            error_log1('Something error' . 'Get Heroku app id: ' . json_encode($res, JSON_PRETTY_PRINT));
            //return message('Get Heroku app id: ' . json_encode($res, JSON_PRETTY_PRINT), 'Something error', 500);
        }
        $response = json_decode(setHerokuConfig($tmp, $tmp['HerokuappId'], $APIKey)['body'], true);
        $title = 'Change function_name to HerokuappId';
        if (api_error($response)) {
            $html = api_error_msg($response);
            $stat = 500;
            error_log1('Change function_name to HerokuappId' . $html);
        } else {
            $html = getconstStr('Wait') . ' 5s, jump to index.
            <meta http-equiv="refresh" content="5;URL=/">';
            $stat = 201;
        }
        //return message($html, $title, $stat);
    }
    $_SERVER['firstacceptlanguage'] = strtolower(splitfirst(splitfirst($_SERVER['HTTP_ACCEPT_LANGUAGE'],';')[0],',')[0]);
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
    $_SERVER['REQUEST_SCHEME'] = $_SERVER['HTTP_X_FORWARDED_PROTO'];
    $_SERVER['host'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    $_SERVER['referhost'] = explode('/', $_SERVER['HTTP_REFERER'])[2];
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
    //error_log1('POST：' . json_encode($_POST));
    if (!$_POST) {
        if (!!$HTTP_RAW_POST_DATA) {
            $tmpdata = $HTTP_RAW_POST_DATA;
            //error_log1('RAW：' . $tmpdata);
        } else {
            $tmpdata = file_get_contents('php://input');
            //error_log1('PHPINPUT：' . $tmpdata);
        }
        if (!!$tmpdata) {
            $postbody = explode("&", $tmpdata);
            foreach ($postbody as $postvalues) {
                $pos = strpos($postvalues,"=");
                $_POST[urldecode(substr($postvalues,0,$pos))]=urldecode(substr($postvalues,$pos+1));
            }
            //error_log1('POSTformPHPINPUT：' . json_encode($_POST));
        }
    }
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
        $env = json_decode(getenv($disktag), true);
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
    $disktags = explode("|",getConfig('disktag'));
    if ($disktag!='') $diskconfig = json_decode(getenv($disktag), true);
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
            $tmp[$newtag] = getConfig($v);
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
            $tmp[$arr['disktag_newname']] = getConfig($arr['disktag_rename']);
            $tmp[$arr['disktag_rename']] = null;
        } else {
            $disktags = array_unique($disktags);
            foreach ($disktags as $disktag) if ($disktag!='') $disktag_s .= $disktag . '|';
            if ($disktag_s!='') $tmp['disktag'] = substr($disktag_s, 0, -1);
            else $tmp['disktag'] = null;
        }
    }
    foreach ($tmp as $key => $val) if ($val=='') $tmp[$key]=null;

    return setHerokuConfig($tmp, getConfig('HerokuappId'), getConfig('APIKey'));
    error_log1(json_encode($arr, JSON_PRETTY_PRINT) . ' => tmp：' . json_encode($tmp, JSON_PRETTY_PRINT));
}

function install()
{
    global $constStr;
    if ($_GET['install1']) {
        if ($_POST['admin']!='') {
            $tmp['admin'] = $_POST['admin'];
            //$tmp['language'] = $_POST['language'];
            $tmp['timezone'] = $_COOKIE['timezone'];
            $APIKey = getConfig('APIKey');
            if ($APIKey=='') {
                $APIKey = $_POST['APIKey'];
                $tmp['APIKey'] = $APIKey;
            }
            $HerokuappId = getConfig('HerokuappId');
            if ($HerokuappId=='') {
                $function_name = getConfig('function_name');
                if ($function_name=='') {
                    $tmp1 = substr($_SERVER['HTTP_HOST'], 0, strrpos($_SERVER['HTTP_HOST'], '.'));
                    $maindomain = substr($tmp1, strrpos($tmp1, '.')+1);
                    if ($maindomain=='herokuapp') $function_name = substr($tmp1, 0, strrpos($tmp1, '.'));
                    else return message('Please visit from xxxx.herokuapp.com', '', 500);
                    $res = HerokuAPI('GET', 'https://api.heroku.com/apps/' . $function_name, '', $APIKey);
                    $response = json_decode($res['body'], true);
                    if (isset($response['build_stack'])) {
                        $HerokuappId = $response['id'];
                    } else {
                        return message('Get Heroku app id: ' . json_encode($res, JSON_PRETTY_PRINT), 'Something error', 500);
                    }
                }
            }
            $tmp['HerokuappId'] = $HerokuappId;
            $response = json_decode(setHerokuConfig($tmp, $HerokuappId, $APIKey)['body'], true);
            if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
            } else {
                return output('Jump
    <script>
        var expd = new Date();
        expd.setTime(expd.getTime()+1000);
        var expires = "expires="+expd.toGMTString();
        document.cookie=\'language=; path=/; \'+expires;
    </script>
    <meta http-equiv="refresh" content="3;URL=' . path_format($_SERVER['base_path'] . '/') . '">', 302);
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
        if (getConfig('APIKey')=='') $html .= '
        <a href="https://dashboard.heroku.com/account" target="_blank">'.getconstStr('Create').' API Key</a><br>
        <label>API Key:<input name="APIKey" type="text" placeholder="" size=""></label><br>';
        $html .= '
        <label>Set admin password:<input name="admin" type="password" placeholder="' . getconstStr('EnvironmentsDescription')['admin'] . '" size="' . strlen(getconstStr('EnvironmentsDescription')['admin']) . '"></label><br>';
        $html .= '
        <input type="submit" value="'.getconstStr('Submit').'">
    </form>
    <script>
        var nowtime= new Date();
        var timezone = 0-nowtime.getTimezoneOffset()/60;
        var expd = new Date();
        expd.setTime(expd.getTime()+(2*60*60*1000));
        var expires = "expires="+expd.toGMTString();
        document.cookie="timezone="+timezone+"; path=/; "+expires;
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
            }';
        if (getConfig('APIKey')=='') $html .= '
            if (t.APIKey.value==\'\') {
                alert(\'input API Key\');
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
    $title = 'Install';
    return message($html, $title, 201);
}

function HerokuAPI($method, $url, $data = '', $apikey)
{
    if ($method=='PATCH'||$method=='POST') {
        $headers['Content-Type'] = 'application/json';
    } 
    $headers['Authorization'] = 'Bearer ' . $apikey;
    $headers['Accept'] = 'application/vnd.heroku+json; version=3';
    //if (!isset($headers['Accept'])) $headers['Accept'] = '*/*';
    //if (!isset($headers['Referer'])) $headers['Referer'] = $url;
    $sendHeaders = array();
    foreach ($headers as $headerName => $headerVal) {
        $sendHeaders[] = $headerName . ': ' . $headerVal;
    }
    error_log1($method . $url . $data . $apikey);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,$method);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
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
    error_log1($response['stat'].'
'.$response['body'].'
');
    return $response;
}

function getHerokuConfig($HerokuappId, $apikey)
{
    return HerokuAPI('GET', 'https://api.heroku.com/apps/' . $HerokuappId . '/config-vars', '', $apikey);
}

function setHerokuConfig($env, $HerokuappId, $apikey)
{
    $data = json_encode($env);
    if (substr($data, 0, 1)=='{') return HerokuAPI('PATCH', 'https://api.heroku.com/apps/' . $HerokuappId . '/config-vars', $data, $apikey);
}

function updateHerokuapp($HerokuappId, $apikey, $source)
{
    $tmp['source_blob']['url'] = $source;
    $data = json_encode($tmp);
    $response = HerokuAPI('POST', 'https://api.heroku.com/apps/' . $HerokuappId . '/builds', $data, $apikey);
    $result = json_decode( $response['body'], true );
    $result['DplStatus'] = $result['id'];
    $response['body'] = json_encode($result);
    return $response;
}

function api_error($response)
{
    return isset($response['id'])&&isset($response['message']);
}

function api_error_msg($response)
{
    return $response['id'] . '<br>
' . $response['message'] . '<br><br>
function_name:' . $_SERVER['function_name'] . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>';
}

function OnekeyUpate($auth = 'qkqpttgf', $project = 'OneManager-php', $branch = 'master')
{
    //'https://github.com/qkqpttgf/OneManager-php/tarball/master/';
    $source = 'https://github.com/' . $auth . '/' . $project . '/tarball/' . urlencode($branch) . '/';
    return updateHerokuapp(getConfig('HerokuappId'), getConfig('APIKey'), $source);
}

function setConfigResponse($response)
{
    return json_decode( $response['body'], true );
}

function WaitFunction($buildId = '') {
    // GET /apps/{app_id_or_name}/builds/{build_id}
    if ($buildId=='1') return true;
    $response = HerokuAPI('GET', 'https://api.heroku.com/apps/' . getConfig('HerokuappId') . '/builds/' . $buildId, '', getConfig('APIKey'));
    if ($response['stat']==200) {
        $result = json_decode($response['body'], true);
        if ($result['status']=="succeeded") return true;
        else return false;
    } else {
        $response['body'] .= $url;
        return $response;
    }
}
