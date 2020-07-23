<?php
    // https://help.aliyun.com/document_detail/53252.html
    // https://github.com/aliyun/fc-php-sdk/blob/master/src/AliyunFC/Client.php

use AliyunFC\Client;

function printInput($event, $context)
{
    if (strlen(json_encode($event['body']))>500) $event['body']=substr($event['body'],0,strpos($event['body'],'base64')+30) . '...Too Long!...' . substr($event['body'],-50);
    echo urldecode(json_encode($event, JSON_PRETTY_PRINT)) . '
 
' . urldecode(json_encode($context, JSON_PRETTY_PRINT)) . '
 
';
}

function GetGlobalVariable($event)
{
    $_GET = $event['queryString'];
    foreach ($_GET as $k => $v) {
        if ($v=='') $_GET[$k] = true;
    }
    $postbody = explode("&",$event['body']);
    foreach ($postbody as $postvalues) {
        $pos = strpos($postvalues,"=");
        $_POST[urldecode(substr($postvalues,0,$pos))]=urldecode(substr($postvalues,$pos+1));
    }
    $cookiebody = explode("; ",$event['headers']['Cookie'][0]);
    foreach ($cookiebody as $cookievalues) {
        $pos = strpos($cookievalues,"=");
        $_COOKIE[urldecode(substr($cookievalues,0,$pos))]=urldecode(substr($cookievalues,$pos+1));
    }
    $_SERVER['FC_SERVER_PATH'] = '/var/fc/runtime/php7.2';
}

function GetPathSetting($event, $context)
{
    $_SERVER['firstacceptlanguage'] = strtolower(splitfirst(splitfirst($event['headers']['Accept-Language'][0],';')[0],',')[0]);
    $_SERVER['accountId'] = $context['accountId'];
    $_SERVER['region'] = $context['region'];
    $_SERVER['service_name'] = $context['service']['name'];
    $_SERVER['function_name'] = $context['function']['name'];

        $_SERVER['base_path'] = '/';
        $path = $event['path'];
        //$path = spurlencode($path, '/');

    if (substr($path,-1)=='/') $path=substr($path,0,-1);
    $_SERVER['is_guestup_path'] = is_guestup_path($path);
    $_SERVER['PHP_SELF'] = path_format($_SERVER['base_path'] . $path);
    $_SERVER['REMOTE_ADDR'] = $event['clientIP'];
    $_SERVER['HTTP_X_REQUESTED_WITH'] = $event['headers']['X-Requested-With'][0];
    return $path;
}

function getConfig($str, $disktag = '')
{
    global $InnerEnv;
    global $Base64Env;
    if (in_array($str, $InnerEnv)) {
        if ($disktag=='') $disktag = $_SERVER['disktag'];
        $env = json_decode(getenv($disktag), true);
        if (isset($env[$str])) {
            if (in_array($str, $Base64Env)) return equal_replace($env[$str],1);
            else return $env[$str];
        }
    } else {
        if (in_array($str, $Base64Env)) return equal_replace(getenv($str),1);
        else return getenv($str);
    }
    return '';
}

function setConfig($arr, $disktag = '')
{
    global $InnerEnv;
    global $Base64Env;
    if ($disktag=='') $disktag = $_SERVER['disktag'];
    $disktags = explode("|",getConfig('disktag'));
    $diskconfig = json_decode(getenv($disktag), true);
    $tmp = [];
    $indisk = 0;
    $oparetdisk = 0;
    foreach ($arr as $k => $v) {
        if (in_array($k, $InnerEnv)) {
            if (in_array($k, $Base64Env)) $diskconfig[$k] = equal_replace($v);
            else $diskconfig[$k] = $v;
            $indisk = 1;
        } elseif ($k=='disktag_add') {
            array_push($disktags, $v);
            $oparetdisk = 1;
        } elseif ($k=='disktag_del') {
            $disktags = array_diff($disktags, [ $v ]);
            $tmp[$v] = '';
            $oparetdisk = 1;
        } else {
            if (in_array($k, $Base64Env)) $tmp[$k] = equal_replace($v);
            else $tmp[$k] = $v;
        }
    }
    if ($indisk) {
        $diskconfig = array_filter($diskconfig, 'array_value_isnot_null');
        ksort($diskconfig);
        $tmp[$disktag] = json_encode($diskconfig);
    }
    if ($oparetdisk) {
        $disktags = array_unique($disktags);
        foreach ($disktags as $disktag) if ($disktag!='') $disktag_s .= $disktag . '|';
        if ($disktag_s!='') $tmp['disktag'] = substr($disktag_s, 0, -1);
        else $tmp['disktag'] = '';
    }
//    echo '正式设置：'.json_encode($tmp,JSON_PRETTY_PRINT).'
//';
    $response = updateEnvironment($tmp, $_SERVER['accountId'], $_SERVER['region'], $_SERVER['service_name'], $_SERVER['function_name'], getConfig('AccessKeyID'), getConfig('AccessKeySecret'));
    //WaitSCFStat();
    return $response;
}

