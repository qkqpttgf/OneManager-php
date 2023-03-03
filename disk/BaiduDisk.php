<?php
    // https://pan.baidu.com/union/doc/Vl19c4jnx
    // http://developer.baidu.com/wiki/index.php?title=docs/oauth/redirect

class BaiduDisk {
    protected $access_token;
    protected $disktag;

    function __construct($tag) {
        $this->disktag = $tag;
        $this->redirect_uri = 'https://scfonedrive.github.io/';
        if (getConfig('client_id', $tag) && getConfig('client_secret', $tag)) {
            $this->appName = 'OneManager';
            $this->client_id = getConfig('client_id', $tag);
            $this->client_secret = getConfig('client_secret', $tag);
        } else {
            $this->appName = 'OneManager';
            $this->client_id = 'knLn7Z8S1YkjIKdjXGr1pBOAuSLGNMbr';
            $this->client_secret = 'BC4M3sLexAfjKUPUdgT3YGhT0dgV5X8t';
        }
        $this->oauth_url = 'http://openapi.baidu.com/oauth/2.0/';
        $this->api_url = 'https://pan.baidu.com/rest/2.0/xpan/';
        $this->scope = 'basic,netdisk';
        $this->client_secret = urlencode($this->client_secret);
        $this->scope = urlencode($this->scope);
        $this->DownurlStrName = 'dlink';
        $this->ext_api_url = '';
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
        return [];
    }

    public function list_files($path = '/')
    {
        global $exts;
        if ($path=='') $path = '/';
        //echo 'path: ' . $path . "<br>\n";
        if (!($files = getcache('path_' . $path, $this->disktag))) {
            /*$pos = splitlast($path, '/');
            $parentpath = $pos[0];
            if ($parentpath=='') $parentpath = '/';
            $filename = strtolower($pos[1]);
            if ($parentfiles = getcache('path_' . $parentpath, $this->disktag)) {
                if (isset($parentfiles['children'][$filename][$this->DownurlStrName])) {
                    if (in_array(splitlast($filename,'.')[1], $exts['txt'])) {
                        if (!(isset($parentfiles['children'][$filename]['content'])&&$parentfiles['children'][$filename]['content']['stat']==200)) {
                            //$content1 = curl('GET', $parentfiles['children'][$filename][$this->DownurlStrName]);
                            //$parentfiles['children'][$filename]['content'] = $content1;
                            //savecache('path_' . $parentpath, $parentfiles, $this->disktag);
                            if ($parentfiles['children'][$filename]['size']<1024*1024) {
                                if (!(isset($parentfiles['children'][$filename]['content'])&&$parentfiles['children'][$filename]['content']['stat']==200)) {
                                    $content1 = curl('GET', $parentfiles['children'][$filename][$this->DownurlStrName]);
                                    $tmp = null;
                                    $tmp = json_decode(json_encode($content1), true);
                                    if ($tmp['body']===null) {
                                        $txtcode = chkTxtCode($content1['body']);
                                        if ($txtcode!==false) $tmp['body'] = iconv($txtcode, 'UTF-8//TRANSLIT', $content1['body']);
                                        $tmp = json_decode(json_encode($tmp), true);
                                        if ($tmp['body']) $content1['body'] = $tmp['body'];
                                    }
                                    $parentfiles['children'][$filename]['content'] = $content1;
                                    savecache('path_' . $path, $parentfiles['children'][$filename], $this->disktag);
                                }
                            } else {
                                $parentfiles['children'][$filename]['content']['stat'] = 202;
                                $parentfiles['children'][$filename]['content']['body'] = 'File too large.';
                            }
                        }
                    }
                    return $this->files_format($parentfiles['children'][$filename]);
                }
            }*/

            $url = $this->api_url . $this->ext_api_url;
            
            //$url .= 'file?method=list&access_token=' . $this->access_token . '&dir=' . $path;
            $url .= 'multimedia?method=listall&access_token=' . $this->access_token . '&path=' . $path . '&web=1';
            $p = 0;
            while ($p<3 && !$arr['stat']) {
                $arr = curl('GET', $url);
                $p ++;
            }
            //echo $url . '<br>外' . $arr['stat'] . '<pre>' . json_encode(json_decode($arr['body']), JSON_PRETTY_PRINT) . '</pre>';
            //echo $arr['body'];
            if ($arr['stat']==200) {
                $files = json_decode($arr['body'], true);
                //echo '<pre>' . json_encode($files, JSON_PRETTY_PRINT) . '</pre>';
                if ($files['errno']==-9) {
                    $files['error']['stat'] = 404;
                    $files['error']['code'] = 'Not Found';
                    $files['error']['message'] = 'NO such file or folder';//errmsg
                } elseif ($files['errno']==-7) {
                    $files['error']['stat'] = 403;
                    $files['error']['code'] = 'Forbidden';
                    $files['error']['message'] = '';//errmsg
                } elseif ($files['errno']===0) {
                    //echo '<pre>正常：' . json_encode($files, JSON_PRETTY_PRINT) . '</pre>';
                    if ($files['list']) { // have item, is folder
                        if ($files['folder']['childCount']>200) {
                            // files num > 200 , then get nextlink
                            $page = $_POST['pagenum']==''?1:$_POST['pagenum'];
                            if ($page>1)
                            //if (!($files = getcache('path_1' . $path . '_' . $page, $this->disktag)))
                            {
                                $children = $this->fetch_files_children($path, $page);
                                //echo '<pre>' . json_encode($children, JSON_PRETTY_PRINT) . '</pre>';
                                $files['children'] = $children['value'];
                                //$files['children'] = children_name($files['children']);
                                $files['folder']['page'] = $page;
                                //savecache('path_' . $path . '_' . $page, $files, $this->disktag);
                            }
                        } else {
                        // files num < 200 , then cache
                            //if (isset($files['children'])) {
                                //$files['list'] = children_name($files['list']);
                            //}
                            savecache('path_' . $path, $files, $this->disktag);
                        }
                    } else { // no item, theck isdir
                        $url = $this->api_url . $this->ext_api_url;
                        $fs = $this->list_files(splitlast($path, '/')[0])['list'][strtolower(urldecode(splitlast($path, '/')[1]))];
                        //var_dump($fs);
                        if ($fs['type']=='folder') {
                            // is empty folder
                            //savecache('path_' . $path, $fs, $this->disktag);
                            $files = $fs;
                        } else {
                            // is file
                            $fsid = $fs['id'];//var_dump($fsid);
                            //$fsid = $this->list_files(splitlast($path, '/')[0])['list'][splitlast($path, '/')[1]];
                            //echo 'ID: ' . $fsid . "<br>\n";
                            //echo 'path: ' . $path;
                            //var_dump(splitlast($path, '/')[1]);
                            //var_dump($fsid);
                            $url .= 'multimedia?method=filemetas&dlink=1&access_token=' . $this->access_token . '&fsids=[' . $fsid . ']';
                            $p = 0;
                            $arr = null;
                            while ($p<3 && !$arr['stat']) {
                                $arr = curl('GET', $url);
                                $p ++;
                            }
                            //echo $url . '<br>是文件' . $arr['stat'] . '<pre>' . json_encode(json_decode($arr['body']), 448) . '</pre>';
                            if ($arr['stat']==200) {
                                $files = json_decode($arr['body'], true)['list'][0];
                                $files['url'] = curl('GET', $files[$this->DownurlStrName] . '&access_token=' . $this->access_token, '', ['User-Agent'=>'pan.baidu.com'], 1)['returnhead']['Location'];
                                $files['list'] = '';
                                if (isset($fs['thumbs'])) $files['thumbs'] = $fs['thumbs'];
                                //echo strtolower(splitlast($files['filename'],'.')[1]) . '进';
                                if (in_array(strtolower(splitlast($files['filename'],'.')[1]), $exts['txt'])) {
                                    if ($files['size']<1024*1024) {
                                        //echo '进1';
                                        if (!(isset($files['content'])&&$files['content']['stat']==200)) {
                                            $content1 = curl('GET', $files['url']);
                                            $tmp = null;
                                            $tmp = json_decode(json_encode($content1), true);
                                            if ($tmp['body']===null) {
                                                $txtcode = chkTxtCode($content1['body']);
                                                if ($txtcode!==false) $tmp['body'] = iconv($txtcode, 'UTF-8//TRANSLIT', $content1['body']);
                                                $tmp = json_decode(json_encode($tmp), true);
                                                if ($tmp['body']) $content1['body'] = $tmp['body'];
                                            }
                                            $files['content'] = $content1;
                                        }
                                    } else {
                                        $files['content']['stat'] = 202;
                                        $files['content']['body'] = 'File too large.';
                                    }
                                }
                                if ($files['url']) savecache('path_' . $path, $files, $this->disktag);
                            } elseif ($arr['stat']==0) {
                                $files['error']['stat'] = 0;
                                $files['error']['code'] = 'Network error';
                                $files['error']['message'] = 'Can not connect to ' . substr($url, 0, strpos($url, '?'));
                            }
                        }
                    }
                } else {
                    $files['error']['stat'] = 500;
                    $files['error']['code'] = 'Other error';
                    $files['error']['message'] = $arr . "~<br>\n" . json_encode($arr) . "~";
                }
            } elseif ($arr['stat']==0) {
                $files['error']['stat'] = 0;
                $files['error']['code'] = 'Network error';
                $files['error']['message'] = 'Can not connect to ' . substr($url, 0, strpos($url, '?'));
            } else {
                //error_log1($arr['body']);
                $files = json_decode($arr['body'], true);
                if ($files['errno']==31066) {
                    $files['error']['stat'] = 404;
                    $files['error']['code'] = 'Not Found';
                    $files['error']['message'] = 'NO such file or folder';//errmsg
                } else {
                    $files['error']['stat'] = 503;
                    $files['error']['code'] = 'unknown Error';
                    $files['error']['message'] = '~' . $arr['body'] . "~";
                }
                //$files = json_decode( '{"unknownError":{ "stat":'.$arr['stat'].',"message":"'.$arr['body'].'"}}', true);
                //error_log1(json_encode($files, JSON_PRETTY_PRINT));
            }
        }
        //echo '<pre>format前' . json_encode($files, 448) . '</pre>';
        return $this->files_format($files);
    }

