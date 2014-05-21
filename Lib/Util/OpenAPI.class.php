<?php

import('@.Util.Util');
vendor('taobao-sdk.TopSdk');

class OpenAPI {

    public static function memberGet($memberId) {
        $api = 'param2/1/cn.alibaba.open/member.get';
        return self::callOpenAPI($api, array('memberId' => $memberId,
                                             'returnFields' => 'memberId,winportAddress,domainAddress'), false);
    }

    public static function offerNew($offer) {
        $api = 'param2/1/cn.alibaba.open/offer.new';
        return self::callOpenAPIWithShortUrl($api, array('offer' => $offer));
    }

    public static function categorySearch($keyWord) {
        $api = 'param2/1/cn.alibaba.open/category.search';
        return self::callOpenAPI($api, array('keyWord' => $keyWord), false);
    }

    public static function getPostCatList($catIDs) {
        $api = 'param2/1/cn.alibaba.open/category.getPostCatList';
        return self::callOpenAPI($api, array('catIDs' => $catIDs), false);
    }

    public static function offerPostFeatures($categoryId) {
        $api = 'param2/1/cn.alibaba.open/offerPostFeatures.get';
        return self::callOpenAPI($api, array('categoryID' => $categoryId), false);
    }

    public static function ibankAlbumList($albumType) {
        $api = 'param2/1/cn.alibaba.open/ibank.album.list';
        return self::callOpenAPI($api, array('albumType' => $albumType), false);
    }

    public static function getSendGoodsAddressList() {
        $api = 'param2/1/cn.alibaba.open/trade.freight.sendGoodsAddressList.get';
        return self::callOpenAPI($api, array('memberId' => session('member_id'),
                                             'returnFields' => 'deliveryAddressId,isCommonUse,location,address'), false);
    }

    public static function getFreightTemplateList() {
        $api = 'param2/1/cn.alibaba.open/e56.delivery.template.list';
        return self::callOpenAPI($api, array('memberId' => session('member_id')), false);
    }

    public static function ibankImageUpload($albumId, $name, $imageBytes) {
        $api = 'param2/1/cn.alibaba.open/ibank.image.upload';
        $url = self::makeUrl($api, array('albumId' => $albumId,
                                         'name' => $name,
                                         'imageBytes' => $imageBytes), false);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('imageBytes' => $imageBytes));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        curl_close($ch);

