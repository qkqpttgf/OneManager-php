<?php

global $timezones;
global $slash;
global $drive;

global $EnvConfigs;
$EnvConfigs = [
    // 1 inner, 0 common
    // 1 showed/enableEdit, 0 hidden/disableEdit
    // 1 base64 to save, 0 not base64
    'APIKey'            => 0b000, // used in heroku.
    'SecretId'          => 0b000, // used in SCF.
    'SecretKey'         => 0b000, // used in SCF.
    'AccessKeyID'       => 0b000, // used in FC.
    'AccessKeySecret'   => 0b000, // used in FC.
    'HW_urn'            => 0b000, // used in FG.
    'HW_key'            => 0b000, // used in FG.
    'HW_secret'         => 0b000, // used in FG.
    'function_name'     => 0b000, // used in heroku.

    'admin'             => 0b000,
    'adminloginpage'    => 0b010,
    'autoJumpFirstDisk' => 0b010,
    'background'        => 0b011,
    'backgroundm'       => 0b011,
    'disableShowThumb'  => 0b010,
    'disableChangeTheme'=> 0b010,
    'disktag'           => 0b000,
    'hideFunctionalityFile'=> 0b010,
    'timezone'          => 0b010,
    'passfile'          => 0b011,
    'sitename'          => 0b011,
    'customScript'      => 0b011,
    'customCss'         => 0b011,
    'customTheme'       => 0b011,
    'theme'             => 0b010,
    'dontBasicAuth'     => 0b010,

    'Driver'            => 0b100,
    'client_id'         => 0b100,
    'client_secret'     => 0b101,
    'sharepointSite'    => 0b101,
    'shareurl'          => 0b101,
    //'sharecookie'       => 0b101,
    'shareapiurl'       => 0b101,
    'siteid'            => 0b100,
    'refresh_token'     => 0b100,
    'token_expires'     => 0b100,
    'default_drive_id'  => 0b100,
    'default_sbox_drive_id'=> 0b100,

    'diskname'          => 0b111,
    'domain_path'       => 0b111,
    'downloadencrypt'   => 0b110,
    'guestup_path'      => 0b111,
    'domainforproxy'    => 0b111,
    'public_path'       => 0b111,
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

function isCommonEnv($str)
{
    global $EnvConfigs;
    if (isset($EnvConfigs[$str])) return ( $EnvConfigs[$str] & 0b100 ) ? false : true;
    else return null;
}

function isInnerEnv($str)
{
    global $EnvConfigs;
    if (isset($EnvConfigs[$str])) return ( $EnvConfigs[$str] & 0b100 ) ? true : false;
    else return null;
}

function isShowedEnv($str)
{
    global $EnvConfigs;
    if (isset($EnvConfigs[$str])) return ( $EnvConfigs[$str] & 0b010 ) ? true : false;
    else return null;
}

function isBase64Env($str)
{
    global $EnvConfigs;
    if (isset($EnvConfigs[$str])) return ( $EnvConfigs[$str] & 0b001 ) ? true : false;
    else return null;
}

function main($path)
{
    global $exts;
    global $constStr;
    global $slash;
    global $drive;

    $slash = '/';
    if (strpos(__DIR__, ':')) $slash = '\\';
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
        if ($_POST['password1']==getConfig('admin')) {
            return adminform('admin', pass2cookie('admin', $_POST['password1']), $url);
        } else return adminform();
    }
    if ( isset($_COOKIE['admin'])&&$_COOKIE['admin']==pass2cookie('admin', getConfig('admin')) ) {
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

    $_SERVER['sitename'] = getConfig('sitename');
    if (empty($_SERVER['sitename'])) $_SERVER['sitename'] = getconstStr('defaultSitename');
    $_SERVER['base_disk_path'] = $_SERVER['base_path'];
    $disktags = explode("|", getConfig('disktag'));
    //    echo 'count$disk:'.count($disktags);
    if (count($disktags)>1) {
        if ($path=='/'||$path=='') {
            $files['type'] = 'folder';
            $files['childcount'] = count($disktags);
            $files['showname'] = 'root';
            foreach ($disktags as $disktag) {
                $files['list'][$disktag]['type'] = 'folder';
                $files['list'][$disktag]['name'] = $disktag;
                $files['list'][$disktag]['showname'] = getConfig('diskname', $disktag);
            }
            if ($_GET['json']) {
                // return a json
                return output(json_encode($files), 200, ['Content-Type' => 'application/json']);
            }
            if (getConfig('autoJumpFirstDisk')) return output('', 302, [ 'Location' => path_format($_SERVER['base_path'].'/'.$disktags[0].'/') ]);
        } else {
            $_SERVER['disktag'] = splitfirst( substr(path_format($path), 1), '/' )[0];
            //$pos = strpos($path, '/');
            //if ($pos>1) $_SERVER['disktag'] = substr($path, 0, $pos);
            if (!in_array($_SERVER['disktag'], $disktags)) {
                $tmp = path_format($_SERVER['base_path'] . '/' . $disktags[0] . '/' . $path);
                if (!!$_GET) {
                    $tmp .= '?';
                    foreach ($_GET as $k => $v) {
                        if ($v === true) $tmp .= $k . '&';
                        else $tmp .= $k . '=' . $v . '&';
                    }
                    $tmp = substr($tmp, 0, -1);
                }
                return output('Please visit <a href="' . $tmp . '">' . $tmp . '</a>.', 302, [ 'Location' => $tmp ]);
                //return message('<meta http-equiv="refresh" content="2;URL='.$_SERVER['base_path'].'">Please visit from <a href="'.$_SERVER['base_path'].'">Home Page</a>.', 'Error', 404);
            }
            $path = substr($path, strlen('/' . $_SERVER['disktag']));
            if ($_SERVER['disktag']!='') $_SERVER['base_disk_path'] = path_format($_SERVER['base_disk_path'] . '/' . $_SERVER['disktag'] . '/');
        }
    } else $_SERVER['disktag'] = $disktags[0];
    //    echo 'main.disktag:'.$_SERVER['disktag'].'，path:'.$path.'';
    $_SERVER['list_path'] = getListpath($_SERVER['HTTP_HOST']);
    if ($_SERVER['list_path']=='') $_SERVER['list_path'] = '/';
    $_SERVER['is_guestup_path'] = is_guestup_path($path);
    $_SERVER['ajax']=0;
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) if ($_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest') $_SERVER['ajax']=1;

    // Add disk
    if (isset($_GET['AddDisk'])) {
        if ($_SERVER['admin']) {
            if (!class_exists($_GET['AddDisk'])) require 'disk' . $slash . $_GET['AddDisk'] . '.php';
                $drive = new $_GET['AddDisk']($_GET['disktag']);
                return $drive->AddDisk();
        } else {
            $url = $_SERVER['PHP_SELF'];
            if ($_GET) {
                $tmp = null;
                $tmp = '';
                foreach ($_GET as $k => $v) {
                    if ($k!='setup') {
                        if ($v===true) $tmp .= '&' . $k;
                        else $tmp .= '&' . $k . '=' . $v;
                    }
                }
                $tmp = substr($tmp, 1);
                if ($tmp!='') $url .= '?' . $tmp;
            }
            return output('<script>alert(\''.getconstStr('SetSecretsFirst').'\');</script>', 302, [ 'Location' => $url ]);
        }
    }

    // Show disks in root
    if ($files['showname'] == 'root') return render_list($path, $files);

    if (!driveisfine($_SERVER['disktag'], $drive)) return render_list();

    // Operate
    if ($_SERVER['ajax']) {
        if ($_GET['action']=='del_upload_cache') {
            // del '.tmp' without login. 无需登录即可删除.tmp后缀文件
            return $drive->del_upload_cache($path);
        }
        if ($_GET['action']=='upbigfile') {
            if (!$_SERVER['admin']) {
                if (!$_SERVER['is_guestup_path']) return output('Not_Guest_Upload_Folder', 400);
                if (strpos($_GET['upbigfilename'], '../')!==false) return output('Not_Allow_Cross_Path', 400);
            }
            $path1 = path_format($_SERVER['list_path'] . path_format($path));
            if (substr($path1, -1)=='/') $path1=substr($path1, 0, -1);
            return $drive->bigfileupload($path1);
        }
    }
    if ($_SERVER['admin']) {
        $tmp = adminoperate($path);
        if ($tmp['statusCode'] > 0) {
            $path1 = path_format($_SERVER['list_path'] . path_format($path));
            //savecache('path_' . $path1, json_decode('{}',true), $_SERVER['disktag'], 1);
            if ($path1!='/'&&substr($path1,-1)=='/') $path1=substr($path1,0,-1);
            savecache('path_' . $path1, json_decode('{}',true), $_SERVER['disktag'], 1);
            return $tmp;
        }
    } else {
        if ($_SERVER['ajax']) return output(getconstStr('RefreshtoLogin'),401);
    }
    $_SERVER['ishidden'] = passhidden($path);
    if (isset($_GET['thumbnails'])) {
        if ($_SERVER['ishidden']<4) {
            if (in_array(strtolower(substr($path, strrpos($path, '.') + 1)), $exts['img'])) {
                $path1 = path_format($_SERVER['list_path'] . path_format($path));
                if ($path1!='/'&&substr($path1, -1)=='/') $path1=substr($path1, 0, -1);
                $thumb_url = $drive->get_thumbnails_url($path1);
                if ($thumb_url!='') {
                    if ($_GET['location']) {
                        $url = $thumb_url;
                        $domainforproxy = '';
                        $domainforproxy = getConfig('domainforproxy', $_SERVER['disktag']);
                        if ($domainforproxy!='') {
                            $url = proxy_replace_domain($url, $domainforproxy);
                        }
                        return output('', 302, [ 'Location' => $url ]);
                    } else return output($thumb_url);
                }
                return output('', 404);
            } else return output(json_encode($exts['img']), 400);
        } else return output('', 401);
    }

    // list folder
    if ($_SERVER['is_guestup_path'] && !$_SERVER['admin']) {
        $files = json_decode('{"type":"folder"}', true);
    } elseif ($_SERVER['ishidden']==4) {
        if (!getConfig('downloadencrypt', $_SERVER['disktag'])) {
            $files = json_decode('{"type":"folder"}', true);
        } else {
            $path1 = path_format($_SERVER['list_path'] . path_format($path));
            if ($path1!='/'&&substr($path1,-1)=='/') $path1=substr($path1, 0, -1);
            $files = $drive->list_files($path1);
            if ($files['type']=='folder') $files = json_decode('{"type":"folder"}', true);
        }
    } else {
        $path1 = path_format($_SERVER['list_path'] . path_format($path));
        if ($path1!='/'&&substr($path1,-1)=='/') $path1=substr($path1, 0, -1);
        $files = $drive->list_files($path1);
    }

    if ($_GET['json']) {
        // return a json
        return output(json_encode($files), 200, ['Content-Type' => 'application/json']);
    }
    // random file
    if (isset($_GET['random'])&&$_GET['random']!=='') {
        if ($_SERVER['ishidden']<4) {
            $tmp = [];
            foreach (array_keys($files['list']) as $filename) {
                if (strtolower(splitlast($filename, '.')[1])==strtolower($_GET['random'])) $tmp[$filename] = $files['list'][$filename]['url'];
            }
            $tmp = array_values($tmp);
            if (count($tmp)>0) {
                $url = $tmp[rand(0, count($tmp)-1)];
                if (isset($_GET['url'])) return output($url, 200);
                $domainforproxy = '';
                $domainforproxy = getConfig('domainforproxy', $_SERVER['disktag']);
                if ($domainforproxy!='') {
                    $url = proxy_replace_domain($url, $domainforproxy);
                }
                return output('', 302, [ 'Location' => $url ]);
            } else return output('No ' . $_GET['random'] . 'file', 404);
        } else return output('Hidden', 401);
    }
    // is file && not preview mode, download file
    if ($files['type']=='file' && !isset($_GET['preview'])) {
        if ( $_SERVER['ishidden']<4 || (!!getConfig('downloadencrypt', $_SERVER['disktag'])&&$files['name']!=getConfig('passfile')) ) {
            $url = $files['url'];
            $domainforproxy = '';
            $domainforproxy = getConfig('domainforproxy', $_SERVER['disktag']);
            if ($domainforproxy!='') {
                $url = proxy_replace_domain($url, $domainforproxy);
            }
            if ( strtolower(splitlast($files['name'], '.')[1])=='html' ) return output($files['content']['body'], $files['content']['stat']);
            else {
                if ($_SERVER['HTTP_RANGE']!='') $header['Range'] = $_SERVER['HTTP_RANGE'];
                $header['Location'] = $url;
                return output('', 302, $header);
            }
        }
    }
    // Show folder
    if ( $files['type']=='folder' || $files['type']=='file' ) {
        return render_list($path, $files);
    } else {
        if (!isset($files['error'])) {
            if (is_array($files)) $files['error']['message'] = json_encode($files, JSON_PRETTY_PRINT);
            else $files['error']['message'] = $files;
            $files['error']['code'] = 'unknownError';
            $files['error']['stat'] = 500;
        }
        return message('<a href="'.$_SERVER['base_path'].'">'.getconstStr('Back').getconstStr('Home').'</a><div style="margin:8px;"><pre>' . $files['error']['message'] . '</pre></div><a href="javascript:history.back(-1)">'.getconstStr('Back').'</a>', $files['error']['code'], $files['error']['stat']);
    }
}

function get_content($path)
{
    global $drive;
    $path1 = path_format($_SERVER['list_path'] . path_format($path));
    if ($path1!='/'&&substr($path1,-1)=='/') $path1=substr($path1, 0, -1);
    $file = $drive->list_files($path1);
    //var_dump($file);
    return $file;
}

function driveisfine($tag, &$drive = null)
{
    global $slash;
    $disktype = getConfig('Driver', $tag);
    if (!$disktype) return false;
    if (!class_exists($disktype)) require 'disk' . $slash . $disktype . '.php';
    $drive = new $disktype($tag);
    if ($drive->isfine()) return true;
    else return false;
}

function baseclassofdrive($d = null)
{
    global $drive;
    if (!$d) $dr = $drive;
    else $dr = $d;
    if (!$dr) return false;
    return $dr->show_base_class();
}

function extendShow_diskenv($drive)
{
    if (!$drive) return [];
    return $drive->ext_show_innerenv();
}

function pass2cookie($name, $pass)
{
    return md5($name . ':' . md5($pass));
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

function getcache($str, $disktag = '')
{
    $cache = filecache($disktag);
    return $cache->fetch($str);
}

function savecache($key, $value, $disktag = '', $exp = 1800)
{
    $cache = filecache($disktag);
    return $cache->save($key, $value, $exp);
}

function filecache($disktag)
{
    $dir = sys_get_temp_dir();
    if (!is_writable($dir)) {
        $tmp = __DIR__ . '/tmp/';
        if (file_exists($tmp)) {
            if ( is_writable($tmp) ) $dir = $tmp;
        } elseif ( mkdir($tmp) ) $dir = $tmp;
    }
    $tag = __DIR__ . '/OneManager/' . $disktag;
    while (strpos($tag, '/')>-1) $tag = str_replace('/', '_', $tag);
    if (strpos($tag, ':')>-1) {
        $tag = str_replace(':', '_', $tag);
        $tag = str_replace('\\', '_', $tag);
    }
    // error_log1('DIR:' . $dir . ' TAG: ' . $tag);
    $cache = new \Doctrine\Common\Cache\FilesystemCache($dir, $tag);
    return $cache;
}

function sortConfig(&$arr)
{
    ksort($arr);

    $tags = explode('|', $arr['disktag']);
    unset($arr['disktag']);
    if ($tags[0]!='') {
        foreach($tags as $tag) {
            $disks[$tag] = $arr[$tag];
            unset($arr[$tag]);
        }
        $arr['disktag'] = implode('|', $tags);
        foreach($disks as $k => $v) {
            $arr[$k] = $v;
        }
    }

    return $arr;
}

function getconstStr($str)
{
    global $constStr;
    if ($constStr[$str][$constStr['language']]!='') return $constStr[$str][$constStr['language']];
    return $constStr[$str]['en-us'];
}

function getListpath($domain)
{
    $domain_path1 = getConfig('domain_path', $_SERVER['disktag']);
    $public_path = getConfig('public_path', $_SERVER['disktag']);
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

function spurlencode($str, $split='')
{
    $str = str_replace(' ', '%20', $str);
    $tmp='';
    if ($split!='') {
        $tmparr=explode($split, $str);
        foreach ($tmparr as $str1) {
            $tmp .= urlencode($str1) . $split;
        }
        $tmp = substr($tmp, 0, strlen($tmp)-strlen($split));
    } else {
        $tmp = urlencode($str);
    }
    $tmp = str_replace('%2520', '%20', $tmp);
    $tmp = str_replace('%26amp%3B', '&', $tmp);
    return $tmp;
}

function base64y_encode($str)
{
    $str = base64_encode($str);
    while (substr($str,-1)=='=') $str=substr($str,0,-1);
    while (strpos($str, '+')!==false) $str = str_replace('+', '-', $str);
    while (strpos($str, '/')!==false) $str = str_replace('/', '_', $str);
    return $str;
}

function base64y_decode($str)
{
    while (strpos($str, '_')!==false) $str = str_replace('_', '/', $str);
    while (strpos($str, '-')!==false) $str = str_replace('-', '+', $str);
    while (strlen($str)%4) $str .= '=';
    $str = base64_decode($str);
    //if (strpos($str, '%')!==false) $str = urldecode($str);
    return $str;
}

function error_log1($str)
{
    error_log($str);
}

function is_guestup_path($path)
{
    if (getConfig('guestup_path', $_SERVER['disktag'])!='') {
        $a1 = path_format(path_format(urldecode($_SERVER['list_path'].path_format($path))).'/');
        $a2 = path_format(path_format(getConfig('guestup_path', $_SERVER['disktag'])).'/');
        if (strtolower($a1)==strtolower($a2)) return 1;
    }
    return 0;
}

function array_value_isnot_null($arr)
{
    return $arr!=='';
}

function curl($method, $url, $data = '', $headers = [], $returnheader = 0)
{
    //if (!isset($headers['Accept'])) $headers['Accept'] = '*/*';
    //if (!isset($headers['Referer'])) $headers['Referer'] = $url;
    //if (!isset($headers['Content-Type'])) $headers['Content-Type'] = 'application/x-www-form-urlencoded';
    if (!isset($headers['Content-Type'])) $headers['Content-Type'] = '';
    $sendHeaders = array();
    foreach ($headers as $headerName => $headerVal) {
        $sendHeaders[] = $headerName . ': ' . $headerVal;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,$method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
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

function curl_request($url, $data = false, $headers = [], $returnheader = 0)
{
    if (!isset($headers['Accept'])) $headers['Accept'] = '*/*';
    //if (!isset($headers['Referer'])) $headers['Referer'] = $url;
    //if (!isset($headers['Content-Type'])) $headers['Content-Type'] = 'application/x-www-form-urlencoded';
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
    $response['stat'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
    if (!getConfig('dontBasicAuth')) {
        // use Basic Auth
        //$_SERVER['PHP_AUTH_USER']
        if ($_SERVER['PHP_AUTH_PW'] !== '') if (md5($_SERVER['PHP_AUTH_PW']) === $pass ) {
            date_default_timezone_set('UTC');
            $_SERVER['Set-Cookie'] = 'password='.$pass.'; expires='.date(DATE_COOKIE,strtotime('+1hour'));
            date_default_timezone_set(get_timezone($_SERVER['timezone']));
            return 2;
        }
    }
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
    $password=getcache('path_' . $path1 . '/?password', $_SERVER['disktag']);
    if ($password=='') {
        $ispassfile = get_content(path_format($path . '/' . urlencode($passfile)));
        //echo $path . '<pre>' . json_encode($ispassfile, JSON_PRETTY_PRINT) . '</pre>';
        if ($ispassfile['type']=='file') {
            $arr = curl('GET', $ispassfile['url']);
            if ($arr['stat']==200) {
                $passwordf=explode("\n",$arr['body']);
                $password=$passwordf[0];
                if ($password==='') {
                    return '';
                } else {
                    $password=md5($password);
                    savecache('path_' . $path1 . '/?password', $password, $_SERVER['disktag']);
                    return $password;
                }
            } else {
                //return md5('DefaultP@sswordWhenNetworkError');
                return md5( md5(time()).rand(1000,9999) );
            }
        } else {
            savecache('path_' . $path1 . '/?password', 'null', $_SERVER['disktag']);
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
    global $slash;
    $current_version = file_get_contents(__DIR__ . $slash . 'version');
    $current_ver = substr($current_version, strpos($current_version, '.')+1);
    $current_ver = explode(urldecode('%0A'),$current_ver)[0];
    $current_ver = explode(urldecode('%0D'),$current_ver)[0];
    $split = splitfirst($current_version, '.' . $current_ver)[0] . '.' . $current_ver;
    if (!($github_version = getcache('github_version'))) {
        $tmp = curl('GET', 'https://raw.githubusercontent.com/qkqpttgf/OneManager-php/master/version');
        if ($tmp['stat']==0) return 0;
        $github_version = $tmp['body'];
        savecache('github_version', $github_version);
    }
    $github_ver = substr($github_version, strpos($github_version, '.')+1);
    $github_ver = explode(urldecode('%0A'),$github_ver)[0];
    $github_ver = explode(urldecode('%0D'),$github_ver)[0];
    if ($current_ver != $github_ver) {
        //$_SERVER['github_version'] = $github_version;
        $_SERVER['github_ver_new'] = splitfirst($github_version, $split)[0];
        $_SERVER['github_ver_old'] = splitfirst($github_version, $_SERVER['github_ver_new'])[1];
        return 1;
    }
    return 0;
}

function output($body, $statusCode = 200, $headers = ['Content-Type' => 'text/html'], $isBase64Encoded = false)
{
    //$headers['Referrer-Policy'] = 'same-origin';
    $headers['Referrer-Policy'] = 'no-referrer';
    return [
        'isBase64Encoded' => $isBase64Encoded,
        'statusCode' => $statusCode,
        'headers' => $headers,
        'body' => $body
    ];
}

function passhidden($path)
{
    if ($_SERVER['admin']) return 0;
    $path = str_replace('+','%2B',$path);
    $path = str_replace('&amp;','&', path_format(urldecode($path)));
    if (getConfig('passfile') != '') {
        $path = spurlencode($path,'/');
        if (substr($path,-1)=='/') $path=substr($path,0,-1);
        $hiddenpass=gethiddenpass($path, getConfig('passfile'));
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
    if ($ISO=='') return date('Y-m-d H:i:s');
    $ISO = str_replace('T', ' ', $ISO);
    $ISO = str_replace('Z', ' ', $ISO);
    return date('Y-m-d H:i:s',strtotime($ISO . " UTC"));
}

function adminform($name = '', $pass = '', $path = '')
{
    $html = '<html><head><title>' . getconstStr('AdminLogin') . '</title><meta charset=utf-8></head>';
    if ($name!=''&&$pass!='') {
        $html .= '<meta http-equiv="refresh" content="3;URL=' . $path . '">
        <meta name=viewport content="width=device-width,initial-scale=1">
        <body>' . getconstStr('LoginSuccess') . '</body></html>';
        $statusCode = 201;
        date_default_timezone_set('UTC');
        $header = [
            'Set-Cookie' => $name . '=' . $pass . '; path=/; expires=' . date(DATE_COOKIE, strtotime('+7day')),
            //'Location' => $path,
            'Content-Type' => 'text/html'
        ];
        return output($html, $statusCode, $header);
    }
    $statusCode = 401;
    $html .= '
<meta name=viewport content="width=device-width,initial-scale=1">
<body>
    <div>
    <center><h4>' . getconstStr('InputPassword') . '</h4>
    <form action="" method="post" onsubmit="return md5pass(this);">
        <div>
            <input name="password1" type="password"/>
            <input name="timestamp" type="hidden"/>
            <input type="submit" value="' . getconstStr('Login') . '">
        </div>
    </form>
    </center>
    </div>
</body>';
    $html .= '
<script>
    function md5pass(f) {
        return true;
        var timestamp = new Date().getTime();
        f.timestamp.value = timestamp;
        //f.password1.value = 
    }
</script>';
    $html .= '</html>';
    return output($html, $statusCode);
}

function adminoperate($path)
{
    global $drive;
    $path1 = path_format($_SERVER['list_path'] . path_format($path));
    if (substr($path1, -1)=='/') $path1=substr($path1, 0, -1);
    $tmpget = $_GET;
    $tmppost = $_POST;
    $tmparr['statusCode'] = 0;
    if ( (isset($tmpget['rename_newname'])&&$tmpget['rename_newname']!=$tmpget['rename_oldname'] && $tmpget['rename_newname']!='') || (isset($tmppost['rename_newname'])&&$tmppost['rename_newname']!=$tmppost['rename_oldname'] && $tmppost['rename_newname']!='') ) {
        if (isset($tmppost['rename_newname'])) $VAR = 'tmppost';
        else $VAR = 'tmpget';
        // rename 重命名
        $file['path'] = $path1;
        $file['name'] = ${$VAR}['rename_oldname'];
        $file['id'] = ${$VAR}['rename_fileid'];
        return $drive->Rename($file, ${$VAR}['rename_newname']);
    }
    if (isset($tmpget['delete_name']) || isset($tmppost['delete_name'])) {
        if (isset($tmppost['delete_name'])) $VAR = 'tmppost';
        else $VAR = 'tmpget';
        // delete 删除
        $file['path'] = $path1;
        $file['name'] = ${$VAR}['delete_name'];
        $file['id'] = ${$VAR}['delete_fileid'];
        return $drive->Delete($file);
    }
    if ( (isset($tmpget['operate_action'])&&$tmpget['operate_action']==getconstStr('Encrypt')) || (isset($tmppost['operate_action'])&&$tmppost['operate_action']==getconstStr('Encrypt')) ) {
        if (isset($tmppost['operate_action'])) $VAR = 'tmppost';
        else $VAR = 'tmpget';
        // encrypt 加密
        if (getConfig('passfile')=='') return message(getconstStr('SetpassfileBfEncrypt'),'',403);
        if (${$VAR}['encrypt_folder']=='/') ${$VAR}['encrypt_folder']=='';
        $folder['path'] = path_format($path1 . '/' . spurlencode(${$VAR}['encrypt_folder'], '/'));
        $folder['name'] = ${$VAR}['encrypt_folder'];
        $folder['id'] = ${$VAR}['id'];
        return $drive->Encrypt($folder, getConfig('passfile'), ${$VAR}['encrypt_newpass']);
    }
    if (isset($tmpget['move_folder']) || isset($tmppost['move_folder'])) {
        if (isset($tmppost['move_folder'])) $VAR = 'tmppost';
        else $VAR = 'tmpget';
        // move 移动
        $moveable = 1;
        if ($path == '/' && ${$VAR}['move_folder'] == '/../') $moveable=0;
        if (${$VAR}['move_folder'] == ${$VAR}['move_name']) $moveable=0;
        if ($moveable) {
            $file['path'] = $path1;
            $file['name'] = ${$VAR}['move_name'];
            $file['id'] = ${$VAR}['move_fileid'];
            if (${$VAR}['move_folder'] == '/../') {
                $foldername = path_format('/' . urldecode($path1 . '/'));
                $foldername = substr($foldername, 0, -1);
                $foldername = splitlast($foldername, '/')[0];
            } else $foldername = path_format('/' . urldecode($path1) . '/' . ${$VAR}['move_folder']);
            $folder['path'] = $foldername;
            $folder['name'] = ${$VAR}['move_folder'];
            $folder['id'] = '';
            return $drive->Move($file, $folder);
        } else {
            return output('{"error":"' . getconstStr('CannotMove') . '"}', 403);
        }
    }
    if (isset($tmpget['copy_name']) || isset($tmppost['copy_name'])) {
        if (isset($tmppost['copy_name'])) $VAR = 'tmppost';
        else $VAR = 'tmpget';
        // copy 复制
        $file['path'] = $path1;
        $file['name'] = ${$VAR}['copy_name'];
        $file['id'] = ${$VAR}['copy_fileid'];
        return $drive->Copy($file);
    }
    if (isset($tmppost['editfile'])) {
        // edit 编辑
        $file['path'] = $path1;
        $file['name'] = '';
        $file['id'] = '';
        return $drive->Edit($file, $tmppost['editfile']);
    }
    if (isset($tmpget['create_name']) || isset($tmppost['create_name'])) {
        if (isset($tmppost['create_name'])) $VAR = 'tmppost';
        else $VAR = 'tmpget';
        // create 新建
        $parent['path'] = $path1;
        $parent['name'] = '';
        $parent['id'] = ${$VAR}['create_fileid'];
        return $drive->Create($parent, ${$VAR}['create_type'], ${$VAR}['create_name'], ${$VAR}['create_text']);
    }
    if (isset($tmpget['RefreshCache'])) {
        //$path1 = path_format($_SERVER['list_path'] . path_format($path));
        //if ($path1!='/'&&substr($path1, -1)=='/') $path1=substr($path1, 0, -1);
        savecache('path_' . $path1 . '/?password', '', $_SERVER['disktag'], 1);
        savecache('customTheme', '', '', 1);
        return message('<meta http-equiv="refresh" content="2;URL=./">
        <meta name=viewport content="width=device-width,initial-scale=1">', getconstStr('RefreshCache'), 202);
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

function children_name($children)
{
    $tmp = [];
    foreach ($children as $file) {
        $tmp[strtolower($file['name'])] = $file;
    }
    return $tmp;
}

function EnvOpt($needUpdate = 0)
{
    global $constStr;
    global $EnvConfigs;
    global $timezones;
    global $slash;
    global $drive;
    ksort($EnvConfigs);
    $envs = '';
    foreach ($EnvConfigs as $env => $v) if (isCommonEnv($env)) $envs .= '\'' . $env . '\', ';

    $html = '<title>OneManager '.getconstStr('Setup').'</title>';
    if (isset($_POST['updateProgram'])&&$_POST['updateProgram']==getconstStr('updateProgram')) {
        $response = setConfigResponse(OnekeyUpate($_POST['auth'], $_POST['project'], $_POST['branch']));
        if (api_error($response)) {
            $html = api_error_msg($response);
            $title = 'Error';
        } else {
            //WaitSCFStat();
            $html .= getconstStr('UpdateSuccess') . '<br><a href="">' . getconstStr('Back') . '</a>';
            $title = getconstStr('Setup');
        }
        return message($html, $title);
    }
    if (isset($_POST['submit1'])) {
        $_SERVER['disk_oprating'] = '';
        foreach ($_POST as $k => $v) {
            if (isShowedEnv($k) || $k=='disktag_del' || $k=='disktag_add' || $k=='disktag_rename' || $k=='disktag_copy') {
                $tmp[$k] = $v;
            }
            if ($k=='disktag_newname') {
                $v = preg_replace('/[^0-9a-zA-Z|_]/i', '', $v);
                $f = substr($v, 0, 1);
                if (strlen($v)==1) $v .= '_';
                if (isCommonEnv($v)) {
                    return message('Do not input ' . $envs . '<br><a href="">' . getconstStr('Back') . '</a>', 'Error', 201);
                } elseif (!(('a'<=$f && $f<='z') || ('A'<=$f && $f<='Z'))) {
                    return message('<a href="">' . getconstStr('Back') . '</a>', 'Please start with letters', 201);
                } elseif (getConfig($v)) {
                    return message('<a href="">' . getconstStr('Back') . '</a>', 'Same tag', 201);
                } else {
                    $tmp[$k] = $v;
                }
            }
            if ($k=='disktag_sort') {
                $td = implode('|', json_decode($v));
                if (strlen($td)==strlen(getConfig('disktag'))) $tmp['disktag'] = $td;
                else return message('Something wrong.');
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
            $html .= getconstStr('Success') . '!<br>
            <a href="">' . getconstStr('Back') . '</a>';
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
    foreach ($EnvConfigs as $key => $val) if (isCommonEnv($key) && isShowedEnv($key)) {
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
            $theme_arr = scandir(__DIR__ . $slash . 'theme');
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
    $disktags = explode('|', getConfig('disktag'));
    if (count($disktags)>1) {
        $html .= '
<script src="//cdn.bootcss.com/Sortable/1.8.3/Sortable.js"></script>
<style>
    .sortable-ghost {
        opacity: 0.4;
        background-color: #1748ce;
    }

    #sortdisks td {
        cursor: move;
    }
</style>
<table border=1>
    <form id="sortdisks_form" action="" method="post" style="margin: 0" onsubmit="return dragsort(this);">
    <tr id="sortdisks">
        <input type="hidden" name="disktag_sort" value="">';
        $num = 0;
        foreach ($disktags as $disktag) {
            if ($disktag!='') {
                $num++;
                $html .= '
        <td>' . $disktag . '</td>';
            }
        }
        $html .= '
    </tr>
    <tr><td colspan="' . $num . '">' . getconstStr('DragSort') . '<input type="submit" name="submit1" value="' . getconstStr('SubmitSortdisks') . '"></td></tr>
    </form>
</table>
<script>
    var disks=' . json_encode($disktags) . ';
    function change(arr, oldindex, newindex) {
        //console.log(oldindex + "," + newindex);
        tmp=arr.splice(oldindex-1, 1);
        if (oldindex > newindex) {
            tmp1=JSON.parse(JSON.stringify(arr));
            tmp1.splice(newindex-1, arr.length-newindex+1);
            tmp2=JSON.parse(JSON.stringify(arr));
            tmp2.splice(0, newindex-1);
        } else {
            tmp1=JSON.parse(JSON.stringify(arr));
            tmp1.splice(newindex-1, arr.length-newindex+1);
            tmp2=JSON.parse(JSON.stringify(arr));
            tmp2.splice(0, newindex-1);
        }
        arr=tmp1.concat(tmp, tmp2);
        //console.log(arr);
        return arr;
    }
    function dragsort(t) {
        if (t.disktag_sort.value==\'\') {
            alert(\'' . getconstStr('DragSort') . '\');
            return false;
        }
        envs = [' . $envs . '];
        if (envs.indexOf(t.disktag_sort.value)>-1) {
            alert("Do not input ' . $envs . '");
            return false;
        }
        return true;
    }
    Sortable.create(document.getElementById(\'sortdisks\'), {
        animation: 150,
        onEnd: function (evt) { //拖拽完毕之后发生该事件
            //console.log(evt.oldIndex);
            //console.log(evt.newIndex);
            if (evt.oldIndex!=evt.newIndex) {
                disks=change(disks, evt.oldIndex, evt.newIndex);
                document.getElementById(\'sortdisks_form\').disktag_sort.value=JSON.stringify(disks);
            }
        }
    });
</script><br>';
    }
    foreach ($disktags as $disktag) {
        if ($disktag!='') {
            $disk_tmp = null;
            $diskok = driveisfine($disktag, $disk_tmp);
            $html .= '
<table border=1 width=100%>
    <tr>
        <td>
            <form action="" method="post" style="margin: 0" onsubmit="return deldiskconfirm(this);">
                <input type="hidden" name="disktag_del" value="'.$disktag.'">
                <input type="submit" name="submit1" value="'.getconstStr('DelDisk').'">
            </form>
        </td>
        <td>
            <form action="" method="post" style="margin: 0" onsubmit="return renametag(this);">
                <input type="hidden" name="disktag_rename" value="'.$disktag.'">
                <input type="text" name="disktag_newname" value="'.$disktag.'" placeholder="' . getconstStr('EnvironmentsDescription')['disktag'] . '">
                <input type="submit" name="submit1" value="'.getconstStr('RenameDisk').'">
            </form>
            <form action="" method="post" style="margin: 0">
                <input type="hidden" name="disktag_copy" value="' . $disktag . '">
                <input type="submit" name="submit1" value="' . getconstStr('CopyDisk') . '">
            </form>
        </td>
    </tr>
    <tr>
        <td>Driver</td>
        <td>' . getConfig('Driver', $disktag);
            if ($diskok && baseclassofdrive($disk_tmp)=='Onedrive') $html .= ' <a href="?AddDisk=' . get_class($disk_tmp) . '&disktag=' . $disktag . '&SelectDrive">' . getconstStr('ChangeOnedrivetype') . '</a>';
            $html .= '</td>
    </tr>
    ';
            foreach (extendShow_diskenv($disk_tmp) as $ext_env) {
                $html .= '<tr><td>' . $ext_env . '</td><td>' . getConfig($ext_env, $disktag) . '</td></tr>
    ';
            }

            if ($diskok) {
                $html .= '
    <form name="'.$disktag.'" action="" method="post">
        <input type="hidden" name="disk" value="'.$disktag.'">';
                foreach ($EnvConfigs as $key => $val) if (isInnerEnv($key) && isShowedEnv($key)) {
                    $html .= '
        <tr>
            <td><label>' . $key . '</label></td>
            <td width=100%><input type="text" name="' . $key .'" value="' . getConfig($key, $disktag) . '" placeholder="' . getconstStr('EnvironmentsDescription')[$key] . '" style="width:100%"></td>
        </tr>';
                }
                $html .= '
        <tr><td></td><td><input type="submit" name="submit1" value="'.getconstStr('Setup').'"></td></tr>
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
    $Diver_arr = scandir(__DIR__ . $slash . 'disk');
    $html .= '
<select name="DriveType" onchange="changedrivetype(this.options[this.options.selectedIndex].value)">';
    foreach ($Diver_arr as $v1) {
        if ($v1!='.' && $v1!='..') {
            //$v1 = substr($v1, 0, -4);
            $v1 = splitlast($v1, '.php')[0];
            $html .= '
    <option value="' . $v1 . '"' . ($v1=='Onedrive'?' selected="selected"':'') . '>' . $v1 . '</option>';
        }
    }
    $html .= '
</select>
<a id="AddDisk_link" href="?AddDisk=Onedrive">' . getconstStr('AddDisk') . '</a>
<script>
    function changedrivetype(d) {
        document.getElementById(\'AddDisk_link\').href="?AddDisk=" + d;
    }
</script>
<br><br>';

    $canOneKeyUpate = 0;
    if (isset($_SERVER['USER'])&&$_SERVER['USER']==='qcloud') {
        $canOneKeyUpate = 1;
    } elseif (isset($_SERVER['HEROKU_APP_DIR'])&&$_SERVER['HEROKU_APP_DIR']==='/app') {
        $canOneKeyUpate = 1;
    } elseif (isset($_SERVER['FC_SERVER_PATH'])&&$_SERVER['FC_SERVER_PATH']==='/var/fc/runtime/php7.2') {
        $canOneKeyUpate = 1;
    } elseif ($_SERVER['BCE_CFC_RUNTIME_NAME']=='php7') {
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
    <button name="QueryBranchs" onclick="querybranchs();return false;">'.getconstStr('QueryBranchs').'</button>
    <select name="branch">
        <option value="master">master</option>
    </select>
    <input type="submit" name="updateProgram" value="'.getconstStr('updateProgram').'">
</form>
<script>
    function deldiskconfirm(t) {
        var msg="' . getconstStr('Delete') . ' ??";
        if (confirm(msg)==true) return true;
        else return false;
    }
    function renametag(t) {
        if (t.disktag_newname.value==\'\') {
            alert(\''.getconstStr('DiskTag').'\');
            return false;
        }
        if (t.disktag_newname.value==t.disktag_rename.value) {
            return false;
        }
        envs = [' . $envs . '];
        if (envs.indexOf(t.disktag_newname.value)>-1) {
            alert("Do not input ' . $envs . '");
            return false;
        }
        var reg = /^[a-zA-Z]([_a-zA-Z0-9]{1,20})$/;
        if (!reg.test(t.disktag_newname.value)) {
            alert(\''.getconstStr('TagFormatAlert').'\');
            return false;
        }
        return true;
    }

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
        $html .= '<div style="position: relative; word-wrap: break-word;">
        ' . str_replace("\r", '<br>', $_SERVER['github_ver_new']) . '
</div>
<button onclick="document.getElementById(\'github_ver_old\').style.display=(document.getElementById(\'github_ver_old\').style.display==\'none\'?\'\':\'none\');">More...</button>
<div id="github_ver_old" style="position: relative; word-wrap: break-word; display: none">
        ' . str_replace("\r", '<br>', $_SERVER['github_ver_old']) . '
</div>';
    }/* else {
        $html .= getconstStr('NotNeedUpdate');
    }*/
    return message($html, getconstStr('Setup'));
}

function render_list($path = '', $files = [])
{
    global $exts;
    global $constStr;
    global $slash;

    if (isset($files['list']['index.html']) && !$_SERVER['admin']) {
        //$htmlcontent = fetch_files(spurlencode(path_format(urldecode($path) . '/index.html'), '/'))['content'];
        $htmlcontent = get_content(spurlencode(path_format(urldecode($path) . '/index.html'), '/'))['content'];
        return output($htmlcontent['body'], $htmlcontent['stat']);
    }
    $path = str_replace('%20','%2520',$path);
    $path = str_replace('+','%2B',$path);
    $path = str_replace('&','&amp;',path_format(urldecode($path))) ;
    $path = str_replace('%20',' ',$path);
    $path = str_replace('#','%23',$path);
    $p_path='';
    if ($path !== '/') {
        if ($files['type']=='file') {
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
      $n_path = $pretitle;
    }
    $n_path = str_replace('&amp;','&',$n_path);
    $p_path = str_replace('&amp;','&',$p_path);
    $pretitle = str_replace('%23','#',$pretitle);
    $statusCode = 200;
    date_default_timezone_set(get_timezone($_SERVER['timezone']));
    $authinfo = '
<!--
    OneManager: An index & manager of Onedrive auth by ysun.
    Github: https://github.com/qkqpttgf/OneManager-php
-->';
    //$authinfo = $path . '<br><pre>' . json_encode($files, JSON_PRETTY_PRINT) . '</pre>';

    if (isset($_COOKIE['theme'])&&$_COOKIE['theme']!='') $theme = $_COOKIE['theme'];
    if ( !file_exists(__DIR__ . $slash .'theme' . $slash . $theme) ) $theme = '';
    if ( $theme=='' ) {
        $tmp = getConfig('customTheme');
        if ( $tmp!='' ) $theme = $tmp;
    }
    if ( $theme=='' ) {
        $theme = getConfig('theme');
        if ( $theme=='' || !file_exists(__DIR__ . $slash .'theme' . $slash . $theme) ) $theme = 'classic.html';
    }
    if (substr($theme,-4)=='.php') {
        @ob_start();
        include 'theme/' . $theme;
        $html = ob_get_clean();
    } else {
        if (file_exists(__DIR__ . $slash .'theme' . $slash . $theme)) {
            $file_path = __DIR__ . $slash .'theme' . $slash . $theme;
            $html = file_get_contents($file_path);
        } else {
            if (!($html = getcache('customTheme'))) {
                $file_path = $theme;
                $tmp = curl('GET', $file_path, false, [], 1);
                if ($tmp['stat']==302) {
                    error_log1(json_encode($tmp));
                    $tmp = curl('GET', $tmp["returnhead"]["Location"]);
                }
                if (!!$tmp['body']) $html = $tmp['body'];
                savecache('customTheme', $html, '', 9999);
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
            // 加密状态
            if (!getConfig('dontBasicAuth')) {
                // use Basic Auth
                return output('Need password.', 401, ['WWW-Authenticate'=>'Basic realm="Secure Area"']);
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
            while (strpos($html, '<!--HeadomfStart-->')) {
                $tmp = splitfirst($html, '<!--HeadomfStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--HeadomfEnd-->');
                $html .= $tmp[1];
            }
            while (strpos($html, '<!--HeadmdStart-->')) {
                $tmp = splitfirst($html, '<!--HeadmdStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--HeadmdEnd-->');
                $html .= $tmp[1];
            }
            while (strpos($html, '<!--ReadmemdStart-->')) {
                $tmp = splitfirst($html, '<!--ReadmemdStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--ReadmemdEnd-->');
                $html .= $tmp[1];
            }
            while (strpos($html, '<!--FootomfStart-->')) {
                $tmp = splitfirst($html, '<!--FootomfStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--FootomfEnd-->');
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
        if ($_SERVER['is_guestup_path']||( $_SERVER['admin']&&$files['type']=='folder'&&$_SERVER['ishidden']<4 )) {
            while (strpos($html, '<!--UploadJsStart-->')) {
                while (strpos($html, '<!--UploadJsStart-->')) $html = str_replace('<!--UploadJsStart-->', '', $html);
                while (strpos($html, '<!--UploadJsEnd-->')) $html = str_replace('<!--UploadJsEnd-->', '', $html);
            }
            if (baseclassofdrive()=='Onedrive') while (strpos($html, '<!--OnedriveUploadJsStart-->')) {
                while (strpos($html, '<!--OnedriveUploadJsStart-->')) $html = str_replace('<!--OnedriveUploadJsStart-->', '', $html);
                while (strpos($html, '<!--OnedriveUploadJsEnd-->')) $html = str_replace('<!--OnedriveUploadJsEnd-->', '', $html);
                $tmp[1] = 'a';
                while ($tmp[1]!='') {
                    $tmp = splitfirst($html, '<!--AliyundriveUploadJsStart-->');
                    $html = $tmp[0];
                    $tmp = splitfirst($tmp[1], '<!--AliyundriveUploadJsEnd-->');
                    $html .= $tmp[1];
                }
            }
            if (baseclassofdrive()=='Aliyundrive') while (strpos($html, '<!--AliyundriveUploadJsStart-->')) {
                while (strpos($html, '<!--AliyundriveUploadJsStart-->')) $html = str_replace('<!--AliyundriveUploadJsStart-->', '', $html);
                while (strpos($html, '<!--AliyundriveUploadJsEnd-->')) $html = str_replace('<!--AliyundriveUploadJsEnd-->', '', $html);
                $tmp[1] = 'a';
                while ($tmp[1]!='') {
                    $tmp = splitfirst($html, '<!--OnedriveUploadJsStart-->');
                    $html = $tmp[0];
                    $tmp = splitfirst($tmp[1], '<!--OnedriveUploadJsEnd-->');
                    $html .= $tmp[1];
                }
            }
            while (strpos($html, '<!--constStr@Calculate-->')) $html = str_replace('<!--constStr@Calculate-->', getconstStr('Calculate'), $html);
        } else {
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--UploadJsStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--UploadJsEnd-->');
                $html .= $tmp[1];
            }
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--OnedriveUploadJsStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--OnedriveUploadJsEnd-->');
                $html .= $tmp[1];
            }
            $tmp[1] = 'a';
            while ($tmp[1]!='') {
                $tmp = splitfirst($html, '<!--AliyundriveUploadJsStart-->');
                $html = $tmp[0];
                $tmp = splitfirst($tmp[1], '<!--AliyundriveUploadJsEnd-->');
                $html .= $tmp[1];
            }
        }

        if ($files['type']=='file') {
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
            //while (strpos($html, '<!--FileDownUrl-->')) $html = str_replace('<!--FileDownUrl-->', $files['url'], $html);
            while (strpos($html, '<!--FileDownUrl-->')) $html = str_replace('<!--FileDownUrl-->', path_format($_SERVER['base_disk_path'] . '/' . $path), $html);
            while (strpos($html, '<!--FileEncodeReplaceUrl-->')) $html = str_replace('<!--FileEncodeReplaceUrl-->', path_format($_SERVER['base_disk_path'] . '/' . $path), $html);
            while (strpos($html, '<!--FileName-->')) $html = str_replace('<!--FileName-->', $files['name'], $html);
            $html = str_replace('<!--FileEncodeDownUrl-->', urlencode($files['url']), $html);
            $html = str_replace('<!--constStr@ClicktoEdit-->', getconstStr('ClicktoEdit'), $html);
            $html = str_replace('<!--constStr@CancelEdit-->', getconstStr('CancelEdit'), $html);
            $html = str_replace('<!--constStr@Save-->', getconstStr('Save'), $html);
            while (strpos($html, '<!--TxtContent-->')) $html = str_replace('<!--TxtContent-->', htmlspecialchars(curl('GET', $files['url'])['body']), $html);
            $html = str_replace('<!--constStr@FileNotSupport-->', getconstStr('FileNotSupport'), $html);


            //$html = str_replace('<!--constStr@File-->', getconstStr('File'), $html);
        } elseif ($files['type']=='folder') {
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
            foreach ($files['list'] as $file) {
                if ($file['type']=='folder') {
                    if ($_SERVER['admin'] or !isHideFile($file['name'])) {
                        $filenum++;
                        $FolderListStr = str_replace('<!--FileEncodeReplaceUrl-->', path_format($_SERVER['base_disk_path'] . '/' . $path . '/' . encode_str_replace($file['name'])), $FolderList);
                        $FolderListStr = str_replace('<!--FileId-->', $file['id'], $FolderListStr);
                        $FolderListStr = str_replace('<!--FileEncodeReplaceName-->', str_replace('&','&amp;', $file['showname']?$file['showname']:$file['name']), $FolderListStr);
                        $FolderListStr = str_replace('<!--lastModifiedDateTime-->', time_format($file['time']), $FolderListStr);
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
            foreach ($files['list'] as $file) {
                if ($file['type']=='file') {
                    if ($_SERVER['admin'] or !isHideFile($file['name'])) {
                        $filenum++;
                        $ext = strtolower(substr($file['name'], strrpos($file['name'], '.') + 1));
                        $FolderListStr = str_replace('<!--FileEncodeReplaceUrl-->', path_format($_SERVER['base_disk_path'] . '/' . $path . '/' . encode_str_replace($file['name'])), $FolderList);
                        $FolderListStr = str_replace('<!--FileExt-->', $ext, $FolderListStr);
                        if (in_array($ext, $exts['music'])) $FolderListStr = str_replace('<!--FileExtType-->', 'audio', $FolderListStr);
                        elseif (in_array($ext, $exts['video'])) $FolderListStr = str_replace('<!--FileExtType-->', 'iframe', $FolderListStr);
                        else $FolderListStr = str_replace('<!--FileExtType-->', '', $FolderListStr);
                        $FolderListStr = str_replace('<!--FileEncodeReplaceName-->', str_replace('&','&amp;', $file['name']), $FolderListStr);
                        $FolderListStr = str_replace('<!--FileId-->', $file['id'], $FolderListStr);
                        //$FolderListStr = str_replace('<!--FileEncodeReplaceUrl-->', path_format($_SERVER['base_disk_path'] . '/' . $path . '/' . str_replace('&','&amp;', $file['name'])), $FolderListStr);
                        $FolderListStr = str_replace('<!--lastModifiedDateTime-->', time_format($file['time']), $FolderListStr);
                        $FolderListStr = str_replace('<!--size-->', size_format($file['size']), $FolderListStr);
                        if (!!$IconValues) {
                            foreach ($IconValues as $key1 => $value1) {
                                if (isset($exts[$key1])&&in_array($ext, $exts[$key1])) {
                                    $FolderListStr = str_replace('<!--IconValue-->', $value1, $FolderListStr);
                                }
                                if ($ext==$key1) {
                                    $FolderListStr = str_replace('<!--IconValue-->', $value1, $FolderListStr);
                                }
                                //error_log1('file:'.$file['name'].':'.$key1);
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

            if ($files['childcount']>200) {
                while (strpos($html, '<!--MorePageStart-->')) $html = str_replace('<!--MorePageStart-->', '', $html);
                while (strpos($html, '<!--MorePageEnd-->')) $html = str_replace('<!--MorePageEnd-->', '', $html);
                
                $pagenum = $files['page'];
                if ($pagenum=='') $pagenum = 1;
                $maxpage = ceil($files['childcount']/200);

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
            // $title .= ' - ' . $diskname;
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
        } elseif ($files['type']=='folder') {
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
            $html .= str_replace('<!--BackgroundUrl-->', getConfig('background'), $tmp[0]);
        }
        $html .= $tmp[1];

        $tmp = splitfirst($html, '<!--BackgroundMStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--BackgroundMEnd-->');
        if (getConfig('backgroundm')) {
            $html .= str_replace('<!--BackgroundMUrl-->', getConfig('backgroundm'), $tmp[0]);
        }
        $html .=  $tmp[1];

        $tmp = splitfirst($html, '<!--PathArrayStart-->');
        $html = $tmp[0];
        if ($tmp[1]!='') {
            $tmp = splitfirst($tmp[1], '<!--PathArrayEnd-->');
            $PathArrayStr = $tmp[0];
            $tmp_url = $_SERVER['base_disk_path'];
            $tmp_path = str_replace('&','&amp;', substr(urldecode($_SERVER['PHP_SELF']), strlen($tmp_url)));
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
        }

        $tmp = splitfirst($html, '<!--DiskPathArrayStart-->');
        $html = $tmp[0];
        if ($tmp[1]!='') {
            $tmp = splitfirst($tmp[1], '<!--DiskPathArrayEnd-->');
            $PathArrayStr = $tmp[0];
            $tmp_url = $_SERVER['base_path'];
            $tmp_path = str_replace('&','&amp;', substr(urldecode($_SERVER['PHP_SELF']), strlen($tmp_url)));
            while ($tmp_path!='') {
                $tmp1 = splitfirst($tmp_path, '/');
                $folder1 = $tmp1[0];
                if ($folder1!='') {
                    $tmp_url .= $folder1 . '/';
                    $PathArrayStr1 = str_replace('<!--PathArrayLink-->', ($folder1==$files['name']?'':$tmp_url), $PathArrayStr);
                    $PathArrayStr1 = str_replace('<!--PathArrayName-->', ($folder1==$_SERVER['disktag']?(getConfig('diskname')==''?$_SERVER['disktag']:getConfig('diskname')):$folder1), $PathArrayStr1);
                    $html .= $PathArrayStr1;
                }
                $tmp_path = $tmp1[1];
            }
            $html .= $tmp[1];
        }
        
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
        $current_url = path_format($_SERVER['PHP_SELF'] . '/');
        if ($current_url !== $_SERVER['base_path']) {
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
        $diskname = getConfig('diskname', $_SERVER['disktag']);
        if ($diskname=='') $diskname = $_SERVER['disktag'];
        //if (strlen($diskname)>15) $diskname = substr($diskname, 0, 12).'...';
        while (strpos($html, '<!--DiskNameNow-->')) $html = str_replace('<!--DiskNameNow-->', $diskname, $html);
        
        $tmp = splitfirst($html, '<!--HeadomfStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--HeadomfEnd-->');
        if (isset($files['list']['head.omf'])) {
            $headomf = str_replace('<!--HeadomfContent-->', get_content(spurlencode(path_format($path . '/head.omf'), '/'))['content']['body'], $tmp[0]);
        }
        $html .= $headomf . $tmp[1];
        
        $tmp = splitfirst($html, '<!--HeadmdStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--HeadmdEnd-->');
        if (isset($files['list']['head.md'])) {
            $headmd = str_replace('<!--HeadmdContent-->', get_content(spurlencode(path_format($path . '/head.md'), '/'))['content']['body'], $tmp[0]);
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
        if (isset($files['list']['readme.md'])) {
            $Readmemd = str_replace('<!--ReadmemdContent-->', get_content(spurlencode(path_format($path . '/readme.md'),'/'))['content']['body'], $tmp[0]);
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
        if (isset($files['list']['foot.omf'])) {
            $Footomf = str_replace('<!--FootomfContent-->', get_content(spurlencode(path_format($path . '/foot.omf'),'/'))['content']['body'], $tmp[0]);
        }
        $html .= $Footomf . $tmp[1];

        
        $tmp = splitfirst($html, '<!--MdRequireStart-->');
        $html = $tmp[0];
        $tmp = splitfirst($tmp[1], '<!--MdRequireEnd-->');
        if (isset($files['list']['head.md'])||isset($files['list']['readme.md'])) {
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
        if ($files['type']=='folder') {
            foreach ($files['list'] as $file) {
                if ($file['type']=='folder') {
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
            foreach ($files['list'] as $file) if ($_SERVER['admin'] or !isHideFile($file['name'])) {
                $tmp = [];
                $tmp['name'] = $file['name'];
                $tmp['size'] = size_format($file['size']);
                $tmp['date'] = time_format($file['lastModifiedDateTime']);
                $tmp['@time'] = $file['date'];
                $tmp['@type'] = ($file['type']=='folder')?'folder':'file';
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
        //$ip2city = json_decode(curl('GET', 'http://ip.taobao.com/outGetIpInfo?ip=' . $_SERVER['REMOTE_ADDR'] . '&accessKey=alibaba-inc')['body'], true);
        //if ($ip2city['code']===0) $city = ' ' . $ip2city['data']['city'];
        $html = str_replace('<!--FootStr-->', date("Y-m-d H:i:s") . " " . getconstStr('Week')[date("w")] . " " . $_SERVER['REMOTE_ADDR'] . $city . ' Runningtime:' . $exetime . 's Mem:' . size_format(memory_get_usage()), $html);
    }

    if ($_SERVER['admin']||!getConfig('disableChangeTheme')) {
        $theme_arr = scandir(__DIR__ . $slash . 'theme');
        $selecttheme = '
    <div style="position: fixed;right: 10px;bottom: 10px;">
        <select name="theme" onchange="changetheme(this.options[this.options.selectedIndex].value)">
            <option value="">'.getconstStr('Theme').'</option>';
        foreach ($theme_arr as $v1) {
            if ($v1!='.' && $v1!='..') $selecttheme .= '
            <option value="' . $v1 . '"' . ($v1==$theme?' selected="selected"':'') . '>' . $v1 . '</option>';
        }
        $selecttheme .= '
        </select>
    </div>
';
        $selectthemescript ='
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
        $tmp = splitfirst($html, '</body>');
        $html = $tmp[0] . $selecttheme . '</body>' . $selectthemescript . $tmp[1];
    }

    $tmp = splitfirst($html, '</title>');
    $html = $tmp[0] . '</title>' . $authinfo . $tmp[1];
    if (isset($_SERVER['Set-Cookie'])) return output($html, $statusCode, [ 'Set-Cookie' => $_SERVER['Set-Cookie'], 'Content-Type' => 'text/html' ]);
    return output($html, $statusCode);
}
