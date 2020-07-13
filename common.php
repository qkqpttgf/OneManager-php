<?php

$Base64Env = [
    //'APIKey', // used in heroku.
    //'Region', // used in SCF.
    //'SecretId', // used in SCF.
    //'SecretKey', // used in SCF.
    //'AccessKeyID', // used in FC.
    //'AccessKeySecret', // used in FC.
    //'HW_urn', // used in FG.
    //'HW_key', // used in FG.
    //'HW_secret', // used in FG.
    //'admin',
    //'adminloginpage',
    'background',
    'diskname',
    //'disableShowThumb',
    //'disableChangeTheme',
    //'disktag',
    //'downloadencrypt',
    //'function_name', // used in heroku.
    //'hideFunctionalityFile',
    //'timezone',
    //'passfile',
    'sitename',
    'customScript',
    'customCss',
    'customTheme',
    //'theme',
    //'Drive_ver',
    //'Drive_custom',
    //'client_id',
    'client_secret',
    'domain_path',
    'guestup_path',
    //'usesharepoint',
    'sharepointSiteAddress',
    'shareurl',
    //'sharecookie',
    'shareapiurl',
    //'siteid',
    'domainforproxy',
    'public_path',
    //'refresh_token',
    //'token_expires',
];

$CommonEnv = [
    'APIKey', // used in heroku.
    'Region', // used in SCF.
    'SecretId', // used in SCF.
    'SecretKey', // used in SCF.
    'AccessKeyID', // used in FC.
    'AccessKeySecret', // used in FC.
    'HW_urn', // used in FG.
    'HW_key', // used in FG.
    'HW_secret', // used in FG.
    'admin',
    'adminloginpage',
    'background',
    'disktag',
    'disableShowThumb',
    'disableChangeTheme',
    'function_name', // used in heroku.
    'hideFunctionalityFile',
    'timezone',
    'passfile',
    'sitename',
    'customScript',
    'customCss',
    'customTheme',
    'theme',
];

$ShowedCommonEnv = [
    //'APIKey', // used in heroku.
    //'Region', // used in SCF.
    //'SecretId', // used in SCF.
    //'SecretKey', // used in SCF.
    //'AccessKeyID', // used in FC.
    //'AccessKeySecret', // used in FC.
    //'HW_urn', // used in FG.
    //'HW_key', // used in FG.
    //'HW_secret', // used in FG.
    //'admin',
    'adminloginpage',
    'background',
    //'disktag',
    'disableShowThumb',
    'disableChangeTheme',
    //'function_name', // used in heroku.
    'hideFunctionalityFile',
    'timezone',
    'passfile',
    'sitename',
    'customScript',
    'customCss',
    'customTheme',
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
    'sharepointSiteAddress',
    'siteid',
    'shareurl',
    //'sharecookie',
    'shareapiurl',
    'domainforproxy',
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
    //'sharepointSiteAddress',
    //'siteid',
    //'shareurl',
    //'sharecookie',
    //'shareapiurl',
    'domainforproxy',
    'public_path',
    //'refresh_token',
    //'token_expires',
];

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

