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

    public function addStore() {
        $storeId = $_REQUEST['store_id'];
        $storeVvic = new Model();
        $rs = $storeVvic->execute("insert into ecm_store (shop_mall,floor,address,store_name,see_price,im_qq,im_ww,tel,shop_http,has_link,serv_refund,serv_exchgoods,serv_sendgoods,serv_deltpic,serv_modpic,serv_golden,qqun_name,csv_http,shop_range,cate_content,weixincode) select shop_mall,floor,address,store_name,see_price,im_qq,im_ww,tel,shop_http,has_link,serv_refund,serv_exchgoods,serv_sendgoods,serv_deltpic,serv_modpic,serv_golden,'','','','','' from ecm_store_vvic where store_id = ".$storeId);
        $this->ajaxReturn($rs);
    }
}