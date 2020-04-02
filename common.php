<?php

$Base64Env = [
    //'APIKey', // used in heroku.
    //'Region', // used in SCF.
    //'SecretId', // used in SCF.
    //'SecretKey', // used in SCF.
    //'admin',
    //'adminloginpage',
    'background',
    'diskname',
    //'disktag',
    //'downloadencrypt',
    //'function_name', // used in heroku.
    //'hideFunctionalityFile',
    //'language',
    //'passfile',
    'sitename',
    //'theme',
    //'Drive_ver',
    //'Drive_custom',
    //'client_id',
    'client_secret',
    'domain_path',
    'guestup_path',
    //'usesharepoint',
    'sharepointname',
    'shareurl',
    'sharecookie',
    'shareapiurl',
    //'siteid',
    'public_path',
    //'refresh_token',
    //'token_expires',
];

$CommonEnv = [
    'APIKey', // used in heroku.
    'Region', // used in SCF.
    'SecretId', // used in SCF.
    'SecretKey', // used in SCF.
    'admin',
    'adminloginpage',
    'background',
    'disktag',
    'function_name', // used in heroku.
    'hideFunctionalityFile',
    //'language',
    'passfile',
    'sitename',
    'theme',
];

$ShowedCommonEnv = [
    //'APIKey', // used in heroku.
    //'Region', // used in SCF.
    //'SecretId', // used in SCF.
    //'SecretKey', // used in SCF.
    //'admin',
    'adminloginpage',
    'background',
    //'disktag',
    //'function_name', // used in heroku.
    'hideFunctionalityFile',
    //'language',
    'passfile',
    'sitename',
    'theme',
];

$InnerEnv = [
    'Drive_ver',
    'Drive_custom',
    'client_id',
    'client_secret',
    'diskname',
    'domain_path',
    'downloadencrypt',
    'guestup_path',
    'usesharepoint',
    'sharepointname',
    'siteid',
    'shareurl',
    'sharecookie',
    'shareapiurl',
    'public_path',
    'refresh_token',
    'token_expires',
];

$ShowedInnerEnv = [
    //'Drive_ver',
    //'Drive_custom',
    //'client_id',
    //'client_secret',
    'diskname',
    'domain_path',
    'downloadencrypt',
    'guestup_path',
    //'usesharepoint',
    //'sharepointname',
    //'siteid',
    //'shareurl',
    //'sharecookie',
    //'shareapiurl',
    'public_path',
    //'refresh_token',
    //'token_expires',
];

function main($path)
{
    global $exts;
    global $constStr;

    if (in_array($_SERVER['firstacceptlanguage'], array_keys($constStr['languages']))) $constStr['language'] = $_SERVER['firstacceptlanguage'];
    if (isset($_COOKIE['language'])&&$_COOKIE['language']!='') $constStr['language'] = $_COOKIE['language'];
    //if (!$constStr['language']) $constStr['language'] = getConfig('language');
    /*echo 'firstacceptlanguage:'.$_SERVER['firstacceptlanguage'].'
    '.'lan:'.$constStr['language'];*/
    if ($constStr['language']=='') $constStr['language'] = 'en-us';
    $_SERVER['language'] = $constStr['language'];
    $_SERVER['PHP_SELF'] = path_format($_SERVER['base_path'] . $path);

    if (getConfig('admin')=='') return install();
    if (getConfig('adminloginpage')=='') {
        $adminloginpage = 'admin';
    } else {
        $adminloginpage = getConfig('adminloginpage');
    }
    if (isset($_GET[$adminloginpage])) {
        if (isset($_GET['preview'])) {
            $url = $_SERVER['PHP_SELF'] . '?preview';
        } else {
            $url = path_format($_SERVER['PHP_SELF'] . '/');
        }
        if (getConfig('admin')!='') {
            if ($_POST['password1']==getConfig('admin')) {
                return adminform('admin',md5($_POST['password1']),$url);
            } else return adminform();
        } else {
            return output('', 302, [ 'Location' => $url ]);
        }
    }
    if (getConfig('admin')!='')
        if ( (isset($_COOKIE['admin'])&&$_COOKIE['admin']==md5(getConfig('admin'))) || (isset($_POST['password1'])&&$_POST['password1']==getConfig('admin')) ) {
            $_SERVER['admin']=1;
            $_SERVER['needUpdate'] = needUpdate();
        } else {
            $_SERVER['admin']=0;
        }
    if (isset($_GET['setup']))
        if ($_SERVER['admin']) {
            // setup Environments. 设置，对环境变量操作
            return EnvOpt($_SERVER['needUpdate']);
        } else {
            $url = path_format($_SERVER['PHP_SELF'] . '/');
            return output('<script>alert(\''.getconstStr('SetSecretsFirst').'\');</script>', 302, [ 'Location' => $url ]);
        }

    $_SERVER['base_disk_path'] = $_SERVER['base_path'];
    $disktags = explode("|",getConfig('disktag'));
//    echo 'count$disk:'.count($disktags);
    if (count($disktags)>1) {
        if ($path=='/'||$path=='') return output('', 302, [ 'Location' => path_format($_SERVER['base_path'].'/'.$disktags[0].'/') ]);
        $_SERVER['disktag'] = $path;
        $pos = strpos($path, '/');
        if ($pos>1) $_SERVER['disktag'] = substr($path, 0, $pos);
        if (!in_array($_SERVER['disktag'], $disktags)) return message('<meta http-equiv="refresh" content="2;URL='.$_SERVER['base_path'].'">Please visit from <a href="'.$_SERVER['base_path'].'">Home Page</a>.', 'Error', 404);
        $path = substr($path, strlen('/'.$_SERVER['disktag']));
        if ($_SERVER['disktag']!='') $_SERVER['base_disk_path'] = path_format($_SERVER['base_disk_path']. '/' . $_SERVER['disktag'] . '/');
    } else $_SERVER['disktag'] = $disktags[0];
//    echo 'main.disktag:'.$_SERVER['disktag'].'，path:'.$path.'
//';
    $_SERVER['list_path'] = getListpath($_SERVER['HTTP_HOST']);
    if ($_SERVER['list_path']=='') $_SERVER['list_path'] = '/';
    $_SERVER['is_guestup_path'] = is_guestup_path($path);
    $_SERVER['ajax']=0;
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) if ($_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest') $_SERVER['ajax']=1;

    config_oauth();
    if ($_SERVER['admin']) if (isset($_GET['AddDisk'])||isset($_GET['authorization_code'])) return get_refresh_token();
    $refresh_token = getConfig('refresh_token');
    //if (!$refresh_token) return get_refresh_token();
    if (!$refresh_token) {
        return render_list();
    } else {
        if (!($_SERVER['access_token'] = getcache('access_token'))) {
            get_access_token($refresh_token);
        }

        if ($_SERVER['ajax']) {
            if ($_GET['action']=='del_upload_cache'&&substr($_GET['filename'],-4)=='.tmp') {
                // del '.tmp' without login. 无需登录即可删除.tmp后缀文件
                error_log('del.tmp:GET,'.json_encode($_GET,JSON_PRETTY_PRINT));
                $tmp = MSAPI('DELETE',path_format(path_format($_SERVER['list_path'] . path_format($path)) . '/' . spurlencode($_GET['filename']) ),'',$_SERVER['access_token']);
                $path1 = path_format($_SERVER['list_path'] . path_format($path));
                if ($path1!='/'&&substr($path1,-1)=='/') $path1=substr($path1,0,-1);
                savecache('path_' . $path1, json_decode('{}',true), 1);
                return output($tmp['body'],$tmp['stat']);
            }
            if ($_GET['action']=='uploaded_rename') {
                // rename .scfupload file without login.
                // 无需登录即可重命名.scfupload后缀文件，filemd5为用户提交，可被构造，问题不大，以后处理
                $oldname = spurlencode($_GET['filename']);
                $pos = strrpos($oldname, '.');
                if ($pos>0) $ext = strtolower(substr($oldname, $pos));
                $oldname = path_format(path_format($_SERVER['list_path'] . path_format($path)) . '/' . $oldname . '.scfupload' );
                $data = '{"name":"' . $_GET['filemd5'] . $ext . '"}';
                //echo $oldname .'<br>'. $data;
                $tmp = MSAPI('PATCH',$oldname,$data,$_SERVER['access_token']);
                if ($tmp['stat']==409) {
                    MSAPI('DELETE',$oldname,'',$_SERVER['access_token']);
                    $tmpbody = json_decode($tmp['body'], true);
                    $tmpbody['name'] = $_GET['filemd5'] . $ext;
                    $tmp['body'] = json_encode($tmpbody);
                }
                $path1 = path_format($_SERVER['list_path'] . path_format($path));
                if ($path1!='/'&&substr($path1,-1)=='/') $path1=substr($path1,0,-1);
                savecache('path_' . $path1, json_decode('{}',true), 1);
                return output($tmp['body'],$tmp['stat']);
            }
            if ($_GET['action']=='upbigfile') return bigfileupload($path);
        }
        if ($_SERVER['admin']) {
            $tmp = adminoperate($path);
            if ($tmp['statusCode'] > 0) {
                $path1 = path_format($_SERVER['list_path'] . path_format($path));
                if ($path1!='/'&&substr($path1,-1)=='/') $path1=substr($path1,0,-1);
                savecache('path_' . $path1, json_decode('{}',true), 1);
                return $tmp;
            }
        } else {
            if ($_SERVER['ajax']) return output(getconstStr('RefreshtoLogin'),401);
        }
        $_SERVER['ishidden'] = passhidden($path);
        if (isset($_GET['thumbnails'])) {
            if ($_SERVER['ishidden']<4) {
                if (in_array(strtolower(substr($path, strrpos($path, '.') + 1)), $exts['img'])) {
                    return get_thumbnails_url($path);
                } else return output(json_encode($exts['img']),400);
            } else return output('',401);
        }

        $files = list_files($path);
        //echo json_encode(array_keys($files['children']), JSON_PRETTY_PRINT);
        if (isset($_GET['random'])&&$_GET['random']!=='') {
            if ($_SERVER['ishidden']<4) {
                $tmp = [];
                foreach (array_keys($files['children']) as $filename) {
                    if (strtolower(splitlast($filename,'.')[1])==strtolower($_GET['random'])) $tmp[$filename] = $files['children'][$filename][$_SERVER['DownurlStrName']];
                }
                $tmp = array_values($tmp);
                if (count($tmp)>0) {
            if (isset($_GET['url'])) return output($tmp[rand(0,count($tmp)-1)], 200);
            return output('', 302, [ 'Location' => $tmp[rand(0,count($tmp)-1)] ]);
                } else return output('',404);
            } else return output('',401);
        }
        if (isset($files['file']) && !isset($_GET['preview'])) {
            // is file && not preview mode
            if ( $_SERVER['ishidden']<4 || (!!getConfig('downloadencrypt')&&$files['name']!=getConfig('passfile')) ) return output('', 302, [ 'Location' => $files[$_SERVER['DownurlStrName']] ]);
        }
        if ( isset($files['folder']) || isset($files['file']) ) {
            return render_list($path, $files);
        } else {
            if (!isset($files['error'])) {
                $files['error']['message'] = json_encode($files, JSON_PRETTY_PRINT);
                $files['error']['code'] = 'unknownError';
                $files['error']['stat'] = 500;
            }
            return message('<a href="'.$_SERVER['base_path'].'">'.getconstStr('Back').getconstStr('Home').'</a><div style="margin:8px;"><pre>' . $files['error']['message'] . '</pre></div><a href="javascript:history.back(-1)">'.getconstStr('Back').'</a>', $files['error']['code'], $files['error']['stat']);
        }
    }
}

