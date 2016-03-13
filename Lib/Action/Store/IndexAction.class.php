<?php

class IndexAction extends Action {
    public function index() {
        $this->display();
    }

    public function findDiff() {
        $store17zwd = new Model();
        $rs = $store17zwd->query("select store_id,shop_mall,floor,address,store_name,see_price,im_qq,im_ww,tel,shop_http from ecm_store_17zwd v where not exists (select * from ecm_store s where s.im_ww = v.im_ww)");
        $this->ajaxReturn($rs);
    }

    public function findUpdate() {
        // $store17zwd = new Model();
        // $rs = $store17zwd->query("select a.store_id,a.shop_mall,a.floor,a.address,a.store_name,a.see_price,a.im_qq,a.im_ww,a.tel,a.shop_http from (select store_id,shop_mall,floor,address,store_name,see_price,im_qq,im_ww,tel,shop_http from ecm_store_17zwd v where not exists (select * from ecm_store s where s.store_name = v.store_name and s.address = v.address and s.im_ww = v.im_ww and s.see_price = v.see_price and s.shop_mall = v.shop_mall) and v.address != '') a inner join (select store_id,shop_mall,floor,address,store_name,see_price,im_qq,im_ww,tel,shop_http from ecm_store_17zwd v where exists (select * from ecm_store s where s.address = v.address and s.im_ww = v.im_ww)) b on a.store_id = b.store_id");
        $rs = array();
        $this->ajaxReturn($rs);
    }

    public function findUnused() {
        $store17zwd = new Model();
        $rs = $store17zwd->query("select store_id, shop_mall, floor, address, store_name, see_price, im_qq, im_ww, tel, shop_http from ecm_store s where state = 1 and not exists (select 1 from ecm_store_17zwd v where s.im_ww = v.im_ww)");
        $this->ajaxReturn($rs);
    }

    public function queryStore() {
        $storeId = $_REQUEST['store_id'];
        $store17zwd = new Model();
        $rs = $store17zwd->query("select s.* from ecm_store s inner join ecm_store_17zwd v on s.im_ww = v.im_ww and s.address = v.address and v.store_id = {$storeId}");
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
        $rs2 = $store17zwd->execute("update ecm_store s set s.see_price = '{$seePrice}', s.shop_mall = '{$shopMall}', s.store_name = '{$storeName}', s.tel = '{$tel}', s.im_qq = '{$imQq}', s.shop_http = '{$shopHttp}', s.floor = '{$floor}', s.state = 1 where s.im_ww = '{$imWw}' and s.address = '{$address}'");
        $this->ajaxReturn($rs2);
    }

    public function deleteStore() {
        $storeId = $_REQUEST['store_id'];
        $store17zwd = new Model();
        $rs = $store17zwd->execute("update ecm_store set state = 9 where store_id = {$storeId}");
        $this->ajaxReturn($rs);
    }
}