<?php

class Googledrive {
    protected $access_token;
    protected $disktag;

    function __construct($tag) {
        $this->disktag = $tag;
        $this->redirect_uri = 'https://scfonedrive.github.io';
        if (getConfig('client_id', $tag) && getConfig('client_secret', $tag)) {
            $this->client_id = getConfig('client_id', $tag);
            $this->client_secret = getConfig('client_secret', $tag);
        } else {
            $this->client_id = '106151778902-ajieetaab5ondhbvia97n4tr5k0cg8eo.apps.googleusercontent.com';
            $this->client_secret = 'LlCV-rQClzYIKCEqiVddh68G';
        }
        //$this->oauth_url = 'https://www.googleapis.com/oauth2/v4/';
        $this->oauth_url = 'https://accounts.google.com/o/oauth2/';
        $this->api_url = 'https://www.googleapis.com/drive/v3';
        $this->scope = 'https://www.googleapis.com/auth/drive';

        $this->client_secret = urlencode($this->client_secret);
        $this->scope = urlencode($this->scope);
        //$this->DownurlStrName = '@microsoft.graph.downloadUrl';
        //$this->ext_api_url = '/me/drive/root';
        $this->driveId = getConfig('driveId', $tag);
        $res = $this->get_access_token(getConfig('refresh_token', $tag));
    }

    public function isfine()
    {
        if (!$this->access_token) return false;
        else return true;
    }
    public function show_base_class()
    {
        return get_class();
        //$tmp[0] = get_class();
        //$tmp[1] = get_class($this);
        //return $tmp;
    }

    public function ext_show_innerenv()
    {
        return ['driveId'];
    }

    public function list_files($path = '/')
    {
        $files = $this->list_path($path);

        return $this->files_format($files);
    }

    protected function files_format($files)
    {
        if (isset($files['files']) || (isset($files['mimeType'])&&$files['mimeType']=='application/vnd.google-apps.folder') ) {
            $tmp['type'] = 'folder';
            $tmp['id'] = $files['id'];
            $tmp['name'] = $files['name'];
            $tmp['time'] = $files['modifiedTime'];
            $tmp['size'] = $files['size'];
            $tmp['childcount'] = $files['folder']['childCount'];
            $tmp['page'] = $files['folder']['page'];
            foreach ($files['files'] as $file) {
                $filename = strtolower($file['name']);
                if ($file['mimeType']=='application/vnd.google-apps.folder') {
                    $tmp['list'][$filename]['type'] = 'folder';
                } else {
                    $tmp['list'][$filename]['type'] = 'file';
                    //var_dump($file);
                    //echo $file['name'] . ':' . $this->DownurlStrName . ':' . $file[$this->DownurlStrName] . PHP_EOL;
                    $tmp['list'][$filename]['url'] = ($file['downUrl']?$file['downUrl']:$file['webContentLink']);
                    $tmp['list'][$filename]['mime'] = $file['mimeType'];
                }
                $tmp['list'][$filename]['id'] = $file['id'];
                $tmp['list'][$filename]['name'] = $file['name'];
                $tmp['list'][$filename]['time'] = $file['modifiedTime'];
                $tmp['list'][$filename]['size'] = $file['size'];
            }
        } elseif (isset($files['mimeType'])) {
            $tmp['type'] = 'file';
            $tmp['id'] = $files['id'];
            $tmp['name'] = $files['name'];
            $tmp['time'] = $files['modifiedTime'];
            $tmp['size'] = $files['size'];
            $tmp['mime'] = $files['mimeType'];
            $tmp['url'] = ($files['downUrl']?$files['downUrl']:$files['webContentLink']);
            $tmp['content'] = $files['content'];
        } else/*if (isset($files['error']))*/ {
            return $files;
        }
        //error_log1(json_encode($tmp));
        return $tmp;
    }