function get_access_token($refresh_token)
{
    if (getConfig('Drive_ver')=='shareurl') {
        $shareurl = getConfig('shareurl');
        $tmp1 = splitlast($shareurl, '/')[0];
        $account = splitlast($tmp1, '/')[1];
        $tmp1 = splitlast($shareurl, ':')[0];
        $domain = splitlast($tmp1, '/')[0];
        if (!isset($_SERVER['sharecookie'])||$_SERVER['sharecookie']=='') $_SERVER['sharecookie'] = curl_request($shareurl,false, [],1)['returnhead']['Set-Cookie'];
        $response = curl_request(
            $domain . "/personal/" . $account . "/_api/web/GetListUsingPath(DecodedUrl=@a1)/RenderListDataAsStream?@a1='" . urlencode("/personal/" . $account . "/Documents") . "'&RootFolder=" . urlencode("/personal/" . $account . "/Documents/") . "&TryNewExperienceSingle=TRUE",
            '{"parameters":{"__metadata":{"type":"SP.RenderListDataParameters"},"RenderOptions":136967,"AllowMultipleValueFilterForTaxonomyFields":true,"AddRequiredFields":true}}',
            [ 'Accept' => 'application/json;odata=verbose', 'Content-Type' => 'application/json;odata=verbose', 'origin' => $domain, 'Cookie' => $_SERVER['sharecookie'] ]
        );
        if ($response['stat']==200) $ret = json_decode($response['body'], true);
        $_SERVER['access_token'] = splitlast($ret['ListSchema']['.driveAccessToken'],'=')[1];
        $_SERVER['api_url'] = $ret['ListSchema']['.driveUrl'].'/root';
        if (!$_SERVER['access_token']) {
            error_log($domain . "/personal/" . $account . "/_api/web/GetListUsingPath(DecodedUrl=@a1)/RenderListDataAsStream?@a1='" . urlencode("/personal/" . $account . "/Documents") . "'&RootFolder=" . urlencode("/personal/" . $account . "/Documents/") . "&TryNewExperienceSingle=TRUE");
            error_log('failed to get share access_token. response' . json_encode($ret));
            throw new Exception($response['stat'].', failed to get share access_token.'.$response['body']);
        }
        error_log('Get access token:'.json_encode($ret, JSON_PRETTY_PRINT));
        savecache('access_token', $_SERVER['access_token']);
        if (getConfig('sharecookie')==''||getConfig('shareapiurl')=='') setConfig([ 'sharecookie' => $_SERVER['sharecookie'], 'shareapiurl' => $_SERVER['api_url'] ]);
    } else {
        $response = curl_request( $_SERVER['oauth_url'] . 'token', 'client_id='. $_SERVER['client_id'] .'&client_secret='. $_SERVER['client_secret'] .'&grant_type=refresh_token&requested_token_use=on_behalf_of&refresh_token=' . $refresh_token );
        if ($response['stat']==200) $ret = json_decode($response['body'], true);
        if (!isset($ret['access_token'])) {
            error_log($_SERVER['oauth_url'] . 'token'.'?client_id='. $_SERVER['client_id'] .'&client_secret='. $_SERVER['client_secret'] .'&grant_type=refresh_token&requested_token_use=on_behalf_of&refresh_token=' . $refresh_token);
            error_log('failed to get access_token. response' . json_encode($ret));
            throw new Exception($response['stat'].', failed to get access_token.'.$response['body']);
        }
        error_log('Get access token:'.json_encode($ret, JSON_PRETTY_PRINT));
        $_SERVER['access_token'] = $ret['access_token'];
        savecache('access_token', $_SERVER['access_token'], $ret['expires_in'] - 300);
        if (time()>getConfig('token_expires')) setConfig([ 'refresh_token' => $ret['refresh_token'], 'token_expires' => time()+7*24*60*60 ]);
    }
}

function list_files($path)
{
    $path = path_format($path);
    if ($_SERVER['is_guestup_path']&&!$_SERVER['admin']) {
        $files = json_decode('{"folder":{}}', true);
    } elseif (!getConfig('downloadencrypt')) {
        if ($_SERVER['ishidden']==4) $files = json_decode('{"folder":{}}', true);
        else $files = fetch_files($path);
    } else {
        $files = fetch_files($path);
    }
    return $files;
}

function isHideFile($name)
{
    $FunctionalityFile = [
        'head.md',
        'readme.md',
        'favicon.ico',
    ];

    if ($name == getConfig('passfile')) return true;
    if (substr($name,0,1) == '.') return true;
    if (getConfig('hideFunctionalityFile')) if (in_array(strtolower($name), $FunctionalityFile)) return true;
    return false;
}

function getcache($str)
{
    $cache = new \Doctrine\Common\Cache\FilesystemCache(sys_get_temp_dir(), __DIR__.'/Onedrive/'.$_SERVER['disktag']);
    return $cache->fetch($str);
}

function savecache($key, $value, $exp = 1800)
{
    $cache = new \Doctrine\Common\Cache\FilesystemCache(sys_get_temp_dir(), __DIR__.'/Onedrive/'.$_SERVER['disktag']);
    $cache->save($key, $value, $exp);
}

function getconstStr($str)
{
    global $constStr;
    if ($constStr[$str][$constStr['language']]!='') return $constStr[$str][$constStr['language']];
    return $constStr[$str]['en-us'];
}

