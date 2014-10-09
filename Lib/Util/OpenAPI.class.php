<?php

import('@.Util.Util');
import('@.Model.StoreSession');
vendor('taobao-sdk.TopSdk');
import('@.Model.TaobaoItem');
import('@.Model.Skus');
import('@.Model.Sku');

class OpenAPI {

    private static function sendRequestWithShortUrl($url, $params) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    public static function getTaobaoItemFromDatabase($goodsId) {
        $goodsModel = M('goods');
        $rs = $goodsModel->query("select t.*, group_concat(a.attr_id separator ',') attr_ids, group_concat(a.value_id separator ',') value_ids, group_concat(a.attr_name separator ',') attr_names, group_concat(a.attr_value separator ',') attr_values from (select g.*, group_concat(s.spec_1 separator ',') spec_1s, group_concat(s.spec_2 separator ',') spec_2s, group_concat(s.price separator ',') prices, group_concat(s.stock separator ',') stocks from ecm_goods g, ecm_goods_spec s where g.goods_id = {$goodsId} and g.goods_id = s.goods_id group by goods_id) t, ecm_goods_attr a where t.goods_id = a.goods_id group by t.goods_id;");
        $taobaoItem = new TaobaoItem;
        if (count($rs) > 0) {
            $result = $rs[0];
            $taobaoItem->setCid(self::getCategoryId($result));
            $taobaoItem->setItemImgs(array(new ItemImg(self::parseDefaultImage($result['default_image']))));
            $taobaoItem->setSkus(self::parseSkus($result));
            $taobaoItem->setPropsName(self::parsePropsName($result));
            $taobaoItem->setTitle($result['goods_name']);
            $taobaoItem->setPicUrl(self::parseDefaultImage($result['default_image']));
            $taobaoItem->setNick(session('taobao_user_nick'));
            $taobaoItem->setPrice($result['price']);
            $taobaoItem->setNum($result['num']);
            $taobaoItem->setPropImgs(array());
            $taobaoItem->setDesc($result['description']);
            $taobaoItem->setDelistTime('2099-12-10 00:00:00');
        }
        return $taobaoItem;
    }

    private static function getCategoryId($good) {
        $categoryId = '0';
        for ($i = 1; $i <= 4; $i++) {
            if ($good['cate_id_'.$i] == '0') {
                break;
            } else {
                $categoryId = $good['cate_id_'.$i];
            }
        }
        return $categoryId;
    }

    private static function parseDefaultImage($image) {
        if (strpos('data/files') !== false) {
            return 'http://ecmall.51zwd.com/'.$image;
        } else {
            return $image;
        }
    }

    private static function parsePropsName($good) {
        $propsName = '';
        $attrIds = split(',', $good['attr_ids']);
        if (count($attrIds) > 0) {
            $valueIds = split(',', $good['value_ids']);
            $attrNames = split(',', $good['attr_names']);
            $attrValues = split(',', $good['attr_values']);
            for ($i = 0; $i < count($attrIds); $i++) {
                $propsName .= $attrIds[$i].':'.$valueIds[$i].':'.$attrNames[$i].':'.$attrValues[$i].';';
            }
        }
        return $propsName;
    }

    private static function parseSkus($good) {
        $skus = new Skus;
        $spec1s = split(',', $good['spec_1s']);
        if (count($spec1s) > 0) {
            $spec2s = split(',', $good['spec_2s']);
            $prices = split(',', $good['prices']);
            $stocks = split(',', $good['stocks']);
            for ($i = 0; $i < count($spec1s); $i++) {
                array_push($skus->sku, new Sku($spec1s[$i].':'.$spec2s[$i], $prices[$i], $stocks[$i]));
            }
        }
        return $skus;
    }

    public static function getTaobaoItem($numIid) {
        if (self::needVerify()) {
            return 'verify';
        }
        if (!session('?taobao_access_token')) {
            return 'timeout';
        }
        $c = new TopClient;
        $c->appkey = C('taobao_app_key');
        $c->secretKey = C('taobao_secret_key');
        $req = new ItemGetRequest;
        $req->setFields("title,desc,pic_url,sku,item_weight,property_alias,price,item_img.url,cid,nick,props_name,prop_img,delist_time");
        $req->setNumIid($numIid);
        $resp = $c->execute($req, null);

        if (isset($resp->item)) {
            return $resp->item;
        } else {
            self::dumpTaobaoApiError('getTaobaoItem', $resp);
        }
    }

