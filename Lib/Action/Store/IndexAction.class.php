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

    public function findDiff() {
        $storeVvic = new Model();
        $rs = $storeVvic->query("select v.* from ecm_store_vvic v, ecm_store s where v.im_ww = s.im_ww and (v.see_price != s.see_price or v.shop_mall != s.shop_mall or v.address != s.address)");
        $this->ajaxReturn($rs);
    }

    public function updateStore() {
        $storeId = $_REQUEST['store_id'];
        $storeVvic = new Model();
        $rs = $storeVvic->execute("update ecm_store s inner join ecm_store_vvic v on s.im_ww = v.im_ww and v.store_id = {$storeId} set s.see_price = v.see_price, s.shop_mall = v.shop_mall, s.address = v.address");
        $this->ajaxReturn($rs);
    }

    public function findUnused() {
        $storeVvic = new Model();
        $rs = $storeVvic->query("select * from ecm_store where im_ww not in (select im_ww from ecm_store_vvic)");
        $this->ajaxReturn($rs);
    }

    public function deleteStore() {
        $storeId = $_REQUEST['store_id'];
        $storeVvic = new Model();
        $rs = $storeVvic->execute("delete from ecm_store where store_id = {$storeId}");
        $this->ajaxReturn($rs);
    }
}