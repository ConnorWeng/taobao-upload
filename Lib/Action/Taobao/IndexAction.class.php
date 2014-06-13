<?php
import('@.Util.Util');
import('@.Model.StoreSession');

class IndexAction extends CommonAction {
    public function index() {
        $this->display();
    }

    public function auth() {
        $taobaoItemId = I('taobaoItemId');
        session('current_taobao_item_id', $taobaoItemId);
        if (!session('?taobao_access_token') || I('newStore') == 'newStore') {
            if (I('taobaoAppKey') != '') {
                Util::changeTaoAppkey($taobaoItemId, I('taobaoAppKey'));
            } else {
                Util::changeTaoAppkey($taobaoItemId);
            }
            header('location: https://'.C('oauth_uri').'/authorize?response_type=code&client_id='.session('taobao_app_key').'&redirect_uri=http://'.C('redirect_host').urlencode(C('redirect_path')).'&state='.I('newStore').'&view=web');
        } else {
            U('Taobao/Index/authBack', null, true, true, false);
        }
    }

    public function authBack() {
        if (!session('?taobao_access_token') || I('state') == 'newStore') {
            $code = I('code');
            $taobaoItemId = session('current_taobao_item_id');
            $url = 'https://'.C('oauth_uri').'/token';
            $params = array(
                'client_id' => session('taobao_app_key'),
                'client_secret' => session('taobao_secret_key'),
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => 'http://'.C('redirect_host').'/taobao-upload',
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
            $dataObject = json_decode($data);
            if (I('state') == 'newStore') {
                $this->addStoreSession($dataObject);
            } else {
                session('taobao_access_token', $dataObject->access_token);
                session('taobao_user_nick', urldecode($dataObject->taobao_user_nick));
                if ($this->isSubscribe() && session('taobao_app_key') != C('stable_taobao_app_key')) {
                    $this->stable();
                }
            }
        }
        U('Taobao/Upload/editItem', array('taobaoItemId'=>$taobaoItemId), true, true, false);
    }

    public function signOut() {
        $taobaoItemId = session('current_taobao_item_id');
        session(null);
        U('Taobao/Index/auth', array('taobaoItemId' => $taobaoItemId), true, true, false);
    }

    public function verifyCode() {
        $this->assign(array(
            'message' => '抱歉，您操作过于频繁，请输入验证码',
        ));
        $this->display();
    }

    public function checkVerifyCode() {
        if($_SESSION['verify'] != md5($_POST['verifyCode'])) {
            $this->error('验证码错误！');
        } else {
            session('need_verify_code', false);
            session('upload_times_in_minutes', 0);
            U('Taobao/Index/authBack', array('state' => session('current_taobao_item_id')), true, true, false);
        }
    }

    public function verify() {
        import('ORG.Util.Image');
        Image::buildImageVerify();
    }

    public function showError() {
        $msg = I('msg');
        $url = I('url');
        $this->error($msg, $url);
    }

    public function deleteStoreSession() {
        $nick = I('nick');
        $storeSession = new StoreSession($nick);
        $storeSession->deleteStoreSession();
        U('Taobao/Upload/editItem', null, true, true, false);
    }

    private function addStoreSession($data) {
        $storeSession = new StoreSession(urldecode($data->taobao_user_nick), $data->access_token);
        $storeSession->addStoreSession();
    }

    public function switchAppKey() {
        $this->display();
    }

    public function free() {
        $currentTaobaoItemId = session('current_taobao_item_id');
        session(null);
        U('Taobao/Index/auth', array(
            'taobaoItemId' => $currentTaobaoItemId,
        ), true, true, false);
    }

    public function stable() {
        $currentTaobaoItemId = session('current_taobao_item_id');
        $taobaoAppKey = session('taobao_app_key');
        session(null);
        U('Taobao/Index/auth', array(
            'taobaoItemId' => $currentTaobaoItemId,
            'taobaoAppKey' => $taobaoAppKey,
        ), true, true, false);
    }
}