    protected function list_path($path = '/')
    {
        global $exts;
        while (substr($path, -1)=='/') $path = substr($path, 0, -1);
        if ($path == '') $path = '/';

        if (!($files = getcache('path_' . $path, $this->disktag))) {
            //$response = curl('GET', $this->api_url . '/drives', '', ['Authorization' => 'Bearer ' . $this->access_token]);
            //$response = curl('GET', $this->api_url . '/files?fields=*,files(id,name,mimeType,size,modifiedTime,parents,webContentLink,thumbnailLink),nextPageToken' . (($this->driveId!='')?'&driveId=' . $this->driveId . '&corpora=teamDrive&includeItemsFromAllDrives=true&supportsAllDrives=true':''), '', ['Authorization' => 'Bearer ' . $this->access_token]);
            if ($path == '/' || $path == '') {
                $files = $this->fileList();
                //error_log1('root_id' . $files['id']);
                //$files['id'] = 'root';
                //$files['type'] = 'folder';
            } else {
                $tmp = splitlast($path, '/');
                $parent_path = $tmp[0];
                $filename = urldecode($tmp[1]);
                $parent_folder = $this->list_path($parent_path);
                $i = 0;
                foreach ($parent_folder['files'] as $item) {
                    if ($item['name']==$filename) {
                        if ($item['mimeType']=='application/vnd.google-apps.folder') {
                            $files = $this->fileList($item['id']);
                            $files['type'] = 'folder';
                            $files['id'] = $item['id'];
                            $files['name'] = $item['name'];
                            $files['time'] = $item['modifiedTime'];
                            $files['size'] = $item['size'];
                        } else {
                            if (isset($item['id'])&&$item['shared']!==true) $this->permission('create', $item['id']);
                            //$this->permission('delete', $files['id']);
                            
                            //if (isset($item['mimeType']) && $item['mimeType']!='application/vnd.google-apps.folder') {
                                if (in_array(splitlast($item['name'],'.')[1], $exts['txt'])) {
                                    if (!(isset($item['content'])&&$item['content']['stat']==200)) {
                                        //if (!isset($item['downUrl'])) {
                                            $res = curl('GET', $item['webContentLink'], '', [], 1);
                                            $weblink = $res['returnhead']['Location'];
                                            //if ($weblink!==null) $item['downUrl'] = $weblink;
                                            //else error_log1('Cant get link:' . json_encode($res, JSON_PRETTY_PRINT));
                                        //}
                                        if ($res['stat']==302) {
                                            $content1 = curl('GET', $weblink, '', ["User-Agent"=>"qkqpttgf/OneManager 3.0.0", "Accept"=>"*/*"]);
                                            $item['content'] = $content1;
                                        }// else $content1 = $res;
                                        //error_log1($item['name'] . '~' . json_encode($content1, JSON_PRETTY_PRINT) . PHP_EOL);
                                        $parent_folder['files'][$i] = $item;
                                        savecache('path_' . $path, $parent_folder, $this->disktag);
                                    }
                                }
                            //}
                            
                            //error_log1(json_encode($item, JSON_PRETTY_PRINT));
                            $files = $item;
                        } 
                    }
                    $i++;
                }
                //echo $files['name'];
            }

            if (!$files) {
                $files['error']['code'] = 'Not Found';
                $files['error']['message'] = 'Not Found';
                $files['error']['stat'] = 404;
            } elseif (isset($files['stat'])) {
                $files['error']['stat'] = $files['stat'];
                $files['error']['code'] = 'Error';
                $files['error']['message'] = $files['body'];
            } else {
                savecache('path_' . $path, $files, $this->disktag, 600);
            }
        }
        //error_log1('path:' . $path . ', files:' . json_encode($files, JSON_PRETTY_PRINT));
        //error_log1('path:' . $path . ', files:' . substr(json_encode($files), 0, 150));
        return $files;
    }
    protected function fileList($parent_file_id = '')
    {
        $url = $this->api_url . '/files';

        $url .= '?fields=files(id,name,mimeType,size,modifiedTime,parents,webContentLink,thumbnailLink,shared,permissions,permissionIds),nextPageToken';
        //$url .= '?fields=files(*),nextPageToken';
        //$url .= '?q=mimeType=\'application/vnd.google-apps.folder\'';
        if ($parent_file_id!='') {
            $q = $parent_file_id;
        } else {
            $q = $this->driveId;
        }
        $q = '\'' . $q . '\' in parents and trashed = false';
        $q = urlencode($q);
        $url .= '&q=' . $q;
        if ($this->driveId!='root') $url .= '&driveId=' . $this->driveId . '&corpora=teamDrive&includeItemsFromAllDrives=true&supportsAllDrives=true';

        $header['Authorization'] = 'Bearer ' . $this->access_token;

        $res = curl('GET', $url, '', $header);
        if ($res['stat']==200) return json_decode($res['body'], true);
        else return $res;
    }
    protected function permission($op, $fileId)
    {
        $url = $this->api_url . '/files/' . $fileId . '/permissions';
        if ($op=='create') {
            $method = 'POST';
            $header['Content-Type'] = 'application/json';
            $tmp['role'] = 'reader';
            $tmp['type'] = 'anyone';
            $data = json_encode($tmp);
        } elseif ($op=='delete') {
            $url .= '/anyoneWithLink';
            $method = 'DELETE';
            $data = '';
        } else {
            return false;
        }
        $url .= '?supportsAllDrives=true';
        $header['Authorization'] = 'Bearer ' . $this->access_token;

        $res = curl($method, $url, $data, $header);
        //error_log1('Set Share' . json_encode($res, JSON_PRETTY_PRINT));
        return $res;
    }