    public static function getTaobaoItemWithoutVerify($numIid) {
        $c = new TopClient;
        $c->appkey = C('taobao_app_key');
        $c->secretKey = C('taobao_secret_key');
        $req = new ItemGetRequest;
        $req->setFields("title,desc,pic_url,sku,item_weight,property_alias,price,item_img.url,cid,nick,props_name,prop_img,delist_time,props");
        $req->setNumIid($numIid);
        $resp = $c->execute($req, null);

        if (isset($resp->item)) {
            return $resp->item;
        } else {
            self::dumpTaobaoApiError('getTaobaoItemWithoutVerify', $resp);
        }
    }

    public static function getTaobaoItemCat($cid) {
        if (self::needVerify()) {
            return 'verify';
        }
        if (!session('?taobao_access_token')) {
            return 'timeout';
        }
        $c = new TopClient;
        $c->appkey = C('taobao_app_key');
        $c->secretKey = C('taobao_secret_key');
        $req = new ItemcatsGetRequest;
        $req->setFields("name");
        $req->setCids($cid);
        $resp = $c->execute($req, null);

        if (isset($resp->item_cats->item_cat)) {
            return Util::extractValue($resp->item_cats->item_cat->name->asXML());
        } else {
            self::dumpTaobaoApiError('getTaobaoItemCat', $resp);
        }
    }

    public static function getTaobaoItemCatWithoutVerify($cid) {
        $c = new TopClient;
        $c->appkey = C('taobao_app_key');
        $c->secretKey = C('taobao_secret_key');
        $req = new ItemcatsGetRequest;
        $req->setFields("name");
        $req->setCids($cid);
        $resp = $c->execute($req, null);

        if (isset($resp->item_cats->item_cat)) {
            return Util::extractValue($resp->item_cats->item_cat->name->asXML());
        } else {
            self::dumpTaobaoApiError('getTaobaoItemCatWithoutVerify', $resp);
        }
    }

    public static function getTaobaoItemProps($cid, $parentPid = null) {
        if (self::needVerify()) {
            return 'verify';
        }
        if (!session('?taobao_access_token')) {
            return 'timeout';
        }
        $c = new TopClient;
        $c->appkey = C('taobao_app_key');
        $c->secretKey = C('taobao_secret_key');
        $req = new ItempropsGetRequest;
        $req->setFields("pid,name,must,multi,prop_values,is_key_prop,is_sale_prop,parent_vid");
        $req->setCid($cid);
        if ($parentPid) {
            $req->setParentPid($parentPid);
        }
        $resp = $c->execute($req, null);

        if (isset($resp->item_props)) {
            return $resp->item_props;
        } else {
            self::dumpTaobaoApiError('getTaobaoItemProps', $resp);
        }
    }

    public static function getTaobaoItemPropsWithoutVerify($cid, $parentPid = null) {
        $c = new TopClient;
        $c->appkey = C('taobao_app_key');
        $c->secretKey = C('taobao_secret_key');
        $req = new ItempropsGetRequest;
        $req->setFields("pid,name,must,multi,prop_values,is_key_prop,is_sale_prop,parent_vid");
        $req->setCid($cid);
        if ($parentPid) {
            $req->setParentPid($parentPid);
        }
        $resp = $c->execute($req, null);

        if (isset($resp->item_props)) {
            return $resp->item_props;
        } else {
            self::dumpTaobaoApiError('getTaobaoItemProps', $resp);
        }
    }