function config_oauth()
{
    $_SERVER['sitename'] = getConfig('sitename');
    if (empty($_SERVER['sitename'])) $_SERVER['sitename'] = getconstStr('defaultSitename');
    $_SERVER['redirect_uri'] = 'https://scfonedrive.github.io';
    if (getConfig('Drive_ver')=='shareurl') {
        $_SERVER['api_url'] = getConfig('shareapiurl');
        $_SERVER['sharecookie'] = getConfig('sharecookie');
        $_SERVER['DownurlStrName'] = '@content.downloadUrl';
        return 0;
    }
    if (getConfig('Drive_ver')=='MS') {
        // MS
        // https://portal.azure.com
        //$_SERVER['client_id'] = '4da3e7f2-bf6d-467c-aaf0-578078f0bf7c';
        //$_SERVER['client_secret'] = '7/+ykq2xkfx:.DWjacuIRojIaaWL0QI6';
        $_SERVER['client_id'] = '734ef928-d74c-4555-8d1b-d942fa0a1a41';
        $_SERVER['client_secret'] = ':EK[e0/4vQ@mQgma8LmnWb6j4_C1CSIW';
        $_SERVER['oauth_url'] = 'https://login.microsoftonline.com/common/oauth2/v2.0/';
        $_SERVER['api_url'] = 'https://graph.microsoft.com/v1.0/me/drive/root';
        $_SERVER['scope'] = 'https://graph.microsoft.com/Files.ReadWrite.All offline_access';
        if (getConfig('usesharepoint')=='on') $_SERVER['api_url'] = 'https://graph.microsoft.com/v1.0/sites/' . getConfig('siteid') . '/drive/root';
    }
    if (getConfig('Drive_ver')=='CN') {
        // CN
        // https://portal.azure.cn
        $_SERVER['client_id'] = '04c3ca0b-8d07-4773-85ad-98b037d25631';
        $_SERVER['client_secret'] = 'h8@B7kFVOmj0+8HKBWeNTgl@pU/z4yLB';
        $_SERVER['oauth_url'] = 'https://login.partner.microsoftonline.cn/common/oauth2/v2.0/';
        $_SERVER['api_url'] = 'https://microsoftgraph.chinacloudapi.cn/v1.0/me/drive/root';
        $_SERVER['scope'] = 'https://microsoftgraph.chinacloudapi.cn/Files.ReadWrite.All offline_access';
        if (getConfig('usesharepoint')=='on') $_SERVER['api_url'] = 'https://microsoftgraph.chinacloudapi.cn/v1.0/sites/' . getConfig('siteid') . '/drive/root';
    }

    if (getConfig('Drive_custom')=='on') {
        // Customer
        $_SERVER['client_id'] = getConfig('client_id');
        $_SERVER['client_secret'] = getConfig('client_secret');
    }
    $_SERVER['client_secret'] = urlencode($_SERVER['client_secret']);
    $_SERVER['scope'] = urlencode($_SERVER['scope']);
    $_SERVER['DownurlStrName'] = '@microsoft.graph.downloadUrl';
}

function get_siteid($access_token)
{
    if (getConfig('Drive_ver')=='MS') $url = 'https://graph.microsoft.com/v1.0/sites/root:/sites/'.getConfig('sharepointname');
    if (getConfig('Drive_ver')=='CN') $url = 'https://microsoftgraph.chinacloudapi.cn/v1.0/sites/root:/sites/'.getConfig('sharepointname');
    $i=0;
    $response = [];
    while ($url!=''&&$response['stat']!=200&&$i<4) {
        $response = curl_request($url, false, ['Authorization' => 'Bearer ' . $access_token]);
        $i++;
    }
    if ($response['stat']!=200) {
        error_log('failed to get siteid. response' . json_encode($response));
        throw new Exception($response['stat'].', failed to get siteid.'.$response['body']);
    }
    return json_decode($response['body'],true)['id'];
}

function getListpath($domain)
{
    $domain_path1 = getConfig('domain_path');
    $public_path = getConfig('public_path');
    $tmp_path='';
    if ($domain_path1!='') {
        $tmp = explode("|",$domain_path1);
        foreach ($tmp as $multidomain_paths){
            $pos = strpos($multidomain_paths,":");
            if ($pos>0) {
                $domain1 = substr($multidomain_paths,0,$pos);
                $tmp_path = path_format(substr($multidomain_paths,$pos+1));
                $domain_path[$domain1] = $tmp_path;
                if ($public_path=='') $public_path = $tmp_path;
            //if (substr($multidomain_paths,0,$pos)==$host_name) $private_path=$tmp_path;
            }
        }
    }
    if (isset($domain_path[$domain])) return spurlencode($domain_path[$domain],'/');
    return spurlencode($public_path,'/');
}

function path_format($path)
{
    $path = '/' . $path;
    while (strpos($path, '//') !== FALSE) {
        $path = str_replace('//', '/', $path);
    }
    return $path;
}

function spurlencode($str,$split='')
{
    $str = str_replace(' ', '%20',$str);
    $tmp='';
    if ($split!='') {
        $tmparr=explode($split,$str);
        for($x=0;$x<count($tmparr);$x++) {
            if ($tmparr[$x]!='') $tmp .= $split . urlencode($tmparr[$x]);
        }
    } else {
        $tmp = urlencode($str);
    }
    $tmp = str_replace('%2520', '%20',$tmp);
    return $tmp;
}

function equal_replace($str, $add = false)
{
    if ($add) {
        while(strlen($str)%4) $str .= '=';
        $str = urldecode(base64_decode($str));
    } else {
        $str = base64_encode(urlencode($str));
        while(substr($str,-1)=='=') $str=substr($str,0,-1);
    }
    return $str;
}

function is_guestup_path($path)
{
    $a1 = path_format(path_format(urldecode($_SERVER['list_path'].path_format($path))).'/');
    $a2 = path_format(path_format(getConfig('guestup_path')).'/');
    if (getConfig('guestup_path')!=''&&strtolower($a1)==strtolower($a2)) return 1;
    return 0;
}

function array_value_isnot_null($arr)
{
    return $arr!=='';
}

function curl_request($url, $data = false, $headers = [], $returnheader = 0)
{
    if (!isset($headers['Accept'])) $headers['Accept'] = '*/*';
    //if (!isset($headers['Referer'])) $headers['Referer'] = $url;
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
    curl_setopt($ch, CURLOPT_HEADER, $returnheader);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $sendHeaders);
    //$response['body'] = curl_exec($ch);
    if ($returnheader) {
        list($returnhead, $response['body']) = explode("\r\n\r\n", curl_exec($ch));
        foreach (explode("\r\n", $returnhead) as $head) {
            $tmp = explode(': ', $head);
            $heads[$tmp[0]] = $tmp[1];
        }
        $response['returnhead'] = $heads;
    } else {
        $response['body'] = curl_exec($ch);
    }
    $response['stat'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $response;
}

