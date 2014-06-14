<?php

class TaoapiModel extends ApiModel {
    protected $tableName = "taoapi_self";

    public function recoveryAppKey($now) {
        $sql = "update ecm_taoapi_self set overflow = 0, recovery_time = 0 where recovery_time != 0 and recovery_time < {$now}";
        return $this->db->query($sql);
    }
}

?>