    public static function addTaobaoItem($item, $sessionKey = null, $isRepeat = false) {
        if (self::needVerify()) {
            return 'verify';
        }
        if (!session('?taobao_access_token')) {
            return 'timeout';
        }
        if ($sessionKey == null) {
            $sessionKey = session('taobao_access_token');
        }
        $c = self::initTopClient();
        $req = new ItemAddRequest;
        foreach ($item as $key => $value) {
            if ($value !== '') {
                call_user_method('set'.$key, $req, $value);
            }
        }
        $resp = $c->execute($req, $sessionKey);
        if (isset($resp->item)) {
            $taoapi = D('Taoapi');
            $taoapi->appKeySuccess(session('current_taobao_app_key_id'));
            return $resp->item;
        } else if ($resp->code == '7' && !$isRepeat && session('taobao_app_key') == C('stable_taobao_app_key')) {
            sleep(3);
            return $this->addTaobaoItem($item, $sessionKey, true);
        } else {
            self::dumpTaobaoApiError('addTaobaoItem', $resp);
        }
    }

    public static function addTaobaoItemWithoutVerify($item, $sessionKey) {
        $c = new TopClient;
        $c->appkey = C('stable_taobao_app_key');
        $c->secretKey = C('stable_taobao_secret_key');
        $req = new ItemAddRequest;
        foreach ($item as $key => $value) {
            if ($value !== '') {
                call_user_method('set'.$key, $req, $value);
            }
        }
        Log::write('accessToken:'.$sessionKey, Log::ERR);
        $resp = $c->execute($req, $sessionKey);
        if (isset($resp->item)) {
            return $resp->item;
        } else {
            self::dumpTaobaoApiError('addTaobaoItemWithoutVerify', $resp);
        }
    }

    public static function addTaobaoItemWithMovePic($item, $sessionKey = null, $isRepeat = false) {
        if (self::needVerify()) {
            return 'verify';
        }
        if (!session('?taobao_access_token')) {
            return 'timeout';
        }
        if ($sessionKey == null) {
            $sessionKey = session('taobao_access_token');
        }
        $currentTaobaoItemId = session('current_taobao_item_id') == '' ? '0' : session('current_taobao_item_id');
        $params = "WebType=51wp_yjsc_dist".
                  "&appKey=".session('taobao_app_key').
                  "&appSecret=".session('taobao_secret_key').
                  "&numIID=".$currentTaobaoItemId.
                  "&sessionID=".$sessionKey.
                  "&Num=".urlencode($item['Num']).
                  "&Price=".urlencode($item['Price']).
                  "&Type=".urlencode($item['Type']).
                  "&StuffStatus=".urlencode($item['StuffStatus']).
                  "&Title=".urlencode($item['Title']).
                  "&Desc=".urlencode($item['Desc']).
                  "&LocationState=".urlencode($item['LocationState']).
                  "&LocationCity=".urlencode($item['LocationCity']).
                  "&Cid=".urlencode($item['Cid']).
                  "&ApproveStatus=".urlencode($item['ApproveStatus']).
                  "&Props=".urlencode($item['Props']).
                  "&FreightPayer=".urlencode($item['FreightPayer']).
                  "&ValidThru=".urlencode($item['ValidThru']).
                  "&HasInvoice=true&HasWarranty=true&HasShowcase=".urlencode($item['HasShowcase']).
                  "&SellerCids=".urlencode($item['SellerCids']).
                  "&HasDiscount=".urlencode($item['HasDiscount']).
                  "&PostFee=".urlencode($item['PostFee']).
                  "&ExpressFee=".urlencode($item['ExpressFee']).
                  "&EmsFee=".urlencode($item['EmsFee']).
                  "&ListTime=".urlencode($item['ListTime']).
                  "&PostageId=".urlencode($item['PostageId']).
                  "&PropertyAlias=".urlencode($item['PropertyAlias']).
                  "&InputStr=".urlencode($item['InputStr']).
                  "&InputPids=".urlencode($item['InputPids']).
                  "&SkuProperties=".urlencode($item['SkuProperties']).
                  "&SkuQuantities=".urlencode($item['SkuQuantities']).
                  "&SkuPrices=".urlencode($item['SkuPrices']).
                  "&SkuOuterIds=".urlencode($item['SkuOuterIds']).
                  "&Outer_id=".urlencode($item['OuterId']).
                  "&mainpic=".urlencode($item['mainpic']).
                  "&checklic=".md5($currentTaobaoItemId.'51');
        $resp = self::sendRequestWithShortUrl(C('servletUri'), $params);
        if (is_numeric($resp)) {
            $taoapi = D('Taoapi');
            $taoapi->appKeySuccess(session('current_taobao_app_key_id'));
            return $resp;
        } else if (strpos($resp, 'limited-by-api-access-count') !== false && !$isRepeat && session('taobao_app_key') == C('stable_taobao_app_key')) {
            sleep(3);
            return $this->addTaobaoItemWithMovePic($item, $sessionKey, true);
        } else {
            self::dumpTaobaoApiError('addTaobaoItemWithMovePic', $resp);
            if (strpos($resp, 'ban') !== false || strpos($resp, 'limited-by-api-access-count') !== false || strpos($resp, 'belong app and user') !== false) {
                self::authWithNewAppKey();
            }
        }
    }