    protected function files_format($files)
    {
        if (isset($files['list'])) {
            if ($files['list']) {
                $tmp['type'] = 'folder';
                //$tmp['id'] = $files['id'];
                //$tmp['name'] = $files['name'];
                //$tmp['time'] = $files['lastModifiedDateTime'];
                //$tmp['size'] = $files['size'];
                //$tmp['childcount'] = $files['folder']['childCount'];
                //$tmp['page'] = $files['folder']['page'];
                foreach ($files['list'] as $file) {
                    $filename = strtolower($file['server_filename']);
                    if (!$file['isdir']) {
                        $tmp['list'][$filename]['type'] = 'file';
                        //var_dump($file);
                        //echo $file['name'] . ':' . $this->DownurlStrName . ':' . $file[$this->DownurlStrName] . PHP_EOL;
                        //$tmp['list'][$filename]['url'] = $file[$this->DownurlStrName];
                        $tmp['list'][$filename]['mime'] = $file['file']['mimeType'];
                    } elseif ($file['isdir']) {
                        $tmp['list'][$filename]['type'] = 'folder';
                    }
                    $tmp['list'][$filename]['id'] = $file['fs_id'];
                    $tmp['list'][$filename]['name'] = $file['server_filename'];
                    $tmp['list'][$filename]['time'] = date('Y-m-d H:i:s', $file['server_mtime']);
                    $tmp['list'][$filename]['size'] = $file['size'];
                    if (isset($file['thumbs'])) $tmp['list'][$filename]['thumbs'] = $file['thumbs'];
                }
            } else {
                $tmp['type'] = 'file';
                $tmp['id'] = $files['fs_id'];
                $tmp['name'] = $files['filename'];
                $tmp['time'] = date('Y-m-d H:i:s', $files['server_mtime']);
                $tmp['size'] = $files['size'];
                $tmp['mime'] = $files['file']['mimeType'];
                $tmp['url'] = $files['url'];//$files[$this->DownurlStrName];
                if (isset($files['content'])) $tmp['content'] = $files['content'];
                if (isset($files['thumbs'])) $tmp['thumbs'] = $files['thumbs'];
            }
        } elseif (isset($files['type']) || isset($files['error'])) {
            //echo '<pre>' . json_encode($files, JSON_PRETTY_PRINT) . '</pre>';
            return $files;
        }
        //error_log1(json_encode($tmp));
        //echo '<pre>' . json_encode($tmp, JSON_PRETTY_PRINT) . '</pre>';
        return $tmp;
    }

