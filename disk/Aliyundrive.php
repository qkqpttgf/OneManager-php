<?php

class Aliyundrive {
    protected $access_token;
    protected $disktag;

    function __construct($tag) {
        $this->disktag = $tag;
        //$this->auth_url = 'https://websv.aliyundrive.com/token/refresh';
        $this->auth_url = 'https://auth.aliyundrive.com/v2/account/token';
        $this->api_url = 'https://api.aliyundrive.com/v2';
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
        //return $files;
        if ($files['type']=='file') {
            $tmp['type'] = 'file';
            $tmp['id'] = $files['file_id'];
            if (isset($files['name'])) $tmp['name'] = $files['name'];
            elseif (isset($files['file_name'])) $tmp['name'] = $files['file_name'];
            $tmp['time'] = $files['updated_at'];
            $tmp['size'] = $files['size'];
            $tmp['mime'] = $files['file']['mimeType'];
            $tmp['url'] = $files['download_url'];
            $tmp['content'] = $files['content'];
            if (isset($files['exist'])) $tmp['exist'] = $files['exist'];
            if (isset($files['rapid_upload'])) $tmp['rapid_upload'] = $files['rapid_upload'];
        } elseif ($files['type']=='folder'||isset($files['items'])) {
            $tmp['type'] = 'folder';
            $tmp['id'] = $files['file_id'];
            if (isset($files['name'])) $tmp['name'] = $files['name'];
            elseif (isset($files['file_name'])) $tmp['name'] = $files['file_name'];
            $tmp['time'] = $files['updated_at'];
            $tmp['size'] = $files['size'];
            //$tmp['page'] = $files['folder']['page'];
            foreach ($files['items'] as $file) {
                $filename = strtolower($file['name']);
                if ($file['type']=='file') {
                    $tmp['list'][$filename]['type'] = 'file';
                    $tmp['list'][$filename]['url'] = $file['download_url'];
                    $tmp['list'][$filename]['mime'] = $file['file']['content_type'];
                } elseif ($file['type']=='folder') {
                    $tmp['list'][$filename]['type'] = 'folder';
                }
                //$tmp['id'] = $file['parent_file_id'];
                $tmp['list'][$filename]['id'] = $file['file_id'];
                $tmp['list'][$filename]['name'] = $file['name'];
                $tmp['list'][$filename]['time'] = $file['updated_at'];
                $tmp['list'][$filename]['size'] = $file['size'];
                //$tmp['childcount']++;
            }
        } elseif (isset($files['code'])||isset($files['error'])) {
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
            if ($path == '/' || $path == '') {
                $files = $this->fileList('root');
                //error_log1('root_id' . $files['id']);
                $files['file_id'] = 'root';
                $files['type'] = 'folder';
            } else {
                $tmp = splitlast($path, '/');
                $parent_path = $tmp[0];
                $filename = urldecode($tmp[1]);
                $parent_folder = $this->list_path($parent_path);
                foreach ($parent_folder['items'] as $item) {
                    if ($item['name']==$filename) {
                        if ($item['type']=='folder') {
                            $files = $this->fileList($item['file_id']);
                            $files['type'] = 'folder';
                            $files['file_id'] = $item['file_id'];
                            $files['name'] = $item['name'];
                            $files['time'] = $item['updated_at'];
                            $files['size'] = $item['size'];
                        } else $files = $item;
                    }
                }
                //echo $files['name'];
            }
            if ($files['type']=='file') {
                if (in_array(strtolower(splitlast($files['name'],'.')[1]), $exts['txt'])) {
                    if ($files['size']<1024*1024) {
                        if (!(isset($files['content'])&&$files['content']['stat']==200)) {
                            $header['Referer'] = 'https://www.aliyundrive.com/';
                            $header['User-Agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36';
                            $content1 = curl('GET', $files['download_url'], '', $header);
                            $tmp = null;
                            $tmp = json_decode(json_encode($content1), true);
                            if ($tmp['body']===null) {
                                $tmp['body'] = iconv("GBK", 'UTF-8//TRANSLIT', $content1['body']);
                                $tmp = json_decode(json_encode($tmp), true);
                                if ($tmp['body']!==null) $content1['body'] = $tmp['body'];
                            }
                            //error_log1('body : ' . $content1['body'] . PHP_EOL);
                            $files['content'] = $content1;
                            savecache('path_' . $path, $files, $this->disktag);
                        }
                    } else {
                        $files['content']['stat'] = 202;
                        $files['content']['body'] = 'File too large.';
                    }
                    //error_log1($files['name'] . ' : ' . json_encode($files['content']) . PHP_EOL);
                }
            }
            if (!$files) {
                $files['error']['code'] = 'Not Found';
                $files['error']['message'] = $path . ' Not Found';
                $files['error']['stat'] = 404;
            } elseif (isset($files['stat'])) {
                $files['error']['stat'] = $files['stat'];
                $files['error']['code'] = 'Error';
                $files['error']['message'] = $files['body'];
                unset($files['file_id']);
                unset($files['type']);
            } elseif (isset($files['code'])) {
                $files['error']['stat'] = 500;
                $files['error']['code'] = $files['code'];
                $files['error']['message'] = $files['message'];
                unset($files['file_id']);
                unset($files['type']);
            } else {
                savecache('path_' . $path, $files, $this->disktag, 600);
            }
        }
        //error_log1('path:' . $path . ', files:' . substr(json_encode($files), 0, 150));
        //error_log1('path:' . $path . ', files:' . json_encode($files));
        return $files;
    }

    protected function fileGet($file_id)
    {
        $url = $this->api_url . '/file/get';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['drive_id'] = $this->driveId;
        $data['file_id'] = $file_id;

        $res = curl('POST', $url, json_encode($data), $header);
        if ($res['stat']==200) return json_decode($res['body'], true);
        else return $res;
    }
    protected function fileList($parent_file_id)
    {
        $url = $this->api_url . '/file/list';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['limit'] = 200;
        $data['marker'] = null;
        $data['drive_id'] = $this->driveId;
        $data['parent_file_id'] = $parent_file_id;
        $data['image_thumbnail_process'] = 'image/resize,w_160/format,jpeg';
        $data['image_url_process'] = 'image/resize,w_1920/format,jpeg';
        $data['video_thumbnail_process'] = 'video/snapshot,t_0,f_jpg,w_300';
        $data['fields'] = '*';
        $data['order_by'] = 'name'; //updated_at
        $data['order_direction'] = 'ASC'; //DESC

        $res = curl('POST', $url, json_encode($data), $header);
        //error_log1($res['stat'] . $res['body']);
        if ($res['stat']==200) {
            $body = json_decode($res['body'], true);
            $body1 = $body;
            while ($body1['next_marker']!='') {
                $data['marker'] = $body1['next_marker'];
                $res1 = null;
                $res1 = curl('POST', $url, json_encode($data), $header);
                $body1 = json_decode($res1['body'], true);
                $body['items'] = array_merge($body['items'], $body1['items']);
            }
            return $body;
            //return json_decode($res['body'], true);
        }
        else return $res;
    }

    public function Rename($file, $newname) {
        $url = $this->api_url . '/file/update';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['check_name_mode'] = 'refuse';
        $data['drive_id'] = $this->driveId;
        $data['file_id'] = $file['id'];
        $data['name'] = $newname;
        //$data['parent_file_id'] = 'root';

        $result = curl('POST', $url, json_encode($data), $header);
        //savecache('path_' . $file['path'], json_decode('{}',true), $this->disktag, 1);
        //error_log1('decode:' . json_encode($result));
        return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
        //return output($result['body'], $result['stat']);
    }
    public function Delete($file) {
        $url = $this->api_url . '/batch';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['resource'] = 'file';
        $data['requests'][0]['url'] = '/file/delete';
        $data['requests'][0]['method'] = 'DELETE';
        $data['requests'][0]['id'] = $file['id'];
        $data['requests'][0]['headers']['Content-Type'] = 'application/json';
        $data['requests'][0]['body']['drive_id'] = $this->driveId;
        $data['requests'][0]['body']['file_id'] = $file['id'];

        $result = curl('POST', $url, json_encode($data), $header);
        //savecache('path_' . $file['path'], json_decode('{}',true), $this->disktag, 1);
        //error_log1('result:' . json_encode($result));
        //return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
        $res = json_decode($result['body'], true)['responses'][0];
        if (isset($res['status'])) return output($res['id'], $res['status']);
        else return output($result['body'], $result['stat']);
    }
    public function Encrypt($folder, $passfilename, $pass) {
        $existfile = $this->list_path($folder['path'] . '/' . $passfilename);
        if (isset($existfile['type'])) { // 删掉原文件
            $this->Delete(['id'=>$existfile['file_id']]);
        }
        if ($pass==='') {
            // 如果为空，上面已经删除了
            return output('Success', 200);
        }
        if (!$folder['id']) {
            $res = $this->list_path($folder['path']);
            //error_log1('res:' . json_encode($res));
            $folder['id'] = $res['file_id'];
        }
        $tmp = '/tmp/' . $passfilename;
        file_put_contents($tmp, $pass);

        $result = $this->tmpfileCreate($folder['id'], $tmp, $passfilename);

        if ($result['stat']==201) {
            //error_log1('1,url:' . $url .' res:' . json_encode($result));
            $res = json_decode($result['body'], true);
            $url = $res['part_info_list'][0]['upload_url'];
            if (!$url) { // 无url，应该算秒传
                return output('no up url', 200);
            }
            $file_id = $res['file_id'];
            $upload_id = $res['upload_id'];
            $result = curl('PUT', $url, $pass, [], 1);
            if ($result['stat']==200) { // 块1传好
                $result = $this->fileComplete($file_id, $upload_id, [ $result['returnhead']['ETag'] ]);
                return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
            }
        }
        //error_log1('2,url:' . $url .' res:' . json_encode($result));
        return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
    }
    public function Move($file, $folder) {
        if (!$folder['id']) {
            $res = $this->list_path($folder['path']);
            //error_log1('res:' . json_encode($res));
            $folder['id'] = $res['file_id'];
        }

        $url = $this->api_url . '/batch';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['resource'] = 'file';
        $data['requests'][0]['url'] = '/file/move';
        $data['requests'][0]['method'] = 'POST';
        $data['requests'][0]['id'] = $file['id'];
        $data['requests'][0]['headers']['Content-Type'] = 'application/json';
        $data['requests'][0]['body']['drive_id'] = $this->driveId;
        $data['requests'][0]['body']['file_id'] = $file['id'];
        $data['requests'][0]['body']['auto_rename'] = true;
        $data['requests'][0]['body']['to_parent_file_id'] = $folder['id'];

        $result = curl('POST', $url, json_encode($data), $header);
        //savecache('path_' . $file['path'], json_decode('{}',true), $this->disktag, 1);
        //error_log1('result:' . json_encode($result));
        return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
    }
    public function Copy($file) {
        return output('NO copy', 415);
        if (!$file['id']) {
            $oldfile = $this->list_path($file['path'] . '/' . $file['name']);
            //error_log1('res:' . json_encode($res));
            //$file['id'] = $res['file_id'];
        } else {
            $oldfile = $this->fileGet($file['id']);
        }

        $url = $this->api_url . '/file/create';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['check_name_mode'] = 'auto_rename'; // ignore, auto_rename, refuse.
        $data['content_hash'] = $oldfile['content_hash'];
        $data['content_hash_name'] = 'sha1';
        $data['content_type'] = $oldfile['content_type'];
        $data['drive_id'] = $this->driveId;
        $data['ignoreError'] = false;
        $data['name'] = $oldfile['name'];
        $data['parent_file_id'] = $oldfile['parent_file_id'];
        $data['part_info_list'][0]['part_number'] = 1;
        $data['size'] = $oldfile['size'];
        $data['type'] = 'file';

        $result = curl('POST', $url, json_encode($data), $header);

        if ($result['stat']==201) {
            //error_log1('1,url:' . $url .' res:' . json_encode($result));
            $res = json_decode($result['body'], true);
            $url = $res['part_info_list'][0]['upload_url'];
            if (!$url) { // 无url，应该算秒传
                return output('no up url', 200);
            } else {
                return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
            }
            /*$file_id = $res['file_id'];
            $upload_id = $res['upload_id'];
            $result = curl('PUT', $url, $content, [], 1);
            if ($result['stat']==200) { // 块1传好
                $etag = $result['returnhead']['ETag'];
                $result = $this->fileComplete($file_id, $upload_id, [ $etag ]);
                if ($result['stat']!=200) return output($result['body'], $result['stat']);
                else return output('success', 0);
            }*/
        }
        //error_log1('2,url:' . $url .' res:' . json_encode($result));
        return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
    }
    public function Edit($file, $content) {
        $tmp = splitlast($file['path'], '/');
        $folderpath = $tmp[0];
        $filename = $tmp[1];
        $existfile = $this->list_path($file['path']);
        if (isset($existfile['type'])) { // 删掉原文件
            $this->Delete(['id'=>$existfile['file_id']]);
        }
        $tmp1 = '/tmp/' . $filename;
        file_put_contents($tmp1, $content);

        $result = $this->tmpfileCreate($this->list_path($folderpath)['file_id'], $tmp1, $filename);

        if ($result['stat']==201) {
            //error_log1('1,url:' . $url .' res:' . json_encode($result));
            $res = json_decode($result['body'], true);
            $url = $res['part_info_list'][0]['upload_url'];
            if (!$url) { // 无url，应该算秒传
                return output('no up url', 0);
            }
            $file_id = $res['file_id'];
            $upload_id = $res['upload_id'];
            $result = curl('PUT', $url, $content, [], 1);
            if ($result['stat']==200) { // 块1传好
                $result = $this->fileComplete($file_id, $upload_id, [ $result['returnhead']['ETag'] ]);
                if ($result['stat']!=200) return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
                else return output('success', 0);
            }
        }
        //error_log1('2,url:' . $url .' res:' . json_encode($result));
        return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
    }
    public function Create($folder, $type, $name, $content = '') {
        if (!$folder['id']) {
            $res = $this->list_path($folder['path']);
            //error_log1('res:' . json_encode($res));
            $folder['id'] = $res['file_id'];
        }
        if ($type=='folder') {
            $result = $this->folderCreate($folder['id'], $name);
            //error_log1('res:' . json_encode($result));
            return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
        }
        if ($type=='file') {
            $tmp = '/tmp/' . $name;
            file_put_contents($tmp, $content);

            $result = $this->tmpfileCreate($folder['id'], $tmp, $name);

            if ($result['stat']==201) {
                //error_log1('1,url:' . $url .' res:' . json_encode($result));
                $res = json_decode($result['body'], true);
                if (isset($res['exist'])&&$res['exist']!=false) {
                    // 已经有
                    //error_log1('exist:' . json_encode($res));
                    return output('{"type":"file","name":"' . $name . '", "exist":true}', 200);
                }
                if (isset($res['rapid_upload'])&&$res['rapid_upload']!=false) {
                    // 秒传
                    //error_log1('rapid up:' . json_encode($res));
                    return output('{"type":"file","name":"' . $name . '", "rapid_upload":true}', 200);
                }
                $url = $res['part_info_list'][0]['upload_url'];
                $file_id = $res['file_id'];
                $upload_id = $res['upload_id'];
                $result = curl('PUT', $url, $content, [], 1);
                //error_log1('2,url:' . $url .' res:' . json_encode($result));
                if ($result['stat']==200) { // 块1传好
                    $result = $this->fileComplete($file_id, $upload_id, [ $result['returnhead']['ETag'] ]);
                    //error_log1('3,url:' . $url .' res:' . json_encode($result));
                    return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
                }
            }
            //error_log1('4,url:' . $url .' res:' . json_encode($result));
            return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
        }
        return output('Type not folder or file.', 500);
    }

    protected function folderCreate($parentId, $folderName) {
        if (strrpos($folderName, '/')) {
            $tmp = splitlast($folderName, '/');
            $parentId = json_decode($this->folderCreate($parentId, $tmp[0])['body'], true)['file_id'];
            $folderName = $tmp[1];
        }
        $url = $this->api_url . '/file/create';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['check_name_mode'] = 'refuse'; // ignore, auto_rename, refuse.
        $data['drive_id'] = $this->driveId;
        $data['name'] = $folderName;
        $data['parent_file_id'] = $parentId;
        $data['type'] = 'folder';

        return curl('POST', $url, json_encode($data), $header);
    }
    protected function fileCreate($parentId, $fileName, $sha1, $size, $part_number) {
        $url = $this->api_url . '/file/create';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;
    
        $data['check_name_mode'] = 'refuse'; // ignore, auto_rename, refuse.
        $data['content_hash'] = $sha1;
        $data['content_hash_name'] = 'sha1';
        $data['content_type'] = '';
        $data['drive_id'] = $this->driveId;
        $data['ignoreError'] = false;
        $data['name'] = $fileName;
        $data['parent_file_id'] = $parentId;
        for ($i=0;$i<$part_number;$i++) {
            $data['part_info_list'][$i]['part_number'] = $i+1;
        }
        $data['size'] = (int)$size;
        $data['type'] = 'file';

        return curl('POST', $url, json_encode($data), $header);
    }
    protected function tmpfileCreate($parentId, $tmpFilePath, $tofileName = '') {
        $sha1 = sha1_file($tmpFilePath);
        if ($tofileName == '') $tofileName = splitlast($tmpFilePath, '/')[1];
        $url = $this->api_url . '/file/create';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['check_name_mode'] = 'refuse'; // ignore, auto_rename, refuse.
        $data['content_hash'] = $sha1;
        $data['content_hash_name'] = 'sha1';
        $data['content_type'] = 'text/plain'; // now only txt
        $data['drive_id'] = $this->driveId;
        $data['ignoreError'] = false;
        $data['name'] = $tofileName;
        $data['parent_file_id'] = $parentId;
        $data['part_info_list'][0]['part_number'] = 1; // now only txt
        $data['size'] = filesize($tmpFilePath);
        $data['type'] = 'file';

        return curl('POST', $url, json_encode($data), $header);
    }
    protected function fileComplete($file_id, $upload_id, $etags) {
        $url = $this->api_url . '/file/complete';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['drive_id'] = $this->driveId;
        $data['file_id'] = $file_id;
        $data['ignoreError'] = false;
        $i = 0;
        foreach ($etags as $etag) {
            $data['part_info_list'][$i]['part_number'] = $i + 1;
            $data['part_info_list'][$i]['etag'] = $etag;
            $i++;
        }
        $data['upload_id'] = $upload_id;

        return curl('POST', $url, json_encode($data), $header);
    }

    public function get_thumbnails_url($path = '/')
    {
        $res = $this->list_path($path);
        $thumb_url = $res['thumbnail'];
        return $thumb_url;
    }
    public function bigfileupload($path)
    {
        if (isset($_POST['uploadid'])) {
            // Complete
            $result = $this->fileComplete($_POST['fileid'], $_POST['uploadid'], json_decode($_POST['etag'], true));
            return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
        } else {
            if ($_POST['upbigfilename']=='') return output('error: no file name', 400);
            if (!is_numeric($_POST['filesize'])) return output('error: no file size', 400);
            if (!isset($_POST['filesha1'])) return output('error: no file sha1', 400);

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
                if ($tmp1[0]==''||$tmp1[1]=='') $filename = $_POST['filesha1'];
                else $filename = $_POST['filesha1'] . '.' . $tmp1[1];
            }

            $parent = $this->list_path($path . '/' . $fileinfo['path']);
            if (isset($parent['file_id'])) {
                $parent_file_id = $parent['file_id'];
            } else {
                $res = $this->folderCreate($this->list_path($path)['file_id'], $fileinfo['path']);
                //error_log1($res['body']);
                $parent_file_id = json_decode($res['body'], true)['file_id'];
            }
            $response = $this->fileCreate($parent_file_id, $filename, $_POST['filesha1'], $fileinfo['size'], ceil($fileinfo['size']/$_POST['chunksize']));
            $res = json_decode($response['body'], true);
            if (isset($res['exist'])) {
                // 已经有
                //error_log1('exist:' . json_encode($res));
                return output(json_encode($this->files_format(json_decode($response['body'], true))), $response['stat']);
                //return output('{"type":"file","name":"' . $_POST['upbigfilename'] . '", "exist":true}', 200);
            }
            if (isset($res['rapid_upload'])&&$res['rapid_upload']!=false) {
                // 秒传
                //error_log1('rapid up:' . json_encode($res));
                return output(json_encode($this->files_format(json_decode($response['body'], true))), $response['stat']);
                //return output('{"type":"file","name":"' . $_POST['upbigfilename'] . '", "rapid upload":true}', 200);
            }
            //if ($response['stat']<500) {
            //    $responsearry = json_decode($response['body'], true);
            //    if (isset($responsearry['error'])) return output($response['body'], $response['stat']);
            //    $fileinfo['uploadUrl'] = $responsearry['uploadUrl'];
            //    if ($fileinfo['size']>10*1024*1024) $this->MSAPI('PUT', path_format($path . '/' . $cachefilename), json_encode($fileinfo, JSON_PRETTY_PRINT), $this->access_token);
            //}
            return output($response['body'], $response['stat']);
        }
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
            if ($_POST['driveId']!='') {
                $tmp['driveId'] = $_POST['driveId'];
            } else {
                return message('no driveId', 'Error', 201);
            }

            $response = setConfigResponse( setConfig($tmp, $this->disktag) );
            if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
                return message($html, $title, 201);
            } else {
                $str .= '
<script>
    var status = "' . $response['DplStatus'] . '";
    var uploadList = setInterval(function(){
        if (document.getElementById("dis").style.display=="none") {
            console.log(min++);
        } else {
            clearInterval(uploadList);
            location.href = "' . $url . '";
        }
    }, 1000);
</script>';
                return message($str, getconstStr('WaitJumpIndex'), 201, 1);
            }
        }
        if (isset($_GET['SelectDrive'])) {
            if ($this->access_token == '') {
                if (isset($_POST['refresh_token'])) {
                    $res = curl('POST', $this->auth_url, json_encode([ 'refresh_token' => $_POST['refresh_token'], 'grant_type' => 'refresh_token' ]), ["content-type"=>"application/json; charset=utf-8"]);
                    //return output($res['body']);
                    if ($res['stat']!=200) {
                        return message($res['body'], $res['stat'], $res['stat']);
                    }
                    //var_dump($res['body']);
                    $result = json_decode($res['body'], true);

                    $tmp = null;
                    $tmp['refresh_token'] = $result['refresh_token'];
                    $tmp['token_expires'] = time()+3*24*60*60;
                    $tmp['Driver'] = 'Aliyundrive';
                    //error_log(json_encode($tmp));

                    $response = setConfigResponse( setConfig($tmp, $this->disktag) );
                    if (api_error($response)) {
                        $html = api_error_msg($response);
                        $title = 'Error';
                        return message($html, $title, 201);
                    }
                    savecache('access_token', $result['access_token'], $this->disktag, $result['expires_in'] - 60);
                } else {
                    $refresh_token = getConfig('refresh_token', $this->disktag);
                    if (!$refresh_token) {
                        $html = 'No refresh_token config, please AddDisk again or wait minutes.<br>' . $this->disktag;
                        $title = 'Error';
                        return message($html, $title, 201);
                    }
                    $response = $this->get_access_token($refresh_token);
                    if (!$response) return message($this->error['body'], 'Error', $this->error['stat']);
                }
            }
            if (!isset($result['default_drive_id'])) {
                $res = curl('POST', $this->auth_url, json_encode([ 'refresh_token' => getConfig('refresh_token', $this->disktag), 'grant_type' => 'refresh_token' ]), ["content-type"=>"application/json; charset=utf-8"]);
                    //return output($res['body']);
                if ($res['stat']!=200) {
                    return message($res['body'], $res['stat'], $res['stat']);
                }
                    //var_dump($res['body']);
                $result = json_decode($res['body'], true);
            }

            //$tmp = null;
            //$tmp['driveId'] = $result['default_drive_id'];
                //$tmp['default_sbox_drive_id'] = $result['default_sbox_drive_id'];
            $title = 'Select Driver';
            $html = '
<div>
    <form action="?Finish&disktag=' . $_GET['disktag'] . '&AddDisk=' . get_class($this) . '" method="post" onsubmit="return notnull(this);">
        <label><input type="radio" name="driveId" value="' . $result['default_drive_id'] . '"' . ($result['default_drive_id']==$this->driveId?' checked':'') . '>' . '用普通空间 ' . getconstStr(' ') . '</label><br>
        <label><input type="radio" name="driveId" value="' . $result['default_sbox_drive_id'] . '"' . ($result['default_sbox_drive_id']==$this->driveId?' checked':'') . '>' . '用虎符文件保险箱 </label><br>
        <input type="submit" value="' . getconstStr('Submit') . '">
    </form>
</div>
<script>
    var status = "' . $response['DplStatus'] . '";
    function notnull(t)
    {
        if (t.driveId.value==\'\') {
                alert(\'Select a Disk\');
                return false;
        }
        return true;
    }
</script>
    ';
            return message($html, $title, 201, 1);
        }
        if (isset($_GET['install0']) && $_POST['disktag_add']!='') {
            $_POST['disktag_add'] = preg_replace('/[^0-9a-zA-Z|_]/i', '', $_POST['disktag_add']);
            $f = substr($_POST['disktag_add'], 0, 1);
            if (strlen($_POST['disktag_add'])==1) $_POST['disktag_add'] .= '_';
            if (isCommonEnv($_POST['disktag_add'])) {
                return message('Do not input ' . $envs . '<br><button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>
                <script>
                var expd = new Date();
                expd.setTime(expd.getTime()+1);
                var expires = "expires="+expd.toGMTString();
                document.cookie=\'disktag=; path=/; \'+expires;
                </script>', 'Error', 201);
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
            foreach ($EnvConfigs as $env => $v) if (isInnerEnv($env)) $tmp[$env] = '';

            $tmp['Driver'] = 'Aliyundrive';
            $tmp['disktag_add'] = $_POST['disktag_add'];
            $tmp['diskname'] = $_POST['diskname'];
            //error_log(json_encode($tmp));

            $response = setConfigResponse( setConfig($tmp, $this->disktag) );
            if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
                return message($html, $title, 400);
            } else {
                $title = 'Refresh token';
                $html = '
<form action="?SelectDrive&disktag=' . $_GET['disktag'] . '&AddDisk=' . get_class($this) . '" method="post" onsubmit="return notnull(this);">
    <div>填入refresh_token:
        <input type="text" name="refresh_token" placeholder="自行百度如何获取' . getconstStr(' ') . '" style="width:100%"><br>
    </div><br>
    <input type="submit" value="' . getconstStr('Submit') . '">
<form>
    <script>
        function notnull(t)
        {
            if (t.refresh_token.value==\'\') {
                alert(\'Input refresh_token\');
                return false;
            }
            return true;
        }
        var status = "' . $response['DplStatus'] . '";
    </script>
    ';
                return message($html, $title, 201, 1);
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
            
            document.getElementById("form1").action="?install0&disktag=" + t.disktag_add.value + "&AddDisk=Aliyundrive";
            //var expd = new Date();
            //expd.setTime(expd.getTime()+(2*60*60*1000));
            //var expires = "expires="+expd.toGMTString();
            //document.cookie=\'disktag=\'+t.disktag_add.value+\'; path=/; \'+expires;
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
            $tmp1['refresh_token'] = $refresh_token;
            $tmp1['grant_type'] = 'refresh_token';
            while ($response['stat']==0&&$p<3) {
                $response = curl('POST', $this->auth_url, json_encode($tmp1), ["content-type"=>"application/json; charset=utf-8"]);
                $p++;
            }
            //error_log1(json_encode($response));
            if ($response['stat']==200) $ret = json_decode($response['body'], true);
            if (!isset($ret['access_token'])) {
                error_log1('failed to get [' . $this->disktag . '] access_token. response: ' . $response['stat'] . $response['body']);
                //$response['body'] = json_encode(json_decode($response['body']), JSON_PRETTY_PRINT);
                $response['body'] .= 'failed to get [' . $this->disktag . '] access_token.';
                $this->error = $response;
                return false;
            }
            $tmp = $ret;
            $tmp['access_token'] = substr($tmp['access_token'], 0, 10) . '******';
            $tmp['refresh_token'] = substr($tmp['refresh_token'], 0, 10) . '******';
            error_log1('[' . $this->disktag . '] Get access token:' . json_encode($tmp, JSON_PRETTY_PRINT));
            $this->access_token = $ret['access_token'];
            savecache('access_token', $this->access_token, $this->disktag, $ret['expires_in'] - 300);
            if (time()>getConfig('token_expires', $this->disktag)) setConfig([ 'refresh_token' => $ret['refresh_token'], 'token_expires' => time()+3*24*60*60 ], $this->disktag);
            return true;
        }
        return true;
    }
    public function getDiskSpace() {
        if (!($diskSpace = getcache('diskSpace', $this->disktag))) {
            $url = $this->api_url . '/databox/get_personal_info';
            $header["content-type"] = "application/json; charset=utf-8";
            $header['authorization'] = 'Bearer ' . $this->access_token;
            $response = curl('POST', $url, '', $header);
            //error_log1(json_encode($response));
            $res = json_decode($response['body'], true)['personal_space_info'];
            $used = size_format($res['used_size']);
            $total = size_format($res['total_size']);
            $diskSpace = $used . ' / ' . $total;
            savecache('diskSpace', $diskSpace, $this->disktag);
        }
        return $diskSpace;
    }
}
