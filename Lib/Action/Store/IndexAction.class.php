<?php

class IndexAction extends Action {
    public function index() {
        $this->display();
    }

    public function findDiff() {
        $store17zwd = new Model();
        $rs = $store17zwd->query("select store_id,shop_mall,floor,address,store_name,see_price,im_qq,im_ww,tel,shop_http from ecm_store_17zwd v where not exists (select * from ecm_store s where s.im_ww = v.im_ww) limit 1000");
        $this->ajaxReturn($rs);
    }

    public function findUpdate() {
        $store17zwd = new Model();
        $rs = $store17zwd->query("select distinct a.store_id,a.shop_mall,a.floor,a.address,a.store_name,a.see_price,a.im_qq,a.im_ww,a.tel,a.shop_http from ecm_store_17zwd a, ecm_store b where a.im_ww = b.im_ww and (a.shop_mall != b.shop_mall or a.floor != b.floor or a.address != b.address) limit 1000");
        $this->ajaxReturn($rs);
    }

    public function findUnused() {
        $store17zwd = new Model();
        $rs = $store17zwd->query("select store_id, shop_mall, floor, address, store_name, see_price, im_qq, im_ww, tel, shop_http from ecm_store s where not exists (select 1 from ecm_store_17zwd v where s.im_ww = v.im_ww) and (datapack is null or datapack != 'keep') limit 1000");
        $this->ajaxReturn($rs);
    }

    public function queryStore() {
        $storeId = $_REQUEST['store_id'];
        $store17zwd = new Model();
        $rs = $store17zwd->query("select s.* from ecm_store s inner join ecm_store_17zwd v on s.im_ww = v.im_ww and v.store_id = {$storeId}");
        $this->ajaxReturn($rs[0]);
    }

    public function addStore() {
        $storeId = $_REQUEST['store_id'];
        $store17zwd = new Model();
        $rs = $store17zwd->query("select * from ecm_store_17zwd where store_id = {$storeId}");
        $seePrice = $rs[0]['see_price'];
        $shopMall = $rs[0]['shop_mall'];
        $address = $rs[0]['address'];
        $imWw = $rs[0]['im_ww'];
        $storeName = $rs[0]['store_name'];
        $tel = $rs[0]['tel'];
        $imQq = $rs[0]['im_qq'];
        $shopHttp = $rs[0]['shop_http'];
        $floor = $rs[0]['floor'];
        $rs2 = $store17zwd->execute("call register_store('{$imQq}', '{$shopMall}', '{$shopMall}', '{$floor}', '{$address}', '{$address}', '{$storeName}', '{$seePrice}', '{$imWw}', '{$shopHttp}')");

        $this->ajaxReturn($rs2);
    }

    public function updateStore() {
        $storeId = $_REQUEST['store_id'];
        $store17zwd = new Model();
        $rs = $store17zwd->query("select * from ecm_store_17zwd where store_id = {$storeId}");
        $seePrice = $rs[0]['see_price'];
        $shopMall = $rs[0]['shop_mall'];
        $address = $rs[0]['address'];
        $imWw = $rs[0]['im_ww'];
        $storeName = $rs[0]['store_name'];
        $tel = $rs[0]['tel'];
        $imQq = $rs[0]['im_qq'];
        $shopHttp = $rs[0]['shop_http'];
        $floor = $rs[0]['floor'];
        $rs2 = $store17zwd->execute("update ecm_store s set s.see_price = '{$seePrice}', s.shop_mall = '{$shopMall}', s.store_name = '{$storeName}', s.tel = '{$tel}', s.im_qq = '{$imQq}', s.shop_http = '{$shopHttp}', s.floor = '{$floor}', s.address = '{$address}', s.dangkou_address = '{$address}', s.state = 1 where s.im_ww = '{$imWw}'");
        $nextStoreId = intval($storeId) + 1;
        $store17zwd->execute("call build_outer_iid({$storeId}, {$nextStoreId})");
        $this->ajaxReturn($rs2);
    }

    public function deleteStore() {
        $storeId = $_REQUEST['store_id'];
        $storeModel = new Model();
        $rs = $storeModel->execute("update ecm_store set state = 2, close_reason = 'according to 17zwd' where store_id = {$storeId}");
        $this->ajaxReturn($rs);
    }

    public function keepStore() {
        $storeId = $_REQUEST['store_id'];
        $storeModel = new Model();
        $rs = $storeModel->execute("update ecm_store set datapack = 'keep' where store_id = {$storeId}");
        $this->ajaxReturn($rs);
    }
}