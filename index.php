<?php
include 'vendor/autoload.php';
include 'conststr.php';
include 'functions.php';

//echo '<pre>'. json_encode($_SERVER, JSON_PRETTY_PRINT).'</pre>';
$path = getpath();
//echo 'path:'. $path;
$_GET = getGET();
//echo '<pre>'. json_encode($_GET, JSON_PRETTY_PRINT).'</pre>';

$re = main($path);
$sendHeaders = array();
foreach ($re['headers'] as $headerName => $headerVal) {
    header($headerName . ': ' . $headerVal, true);
}
http_response_code($re['statusCode']);
echo $re['body'];

function main($path)
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
        if ( $_COOKIE['admin']==md5(getConfig('admin')) || $_POST['password1']==getConfig('admin') ) {
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

    if (!($_SERVER['access_token'] = getcache('access_token'))) {
        $ret = json_decode(curl_request(
            $_SERVER['oauth_url'] . 'token',
            'client_id='. $_SERVER['client_id'] .'&client_secret='. $_SERVER['client_secret'] .'&grant_type=refresh_token&requested_token_use=on_behalf_of&refresh_token=' . $refresh_token
        )['body'], true);
        if (!isset($ret['access_token'])) {
            error_log('failed to get access_token. response' . json_encode($ret));
            throw new Exception('failed to get access_token.');
        }
        $_SERVER['access_token'] = $ret['access_token'];
        savecache('access_token', $_SERVER['access_token']);
    }

    if ($_SERVER['ajax']) {
        if ($_GET['action']=='del_upload_cache'&&substr($_GET['filename'],-4)=='.tmp') {
            // del '.tmp' without login. 无需登录即可删除.tmp后缀文件
            $tmp = MSAPI('DELETE',path_format(path_format($_SERVER['list_path'] . path_format($path)) . '/' . spurlencode($_GET['filename']) ),'',$_SERVER['access_token']);
            $path1 = path_format($_SERVER['list_path'] . path_format($path));
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
            if ($tmp['stat']==409) MSAPI('DELETE',$oldname,'',$_SERVER['access_token'])['body'];
            $path1 = path_format($_SERVER['list_path'] . path_format($path));
            savecache('path_' . $path1, json_decode('{}',true), 1);
            return output($tmp['body'],$tmp['stat']);
        }
        if ($_GET['action']=='upbigfile') return bigfileupload($path);
    }
    if ($_SERVER['admin']) {
        $tmp = adminoperate($path);
        if ($tmp['statusCode'] > 0) {
            $path1 = path_format($_SERVER['list_path'] . path_format($path));
            savecache('path_' . $path1, json_decode('{}',true), 1);
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
        return message('<div style="margin:8px;">' . $files['error']['message'] . '</div><a href="javascript:history.back(-1)">'.$constStr['Back'][$constStr['language']].'</a>', $files['error']['code'], $files['error']['stat']);
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
        error_log( json_encode($files) . ' Network Error<br>' );
        $_SERVER['retry']++;
        if ($_SERVER['retry'] < 3) {
            return list_files($path);
        } else return $files;
    }
}

function adminform($name = '', $pass = '', $path = '')
{
    global $constStr;
    $statusCode = 401;
    $html = '<html><head><title>'.$constStr['AdminLogin'][$constStr['language']].'</title><meta charset=utf-8></head>';
    if ($name!=''&&$pass!='') {
        $html .= '<body>'.$constStr['LoginSuccess'][$constStr['language']].'</body></html>';
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
	  <center><h4>'.$constStr['InputPassword'][$constStr['language']].'</h4>
	  <form action="" method="post">
		  <div>
		    <input name="password1" type="password"/>
		    <input type="submit" value="'.$constStr['Login'][$constStr['language']].'">
          </div>
	  </form>
      </center>
	</div>
';
    $html .= '</body></html>';
    return output($html,$statusCode);
}

function EnvOpt($function_name, $needUpdate = 0)
{
    global $constStr;
    $constEnv = [
        //'admin',
        'adminloginpage', 'domain_path', 'guestup_path', 'passfile',
        //'private_path', 
        'public_path', 'sitename', 'language', 'theme'
    ];
    asort($constEnv);
    $html = '<title>OneManager '.$constStr['Setup'][$constStr['language']].'</title>';
    /*if ($_POST['updateProgram']==$constStr['updateProgram'][$constStr['language']]) {
        $response = json_decode(updataProgram($function_name, $Region, $namespace), true)['Response'];
        if (isset($response['Error'])) {
            $html = $response['Error']['Code'] . '<br>
' . $response['Error']['Message'] . '<br><br>
function_name:' . $_SERVER['function_name'] . '<br>
Region:' . $_SERVER['Region'] . '<br>
namespace:' . $namespace . '<br>
<button onclick="location.href = location.href;">'.$constStr['Reflesh'][$constStr['language']].'</button>';
            $title = 'Error';
        } else {
            $html .= $constStr['UpdateSuccess'][$constStr['language']] . '<br>
<button onclick="location.href = location.href;">'.$constStr['Reflesh'][$constStr['language']].'</button>';
            $title = $constStr['Setup'][$constStr['language']];
        }
        return message($html, $title);
    }*/
    if ($_POST['submit1']) {
        foreach ($_POST as $k => $v) {
            if (in_array($k, $constEnv)) {
                //if (!(getConfig($k)==''&&$v=='')) 
                $tmp[$k] = $v;
            }
        }
        if ($tmp['domain_path']!='') {
            $tmp1 = explode("|",$tmp['domain_path']);
            $tmparr = [];
            foreach ($tmp1 as $multidomain_paths){
                $pos = strpos($multidomain_paths,":");
                if ($pos>0) $tmparr[substr($multidomain_paths, 0, $pos)] = path_format(substr($multidomain_paths, $pos+1));
            }
            $tmp['domain_path'] = $tmparr;
        }
        $response = setConfig($tmp);
        if (!$response) {
            $html = $response . '<br>
<button onclick="location.href = location.href;">'.$constStr['Reflesh'][$constStr['language']].'</button>';
            $title = 'Error';
        } else {
            $html .= '<script>location.href=location.href</script>';
        }
    }
    if ($_GET['preview']) {
        $preurl = $_SERVER['PHP_SELF'] . '?preview';
    } else {
        $preurl = path_format($_SERVER['PHP_SELF'] . '/');
    }
    $html .= '
        <a href="'.$preurl.'">'.$constStr['Back'][$constStr['language']].'</a>&nbsp;&nbsp;&nbsp;
        <a href="https://github.com/qkqpttgf/OneManager-php">Github</a><br>';
    /*if ($needUpdate) {
        $html .= '<pre>' . $_SERVER['github_version'] . '</pre>
        <form action="" method="post">
            <input type="submit" name="updateProgram" value="'.$constStr['updateProgram'][$constStr['language']].'">
        </form>';
    } else {
        $html .= $constStr['NotNeedUpdate'][$constStr['language']];
    }*/
    $html .= '
    <form action="" method="post">
    <table border=1 width=100%>';
    foreach ($constEnv as $key) {
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
                <select name="' . $key .'">';
            foreach ($theme_arr as $v1) {
                if ($v1!='.' && $v1!='..') $html .= '
                    <option value="'.$v1.'" '.($v1==getConfig($key)?'selected="selected"':'').'>'.$v1.'</option>';
            }
            $html .= '
                </select>
            </td>
        </tr>';
        } elseif ($key=='domain_path') {
            $tmp = getConfig($key);
            $domain_path = '';
            foreach ($tmp as $k1 => $v1) {
                $domain_path .= $k1 . ':' . $v1 . '|';
            }
            $domain_path = substr($domain_path, 0, -1);
            $html .= '
        <tr>
            <td><label>' . $key . '</label></td>
            <td width=100%><input type="text" name="' . $key .'" value="' . $domain_path . '" placeholder="' . $constStr['EnvironmentsDescription'][$key][$constStr['language']] . '" style="width:100%"></td>
        </tr>';
        } else $html .= '
        <tr>
            <td><label>' . $key . '</label></td>
            <td width=100%><input type="text" name="' . $key .'" value="' . getConfig($key) . '" placeholder="' . $constStr['EnvironmentsDescription'][$key][$constStr['language']] . '" style="width:100%"></td>
        </tr>';
    }
    $html .= '</table>
    <input type="submit" name="submit1" value="'.$constStr['Setup'][$constStr['language']].'">
    </form>';
    return message($html, $constStr['Setup'][$constStr['language']]);
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
            $getoldupinfo_j = curl_request($getoldupinfo['@microsoft.graph.downloadUrl']);
            $getoldupinfo = json_decode($getoldupinfo_j , true);
            if ( json_decode( curl_request($getoldupinfo['uploadUrl']), true)['@odata.context']!='' ) return output($getoldupinfo_j);
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
function adminoperate($path)
{
    global $constStr;
    $path1 = path_format($_SERVER['list_path'] . path_format($path));
    if (substr($path1,-1)=='/') $path1=substr($path1,0,-1);
    $tmparr['statusCode'] = 0;
    if ($_GET['rename_newname']!=$_GET['rename_oldname'] && $_GET['rename_newname']!='') {
        // rename 重命名
        $oldname = spurlencode($_GET['rename_oldname']);
        $oldname = path_format($path1 . '/' . $oldname);
        $data = '{"name":"' . $_GET['rename_newname'] . '"}';
                //echo $oldname;
        $result = MSAPI('PATCH',$oldname,$data,$_SERVER['access_token']);
        savecache('path_' . $path1, json_decode('{}',true), 1);
        return output($result['body'], $result['stat']);
    }
    if ($_GET['delete_name']!='') {
        // delete 删除
        $filename = spurlencode($_GET['delete_name']);
        $filename = path_format($path1 . '/' . $filename);
                //echo $filename;
        $result = MSAPI('DELETE', $filename, '', $_SERVER['access_token']);
        savecache('path_' . $path1, json_decode('{}',true), 1);
        return output($result['body'], $result['stat']);
    }
    if ($_GET['operate_action']==$constStr['encrypt'][$constStr['language']]) {
        // encrypt 加密
        if (getConfig('passfile')=='') return message($constStr['SetpassfileBfEncrypt'][$constStr['language']],'',403);
        if ($_GET['encrypt_folder']=='/') $_GET['encrypt_folder']=='';
        $foldername = spurlencode($_GET['encrypt_folder']);
        $filename = path_format($path1 . '/' . $foldername . '/' . getConfig('passfile'));
                //echo $foldername;
        $result = MSAPI('PUT', $filename, $_GET['encrypt_newpass'], $_SERVER['access_token']);
        savecache('path_' . $path1, json_decode('{}',true), 1);
        return output($result['body'], $result['stat']);
    }
    if ($_GET['move_folder']!='') {
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
            savecache('path_' . $path1, json_decode('{}',true), 1);
            if ($_GET['move_folder'] == '/../') $path2 = path_format( substr($path1, 0, strrpos($path1, '/')) . '/' );
            else $path2 = path_format( $path1 . '/' . $_GET['move_folder'] . '/' );
            savecache('path_' . $path2, json_decode('{}',true), 1);
            return output($result['body'], $result['stat']);
        } else {
            return output('{"error":"Can not Move!"}', 403);
        }
    }
    if ($_POST['editfile']!='') {
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
        if (isset($resultarry['error'])) return message($resultarry['error']['message']. '<hr><a href="javascript:history.back(-1)">上一页</a>','Error',403);
    }
    if ($_GET['create_name']!='') {
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
        savecache('path_' . $path1, json_decode('{}',true), 1);
        return output($result['body'], $result['stat']);
    }
    return $tmparr;
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
    if (!isset($headers['Referer'])) $headers['Referer'] = $url;
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
    curl_setopt($ch, CURLOPT_HTTPHEADER, $sendHeaders);
    $response['body'] = curl_exec($ch);
    $response['stat'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    error_log($response['stat'].'
'.$response['body'].'
');
    return $response;
}

function fetch_files($path = '/')
{
    $path1 = path_format($path);
    $path = path_format($_SERVER['list_path'] . path_format($path));
    if (!($files = getcache('path_' . $path))) {
        // https://docs.microsoft.com/en-us/graph/api/driveitem-get?view=graph-rest-1.0
        // https://docs.microsoft.com/zh-cn/graph/api/driveitem-put-content?view=graph-rest-1.0&tabs=http
        // https://developer.microsoft.com/zh-cn/graph/graph-explorer
        $url = $_SERVER['api_url'];
        if ($path !== '/') {
            $url .= ':' . $path;
            if (substr($url,-1)=='/') $url=substr($url,0,-1);
        }
        $url .= '?expand=children(select=name,size,file,folder,parentReference,lastModifiedDateTime)';
        $arr = curl_request($url, false, ['Authorization' => 'Bearer ' . $_SERVER['access_token']]);
        if ($arr['stat']<500) {
            $files = json_decode($arr['body'], true);
            // echo $path . '<br><pre>' . json_encode($files, JSON_PRETTY_PRINT) . '</pre>';
            if (isset($files['folder'])) {
                if ($files['folder']['childCount']>200) {
                // files num > 200 , then get nextlink
                    $page = $_POST['pagenum']==''?1:$_POST['pagenum'];
                    $files=fetch_files_children($files, $path, $page);
                } else {
                // files num < 200 , then cache
                    savecache('path_' . $path, $files);
                }
            }
            if (isset($files['error'])) {
                $files['error']['stat'] = $arr['stat'];
            }
        } else {
            error_log($arr['body']);
            $files = json_decode( '{"unknownError":{ "stat":'.$arr['stat'].',"message":"'.$arr['body'].'"}}', true);
        }
    }
    return $files;
}
function fetch_files_children($files, $path, $page)
{
    $cachefilename = '.SCFcache_'.$_SERVER['function_name'];
    $maxpage = ceil($files['folder']['childCount']/200);
    if (!($files['children'] = getcache('files_' . $path . '_page_' . $page))) {
        // down cache file get jump info. 下载cache文件获取跳页链接
        $cachefile = fetch_files(path_format($path1 . '/' .$cachefilename));
        if ($cachefile['size']>0) {
            $pageinfo = curl_request($cachefile['@microsoft.graph.downloadUrl'])['body'];
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
                        $url .= ':/children?$select=name,size,file,folder,parentReference,lastModifiedDateTime';
                    } else {
                        $url .= '/children?$select=name,size,file,folder,parentReference,lastModifiedDateTime';
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

function render_list($path, $files)
{
    global $exts;
    global $constStr;

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
      $pretitle = $constStr['Home'][$constStr['language']];
      $n_path=$pretitle;
    }
    $n_path=str_replace('&amp;','&',$n_path);
    $p_path=str_replace('&amp;','&',$p_path);
    $pretitle = str_replace('%23','#',$pretitle);
    $statusCode=200;
    $theme = getConfig('theme');
    if ( $theme=='' || !file_exists('theme/'.$theme) ) $theme = 'classic.php';
    date_default_timezone_set(get_timezone($_COOKIE['timezone']));
    @ob_start();

    include 'theme/'.$theme;

    $html = '<!--
    github ： https://github.com/qkqpttgf/OneManager-php
-->' . ob_get_clean();
    if ($_SERVER['Set-Cookie']!='') return output($html, $statusCode, [ 'Set-Cookie' => $_SERVER['Set-Cookie'], 'Content-Type' => 'text/html' ]);
    return output($html,$statusCode);
}
