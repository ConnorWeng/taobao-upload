<?php
import('@.Util.OpenAPI');
import('@.Util.Util');

class ApiAction extends CommonAction {
    public function makePropsHtml() {
        $cid = I('cid');
        $goodsId = I('goodsId');
        $upload = new UploadAction();
        $props = OpenAPI::getTaobaoItemPropsWithoutVerify($cid);
        $propsName = '';
        if ($goodsId != '') {
            Util::changeDatabase(I('db'));
            $taobaoItem = OpenAPI::getTaobaoItemFromDatabase($goodsId);
            $propsName = $taobaoItem->props_name;
        }
        $html = $upload->makePropsHtml($props, $propsName, $cid, false);
        $this->ajaxReturn($html);
    }

    public function getTaobaoItem() {
        Util::changeDatabase(I('db'));
        $goodsId = I('goodsId');
        $taobaoItem = OpenAPI::getTaobaoItemFromDatabase($goodsId);
        $upload = new UploadAction();
        $storeInfo = $upload->getStoreInfo($taobaoItem);
        $outerId = $upload->makeOuterId($taobaoItem->title, $taobaoItem->price, $storeInfo);
        $taobaoItem->setOuterId($outerId);
        $this->ajaxReturn($taobaoItem);
    }
}