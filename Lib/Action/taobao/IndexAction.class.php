<?php
import('@.Util.Util');

class IndexAction extends Action {
    public function index() {
        $this->display();
    }

    public function auth() {
        $taobaoItemId = I('taobaoItemId');
        Util::changeTaoAppkey($taobaoItemId);
        header('location: https://'.C('oauth_uri').'/authorize?response_type=code&client_id='.session('taobao_app_key').'&redirect_uri=http://'.C('redirect_host').urlencode(U('taobao/Index/authBack')).'&state='.$taobaoItemId.'&view=web');
    }

    public function authBack() {
        $code = I('code');
        $taobaoItemId = I('state');
        $url = 'https://'.C('oauth_uri').'/token';
        $params = array(
            'client_id' => session('taobao_app_key'),
            'client_secret' => session('taobao_secret_key'),
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => 'http://localhost/php/taobao-upload',
        );
        foreach($params as $key=>$value) { $params_string .= $key.'='.$value.'&'; }
        rtrim($params_string, '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($params));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        curl_close($ch);
        session('taobao_access_token', json_decode($data)->access_token);
        U('taobao/Upload/editItem', array('taobaoItemId'=>$taobaoItemId), true, true, false);
    }
}