        return json_decode($data);
    }

    public static function offerGroupHasOpened() {
        $api = 'param2/1/cn.alibaba.open/offerGroup.hasOpened';
        return self::callOpenAPI($api, array('memberId' => session('member_id')), false);
    }

    public static function getSelfCatlist() {
        $api = 'param2/1/cn.alibaba.open/category.getSelfCatlist';
        return self::callOpenAPI($api, array('memberId' => session('member_id')), false);
    }

    private static function callOpenAPI($api, $params, $urlencode) {
        if (!session('?access_token')) {
            return 'timeout';
        }

        if (self::needVerify()) {
            return 'verify';
        }

        $url = self::makeUrl($api, $params, $urlencode);
        $data = self::sendRequest($url);
        $response = json_decode($data);

        if (isset($response->error_code) && $response->error_code == '403') {
            Util::changeAliAppkey('', session('current_alibaba_app_key_id'));
            return 'reauth';
        } else {
            return $response;
        }
    }

    private static function sendRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    private static function makeUrl($api, $paramsArray, $urlencode) {
        $timestamp = time() * 1000;
        $accessToken = session('access_token');
        $params = '';
        foreach ($paramsArray as $key => $val) {
            $value = $val;
            if ($urlencode) {
                $value = urlencode($val);
            }
            $params = $params.$key.'='.$value.'&';
        }
        $url = C('open_url').'/'.$api.'/'.session('alibaba_app_key').'?'.$params.'_aop_timestamp='.
               $timestamp.'&access_token='.$accessToken.'&_aop_signature='.
               Util::signDefault(C('open_url'), $api, array_merge($paramsArray, array('_aop_timestamp' => $timestamp, 'access_token' => $accessToken)));

        return $url;
    }

    private static function callOpenAPIWithShortUrl($api, $params) {
        if (!session('?access_token')) {
            return 'timeout';
        }

        if (self::needVerify()) {
            return 'verify';
        }

        $url = self::makeShortUrl($api, $params);
        $data = self::sendRequestWithShortUrl($url, $params);
        $response = json_decode($data);

        if (isset($response->error_code) && $response->error_code == '403') {
            Util::changeAliAppkey('', session('current_alibaba_app_key_id'));
            return 'reauth';
        } else {
            return $response;
        }
    }

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

    private static function makeShortUrl($api, $paramsArray) {
        $timestamp = time() * 1000;
        $accessToken = session('access_token');
        $url = C('open_url').'/'.$api.'/'.session('alibaba_app_key').'?_aop_timestamp='.
               $timestamp.'&access_token='.$accessToken.'&_aop_signature='.
               Util::signDefault(C('open_url'), $api, array_merge($paramsArray, array('_aop_timestamp' => $timestamp, 'access_token' => $accessToken)));

        return $url;
    }

    public static function getTaobaoItem($numIid) {
        if (self::needVerify()) {
            return 'verify';
        }
        $c = new TopClient;
        $c->appkey = C('taobao_app_key');
        $c->secretKey = C('taobao_secret_key');
        $req = new ItemGetRequest;
        $req->setFields("title,desc,pic_url,sku,item_weight,property_alias,price,item_img.url,cid,nick,props_name,prop_img");
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
        $req->setFields("title,desc,pic_url,sku,item_weight,property_alias,price,item_img.url,cid,nick");
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

    public static function getTaobaoItemProps($cid) {
        if (self::needVerify()) {
            return 'verify';
        }
        $c = new TopClient;
        $c->appkey = C('taobao_app_key');
        $c->secretKey = C('taobao_secret_key');
        $req = new ItempropsGetRequest;
        $req->setFields("pid,name,must,multi,prop_values,is_key_prop,is_sale_prop");
        $req->setCid($cid);
        $resp = $c->execute($req, null);

        if (isset($resp->item_props)) {
            return $resp->item_props;
        } else {
            self::dumpTaobaoApiError('getTaobaoItemProps', $resp);
        }
    }

    public static function addTaobaoItem($item) {
        if (self::needVerify()) {
            return 'verify';
        }
        $c = self::initTopClient();
        $req = new ItemAddRequest;
        foreach ($item as $key => $value) {
            if ($value !== '') {
                call_user_method('set'.$key, $req, $value);
            }
        }
        $resp = $c->execute($req, session('taobao_access_token'));
        $taoapi = D('Taoapi');
        if ($resp->code == '7') { // accesscontrol.limited-by-app-access-count
            $taoapi->appKeyFail(session('current_taobao_app_key_id'));
            self::authWithNewAppKey();
        } else if (isset($resp->item)) {
            $taoapi->appKeySuccess(session('current_taobao_app_key_id'));
            return $resp->item;
        } else {
            self::dumpTaobaoApiError('addTaobaoItem', $resp);
        }
    }

    public static function addTaobaoItemWithMovePic($item) {
        $params = "WebType=51wp_yjsc_dist".
                  "&appKey=".session('taobao_app_key').
                  "&appSecret=".session('taobao_secret_key').
                  "&numIID=".session('current_taobao_item_id').
                  "&sessionID=".session('taobao_access_token').
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
                  "&PostageId=".urlencode('').
                  "&PropertyAlias=".urlencode($item['PropertyAlias']).
                  "&InputStr=".urlencode($item['InputStr']).
                  "&InputPids=".urlencode($item['InputPids']).
                  "&SkuProperties=".urlencode($item['SkuProperties']).
                  "&SkuQuantities=".urlencode($item['SkuQuantities']).
                  "&SkuPrices=".urlencode($item['SkuPrices']).
                  "&SkuOuterIds=".urlencode($item['SkuOuterIds']).
                  "&Outer_id=".urlencode($item['OuterId']).
                  "&mainpic=".urlencode($item['mainpic']).
                  "&checklic=".md5(session('current_taobao_item_id').'51');
        $resp = self::sendRequestWithShortUrl(C('servletUri'), $params);
        if (is_numeric($resp)) {
            $taoapi = D('Taoapi');
            $taoapi->appKeySuccess(session('current_taobao_app_key_id'));
            return $resp;
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
        $c = self::initTopClient();
        $req = new UserBuyerGetRequest;
        $req->setFields("nick");
        $resp = $c->execute($req, session('taobao_access_token'));
        $taoapi = D('Taoapi');
        if ($resp->code == '7') { // accesscontrol.limited-by-app-access-count
            $taoapi->appKeyFail(session('current_taobao_app_key_id'));
            self::authWithNewAppKey();
        } else if (isset($resp->user)) {
            $taoapi->appKeySuccess(session('current_taobao_app_key_id'));
            return $resp->user;
        } else {
            self::dumpTaobaoApiError('getTaobaoUserBuyer', $resp);
        }
    }

    public static function uploadTaobaoItemImg($numIid, $image, $position) {
        if (self::needVerify()) {
            return 'verify';
        }
        $c = self::initTopClient();
        $req = new ItemImgUploadRequest;
        $req->setNumIid($numIid);
        $req->setImage('@'.$image);
        $req->setPosition($position);
        $resp = $c->execute($req, session('taobao_access_token'));
        $taoapi = D('Taoapi');
        if ($resp->code == '7') { // accesscontrol.limited-by-app-access-count
            $taoapi->appKeyFail(session('current_taobao_app_key_id'));
            self::authWithNewAppKey();
        } else if (isset($resp->item_img)) {
            $taoapi->appKeySuccess(session('current_taobao_app_key_id'));
            return $resp->item_img;
        } else {
            self::dumpTaobaoApiError('uploadTaobaoItemImg', $resp);
        }
    }

    public static function uploadTaobaoItemPropImg($numIid, $prop, $image, $position) {
        if (self::needVerify()) {
            return 'verify';
        }
        $c = self::initTopClient();
        $req = new ItemPropimgUploadRequest;
        $req->setNumIid($numIid);
        $req->setProperties($prop);
        $req->setImage($image);
        $req->setPosition($position);
        $resp = $c->execute($req, session('taobao_access_token'));
        $taoapi = D('Taoapi');
        if ($resp->code == '7') { // accesscontrol.limited-by-app-access-count
            $taoapi->appKeyFail(session('current_taobao_app_key_id'));
            self::authWithNewAppKey();
        } else if ($resp->prop_img) {
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
        $c = self::initTopClient();
        $req = new ItemsCustomGetRequest;
        $req->setOuterId($outerId);
        $req->setFields("num_iid");
        $resp = $c->execute($req, session('taobao_access_token'));
        $taoapi = D('Taoapi');
        if ($resp->code == '7') { // accesscontrol.limited-by-app-access-count
            $taoapi->appKeyFail(session('current_taobao_app_key_id'));
            self::authWithNewAppKey();
        } else if (isset($resp->items) || count($resp) == 0) {
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
        $c = self::initTopClient();
        $req = new DeliveryTemplatesGetRequest;
        $req->setFields("template_id,template_name");
        $resp = $c->execute($req, session('taobao_access_token'));
        $taoapi = D('Taoapi');
        if ($resp->code == '7') { // accesscontrol.limited-by-app-access-count
            $taoapi->appKeyFail(session('current_taobao_app_key_id'));
            self::authWithNewAppKey();
        } else if (isset($resp->delivery_templates) || ''.$resp->total_results == '0') {
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
        $c = new TopClient;
        $c->appkey = C('taobao_app_key');
        $c->secretKey = C('taobao_secret_key');
        $req = new SellercatsListGetRequest;
        $req->setNick($nick);
        $resp = $c->execute($req);
        if (isset($resp->seller_cats)) {
            return $resp->seller_cats;
        } else {
            self::dumpTaobaoApiError('getTaobaoItem', $resp);
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
        $errorMsg = "[ taobao_api_error ] apiName:{$apiName} appKey:{$appkey} appSecret:{$appSecret} sessionKey:{$sessionKey} numIid:{$numIid} nick:{$nick} code:{$code} msg:{$msg}";
        Log::write($errorMsg, Log::ERR);
        echo('<h6 style="color:red;">'.$apiName.' error:'.$resp->msg.$resp->sub_msg.'</h6>');
        dump($resp);
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
        $currentTaobaoItemId = session('current_taobao_item_id');
        $taobaoAppKey = session('taobao_app_key');
        session(null);
        U('Taobao/Index/auth', array(
            'taobaoItemId' => $currentTaobaoItemId,
            'taobaoAppKey' => $taobaoAppKey,
        ), true, true, false);
    }
}

?>