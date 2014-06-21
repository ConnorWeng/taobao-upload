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

        if ($oldAppKey == null) {
            $sql = 'SELECT * from '.C('DB_PREFIX').$this->tableName.' where overflow = 0 ORDER BY RAND() LIMIT 1';
            $rs = $this->query($sql);
            return array('appkey' => $rs[0]['appkey'],
                         'appsecret' => $rs[0]['appscret'],
                         'id' => $rs[0]['id']);
        } else {
            return array('appkey' => C('stable_taobao_app_key'),
                         'appsecret' => C('stable_taobao_secret_key'),
                         'id' => '');
        }
    }

    public function appKeyFail($id) {
        $recoveryTime = strval(time() + 25 * 60 * 60);
        $sql = "update ecm_taoapi_self set `overflow` = 97, recovery_time = {$recoveryTime} where id = {$id}";
        $this->db->query($sql);
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