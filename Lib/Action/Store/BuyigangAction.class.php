<?php

import('@.Util.Util');

class BuyigangAction extends Action {
    public function getAllStoreIds() {
        Util::checkIP();
        $shopMall = I('shopMall');
        $storeModel = M('store');
        $where['shop_mall'] = $shopMall;
        $this->ajaxReturn($storeModel->where($where)->field('store_id')->select());
    }

    public function getStore() {
        Util::checkIP();
        $storeId = I('storeId');
        $storeModel = M('store');
        $where['store_id'] = $storeId;
        $this->ajaxReturn($storeModel->where($where)->find());
    }

    public function getAllGoodIds() {
        Util::checkIP();
        $storeId = I('storeId');
        $goodsModel = M('goods');
        $where['store_id'] = $storeId;
        $this->ajaxReturn($goodsModel->where($where)->field('goods_id')->select());
    }

    public function getGood() {
        Util::checkIP();
        $goodsId = I('goodsId');
        $goodsModel = M('goods');
        $where['goods_id'] = $goodsId;
        $this->ajaxReturn($goodsModel->where($where)->find());
    }
}