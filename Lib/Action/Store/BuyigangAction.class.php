<?php

class BuyigangAction extends Action {
    public function getAllStoreIds() {
        $shopMall = I('shopMall');
        $storeModel = M('store');
        $where['shop_mall'] = $shopMall;
        $this->ajaxReturn($storeModel->where($where)->field('store_id')->select());
    }

    public function getStore() {
        $storeId = I('storeId');
        $storeModel = M('store');
        $where['store_id'] = $storeId;
        $this->ajaxReturn($storeModel->where($where)->find());
    }

    public function getAllGoodIds() {
        $storeId = I('storeId');
        $goodsModel = M('goods');
        $where['store_id'] = $storeId;
        $this->ajaxReturn($goodsModel->where($where)->field('goods_id')->select());
    }

    public function getGood() {
        $goodsId = I('goodsId');
        $goodsModel = M('goods');
        $where['goods_id'] = $goodsId;
        $this->ajaxReturn($goodsModel->where($where)->find());
    }
}