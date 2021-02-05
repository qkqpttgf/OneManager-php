<?php
if (!class_exists('Onedrive')) require 'Onedrive.php';

class Sharelink extends Onedrive {

    function __construct($tag) {
        $this->disktag = $tag;
        $this->redirect_uri = 'https://scfonedrive.github.io';
        $this->api_url = getConfig('shareapiurl', $tag);
        $res = $this->get_access_token(1);
        //$this->ext_api_url = '/me/drive/root';
        $this->DownurlStrName = '@content.downloadUrl';
    }

    public function ext_show_innerenv()
    {
        return [ 'shareurl' ];
    }

    protected function get_access_token($refresh_token) {
        if (!($this->access_token = getcache('access_token', $this->disktag))) {
            $shareurl = getConfig('shareurl', $this->disktag);
            if (!($this->sharecookie = getcache('sharecookie', $this->disktag))) {
                $this->sharecookie = curl('GET', $shareurl, false, [], 1)['returnhead']['Set-Cookie'];
                //$tmp = curl_request($shareurl, false, [], 1);
                //$tmp['body'] .= json_encode($tmp['returnhead'],JSON_PRETTY_PRINT);
                //return $tmp;
                //$_SERVER['sharecookie'] = $tmp['returnhead']['Set-Cookie'];
                //if ($tmp['stat']==302) $url = $tmp['returnhead']['Location'];
                //return curl('GET', $url, [ 'Accept' => 'application/json;odata=verbose', 'Content-Type' => 'application/json;odata=verbose', 'Cookie' => $_SERVER['sharecookie'] ]);
                savecache('sharecookie', $this->sharecookie, $this->disktag);
            }
            $tmp1 = splitlast($shareurl, '/')[0];
            $account = splitlast($tmp1, '/')[1];
            $domain = splitlast($shareurl, '/:')[0];
            $response = curl('POST', 
                $domain . "/personal/" . $account . "/_api/web/GetListUsingPath(DecodedUrl=@a1)/RenderListDataAsStream?@a1='" . urlencode("/personal/" . $account . "/Documents") . "'&RootFolder=" . urlencode("/personal/" . $account . "/Documents/") . "&TryNewExperienceSingle=TRUE",
                '{"parameters":{"__metadata":{"type":"SP.RenderListDataParameters"},"RenderOptions":136967,"AllowMultipleValueFilterForTaxonomyFields":true,"AddRequiredFields":true}}',
                [ 'Accept' => 'application/json;odata=verbose', 'Content-Type' => 'application/json;odata=verbose', 'origin' => $domain, 'Cookie' => $this->sharecookie ]
            );
            if ($response['stat']==200) $ret = json_decode($response['body'], true);
            $this->access_token = splitlast($ret['ListSchema']['.driveAccessToken'],'=')[1];
            $this->api_url = $ret['ListSchema']['.driveUrl'].'/root';
            if (!$this->access_token) {
                error_log1($domain . "/personal/" . $account . "/_api/web/GetListUsingPath(DecodedUrl=@a1)/RenderListDataAsStream?@a1='" . urlencode("/personal/" . $account . "/Documents") . "'&RootFolder=" . urlencode("/personal/" . $account . "/Documents/") . "&TryNewExperienceSingle=TRUE");
                error_log1('failed to get share access_token. response' . json_encode($ret));
                $response['body'] = json_encode(json_decode($response['body']), JSON_PRETTY_PRINT);
                $response['body'] .= '\nfailed to get shareurl access_token.';
                return $response;
                //throw new Exception($response['stat'].', failed to get share access_token.'.$response['body']);
            }
            //$tmp = $ret;
            //$tmp['access_token'] = '******';
            //error_log1('['.$this->disktag.'] Get access token:'.json_encode($tmp, JSON_PRETTY_PRINT));
            savecache('access_token', $this->access_token, $this->disktag);
            $tmp1 = null;
            if (getConfig('shareapiurl', $this->disktag)!=$this->api_url) $tmp1['shareapiurl'] = $this->api_url;
            //if (getConfig('sharecookie', $this->disktag)!=$this->sharecookie) $tmp1['sharecookie'] = $this->sharecookie;
            if (!!$tmp1) setConfig($tmp1);
            return true;
        }
        return true;
    }
}