    public static function getTaobaoUserBuyer() {
        if (self::needVerify()) {
            return 'verify';
        }
        if (!session('?taobao_access_token')) {
            return 'timeout';
        }
        $c = self::initTopClient();
        $req = new UserBuyerGetRequest;
        $req->setFields("nick");
        $resp = $c->execute($req, session('taobao_access_token'));
        if (isset($resp->user)) {
            $taoapi = D('Taoapi');
            $taoapi->appKeySuccess(session('current_taobao_app_key_id'));
            return $resp->user;
        } else {
            self::dumpTaobaoApiError('getTaobaoUserBuyer', $resp);
        }
    }

    public static function uploadTaobaoItemImg($numIid, $image, $position, $sessionKey = null) {
        if (self::needVerify()) {
            return 'verify';
        }
        if (!session('?taobao_access_token')) {
            return 'timeout';
        }
        if ($sessionKey == null) {
            $sessionKey = session('taobao_access_token');
        }
        $c = self::initTopClient();
        $req = new ItemImgUploadRequest;
        $req->setNumIid($numIid);
        $req->setImage('@'.$image);
        $req->setPosition($position);
        $resp = $c->execute($req, $sessionKey);
        if (isset($resp->item_img)) {
            $taoapi = D('Taoapi');
            $taoapi->appKeySuccess(session('current_taobao_app_key_id'));
            return $resp->item_img;
        } else {
            self::dumpTaobaoApiError('uploadTaobaoItemImg', $resp);
        }
    }

    public static function uploadTaobaoItemPropImg($numIid, $prop, $image, $position, $sessionKey = null) {
        if (self::needVerify()) {
            return 'verify';
        }
        if (!session('?taobao_access_token')) {
            return 'timeout';
        }
        if ($sessionKey == null) {
            $sessionKey = session('taobao_access_token');
        }
        $c = self::initTopClient();
        $req = new ItemPropimgUploadRequest;
        $req->setNumIid($numIid);
        $req->setProperties($prop);
        $req->setImage($image);
        $req->setPosition($position);
        $resp = $c->execute($req, $sessionKey);
        if ($resp->prop_img) {
            $taoapi = D('Taoapi');
            $taoapi->appKeySuccess(session('current_taobao_app_key_id'));
            return $resp->prop_img;
        } else {
            self::dumpTaobaoApiError('uploadTaobaoItemPropImg', $resp);
        }
    }

    public static function getTaobaoCustomItems($outerId) {
        if (self::needVerify()) {
            return 'verify';
        }
        if (!session('?taobao_access_token')) {
            return 'timeout';
        }
        $c = self::initTopClient();
        $req = new ItemsCustomGetRequest;
        $req->setOuterId($outerId);
        $req->setFields("num_iid");
        $resp = $c->execute($req, session('taobao_access_token'));
        if (isset($resp->items) || count($resp) == 0) {
            $taoapi = D('Taoapi');
            $taoapi->appKeySuccess(session('current_taobao_app_key_id'));
            return $resp->items;
        } else {
            self::dumpTaobaoApiError('getTaobaoCustomItems', $resp);
        }
    }

