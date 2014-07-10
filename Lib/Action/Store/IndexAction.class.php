<?php

class IndexAction extends Action {
    public function index() {
        $this->display();
    }

    public function findNew() {
        $storeVvic = new Model();
        $rs = $storeVvic->query('select * from ecm_store_vvic where im_ww not in (select im_ww from ecm_store)');
        $this->ajaxReturn($rs);
    }
}