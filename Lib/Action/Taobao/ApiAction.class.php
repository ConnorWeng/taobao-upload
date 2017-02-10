<?php
import('@.Util.OpenAPI');
import('@.Util.Util');

class ApiAction extends CommonAction {
    public function getTradesSold() {
        Util::changeDatabase(I('db'));
        if (get_client_ip() === '112.124.54.224') {
            $pageNo = intval(I('page_no'));
            $userId = I('user_id');
            $memberAuth = M('MemberAuth');
            $authInfo = $memberAuth->find('user_id=' + $userId);
            if ($authInfo['access_token']) {
                $trades = OpenAPI::getTradesSold($authInfo['access_token'], $pageNo);
                $this->ajaxReturn($trades);
            } else {
                $this->ajaxReturn('{"code": "5101", "msg": "no access token"}');
            }
        } else {
            $this->ajaxReturn('{"code": "5102", "msg": "attack detected!"}');
        }
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

    public function cancelTaobaoTmc() {
        $nick = urldecode(I('nick'));
        $resp = OpenAPI::cancelTaobaoTmc($nick);
        $this->ajaxReturn($resp);
    }

    public function getTaobaoLogisticsCompanies() {
        $this->ajaxReturn(OpenAPI::getTaobaoLogisticsCompanies());
    }

    public function sendTaobaoLogisticsOnline() {
        $shipping = OpenAPI::sendTaobaoLogisticsOnline(I('tid'), I('out_sid'), I('company_code'), I('app_key'), I('secret_key'), I('session_key'));
        $this->ajaxReturn($shipping);
    }

    public function getItemIncrementUpdateSchema() {
        Util::changeDatabase(I('db'));
        $userId = I('user_id');
        $memberAuth = M('MemberAuth');
        $authInfo = $memberAuth->find('user_id=' + $userId);
        if ($authInfo['access_token']) {
            $updateItemSchema = OpenAPI::getItemIncrementUpdateSchema(I('item_id'), $authInfo['access_token']);
            $this->ajaxReturn($updateItemSchema);
        } else {
            $this->ajaxReturn('{}');
        }
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

    public function getItemInfo() {
        Util::changeDatabase('mall');
        $goodsId = I('goodsId');
        $taobaoItem = OpenAPI::getTaobaoItemFromDatabase($goodsId);
        if ($taobaoItem->title === 'null') {
            $data['error'] = 1;
            $data['msg'] = '';
            return $this->ajaxReturn($data);
        }

        $upload = new UploadAction();
        $storeInfo = $upload->getStoreInfo($taobaoItem);
        $outerId = $upload->makeOuterId($taobaoItem->title, $taobaoItem->price, $storeInfo);
        $taobaoItem->setOuterId($outerId);
        $taobaoItem->setPropsName($this->propsNameWithoutNameAndValue($taobaoItem->props_name));
        $taobaoItem->setNumIid(Util::getNumIidFromUrl($taobaoItem->good_http));

        $itemImages = array();
        for ($i = 0; $i < count($taobaoItem->item_imgs->item_img); $i++) {
            $itemImages[] = array(
                'id' => $i,
                'position' => $i,
                'url' => $taobaoItem->item_imgs->item_img[$i]->url);
        }

        $propsName = array();
        $attrNames = explode(',', $taobaoItem->detail['attr_names']);
        $attrValues = explode('*|*', $taobaoItem->detail['attr_values']);
        for ($i = 0; $i < count($attrNames); $i++) {
            $propsName[] = array(
                'key' => $attrNames[$i],
                'value' => $attrValues[$i]);
        }

        $shop = array(
            'name' => $storeInfo['store_name'],
            'mobile' => $storeInfo['tel'],
            'weixin' => $storeInfo['im_wx'],
            'wangwang' => $storeInfo['im_ww'],
            'qq' => $storeInfo['im_qq'],
            'address' => $storeInfo['mk_name'].'-'.$storeInfo['address'],
            'category' => $storeInfo['business_scope']);

        $data['success'] = 1;
        $data['data'] = array(
            'goodsId' => $goodsId,
            'title' => $taobaoItem->title,
            'price' => $taobaoItem->price,
            'tbPrice' => number_format($this->make_price($taobaoItem->price, $storeInfo['see_price'], $taobaoItem->title), 2, '.', ''),
            'tbUrl' => 'http://item.taobao.com/item.htm?id='.$taobaoItem->num_iid,
            'num_iid' => $taobaoItem->num_iid,
            'market_id' => '',
            'category_id' => $taobaoItem->cid,
            'teams' => array(),
            'goodsStatus' => 0,
            'desc' => $taobaoItem->desc,
            'itemImages' => $itemImages,
            'prices' => $this->make_prices($taobaoItem->skus->sku),
            'shop' => $shop,
            'propsName' => $propsName,
            'goodsAuth' => 0,
            'numIid' => $taobaoItem->num_iid);

        $this->ajaxReturn($data);
    }

    /*根据现有价格计算出淘宝价*/
    function make_price($price, $seePrice, $title = null) {
        $finalPrice = $rawPrice = floatval ( $price );
        if (strpos ( $seePrice, '减半' ) !== false)
        {
            $finalPrice = $rawPrice * 2;
        }
        else if (strpos ( $seePrice, 'P' ) !== false || $seePrice == '减P' || $seePrice == '减p')
        {
            $regexP = '/[Pp](\d+)/';
            $regexF = '/[Ff](\d+)/';
            if (preg_match ( $regexP, $title, $matches ) == 1)
            {
                $finalPrice = floatval ( $matches [1] );
            }
            else if (preg_match ( $regexF, $title, $matches ) == 1)
            {
                $finalPrice = floatval ( $matches [1] );
            }
        }
        else if (strpos ( $seePrice, '减' ) === 0)
        {
            $finalPrice = $rawPrice + floatval ( mb_substr ( $seePrice, 1, mb_strlen ( $seePrice, 'utf-8' ) - 1, 'utf-8' ) );
        }
        else if (strpos ( $seePrice, '实价' ) !== false)
        {
            $finalPrice = $rawPrice;
        }
        else if (strpos ( $seePrice, '*' ) === 0)
        {
            $finalPrice = $rawPrice / floatval ( mb_substr ( $seePrice, 1, mb_strlen ( $seePrice, 'utf-8' ) - 1, 'utf-8' ) );
        }
        else if (strpos ( $seePrice, '打' ) === 0)
        {
            $finalPrice = $rawPrice / (floatval ( mb_substr ( $seePrice, 1, mb_strlen ( $seePrice, 'utf-8' ) - 1, 'utf-8' ) ) / 10);
        }
        else if (strpos ( $seePrice, '折' ) === mb_strlen ( $seePrice, 'utf-8' ) - 1)
        {
            $finalPrice = $rawPrice / (floatval ( mb_substr ( $seePrice, 0, mb_strlen ( $seePrice, 'utf-8' ) - 1, 'utf-8' ) ) / 10);
        }
        if (is_numeric ( $finalPrice ))
        {
            return $finalPrice;
        }
        else
        {
            return $price;
        }
    }

    private function make_prices($skus) {
        $array = array();
        foreach($skus as $sku) {
            $new = array();
            $new['price'] = $sku->price;
            $size = $this->get_size($sku->properties_name);
            if ($size) {
                $new['size'] = $size;
            }
            $color = $this->get_color($sku->properties_name);
            if ($color) {
                $new['color'] = $color;
            }
            array_push($array, $new);
        }
        return $array;
    }

    private function get_color($properties_name) {
        return $this->get_value_in_properties_name($properties_name, '颜');
    }

    private function get_size($properties_name) {
        return $this->get_value_in_properties_name($properties_name, '尺');
    }

    private function get_value_in_properties_name($properties_name, $key) {
        if (strpos($properties_name, $key) !== false) {
            $parts = explode(';', $properties_name);
            for ($i = 0; $i < count($parts); $i++) {
                if (strpos($parts[$i], $key) !== false) {
                    return explode(':', $parts[$i])[3];
                }
            }
        }
        return false;
    }
}