    public function Rename($file, $newname) {
        $url = $this->api_url . '/files/' . $file['id'] . '?supportsAllDrives=true';
        $tmp['name'] = $newname;
        $result = $this->GDAPI('PATCH', $url, json_encode($tmp));
        return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
    }
    public function Delete($file) {
        $url = $this->api_url . '/files/' . $file['id'] . '?supportsAllDrives=true';
        $result = $this->GDAPI('DELETE', $url);
        return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
    }
    public function Encrypt($folder, $passfilename, $pass) {
        $existfile = $this->list_path($folder['path'] . '/' . $passfilename);
        if (isset($existfile['name'])) {
            if ($pass==='') {
                // 如果为空，删除
                $this->Delete(['id'=>$existfile['id']]);
                return output('Success', 200);
            } else {
                $result = $this->editFile($existfile['id'], $pass);
            }
        } else {
            if (!$folder['id']) {
                $res = $this->list_path($folder['path']);
                //error_log1('res:' . json_encode($res));
                $folder['id'] = $res['id'];
            }
            if (!$folder['id']) {
                $folder['id'] = $this->driveId;
            }
            $result = $this->createFile_c($folder['id'], $passfilename, $pass);
        }

        //error_log1('2,url:' . $url .' res:' . json_encode($result));
        return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
    }
    public function Move($file, $folder) {
        $nowParentId = $this->list_path($file['path'])['id'];
        if (!$nowParentId) {
            $nowParentId = $this->driveId;
        }
        if (!$folder['id']) {
            $res = $this->list_path($folder['path']);
            //error_log1('res:' . json_encode($res));
            $folder['id'] = $res['id'];
        }
        if (!$folder['id']) {
            $folder['id'] = $this->driveId;
        }
        $url = $this->api_url . '/files/' . $file['id'] . '?removeParents=' . $nowParentId . '&addParents=' . $folder['id'] . '&supportsAllDrives=true';
        //$tmp['name'] = $newname;
        $result = $this->GDAPI('PATCH', $url);
        return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
    }
    public function Copy($file) {
        $url = $this->api_url . '/files/' . $file['id'] . '/copy?supportsAllDrives=true';
        $namearr = splitlast($file['name'], '.');
        date_default_timezone_set('UTC');
        if ($namearr[0]!='') {
            $newname = $namearr[0] . '_' . date("Ymd\_His");
            if ($namearr[1]!='') $newname .= '.' . $namearr[1];
        } else {
            $newname = '.' . $namearr[1] . '_' . date("Ymd\_His");
        }
        $tmp['name'] = $newname;
        $result = $this->GDAPI('POST', $url, json_encode($tmp));
        return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
    }
    public function Edit($file, $content) {
        $tmp = splitlast($file['path'], '/');
        $folderpath = $tmp[0];
        $filename = $tmp[1];
        $existfile = $this->list_path($file['path']);
        if (isset($existfile['name'])) {
            $result = $this->editFile($existfile['id'], $content);
        } else {
            $result = $this->createFile_c($this->list_path($folderpath)['id'], $filename, $content);
        }
        //error_log1('edit: ' . json_encode($result, JSON_PRETTY_PRINT));
        if ($result['stat']==200) return output('success', 0);
        return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
    }
    public function Create($parent, $type, $name, $content = '') {
        $filename = spurlencode($name);
        $filename = path_format($parent['path'] . '/' . $filename);
        $res = $this->list_path($filename);
        //error_log1($filename . '查重:' . json_encode($res, JSON_PRETTY_PRINT) . PHP_EOL);
        if (isset($res['name'])) {
            $tmp['error']['code'] = 'File exist';
            $tmp['error']['message'] = $res;
            $tmp['error']['stat'] = 409;
            return output(json_encode($this->files_format($tmp), JSON_PRETTY_PRINT), $tmp['error']['stat']);
        }
        if (!$parent['id']) {
            $res = $this->list_path($parent['path']);
            //error_log1('找ID:' . json_encode($res));
            $parent['id'] = $res['id'];
        }
        if (!$parent['id']) {
            $parent['id'] = $this->driveId;
        }

        if ($type=='file') {
            $result = $this->createFile_c($parent['id'], $name, $content);
        }
        if ($type=='folder') {
            $result = $this->createFolder($parent['id'], $name);
        }
        //error_log1('data:' . $data . ' res:' . json_encode($result, JSON_PRETTY_PRINT));
        //savecache('path_' . $path1, json_decode('{}',true), $_SERVER['disktag'], 1);
        return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
    }
    public function get_thumbnails_url($path = '/') {
        $res = $this->list_path($path);
        $thumb_url = $res['thumbnailLink'];
        return $thumb_url;
    }
    public function bigfileupload($path) {
        return output('Stop!\nCan not upload form explorer without token.', 403);

        // https://developers.google.com/drive/api/v3/manage-uploads#http---multiple-requests

        if ($_POST['upbigfilename']=='') return output('error: no file name', 400);
        if (!is_numeric($_POST['filesize'])) return output('error: no file size', 400);
        if (!$_SERVER['admin']) if (!isset($_POST['filemd5'])) return output('error: no file md5', 400);
        $filename = $_POST['upbigfilename'];
        $filename = path_format($path . '/' . $filename);
        $res = $this->list_path($filename);
        //error_log1($filename . '查重:' . json_encode($res, JSON_PRETTY_PRINT) . PHP_EOL);
        if (isset($res['name'])) {
            $tmp['error']['code'] = 'File exist';
            $tmp['error']['message'] = json_encode($res);
            $tmp['error']['stat'] = 409;
            return output(json_encode($this->files_format($tmp), JSON_PRETTY_PRINT), $tmp['error']['stat']);
        }
        $tmp = splitlast($_POST['upbigfilename'], '/');
        if ($tmp[1]!='') {
            $fileinfo['name'] = $tmp[1];
            if ($_SERVER['admin']) $fileinfo['path'] = $tmp[0];
        } else {
            $fileinfo['name'] = $_POST['upbigfilename'];
        }
        $fileinfo['size'] = $_POST['filesize'];
        $fileinfo['filelastModified'] = $_POST['filelastModified'];
        if ($_SERVER['admin']) {
            $filename = $fileinfo['name'];
        } else {
            $tmp1 = splitlast($fileinfo['name'], '.');
            if ($tmp1[0]==''||$tmp1[1]=='') $filename = $_POST['filemd5'];
            else $filename = $_POST['filemd5'] . '.' . $tmp1[1];
        }
        $parent = $this->list_path($path . '/' . $fileinfo['path']);
        if (isset($parent['id'])) {
            $parent_file_id = $parent['id'];
        } else {
            $res = $this->createFolder($this->list_path($path)['id'], $fileinfo['path']);
            //error_log1($res['body']);
            $parent_file_id = json_decode($res['body'], true)['id'];
        }
        $url = 'https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable';
        $res = $this->list_path($path);
        //error_log1('找ID:' . json_encode($res));
        $parentId = $res['id'];
        if (!$parentId) {
            $parentId = $this->driveId;
        }
        $tmp['name'] = $_POST['upbigfilename'];
        $tmp['parents'][0] = $parentId;

        $header['Authorization'] = 'Bearer ' . $this->access_token;
        $header['Content-Type'] = 'application/json; charset=UTF-8';
        //$header['Content-Length'] = '';
        //$header['X-Upload-Content-Type'] = '';
        //$header['X-Upload-Content-Length'] = $_POST['filesize'];

        $response = curl('POST', $url, json_encode($tmp), $header, 1);
        return output($response['returnhead']['Location'], $response['stat']);
    }

