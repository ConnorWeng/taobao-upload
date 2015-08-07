<?php
import('@.Util.OpenAPI');
import('@.Util.Util');

class ApiAction extends CommonAction {
    public function getTradesSold() {
        $trades = OpenAPI::getTradesSold(I('sessionKey'));
        $this->ajaxReturn($trades);
    }

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
        $storeInfo['see_price'] = '实价';
        $outerId = $upload->makeOuterId($taobaoItem->title, $taobaoItem->price, $storeInfo);
        $taobaoItem->setOuterId($outerId);
        $taobaoItem->setPropsName($this->propsNameWithoutNameAndValue($taobaoItem->props_name));
        $taobaoItem->setNumIid(Util::getNumIidFromUrl($taobaoItem->good_http));
        $this->ajaxReturn($taobaoItem);
    }

    public function permitTaobaoTmc() {
        $sessionKey = I('session_key');
        $isSuccess = OpenAPI::permitTaobaoTmc($sessionKey);
        $this->ajaxReturn($isSuccess);
    }

    public function getTaobaoLogisticsCompanies() {
        $this->ajaxReturn(OpenAPI::getTaobaoLogisticsCompanies());
    }

    public function sendTaobaoLogisticsOnline() {
        $shipping = OpenAPI::sendTaobaoLogisticsOnline(I('tid'), I('out_sid'), I('company_code'), I('app_key'), I('secret_key'), I('session_key'));
        $this->ajaxReturn($shipping);
    }

    private function propsNameWithoutNameAndValue($propsName) {
        $new = '';
        $propsNameAttr = explode(';', $propsName);
        foreach ($propsNameAttr as $propsNameStr) {
            $parts = explode(':', $propsNameStr);
            if ($parts[0] && $parts[1]) {
                $new .= $parts[0].':'.$parts[1].';';
            }
        }
        return $new;
    }
}