    protected function fetch_files_children($path, $page, $getNextlink = false) {
        $children = getcache('files_' . $path . '_page_' . $page, $this->disktag);
        if (!$children) {
            $url = $this->api_url . $this->ext_api_url;
            if ($path !== '/') {
                $url .= ':' . $path;
                if (substr($url,-1)=='/') $url=substr($url,0,-1);
                $url .= ':';
            }
            $url .= '/children?$top=' . ($page-1)*200 . '&$select=id,name,size,file,folder,parentReference,lastModifiedDateTime,' . $this->DownurlStrName;
            $children_tmp = json_decode($this->MSAPI('GET', $url)['body'], true);
            //echo $url . '<br><pre>' . json_encode($children_tmp, JSON_PRETTY_PRINT) . '</pre>';
            $p = 1;
            $i = 0;
            foreach ($children_tmp['value'] as $child) {
                $i++;
                $value_name = 'child_' . $p;
                ${$value_name}['value'][] = $child;
                if ($i==200) {
                    savecache('files_' . $path . '_page_' . $p, ${$value_name}, $this->disktag);
                    unset(${$value_name});
                    $i = 0;
                    $p++;
                }
            }

            $url = $children_tmp['@odata.nextLink'];
            $children_tmp = json_decode($this->MSAPI('GET', $url)['body'], true);
            //echo $url . '<br><pre>' . json_encode($children_tmp, JSON_PRETTY_PRINT) . '</pre>';
            $p = $page;
            $i = 0;
            foreach ($children_tmp['value'] as $child) {
                $i++;
                $value_name = 'child_' . $p;
                ${$value_name}['value'][] = $child;
                if ($i==200) {
                    savecache('files_' . $path . '_page_' . $p, ${$value_name}, $this->disktag);
                    //unset(${$value_name});
                    $i = 0;
                    $p++;
                }
            }
            if ($i!=0) savecache('files_' . $path . '_page_' . $p, ${$value_name}, $this->disktag);
            $value_name = 'child_' . $page;
            return ${$value_name};
        }
        return $children;
        
        /*if ($getNextlink) {
            if (isset($children['@odata.nextLink'])) {
                return $children;
            } else {
                if ($page*200>9800) {
                    $children_tmp = fetch_files_children($path, floor($page/49)*49, 1);
                    $url = $children_tmp['@odata.nextLink'];
                    $children = json_decode(curl('GET', $url, false, ['Authorization' => 'Bearer ' . $this->access_token])['body'], true);
                }
            }
        }*/
    }
    protected function fetch_files_children1($files, $path, $page)
    {
        $maxpage = ceil($files['folder']['childCount']/200);
        if (!($children = getcache('files_' . $path . '_page_' . $page, $this->disktag))) {
            $pageinfochange=0;
            for ($page1=$page;$page1>=1;$page1--) {
                $page3=$page1-1;
                $url = getcache('nextlink_' . $path . '_page_' . $page3, $this->disktag);
                if ($url == '') {
                    if ($page1==1) {
                        $url = $this->api_url . $this->ext_api_url;
                        if ($path !== '/') {
                            $url .= ':' . $path;
                            if (substr($url,-1)=='/') $url=substr($url,0,-1);
                            $url .= ':';
                        }
                        $url .= '/children?$select=id,name,size,file,folder,parentReference,lastModifiedDateTime,'.$this->DownurlStrName;
                        $children = json_decode($this->MSAPI('GET', $url)['body'], true);
                        // echo $url . '<br><pre>' . json_encode($children, JSON_PRETTY_PRINT) . '</pre>';
                        savecache('files_' . $path . '_page_' . $page1, $children, $this->disktag);
                        $nextlink=getcache('nextlink_' . $path . '_page_' . $page1, $this->disktag);
                        if ($nextlink!=$children['@odata.nextLink']) {
                            savecache('nextlink_' . $path . '_page_' . $page1, $children['@odata.nextLink'], $this->disktag);
                            $pageinfocache['nextlink_' . $path . '_page_' . $page1] = $children['@odata.nextLink'];
                            $pageinfocache = clearbehindvalue($path,$page1,$maxpage,$pageinfocache);
                            $pageinfochange = 1;
                        }
                        $url = $children['@odata.nextLink'];
                        for ($page2=$page1+1;$page2<=$page;$page2++) {
                            sleep(1);
                            $children = json_decode($this->MSAPI('GET', $url)['body'], true);
                            savecache('files_' . $path . '_page_' . $page2, $children, $this->disktag);
                            $nextlink=getcache('nextlink_' . $path . '_page_' . $page2, $this->disktag);
                            if ($nextlink!=$children['@odata.nextLink']) {
                                savecache('nextlink_' . $path . '_page_' . $page2, $children['@odata.nextLink'], $this->disktag);
                                $pageinfocache['nextlink_' . $path . '_page_' . $page2] = $children['@odata.nextLink'];
                                $pageinfocache = clearbehindvalue($path,$page2,$maxpage,$pageinfocache);
                                $pageinfochange = 1;
                            }
                            $url = $children['@odata.nextLink'];
                        }
                        //echo $url . '<br><pre>' . json_encode($children, JSON_PRETTY_PRINT) . '</pre>';
                        return $children;
                        /*
                        $files['children'] = $children['value'];
                        $files['folder']['page']=$page;
                        $pageinfocache['filenum'] = $files['folder']['childCount'];
                        $pageinfocache['dirsize'] = $files['size'];
                        $pageinfocache['cachesize'] = $cachefile['size'];
                        $pageinfocache['size'] = $files['size']-$cachefile['size'];
                        if ($pageinfochange == 1) $this->MSAPI('PUT', path_format($path.'/'.$cachefilename), json_encode($pageinfocache, JSON_PRETTY_PRINT))['body'];
                        return $files;*/
                    }
                } else {
                    for ($page2=$page3+1;$page2<=$page;$page2++) {
                        sleep(1);
                        $children = json_decode($this->MSAPI('GET', $url)['body'], true);
                        savecache('files_' . $path . '_page_' . $page2, $children, $this->disktag, 3300);
                        $nextlink=getcache('nextlink_' . $path . '_page_' . $page2, $this->disktag);
                        if ($nextlink!=$children['@odata.nextLink']) {
                            savecache('nextlink_' . $path . '_page_' . $page2, $children['@odata.nextLink'], $this->disktag, 3300);
                            $pageinfocache['nextlink_' . $path . '_page_' . $page2] = $children['@odata.nextLink'];
                            $pageinfocache = clearbehindvalue($path,$page2,$maxpage,$pageinfocache);
                            $pageinfochange = 1;
                        }
                        $url = $children['@odata.nextLink'];
                    }
                    //echo $url . '<br><pre>' . json_encode($children, JSON_PRETTY_PRINT) . '</pre>';
                    return $children;

                    /*$files['children'] = $children['value'];
                    $files['folder']['page']=$page;
                    $pageinfocache['filenum'] = $files['folder']['childCount'];
                    $pageinfocache['dirsize'] = $files['size'];
                    $pageinfocache['cachesize'] = $cachefile['size'];
                    $pageinfocache['size'] = $files['size']-$cachefile['size'];
                    if ($pageinfochange == 1) $this->MSAPI('PUT', path_format($path.'/'.$cachefilename), json_encode($pageinfocache, JSON_PRETTY_PRINT))['body'];
                    return $files;*/
                }
            }
        }/* else {
            $files['folder']['page']=$page;
            for ($page4=1;$page4<=$maxpage;$page4++) {
                if (!($url = getcache('nextlink_' . $path . '_page_' . $page4, $this->disktag))) {
                    if ($files['folder'][$path.'_'.$page4]!='') savecache('nextlink_' . $path . '_page_' . $page4, $files['folder'][$path.'_'.$page4], $this->disktag);
                } else {
                    $files['folder'][$path.'_'.$page4] = $url;
                }
            }
        }*/
        return $children;
        //return $files;
    }

