<?php
if (!class_exists('Onedrive')) require 'Onedrive.php';

class Sharepoint extends Onedrive {

    function __construct($tag) {
        $this->disktag = $tag;
        $this->redirect_uri = 'https://scfonedrive.github.io';
        if (getConfig('client_id', $tag) && getConfig('client_secret', $tag)) {
            $this->client_id = getConfig('client_id', $tag);
            $this->client_secret = getConfig('client_secret', $tag);
        } else {
            $this->client_id = '734ef928-d74c-4555-8d1b-d942fa0a1a41';
            $this->client_secret = ':EK[e0/4vQ@mQgma8LmnWb6j4_C1CSIW';
        }
        $this->oauth_url = 'https://login.microsoftonline.com/common/oauth2/v2.0/';
        $this->api_url = 'https://graph.microsoft.com/v1.0';
        $this->scope = 'https://graph.microsoft.com/Files.ReadWrite.All offline_access';
        $res = $this->get_access_token(getConfig('refresh_token', $tag));

        $this->client_secret = urlencode($this->client_secret);
        $this->scope = urlencode($this->scope);
        $this->DownurlStrName = '@microsoft.graph.downloadUrl';
        $this->ext_api_url = '/sites/' . getConfig('siteid', $tag) . '/drive/root';
    }

    public function ext_show_innerenv()
    {
        return [ 'sharepointSite', 'siteid' ];
    }
}