function install()
{
    global $constStr;
    if ($_GET['install2']) {
        $tmp['admin'] = $_POST['admin'];
        setConfig($tmp);
        if (needUpdate()) {
            OnekeyUpate();
            return message('update to github version, reinstall.
    <script>
        var expd = new Date();
        expd.setTime(expd.getTime()+(2*60*60*1000));
        var expires = "expires="+expd.toGMTString();
        document.cookie=\'language=; path=/; \'+expires;
    </script>
    <meta http-equiv="refresh" content="3;URL=' . $url . '">', 'Program updating', 201);
        }
        return output('Jump
    <script>
        var expd = new Date();
        expd.setTime(expd.getTime()+(2*60*60*1000));
        var expires = "expires="+expd.toGMTString();
        document.cookie=\'language=; path=/; \'+expires;
    </script>
    <meta http-equiv="refresh" content="3;URL=' . path_format($_SERVER['base_path'] . '/') . '">', 302);
    }
    if ($_GET['install1']) {
        //if ($_POST['admin']!='') {
            $tmp['timezone'] = $_COOKIE['timezone'];
            $AccessKeyID = getConfig('AccessKeyID');
            if ($AccessKeyID=='') {
                $AccessKeyID = $_POST['AccessKeyID'];
                $tmp['AccessKeyID'] = $AccessKeyID;
            }
            $AccessKeySecret = getConfig('AccessKeySecret');
            if ($AccessKeySecret=='') {
                $AccessKeySecret = $_POST['AccessKeySecret'];
                $tmp['AccessKeySecret'] = $AccessKeySecret;
            }
            $response = json_decode(SetbaseConfig($tmp, $_SERVER['accountId'], $_SERVER['region'], $_SERVER['service_name'], $_SERVER['function_name'], $AccessKeyID, $AccessKeySecret), true);
            if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
                return message($html, $title, 201);
            } else {
                $html .= '
    <form action="?install2" method="post" onsubmit="return notnull(this);">
        <label>'.getconstStr('SetAdminPassword').':<input name="admin" type="password" placeholder="' . getconstStr('EnvironmentsDescription')['admin'] . '" size="' . strlen(getconstStr('EnvironmentsDescription')['admin']) . '"></label><br>
        <input type="submit" value="'.getconstStr('Submit').'">
    </form>
    <script>
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
        //}
    }
    if ($_GET['install0']) {
        $html .= '
    <form action="?install1" method="post" onsubmit="return notnull(this);">
language:<br>';
        foreach ($constStr['languages'] as $key1 => $value1) {
            $html .= '
        <label><input type="radio" name="language" value="'.$key1.'" '.($key1==$constStr['language']?'checked':'').' onclick="changelanguage(\''.$key1.'\')">'.$value1.'</label><br>';
        }
        if (getConfig('AccessKeyID')==''||getConfig('AccessKeySecret')=='') $html .= '
        <a href="https://usercenter.console.aliyun.com/?#/manage/ak" target="_blank">'.getconstStr('Create').' AccessKeyID & AccessKeySecret</a><br>
        <label>AccessKeyID:<input name="AccessKeyID" type="text" placeholder="" size=""></label><br>
        <label>AccessKeySecret:<input name="AccessKeySecret" type="text" placeholder="" size=""></label><br>';
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
        {';
        if (getConfig('AccessKeyID')==''||getConfig('AccessKeySecret')=='') $html .= '
            if (t.AccessKeyID.value==\'\') {
                alert(\'input AccessKeyID\');
                return false;
            }
            if (t.AccessKeySecret.value==\'\') {
                alert(\'input SecretKey\');
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
    $title = 'Error';
    return message($html, $title, 201);
}

function getfunctioninfo($accountId, $region, $service_name, $function_name, $AccessKeyID, $AccessKeySecret)
{
    $fcClient = new Client([
        "endpoint" => 'https://'.$accountId.'.'.$region.'.fc.aliyuncs.com',
        "accessKeyID" => $AccessKeyID,
        "accessKeySecret" => $AccessKeySecret
    ]);
    return $fcClient->getFunction($service_name, $function_name);
}

function updateEnvironment($Envs, $accountId, $region, $service_name, $function_name, $AccessKeyID, $AccessKeySecret)
{
    //print_r($Envs);
    $fcClient = new Client([
        "endpoint" => 'https://'.$accountId.'.'.$region.'.fc.aliyuncs.com',
        "accessKeyID" => $AccessKeyID,
        "accessKeySecret" => $AccessKeySecret
    ]);
    $tmp = $fcClient->getFunction($service_name, $function_name)['data'];
    //$tmp = getfunctioninfo($accountId, $region, $service_name, $function_name, $AccessKeyID, $AccessKeySecret)['data'];
    foreach ($tmp['environmentVariables'] as $key => $value ) {
        $tmp_env[$key] = $value;
    }
    foreach ($Envs as $key1 => $value1) {
        $tmp_env[$key1] = $value1;
    }
    $tmp_env = array_filter($tmp_env, 'array_value_isnot_null'); // remove null. 清除空值
    ksort($tmp_env);

    $tmpdata['functionName'] = $tmp['functionName'];
    $tmpdata['description'] = $tmp['description'];
    $tmpdata['memorySize'] = $tmp['memorySize'];
    $tmpdata['timeout'] = $tmp['timeout'];
    $tmpdata['runtime'] = $tmp['runtime'];
    $tmpdata['handler'] = $tmp['handler'];
    $tmpdata['environmentVariables'] = $tmp_env;
    $tmpdata['code']['zipFile'] = base64_encode( file_get_contents($fcClient->getFunctionCode($service_name, $function_name)['data']['url']) );
    return $fcClient->updateFunction($service_name, $function_name, $tmpdata);
}

function SetbaseConfig($Envs, $accountId, $region, $service_name, $function_name, $AccessKeyID, $AccessKeySecret)
{
    //echo json_encode($Envs,JSON_PRETTY_PRINT);
    $fcClient = new Client([
        "endpoint" => 'https://'.$accountId.'.'.$region.'.fc.aliyuncs.com',
        "accessKeyID" => $AccessKeyID,
        "accessKeySecret" => $AccessKeySecret
    ]);
    $tmp = $fcClient->getFunction($service_name, $function_name)['data'];
    // $tmp = getfunctioninfo($accountId, $region, $service_name, $function_name, $AccessKeyID, $AccessKeySecret)['data'];
    foreach ($tmp['environmentVariables'] as $key => $value ) {
        $tmp_env[$key] = $value;
    }
    foreach ($Envs as $key1 => $value1) {
        $tmp_env[$key1] = $value1;
    }
    $tmp_env = array_filter($tmp_env, 'array_value_isnot_null'); // remove null. 清除空值
    ksort($tmp_env);

    $tmpdata['functionName'] = $function_name;
    $tmpdata['description'] = 'Onedrive index and manager in Ali FC.';
    $tmpdata['memorySize'] = 128;
    $tmpdata['timeout'] = 30;
    $tmpdata['runtime'] = 'php7.2';
    $tmpdata['handler'] = 'index.handler';
    $tmpdata['environmentVariables'] = $tmp_env;
    $tmpdata['code']['zipFile'] = base64_encode( file_get_contents($fcClient->getFunctionCode($service_name, $function_name)['data']['url']) );
    return $fcClient->updateFunction($service_name, $function_name, $tmpdata);
}

function updateProgram($accountId, $region, $service_name, $function_name, $AccessKeyID, $AccessKeySecret, $source)
{
    //WaitSCFStat();
    $fcClient = new Client([
        "endpoint" => 'https://'.$accountId.'.'.$region.'.fc.aliyuncs.com',
        "accessKeyID" => $AccessKeyID,
        "accessKeySecret" => $AccessKeySecret
    ]);
    $tmp = $fcClient->getFunction($service_name, $function_name)['data'];
    //$tmp = getfunctioninfo($accountId, $region, $service_name, $function_name, $AccessKeyID, $AccessKeySecret)['data'];

    $tmpdata['functionName'] = $tmp['functionName'];
    $tmpdata['description'] = $tmp['description'];
    $tmpdata['memorySize'] = $tmp['memorySize'];
    $tmpdata['timeout'] = $tmp['timeout'];
    $tmpdata['runtime'] = $tmp['runtime'];
    $tmpdata['handler'] = $tmp['handler'];
    $tmpdata['environmentVariables'] = $tmp['environmentVariables'];
    $tmpdata['code']['zipFile'] = base64_encode( file_get_contents($source) );

    return $fcClient->updateFunction($service_name, $function_name, $tmpdata);
}

function api_error($response)
{
    return !isset($response['data']);
}

function api_error_msg($response)
{
    return $response;
    return $response['Error']['Code'] . '<br>
' . $response['Error']['Message'] . '<br><br>
function_name:' . $_SERVER['function_name'] . '<br>
Region:' . $_SERVER['Region'] . '<br>
namespace:' . $_SERVER['namespace'] . '<br>
<button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>';
}

function setConfigResponse($response)
{
    return $response;
}

function OnekeyUpate($auth = 'qkqpttgf', $project = 'OneManager-php', $branch = 'master')
{
    $source = '/tmp/code.zip';
    $outPath = '/tmp/';

    // 从github下载对应tar.gz，并解压
    $url = 'https://github.com/' . $auth . '/' . $project . '/tarball/' . urlencode($branch) . '/';
    $tarfile = '/tmp/github.tar.gz';
    file_put_contents($tarfile, file_get_contents($url));
    $phar = new PharData($tarfile);
    $html = $phar->extractTo($outPath, null, true);//路径 要解压的文件 是否覆盖

    // 获取解压出的目录名
/*
    @ob_start();
    passthru('ls /tmp | grep '.$auth.'-'.$project.'',$stat);
            $html.='状态：' . $stat . '
    结果：
    ';
    $archivefolder = ob_get_clean();
    if (substr($archivefolder,-1)==PHP_EOL) $archivefolder = substr($archivefolder, 0, -1);
    $outPath .= $archivefolder;
    $html.=htmlspecialchars($archivefolder);
    //return $html;
*/
    $tmp = scandir($outPath);
    $name = $auth.'-'.$project;
    foreach ($tmp as $f) {
        if ( substr($f, 0, strlen($name)) == $name) {
            $outPath .= $f;
            break;
        }
    }

    // 将目录中文件打包成zip
    $zip=new ZipArchive();
    if($zip->open($source, ZipArchive::CREATE)){
        addFileToZip($zip, $outPath); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
        $zip->close(); //关闭处理的zip文件
    }

    return updateProgram($_SERVER['accountId'], $_SERVER['region'], $_SERVER['service_name'], $_SERVER['function_name'], getConfig('AccessKeyID'), getConfig('AccessKeySecret'), $source);
}

function addFileToZip($zip, $rootpath, $path = '')
{
    if (substr($rootpath,-1)=='/') $rootpath = substr($rootpath, 0, -1);
    if (substr($path,0,1)=='/') $path = substr($path, 1);
    $handler=opendir(path_format($rootpath.'/'.$path)); //打开当前文件夹由$path指定。
    while($filename=readdir($handler)){
        if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
            $nowname = path_format($rootpath.'/'.$path."/".$filename);
            if(is_dir($nowname)){// 如果读取的某个对象是文件夹，则递归
                addFileToZip($zip, $rootpath, $path."/".$filename);
            }else{ //将文件加入zip对象
                $zip->addFile($nowname);
                $newname = $path."/".$filename;
                if (substr($newname,0,1)=='/') $newname = substr($newname, 1);
                $zip->renameName($nowname, $newname);
            }
        }
    }
    @closedir($path);
}