    public function Rename($file, $newname) {
        $oldname = spurlencode($file['name']);
        $oldname = path_format($file['path'] . '/' . $oldname);
        $data = 'async=1';
        $data .= '&filelist=[{"path":"' . $oldname . '","newname":"' . $newname . '"}]';
        $url = $this->api_url . $this->ext_api_url;
        $url .= 'file?method=filemanager&access_token=' . $this->access_token . '&opera=rename';
        $result = curl('POST', $url, $data);
        if (json_decode($result['body'], true)['errno']===0) return output('{"name":"' . $newname . '"}', 200);
        else return output($result['body'], $result['stat']);
        //return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
    }
    public function Delete($file) {
        $filename = spurlencode($file['name']);
        $filename = path_format($file['path'] . '/' . $filename);
        $data = 'async=1';
        $data .= '&filelist=["' . $filename . '"]';
        $url = $this->api_url . $this->ext_api_url;
        $url .= 'file?method=filemanager&access_token=' . $this->access_token . '&opera=delete';
        $result = curl('POST', $url, $data);
        if (json_decode($result['body'], true)['errno']===0) return output('', 204);
        else return output($result['body'], $result['stat']);
    }
    public function Encrypt($folder, $passfilename, $pass) {
        $result = $this->Create($folder, 'file', $passfilename, $pass);
        $path1 = $folder['path'];
        if ($path1!='/'&&substr($path1, -1)=='/') $path1 = substr($path1, 0, -1);
        savecache('path_' . $path1 . '/?password', '', $this->disktag, 1);
        return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
        //return output($result['body'], $result['stat']);
    }
    public function Move($file, $folder, $replace = 'fail') {
        $filename = spurlencode($file['name']);
        $filename = path_format($file['path'] . '/' . $filename);
        $data = 'async=1';
        $data .= '&filelist=[{"path":"' . $filename . '","dest":"' . $folder['path'] . '","newname":"' . $file['name'] . '","ondup":"' . $replace . '"}]';
        error_log1('data in MOVE: ' . $data);
        $url = $this->api_url . $this->ext_api_url;
        $url .= 'file?method=filemanager&access_token=' . $this->access_token . '&opera=move';
        $result = curl('POST', $url, $data);
        if (!$result['stat']) $result['stat'] = 501;
        $path2 = spurlencode($folder['path'], '/');
        if ($path2!='/'&&substr($path2, -1)=='/') $path2 = substr($path2, 0, -1);
        savecache('path_' . $path2, json_decode('{}', true), $this->disktag, 1);
        if (json_decode($result['body'], true)['errno']===0) return ['stat'=>200, 'body'=>'{"errno":0,"name":"' . $file['name'] . '"}'];
        else return output($result['body'], $result['stat']);
        //return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
        return output($result['body'], $result['stat']);
    }
    public function Copy($file) {
        $filename = spurlencode($file['name']);
        $filename = path_format($file['path'] . '/' . $filename);
        $data = 'async=0';
        $data .= '&filelist=[{"path":"' . $filename . '","dest":"' . $file['path'] . '","newname":"' . $file['name'] . '","ondup":"newcopy"}]';
        $url = $this->api_url . $this->ext_api_url;
        $url .= 'file?method=filemanager&access_token=' . $this->access_token . '&opera=copy';
        while ($p<3 && !$result['stat']) {
            $result = curl('POST', $url, $data);
            $p++;
        }

        /*$namearr = splitlast($file['name'], '.');
        date_default_timezone_set('UTC');
        if ($namearr[0]!='') {
            $newname = $namearr[0] . ' (' . date("Ymd\THis\Z") . ')';
            if ($namearr[1]!='') $newname .= '.' . $namearr[1];
        } else {
            $newname = '.' . $namearr[1] . ' (' . date("Ymd\THis\Z") . ')';
        }
        $data = '{ "name": "' . $newname . '" }';
        if ($file['id']) $result = $this->MSAPI('copy', "/items/" . $file['id'], $data);
        else $result = $this->MSAPI('copy', $filename, $data);
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
            $result = $this->MSAPI('copy', $filename, $data);
        }*/
        if (json_decode($result['body'], true)['errno']===0) return output('{"name":"' . $file['name'] . '"}', 200);
        else return output($result['body'], $result['stat']);
        //return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
        //return output($url . "\n" . $data . "\n" . $result['body'], $result['stat']);
    }
    public function Edit($file, $content) {
        /*TXT一般不会超过4M，不用二段上传
        $filename = $path1 . ':/createUploadSession';
        $response=MSAPI('POST',$filename,'{"item": { "@microsoft.graph.conflictBehavior": "replace"  }}',$_SERVER['access_token']);
        $uploadurl=json_decode($response,true)['uploadUrl'];
        echo MSAPI('PUT',$uploadurl,$data,$_SERVER['access_token']);*/
        $result = $this->MSAPI('PUT', $file['path'], $content);
        //return output($result['body'], $result['stat']);
        //echo $result;
        $resultarry = json_decode($result['body'],true);
        if (isset($resultarry['error'])) return message($resultarry['error']['message']. '<hr><a href="javascript:history.back(-1)">'.getconstStr('Back').'</a>','Error', 403);
        else return output('success', 0);
    }
    public function Create($parent, $type, $name, $content = '') {
        if ($type=='file') {
            $file['path'] = $parent['path'];
            $file['name'] = $name;
            $file['size'] = strlen($content);
            $file['md5s'] = '["' . md5($content) . '"]';
            $result = $this->preCreate($file);
            //echo 'preCreate<pre>' . json_encode($result, 448) . '</pre>';
            if ($result['stat']==200 && json_decode($result['body'],true)['errno']==0) {
                $res = json_decode($result['body'], true);
                if ($res['return_type']==2) {
                    //已有，秒传？
                    error_log1('Rapid_upload');
                    $a = 1;
                } else {
                    $result = $this->partUpload($res['path'], $res['uploadid'], 0, $content);
                    if ($result['stat']==200 && json_decode($result['body'],true)['md5']) {
                        $md5 = json_decode($result['body'], true)['md5'];
                        $info['path'] = $res['path'];
                        $info['size'] = $file['size'];
                        $info['md5s'] = '["' . $md5 . '"]';
                        $info['uploadid'] = $res['uploadid'];
                        $result = $this->completeUpload($info);
                        if ($result['stat']==200 && json_decode($result['body'],true)['errno']==0) {
                            $res = json_decode($result['body'], true);
                            if (substr($res['path'], 0, strlen($res['path'])-strlen($res['server_filename']))!=$parent['path']) {
                                $result = $this->Move(['name'=>$res['server_filename'],'path'=>spurlencode(substr($res['path'], 0, strlen($res['path'])-strlen($res['server_filename'])), '/')], ['path'=>$parent['path']], 'overwrite');
                                $res = json_decode($result['body'], true);
                                if ($res['errno']===0) {
                                    $name1 = splitfirst(splitfirst($parent['path'],'/')[1],'/')[0];
                                    //echo 'deleting ' . $name1;
                                    $this->Delete(['name'=>$name1, 'path'=>'/apps/' . $this->appName . '/']);
                                } else {
                                    $res = json_decode($result['body'], true);
                                    $res['OMmsg'] = 'err in Move.';
                                    $result['body'] = json_encode($res);
                                }
                            }
                        } else {
                            $res1 = json_decode($result['body'], true);
                            $res1['OMmsg'] = 'err in completeUpload.';
                            $res1['preCreate_result'] = $res;
                            $result['body'] = json_encode($res1);
                        }
                    } else {
                        $res = json_decode($result['body'], true);
                        $res['OMmsg'] = 'err in partUpload.';
                        $result['body'] = json_encode($res);
                    }
                }
            } else {
                $res = json_decode($result['body'], true);
                $res['OMmsg'] = 'err in preCreate.';
                $result['body'] = json_encode($res);
            }
        }
        if ($type=='folder') {
            $file['path'] = urldecode($parent['path']) . '/' . $name;
            $file['size'] = strlen($content);
            //$file['md5s'] = '["' . md5($content) . '"]';
            $result = $this->completeUpload($file, 1);
        }
        if ($result['stat']===0) $result['stat'] = 501;
        //savecache('path_' . $path1, json_decode('{}',true), $_SERVER['disktag'], 1);
        //return output(json_encode($this->files_format(json_decode($result['body'], true))), $result['stat']);
        return output($result['body'], $result['stat']);
    }
    private function preCreate($file, $isdir = 0) {
        $url = $this->api_url . $this->ext_api_url;
        $url .= 'file?method=precreate&access_token=' . $this->access_token;
        //error_log1('url: ' . $url);
        if (substr($file['path'],0,17)==='/apps/' . $this->appName . '/') $path = $file['path'];
        else $path = '/apps/' . $this->appName . '/' . $file['path'];
        $data['path'] = path_format($path . '/' . urlencode($file['name']));
        $data['size'] = $file['size'];
        $data['isdir'] = $isdir;
        $data['block_list'] = $file['md5s'];
        $data['autoinit'] = 1;
        $data['rtype'] = 3; // 0: fail, 1: rename, 2: rename when not same, 3: overlay
        foreach ($data as $key => $value) $formdata .= '&' . $key . '=' . $value;
        $formdata = substr($formdata, 1);
        error_log1('data: ' . $formdata);
        while ($p<3 && !$result['stat']) {
            $result = curl('POST', $url, $formdata);
            $p++;
        }
        error_log1('res_pre: ' . json_encode($result));
        return $result;
    }
    private function partUpload($path, $uploadid, $partnum, $content) {
        $url = 'https://d.pcs.baidu.com/rest/2.0/pcs/superfile2?method=upload&access_token=' . $this->access_token . '&type=tmpfile&path=' . $path . '&uploadid=' . $uploadid . '&partseq=' . $partnum;
        $file = sys_get_temp_dir() . '/' . $uploadid . '_' . $partnum;
        file_put_contents($file, $content);
        $data['file'] = '@' . $file;
        while ($p<3 && !$result['stat']) {
            $result = curl('POST', $url, $data, ['content-type'=>'multipart/form-data']);
            $p++;
        }
        error_log1('res_uping: ' . json_encode($result));
        file_put_contents($file, '');
        return $result;
    }
    private function completeUpload($info, $isdir = 0) {
        $url = $this->api_url . $this->ext_api_url;
        $url .= 'file?method=create&access_token=' . $this->access_token;
        //error_log1('url: ' . $url);
        $data['path'] = urlencode($info['path']);
        //$data['path'] = $info['path'];
        $data['size'] = $info['size'];
        $data['isdir'] = $isdir;
        $data['block_list'] = $info['md5s'];
        $data['uploadid'] = $info['uploadid'];
        $data['rtype'] = 3; // 0: fail, 1: rename, 2: rename when not same, 3: overlay
        foreach ($data as $key => $value) $formdata .= '&' . $key . '=' . $value;
        $formdata = substr($formdata, 1);
        error_log1('data: ' . $formdata);
        while ($p<3 && !$result['stat']) {
            $result = curl('POST', $url, $formdata);
            $p++;
        }
        error_log1('res_comp: ' . json_encode($result));
        return $result;
    }