    protected function editFile($id, $content) {
        $url = 'https://www.googleapis.com/upload/drive/v3/files/' . $id . '?uploadType=multipart&supportsAllDrives=true';

        $boundary = md5($id . date());
        $header['Content-Type'] = 'multipart/related; boundary=' . $boundary;
        $header['Authorization'] = 'Bearer ' . $this->access_token;
        $data = '--' . $boundary . "\r\n";
        $data .= 'Content-Type: application/json; charset=UTF-8' . "\r\n";
        $data .= "\r\n";
        $data .= "\r\n";
        $data .= "\r\n";
        $data .= '--' . $boundary . "\r\n";
        $data .= 'Content-Type: text/plain' . "\r\n";
        $data .= "\r\n";
        $data .= $content . "\r\n";
        $data .= '--' . $boundary . "--";

        $result = curl('PATCH', $url, $data, $header);
        //error_log1('url: ' . $url . ' data: ' . $data . ' result: ' . json_encode($result, JSON_PRETTY_PRINT));
        return $result;
    }
    protected function createFile_c($parentId, $name, $content) {
        while (substr($name, 0, 1)=='/') $name = substr($name, 1);
        while (substr($name, -1)=='/') $name = substr($name, 0, -1);
        if (strpos($name, '/')>0) {
            $p = splitlast($name, '/');
            $res = $this->createFolder($parentId, $p[0]);
            $parentId = json_decode($res['body'], true)['id'];
            $name = $p[1];
        }

        $url = 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&supportsAllDrives=true';
        $tmp['name'] = $name;
        $tmp['parents'][0] = $parentId;

        $boundary = md5($name . date());
        $header['Content-Type'] = 'multipart/related; boundary=' . $boundary;
        $header['Authorization'] = 'Bearer ' . $this->access_token;
        $data = '--' . $boundary . "\r\n";
        $data .= 'Content-Type: application/json; charset=UTF-8' . "\r\n";
        $data .= "\r\n";
        $data .= json_encode($tmp, JSON_PRETTY_PRINT) . "\r\n";
        $data .= "\r\n";
        $data .= '--' . $boundary . "\r\n";
        $data .= 'Content-Type: text/plain' . "\r\n";
        $data .= "\r\n";
        $data .= $content . "\r\n";
        $data .= '--' . $boundary . "--";

        $result = curl('POST', $url, $data, $header);
        return $result;
    }
    protected function createFolder($parentId, $name) {
        while (substr($name, 0, 1)=='/') $name = substr($name, 1);
        while (substr($name, -1)=='/') $name = substr($name, 0, -1);
        if (strpos($name, '/')>0) {
            $p = splitlast($name, '/');
            $res = $this->createFolder($parentId, $p[0]);
            $parentId = json_decode($res['body'], true)['id'];
            $name = $p[1];
        }

        $url = $this->api_url . '/files?&supportsAllDrives=true';

        $tmp['name'] = $name;
        $tmp['parents'][0] = $parentId;
        $tmp['mimeType'] = 'application/vnd.google-apps.folder';
        $data = json_encode($tmp);

        $header['Content-Type'] = 'application/json; charset=UTF-8';
        $header['Authorization'] = 'Bearer ' . $this->access_token;

        $result = curl('POST', $url, $data, $header);
        return $result;
    }
    
