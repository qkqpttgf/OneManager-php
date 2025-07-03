<?php
// https://www.yuque.com/aliyundrive/zpfszx

if (!class_exists('Aliyundrive')) require 'Aliyundrive.php';
class AliyundriveOpen extends Aliyundrive {
    protected $client_id;
    protected $client_secret;
    protected $my_oauth_url;
    protected $scope;
    protected $redirect_uri;

    function __construct($tag) {
        $this->disktag = $tag;
        $this->client_id = getConfig('client_id', $tag);
        if ($this->client_id == "") $this->client_id = "4685bbf979d44b138fbba410de99dfe4";
        $this->client_secret = getConfig('client_secret', $tag);
        $this->oauth_url = 'https://openapi.alipan.com/oauth/';
        $this->my_oauth_url = 'https://aliyunopenaccesstoken.onemanager.eu.org/';
        $this->scope = 'user:base,file:all:read,file:all:write';
        $this->redirect_uri = 'https://scfonedrive.github.io';
        $this->api_url = 'https://openapi.alipan.com/adrive/v1.0/';
        $this->driveId = getConfig('driveId', $tag);
        $this->DownurlStrName = 'url';
        $res = $this->get_access_token(getConfig('refresh_token', $tag));
    }

    protected function list_path($path = '/') {
        global $exts;
        while (substr($path, -1) == '/') $path = substr($path, 0, -1);
        if ($path == '') $path = '/';
        if (!($files = getcache('path_' . $path, $this->disktag))) {
            if ($path == '/' || $path == '') {
                $files = $this->fileList('root');
                //error_log1('root_id' . $files['file_id']);
                $files['file_id'] = 'root';
                $files['type'] = 'folder';
                //error_log1(json_encode($files, JSON_PRETTY_PRINT));
            } else {
                $file = $this->fileGetByPath($path);
                //echo json_encode($file);
                if ($file['type'] == 'folder') {
                    $files = $this->fileList($file['file_id']);
                    $files['file_id'] = $file['file_id'];
                    $files['type'] = 'folder';
                } else {
                    $files = $file;
                    if ($file['type'] == 'file') {
                        $tmp = $this->getDownloadUrl($file['file_id']);
                        if (isset($tmp['stat'])) $files = $tmp;
                        else $files[$this->DownurlStrName] = $tmp;
                    }
                }
            }
            if ($files['type'] == 'file') {
                if (in_array(strtolower(splitlast($files['name'], '.')[1]), $exts['txt'])) {
                    if ($files['size'] < 1024 * 1024) {
                        if (!(isset($files['content']) && $files['content']['stat'] == 200)) {
                            $header['Referer'] = 'https://www.aliyundrive.com/';
                            $header['User-Agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36';
                            $content1 = curl('GET', $files[$this->DownurlStrName], '', $header);
                            $tmp = null;
                            $tmp = json_decode(json_encode($content1), true);
                            if ($tmp['body'] === null) {
                                $tmp['body'] = iconv("GBK", 'UTF-8//TRANSLIT', $content1['body']);
                                $tmp = json_decode(json_encode($tmp), true);
                                if ($tmp['body'] !== null) $content1['body'] = $tmp['body'];
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
            } else {
                // clear txt cache in this folder
                foreach ($files['items'] as $item) {
                    $filename = path_format($path . "/" . $item['name']);
                    //error_log1($filename);
                    if ($tmpcache = getcache('path_' . $filename, $this->disktag)) {
                        //error_log1("Clear content.");
                        savecache('path_' . $filename, "", $this->disktag);
                    }
                }
            }
            if (!$files) {
                $files['error']['stat'] = 404;
                $files['error']['code'] = 'Not Found';
                $files['error']['message'] = $path . ' Not Found';
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
    protected function fileGet($file_id) {
        $url = $this->api_url . 'openFile/get';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['drive_id'] = $this->driveId;
        $data['file_id'] = $file_id;

        $res = curl('POST', $url, json_encode($data), $header);
        if ($res['stat'] == 200) return json_decode($res['body'], true);
        else return $res;
    }
    protected function fileGetByPath($file_path) {
        $file_path = urldecode($file_path);
        //echo $file_path . "<br>";
        $url = $this->api_url . 'openFile/get_by_path';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['drive_id'] = $this->driveId;
        $data['file_path'] = $file_path;

        $res = curl('POST', $url, json_encode($data), $header);
        if ($res['stat'] == 200) return json_decode($res['body'], true);
        else return $res;
    }
    protected function fileList($parent_file_id) {
        $url = $this->api_url . 'openFile/list';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        //$data['limit'] = 200;
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
        if ($res['stat'] == 200) {
            $body = json_decode($res['body'], true);
            $body1 = $body;
            while ($body1['next_marker'] != '') {
                $data['marker'] = $body1['next_marker'];
                $res1 = null;
                $res1 = curl('POST', $url, json_encode($data), $header);
                $body1 = json_decode($res1['body'], true);
                $body['items'] = array_merge($body['items'], $body1['items']);
            }
            return $body;
            //return json_decode($res['body'], true);
        } else return $res;
    }
    // 2024/01/20号起，该字段不再返回超过5MB的文件url
    protected function getDownloadUrl($file_id) {
        $url = $this->api_url . 'openFile/getDownloadUrl';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['drive_id'] = $this->driveId;
        $data['file_id'] = $file_id;

        $res = curl('POST', $url, json_encode($data), $header);
        //error_log1($res['stat'] . $res['body']);
        if ($res['stat'] == 200) {
            error_log1($res['body']);
            $body = json_decode($res['body'], true);
            return $body['url'];
        } else return $res;
    }

    public function Rename($file, $newname) {
        $url = $this->api_url . 'openFile/update';

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
        $url = $this->api_url . 'openFile/delete';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['drive_id'] = $this->driveId;
        $data['file_id'] = $file['id'];

        $result = curl('POST', $url, json_encode($data), $header);
        //savecache('path_' . $file['path'], json_decode('{}',true), $this->disktag, 1);
        //error_log1('result:' . json_encode($result));
        //return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
        return output($result['body'], $result['stat']);
    }
    public function Encrypt($folder, $passfilename, $pass) {
        $existfile = $this->list_path($folder['path'] . '/' . $passfilename);
        if (isset($existfile['type'])) { // 删掉原文件
            $this->Delete(['id' => $existfile['file_id']]);
        }
        if ($pass === '') {
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

        if ($result['stat'] == 200) {
            //error_log1('1,url:' . $url .' res:' . json_encode($result));
            $res = json_decode($result['body'], true);
            $url = $res['part_info_list'][0]['upload_url'];
            if (!$url) { // 无url，应该算秒传
                return output('no up url', 200);
            }
            $file_id = $res['file_id'];
            $upload_id = $res['upload_id'];
            $result = curl('PUT', $url, $pass, [], 1);
            if ($result['stat'] == 200) { // 块1传好
                $result = $this->fileComplete($file_id, $upload_id, [$result['returnhead']['ETag']]);
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

        $url = $this->api_url . 'openFile/move';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['drive_id'] = $this->driveId;
        $data['file_id'] = $file['id'];
        $data['to_parent_file_id'] = $folder['id'];
        $data['check_name_mode'] = 'refuse';

        $result = curl('POST', $url, json_encode($data), $header);
        //savecache('path_' . $file['path'], json_decode('{}',true), $this->disktag, 1);
        //error_log1('result:' . json_encode($result));
        return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
    }
    public function Copy($file) {
        //echo json_encode($file);
        if (!$file['id']) {
            $oldfile = $this->fileGetByPath($file['path'] . '/' . $file['name']);
        } else {
            $oldfile = $this->fileGet($file['id']);
        }
        if ($oldfile['type'] == 'folder') return output('Can not copy a folder', 415);

        $url = $this->api_url . 'openFile/copy';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['drive_id'] = $this->driveId;
        $data['file_id'] = $file['id'];
        $data['to_parent_file_id'] = $oldfile['parent_file_id'];
        $data['auto_rename'] = true;

        $result = curl('POST', $url, json_encode($data), $header);
        //savecache('path_' . $file['path'], json_decode('{}',true), $this->disktag, 1);
        //error_log1('result:' . json_encode($result));
        return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
    }
    public function Edit($file, $content) {
        $tmp = splitlast($file['path'], '/');
        $folderpath = $tmp[0];
        $filename = $tmp[1];
        $existfile = $this->list_path($file['path']);
        if (isset($existfile['type'])) { // 删掉原文件
            $this->Delete(['id' => $existfile['file_id']]);
        }
        $tmp1 = '/tmp/' . $filename;
        file_put_contents($tmp1, $content);

        $result = $this->tmpfileCreate($this->list_path($folderpath)['file_id'], $tmp1, $filename);

        if ($result['stat'] == 200) {
            //error_log1('1,url:' . $url .' res:' . json_encode($result));
            $res = json_decode($result['body'], true);
            $url = $res['part_info_list'][0]['upload_url'];
            if (!$url) { // 无url，应该算秒传
                return output('no up url', 0);
            }
            $file_id = $res['file_id'];
            $upload_id = $res['upload_id'];
            $result = curl('PUT', $url, $content, [], 1);
            if ($result['stat'] == 200) { // 块1传好
                $result = $this->fileComplete($file_id, $upload_id, [$result['returnhead']['ETag']]);
                if ($result['stat'] != 200) return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
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
        if ($type == 'folder') {
            $result = $this->folderCreate($folder['id'], $name);
            //error_log1('res:' . json_encode($result));
            return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
        }
        if ($type == 'file') {
            $tmp = '/tmp/' . $name;
            file_put_contents($tmp, $content);

            $result = $this->tmpfileCreate($folder['id'], $tmp, $name);

            if ($result['stat'] == 200) {
                //error_log1('1,url:' . $url .' res:' . json_encode($result));
                $res = json_decode($result['body'], true);
                if (isset($res['exist']) && $res['exist'] != false) {
                    // 已经有
                    //error_log1('exist:' . json_encode($res));
                    return output('{"type":"file","name":"' . $name . '", "exist":true}', 200);
                }
                if (isset($res['rapid_upload']) && $res['rapid_upload'] != false) {
                    // 秒传
                    //error_log1('rapid up:' . json_encode($res));
                    return output('{"type":"file","name":"' . $name . '", "rapid_upload":true}', 200);
                }
                $url = $res['part_info_list'][0]['upload_url'];
                $file_id = $res['file_id'];
                $upload_id = $res['upload_id'];
                $result = curl('PUT', $url, $content, [], 1);
                //error_log1('2,url:' . $url .' res:' . json_encode($result));
                if ($result['stat'] == 200) { // 块1传好
                    $result = $this->fileComplete($file_id, $upload_id, [$result['returnhead']['ETag']]);
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
        $url = $this->api_url . 'openFile/create';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['drive_id'] = $this->driveId;
        $data['parent_file_id'] = $parentId;
        $data['name'] = $folderName;
        $data['type'] = 'folder';
        $data['check_name_mode'] = 'refuse'; // ignore, auto_rename, refuse.

        return curl('POST', $url, json_encode($data), $header);
    }
    protected function fileCreate($parentId, $fileName, $sha1, $proof_code, $size, $part_number) {
        $url = $this->api_url . 'openFile/create';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['drive_id'] = $this->driveId;
        $data['parent_file_id'] = $parentId;
        $data['name'] = $fileName;
        $data['type'] = 'file';
        $data['check_name_mode'] = 'refuse'; // ignore, auto_rename, refuse.
        for ($i = 0; $i < $part_number; $i++) {
            $data['part_info_list'][$i]['part_number'] = $i + 1;
        }
        $data['size'] = (int)$size;
        $data['content_hash'] = $sha1;
        $data['content_hash_name'] = 'sha1';
        $proof_code = str_replace(" ", "+", $proof_code); // proof code里不可能有空格
        //error_log1($proof_code);
        $data['proof_code'] = $proof_code;
        $data['proof_version'] = 'v1';

        return curl('POST', $url, json_encode($data), $header);
    }
    protected function tmpfileCreate($parentId, $tmpFilePath, $tofileName = '') {
        $sha1 = sha1_file($tmpFilePath);
        if ($tofileName == '') $tofileName = splitlast($tmpFilePath, '/')[1];
        $url = $this->api_url . 'openFile/create';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['drive_id'] = $this->driveId;
        $data['parent_file_id'] = $parentId;
        $data['name'] = $tofileName;
        $data['type'] = 'file';
        $data['check_name_mode'] = 'refuse'; // ignore, auto_rename, refuse.
        $data['part_info_list'][0]['part_number'] = 1; // now only txt
        $data['size'] = filesize($tmpFilePath);
        $data['content_hash'] = $sha1;
        $data['content_hash_name'] = 'sha1';

        return curl('POST', $url, json_encode($data), $header);
    }
    protected function fileComplete($file_id, $upload_id, $etags) {
        $url = $this->api_url . 'openFile/complete';

        $header["content-type"] = "application/json; charset=utf-8";
        $header['authorization'] = 'Bearer ' . $this->access_token;

        $data['drive_id'] = $this->driveId;
        $data['file_id'] = $file_id;
        /*$i = 0;
        foreach ($etags as $etag) {
            $data['part_info_list'][$i]['part_number'] = $i + 1;
            $data['part_info_list'][$i]['etag'] = $etag;
            $i++;
        }*/
        $data['upload_id'] = $upload_id;

        return curl('POST', $url, json_encode($data), $header);
    }

    public function smallfileupload($path, $tmpfile) {
        if (!$_SERVER['admin']) {
            $tmp1 = splitlast($tmpfile['name'], '.');
            if ($tmp1[0] == '' || $tmp1[1] == '') $filename = sha1_file($tmpfile['tmp_name']);
            else $filename = sha1_file($tmpfile['tmp_name']) . '.' . $tmp1[1];
        } else {
            $filename = $tmpfile['name'];
        }
        //$content = file_get_contents($tmpfile['tmp_name']);
        $result = $this->tmpfileCreate($this->list_path($_SERVER['list_path'] . '/' . $path . '/')['file_id'], $tmpfile['tmp_name'], $filename);
        //error_log1('1,url:' . $url .' res:' . json_encode($result));
        if ($result['stat'] == 200) {
            $res = json_decode($result['body'], true);
            $url = $res['part_info_list'][0]['upload_url'];
            if (!$url) { // 无url，应该算秒传
                //return output('no up url', 0);
                $a = 1;
            } else {
                $file_id = $res['file_id'];
                $upload_id = $res['upload_id'];
                //$result = curl('PUT', $url, $content, [], 1);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_PUT, 1);
                curl_setopt($ch, CURLOPT_HEADER, 1);
                $fh_res = fopen($tmpfile['tmp_name'], 'r');
                curl_setopt($ch, CURLOPT_INFILE, $fh_res);
                curl_setopt($ch, CURLOPT_INFILESIZE, filesize($tmpfile['tmp_name']));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $tmpres = splitlast(curl_exec($ch), "\r\n\r\n");
                $result['body'] = $tmpres[1];
                $returnhead = $tmpres[0];
                foreach (explode("\r\n", $returnhead) as $head) {
                    $tmp = explode(': ', $head);
                    $heads[$tmp[0]] = $tmp[1];
                }
                $result['returnhead'] = $heads;
                $result['stat'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                fclose($fh_res);
                curl_close($ch);
                //error_log1('2,url:' . $url .' res:' . json_encode($result));
                if ($result['stat'] == 200) { // 块1传好
                    $result = $this->fileComplete($file_id, $upload_id, [$result['returnhead']['ETag']]);
                    //error_log1('3, res:' . json_encode($result));
                    //if ($result['stat']!=200) return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
                    //else return output('success', 0);
                }
            }
            $res = json_decode($result['body'], true);
            //if (isset($res['url'])) 
            $res[$this->DownurlStrName] = $_SERVER['host'] . path_format($_SERVER['base_disk_path'] . '/' . $path . '/' . $filename);
        }
        return output(json_encode($this->files_format($res), JSON_UNESCAPED_SLASHES), $result['stat']);
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
            if ($_POST['driveId'] != '') {
                $tmp['driveId'] = $_POST['driveId'];
            } else {
                return message('no driveId', 'Error', 201);
            }

            $response = setConfigResponse(setConfig($tmp, $this->disktag));
            if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
                return message($html, $title, 201);
            } else {
                $str = '
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
                $refresh_token = getConfig('refresh_token', $this->disktag);
                if (!$refresh_token) {
                    $html = 'No refresh_token config, please AddDisk again or wait minutes.<br>' . $this->disktag;
                    $title = 'Error';
                    return message($html, $title, 201);
                }
                $response = $this->get_access_token($refresh_token);
                if (!$response) return message($this->error['body'], 'Error', $this->error['stat']);
            }
            $header["content-type"] = "application/json; charset=utf-8";
            $header['authorization'] = 'Bearer ' . $this->access_token;
            $res = curl('POST', $this->api_url . 'user/getDriveInfo', '', $header);
            $result = json_decode($res['body'], true);
            if (isset($result['default_drive_id'])) {
                //$tmp = null;
                //$tmp['driveId'] = $result['default_drive_id'];
                //$tmp['default_sbox_drive_id'] = $result['default_sbox_drive_id'];
                $title = 'Select Driver';
                $html = '
        <div>
            <form action="?Finish&disktag=' . $_GET['disktag'] . '&AddDisk=' . get_class($this) . '" method="post" onsubmit="return notnull(this);">
                <label><input type="radio" name="driveId" value="' . $result['default_drive_id'] . '"' . ($result['default_drive_id'] == $this->driveId ? ' checked' : '') . '>' . '用备份盘 </label><br>
                <label><input type="radio" name="driveId" value="' . $result['resource_drive_id'] . '"' . ($result['resource_drive_id'] == $this->driveId ? ' checked' : '') . '>' . '用资源库 </label><br>
                <input type="submit" value="' . getconstStr('Submit') . '">
            </form>
        </div>
        <script>
            function notnull(t) {
                if (t.driveId.value==\'\') {
                        alert(\'Select a Disk\');
                        return false;
                }
                return true;
            }
        </script>
        ';
                return message($html, $title, 201);
            } else {
                return message('<pre>' . json_encode(json_decode($res['body']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</pre>', $res['stat']);
            }
        }
        if (isset($_GET['install1']) && isset($_GET['code'])) {
            $data['grant_type'] = 'authorization_code';
            $data['code'] = $_GET['code'];
            if ($this->client_id != "" && $this->client_secret != "") {
                $data['client_id'] = $this->client_id;
                $data['client_secret'] = $this->client_secret;
                $tmp = curl('POST', $this->oauth_url . 'access_token',  json_encode($data), ["Content-type" => "application/json"]);
            } else {
                $tmp = no_return_curl('GET', $this->my_oauth_url . "test");
                if ($tmp['stat'] != 0) {
                    $tmp = curl('POST', $this->my_oauth_url . 'access_token',  json_encode($data), ["Content-type" => "application/json"]);
                } else {
                    $title = getconstStr('Wait');
                    $html = '程序连接到授权服务器超时。<br>它是部署在Vercel上的，请确认你服务器与vercel的连接是否正常，<br>另外它可能还在冷启动中，请稍等几秒后点击' . getconstStr('Refresh') . '按钮重试。<br><button onclick="this.style.disabled = 1; location.href = location.href;">' . getconstStr('Refresh') . '</button>';
                    return message($html, $title, 400);
                }
            }
            //error_log(json_encode($tmp));
            if ($tmp['stat'] == 200) $ret = json_decode($tmp['body'], true);
            if (isset($ret['refresh_token'])) {
                $this->access_token = $ret['access_token'];
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
                $tmptoken['Driver'] = $_GET['AddDisk'];
                $tmptoken['refresh_token'] = $refresh_token;
                $tmptoken['token_expires'] = time() + 7 * 24 * 60 * 60;
                $response = setConfigResponse(setConfig($tmptoken, $this->disktag));
                if (api_error($response)) {
                    $html = api_error_msg($response);
                    $title = 'Error';
                    return message($html, $title, 400);
                } else {
                    $html = '<script>
                var i = 0;
                var status = "' . $response['DplStatus'] . '";
            var uploadList = setInterval(function(){
                if (document.getElementById("dis").style.display=="none") {
                    console.log(i++); 
                } else {
                    clearInterval(uploadList);
                    location.href = "?SelectDrive&disktag=' . $this->disktag . '&AddDisk=' . get_class($this) . '";
                }
            }, 1000);
            </script>';
                    return message($html, getconstStr('Wait') . ' 3s', 201, 1);
                }
            } else {
                return message($tmp['body'], $tmp['stat']);
            }
        }
        if (isset($_GET['install0']) && $_POST['disktag_add'] != '') {
            $_POST['disktag_add'] = preg_replace('/[^0-9a-zA-Z|_]/i', '', $_POST['disktag_add']);
            $f = substr($_POST['disktag_add'], 0, 1);
            if (strlen($_POST['disktag_add']) == 1) $_POST['disktag_add'] .= '_';
            if (isCommonEnv($_POST['disktag_add'])) {
                return message('Do not input ' . $envs . '<br><button onclick="location.href = location.href;">' . getconstStr('Refresh') . '</button>
                <script>
                var expd = new Date();
                expd.setTime(expd.getTime()+1);
                var expires = "expires="+expd.toGMTString();
                document.cookie=\'disktag=; path=/; \'+expires;
                </script>', 'Error', 201);
            } elseif (!(('a' <= $f && $f <= 'z') || ('A' <= $f && $f <= 'Z'))) {
                return message('Please start with letters<br><button onclick="location.href = location.href;">' . getconstStr('Refresh') . '</button>
                <script>
                var expd = new Date();
                expd.setTime(expd.getTime()+1);
                var expires = "expires="+expd.toGMTString();
                document.cookie=\'disktag=; path=/; \'+expires;
                </script>', 'Error', 201);
            }
            $tmp = null;
            foreach ($EnvConfigs as $env => $v) if (isInnerEnv($env)) $tmp[$env] = '';

            $tmp['Driver'] = get_class($this);
            $tmp['disktag_add'] = $_POST['disktag_add'];
            $tmp['diskname'] = $_POST['diskname'];
            if ($_POST['Drive_custom'] == 'on' && $_POST['client_id'] != '' && $_POST['client_secret'] != '') {
                $tmp['client_id'] = $_POST['client_id'];
                $tmp['client_secret'] = $_POST['client_secret'];
            }
            //error_log(json_encode($tmp));

            $response = setConfigResponse(setConfig($tmp, $this->disktag));
            if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
                return message($html, $title, 400);
            } else {
                if ($_POST['Drive_custom'] == 'on' && $_POST['client_id'] != '' && $_POST['client_secret'] != '') {
                    $client_id1 = $_POST['client_id'];
                } else {
                    $client_id1 = $this->client_id;
                }
                $title = getconstStr('MayinEnv');
                $html = '
        <a href="" id="a1">' . getconstStr('Wait') . '</a>
        <script>
            var url = location.protocol + "//" + location.host + "' . $url . '?install1&disktag=' . $this->disktag . '&AddDisk=' . get_class($this) . '";
            url = "' . $this->oauth_url . 'authorize?client_id=' . $client_id1 . '&redirect_uri=' . $this->redirect_uri . '&scope=' . $this->scope . '&response_type=code&state=' . '" + window.btoa(url);
            document.getElementById(\'a1\').href = url;
            document.getElementById(\'a1\').innerText = "跳转去登录并授权";
            var i = 0;
            var status = "' . $response['DplStatus'] . '";
            var uploadList = setInterval(function(){
                if (document.getElementById("dis").style.display=="none") {
                    console.log(i++); 
                } else {
                    clearInterval(uploadList);
                    //window.open(url, "_blank");
                    location.href = url;
                }
            }, 1000);
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
                <div id="NT_custom">
                    <label><input type="checkbox" name="Drive_custom" onclick="document.getElementById(\'custom_app\').style.display=(this.checked?\'\':\'none\');">' . getconstStr('CustomIdSecret') . '</label><br>
                    <div id="custom_app" style="display:none;margin:10px 35px">
                        <a href="https://www.aliyundrive.com/developer/f" target="_blank">申请、创建应用</a><br>
                        App ID:<input type="text" name="client_id" style="width:100%" placeholder=""><br>
                        App Secret:<input type="text" name="client_secret" style="width:100%"><br>
                        授权回调URL:<br>' . $this->redirect_uri . '<br>
                    </div>
                </div><br>';
        if ($_SERVER['language'] == 'zh-cn') $html .= '你要理解 scfonedrive.github.io 是github上的静态网站，<br><font color="red">除非github真的挂掉</font>了，<br>不然，稍后你如果<font color="red">连不上</font>，请检查你的运营商或其它“你懂的”问题！<br>';
        $html .= '
        <br>
        <input type="submit" value="' . getconstStr('Submit') . '">
            </form>
        </div>
            <script>
                function notnull(t) {
                    if (t.disktag_add.value==\'\') {
                        alert(\'' . getconstStr('DiskTag') . '\');
                        return false;
                    }
                    if (t.Drive_custom.checked) {
                        if (t.client_id.value==\'\') {
                            alert(\'请输入App ID\');
                            return false;
                        }
                        if (t.client_secret.value==\'\') {
                            alert(\'请输入App Secret\');
                            return false;
                        }
                    }
                    envs = [' . $envs . '];
                    if (envs.indexOf(t.disktag_add.value)>-1) {
                        alert("Do not input ' . $envs . '");
                        return false;
                    }
                    var reg = /^[a-zA-Z]([_a-zA-Z0-9]{1,})$/;
                    if (!reg.test(t.disktag_add.value)) {
                        alert(\'' . getconstStr('TagFormatAlert') . '\');
                        return false;
                    }

                    document.getElementById("form1").action="?install0&disktag=" + t.disktag_add.value + "&AddDisk=' . get_class($this) . '";
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
            $p = 0;
            $url = "";
            $tmp1['grant_type'] = 'refresh_token';
            $tmp1['refresh_token'] = $refresh_token;
            if ($this->client_id != "" && $this->client_secret != "") {
                $tmp1['client_id'] = $this->client_id;
                $tmp1['client_secret'] = $this->client_secret;
                $url = $this->oauth_url;
            } else {
                $url = $this->my_oauth_url;
                $tmp = no_return_curl('GET', $url . "test");
                if ($tmp['stat'] == 0) {
                    $tmp['stat'] = 429;
                    $tmp['body'] = "程序连接到授权服务器超时。<br>它是部署在Vercel上的，请确认你服务器与vercel的连接是否正常，<br>另外它可能还在冷启动中，请稍后几秒再试。";
                    $this->error = $tmp;
                    return false;
                }
            }
            $response = null;
            while ($response['stat'] == 0 && $p < 3) {
                $response = curl('POST', $url . "access_token", json_encode($tmp1), ["Content-Type" => "application/json"]);
                $p++;
            }
            //error_log1(json_encode($response));
            if ($response['stat'] == 200) $ret = json_decode($response['body'], true);
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
            if (time() > getConfig('token_expires', $this->disktag)) setConfig(['refresh_token' => $ret['refresh_token'], 'token_expires' => time() + 3 * 24 * 60 * 60], $this->disktag);
            return true;
        }
        return true;
    }
    public function getDiskSpace() {
        if (!($diskSpace = getcache('diskSpace', $this->disktag))) {
            $url = $this->api_url . 'user/getSpaceInfo';
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