function main($path)
{
    global $exts;
    global $constStr;

    $_SERVER['php_starttime'] = microtime(true);
    $path = path_format($path);
    if (in_array($_SERVER['firstacceptlanguage'], array_keys($constStr['languages']))) {
        $constStr['language'] = $_SERVER['firstacceptlanguage'];
    } else {
        $prelang = splitfirst($_SERVER['firstacceptlanguage'], '-')[0];
        foreach ( array_keys($constStr['languages']) as $lang) {
            if ($prelang == splitfirst($lang, '-')[0]) {
                $constStr['language'] = $lang;
                break;
            }
        }
    }
    if (isset($_COOKIE['language'])&&$_COOKIE['language']!='') $constStr['language'] = $_COOKIE['language'];
    //if (!$constStr['language']) $constStr['language'] = getConfig('language');
    /*echo 'firstacceptlanguage:'.$_SERVER['firstacceptlanguage'].'
    '.'lan:'.$constStr['language'];*/
    if ($constStr['language']=='') $constStr['language'] = 'en-us';
    $_SERVER['language'] = $constStr['language'];
    $_SERVER['timezone'] = getConfig('timezone');
    if (isset($_COOKIE['timezone'])&&$_COOKIE['timezone']!='') $_SERVER['timezone'] = $_COOKIE['timezone'];
    if ($_SERVER['timezone']=='') $_SERVER['timezone'] = 0;
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
        if ( isset($_COOKIE['admin'])&&$_COOKIE['admin']==md5(getConfig('admin')) ) {
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
        $_SERVER['disktag'] = splitfirst( substr(path_format($path), 1), '/' )[0];
        //$pos = strpos($path, '/');
        //if ($pos>1) $_SERVER['disktag'] = substr($path, 0, $pos);
        if (!in_array($_SERVER['disktag'], $disktags)) {
            $tmp = path_format($_SERVER['base_path'].'/'.$disktags[0].'/'.$path);
            return output('Please visit <a href="'.$tmp.'">'.$tmp.'</a>.', 302, [ 'Location' => $tmp ]);
            //return message('<meta http-equiv="refresh" content="2;URL='.$_SERVER['base_path'].'">Please visit from <a href="'.$_SERVER['base_path'].'">Home Page</a>.', 'Error', 404);
        }
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
            if ($_GET['action']=='del_upload_cache') {
                // del '.tmp' without login. 无需登录即可删除.tmp后缀文件
                error_log('del.tmp:GET,'.json_encode($_GET,JSON_PRETTY_PRINT));
                $tmp = splitlast($_GET['filename'], '/');
                if ($tmp[1]!='') {
                    $filename = $tmp[0] . '/.' . $_GET['filelastModified'] . '_' . $_GET['filesize'] . '_' . $tmp[1] . '.tmp';
                } else {
                    $filename = '.' . $_GET['filelastModified'] . '_' . $_GET['filesize'] . '_' . $_GET['filename'] . '.tmp';
                }
                $filename = path_format( path_format($_SERVER['list_path'] . path_format($path)) . '/' . spurlencode($filename, '/') );
                $tmp = MSAPI('DELETE', $filename, '', $_SERVER['access_token']);
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
                //$oldname = path_format(path_format($_SERVER['list_path'] . path_format($path)) . '/' . $oldname . '.scfupload' );
                $oldname = path_format(path_format($_SERVER['list_path'] . path_format($path)) . '/' . $oldname);
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
                    return get_thumbnails_url($path, $_GET['location']);
                } else return output(json_encode($exts['img']),400);
            } else return output('',401);
        }

        $files = list_files($path);
        //echo json_encode(array_keys($files['children']), JSON_PRETTY_PRINT);
        if ($_GET['json']) {
            // return a json
            return files_json($files);
        }
        if (isset($_GET['random'])&&$_GET['random']!=='') {
            if ($_SERVER['ishidden']<4) {
                $tmp = [];
                foreach (array_keys($files['children']) as $filename) {
                    if (strtolower(splitlast($filename,'.')[1])==strtolower($_GET['random'])) $tmp[$filename] = $files['children'][$filename][$_SERVER['DownurlStrName']];
                }
                $tmp = array_values($tmp);
                if (count($tmp)>0) {
                    $url = $tmp[rand(0,count($tmp)-1)];
                    if (isset($_GET['url'])) return output($url, 200);
                    $domainforproxy = '';
                    $domainforproxy = getConfig('domainforproxy');
                    if ($domainforproxy!='') {
                        $url = proxy_replace_domain($url, $domainforproxy);
                    }
                    return output('', 302, [ 'Location' => $url ]);
                } else return output('',404);
            } else return output('',401);
        }
        if (isset($files['file']) && !isset($_GET['preview'])) {
            // is file && not preview mode
            if ( $_SERVER['ishidden']<4 || (!!getConfig('downloadencrypt')&&$files['name']!=getConfig('passfile')) ) {
                $url = $files[$_SERVER['DownurlStrName']];
                $domainforproxy = '';
                $domainforproxy = getConfig('domainforproxy');
                if ($domainforproxy!='') {
                    $url = proxy_replace_domain($url, $domainforproxy);
                }
                if ( strtolower(splitlast($files['name'],'.')[1])=='html' ) return output($files['content']['body'], $files['content']['stat']);
                else return output('', 302, [ 'Location' => $url ]);
            }
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

function proxy_replace_domain($url, $domainforproxy)
{
    $tmp = splitfirst($url, '//');
    $http = $tmp[0];
    $tmp = splitfirst($tmp[1], '/');
    $domain = $tmp[0];
    $uri = $tmp[1];
    if (substr($domainforproxy, 0, 7)=='http://' || substr($domainforproxy, 0, 8)=='https://') $aim = $domainforproxy;
    else $aim = $http . '//' . $domainforproxy;
    if (substr($aim, -1)=='/') $aim = substr($aim, 0, -1);
    return $aim . '/' . $uri . '&Origindomain=' . $domain;
    //$url = str_replace($tmp, $domainforproxy, $url).'&Origindomain='.$tmp;
}

function files_json($files)
{
    //$tmp = '';
    if (isset($files['file'])) {
        $tmp['file']['type'] = 0;
        $tmp['file']['id'] = $files['id'];
        $tmp['file']['name'] = $files['name'];
        $tmp['file']['time'] = $files['lastModifiedDateTime'];
        $tmp['file']['size'] = $files['size'];
        $tmp['file']['mime'] = $files['file']['mimeType'];
        $tmp['file']['url'] = $files[$_SERVER['DownurlStrName']];
        $tmp['url'] = $files[$_SERVER['DownurlStrName']];
    } elseif (isset($files['folder'])) {
        $tmp['list'] = [];
        foreach ($files['children'] as $file) {
            $tmp1 = null;
            $tmp1 = [];
            if (isset($file['file'])) {
                $tmp1['type'] = 0;
                $tmp1['url'] = $file[$_SERVER['DownurlStrName']];
            } elseif (isset($file['folder'])) {
                $tmp1['type'] = 1;
            }
            $tmp1['id'] = $file['id'];
            $tmp1['name'] = $file['name'];
            $tmp1['time'] = $file['lastModifiedDateTime'];
            $tmp1['size'] = $file['size'];
            $tmp1['mime'] = $file['file']['mimeType'];
            array_push($tmp['list'], $tmp1);
        }
    } else return output('', 404);
    return output(json_encode($tmp));
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
        $tmp = $ret;
        $tmp['access_token'] = '******';
        error_log('['.$_SERVER['disktag'].'] Get access token:'.json_encode($tmp, JSON_PRETTY_PRINT));
        savecache('access_token', $_SERVER['access_token']);
        $tmp1 = [];
        $tmp1['shareapiurl'] = $_SERVER['api_url'];
        if (getConfig('shareapiurl')=='') setConfig($tmp1);
    } else {
        $p=0;
        while ($response['stat']==0&&$p<3) {
            $response = curl_request( $_SERVER['oauth_url'] . 'token', 'client_id='. $_SERVER['client_id'] .'&client_secret='. $_SERVER['client_secret'] .'&grant_type=refresh_token&requested_token_use=on_behalf_of&refresh_token=' . $refresh_token );
            $p++;
        }
        if ($response['stat']==200) $ret = json_decode($response['body'], true);
        if (!isset($ret['access_token'])) {
            error_log($_SERVER['oauth_url'] . 'token'.'?client_id='. $_SERVER['client_id'] .'&client_secret='. $_SERVER['client_secret'] .'&grant_type=refresh_token&requested_token_use=on_behalf_of&refresh_token=' . substr($refresh_token, 0, 20) . '******' . substr($refresh_token, -20));
            error_log('failed to get ['.$_SERVER['disktag'].'] access_token. response' . json_encode($ret));
            throw new Exception($response['stat'].', failed to get ['.$_SERVER['disktag'].'] access_token.'.$response['body']);
        }
        $tmp = $ret;
        $tmp['access_token'] = '******';
        $tmp['refresh_token'] = '******';
        error_log('['.$_SERVER['disktag'].'] Get access token:'.json_encode($tmp, JSON_PRETTY_PRINT));
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
        'head.omf',
        'foot.omf',
        'favicon.ico',
        'index.html',
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
    $sharepointSiteAddress = getConfig('sharepointSiteAddress');
    while (substr($sharepointSiteAddress, -1)=='/') $sharepointSiteAddress = substr($sharepointSiteAddress, 0, -1);
    $tmp = splitlast($sharepointSiteAddress, '/');
    $sharepointname = $tmp[1];
    $tmp = splitlast($tmp[0], '/');
    $sharepointname = $tmp[1] . '/' . $sharepointname;
    if (getConfig('Drive_ver')=='MS') $url = 'https://graph.microsoft.com/v1.0/sites/root:/'.$sharepointname;
    if (getConfig('Drive_ver')=='CN') $url = 'https://microsoftgraph.chinacloudapi.cn/v1.0/sites/root:/'.$sharepointname;

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
        date_default_timezone_set(get_timezone($_SERVER['timezone']));
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
    global $timezones;
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
    //$github_version = file_get_contents('https://raw.githubusercontent.com/qkqpttgf/OneManager-php/master/version');
    $tmp = curl_request('https://raw.githubusercontent.com/qkqpttgf/OneManager-php/master/version');
    if ($tmp['stat']==0) return 0;
    $github_version = $tmp['body'];
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

function get_thumbnails_url($path = '/', $location = 0)
{
    $path1 = path_format($path);
    $path = path_format($_SERVER['list_path'] . path_format($path));
    if ($path!='/'&&substr($path,-1)=='/') $path=substr($path,0,-1);
    $thumb_url = getcache('thumb_'.$path);
    if ($thumb_url=='') {
        $url = $_SERVER['api_url'];
        if ($path !== '/') {
            $url .= ':' . $path;
            if (substr($url,-1)=='/') $url=substr($url,0,-1);
        }
        $url .= ':/thumbnails/0/medium';
        $files = json_decode(curl_request($url, false, ['Authorization' => 'Bearer ' . $_SERVER['access_token']])['body'], true);
        if (isset($files['url'])) {
            savecache('thumb_'.$path, $files['url']);
            $thumb_url = $files['url'];
        }
    }
    if ($thumb_url!='') {
        if ($location) {
            $url = $thumb_url;
            $domainforproxy = '';
            $domainforproxy = getConfig('domainforproxy');
            if ($domainforproxy!='') {
                $url = proxy_replace_domain($url, $domainforproxy);
            }
            return output('', 302, [ 'Location' => $url ]);
        } else return output($thumb_url);
    }
    return output('', 404);
}

function bigfileupload($path)
{
    $path1 = path_format($_SERVER['list_path'] . path_format($path));
    if (substr($path1,-1)=='/') $path1=substr($path1,0,-1);
    if ($_GET['upbigfilename']!=''&&$_GET['filesize']>0) {
        $tmp = splitlast($_GET['upbigfilename'], '/');
        if ($tmp[1]!='') {
            $fileinfo['name'] = $tmp[1];
            $fileinfo['path'] = $tmp[0];
        } else {
            $fileinfo['name'] = $_GET['upbigfilename'];
        }
        $fileinfo['size'] = $_GET['filesize'];
        $fileinfo['lastModified'] = $_GET['lastModified'];
        $filename = spurlencode($_GET['upbigfilename'],'/');
        $cachefilename = spurlencode( $fileinfo['path'] . '/.' . $fileinfo['lastModified'] . '_' . $fileinfo['size'] . '_' . $fileinfo['name'] . '.tmp', '/');
        $getoldupinfo=fetch_files(path_format($path . '/' . $cachefilename));
        //echo json_encode($getoldupinfo, JSON_PRETTY_PRINT);
        if (isset($getoldupinfo['file'])&&$getoldupinfo['size']<5120) {
            $getoldupinfo_j = curl_request($getoldupinfo[$_SERVER['DownurlStrName']]);
            $getoldupinfo = json_decode($getoldupinfo_j['body'], true);
            if ( json_decode( curl_request($getoldupinfo['uploadUrl'])['body'], true)['@odata.context']!='' ) return output($getoldupinfo_j['body'], $getoldupinfo_j['stat']);
        }
        //if (!$_SERVER['admin']) $filename = spurlencode( $fileinfo['name'] ) . '.scfupload';
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
    if (isset($_GET['operate_action'])&&$_GET['operate_action']==getconstStr('Encrypt')) {
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
        savecache('customTheme', '', 1);
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
        $url .= '?expand=children(select=id,name,size,file,folder,parentReference,lastModifiedDateTime,'.$_SERVER['DownurlStrName'].')';
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
                    /*$url = $_SERVER['api_url'];
                    if ($path !== '/') {
                        $url .= ':' . $path;
                        if (substr($url,-1)=='/') $url=substr($url,0,-1);
                        $url .= ':/children?$top=9999&$select=id,name,size,file,folder,parentReference,lastModifiedDateTime,'.$_SERVER['DownurlStrName'];
                    } else {
                        $url .= '/children?$top=9999&$select=id,name,size,file,folder,parentReference,lastModifiedDateTime,'.$_SERVER['DownurlStrName'];
                    }
                    $children = json_decode(curl_request($url, false, ['Authorization' => 'Bearer ' . $_SERVER['access_token']])['body'], true);
                    $files['children'] = $children['value'];*/
                } else {
                // files num < 200 , then cache
                    //if (isset($files['children'])) {
                        $files['children'] = children_name($files['children']);
                    //}
                    savecache('path_' . $path, $files);
                }
            }
            if (isset($files['file'])) {
                if (in_array(splitlast($files['name'],'.')[1], $exts['txt'])) {
                    if (!(isset($files['content'])&&$files['content']['stat']==200)) {
                        $content1 = curl_request($files[$_SERVER['DownurlStrName']]);
                        $files['content'] = $content1;
                        savecache('path_' . $path, $files);
                    }
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
                        $url .= ':/children?$select=id,name,size,file,folder,parentReference,lastModifiedDateTime,'.$_SERVER['DownurlStrName'];
                    } else {
                        $url .= '/children?$select=id,name,size,file,folder,parentReference,lastModifiedDateTime,'.$_SERVER['DownurlStrName'];
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
            return message('Something error, retry after a few seconds.', 'retry', 201);
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
                } else {
                    $tmp['Drive_custom'] = '';
                    $tmp['client_id'] = '';
                    $tmp['client_secret'] = '';
                }
                if ($_POST['usesharepoint']=='on') {
                    $tmp['usesharepoint'] = $_POST['usesharepoint'];
                    $tmp['sharepointSiteAddress'] = $_POST['sharepointSiteAddress'];
                } else {
                    $tmp['usesharepoint'] = '';
                    $tmp['sharepointSiteAddress'] = '';
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
            <label><input type="radio" name="Drive_ver" value="shareurl" onclick="document.getElementById(\'inputshareurl\').style.display=\'\';document.getElementById(\'morecustom\').style.display=\'none\';">ShareUrl: '.getconstStr('DriveVerShareurl').'</label><br>
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
                '.getconstStr('GetSharepointSiteAddress').'<br>
                <input type="text" name="sharepointSiteAddress" style="width:100%" placeholder="'.getconstStr('InputSharepointSiteAddress').'"><br>
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
                    if (t.sharepointSiteAddress.value==\'\') {
                        alert(\''.getconstStr('InputSharepointSiteAddress').'\');
                        return false;
                    }
                }
            }
            var expd = new Date();
            expd.setTime(expd.getTime()+(2*60*60*1000));
            var expires = "expires="+expd.toGMTString();
            document.cookie=\'disktag=\'+t.disktag_add.value+\'; path=/; \'+expires;
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
    global $timezones;
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
        if ($key=='timezone') {
            $html .= '
        <tr>
            <td><label>' . $key . '</label></td>
            <td width=100%>
                <select name="' . $key .'">';
            foreach (array_keys($timezones) as $zone) {
                $html .= '
                    <option value="'.$zone.'" '.($zone==getConfig($key)?'selected="selected"':'').'>'.$zone.'</option>';
            }
            $html .= '
                </select>
                '.getconstStr('EnvironmentsDescription')[$key].'
            </td>
        </tr>';
        } elseif ($key=='theme') {
            $theme_arr = scandir(__DIR__.'/theme');
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
                '.getconstStr('EnvironmentsDescription')[$key].'
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
            <td width=100%><input type="text" name="' . $key .'" value="' . htmlspecialchars(getConfig($key)) . '" placeholder="' . getconstStr('EnvironmentsDescription')[$key] . '" style="width:100%"></td>
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
            } else {
                $html .= '
    <tr>
        <td colspan="2">Please add this disk again.</td>
    </tr>';
            }
            $html .= '
</table><br>';
        }
    }
    $html .= '
<a href="?AddDisk">'.getconstStr('AddDisk').'</a><br><br>';

    $canOneKeyUpate = 0;
    if (isset($_SERVER['USER'])&&$_SERVER['USER']==='qcloud') {
        $canOneKeyUpate = 1;
    } elseif (isset($_SERVER['HEROKU_APP_DIR'])&&$_SERVER['HEROKU_APP_DIR']==='/app') {
        $canOneKeyUpate = 1;
    } elseif (isset($_SERVER['FC_SERVER_PATH'])&&$_SERVER['FC_SERVER_PATH']==='/var/fc/runtime/php7.2') {
        $canOneKeyUpate = 1;
    } elseif ($_SERVER['_APP_SHARE_DIR']==='/var/share/CFF/processrouter') {
        $canOneKeyUpate = 1;
    } else {
        $tmp = time();
        if ( mkdir(''.$tmp, 0777) ) {
            rmdir(''.$tmp);
            $canOneKeyUpate = 1;
        }
    }
    if (!$canOneKeyUpate) {
        $html .= '
'.getconstStr('CannotOneKeyUpate').'<br>';
    } else {
        $html .= '
<form name="updateform" action="" method="post">
    <input type="text" name="auth" size="6" placeholder="auth" value="qkqpttgf">
    <input type="text" name="project" size="12" placeholder="project" value="OneManager-php">
    <button name="QueryBranchs" onclick="querybranchs();return false">'.getconstStr('QueryBranchs').'</button>
    <select name="branch">
        <option value="master">master</option>
    </select>
    <input type="submit" name="updateProgram" value="'.getconstStr('updateProgram').'">
</form>
<script>
    function querybranchs()
    {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "https://api.github.com/repos/"+document.updateform.auth.value+"/"+document.updateform.project.value+"/branches");
        //xhr.setRequestHeader("User-Agent","qkqpttgf/OneManager");
        xhr.send(null);
        xhr.onload = function(e){
            console.log(xhr.responseText+","+xhr.status);
            if (xhr.status==200) {
                document.updateform.branch.options.length=0;
                JSON.parse(xhr.responseText).forEach( function (e) {
                    document.updateform.branch.options.add(new Option(e.name,e.name));
                    if ("master"==e.name) document.updateform.branch.options[document.updateform.branch.options.length-1].selected = true; 
                });
                document.updateform.QueryBranchs.style.display="none";
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
    }/* else {
        $html .= getconstStr('NotNeedUpdate');
    }*/
    return message($html, getconstStr('Setup'));
}

function render_list($path = '', $files = '')
{
    global $exts;
    global $constStr;

    if (isset($files['children']['index.html']) && !$_SERVER['admin']) {
        $htmlcontent = fetch_files(spurlencode(path_format(urldecode($path) . '/index.html'),'/'))['content'];
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
            $n_path = $pretitle;
            $tmp = splitlast(splitlast($path,'/')[0],'/');
            if ($tmp[1]=='') {
                $p_path = $tmp[0];
            } else {
                $p_path = $tmp[1];
            }
        } else {
            if (substr($path, 0, 1)=='/') $pretitle = substr($path, 1);
            if (substr($path, -1)=='/') $pretitle = substr($pretitle, 0, -1);
            $tmp=splitlast($pretitle,'/');
            if ($tmp[1]=='') {
                $n_path = $tmp[0];
            } else {
                $n_path = $tmp[1];
                $tmp = splitlast($tmp[0],'/');
                if ($tmp[1]=='') {
                    $p_path = $tmp[0];
                } else {
                    $p_path = $tmp[1];
                }
            }
        }
    } else {
      $pretitle = getconstStr('Home');
      $n_path=$pretitle;
    }
    $n_path=str_replace('&amp;','&',$n_path);
    $p_path=str_replace('&amp;','&',$p_path);
    $pretitle = str_replace('%23','#',$pretitle);
    $statusCode=200;
    date_default_timezone_set(get_timezone($_SERVER['timezone']));
    $authinfo = '<!--
    OneManager: An index & manager of Onedrive auth by ysun.
    Github: https://github.com/qkqpttgf/OneManager-php
-->';
    //$authinfo = $path . '<br><pre>' . json_encode($files, JSON_PRETTY_PRINT) . '</pre>';

    if (isset($_COOKIE['theme'])&&$_COOKIE['theme']!='') $theme = $_COOKIE['theme'];
    if ( !file_exists(__DIR__.'/theme/'.$theme) ) $theme = '';
    if ( $theme=='' ) {
        $tmp = getConfig('customTheme');
        if ( $tmp!='' ) $theme = $tmp;
    }
    if ( $theme=='' ) {
        $theme = getConfig('theme');
        if ( $theme=='' || !file_exists(__DIR__.'/theme/'.$theme) ) $theme = 'classic.html';
    }
    if (substr($theme,-4)=='.php') {
        @ob_start();
        include 'theme/'.$theme;
        $html = ob_get_clean();
    } else {
        if (file_exists(__DIR__.'/theme/'.$theme)) {
            $file_path = __DIR__.'/theme/'.$theme;
            $html = file_get_contents($file_path);
        } else {
            if (!($html = getcache('customTheme'))) {
                $file_path = $theme;
                $tmp = curl_request($file_path, false, [], 1);
                if ($tmp['stat']==302) {
                    error_log(json_encode($tmp));
                    $tmp = curl_request($tmp["returnhead"]["Location"]);
                }
                if (!!$tmp['body']) $html = $tmp['body'];
                savecache('customTheme', $html, 9999);
            }
            
        }

        $tmp = splitfirst($html, '<!--IconValuesStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--IconValuesEnd-->');
        $IconValues = json_decode($tmp[0], true);
        $html .= $tmp[1];

        if (!$files) {
            //$html = '<pre>'.json_encode($files, JSON_PRETTY_PRINT).'</pre>' . $html;
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--IsFileStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--IsFileEnd-->');
                $html .= $tmp[1];
            }
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--IsFolderStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--IsFolderEnd-->');
                $html .= $tmp[1];
            }
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--ListStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--ListEnd-->');
                $html .= $tmp[1];
            }
            while (strpos($html, '<!--GuestUploadStart-->')) {
                $tmp = splitfirst($html, '<!--GuestUploadStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--GuestUploadEnd-->');
                $html .= $tmp[1];
            }
            while (strpos($html, '<!--EncryptedStart-->')) {
                $tmp = splitfirst($html, '<!--EncryptedStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--EncryptedEnd-->');
                $html .= $tmp[1];
            }
        }
        if ($_SERVER['admin']) {
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--LoginStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--LoginEnd-->');
                $html .= $tmp[1];
            }
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--GuestStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--GuestEnd-->');
                $html .= $tmp[1];
            }
            while (strpos($html, '<!--AdminStart-->')) {
                $html = str_replace('<!--AdminStart-->', '', $html);
                $html = str_replace('<!--AdminEnd-->', '', $html);
            }
            while (strpos($html, '<!--constStr@Operate-->')) $html = str_replace('<!--constStr@Operate-->', getconstStr('Operate'), $html);
            while (strpos($html, '<!--constStr@Create-->')) $html = str_replace('<!--constStr@Create-->', getconstStr('Create'), $html);
            while (strpos($html, '<!--constStr@Encrypt-->')) $html = str_replace('<!--constStr@Encrypt-->', getconstStr('Encrypt'), $html);
            while (strpos($html, '<!--constStr@RefreshCache-->')) $html = str_replace('<!--constStr@RefreshCache-->', getconstStr('RefreshCache'), $html);
            while (strpos($html, '<!--constStr@Setup-->')) $html = str_replace('<!--constStr@Setup-->', getconstStr('Setup'), $html);
            while (strpos($html, '<!--constStr@Logout-->')) $html = str_replace('<!--constStr@Logout-->', getconstStr('Logout'), $html);
            while (strpos($html, '<!--constStr@Rename-->')) $html = str_replace('<!--constStr@Rename-->', getconstStr('Rename'), $html);
            while (strpos($html, '<!--constStr@Submit-->')) $html = str_replace('<!--constStr@Submit-->', getconstStr('Submit'), $html);
            while (strpos($html, '<!--constStr@Delete-->')) $html = str_replace('<!--constStr@Delete-->', getconstStr('Delete'), $html);
            while (strpos($html, '<!--constStr@Copy-->')) $html = str_replace('<!--constStr@Copy-->', getconstStr('Copy'), $html);
            while (strpos($html, '<!--constStr@Move-->')) $html = str_replace('<!--constStr@Move-->', getconstStr('Move'), $html);
            while (strpos($html, '<!--constStr@Folder-->')) $html = str_replace('<!--constStr@Folder-->', getconstStr('Folder'), $html);
            while (strpos($html, '<!--constStr@File-->')) $html = str_replace('<!--constStr@File-->', getconstStr('File'), $html);
            while (strpos($html, '<!--constStr@Name-->')) $html = str_replace('<!--constStr@Name-->', getconstStr('Name'), $html);
            while (strpos($html, '<!--constStr@Content-->')) $html = str_replace('<!--constStr@Content-->', getconstStr('Content'), $html);
            
        } else {
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--AdminStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--AdminEnd-->');
                $html .= $tmp[1];
            }
            if (getConfig('adminloginpage')=='') {
                while (strpos($html, '<!--LoginStart-->')) $html = str_replace('<!--LoginStart-->', '', $html);
                while (strpos($html, '<!--LoginEnd-->')) $html = str_replace('<!--LoginEnd-->', '', $html);
            } else {
                $tmp[1] = 'a';
                while ($tmp[1]!='') {
                    $tmp = splitfirst($html, '<!--LoginStart-->');
                    $html = $tmp[0];
                    $tmp = splitfirst($tmp[1], '<!--LoginEnd-->');
                    $html .= $tmp[1];
                }
            }
            while (strpos($html, '<!--GuestStart-->')) $html = str_replace('<!--GuestStart-->', '', $html);
            while (strpos($html, '<!--GuestEnd-->')) $html = str_replace('<!--GuestEnd-->', '', $html);
        }

        if ($_SERVER['ishidden']==4) {
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--IsFileStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--IsFileEnd-->');
                $html .= $tmp[1];
            }
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--IsFolderStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--IsFolderEnd-->');
                $html .= $tmp[1];
            }
            /*$tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--ListStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--ListEnd-->');
                $html .= $tmp[1];
            }*/
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--IsNotHiddenStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--IsNotHiddenEnd-->');
                $html .= $tmp[1];
            }
            while (strpos($html, '<!--EncryptedStart-->')) {
                $html = str_replace('<!--EncryptedStart-->', '', $html);
                $html = str_replace('<!--EncryptedEnd-->', '', $html);
            }
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--GuestUploadStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--GuestUploadEnd-->');
                $html .= $tmp[1];
            }
            while (strpos($html, '<!--IsNotHiddenStart-->')) {
                $tmp = splitfirst($html, '<!--IsNotHiddenStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--IsNotHiddenEnd-->');
                $html .= $tmp[1];
            }
        } else {
            while (strpos($html, '<!--EncryptedStart-->')) {
                $tmp = splitfirst($html, '<!--EncryptedStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--EncryptedEnd-->');
                $html .= $tmp[1];
            }
            while (strpos($html, '<!--IsNotHiddenStart-->')) {
                $html = str_replace('<!--IsNotHiddenStart-->', '', $html);
                $html = str_replace('<!--IsNotHiddenEnd-->', '', $html);
            }
        }
        while (strpos($html, '<!--constStr@Download-->')) $html = str_replace('<!--constStr@Download-->', getconstStr('Download'), $html);

        if ($_SERVER['is_guestup_path']&&!$_SERVER['admin']) {
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--IsFileStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--IsFileEnd-->');
                $html .= $tmp[1];
            }
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--IsFolderStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--IsFolderEnd-->');
                $html .= $tmp[1];
            }
            while (strpos($html, '<!--GuestUploadStart-->')) {
                $html = str_replace('<!--GuestUploadStart-->', '', $html);
                $html = str_replace('<!--GuestUploadEnd-->', '', $html);
            }
            while (strpos($html, '<!--IsNotHiddenStart-->')) {
                $tmp = splitfirst($html, '<!--IsNotHiddenStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--IsNotHiddenEnd-->');
                $html .= $tmp[1];
            }
        } else {
            while (strpos($html, '<!--GuestUploadStart-->')) {
                $tmp = splitfirst($html, '<!--GuestUploadStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--GuestUploadEnd-->');
                $html .= $tmp[1];
            }
            while (strpos($html, '<!--IsNotHiddenStart-->')) {
                $html = str_replace('<!--IsNotHiddenStart-->', '', $html);
                $html = str_replace('<!--IsNotHiddenEnd-->', '', $html);
            }
        }
        if ($_SERVER['is_guestup_path']||( $_SERVER['admin']&&isset($files['folder'])&&$_SERVER['ishidden']<4 )) {
            while (strpos($html, '<!--UploadJsStart-->')) {
                while (strpos($html, '<!--UploadJsStart-->')) $html = str_replace('<!--UploadJsStart-->', '', $html);
                while (strpos($html, '<!--UploadJsEnd-->')) $html = str_replace('<!--UploadJsEnd-->', '', $html);
                while (strpos($html, '<!--constStr@Calculate-->')) $html = str_replace('<!--constStr@Calculate-->', getconstStr('Calculate'), $html);
            }
        } else {
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--UploadJsStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--UploadJsEnd-->');
                $html .= $tmp[1];
            }
        }

        if (isset($files['file'])) {
            while (strpos($html, '<!--GuestUploadStart-->')) {
                $tmp = splitfirst($html, '<!--GuestUploadStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--GuestUploadEnd-->');
                $html .= $tmp[1];
            }
            $tmp = splitfirst($html, '<!--EncryptedStart-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--EncryptedEnd-->');
            $html .= $tmp[1];

            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--IsFolderStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--IsFolderEnd-->');
                $html .= $tmp[1];
            }
            while (strpos($html, '<!--IsFileStart-->')) {
                $html = str_replace('<!--IsFileStart-->', '', $html);
                $html = str_replace('<!--IsFileEnd-->', '', $html);
            }
            $html = str_replace('<!--FileEncodeUrl-->', str_replace('%2523', '%23', str_replace('%26amp%3B','&amp;',spurlencode(path_format($_SERVER['base_disk_path'] . '/' . $path), '/'))), $html);
            $html = str_replace('<!--FileUrl-->', path_format($_SERVER['base_disk_path'] . '/' . $path), $html);
            
            $ext = strtolower(substr($path, strrpos($path, '.') + 1));
            if (in_array($ext, $exts['img'])) $ext = 'img';
            elseif (in_array($ext, $exts['video'])) $ext = 'video';
            elseif (in_array($ext, $exts['music'])) $ext = 'music';
            //elseif (in_array($ext, $exts['pdf'])) $ext = 'pdf';
            elseif ($ext=='pdf') $ext = 'pdf';
            elseif (in_array($ext, $exts['office'])) $ext = 'office';
            elseif (in_array($ext, $exts['txt'])) $ext = 'txt';
            else $ext = 'Other';
            $previewext = ['img', 'video', 'music', 'pdf', 'office', 'txt', 'Other'];
            $previewext = array_diff($previewext, [ $ext ]);
            foreach ($previewext as $ext1) {
                $tmp[1] = 'a';
                while ($tmp[1]!='') {
                    $tmp = splitfirst($html, '<!--Is'.$ext1.'FileStart-->');
                    $html = $tmp[0];
                    $tmp = splitfirst($tmp[1], '<!--Is'.$ext1.'FileEnd-->');
                    $html .= $tmp[1];
                }
            }
            while (strpos($html, '<!--Is'.$ext.'FileStart-->')) {
                $html = str_replace('<!--Is'.$ext.'FileStart-->', '', $html);
                $html = str_replace('<!--Is'.$ext.'FileEnd-->', '', $html);
            }
            //while (strpos($html, '<!--FileDownUrl-->')) $html = str_replace('<!--FileDownUrl-->', $files[$_SERVER['DownurlStrName']], $html);
            while (strpos($html, '<!--FileDownUrl-->')) $html = str_replace('<!--FileDownUrl-->', path_format($_SERVER['base_disk_path'] . '/' . $path), $html);
            while (strpos($html, '<!--FileEncodeReplaceUrl-->')) $html = str_replace('<!--FileEncodeReplaceUrl-->', path_format($_SERVER['base_disk_path'] . '/' . $path), $html);
            while (strpos($html, '<!--FileName-->')) $html = str_replace('<!--FileName-->', $files['name'], $html);
            $html = str_replace('<!--FileEncodeDownUrl-->', urlencode($files[$_SERVER['DownurlStrName']]), $html);
            $html = str_replace('<!--constStr@ClicktoEdit-->', getconstStr('ClicktoEdit'), $html);
            $html = str_replace('<!--constStr@CancelEdit-->', getconstStr('CancelEdit'), $html);
            $html = str_replace('<!--constStr@Save-->', getconstStr('Save'), $html);
            while (strpos($html, '<!--TxtContent-->')) $html = str_replace('<!--TxtContent-->', htmlspecialchars(curl_request($files[$_SERVER['DownurlStrName']])['body']), $html);
            $html = str_replace('<!--constStr@FileNotSupport-->', getconstStr('FileNotSupport'), $html);


            //$html = str_replace('<!--constStr@File-->', getconstStr('File'), $html);
        } elseif (isset($files['children'])) {
            while (strpos($html, '<!--GuestUploadStart-->')) {
                $tmp = splitfirst($html, '<!--GuestUploadStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--GuestUploadEnd-->');
                $html .= $tmp[1];
            }
            $tmp = splitfirst($html, '<!--EncryptedStart-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--EncryptedEnd-->');
            $html .= $tmp[1];
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--IsFileStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--IsFileEnd-->');
                $html .= $tmp[1];
            }
            while (strpos($html, '<!--IsFolderStart-->')) {
                $html = str_replace('<!--IsFolderStart-->', '', $html);
                $html = str_replace('<!--IsFolderEnd-->', '', $html);
            }
            $html = str_replace('<!--constStr@File-->', getconstStr('File'), $html);
            $html = str_replace('<!--constStr@ShowThumbnails-->', getconstStr('ShowThumbnails'), $html);
            $html = str_replace('<!--constStr@CopyAllDownloadUrl-->', getconstStr('CopyAllDownloadUrl'), $html);
            $html = str_replace('<!--constStr@EditTime-->', getconstStr('EditTime'), $html);
            $html = str_replace('<!--constStr@Size-->', getconstStr('Size'), $html);

            $filenum = 0;

            $tmp = splitfirst($html, '<!--FolderListStart-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--FolderListEnd-->');
            $FolderList = $tmp[0];
            foreach ($files['children'] as $file) {
                if (isset($file['folder'])) {
                    if ($_SERVER['admin'] or !isHideFile($file['name'])) {
                        $filenum++;
                        $FolderListStr = str_replace('<!--FileEncodeReplaceUrl-->', path_format($_SERVER['base_disk_path'] . '/' . $path . '/' . encode_str_replace($file['name'])), $FolderList);
                        $FolderListStr = str_replace('<!--FileEncodeReplaceName-->', str_replace('&','&amp;', $file['name']), $FolderListStr);
                        $FolderListStr = str_replace('<!--lastModifiedDateTime-->', time_format($file['lastModifiedDateTime']), $FolderListStr);
                        $FolderListStr = str_replace('<!--size-->', size_format($file['size']), $FolderListStr);
                        while (strpos($FolderListStr, '<!--filenum-->')) $FolderListStr = str_replace('<!--filenum-->', $filenum, $FolderListStr);
                        $html .= $FolderListStr;
                    }
                }
            }
            $html .= $tmp[1];

            $tmp = splitfirst($html, '<!--FileListStart-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--FileListEnd-->');
            $FolderList = $tmp[0];
            foreach ($files['children'] as $file) {
                if (isset($file['file'])) {
                    if ($_SERVER['admin'] or !isHideFile($file['name'])) {
                        $filenum++;
                        $ext = strtolower(substr($file['name'], strrpos($file['name'], '.') + 1));
                        $FolderListStr = str_replace('<!--FileEncodeReplaceUrl-->', path_format($_SERVER['base_disk_path'] . '/' . $path . '/' . encode_str_replace($file['name'])), $FolderList);
                        $FolderListStr = str_replace('<!--FileExt-->', $ext, $FolderListStr);
                        if (in_array($ext, $exts['music'])) $FolderListStr = str_replace('<!--FileExtType-->', 'audio', $FolderListStr);
                        elseif (in_array($ext, $exts['video'])) $FolderListStr = str_replace('<!--FileExtType-->', 'iframe', $FolderListStr);
                        else $FolderListStr = str_replace('<!--FileExtType-->', '', $FolderListStr);
                        $FolderListStr = str_replace('<!--FileEncodeReplaceName-->', str_replace('&','&amp;', $file['name']), $FolderListStr);
                        //$FolderListStr = str_replace('<!--FileEncodeReplaceUrl-->', path_format($_SERVER['base_disk_path'] . '/' . $path . '/' . str_replace('&','&amp;', $file['name'])), $FolderListStr);
                        $FolderListStr = str_replace('<!--lastModifiedDateTime-->', time_format($file['lastModifiedDateTime']), $FolderListStr);
                        $FolderListStr = str_replace('<!--size-->', size_format($file['size']), $FolderListStr);
                        if (!!$IconValues) {
                            foreach ($IconValues as $key1 => $value1) {
                                if (isset($exts[$key1])&&in_array($ext, $exts[$key1])) {
                                    $FolderListStr = str_replace('<!--IconValue-->', $value1, $FolderListStr);
                                }
                                if ($ext==$key1) {
                                    $FolderListStr = str_replace('<!--IconValue-->', $value1, $FolderListStr);
                                }
                                //error_log('file:'.$file['name'].':'.$key1);
                                if (!strpos($FolderListStr, '<!--IconValue-->')) break;
                            }
                            if (strpos($FolderListStr, '<!--IconValue-->')) $FolderListStr = str_replace('<!--IconValue-->', $IconValues['default'], $FolderListStr);
                        }
                        while (strpos($FolderListStr, '<!--filenum-->')) $FolderListStr = str_replace('<!--filenum-->', $filenum, $FolderListStr);
                        $html .= $FolderListStr;
                    }
                }
            }
            $html .= $tmp[1];
            while (strpos($html, '<!--maxfilenum-->')) $html = str_replace('<!--maxfilenum-->', $filenum, $html);

            if ($files['folder']['childCount']>200) {
                while (strpos($html, '<!--MorePageStart-->')) $html = str_replace('<!--MorePageStart-->', '', $html);
                while (strpos($html, '<!--MorePageEnd-->')) $html = str_replace('<!--MorePageEnd-->', '', $html);
                
                $pagenum = $files['folder']['page'];
                if ($pagenum=='') $pagenum = 1;
                $maxpage = ceil($files['folder']['childCount']/200);

                if ($pagenum!=1) {
                    $html = str_replace('<!--PrePageStart-->', '', $html);
                    $html = str_replace('<!--PrePageEnd-->', '', $html);
                    $html = str_replace('<!--constStr@PrePage-->', getconstStr('PrePage'), $html);
                    $html = str_replace('<!--PrePageNum-->', $pagenum-1, $html);
                } else {
                    $tmp = splitfirst($html, '<!--PrePageStart-->');
                    $html = $tmp[0];
                    $tmp = splitfirst($tmp[1], '<!--PrePageEnd-->');
                    $html .= $tmp[1];
                }
                //$html .= json_encode($files['folder']);
                if ($pagenum!=$maxpage) {
                    $html = str_replace('<!--NextPageStart-->', '', $html);
                    $html = str_replace('<!--NextPageEnd-->', '', $html);
                    $html = str_replace('<!--constStr@NextPage-->', getconstStr('NextPage'), $html);
                    $html = str_replace('<!--NextPageNum-->', $pagenum+1, $html);
                } else {
                    $tmp = splitfirst($html, '<!--NextPageStart-->');
                    $html = $tmp[0];
                    $tmp = splitfirst($tmp[1], '<!--NextPageEnd-->');
                    $html .= $tmp[1];
                }
                $tmp = splitfirst($html, '<!--MorePageListNowStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--MorePageListNowEnd-->');
                $MorePageListNow = str_replace('<!--PageNum-->', $pagenum, $tmp[0]);
                $html .= $tmp[1];

                $tmp = splitfirst($html, '<!--MorePageListStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--MorePageListEnd-->');
                $MorePageList = $tmp[0];
                for ($page=1;$page<=$maxpage;$page++) {
                    if ($page == $pagenum) {
                        $MorePageListStr = $MorePageListNow;
                    } else {
                        $MorePageListStr = str_replace('<!--PageNum-->', $page, $MorePageList);
                        $MorePageListStr = str_replace('<!--PageNum-->', $page, $MorePageListStr);
                    }
                    $html .= $MorePageListStr;
                }
                $html .= $tmp[1];

                while (strpos($html, '<!--MaxPageNum-->')) $html = str_replace('<!--MaxPageNum-->', $maxpage, $html);

            } else {
                while (strpos($html, '<!--MorePageStart-->')) {
                    $tmp = splitfirst($html, '<!--MorePageStart-->');
                    $html = $tmp[0];
                    $tmp = splitfirst($tmp[1], '<!--MorePageEnd-->');
                    $html .= $tmp[1];
                }
            }
            
        }

        $html = str_replace('<!--constStr@language-->', $constStr['language'], $html);

        $title = $pretitle;
        if ($_SERVER['base_disk_path']!=$_SERVER['base_path']) {
            if (getConfig('diskname')!='') $diskname = getConfig('diskname');
            else $diskname = $_SERVER['disktag'];
            $title .= ' - ' . $diskname;
        }
        $title .= ' - ' . $_SERVER['sitename'];
        $html = str_replace('<!--Title-->', $title, $html);

        $keywords = $n_path;
        if ($p_path!='') $keywords .= ', ' . $p_path;
        if ($_SERVER['sitename']!='OneManager') $keywords .= ', ' . $_SERVER['sitename'] . ', OneManager';
        else $keywords .= ', OneManager';
        $html = str_replace('<!--Keywords-->', $keywords, $html);

        if ($_GET['preview']) {
            $description = $n_path.', '.getconstStr('Preview');//'Preview of '.
        } elseif (isset($files['folder'])) {
            $description = $n_path.', '.getconstStr('List');//'List of '.$n_path.'. ';
        }
        //$description .= 'In '.$_SERVER['sitename'];
        $html = str_replace('<!--Description-->', $description, $html);

        while (strpos($html, '<!--base_disk_path-->')) $html = str_replace('<!--base_disk_path-->', (substr($_SERVER['base_disk_path'],-1)=='/'?substr($_SERVER['base_disk_path'],0,-1):$_SERVER['base_disk_path']), $html);
        while (strpos($html, '<!--base_path-->')) $html = str_replace('<!--base_path-->', $_SERVER['base_path'], $html);
        while (strpos($html, '<!--Path-->')) $html = str_replace('<!--Path-->', str_replace('%23', '#', str_replace('&','&amp;', path_format($path.'/'))), $html);
        while (strpos($html, '<!--constStr@Home-->')) $html = str_replace('<!--constStr@Home-->', getconstStr('Home'), $html);

        $html = str_replace('<!--customCss-->', getConfig('customCss'), $html);
        $html = str_replace('<!--customScript-->', getConfig('customScript'), $html);
        
        while (strpos($html, '<!--constStr@Login-->')) $html = str_replace('<!--constStr@Login-->', getconstStr('Login'), $html);
        while (strpos($html, '<!--constStr@Close-->')) $html = str_replace('<!--constStr@Close-->', getconstStr('Close'), $html);
        while (strpos($html, '<!--constStr@InputPassword-->')) $html = str_replace('<!--constStr@InputPassword-->', getconstStr('InputPassword'), $html);
        while (strpos($html, '<!--constStr@InputPasswordUWant-->')) $html = str_replace('<!--constStr@InputPasswordUWant-->', getconstStr('InputPasswordUWant'), $html);
        while (strpos($html, '<!--constStr@Submit-->')) $html = str_replace('<!--constStr@Submit-->', getconstStr('Submit'), $html);
        while (strpos($html, '<!--constStr@Success-->')) $html = str_replace('<!--constStr@Success-->', getconstStr('Success'), $html);
        while (strpos($html, '<!--constStr@GetUploadLink-->')) $html = str_replace('<!--constStr@GetUploadLink-->', getconstStr('GetUploadLink'), $html);
        while (strpos($html, '<!--constStr@UpFileTooLarge-->')) $html = str_replace('<!--constStr@UpFileTooLarge-->', getconstStr('UpFileTooLarge'), $html);
        while (strpos($html, '<!--constStr@UploadStart-->')) $html = str_replace('<!--constStr@UploadStart-->', getconstStr('UploadStart'), $html);
        while (strpos($html, '<!--constStr@UploadStartAt-->')) $html = str_replace('<!--constStr@UploadStartAt-->', getconstStr('UploadStartAt'), $html);
        while (strpos($html, '<!--constStr@LastUpload-->')) $html = str_replace('<!--constStr@LastUpload-->', getconstStr('LastUpload'), $html);
        while (strpos($html, '<!--constStr@ThisTime-->')) $html = str_replace('<!--constStr@ThisTime-->', getconstStr('ThisTime'), $html);

        while (strpos($html, '<!--constStr@Upload-->')) $html = str_replace('<!--constStr@Upload-->', getconstStr('Upload'), $html);
        while (strpos($html, '<!--constStr@AverageSpeed-->')) $html = str_replace('<!--constStr@AverageSpeed-->', getconstStr('AverageSpeed'), $html);
        while (strpos($html, '<!--constStr@CurrentSpeed-->')) $html = str_replace('<!--constStr@CurrentSpeed-->', getconstStr('CurrentSpeed'), $html);
        while (strpos($html, '<!--constStr@Expect-->')) $html = str_replace('<!--constStr@Expect-->', getconstStr('Expect'), $html);
        while (strpos($html, '<!--constStr@UploadErrorUpAgain-->')) $html = str_replace('<!--constStr@UploadErrorUpAgain-->', getconstStr('UploadErrorUpAgain'), $html);
        while (strpos($html, '<!--constStr@EndAt-->')) $html = str_replace('<!--constStr@EndAt-->', getconstStr('EndAt'), $html);
        
        while (strpos($html, '<!--constStr@UploadComplete-->')) $html = str_replace('<!--constStr@UploadComplete-->', getconstStr('UploadComplete'), $html);
        while (strpos($html, '<!--constStr@CopyUrl-->')) $html = str_replace('<!--constStr@CopyUrl-->', getconstStr('CopyUrl'), $html);
        while (strpos($html, '<!--constStr@UploadFail23-->')) $html = str_replace('<!--constStr@UploadFail23-->', getconstStr('UploadFail23'), $html);
        while (strpos($html, '<!--constStr@GetFileNameFail-->')) $html = str_replace('<!--constStr@GetFileNameFail-->', getconstStr('GetFileNameFail'), $html);
        while (strpos($html, '<!--constStr@UploadFile-->')) $html = str_replace('<!--constStr@UploadFile-->', getconstStr('UploadFile'), $html);
        while (strpos($html, '<!--constStr@UploadFolder-->')) $html = str_replace('<!--constStr@UploadFolder-->', getconstStr('UploadFolder'), $html);
        while (strpos($html, '<!--constStr@FileSelected-->')) $html = str_replace('<!--constStr@FileSelected-->', getconstStr('FileSelected'), $html);
        while (strpos($html, '<!--IsPreview?-->')) $html = str_replace('<!--IsPreview?-->', (isset($_GET['preview'])?'?preview&':'?'), $html);

        $tmp = splitfirst($html, '<!--BackgroundStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--BackgroundEnd-->');
        if (getConfig('background')) {
            $background = str_replace('<!--BackgroundUrl-->', getConfig('background'), $tmp[0]);
        }
        $html .= $background . $tmp[1];

        $tmp = splitfirst($html, '<!--PathArrayStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--PathArrayEnd-->');
        $PathArrayStr = $tmp[0];
        $tmp_path = str_replace('%23', '#', str_replace('&','&amp;', $path));
        $tmp_url = $_SERVER['base_disk_path'];
        while ($tmp_path!='') {
            $tmp1 = splitfirst($tmp_path, '/');
            $folder1 = $tmp1[0];
            if ($folder1!='') {
                $tmp_url .= $folder1 . '/';
                $PathArrayStr1 = str_replace('<!--PathArrayLink-->', ($folder1==$files['name']?'':$tmp_url), $PathArrayStr);
                $PathArrayStr1 = str_replace('<!--PathArrayName-->', $folder1, $PathArrayStr1);
                $html .= $PathArrayStr1;
            }
            $tmp_path = $tmp1[1];
        }
        $html .= $tmp[1];
        
        $tmp = splitfirst($html, '<!--SelectLanguageStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--SelectLanguageEnd-->');
        $SelectLanguage = $tmp[0];
        foreach ($constStr['languages'] as $key1 => $value1) {
            $SelectLanguageStr = str_replace('<!--SelectLanguageKey-->', $key1, $SelectLanguage);
            $SelectLanguageStr = str_replace('<!--SelectLanguageValue-->', $value1, $SelectLanguageStr);
            $SelectLanguageStr = str_replace('<!--SelectLanguageSelected-->', ($key1==$constStr['language']?'selected="selected"':''), $SelectLanguageStr);
            $html .= $SelectLanguageStr;
        }
        $html .= $tmp[1];

        $tmp = splitfirst($html, '<!--NeedUpdateStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--NeedUpdateEnd-->');
        $NeedUpdateStr = $tmp[0];
        if (isset($_SERVER['needUpdate'])&&$_SERVER['needUpdate']) $NeedUpdateStr = str_replace('<!--constStr@NeedUpdate-->', getconstStr('NeedUpdate'), $NeedUpdateStr);
        else $NeedUpdateStr ='';
        $html .= $NeedUpdateStr . $tmp[1];
        
        $tmp = splitfirst($html, '<!--BackArrowStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--BackArrowEnd-->');
        if ($path !== '/') {
            $current_url = $_SERVER['PHP_SELF'];
            while (substr($current_url, -1) === '/') {
                $current_url = substr($current_url, 0, -1);
            }
            if (strpos($current_url, '/') !== FALSE) {
                $parent_url = substr($current_url, 0, strrpos($current_url, '/'));
            } else {
                $parent_url = $current_url;
            }
            $BackArrow = str_replace('<!--BackArrowUrl-->', $parent_url.'/', $tmp[0]);
        }
        $html .= $BackArrow . $tmp[1];

        $tmp[1] = 'a';
        while ($tmp[1]!='') {
            $tmp = splitfirst($html, '<!--ShowThumbnailsStart-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--ShowThumbnailsEnd-->');
            //if (!(isset($_SERVER['USER'])&&$_SERVER['USER']=='qcloud')) {
            if (!getConfig('disableShowThumb')) {
                $html .= str_replace('<!--constStr@OriginalPic-->', getconstStr('OriginalPic'), $tmp[0]) . $tmp[1];
            } else $html .= $tmp[1];
        }
        $imgextstr = '';
        foreach ($exts['img'] as $imgext) $imgextstr .= '\''.$imgext.'\', '; 
        $html = str_replace('<!--ImgExts-->', $imgextstr, $html);
        

        $html = str_replace('<!--Sitename-->', $_SERVER['sitename'], $html);

        $tmp = splitfirst($html, '<!--MultiDiskAreaStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--MultiDiskAreaEnd-->');
        $disktags = explode("|",getConfig('disktag'));
        if (count($disktags)>1) {
            $tmp1 = $tmp[1];
            $tmp = splitfirst($tmp[0], '<!--MultiDisksStart-->');
            $MultiDiskArea = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--MultiDisksEnd-->');
            $MultiDisks = $tmp[0];
            foreach ($disktags as $disk) {
                $diskname = getConfig('diskname', $disk);
                if ($diskname=='') $diskname = $disk;
                $MultiDisksStr = str_replace('<!--MultiDisksUrl-->', path_format($_SERVER['base_path'].'/'.$disk.'/'), $MultiDisks);
                $MultiDisksStr = str_replace('<!--MultiDisksNow-->', ($_SERVER['disktag']==$disk?' now':''), $MultiDisksStr);
                $MultiDisksStr = str_replace('<!--MultiDisksName-->', $diskname, $MultiDisksStr);
                $MultiDiskArea .= $MultiDisksStr;
            }
            $MultiDiskArea .= $tmp[1];
            $tmp[1] = $tmp1;
        }
        $html .= $MultiDiskArea . $tmp[1];
        $diskname = getConfig('diskname');
        if ($diskname=='') $diskname = $_SERVER['disktag'];
        //if (strlen($diskname)>15) $diskname = substr($diskname, 0, 12).'...';
        while (strpos($html, '<!--DiskNameNow-->')) $html = str_replace('<!--DiskNameNow-->', $diskname, $html);
        
        $tmp = splitfirst($html, '<!--HeadomfStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--HeadomfEnd-->');
        if (isset($files['children']['head.omf'])) {
            $headomf = str_replace('<!--HeadomfContent-->', fetch_files(spurlencode(path_format(urldecode($path) . '/head.omf'),'/'))['content']['body'], $tmp[0]);
        }
        $html .= $headomf . $tmp[1];
        
        $tmp = splitfirst($html, '<!--HeadmdStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--HeadmdEnd-->');
        if (isset($files['children']['head.md'])) {
            $headmd = str_replace('<!--HeadmdContent-->', fetch_files(spurlencode(path_format(urldecode($path) . '/head.md'),'/'))['content']['body'], $tmp[0]);
            $html .= $headmd . $tmp[1];
            while (strpos($html, '<!--HeadmdStart-->')) {
                $html = str_replace('<!--HeadmdStart-->', '', $html);
                $html = str_replace('<!--HeadmdEnd-->', '', $html);
            }
        } else {
            $html .= $tmp[1];
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--HeadmdStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--HeadmdEnd-->');
                $html .= $tmp[1];
            }
        }

        $tmp[1] = 'a';
        while ($tmp[1]!='') {
            $tmp = splitfirst($html, '<!--ListStart-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--ListEnd-->');
            $html_aft = $tmp[1];
            if ($files) {
                $listarea = $tmp[0];
            }
            $html .= $listarea . $html_aft;
        }

        $tmp = splitfirst($html, '<!--ReadmemdStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--ReadmemdEnd-->');
        if (isset($files['children']['readme.md'])) {
            $Readmemd = str_replace('<!--ReadmemdContent-->', fetch_files(spurlencode(path_format(urldecode($path) . '/readme.md'),'/'))['content']['body'], $tmp[0]);
            $html .= $Readmemd . $tmp[1];
            while (strpos($html, '<!--ReadmemdStart-->')) {
                $html = str_replace('<!--ReadmemdStart-->', '', $html);
                $html = str_replace('<!--ReadmemdEnd-->', '', $html);
            }
        } else {
            $html .= $tmp[1];
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--ReadmemdStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--ReadmemdEnd-->');
                $html .= $tmp[1];
            }
        }

        
        $tmp = splitfirst($html, '<!--FootomfStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--FootomfEnd-->');
        if (isset($files['children']['foot.omf'])) {
            $Footomf = str_replace('<!--FootomfContent-->', fetch_files(spurlencode(path_format(urldecode($path) . '/foot.omf'),'/'))['content']['body'], $tmp[0]);
        }
        $html .= $Footomf . $tmp[1];

        
        $tmp = splitfirst($html, '<!--MdRequireStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--MdRequireEnd-->');
        if (isset($files['children']['head.md'])||isset($files['children']['readme.md'])) {
            $html .= $tmp[0] . $tmp[1];
        } else $html .= $tmp[1];

        if (getConfig('passfile')!='') {
            $tmp = splitfirst($html, '<!--EncryptBtnStart-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--EncryptBtnEnd-->');
            $html .= str_replace('<!--constStr@Encrypt-->', getconstStr('Encrypt'), $tmp[0]) . $tmp[1];
            $tmp = splitfirst($html, '<!--EncryptAlertStart-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--EncryptAlertEnd-->');
            $html .= $tmp[1];
        } else {
            $tmp = splitfirst($html, '<!--EncryptAlertStart-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--EncryptAlertEnd-->');
            $html .= str_replace('<!--constStr@SetpassfileBfEncrypt-->', getconstStr('SetpassfileBfEncrypt'), $tmp[0]) . $tmp[1];
            $tmp = splitfirst($html, '<!--EncryptBtnStart-->');
            $html = $tmp[0];
            $tmp = splitfirst($tmp[1], '<!--EncryptBtnEnd-->');
            $html .= $tmp[1];
        }

        $tmp = splitfirst($html, '<!--MoveRootStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--MoveRootEnd-->');
        if ($path != '/') {
            $html .= str_replace('<!--constStr@ParentDir-->', getconstStr('ParentDir'), $tmp[0]) . $tmp[1];
        } else $html .= $tmp[1];

        $tmp = splitfirst($html, '<!--MoveDirsStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--MoveDirsEnd-->');
        $MoveDirs = $tmp[0];
        if (isset($files['children'])) {
            foreach ($files['children'] as $file) {
                if (isset($file['folder'])) {
                    $MoveDirsStr = str_replace('<!--MoveDirsValue-->', str_replace('&','&amp;', $file['name']), $MoveDirs);
                    $MoveDirsStr = str_replace('<!--MoveDirsValue-->', str_replace('&','&amp;', $file['name']), $MoveDirsStr);
                    $html .= $MoveDirsStr;
                }
            }
        }
        $html .= $tmp[1];

        $tmp = splitfirst($html, '<!--WriteTimezoneStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--WriteTimezoneEnd-->');
        if (!isset($_COOKIE['timezone'])) $html .= str_replace('<!--timezone-->', $_SERVER['timezone'], $tmp[0]);
        $html .= $tmp[1];
        while (strpos($html, '<!--timezone-->')) $html = str_replace('<!--timezone-->', $_SERVER['timezone'], $html);

        while (strpos($html, '{{.RawData}}')) {
            $str = '[';
            $i = 0;
            foreach ($files['children'] as $file) if ($_SERVER['admin'] or !isHideFile($file['name'])) {
                $tmp = [];
                $tmp['name'] = $file['name'];
                $tmp['size'] = size_format($file['size']);
                $tmp['date'] = time_format($file['lastModifiedDateTime']);
                $tmp['@time'] = $file['date'];
                $tmp['@type'] = isset($file['folder'])?'folder':'file';
                $str .= json_encode($tmp).',';
            }
            if ($str == '[') {
                $str = '';
            } else $str = substr($str, 0, -1).']';
            $html = str_replace('{{.RawData}}', base64_encode($str), $html);
        }

        // 最后清除换行
        while (strpos($html, "\r\n\r\n")) $html = str_replace("\r\n\r\n", "\r\n", $html);
        //while (strpos($html, "\r\r")) $html = str_replace("\r\r", "\r", $html);
        while (strpos($html, "\n\n")) $html = str_replace("\n\n", "\n", $html);
        //while (strpos($html, PHP_EOL.PHP_EOL)) $html = str_replace(PHP_EOL.PHP_EOL, PHP_EOL, $html);

        $exetime = round(microtime(true)-$_SERVER['php_starttime'],3);
        $html = str_replace('<!--FootStr-->', date("Y-m-d H:i:s")." ".getconstStr('Week')[date("w")]." ".$_SERVER['REMOTE_ADDR'].' Runningtime:'.$exetime.'s Mem:'.size_format(memory_get_usage()), $html);
    }

    if ($_SERVER['admin']||!getConfig('disableChangeTheme')) {
        $theme_arr = scandir(__DIR__.'/theme');
        $html .= '
<div style="position: fixed;right: 10px;bottom: 10px;/*color: rgba(247,247,249,0);*/">
    <select name="theme" onchange="changetheme(this.options[this.options.selectedIndex].value)">
        <option value="">'.getconstStr('Theme').'</option>';
        foreach ($theme_arr as $v1) {
            if ($v1!='.' && $v1!='..') $html .= '
        <option value="'.$v1.'"'.($v1==$theme?' selected="selected"':'').'>'.$v1.'</option>';
        }
        $html .= '
    </select>
</div>
<script type="text/javascript">
    function changetheme(str)
    {
        var expd = new Date();
        expd.setTime(expd.getTime()+(2*60*60*1000));
        var expires = "expires="+expd.toGMTString();
        document.cookie=\'theme=\'+str+\'; path=/; \'+expires;
        location.href = location.href;
    }
</script>';
    }

    $html = $authinfo . $html;
    if (isset($_SERVER['Set-Cookie'])) return output($html, $statusCode, [ 'Set-Cookie' => $_SERVER['Set-Cookie'], 'Content-Type' => 'text/html' ]);
    return output($html,$statusCode);
}