    public function AddDisk() {
        global $constStr;
        global $EnvConfigs;

        $envs = '';
        foreach ($EnvConfigs as $env => $v) if (isCommonEnv($env)) $envs .= '\'' . $env . '\', ';
        $url = path_format($_SERVER['PHP_SELF'] . '/');

        if (isset($_GET['Finish'])) {
            if ($this->access_token == '') {
                $refresh_token = getConfig('refresh_token', $this->disktag);
                if (!$refresh_token) {
                    $html = 'No refresh_token config, please AddDisk again or wait minutes.<br>' . $this->disktag;
                    $title = 'Error';
                    return message($html, $title, 201);
                }
                $response = $this->get_access_token($refresh_token);
                if (!$response) return message($this->error['body'], 'Error', $this->error['stat']);
            }

            $tmp = null;
            if ($_POST['DriveType']=='Googledrive') {
                $tmp['driveId'] = 'root';
            } else {
                // 直接是id
                $tmp['driveId'] = $_POST['DriveType'];
            }

            $response = setConfigResponse( setConfig($tmp, $this->disktag) );
            if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
                return message($html, $title, 201);
            } else {
                $str .= '<meta http-equiv="refresh" content="5;URL=' . $url . '">';
                return message($str, getconstStr('WaitJumpIndex'), 201);
            }
        }