    public function AddDisk() {
        global $constStr;
        global $EnvConfigs;

        $envs = '';
        foreach ($EnvConfigs as $env => $v) if (isCommonEnv($env)) $envs .= '\'' . $env . '\', ';
        $url = $_SERVER['PHP_SELF'];
        //$this->api_url = splitfirst($_SERVER['api_url'], '/v1.0')[0] . '/v1.0';

        if (isset($_GET['Finish'])) {
            if ($this->access_token == '') {
                $refresh_token = getConfig('refresh_token', $this->disktag);
                if (!$refresh_token) {
                    $html = 'No refresh_token config, please AddDisk again or wait minutes.<br>' . $this->disktag;
                    $title = 'Error';
                    return message($html, $title, 201);
                }
                $response = $this->get_access_token($refresh_token);
                if (!$response) return message($this->error['body'], $this->error['stat'] . ' Error', $this->error['stat']);
            }

            $tmp = null;
            $tmp['Driver'] = get_class($this);
            if ($_POST['DriveType']=='Onedrive') {
                /*$api = $this->api_url . '/me';
                $arr = curl('GET', $api, '', [ 'Authorization' => 'Bearer ' . $this->access_token ], 1);
                if ($arr['stat']==200) {
                    $userid = json_decode($arr['body'], true)['id'];
                    $api = $this->api_url . '/users/' . $userid . '/drive';
                    $arr = curl('GET', $api, '', [ 'Authorization' => 'Bearer ' . $this->access_token ], 1);
                    if ($arr['stat']!=200) return message($arr['stat'] . '<br>' . $api . '<br>' . $arr['body'], 'Get User Drive ID', $arr['stat']);
                    $tmp['DriveId'] = json_decode($arr['body'], true)['id'];
                } elseif ($arr['stat']==403||$arr['stat']==401) {
                    // 403：世纪不让列me，401：个人也不给拿
                    $api = $this->api_url . '/me/drive';
                } else {
                    return message($arr['stat'] . $arr['body'], 'Get User ID', $arr['stat']);
                }*/
                if ($tmp['Driver']=='Sharepoint') $tmp['Driver'] = 'Onedrive';
                elseif ($tmp['Driver']=='SharepointCN') $tmp['Driver'] = 'OnedriveCN';
                $tmp['sharepointSite'] = '';
                $tmp['siteid'] = '';
            } elseif ($_POST['DriveType']=='Custom') {
                // sitename计算siteid
                $tmp1 = $this->get_siteid($_POST['sharepointSite']);
                if (isset($tmp1['stat'])) return message($arr['stat'] . $tmp1['body'], 'Get Sharepoint Site ID ' . $_POST['sharepointSite'], $tmp1['stat']);
                $siteid = $tmp1;
                //$api = $this->api_url . '/sites/' . $siteid . '/drive/';
                //$arr = curl('GET', $api, '', [ 'Authorization' => 'Bearer ' . $this->access_token ], 1);
                //if ($arr['stat']!=200) return message($arr['stat'] . $arr['body'], 'Get Sharepoint Drive ID ' . $_POST['DriveType'], $arr['stat']);
                $tmp['siteid'] = $siteid;
                $tmp['sharepointSite'] = $_POST['sharepointSite'];
                if ($tmp['Driver']=='Onedrive') $tmp['Driver'] = 'Sharepoint';
                elseif ($tmp['Driver']=='OnedriveCN') $tmp['Driver'] = 'SharepointCN';
            } else {
                // 直接是siteid
                $tmp['siteid'] = $_POST['DriveType'];
                $tmp['sharepointSite'] = $_POST['sharepointSiteUrl'];
                if ($tmp['Driver']=='Onedrive') $tmp['Driver'] = 'Sharepoint';
                elseif ($tmp['Driver']=='OnedriveCN') $tmp['Driver'] = 'SharepointCN';
            }

            $response = setConfigResponse( setConfig($tmp, $this->disktag) );
            if (api_error($response)) {
                $html = api_error_msg($response);
                $title = 'Error';
                return message($html, $title, 201);
            } else {
                $html .= '<script>
                var expd = new Date();
                expd.setTime(expd.getTime()+1);
                var expires = "expires="+expd.toGMTString();
                document.cookie=\'disktag=; path=/; \'+expires;
                var i = 0;
                var status = "' . $response['DplStatus'] . '";
                var uploadList = setInterval(function(){
                    if (document.getElementById("dis").style.display=="none") {
                        console.log(i++);
                    } else {
                        clearInterval(uploadList);
                        location.href = "' . $url . '?setup&disktag=' . $_GET['disktag'] . '";
                    }
                }, 1000);
                </script>';
                return message($html, getconstStr('Wait'), 201, 1);
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
                if (!$response) return message($this->error['body'], $this->error['stat'] . ' Error', $this->error['stat']);
            }
            $url = 'https://pan.baidu.com/rest/2.0/xpan/nas?method=uinfo&access_token=' . $this->access_token;
            $response = json_decode(curl('GET', $url)['body'], true);
            $html = '头图 ' . $response['avatar_url'] . "<br>\n";
            $html .= '名 ' . $response['baidu_name'] . "<br>\n";
            $html .= '名 ' . $response['netdisk_name'] . "<br>\n";
            $html .= $response['errno'] . "<br>\n";
            $html .= $response['errmsg'] . "<br>\n";
            
            $html .= '空间 ' . $this->getDiskSpace() . "<br>\n";

            $html .= '程序还在测试，上面信息显示正常就请直接去首页。';
            return message($html, $title, 201);

            $title = 'Select Driver';
            $html = '
<div>
    <form name="form1" action="?Finish&disktag=' . $_GET['disktag'] . '&AddDisk=' . get_class($this) . '" method="post" onsubmit="return notnull(this);">
        <label><input type="radio" name="DriveType" value="Onedrive">' . 'Use Onedrive ' . getconstStr(' ') . '</label><br>';
            if ($sites[0]!='') foreach ($sites as $k => $v) {
                $html .= '
        <label>
            <input type="radio" name="DriveType" value="' . $v['id'] . '" onclick="document.getElementById(\'sharepointSiteUrl\').value=\'' . $v['webUrl'] . '\';">' . 'Use Sharepoint: <br><div style="width:100%;margin:0px 35px">webUrl: ' . $v['webUrl'] . '<br>siteid: ' . $v['id'] . '</div>
            <input type="hidden" id="' . $v['id'] . '" name="' . $v['id'] . '" value="' . $v['webUrl'] . '">
        </label>';
            }
            $html .= '
        <input type="hidden" id="sharepointSiteUrl" name="sharepointSiteUrl" value="">
        <label>
            <input type="radio" name="DriveType" value="Custom" id="Custom">' . 'Use Other Sharepoint:' . getconstStr(' ') . '<br>
            <div style="width:100%;margin:0px 35px"><a href="' . $Tenant . '/_layouts/15/sharepoint.aspx" target="_blank">' . getconstStr('GetSharepointSiteAddress') . '</a><br>
                <input type="text" name="sharepointSite" style="width:100%;" placeholder="' . getconstStr('InputSharepointSiteAddress') . '" onclick="document.getElementById(\'Custom\').checked=\'checked\';">
            </div>
        </label><br>
        ';
            $html .= '
        <input type="submit" value="' . getconstStr('Submit') . '">
    </form>
</div>
<script>
    var driver = "' . $_GET['AddDisk'] . '";
    if (driver=="Onedrive"||driver=="OnedriveCN") {
        document.form1.DriveType[0].checked = true;
    } else {
        var siteid = "' . getConfig('siteid', $this->disktag) . '";
        if (siteid==="") {
            document.form1.DriveType[document.form1.DriveType.length-1].checked = true;
        } else {
            for (i=0; i<document.form1.DriveType.length; i++) {
                if (document.form1.DriveType[i].value==siteid) {
                    document.form1.DriveType[i].checked = true;
                    document.getElementById(\'sharepointSiteUrl\').value = document.getElementById(siteid).value;
                }
            }
            if (document.getElementById(\'sharepointSiteUrl\').value=="") {
                document.form1.DriveType[document.form1.DriveType.length-1].checked = true;
                document.getElementById(\'sharepointSite\').value = "' . getConfig('sharepointSite', $this->disktag) . '";
            }
        }
    }
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
            $tmp = curl('GET', $this->oauth_url . 'token?grant_type=authorization_code&code=' . $_GET['code'] . '&client_id=' . $this->client_id .'&client_secret=' . $this->client_secret . '&redirect_uri=' . $this->redirect_uri );
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
                $tmptoken['Driver'] = $_GET['AddDisk'];
                $tmptoken['refresh_token'] = $refresh_token;
                $tmptoken['token_expires'] = time()+7*24*60*60;
                $response = setConfigResponse( setConfig($tmptoken, $this->disktag) );
                if (api_error($response)) {
                    $html = api_error_msg($response);
                    $title = 'Error';
                    return message($html, $title, 201);
                } else {
                    savecache('access_token', $ret['access_token'], $this->disktag, $ret['expires_in'] - 60);
                    $html .= '<script>
                    var i = 0;
                    var status = "' . $response['DplStatus'] . '";
                var uploadList = setInterval(function(){
                    if (document.getElementById("dis").style.display=="none") {
                        console.log(i++);
                    } else {
                        clearInterval(uploadList);
                        location.href = "' . $url . '?AddDisk=' . $_GET['AddDisk'] . '&disktag=' . $_GET['disktag'] . '&SelectDrive";
                    }
                }, 1000);
                </script>';
                    return message($html, getconstStr('Wait') . ' 3s', 201, 1);
                }
            }
            return message('<pre>' . json_encode(json_decode($tmp['body']), JSON_PRETTY_PRINT) . '</pre>', $tmp['stat']);
            //return message('<pre>' . json_encode($ret, JSON_PRETTY_PRINT) . '</pre>', 500);
        }

        if (isset($_GET['install1'])) {
            //if (get_class($this)=='Onedrive' || get_class($this)=='OnedriveCN') {
                return message('
    <a href="" id="a1">' . getconstStr('JumptoOffice') . '</a>
    <script>
        url=location.protocol + "//" + location.host + "' . $url . '?install2&disktag=' . $_GET['disktag'] . '&AddDisk=' . get_class($this) . '";
        url="' . $this->oauth_url . 'authorize?response_type=code&client_id=' . $this->client_id . '&redirect_uri=' . $this->redirect_uri . '&scope=' . $this->scope . '&state=' . '"+encodeURIComponent(url);
        document.getElementById(\'a1\').href=url;
        //window.open(url,"_blank");
        location.href = url;
    </script>
    ', getconstStr('Wait') . ' 1s', 201);
            //} else {
            //    return message('Something error, retry after a few seconds.', 'Retry', 201);
            //}
        }

        if (isset($_GET['install0'])) {
            if ($_POST['disktag_add']!='') {
                $_POST['disktag_add'] = preg_replace('/[^0-9a-zA-Z|_]/i', '', $_POST['disktag_add']);
                $f = substr($_POST['disktag_add'], 0, 1);
                if (strlen($_POST['disktag_add'])==1) $_POST['disktag_add'] .= '_';
                if (isCommonEnv($_POST['disktag_add'])) {
                    return message('Do not input ' . $envs . '<br><button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>', 'Error', 400);
                } elseif (!(('a'<=$f && $f<='z') || ('A'<=$f && $f<='Z'))) {
                    return message('Please start with letters<br><button onclick="location.href = location.href;">'.getconstStr('Refresh').'</button>
                    <script>
                    var expd = new Date();
                    expd.setTime(expd.getTime()+1);
                    var expires = "expires="+expd.toGMTString();
                    document.cookie=\'disktag=; path=/; \'+expires;
                    </script>', 'Error', 400);
                }

                $tmp = null;
                // clear envs
                foreach ($EnvConfigs as $env => $v) if (isInnerEnv($env)) $tmp[$env] = '';

                $tmp['disktag_add'] = $_POST['disktag_add'];
                $tmp['diskname'] = $_POST['diskname'];
                $tmp['Driver'] = $_GET['AddDisk'];
                if ($_POST['custom_client']=='on') {
                    $tmp['client_id'] = $_POST['client_id'];
                    $tmp['client_secret'] = $_POST['client_secret'];
                }
                $response = setConfigResponse( setConfig($tmp, $this->disktag) );
                if (api_error($response)) {
                    $html = api_error_msg($response);
                    $title = 'Error';
                    return message($html, $title, 400);
                } else {
                    $title = getconstStr('MayinEnv');
                    $html = getconstStr('Wait');
                    $url .= '?install1&disktag=' . $_GET['disktag'] . '&AddDisk=' . $_GET['AddDisk'];
                    $html .= '<script>
                    var i = 0;
                    var status = "' . $response['DplStatus'] . '";
                var uploadList = setInterval(function(){
                    if (document.getElementById("dis").style.display=="none") {
                        console.log(i++);
                    } else {
                        clearInterval(uploadList);
                        location.href = "' . $url . '";
                    }
                }, 1000);
                </script>';
                    return message($html, $title, 201, 1);
                }
                
            }
        }

        $html = '
<div>
    <form id="form1" name="form1" action="" method="post" onsubmit="return notnull(this);">
        ' . getconstStr('DiskTag') . ': (' . getConfig('disktag') . ')
        <input type="text" name="disktag_add" placeholder="' . getconstStr('EnvironmentsDescription')['disktag'] . '" style="width:100%"><br>
        ' . getconstStr('DiskName') . ':
        <input type="text" name="diskname" placeholder="' . getconstStr('EnvironmentsDescription')['diskname'] . '" style="width:100%"><br>
        <br>
        <label><input type="checkbox" name="custom_client" onclick="document.getElementById(\'secret\').style.display=(this.checked?\'\':\'none\');">' . getconstStr('CustomIdSecret') . '</label><br>
        <div id="secret" style="display:none;margin:10px 35px">
            <a href="https://pan.baidu.com/union/console/applist" target="_blank">' . getconstStr('GetSecretIDandKEY') . '</a><br>
            return_uri(Reply URL):<br>' . $this->redirect_uri . '<br>
            client_id:<input type="text" name="client_id" style="width:100%" placeholder="Appkey"><br>
            client_secret:<input type="text" name="client_secret" style="width:100%" placeholder="Secretkey"><br>
        </div>
        <br>';
        $html .= '第三方程序只能在"/apps/程序名/"有上传权限，所以最好将public_path设置为"/apps/' . $this->appName . '/"。<br><br>';
        if ($_SERVER['language']=='zh-cn') $html .= '你要理解 scfonedrive.github.io 是github上的静态网站，<br><font color="red">除非github真的挂掉</font>了，<br>不然，稍后你如果<font color="red">连不上</font>，请检查你的运营商或其它“你懂的”问题！<br>';
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
            if (t.custom_client.checked==true) {
                if (t.client_secret.value==\'\'||t.client_id.value==\'\') {
                    alert(\'client_id & client_secret\');
                    return false;
                }
            }
            envs = [' . $envs . '];
            if (envs.indexOf(t.disktag_add.value)>-1) {
                alert("Do not input \"" + t.disktag_add.value + "\" in ' . getconstStr('DiskTag') . '");
                return false;
            }
            var reg = /^[a-zA-Z]([_a-zA-Z0-9]{1,})$/;
            if (!reg.test(t.disktag_add.value)) {
                alert(\'' . getconstStr('TagFormatAlert') . '\');
                return false;
            }
            document.getElementById("form1").action="?install0&disktag=" + t.disktag_add.value + "&AddDisk=' . $_GET['AddDisk'] . '";
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
            while ($response['stat']==0&&$p<3) {
                $response = curl('GET', $this->oauth_url . 'token?grant_type=refresh_token&refresh_token=' . $refresh_token . '&client_id=' . $this->client_id . '&client_secret=' . $this->client_secret );
                $p++;
            }
            if ($response['stat']==200) $ret = json_decode($response['body'], true);
            if (!isset($ret['access_token'])) {
                error_log1($this->oauth_url . 'token' . '?client_id=' . $this->client_id . '&client_secret=' . $this->client_secret . '&grant_type=refresh_token&requested_token_use=on_behalf_of&refresh_token=' . substr($refresh_token, 0, 20) . '******' . substr($refresh_token, -20));
                error_log1('failed to get [' . $this->disktag . '] access_token. response: ' . $response['body']);
                $response['body'] = json_encode(json_decode($response['body']), JSON_PRETTY_PRINT);
                $response['body'] .= '\nfailed to get [' . $this->disktag . '] access_token.';
                $this->error = $response;
                return false;
                //throw new Exception($response['stat'].', failed to get ['.$this->disktag.'] access_token.'.$response['body']);
            }
            $tmp = $ret;
            $tmp['access_token'] = substr($tmp['access_token'], 0, 10) . '******';
            $tmp['refresh_token'] = substr($tmp['refresh_token'], 0, 10) . '******';
            error_log1('[' . $this->disktag . '] Get access token:' . json_encode($tmp, JSON_PRETTY_PRINT));
            $this->access_token = $ret['access_token'];
            savecache('access_token', $this->access_token, $this->disktag, $ret['expires_in'] - 300);
            //if (time()>getConfig('token_expires', $this->disktag)) 
            setConfig([ 'refresh_token' => $ret['refresh_token'], 'token_expires' => time()+7*24*60*60 ], $this->disktag);
            return true;
        }
        return true;
    }

    public function del_upload_cache($path)
    {
        error_log1('del.tmp:GET,'.json_encode($_GET,JSON_PRETTY_PRINT));
        $tmp = splitlast($_GET['filename'], '/');
        if ($tmp[1]!='') {
            $filename = $tmp[0] . '/.' . $_GET['filelastModified'] . '_' . $_GET['filesize'] . '_' . $tmp[1] . '.tmp';
        } else {
            $filename = '.' . $_GET['filelastModified'] . '_' . $_GET['filesize'] . '_' . $_GET['filename'] . '.tmp';
        }
        $filename = path_format( path_format($_SERVER['list_path'] . path_format($path)) . '/' . spurlencode($filename, '/') );
        $tmp = $this->MSAPI('DELETE', $filename, '');
        $path1 = path_format($_SERVER['list_path'] . path_format($path));
        if ($path1!='/'&&substr($path1,-1)=='/') $path1=substr($path1,0,-1);
        savecache('path_' . $path1, json_decode('{}',true), $this->disktag, 1);
        return output($tmp['body'],$tmp['stat']);
    }

    public function get_thumbnails_url($path = '/')
    {
        $thumb_url = getcache('thumb_'.$path, $this->disktag);
        if ($thumb_url=='') {
            $files = $this->list_files(splitlast($path, '/')[0])['list'][strtolower(urldecode(splitlast($path, '/')[1]))];
            if (isset($files['thumbs'])) {
                savecache('thumb_' . $path, $files['thumbs']['url2'], $this->disktag);
                $thumb_url = $files['thumbs']['url2'];
            }
        }
        return $thumb_url;
    }

    public function smallfileupload($path, $tmpfile) {
        if (!$_SERVER['admin']) {
            $tmp1 = splitlast($tmpfile['name'], '.');
            if ($tmp1[0]==''||$tmp1[1]=='') $filename = md5_file($tmpfile['tmp_name']);
            else $filename = md5_file($tmpfile['tmp_name']) . '.' . $tmp1[1];
        } else {
            $filename = $tmpfile['name'];
        }
        $content = file_get_contents($tmpfile['tmp_name']);
        $result = $this->Create(['path'=>path_format($_SERVER['list_path'] . '/' . $path . '/')], 'file', $filename, $content);
        //echo 'Small<pre>' . json_encode($result, 448) . '</pre>';
        $res = json_decode($result['body'], true);
        if (isset($res['errno'])&&$res['errno']===0) $res['url'] = $_SERVER['host'] . path_format($_SERVER['base_disk_path'] . '/' . $path . '/' . $filename);
        return output(json_encode($res, JSON_UNESCAPED_SLASHES), $result['stat']);
    }
    public function bigfileupload($path)
    {
        if ($_POST['upbigfilename']=='') return output('error: no file name', 400);
        if (!is_numeric($_POST['filesize'])) return output('error: no file size', 400);
        if (!$_SERVER['admin']) if (!isset($_POST['filemd5'])) return output('error: no file md5', 400);

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
            $filename = spurlencode($_POST['upbigfilename'], '/');
        } else {
            $tmp1 = splitlast($fileinfo['name'], '.');
            if ($tmp1[0]==''||$tmp1[1]=='') $filename = $_POST['filemd5'];
            else $filename = $_POST['filemd5'] . '.' . $tmp1[1];
        }
        if ($fileinfo['size']>10*1024*1024) {
            $cachefilename = spurlencode( $fileinfo['path'] . '/.' . $fileinfo['filelastModified'] . '_' . $fileinfo['size'] . '_' . $fileinfo['name'] . '.tmp', '/');
            $getoldupinfo = $this->list_files(path_format($path . '/' . $cachefilename));
            //error_log1(json_encode($getoldupinfo, JSON_PRETTY_PRINT));
            if (isset($getoldupinfo['url'])&&$getoldupinfo['size']<5120) {
                $getoldupinfo_j = curl('GET', $getoldupinfo['url']);
                $getoldupinfo = json_decode($getoldupinfo_j['body'], true);
                if ( json_decode( curl('GET', $getoldupinfo['uploadUrl'])['body'], true)['@odata.context']!='' ) return output($getoldupinfo_j['body'], $getoldupinfo_j['stat']);
            }
        }
        $response = $this->MSAPI('createUploadSession', path_format($path . '/' . $filename), '{"item": { "@microsoft.graph.conflictBehavior": "fail" }}');
        if ($response['stat']<500) {
            $responsearry = json_decode($response['body'],true);
            if (isset($responsearry['error'])) return output($response['body'], $response['stat']);
            $fileinfo['uploadUrl'] = $responsearry['uploadUrl'];
            if ($fileinfo['size']>10*1024*1024) $this->MSAPI('PUT', path_format($path . '/' . $cachefilename), json_encode($fileinfo, JSON_PRETTY_PRINT));
        }
        return output($response['body'], $response['stat']);
    }
    public function getDiskSpace() {
        if (!($diskSpace = getcache('diskSpace', $this->disktag))) {
            $url = 'https://pan.baidu.com/api/quota?access_token=' . $this->access_token;
            $p=0;
            while ($response['stat']==0&&$p<3) {
                $response = curl('GET', $url);
                $p++;
            }//return $response['body'];
            $res = json_decode($response['body'], true);
            $used = size_format($res['used']);
            $total = size_format($res['total']);
            $diskSpace = $used . ' / ' . $total;
            //$diskSpace = $response['body'] . '<br>' . $res['used'] . ' / ' . $res['total'];
            savecache('diskSpace', $diskSpace, $this->disktag);
        }
        return $diskSpace;
    }
    public function ConduitDown($url, $filetime, $fileConduitCacheTime) {
        $res = curl('GET', $url . '&access_token=' . $this->access_token, '', ['User-Agent'=>'pan.baidu.com'], 0, 1);
        //echo $res['returnhead']['Location'];
        if ($res['stat']==200) {
            return output(
                base64_encode($res['body']),
                200,
                [
                    'Accept-Ranges' => 'bytes',
                    //'access-control-allow-origin' => '*',
                    //'access-control-expose-headers' => 'Content-Length, WWW-Authenticate, Location, Accept-Ranges',
                    'Content-Type' => $files['mime'],
                    'Cache-Control' => 'max-age=' . $fileConduitCacheTime,
                    //'Cache-Control' => 'max-age=0',
                    'Last-Modified' => gmdate('D, d M Y H:i:s T', strtotime($filetime))
                ], 
                true
            );
        } else {
            return output($res['body'], $res['stat']);
        }
    }

    protected function MSAPI($method, $path, $data = '', $headers = [])
    {
        $activeLimit = getConfig('activeLimit', $this->disktag);
        if ($activeLimit!='') {
            if ($activeLimit>time()) {
                $tmp['error']['code'] = 'Retry-After';
                $tmp['error']['message'] = 'MS limit until ' . date('Y-m-d H:i:s', $activeLimit);
                return [ 'stat' => 429, 'body' => json_encode($tmp) ];
            } else {
                setConfig(['activeLimit' => ''], $this->disktag);
            }
        }
        if (substr($path,0,7) == 'http://' or substr($path,0,8) == 'https://') {
            $url = $path;
        } else {
            $url = $this->api_url . $this->ext_api_url;
            if ($path=='' or $path=='/') {
                $url .= '/';
            } elseif (substr($path, 0, 6)=="/items") {
                $url = substr($url, 0, -5);
                $url .= $path;
            } else {
                $url .= ':' . $path;
                if (substr($url,-1)=='/') $url=substr($url,0,-1);
            }
            if ($method=='GET') {
                $method = 'GET'; // do nothing
            } elseif ($method=='PUT') {
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
                } elseif (substr($path, 0, 6)=="/items") {
                    $url .= '/' . $method;
                } else {
                    $url .= ':/' . $method;
                }
                $method='POST';
                $headers['Content-Type'] = 'application/json';
            }
        }
        $headers['Authorization'] = 'Bearer ' . $this->access_token;
        if (!isset($headers['Accept'])) $headers['Accept'] = '*/*';
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
        $retry = 0;
        $response = [];
        while ($retry<3&&!$response['stat']) {
            $response['body'] = curl_exec($ch);
            $response['stat'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $retry++;
        }
        //$response['Location'] = curl_getinfo($ch);
        if ($response['stat']==429) {
            $res = json_decode($response['body'], true);
            $retryAfter = $res['error']['retryAfterSeconds'];
            $retryAfter_n = (int)$retryAfter;
            if ($retryAfter_n>0) {
                $tmp1['activeLimit'] = $retryAfter_n + time();
                setConfig($tmp1, $this->disktag);
            }
        }
        curl_close($ch);
        /*error_log1($response['stat'].'
    '.$response['body'].'
    '.$url.'
    ');*/
        return $response;
    }

}