function clearbehindvalue($path,$page1,$maxpage,$pageinfocache)
{
    for ($page=$page1+1;$page<$maxpage;$page++) {
        $pageinfocache['nextlink_' . $path . '_page_' . $page] = '';
    }
    $pageinfocache = array_filter($pageinfocache, 'array_value_isnot_null');
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
    $path1 = path_format($_SERVER['list_path'] . path_format($path));
    if ($path1!='/'&&substr($path1,-1)=='/') $path1=substr($path1,0,-1);
    $password=getcache('path_' . $path1 . '/?password');
    if ($password=='') {
        $ispassfile = fetch_files(path_format($path . '/' . urlencode($passfile)));
        //echo $path . '<pre>' . json_encode($ispassfile, JSON_PRETTY_PRINT) . '</pre>';
        if (isset($ispassfile['file'])) {
            $arr = curl_request($ispassfile[$_SERVER['DownurlStrName']]);
            if ($arr['stat']==200) {
                $passwordf=explode("\n",$arr['body']);
                $password=$passwordf[0];
                if ($password!='') $password=md5($password);
                savecache('path_' . $path1 . '/?password', $password);
                return $password;
            } else {
                //return md5('DefaultP@sswordWhenNetworkError');
                return md5( md5(time()).rand(1000,9999) );
            }
        } else {
            savecache('path_' . $path1 . '/?password', 'null');
            if ($path !== '' ) {
                $path = substr($path,0,strrpos($path,'/'));
                return gethiddenpass($path,$passfile);
            } else {
                return '';
            }
        }
    } elseif ($password==='null') {
        if ($path !== '' ) {
            $path = substr($path,0,strrpos($path,'/'));
            return gethiddenpass($path,$passfile);
        } else {
            return '';
        }
    } else return $password;
    // return md5('DefaultP@sswordWhenNetworkError');
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
    return output('
<html lang="' . $_SERVER['language'] . '">
<html>
    <meta charset=utf-8>
    <meta name=viewport content="width=device-width,initial-scale=1">
    <body>
        <h1>' . $title . '</h1>
        <p>

' . $message . '

        </p>
    </body>
</html>
', $statusCode);
}

function needUpdate()
{
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
        $path = spurlencode($path,'/');
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

function get_thumbnails_url($path = '/')
{
    $path1 = path_format($path);
    $path = path_format($_SERVER['list_path'] . path_format($path));
    if ($path!='/'&&substr($path,-1)=='/') $path=substr($path,0,-1);
    $thumb_url = getcache('thumb_'.$path);
    if ($thumb_url!='') return output($thumb_url);
    $url = $_SERVER['api_url'];
    if ($path !== '/') {
        $url .= ':' . $path;
        if (substr($url,-1)=='/') $url=substr($url,0,-1);
    }
    $url .= ':/thumbnails/0/medium';
    $files = json_decode(curl_request($url, false, ['Authorization' => 'Bearer ' . $_SERVER['access_token']])['body'], true);
    if (isset($files['url'])) {
        savecache('thumb_'.$path, $files['url']);
        return output($files['url']);
    }
    return output('', 404);
}

function bigfileupload($path)
{
    $path1 = path_format($_SERVER['list_path'] . path_format($path));
    if (substr($path1,-1)=='/') $path1=substr($path1,0,-1);
    if ($_GET['upbigfilename']!=''&&$_GET['filesize']>0) {
        $fileinfo['name'] = $_GET['upbigfilename'];
        $fileinfo['size'] = $_GET['filesize'];
        $fileinfo['lastModified'] = $_GET['lastModified'];
        $filename = spurlencode( $fileinfo['name'] );
        $cachefilename = '.' . $fileinfo['lastModified'] . '_' . $fileinfo['size'] . '_' . $filename . '.tmp';
        $getoldupinfo=fetch_files(path_format($path . '/' . $cachefilename));
        //echo json_encode($getoldupinfo, JSON_PRETTY_PRINT);
        if (isset($getoldupinfo['file'])&&$getoldupinfo['size']<5120) {
            $getoldupinfo_j = curl_request($getoldupinfo[$_SERVER['DownurlStrName']]);
            $getoldupinfo = json_decode($getoldupinfo_j['body'], true);
            if ( json_decode( curl_request($getoldupinfo['uploadUrl'])['body'], true)['@odata.context']!='' ) return output($getoldupinfo_j['body'], $getoldupinfo_j['stat']);
        }
        if (!$_SERVER['admin']) $filename = spurlencode( $fileinfo['name'] ) . '.scfupload';
        $response=MSAPI('createUploadSession',path_format($path1 . '/' . $filename),'{"item": { "@microsoft.graph.conflictBehavior": "fail"  }}',$_SERVER['access_token']);
        $responsearry = json_decode($response['body'],true);
        if (isset($responsearry['error'])) return output($response['body'], $response['stat']);
        $fileinfo['uploadUrl'] = $responsearry['uploadUrl'];
        MSAPI('PUT', path_format($path1 . '/' . $cachefilename), json_encode($fileinfo, JSON_PRETTY_PRINT), $_SERVER['access_token'])['body'];
        return output($response['body'], $response['stat']);
    }
    return output('error', 400);
}

function adminform($name = '', $pass = '', $path = '')
{
    $statusCode = 401;
    $html = '<html><head><title>'.getconstStr('AdminLogin').'</title><meta charset=utf-8></head>';
    if ($name!=''&&$pass!='') {
        $html .= '<body>'.getconstStr('LoginSuccess').'</body></html>';
        $statusCode = 302;
        date_default_timezone_set('UTC');
        $header = [
            'Set-Cookie' => $name.'='.$pass.'; path=/; expires='.date(DATE_COOKIE,strtotime('+1hour')),
            'Location' => $path,
            'Content-Type' => 'text/html'
        ];
        return output($html,$statusCode,$header);
    }
    $html .= '
    <body>
	<div>
	  <center><h4>'.getconstStr('InputPassword').'</h4>
	  <form action="" method="post">
		  <div>
		    <input name="password1" type="password"/>
		    <input type="submit" value="'.getconstStr('Login').'">
          </div>
	  </form>
      </center>
	</div>
';
    $html .= '</body></html>';
    return output($html,$statusCode);
}

function adminoperate($path)
{
    $path1 = path_format($_SERVER['list_path'] . path_format($path));
    if (substr($path1,-1)=='/') $path1=substr($path1,0,-1);
    $tmparr['statusCode'] = 0;
    if (isset($_GET['rename_newname'])&&$_GET['rename_newname']!=$_GET['rename_oldname'] && $_GET['rename_newname']!='') {
        // rename 重命名
        $oldname = spurlencode($_GET['rename_oldname']);
        $oldname = path_format($path1 . '/' . $oldname);
        $data = '{"name":"' . $_GET['rename_newname'] . '"}';
                //echo $oldname;
        $result = MSAPI('PATCH',$oldname,$data,$_SERVER['access_token']);
        //savecache('path_' . $path1, json_decode('{}',true), 1);
        return output($result['body'], $result['stat']);
    }
    if (isset($_GET['delete_name'])) {
        // delete 删除
        $filename = spurlencode($_GET['delete_name']);
        $filename = path_format($path1 . '/' . $filename);
                //echo $filename;
        $result = MSAPI('DELETE', $filename, '', $_SERVER['access_token']);
        //savecache('path_' . $path1, json_decode('{}',true), 1);
        return output($result['body'], $result['stat']);
    }
    if (isset($_GET['operate_action'])&&$_GET['operate_action']==getconstStr('encrypt')) {
        // encrypt 加密
        if (getConfig('passfile')=='') return message(getconstStr('SetpassfileBfEncrypt'),'',403);
        if ($_GET['encrypt_folder']=='/') $_GET['encrypt_folder']=='';
        $foldername = spurlencode($_GET['encrypt_folder']);
        $filename = path_format($path1 . '/' . $foldername . '/' . getConfig('passfile'));
                //echo $foldername;
        $result = MSAPI('PUT', $filename, $_GET['encrypt_newpass'], $_SERVER['access_token']);
        $path1 = path_format($path1 . '/' . $foldername );
        if ($path1!='/'&&substr($path1,-1)=='/') $path1=substr($path1,0,-1);
        savecache('path_' . $path1 . '/?password', '', 1);
        return output($result['body'], $result['stat']);
    }
    if (isset($_GET['move_folder'])) {
        // move 移动
        $moveable = 1;
        if ($path == '/' && $_GET['move_folder'] == '/../') $moveable=0;
        if ($_GET['move_folder'] == $_GET['move_name']) $moveable=0;
        if ($moveable) {
            $filename = spurlencode($_GET['move_name']);
            $filename = path_format($path1 . '/' . $filename);
            $foldername = path_format('/'.urldecode($path1).'/'.$_GET['move_folder']);
            $data = '{"parentReference":{"path": "/drive/root:'.$foldername.'"}}';
            $result = MSAPI('PATCH', $filename, $data, $_SERVER['access_token']);
            //savecache('path_' . $path1, json_decode('{}',true), 1);
            if ($_GET['move_folder'] == '/../') $path2 = path_format( substr($path1, 0, strrpos($path1, '/')) . '/' );
            else $path2 = path_format( $path1 . '/' . $_GET['move_folder'] . '/' );
            if ($path2!='/'&&substr($path2,-1)=='/') $path2=substr($path2,0,-1);
            savecache('path_' . $path2, json_decode('{}',true), 1);
            return output($result['body'], $result['stat']);
        } else {
            return output('{"error":"'.getconstStr('CannotMove').'"}', 403);
        }
    }
    if (isset($_GET['copy_name'])) {
        // copy 复制
        $filename = spurlencode($_GET['copy_name']);
        $filename = path_format($path1 . '/' . $filename);
        $namearr = splitlast($_GET['copy_name'], '.');
        if ($namearr[0]!='') {
            $newname = $namearr[0] . ' (' . getconstStr('Copy') . ')';
            if ($namearr[1]!='') $newname .= '.' . $namearr[1];
        } else {
            $newname = '.' . $namearr[1] . ' (' . getconstStr('Copy') . ')';
        }
        //$newname = spurlencode($newname);
            //$foldername = path_format('/'.urldecode($path1).'/./');
            //$data = '{"parentReference":{"path": "/drive/root:'.$foldername.'"}}';
        $data = '{ "name": "' . $newname . '" }';
        $result = MSAPI('copy', $filename, $data, $_SERVER['access_token']);
        $num = 0;
        while ($result['stat']==409 && json_decode($result['body'], true)['error']['code']=='nameAlreadyExists') {
            $num++;
            if ($namearr[0]!='') {
                $newname = $namearr[0] . ' (' . getconstStr('Copy') . ' ' . $num . ')';
                if ($namearr[1]!='') $newname .= '.' . $namearr[1];
            } else {
                $newname = '.' . $namearr[1] . ' ('.getconstStr('Copy'). ' ' . $num .')';
            }
            //$newname = spurlencode($newname);
            $data = '{ "name": "' . $newname . '" }';
            $result = MSAPI('copy', $filename, $data, $_SERVER['access_token']);
        }
        //echo $result['stat'].$result['body'];
            //savecache('path_' . $path1, json_decode('{}',true), 1);
            //if ($_GET['move_folder'] == '/../') $path2 = path_format( substr($path1, 0, strrpos($path1, '/')) . '/' );
            //else $path2 = path_format( $path1 . '/' . $_GET['move_folder'] . '/' );
            //savecache('path_' . $path2, json_decode('{}',true), 1);
        return output($result['body'], $result['stat']);
    }
    if (isset($_POST['editfile'])) {
        // edit 编辑
        $data = $_POST['editfile'];
        /*TXT一般不会超过4M，不用二段上传
        $filename = $path1 . ':/createUploadSession';
        $response=MSAPI('POST',$filename,'{"item": { "@microsoft.graph.conflictBehavior": "replace"  }}',$_SERVER['access_token']);
        $uploadurl=json_decode($response,true)['uploadUrl'];
        echo MSAPI('PUT',$uploadurl,$data,$_SERVER['access_token']);*/
        $result = MSAPI('PUT', $path1, $data, $_SERVER['access_token'])['body'];
        //echo $result;
        $resultarry = json_decode($result,true);
        if (isset($resultarry['error'])) return message($resultarry['error']['message']. '<hr><a href="javascript:history.back(-1)">'.getconstStr('Back').'</a>','Error',403);
    }
    if (isset($_GET['create_name'])) {
        // create 新建
        if ($_GET['create_type']=='file') {
            $filename = spurlencode($_GET['create_name']);
            $filename = path_format($path1 . '/' . $filename);
            $result = MSAPI('PUT', $filename, $_GET['create_text'], $_SERVER['access_token']);
        }
        if ($_GET['create_type']=='folder') {
            $data = '{ "name": "' . $_GET['create_name'] . '",  "folder": { },  "@microsoft.graph.conflictBehavior": "rename" }';
            $result = MSAPI('children', $path1, $data, $_SERVER['access_token']);
        }
        //savecache('path_' . $path1, json_decode('{}',true), 1);
        return output($result['body'], $result['stat']);
    }
    if (isset($_GET['RefreshCache'])) {
        $path1 = path_format($_SERVER['list_path'] . path_format($path));
        if ($path1!='/'&&substr($path1,-1)=='/') $path1=substr($path1,0,-1);
        savecache('path_' . $path1 . '/?password', '', 1);
        return message('<meta http-equiv="refresh" content="2;URL=./">', getconstStr('RefreshCache'), 302);
    }
    return $tmparr;
}

function splitfirst($str, $split)
{
    $len = strlen($split);
    $pos = strpos($str, $split);
    if ($pos===false) {
        $tmp[0] = $str;
        $tmp[1] = '';
    } elseif ($pos>0) {
        $tmp[0] = substr($str, 0, $pos);
        $tmp[1] = substr($str, $pos+$len);
    } else {
        $tmp[0] = '';
        $tmp[1] = substr($str, $len);
    }
    return $tmp;
}

function splitlast($str, $split)
{
    $len = strlen($split);
    $pos = strrpos($str, $split);
    if ($pos===false) {
        $tmp[0] = $str;
        $tmp[1] = '';
    } elseif ($pos>0) {
        $tmp[0] = substr($str, 0, $pos);
        $tmp[1] = substr($str, $pos+$len);
    } else {
        $tmp[0] = '';
        $tmp[1] = substr($str, $len);
    }
    return $tmp;
}

function MSAPI($method, $path, $data = '', $access_token)
{
    if (substr($path,0,7) == 'http://' or substr($path,0,8) == 'https://') {
        $url=$path;
        $lenth=strlen($data);
        $headers['Content-Length'] = $lenth;
        $lenth--;
        $headers['Content-Range'] = 'bytes 0-' . $lenth . '/' . $headers['Content-Length'];
    } else {
        $url = $_SERVER['api_url'];
        if ($path=='' or $path=='/') {
            $url .= '/';
        } else {
            $url .= ':' . $path;
            if (substr($url,-1)=='/') $url=substr($url,0,-1);
        }
        if ($method=='PUT') {
            if ($path=='' or $path=='/') {
                $url .= 'content';
            } else {
                $url .= ':/content';
            }
            $headers['Content-Type'] = 'text/plain';
        } elseif ($method=='PATCH') {
            $headers['Content-Type'] = 'application/json';
        } elseif ($method=='POST') {
            $headers['Content-Type'] = 'application/json';
        } elseif ($method=='DELETE') {
            $headers['Content-Type'] = 'application/json';
        } else {
            if ($path=='' or $path=='/') {
                $url .= $method;
            } else {
                $url .= ':/' . $method;
            }
            $method='POST';
            $headers['Content-Type'] = 'application/json';
        }
    }
    $headers['Authorization'] = 'Bearer ' . $access_token;
    if (!isset($headers['Accept'])) $headers['Accept'] = '*/*';
    //if (!isset($headers['Referer'])) $headers['Referer'] = $url;*
    $sendHeaders = array();
    foreach ($headers as $headerName => $headerVal) {
        $sendHeaders[] = $headerName . ': ' . $headerVal;
    }
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
    //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $sendHeaders);
    $response['body'] = curl_exec($ch);
    $response['stat'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    //$response['Location'] = curl_getinfo($ch);
    curl_close($ch);
    error_log($response['stat'].'
'.$response['body'].'
');
    return $response;
}

function fetch_files($path = '/')
{
    global $exts;
    $path1 = path_format($path);
    $path = path_format($_SERVER['list_path'] . path_format($path));
    if ($path!='/'&&substr($path,-1)=='/') $path=substr($path,0,-1);
    if (!($files = getcache('path_' . $path))) {
        // https://docs.microsoft.com/en-us/graph/api/driveitem-get?view=graph-rest-1.0
        // https://docs.microsoft.com/zh-cn/graph/api/driveitem-put-content?view=graph-rest-1.0&tabs=http
        // https://developer.microsoft.com/zh-cn/graph/graph-explorer
        $pos = splitlast($path, '/');
        $parentpath = $pos[0];
        if ($parentpath=='') $parentpath = '/';
        $filename = $pos[1];
        if ($parentfiles = getcache('path_' . $parentpath)) {
            if (isset($parentfiles['children'][$filename][$_SERVER['DownurlStrName']])) {
                if (in_array(splitlast($filename,'.')[1], $exts['txt'])) {
                    if (!(isset($parentfiles['children'][$filename]['content'])&&$parentfiles['children'][$filename]['content']['stat']==200)) {
                        $content1 = curl_request($parentfiles['children'][$filename][$_SERVER['DownurlStrName']]);
                        $parentfiles['children'][$filename]['content'] = $content1;
                        savecache('path_' . $parentpath, $parentfiles);
                    }
                }
                return $parentfiles['children'][$filename];
            }
        }

        $url = $_SERVER['api_url'];
        if ($path !== '/') {
            $url .= ':' . $path;
            if (substr($url,-1)=='/') $url=substr($url,0,-1);
        }
        $url .= '?expand=children(select=name,size,file,folder,parentReference,lastModifiedDateTime,'.$_SERVER['DownurlStrName'].')';
        $retry = 0;
        $arr = [];
        while ($retry<3&&!$arr['stat']) {
            $arr = curl_request($url, false, ['Authorization' => 'Bearer ' . $_SERVER['access_token']],1);
            $retry++;
        }
        if ($arr['stat']<500) {
            $files = json_decode($arr['body'], true);
            //echo $path . '<br><pre>' . json_encode($arr, JSON_PRETTY_PRINT) . '</pre>';
            if (isset($files['folder'])) {
                if ($files['folder']['childCount']>200) {
                    // files num > 200 , then get nextlink
                    $page = $_POST['pagenum']==''?1:$_POST['pagenum'];
                    if ($page>1) $files=fetch_files_children($files, $path1, $page);
                    $files['children'] = children_name($files['children']);
                } else {
                // files num < 200 , then cache
                    //if (isset($files['children'])) {
                        $files['children'] = children_name($files['children']);
                    //}
                    savecache('path_' . $path, $files);
                }
            }
            if (isset($files['error'])) {
                $files['error']['stat'] = $arr['stat'];
            }
        } else {
            //error_log($arr['body']);
            $files = json_decode($arr['body'], true);
            if (isset($files['error'])) {
                $files['error']['stat'] = $arr['stat'];
            } else {
                $files['error']['stat'] = 503;
                $files['error']['code'] = 'unknownError';
                $files['error']['message'] = 'unknownError';
            }
            //$files = json_decode( '{"unknownError":{ "stat":'.$arr['stat'].',"message":"'.$arr['body'].'"}}', true);
            //error_log(json_encode($files, JSON_PRETTY_PRINT));
        }
    }

    return $files;
}

function children_name($children)
{
    $tmp = [];
    foreach ($children as $file) {
        $tmp[strtolower($file['name'])] = $file;
    }
    return $tmp;
}

function fetch_files_children($files, $path, $page)
{
    $path1 = path_format($path);
    $path = path_format($_SERVER['list_path'] . path_format($path));
    if ($path!='/'&&substr($path,-1)=='/') $path=substr($path,0,-1);
    $cachefilename = '.SCFcache_'.$_SERVER['function_name'];
    $maxpage = ceil($files['folder']['childCount']/200);
    if (!($files['children'] = getcache('files_' . $path . '_page_' . $page))) {
        // down cache file get jump info. 下载cache文件获取跳页链接
        $cachefile = fetch_files(path_format($path1 . '/' .$cachefilename));
        if ($cachefile['size']>0) {
            $pageinfo = curl_request($cachefile[$_SERVER['DownurlStrName']])['body'];
            $pageinfo = json_decode($pageinfo,true);
            for ($page4=1;$page4<$maxpage;$page4++) {
                savecache('nextlink_' . $path . '_page_' . $page4, $pageinfo['nextlink_' . $path . '_page_' . $page4]);
                $pageinfocache['nextlink_' . $path . '_page_' . $page4] = $pageinfo['nextlink_' . $path . '_page_' . $page4];
            }
        }
        $pageinfochange=0;
        for ($page1=$page;$page1>=1;$page1--) {
            $page3=$page1-1;
            $url = getcache('nextlink_' . $path . '_page_' . $page3);
            if ($url == '') {
                if ($page1==1) {
                    $url = $_SERVER['api_url'];
                    if ($path !== '/') {
                        $url .= ':' . $path;
                        if (substr($url,-1)=='/') $url=substr($url,0,-1);
                        $url .= ':/children?$select=name,size,file,folder,parentReference,lastModifiedDateTime,'.$_SERVER['DownurlStrName'];
                    } else {
                        $url .= '/children?$select=name,size,file,folder,parentReference,lastModifiedDateTime,'.$_SERVER['DownurlStrName'];
                    }
                    $children = json_decode(curl_request($url, false, ['Authorization' => 'Bearer ' . $_SERVER['access_token']])['body'], true);
                    // echo $url . '<br><pre>' . json_encode($children, JSON_PRETTY_PRINT) . '</pre>';
                    savecache('files_' . $path . '_page_' . $page1, $children['value']);
                    $nextlink=getcache('nextlink_' . $path . '_page_' . $page1);
                    if ($nextlink!=$children['@odata.nextLink']) {
                        savecache('nextlink_' . $path . '_page_' . $page1, $children['@odata.nextLink']);
                        $pageinfocache['nextlink_' . $path . '_page_' . $page1] = $children['@odata.nextLink'];
                        $pageinfocache = clearbehindvalue($path,$page1,$maxpage,$pageinfocache);
                        $pageinfochange = 1;
                    }
                    $url = $children['@odata.nextLink'];
                    for ($page2=$page1+1;$page2<=$page;$page2++) {
                        sleep(1);
                        $children = json_decode(curl_request($url, false, ['Authorization' => 'Bearer ' . $_SERVER['access_token']])['body'], true);
                        savecache('files_' . $path . '_page_' . $page2, $children['value']);
                        $nextlink=getcache('nextlink_' . $path . '_page_' . $page2);
                        if ($nextlink!=$children['@odata.nextLink']) {
                            savecache('nextlink_' . $path . '_page_' . $page2, $children['@odata.nextLink']);
                            $pageinfocache['nextlink_' . $path . '_page_' . $page2] = $children['@odata.nextLink'];
                            $pageinfocache = clearbehindvalue($path,$page2,$maxpage,$pageinfocache);
                            $pageinfochange = 1;
                        }
                        $url = $children['@odata.nextLink'];
                    }
                    //echo $url . '<br><pre>' . json_encode($children, JSON_PRETTY_PRINT) . '</pre>';
                    $files['children'] = $children['value'];
                    $files['folder']['page']=$page;
                    $pageinfocache['filenum'] = $files['folder']['childCount'];
                    $pageinfocache['dirsize'] = $files['size'];
                    $pageinfocache['cachesize'] = $cachefile['size'];
                    $pageinfocache['size'] = $files['size']-$cachefile['size'];
                    if ($pageinfochange == 1) MSAPI('PUT', path_format($path.'/'.$cachefilename), json_encode($pageinfocache, JSON_PRETTY_PRINT), $_SERVER['access_token'])['body'];
                    return $files;
                }
            } else {
                for ($page2=$page3+1;$page2<=$page;$page2++) {
                    sleep(1);
                    $children = json_decode(curl_request($url, false, ['Authorization' => 'Bearer ' . $_SERVER['access_token']])['body'], true);
                    savecache('files_' . $path . '_page_' . $page2, $children['value'], 3300);
                    $nextlink=getcache('nextlink_' . $path . '_page_' . $page2);
                    if ($nextlink!=$children['@odata.nextLink']) {
                        savecache('nextlink_' . $path . '_page_' . $page2, $children['@odata.nextLink'], 3300);
                        $pageinfocache['nextlink_' . $path . '_page_' . $page2] = $children['@odata.nextLink'];
                        $pageinfocache = clearbehindvalue($path,$page2,$maxpage,$pageinfocache);
                        $pageinfochange = 1;
                    }
                    $url = $children['@odata.nextLink'];
                }
                //echo $url . '<br><pre>' . json_encode($children, JSON_PRETTY_PRINT) . '</pre>';
                $files['children'] = $children['value'];
                $files['folder']['page']=$page;
                $pageinfocache['filenum'] = $files['folder']['childCount'];
                $pageinfocache['dirsize'] = $files['size'];
                $pageinfocache['cachesize'] = $cachefile['size'];
                $pageinfocache['size'] = $files['size']-$cachefile['size'];
                if ($pageinfochange == 1) MSAPI('PUT', path_format($path.'/'.$cachefilename), json_encode($pageinfocache, JSON_PRETTY_PRINT), $_SERVER['access_token'])['body'];
                return $files;
            }
        }
    } else {
        $files['folder']['page']=$page;
        for ($page4=1;$page4<=$maxpage;$page4++) {
            if (!($url = getcache('nextlink_' . $path . '_page_' . $page4))) {
                if ($files['folder'][$path.'_'.$page4]!='') savecache('nextlink_' . $path . '_page_' . $page4, $files['folder'][$path.'_'.$page4]);
            } else {
                $files['folder'][$path.'_'.$page4] = $url;
            }
        }
    }
    return $files;
}

function render_list($path = '', $files = '')
{
    global $exts;
    global $constStr;

    if (isset($files['children']['index.html']) && !$_SERVER['admin']) {
        $htmlcontent = fetch_files(spurlencode(path_format($path . '/index.html'),'/'))['content'];
        return output($htmlcontent['body'], $htmlcontent['stat']);
    }
    $path = str_replace('%20','%2520',$path);
    $path = str_replace('+','%2B',$path);
    $path = str_replace('&','&amp;',path_format(urldecode($path))) ;
    $path = str_replace('%20',' ',$path);
    $path = str_replace('#','%23',$path);
    $p_path='';
    if ($path !== '/') {
        if (isset($files['file'])) {
            $pretitle = str_replace('&','&amp;', $files['name']);
            $n_path=$pretitle;
        } else {
            $pretitle = substr($path,-1)=='/'?substr($path,0,-1):$path;
            $n_path=substr($pretitle,strrpos($pretitle,'/')+1);
            $pretitle = substr($pretitle,1);
        }
        if (strrpos($path,'/')!=0) {
            $p_path=substr($path,0,strrpos($path,'/'));
            $p_path=substr($p_path,strrpos($p_path,'/')+1);
        }
    } else {
      $pretitle = getconstStr('Home');
      $n_path=$pretitle;
    }
    $n_path=str_replace('&amp;','&',$n_path);
    $p_path=str_replace('&amp;','&',$p_path);
    $pretitle = str_replace('%23','#',$pretitle);
    $statusCode=200;
    date_default_timezone_set(get_timezone($_COOKIE['timezone']));
    @ob_start();

    $theme = getConfig('theme');
    if ( $theme=='' || !file_exists('theme/'.$theme) ) $theme = 'classic.php';
    include 'theme/'.$theme;

    $html = '<!--
    Github ： https://github.com/qkqpttgf/OneManager-php
-->' . ob_get_clean();
    //if (isset($htmlpage['statusCode'])) return $htmlpage;
    if (isset($_SERVER['Set-Cookie'])) return output($html, $statusCode, [ 'Set-Cookie' => $_SERVER['Set-Cookie'], 'Content-Type' => 'text/html' ]);
    return output($html,$statusCode);
}

function get_refresh_token()
{
    global $constStr;
    global $CommonEnv;
    $envs = '';
    foreach ($CommonEnv as $env) $envs .= '\'' . $env . '\', ';
    $url = path_format($_SERVER['PHP_SELF'] . '/');
    if (isset($_GET['authorization_code']) && isset($_GET['code'])) {
        $_SERVER['disktag'] = $_COOKIE['disktag'];
        config_oauth();
        $tmp = curl_request($_SERVER['oauth_url'] . 'token', 'client_id=' . $_SERVER['client_id'] .'&client_secret=' . $_SERVER['client_secret'] . '&grant_type=authorization_code&requested_token_use=on_behalf_of&redirect_uri=' . $_SERVER['redirect_uri'] .'&code=' . $_GET['code']);
        if ($tmp['stat']==200) $ret = json_decode($tmp['body'], true);
        if (isset($ret['refresh_token'])) {
            $refresh_token = $ret['refresh_token'];
            $str = '
        refresh_token :<br>';
            $str .= '
        <textarea readonly style="width: 95%">' . $refresh_token . '</textarea><br><br>
        '.getconstStr('SavingToken').'
        <script>
            var texta=document.getElementsByTagName(\'textarea\');
            for(i=0;i<texta.length;i++) {
                texta[i].style.height = texta[i].scrollHeight + \'px\';
            }
            document.cookie=\'language=; path=/\';
            document.cookie=\'disktag=; path=/\';
        </script>';
            $tmptoken['refresh_token'] = $refresh_token;
            $tmptoken['token_expires'] = time()+7*24*60*60;
            if (getConfig('usesharepoint')=='on') $tmptoken['siteid'] = get_siteid($ret['access_token']);
            setConfig($tmptoken, $_COOKIE['disktag']);
            savecache('access_token', $ret['access_token'], $ret['expires_in'] - 60);
            //WaitSCFStat();
            $str .= '
            <meta http-equiv="refresh" content="5;URL=' . $url . '">';
            return message($str, getconstStr('WaitJumpIndex'));
        }
        return message('<pre>' . json_encode(json_decode($tmp['body']), JSON_PRETTY_PRINT) . '</pre>', $tmp['stat']);
        //return message('<pre>' . json_encode($ret, JSON_PRETTY_PRINT) . '</pre>', 500);
    }
    if (isset($_GET['install1'])) {
        $_SERVER['disktag'] = $_COOKIE['disktag'];
        config_oauth();
        if (getConfig('Drive_ver')=='MS' || getConfig('Drive_ver')=='CN') {
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
        } else {
            return message('something error, try after a few seconds.', 'retry', 201);
        }
    }
    if (isset($_GET['install0'])) {
        if ($_POST['disktag_add']!='') {
            if (in_array($_COOKIE['disktag'], $CommonEnv)) {
                return message('Do not input ' . $envs . '<br><button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button><script>document.cookie=\'disktag=; path=/\';</script>', 'Error', 201);
            }
            $_SERVER['disktag'] = $_COOKIE['disktag'];
            $tmp['disktag_add'] = $_POST['disktag_add'];
            $tmp['diskname'] = $_POST['diskname'];
            $tmp['Drive_ver'] = $_POST['Drive_ver'];
            if ($_POST['Drive_ver']=='shareurl') {
                $tmp['shareurl'] = $_POST['shareurl'];
                $tmp['refresh_token'] = 1;
            } else {
                if ($_POST['Drive_custom']=='on') {
                    $tmp['Drive_custom'] = $_POST['Drive_custom'];
                    $tmp['client_id'] = $_POST['client_id'];
                    $tmp['client_secret'] = $_POST['client_secret'];
                }
                if ($_POST['usesharepoint']=='on') {
                    $tmp['usesharepoint'] = $_POST['usesharepoint'];
                    $tmp['sharepointname'] = $_POST['sharepointname'];
                }
            }
            $response = setConfigResponse( setConfig($tmp, $_COOKIE['disktag']) );
            if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
            } else {
                $title = getconstStr('MayinEnv');
                $html = getconstStr('Wait') . ' 3s<meta http-equiv="refresh" content="3;URL=' . $url . '?AddDisk&install1">';
                if ($_POST['Drive_ver']=='shareurl') $html = getconstStr('Wait') . ' 3s<meta http-equiv="refresh" content="3;URL=' . $url . '">';
            }
            return message($html, $title, 201);
        }
    }

    if ($constStr['language']!='zh-cn') {
        $linklang='en-us';
    } else $linklang='zh-cn';
    $ru = "https://developer.microsoft.com/".$linklang."/graph/quick-start?appID=_appId_&appName=_appName_&redirectUrl=".$_SERVER['redirect_uri']."&platform=option-php";
    $deepLink = "/quickstart/graphIO?publicClientSupport=false&appName=OneManager&redirectUrl=".$_SERVER['redirect_uri']."&allowImplicitFlow=false&ru=".urlencode($ru);
    $app_url = "https://apps.dev.microsoft.com/?deepLink=".urlencode($deepLink);
    $html = '
<div>
    <form action="?AddDisk&install0" method="post" onsubmit="return notnull(this);">
        '.getconstStr('OnedriveDiskTag').': ('.getConfig('disktag').')<input type="text" name="disktag_add" placeholder="' . getconstStr('EnvironmentsDescription')['disktag'] . '" style="width:100%"><br>
        '.getconstStr('OnedriveDiskName').':<input type="text" name="diskname" placeholder="' . getconstStr('EnvironmentsDescription')['diskname'] . '" style="width:100%"><br>
        <br>
        <div>
            <label><input type="radio" name="Drive_ver" value="MS" checked onclick="document.getElementById(\'morecustom\').style.display=\'\';document.getElementById(\'inputshareurl\').style.display=\'none\';">MS: '.getconstStr('DriveVerMS').'</label><br>
            <label><input type="radio" name="Drive_ver" value="CN" onclick="document.getElementById(\'morecustom\').style.display=\'\';document.getElementById(\'inputshareurl\').style.display=\'none\';">CN: '.getconstStr('DriveVerCN').'</label><br>
            <label><input type="radio" name="Drive_ver" value="shareurl" onclick="document.getElementById(\'inputshareurl\').style.display=\'\';document.getElementById(\'morecustom\').style.display=\'none\';">shareurl: '.getconstStr('DriveVerShareurl').'</label><br>
        </div>
        <br>
        <div id="inputshareurl" style="display:none;margin:10px 35px">
            '.getconstStr('UseShareLink').'
            <input type="text" name="shareurl" style="width:100%" placeholder="https://xxxx.sharepoint.com/:f:/g/personal/xxxxxxxx/mmmmmmmmm?e=XXXX"><br>
        </div>
        <div id="morecustom">
            <label><input type="checkbox" name="Drive_custom" onclick="document.getElementById(\'secret\').style.display=(this.checked?\'\':\'none\');">'.getconstStr('CustomIdSecret').'</label><br>
            <div id="secret" style="display:none;margin:10px 35px">
                <a href="'.$app_url.'" target="_blank">'.getconstStr('GetSecretIDandKEY').'</a><br>
                client_secret:<input type="text" name="client_secret"><br>
                client_id:<input type="text" name="client_id" placeholder="12345678-90ab-cdef-ghij-klmnopqrstuv"><br>
            </div>
            <label><input type="checkbox" name="usesharepoint" onclick="document.getElementById(\'sharepoint\').style.display=(this.checked?\'\':\'none\');">'.getconstStr('UseSharepointInstead').'</label><br>
            <div id="sharepoint" style="display:none;margin:10px 35px">
                '.getconstStr('GetSharepointName').'<br>
                <input type="text" name="sharepointname" placeholder="'.getconstStr('InputSharepointName').'"><br>
            </div>
        </div>
        <br>
        <input type="submit" value="'.getconstStr('Submit').'">
    </form>
</div>
    <script>
        function notnull(t)
        {
            if (t.disktag_add.value==\'\') {
                alert(\''.getconstStr('OnedriveDiskTag').'\');
                return false;
            }
            envs = [' . $envs . '];
            if (envs.indexOf(t.disktag_add.value)>-1) {
                alert("Do not input ' . $envs . '");
                return false;
            }
            var reg = /^[a-zA-Z]([-_a-zA-Z0-9]{1,20})$/;
            if (!reg.test(t.disktag_add.value)) {
                alert(\''.getconstStr('TagFormatAlert').'\');
                return false;
            }
            if (t.Drive_ver.value==\'shareurl\') {
                if (t.shareurl.value==\'\') {
                    alert(\'shareurl\');
                    return false;
                }
            } else {
                if (t.Drive_custom.checked==true) {
                    if (t.client_secret.value==\'\'||t.client_id.value==\'\') {
                        alert(\'client_id & client_secret\');
                        return false;
                    }
                }
                if (t.usesharepoint.checked==true) {
                    if (t.sharepointname.value==\'\') {
                        alert(\''.getconstStr('InputSharepointName').'\');
                        return false;
                    }
                }
            }
            document.cookie=\'disktag=\'+t.disktag_add.value+\'; path=/\';
            return true;
        }
    </script>';
    $title = 'Bind Disk';
    return message($html, $title, 201);
}

function EnvOpt($needUpdate = 0)
{
    global $constStr;
    global $ShowedCommonEnv;
    global $ShowedInnerEnv;
    asort($ShowedCommonEnv);
    asort($ShowedInnerEnv);
    $html = '<title>OneManager '.getconstStr('Setup').'</title>';
    if (isset($_POST['updateProgram'])&&$_POST['updateProgram']==getconstStr('updateProgram')) {
        $response = OnekeyUpate($_POST['auth'], $_POST['project'], $_POST['branch']);
        if (api_error($response)) {
            $html = api_error_msg($response);
            $title = 'Error';
        } else {
            //WaitSCFStat();
            $html .= getconstStr('UpdateSuccess') . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>';
            $title = getconstStr('Setup');
        }
        return message($html, $title);
    }
    if (isset($_POST['submit1'])) {
        $_SERVER['disk_oprating'] = '';
        foreach ($_POST as $k => $v) {
            if (in_array($k, $ShowedCommonEnv)||in_array($k, $ShowedInnerEnv)||$k=='disktag_del' || $k=='disktag_add') {
                $tmp[$k] = $v;
            }
            if ($k == 'disk') $_SERVER['disk_oprating'] = $v;
        }
        /*if ($tmp['domain_path']!='') {
            $tmp1 = explode("|",$tmp['domain_path']);
            $tmparr = [];
            foreach ($tmp1 as $multidomain_paths){
                $pos = strpos($multidomain_paths,":");
                if ($pos>0) $tmparr[substr($multidomain_paths, 0, $pos)] = path_format(substr($multidomain_paths, $pos+1));
            }
            $tmp['domain_path'] = $tmparr;
        }*/
        $response = setConfigResponse( setConfig($tmp, $_SERVER['disk_oprating']) );
        if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
            } else {
                //WaitSCFStat();
                //sleep(3);
            $html .= getconstStr('Success') . '!<br>
<button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>';
            $title = getconstStr('Setup');
        }
        return message($html, $title);
    }
    if (isset($_GET['preview'])) {
        $preurl = $_SERVER['PHP_SELF'] . '?preview';
    } else {
        $preurl = path_format($_SERVER['PHP_SELF'] . '/');
    }
    $html .= '
<a href="'.$preurl.'">'.getconstStr('Back').'</a>&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['base_path'].'">'.getconstStr('Back').getconstStr('Home').'</a><br>
<a href="https://github.com/qkqpttgf/OneManager-php">Github</a><br>';

    $html .= '
<table border=1 width=100%>
    <form name="common" action="" method="post">
        <tr>
            <td colspan="2">'.getconstStr('PlatformConfig').'</td>
        </tr>';
    foreach ($ShowedCommonEnv as $key) {
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
    $html .= '
        <tr><td><input type="submit" name="submit1" value="'.getconstStr('Setup').'"></td></tr>
    </form>
</table><br>';
    foreach (explode("|",getConfig('disktag')) as $disktag) {
        if ($disktag!='') {
            $html .= '
<table border=1 width=100%>
    <form action="" method="post">
        <tr>
            <td colspan="2">'.$disktag.'：
            <input type="hidden" name="disktag_del" value="'.$disktag.'">
            <input type="submit" name="submit1" value="'.getconstStr('DelDisk').'">
            </td>
        </tr>
    </form>';
            if (getConfig('refresh_token', $disktag)!='') {
                $html .= '
    <form name="'.$disktag.'" action="" method="post">
        <input type="hidden" name="disk" value="'.$disktag.'">';
                foreach ($ShowedInnerEnv as $key) {
                    $html .= '
        <tr>
            <td><label>' . $key . '</label></td>
            <td width=100%><input type="text" name="' . $key .'" value="' . getConfig($key, $disktag) . '" placeholder="' . getconstStr('EnvironmentsDescription')[$key] . '" style="width:100%"></td>
        </tr>';
                }
                $html .= '
        <tr><td><input type="submit" name="submit1" value="'.getconstStr('Setup').'"></td></tr>
    </form>';
            }
            $html .= '
</table><br>';
        }
    }
    $html .= '
<a href="?AddDisk">'.getconstStr('AddDisk').'</a><br><br>';
    if (!((isset($_SERVER['USER'])&&$_SERVER['USER']==='qcloud')||(isset($_SERVER['HEROKU_APP_DIR'])&&$_SERVER['HEROKU_APP_DIR']==='/app'))) {
        $html .= '
'.getconstStr('VPSnotupdate').'<br>';
    } else {
        $html .= '
<form name="updateform" action="" method="post">
    <input type="text" name="auth" placeholder="auth" value="qkqpttgf">
    <input type="text" name="project" placeholder="project" value="OneManager-php">
    <button onclick="querybranchs();return false">'.getconstStr('QueryBranchs').'</button>
    <!--<input type="text" name="branch" placeholder="branch" value="master">-->
    <select name="branch">
        <option value="master">master</option>
    </select>
    <input type="submit" name="updateProgram" value="'.getconstStr('updateProgram').'">
</form>
<script>
    function querybranchs()
    {
        //alert(document.updateform.auth.value);
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "https://api.github.com/repos/"+document.updateform.auth.value+"/"+document.updateform.project.value+"/branches");
        //xhr.setRequestHeader("User-Agent","qkqpttgf/OneManager");
        xhr.send(null);
        xhr.onload = function(e){
            console.log(xhr.responseText+","+xhr.status);
            if (xhr.status==200) {
                document.updateform.branch.options.length=0;
                JSON.parse(xhr.responseText).forEach( function (e) {
                    //alert(e.name);
                    document.updateform.branch.options.add(new Option(e.name,e.name));
                    if ("master"==e.name) document.updateform.branch.options[document.updateform.branch.options.length-1].selected = true; 
                });
            } else {
                alert(xhr.responseText+"\n"+xhr.status);
            }
        }
        xhr.onerror = function(e){
            alert("Network Error "+xhr.status);
        }
    }
</script>
';
    }
    if ($needUpdate) {
        $html .= '<div style="position:relative;word-wrap: break-word;">
        ' . str_replace("\r", '<br>',$_SERVER['github_version']) . '
</div>';
    } else {
        $html .= getconstStr('NotNeedUpdate');
    }
    return message($html, getconstStr('Setup'));
}
