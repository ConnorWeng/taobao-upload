<?php

import('@.Util.Util');
vendor('taobao-sdk.TopSdk');

class AlibabaOpenAPI {

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

    public static function downloadImage($picUrl) {
        $tmpFile = APP_PATH.'Upload/'.uniqid().'.jpg';
        $content = file_get_contents($picUrl);
        file_put_contents($tmpFile, $content);

        return $tmpFile;
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
        $url = C('open_url').'/'.$api.'/'.session('alibaba_app_key').'?'.$params.
               '&access_token='.$accessToken.'&_aop_signature='.
               Util::signDefault(C('open_url'), $api, array_merge($paramsArray, array('access_token' => $accessToken)));

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
        $url = C('open_url').'/'.$api.'/'.session('alibaba_app_key').'?access_token='.$accessToken.'&_aop_signature='.
               Util::signDefault(C('open_url'), $api, array_merge($paramsArray, array('access_token' => $accessToken)));

        return $url;
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

}

?>