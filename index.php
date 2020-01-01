<?php
include 'vendor/autoload.php';
include 'conststr.php';
include 'functions.php';

//echo '<pre>'. json_encode($_SERVER, JSON_PRETTY_PRINT).'</pre>';
//echo '<pre>'. json_encode($_GET, JSON_PRETTY_PRINT).'</pre>';
if (!isset($_SERVER['REDIRECT_URL'])) $_SERVER['REDIRECT_URL'] = '/index.php';
$path = $_SERVER['REDIRECT_URL'];
//echo 'path:'. $path;
$_GET = getGET();
//echo '<pre>'. json_encode($_GET, JSON_PRETTY_PRINT).'</pre>';

$re = main();
$sendHeaders = array();
foreach ($re['headers'] as $headerName => $headerVal) {
    header($headerName . ': ' . $headerVal, true);
}
http_response_code($re['statusCode']);
echo $re['body'];

function main()
{
    global $exts;
    global $constStr;
    config_oauth();
    $_SERVER['list_path'] = getListpath($_SERVER['HTTP_HOST']);
    if ($_SERVER['list_path']=='') $_SERVER['list_path'] = '/';
    $_SERVER['is_guestup_path'] = is_guestup_path($path);
    $_SERVER['PHP_SELF'] = path_format($_SERVER['base_path'] . $path);
    $_SERVER['ajax']=0;
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) if ($_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest') $_SERVER['ajax']=1;

    $refresh_token = getConfig('refresh_token');
    if (!$refresh_token) return get_refresh_token();

    if (getConfig('adminloginpage')=='') {
        $adminloginpage = 'admin';
    } else {
        $adminloginpage = getConfig('adminloginpage');
    }
    if ($_GET[$adminloginpage]) {
        if ($_GET['preview']) {
            $url = $_SERVER['PHP_SELF'] . '?preview';
        } else {
            $url = path_format($_SERVER['PHP_SELF'] . '/');
        }
        if (getConfig('admin')!='') {
            if ($_POST['password1']==getConfig('admin')) {
                return adminform($_SERVER['function_name'].'admin',md5($_POST['password1']),$url);
            } else return adminform();
        } else {
            return output('', 302, [ 'Location' => $url ]);
        }
    }
    if (getConfig('admin')!='')
        if ($_COOKIE['admin']==md5(getConfig('admin')) || $_POST['password1']==getConfig('admin') ) {
            $_SERVER['admin']=1;
            $_SERVER['needUpdate'] = needUpdate();
        } else {
            $_SERVER['admin']=0;
        }
    if ($_GET['setup'])
        if ($_SERVER['admin']) {
            // setup Environments. 设置，对环境变量操作
            return EnvOpt($_SERVER['function_name'], $_SERVER['needUpdate']);
        } else {
            $url = path_format($_SERVER['PHP_SELF'] . '/');
            return output('<script>alert(\''.$constStr['SetSecretsFirst'][$constStr['language']].'\');</script>', 302, [ 'Location' => $url ]);
        }
    $_SERVER['retry'] = 0;
    $cache = null;
    $cache = new \Doctrine\Common\Cache\FilesystemCache(sys_get_temp_dir(), '.Onedrive');
    if (!($_SERVER['access_token'] = $cache->fetch('access_token'))) {
        $ret = json_decode(curl_request(
            $_SERVER['oauth_url'] . 'token',
            'client_id='. $_SERVER['client_id'] .'&client_secret='. $_SERVER['client_secret'] .'&grant_type=refresh_token&requested_token_use=on_behalf_of&refresh_token=' . $refresh_token
        ), true);
        if (!isset($ret['access_token'])) {
            error_log('failed to get access_token. response' . json_encode($ret));
            throw new Exception('failed to get access_token.');
        }
        $_SERVER['access_token'] = $ret['access_token'];
        $cache->save('access_token', $_SERVER['access_token'], $ret['expires_in'] - 60);
    }

    if ($_SERVER['ajax']) {
        if ($_GET['action']=='del_upload_cache'&&substr($_GET['filename'],-4)=='.tmp') {
            // del '.tmp' without login. 无需登录即可删除.tmp后缀文件
            $tmp = MSAPI('DELETE',path_format(path_format($_SERVER['list_path'] . path_format($path)) . '/' . spurlencode($_GET['filename']) ),'',$_SERVER['access_token']);
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
            if ($tmp['stat']==409) MSAPI('DELETE',$oldname,'',$_SERVER['access_token'])['body'];
            return output($tmp['body'],$tmp['stat']);
        }
        if ($_GET['action']=='upbigfile') return bigfileupload($path);
    }
    if ($_SERVER['admin']) {
        $tmp = adminoperate($path);
        if ($tmp['statusCode'] > 0) {
            $path1 = path_format($_SERVER['list_path'] . path_format($path));
            $cache->save('path_' . $path1, json_decode('{}',true), 1);
            return $tmp;
        }
    } else {
        if ($_SERVER['ajax']) return output($constStr['RefleshtoLogin'][$constStr['language']],401);
    }
    $_SERVER['ishidden'] = passhidden($path);
    if ($_GET['thumbnails']) {
        if ($_SERVER['ishidden']<4) {
            if (in_array(strtolower(substr($path, strrpos($path, '.') + 1)), $exts['img'])) {
                return get_thumbnails_url($path);
            } else return output(json_encode($exts['img']),400);
        } else return output('',401);
    }
    $files = list_files($path);
    if (isset($files['file']) && !$_GET['preview']) {
        // is file && not preview mode
        if ($_SERVER['ishidden']<4) return output('', 302, [ 'Location' => $files['@microsoft.graph.downloadUrl'] ]);
    }
    if ( isset($files['folder']) || isset($files['file']) ) {
        return render_list($path, $files);
    } else {
        return output('<div style="margin:8px;">' . $files['error']['message'] . '</div>', 404);
    }
}

function list_files($path)
{
    $path = path_format($path);
    if ($_SERVER['is_guestup_path']&&!$_SERVER['admin']) {
        $files = json_decode('{"folder":{}}', true);
    } elseif ($_SERVER['ishidden']==4) {
        $files = json_decode('{"folder":{}}', true);
    } else {
        $files = fetch_files($path);
    }
    if ( isset($files['folder']) || isset($files['file']) || isset($files['error']) ) {
        return $files;
    } else {
        echo 'Error $files' . json_encode($files, JSON_PRETTY_PRINT);
        $_SERVER['retry']++;
        if ($_SERVER['retry'] < 3) {
            return list_files($path);
        } else return '';
    }
}
?>