    public static function getTaobaoDeliveryTemplates() {
        if (self::needVerify()) {
            return 'verify';
        }
        if (!session('?taobao_access_token')) {
            return 'timeout';
        }
        $c = self::initTopClient();
        $req = new DeliveryTemplatesGetRequest;
        $req->setFields("template_id,template_name");
        $resp = $c->execute($req, session('taobao_access_token'));
        if (isset($resp->delivery_templates) || ''.$resp->total_results == '0') {
            $taoapi = D('Taoapi');
            $taoapi->appKeySuccess(session('current_taobao_app_key_id'));
            return $resp->delivery_templates;
        } else {
            self::dumpTaobaoApiError('getTaobaoDeliveryTemplates', $resp);
        }
    }

    public static function getTaobaoSellercatsList($nick) {
        if (self::needVerify()) {
            return 'verify';
        }
        if (!session('?taobao_access_token')) {
            return 'timeout';
        }
        $c = new TopClient;
        $c->appkey = C('taobao_app_key');
        $c->secretKey = C('taobao_secret_key');
        $req = new SellercatsListGetRequest;
        $req->setNick($nick);
        $resp = $c->execute($req);
        if (isset($resp->seller_cats) || count($resp) == 0) {
            return $resp->seller_cats;
        } else {
            self::dumpTaobaoApiError('getTaobaoSellercatsList', $resp);
        }
    }

    public static function getVasSubscribe($nick) {
        $c = new TopClient;
        $c->appkey = C('taobao_app_key');
        $c->secretKey = C('taobao_secret_key');
        $req = new VasSubscribeGetRequest;
        $req->setNick($nick);
        $req->setArticleCode(C('article_code'));
        $resp = $c->execute($req);
        if (isset($resp->article_user_subscribes->article_user_subscribe) || count($resp) == 0) {
            return $resp->article_user_subscribes->article_user_subscribe;
        } else {
            self::dumpTaobaoApiError('getVasSubscribe', $resp);
        }
    }

    public static function dumpTaobaoApiError($apiName, $resp) {
        $appKey = session('taobao_app_key');
        $appSecret = session('taobao_secret_key');
        $sessionKey = session('taobao_access_token');
        $numIid = session('current_taobao_item_id');
        $nick = session('taobao_user_nick');
        $code = $resp->code;
        if (strpos($apiName, 'MovePic') !== false) {
            $msg = $resp;
        } else {
            $msg = $resp->msg.$resp->sub_msg;
        }
        $errorMsg = "[ taobao_api_error ] apiName:{$apiName} appKey:{$appKey} appSecret:{$appSecret} sessionKey:{$sessionKey} numIid:{$numIid} nick:{$nick} code:{$code} msg:{$msg}";
        Log::write($errorMsg, Log::ERR);
        if ($resp->code == '7' || $resp->code == '27') { // 7: accesscontrol.limited-by-app-access-count, 27: Invalid session
            $taoapi = D('Taoapi');
            $taoapi->appKeyFail(session('current_taobao_app_key_id'));
            self::authWithNewAppKey();
        } else {
            echo('<h6 style="color:red;">'.$apiName.' error:'.$resp->msg.$resp->sub_msg.'</h6>');
            dump($resp);
        }
    }

    private static function initTopClient() {
        $c = new TopClient;
        $c->appkey = session('taobao_app_key');
        $c->secretKey = session('taobao_secret_key');
        return $c;
    }

    private static function needVerify() {
        $date = getdate();
        $minutes = $date['minutes'];
        $times = 0;
        if (session('?upload_times_in_minutes')) {
            $times = session('upload_times_in_minutes');
        } else {
            session('upload_times_in_minutes', $times);
        }
        if (session('which_minutes') == $minutes) {
            $times++;
            if ($times > C('max_api_times_per_minute')) {
                session('need_verify_code', true);
                return true;
            }
            session('upload_times_in_minutes', $times);
        } else {
            if (session('?need_verify_code') && !session('need_verify_code')) {
                session('upload_times_in_minutes', 1);
            }
        }
        session('which_minutes', $minutes);
        return false;
    }

    private static function authWithNewAppKey() {
        U('Taobao/Index/switchAppKey', null, true, true, false);
    }
}

?>