        if (isset($_GET['SelectDrive'])) {
            if ($this->access_token == '') {
                $refresh_token = getConfig('refresh_token', $this->disktag);
                if (!$refresh_token) {
                    $html = 'No refresh_token config, please AddDisk again or wait minutes.<br>' . $this->disktag;
                    $title = 'Error';
                    return message($html, $title, 201);
                }
                $response = $this->get_access_token($refresh_token);
                if (!$response) return message($this->error['body'], 'Error', $this->error['stat']);
            }

            $api = $this->api_url . '/drives';
            $arr = curl('GET', $api, '', [ 'Authorization' => 'Bearer ' . $this->access_token ]);
            //if (!($arr['stat']==200||$arr['stat']==403||$arr['stat']==400||$arr['stat']==404))
            if ($arr['stat']!=200) return message($arr['stat'] . json_encode(json_decode($arr['body']), JSON_PRETTY_PRINT), 'Get followedSites', $arr['stat']);
            error_log1($arr['body']);
            $drives = json_decode($arr['body'], true)['drives'];

            $title = 'Select Driver';
            $html = '
<div>
    <form action="?Finish&disktag=' . $_GET['disktag'] . '&AddDisk=' . get_class($this) . '" method="post" onsubmit="return notnull(this);">
        <label><input type="radio" name="DriveType" value="Googledrive" checked>' . 'Use Googledrive ' . getconstStr(' ') . '</label><br>';
            if ($drives[0]!='') foreach ($drives as $k => $v) {
                $html .= '
        <label>
            <input type="radio" name="DriveType" value="' . $v['id'] . '" onclick="document.getElementById(\'sharepointSiteUrl\').value=\'' . $v['webUrl'] . '\';">' . 'Use Google share drive: <br><div style="width:100%;margin:0px 35px">: ' . $v['name'] . '<br>siteid: ' . $v['id'] . '</div>
        </label>';
            }
            $html .= '
        <input type="submit" value="' . getconstStr('Submit') . '">
    </form>
</div>
<script>
        function notnull(t)
        {
            if (t.DriveType.value==\'\') {
                    alert(\'Select a Disk\');
                    return false;
            }
            if (t.DriveType.value==\'Custom\') {
                if (t.sharepointSite.value==\'\') {
                    alert(\'sharepoint Site Address\');
                    return false;
                }
            }
            return true;
        }
    </script>
    ';
            return message($html, $title, 201);
        }

        if (isset($_GET['install2']) && isset($_GET['code'])) {
            $data['client_id'] = $this->client_id;
            $data['client_secret'] = $this->client_secret;
            $data['grant_type'] = 'authorization_code';
            $data['redirect_uri'] = $this->redirect_uri;
            $data['code'] = $_GET['code'];
            $api = $this->oauth_url . 'token';
            //$api = 'https://www.googleapis.com/oauth2/v4/token';
            $tmp = curl('POST',
                $api,
                json_encode($data)
            );
            if ($tmp['stat']==200) $ret = json_decode($tmp['body'], true);
            if (isset($ret['refresh_token'])) {
                $refresh_token = $ret['refresh_token'];
                $str = '
        refresh_token :<br>';
                $str .= '
        <textarea readonly style="width: 95%">' . $refresh_token . '</textarea><br><br>
        ' . getconstStr('SavingToken') . '
        <script>
            var texta=document.getElementsByTagName(\'textarea\');
            for(i=0;i<texta.length;i++) {
                texta[i].style.height = texta[i].scrollHeight + \'px\';
            }
        </script>';
                $tmptoken['refresh_token'] = $refresh_token;
                //$tmptoken['token_expires'] = time()+7*24*60*60;
                $response = setConfigResponse( setConfig($tmptoken, $this->disktag) );
                if (api_error($response)) {
                    $html = api_error_msg($response);
                    $title = 'Error';
                    return message($html, $title, 201);
                } else {
                    savecache('access_token', $ret['access_token'], $this->disktag, $ret['expires_in'] - 60);
                    $str .= '
                <meta http-equiv="refresh" content="3;URL=' . $url . '?AddDisk=' . get_class($this) . '&disktag=' . $_GET['disktag'] . '&SelectDrive">';
                    return message($str, getconstStr('Wait') . ' 3s', 201);
                }
            }
            return message('<pre>' . $tmp['body'] . '</pre>', $tmp['stat']);
            //return message('<pre>' . json_encode($ret, JSON_PRETTY_PRINT) . '</pre>', 500);
        }

        if (isset($_GET['install1'])) {
            if (get_class($this)=='Googledrive') {
                return message('
    <a href="" id="a1">' . getconstStr('JumptoOffice') . '</a>
    <script>
        url=location.protocol + "//" + location.host + "' . $url . '?install2&disktag=' . $_GET['disktag'] . '&AddDisk=' . get_class($this) . '";
        url="' . $this->oauth_url . 'auth?scope=' . $this->scope . '&response_type=code&client_id=' . $this->client_id . '&redirect_uri=' . $this->redirect_uri . '&access_type=offline&state=' . '"+encodeURIComponent(url);
        document.getElementById(\'a1\').href=url;
        //window.open(url,"_blank");
        location.href = url;
    </script>
    ', getconstStr('Wait') . ' 1s', 201);
            } else {
                return message('Something error, retry after a few seconds.', 'Retry', 201);
            }
        }

        if (isset($_GET['install0'])) {
            if ($_POST['disktag_add']!='') {
                $_POST['disktag_add'] = preg_replace('/[^0-9a-zA-Z|_]/i', '', $_POST['disktag_add']);
                $f = substr($_POST['disktag_add'], 0, 1);
                if (strlen($_POST['disktag_add'])==1) $_POST['disktag_add'] .= '_';
                if (isCommonEnv($_POST['disktag_add'])) {
                    return message('Do not input ' . $envs . '<br><button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>', 'Error', 201);
                } elseif (!(('a'<=$f && $f<='z') || ('A'<=$f && $f<='Z'))) {
                    return message('Please start with letters<br><button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>
                    <script>
                    var expd = new Date();
                    expd.setTime(expd.getTime()+1);
                    var expires = "expires="+expd.toGMTString();
                    document.cookie=\'disktag=; path=/; \'+expires;
                    </script>', 'Error', 201);
                }

                $tmp = null;
                // clear envs
                foreach ($EnvConfigs as $env => $v) if (isInnerEnv($env)) $tmp[$env] = '';

                //$this->disktag = $_POST['disktag_add'];
                $tmp['disktag_add'] = $_POST['disktag_add'];
                $tmp['diskname'] = $_POST['diskname'];
                $tmp['Driver'] = 'Googledrive';
                
                    if ($_POST['NT_Drive_custom']=='on') {
                        $tmp['client_id'] = $_POST['NT_client_id'];
                        $tmp['client_secret'] = $_POST['NT_client_secret'];
                    }
                
                $response = setConfigResponse( setConfig($tmp, $this->disktag) );
                if (api_error($response)) {
                    $html = api_error_msg($response);
                    $title = 'Error';
                } else {
                    $title = getconstStr('MayinEnv');
                    $html = getconstStr('Wait') . ' 3s<meta http-equiv="refresh" content="3;URL=' . $url . '?install1&disktag=' . $_GET['disktag'] . '&AddDisk=Googledrive">';
                }
                return message($html, $title, 201);
            }
        }

        $html = '
<div>
    <form id="form1" action="" method="post" onsubmit="return notnull(this);">
        ' . getconstStr('DiskTag') . ': (' . getConfig('disktag') . ')
        <input type="text" name="disktag_add" placeholder="' . getconstStr('EnvironmentsDescription')['disktag'] . '" style="width:100%"><br>
        ' . getconstStr('DiskName') . ':
        <input type="text" name="diskname" placeholder="' . getconstStr('EnvironmentsDescription')['diskname'] . '" style="width:100%"><br>
        <br>
        <div>
                <label><input type="checkbox" name="NT_Drive_custom" onclick="document.getElementById(\'NT_secret\').style.display=(this.checked?\'\':\'none\');">' . getconstStr('CustomIdSecret') . '</label><br>
                <div id="NT_secret" style="display:none;margin:10px 35px">
                    <a href="https://console.cloud.google.com/apis" target="_blank">' . getconstStr('GetSecretIDandKEY') . '</a><br>
                    return_uri(Reply URL):<br>https://scfonedrive.github.io<br>
                    client_id:<input type="text" name="NT_client_id" style="width:100%" placeholder="123456789-abcdefghijklmno.apps.googleusercontent.com"><br>
                    client_secret:<input type="text" name="NT_client_secret" style="width:100%"><br>
                </div>
        </div>
        <br>';
        if ($_SERVER['language']=='zh-cn') $html .= '你要理解 scfonedrive.github.io 是github上的静态网站，<br><font color="red">除非github真的挂掉</font>了，<br>不然，稍后你如果连不上，请检查你的运营商或其它“你懂的”问题！<br>';
        $html .='
        <input type="submit" value="' . getconstStr('Submit') . '">
    </form>
</div>
    <script>
        function notnull(t)
        {
            if (t.disktag_add.value==\'\') {
                alert(\'' . getconstStr('DiskTag') . '\');
                return false;
            }
            envs = [' . $envs . '];
            if (envs.indexOf(t.disktag_add.value)>-1) {
                alert("Do not input ' . $envs . '");
                return false;
            }
            var reg = /^[a-zA-Z]([_a-zA-Z0-9]{1,20})$/;
            if (!reg.test(t.disktag_add.value)) {
                alert(\'' . getconstStr('TagFormatAlert') . '\');
                return false;
            }
            
            
                if (t.NT_Drive_custom.checked==true) {
                    if (t.NT_client_secret.value==\'\'||t.NT_client_id.value==\'\') {
                        alert(\'client_id & client_secret\');
                        return false;
                    }
                }
            
            document.getElementById("form1").action="?install0&disktag=" + t.disktag_add.value + "&AddDisk=Googledrive";
            return true;
        }
    </script>';
        $title = 'Select Account Type';
        return message($html, $title, 201);
    }

    protected function get_access_token($refresh_token) {
        if (!$refresh_token) {
            $tmp['stat'] = 0;
            $tmp['body'] = 'No refresh_token';
            $this->error = $tmp;
            return false;
        }
        if (!($this->access_token = getcache('access_token', $this->disktag))) {
            $p=0;
            $data['client_id'] = $this->client_id;
            $data['client_secret'] = $this->client_secret;
            $data['grant_type'] = 'refresh_token';
            //$data['redirect_uri'] = $this->redirect_uri;
            $data['refresh_token'] = $refresh_token;
            while ($response['stat']==0&&$p<3) {
                //$response = curl('POST', 'https://www.googleapis.com/oauth2/v4/token', json_encode($data));
                $response = curl('POST', $this->oauth_url . 'token', json_encode($data));
                $p++;
            }
            if ($response['stat']==200) $ret = json_decode($response['body'], true);
            if (!isset($ret['access_token'])) {
                error_log1($this->oauth_url . 'token' . '?client_id=' . $this->client_id . '&client_secret=' . $this->client_secret . '&grant_type=refresh_token&refresh_token=' . substr($refresh_token, 0, 20) . '******' . substr($refresh_token, -20));
                error_log1('failed to get [' . $this->disktag . '] access_token. response: ' . $response['body']);
                $response['body'] = json_encode(json_decode($response['body']), JSON_PRETTY_PRINT);
                $response['body'] .= '\nfailed to get [' . $this->disktag . '] access_token.';
                $this->error = $response;
                return false;
                //throw new Exception($response['stat'].', failed to get ['.$this->disktag.'] access_token.'.$response['body']);
            }
            $tmp = $ret;
            $tmp['access_token'] = substr($tmp['access_token'], 0, 10) . '******';
            //$tmp['refresh_token'] = substr($tmp['refresh_token'], 0, 10) . '******';
            error_log1('[' . $this->disktag . '] Get access token:' . json_encode($tmp, JSON_PRETTY_PRINT));
            $this->access_token = $ret['access_token'];
            savecache('access_token', $this->access_token, $this->disktag, $ret['expires_in'] - 300);
            //if (time()>getConfig('token_expires', $this->disktag)) setConfig([ 'refresh_token' => $ret['refresh_token'], 'token_expires' => time()+7*24*60*60 ], $this->disktag);
            return true;
        }
        return true;
    }
    public function getDiskSpace() {
        if ($this->driveId!='root') return '0 / 0';
        if (!($diskSpace = getcache('diskSpace', $this->disktag))) {
            $url = $this->api_url . '/about?fields=storageQuota';
            $response = $this->GDAPI('GET', $url);
            if ($response['stat']==200) {
                $res = json_decode($response['body'], true)['storageQuota'];
                $used = size_format($res['usage']);
                $total = size_format($res['limit']);
                $diskSpace = $used . ' / ' . $total;
                savecache('diskSpace', $diskSpace, $this->disktag);
            } else return json_encode($response);
        }
        return $diskSpace;
    }

    protected function GDAPI($method, $url, $data = '')
    {
        /*if (substr($path,0,7) == 'http://' or substr($path,0,8) == 'https://') {
            $url = $path;
        } else {
            $url = $this->api_url;
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
        }*/
        $headers['Authorization'] = 'Bearer ' . $this->access_token;
        if (!isset($headers['Accept'])) $headers['Accept'] = '*/*';
        $headers['Content-Type'] = 'application/json';
        //if (!isset($headers['Referer'])) $headers['Referer'] = $url;*
        $sendHeaders = array();
        foreach ($headers as $headerName => $headerVal) {
            $sendHeaders[] = $headerName . ': ' . $headerVal;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $sendHeaders);
        $response['body'] = curl_exec($ch);
        $response['stat'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //$response['Location'] = curl_getinfo($ch);
        curl_close($ch);
        /*error_log1($response['stat'].'
    ' . $response['body'].'
    ' . $url.'
    ' . $data);*/
        return $response;
    }

}
