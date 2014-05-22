<?php

class ApiModel extends Model {

    public function getAppKey($taobaoItemId, $oldAppKey = null) {
        $times = 0;
        if (session('?try_api_times')) {
            $times = session('try_api_times');
        }
        $times++;
        if ($times > C('max_try_api_times')) {
            session('try_api_times', null);
            U('Taobao/Index/showError', array('msg' => urlencode('抱歉，尝试获取接口失败，请稍后再试!'),
                                       'url' => urlencode(U('Taobao/Index/signOut'))), true, true, false);
        }
        session('try_api_times', $times);

        return array('appkey' => C('stable_taobao_app_key'),
                     'appsecret' => C('stable_taobao_secret_key'),
                     'id' => '');
    }

    public function appKeyFail($id) {
        $where['id'] = $id;
        $this->where($where)->setInc('overflow');
    }

    public function appKeySuccess($id) {
        $where['id'] = $id;
        $this->where($where)->setField('overflow', '0');
    }

    protected function getFirstId($taobaoItemId) {
        $len = strlen($taobaoItemId);
        $id = substr($taobaoItemId, $len-2, $len);
        $id = $id % C('taoapi_count') + 1;
        return $id;
    }

}

?>