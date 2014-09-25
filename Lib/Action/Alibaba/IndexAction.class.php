<?php

import('@.Util.Util');
import('@.Util.OpenAPI');
import('@.Util.AlibabaOpenAPI');

class IndexAction extends CommonAction {

    public function index() {
        $this->display();
    }

    // 跳转到alibaba的认证页面
    public function auth() {
        $taobaoItemId = I('taobaoItemId');
        session('use_db', I('db'));
        if (I('taobaoItemId') == '' && I('goodsId') != '') {
            session('alibaba_current_goods_id', I('goodsId'));
        } else {
            session('alibaba_current_goods_id', null);
        }
        Util::changeDatabaseAccordingToSession();
        if (!session('?access_token')) {
            // fetch taobao appkey
            Util::changeTaoAppkey($taobaoItemId, 'trival');

            // fetch alibaba appkey
            Util::changeAliAppkey($taobaoItemId);

            // auth
            header('location:'.Util::getAlibabaAuthUrl($taobaoItemId ? $taobaoItemId : I('goodsId')));
        } else {
            U('Index/authBack', array('state' => $taobaoItemId), true, true, false);
        }
    }

    // 从alibaba跳转回来的action
    public function authBack() {
        Util::changeDatabaseAccordingToSession();
        $taobaoItemId = $_REQUEST['state'];
        session('current_taobao_item_id', $taobaoItemId);

        if (!session('?access_token')) {
            $code = I('code');
            $tokens = Util::getTokens($code);

            session('member_id', $tokens->memberId);
            session('access_token', $tokens->access_token);
            cookie('refresh_token', $tokens->refresh_token);

            $response = $this->checkApiResponse(AlibabaOpenAPI::memberGet(session('member_id')));
        }

        if (session('?alibaba_current_goods_id')) {
            $taobaoItem = OpenAPI::getTaobaoItemFromDatabase(session('alibaba_current_goods_id'));
        } else {
            $taobaoItem = OpenAPI::getTaobaoItemWithoutVerify($taobaoItemId);
        }
        $taobaoItemCat = OpenAPI::getTaobaoItemCatWithoutVerify($taobaoItem->cid);

        $this->assign(array(
            'basepath' => str_replace('index.php', 'Public', __APP__),
            'memberId' => session('member_id'),
            'taobaoItemId' => $taobaoItemId,
            'taobaoItemTitle' => $taobaoItem->title,
            'taobaoItemCat' => $taobaoItemCat,
        ));

        $this->display();
    }

    // 通过关键字查询分类
    public function searchCategory() {
        $keyWord = I('keyWord');
        $searchResult = $this->checkApiResponseAjax(AlibabaOpenAPI::categorySearch($keyWord))->result;
        $categoryIds = '';
        if ($searchResult->total > 0) {
            foreach ($searchResult->toReturn as $val) {
                $categoryIds .= $val.',';
            }
        }
        if (stripos($categoryIds, ',')) {
            $categoryIds = substr($categoryIds, 0, strlen($categoryIds) - 1);
        }
        $categoryList = $this->checkApiResponseAjax(AlibabaOpenAPI::getPostCatList($categoryIds))->result->toReturn;

        $this->ajaxReturn($categoryList, 'JSON');
    }

    // 编辑页面
    public function editPage() {
        Util::changeDatabaseAccordingToSession();
        $taobaoItemId = session('current_taobao_item_id');
        $categoryName = $this->checkApiResponse(AlibabaOpenAPI::getPostCatList(I('categoryId')))->result->toReturn[0]->catsName;

        if (session('?alibaba_current_goods_id')) {
            $taobaoItem = OpenAPI::getTaobaoItemFromDatabase(session('alibaba_current_goods_id'));
        } else {
            $taobaoItem = OpenAPI::getTaobaoItemWithoutVerify($taobaoItemId);
        }

        $imgsInDesc = $this->parseDescImages($taobaoItem->desc);

        $price = floatval($taobaoItem->price);
        $seePrice = '';
        $store = M('store');
        $storeInfo = $store->where('im_ww="'.$taobaoItem->nick.'"')->find();
        if ($storeInfo && $store != null) {
            $seePrice = $storeInfo['see_price'];
        }

        $price = Util::makePrice($price, $seePrice, $taobaoItem->title);
        $title = Util::makeTitle($taobaoItem->title);
        $khn = Util::getHuoHao($taobaoItem->title);

        $profit = '0.00';
        $userdataAli = D('UserdataAli');
        $userdata = $userdataAli->where('nick="'.session('member_id').'"')->find();
        if ($userdata && $userdata != null) {
            $profit = $userdata['profit'];
        }

        // $offerGroupHasOpened = $this->checkApiResponse(AlibabaOpenAPI::offerGroupHasOpened())->result->toReturn[0]->isOpened;
        // if ($offerGroupHasOpened) {
        $selfCatlist = $this->checkApiResponse(AlibabaOpenAPI::getSelfCatlist())->result->toReturn[0]->sellerCats;
        // }

        $this->assign(array(
            'taobaoItemId' => $taobaoItemId,
            'price' => $price + floatval($profit),
            'rawPrice' => $price,
            'memberId' => session('member_id'),
            'basepath' => str_replace('index.php', 'Public', __APP__),
            'infoTitle' => $title,
            'taobaoItemTitle' => $taobaoItem->title,
            'categoryId' => I('categoryId'),
            'categoryName' => $categoryName,
            'offerDetail' => $taobaoItem->desc,
            'picUrl' => $taobaoItem->pic_url,
            'itemImgs' => json_encode(Util::parseItemImgs($taobaoItem->item_imgs)),
            'initSkus' => json_encode(Util::parseSkus($taobaoItem->skus)),
            'propsAlias' => $taobaoItem->property_alias,
            'offerWeight' => '0.2',
            'khn' => $khn,
            'profit' => $profit,
            'selfCatlist' => json_encode($selfCatlist),
            'seePrice' => $seePrice,
            'imgsInDesc' => $imgsInDesc,
            'propsName' => $taobaoItem->props_name
        ));

        $this->display();
    }

    private function parseDescImages($desc) {
        $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
        preg_match_all($pattern, $desc, $matches);//带引号
        return json_encode($matches[1]);
    }

    // 获取发布相关属性
    public function getPostFeatures() {
        $categoryId = I('categoryId');
        $features = $this->checkApiResponseAjax(AlibabaOpenAPI::offerPostFeatures($categoryId))->result->toReturn;

        $this->ajaxReturn($features, 'JSON');
    }

    // 获取发货地址
    public function getSendGoodsAddress() {
        $addressList = $this->checkApiResponseAjax(AlibabaOpenAPI::getSendGoodsAddressList())->result->toReturn;

        $this->ajaxReturn($addressList, 'JSON');
    }

    // 获取运费模版
    public function getFreightTemplateList() {
        $freightTemplateList = $this->checkApiResponseAjax(AlibabaOpenAPI::getFreightTemplateList())->result;

        $this->ajaxReturn($freightTemplateList, 'JSON');
    }

    // 获取相册列表
    public function getAlbumList() {
        $myAlbumList = $this->checkApiResponseAjax(AlibabaOpenAPI::ibankAlbumList('MY'))->result->toReturn;
        $customAlbumList = $this->checkApiResponseAjax(AlibabaOpenAPI::ibankAlbumList('CUSTOM'))->result->toReturn;

        $this->ajaxReturn(array_merge($myAlbumList, $customAlbumList), 'JSON');
    }

    // 发布新产品
    public function offerNew() {
        Util::changeDatabaseAccordingToSession();
        $categoryId = I('categoryId');
        if (get_magic_quotes_gpc() == 0) {
            $detail = addslashes(addslashes($_REQUEST['details']));
        } else {
            $detail = addslashes(($_REQUEST['details']));
        }
        $subject = I('subject');
        $priceRanges = I('priceRanges');
        $supportOnline = Util::check(I('support-online'));
        $skuTradeSupported = Util::check(I('isSkuTradeSupported'));
        $sendGoodsAddressId = I('sendGoodsAddressId');
        $freightType = I('freightType');
        $freightTemplateId = I('freight-list');
        $offerWeight = I('offerWeight');
        $mixWholeSale = Util::check(I('mixSupport'));
        $skuList = $_REQUEST['skuList']; // FIXME: problem
        $periodOfValidity = I('info-validity');
        $productFeatures = $_REQUEST['productFeatures'];
        $taobaoItemId = session('current_taobao_item_id');
        $userCategorys = $_REQUEST['userCategorys'];

        /* upload image */
        $imageUriList = '[';
        for ($i = 1; $i <= 3; $i += 1) { // 因为一共三个input: pictureUrl1,pictureUrl2,pictureUrl3
            $picUrl = $_REQUEST['pictureUrl'.$i];
            if ($picUrl != '') {
                $albumId = I('albumId');
                $localImageFile = '@'.Util::downloadImage($picUrl);
                $uploadResult = $this->checkApiResponse(AlibabaOpenAPI::ibankImageUpload($albumId, uniqid(), $localImageFile))->result->toReturn[0];
                unlink(substr($localImageFile,1));
                if ($uploadResult != null) {
                    $imageUriList .= '"http://img.china.alibaba.com/'.$uploadResult->url.'",';
                }
            }
        }
        $imageUriList = substr($imageUriList, 0, strlen($imageUriList) - 1);
        $imageUriList .= ']';
        /* end */

        /* auto off */
        $autoOff = Util::check(I('autoOff'));
        if ($autoOff == 'true') {
            $encNumIid = '51chk'.base64_encode($taobaoItemId);
            $autoOffJpg = 'http://51wangpi.com/'.$encNumIid.'.jpg';
            $autoOffWarnHtml = '<img align="middle" src="'.$autoOffJpg.'"/><br/>';
            if (get_magic_quotes_gpc() == 0) {
                $autoOffWarnHtml = addslashes(addslashes($autoOffWarnHtml));
            } else {
                $autoOffWarnHtml = addslashes($autoOffWarnHtml);
            }
            $detail = $autoOffWarnHtml.$detail;
        }
        /* end */

        $offer = '{"bizType":"1","categoryID":"'.$categoryId.'","supportOnlineTrade":'.$supportOnline.',"pictureAuthOffer":"false","priceAuthOffer":"false","skuTradeSupport":'.$skuTradeSupported.',"mixWholeSale":"'.$mixWholeSale.'","priceRanges":"'.$priceRanges.'","amountOnSale":"100","offerDetail":"'.$detail.'","subject":"'.$subject.'","imageUriList":'.$imageUriList.',"freightType":"'.$freightType.'","productFeatures":'.$productFeatures.',"sendGoodsAddressId":"'.$sendGoodsAddressId.'","freightTemplateId":"'.$freightTemplateId.'","offerWeight":"'.$offerWeight.'","skuList":'.$skuList.',"periodOfValidity":'.$periodOfValidity.',"userCategorys":'.$userCategorys.'}';

        $result = $this->checkApiResponse(AlibabaOpenAPI::offerNew(stripslashes($offer)));
        if ($result->result->success) {
            $offerId = $result->result->toReturn[0];
            $itemUrl = "http://detail.1688.com/offer/$offerId.html";
            $this->assign(array(
                'result' => '发布成功啦！',
                'message' => '宝贝已经顺利上架哦！亲，感谢你对51网的大力支持！',
                'itemUrl' => '<li><a href="'.$itemUrl.'">来看看刚上架的宝贝吧！</a></li>',
                'error' => 'false'
            ));
            $this->uploadCount(session('member_id'), get_client_ip());
        } else {
            Log::write('session[access_token]:' . session('access_token'));
            Log::write('offer params:' . $offer);
            $this->assign(array(
                'result' => '发布失败！'.json_encode($result->message),
                'message' => '宝贝没有顺利上架，请不要泄气哦，换个宝贝试试吧！祝生意欣荣，财源广进！',
                'itemUrl' => '',
                'error' => 'true'
            ));
        }

        $this->display();
    }

    public function verifyCode() {
        $this->assign(array(
            'message' => '抱歉，您操作过于频繁，请输入验证码',
            'state' => session('current_taobao_item_id'),
        ));
        $this->display();
    }

    public function checkVerifyCode() {
        if($_SESSION['verify'] != md5($_POST['verifyCode'])) {
            $this->error('验证码错误！');
        } else {
            session('need_verify_code', false);
            session('upload_times_in_minutes', 0);
            U('Index/authBack', array('state' => session('current_taobao_item_id')), true, true, false);
        }
    }

    public function verify() {
        import('ORG.Util.Image');
        Image::buildImageVerify();
    }

    private function uploadCount($memberId, $ip) {
        Util::changeDatabaseAccordingToSession();
        $userdataAli = D('UserdataAli');
        $userdataAli->uploadCount($memberId, $ip);
    }

    public function updateProfit() {
        Util::changeDatabaseAccordingToSession();
        $memberId = session('member_id');
        $profit = I('profit');
        $userdataAli = D('UserdataAli');
        $this->ajaxReturn($userdataAli->updateProfit($memberId, $profit), 'JSON');
    }

    // 登出
    public function signOut() {
        $taobaoItemId = session('current_taobao_item_id');
        $currentGoodsId = session('alibaba_current_goods_id');
        $useDb = session('use_db');
        session(null);
        cookie(null);
        U('Index/auth', array('taobaoItemId' => $taobaoItemId,
                              'goodsId' => $currentGoodsId,
                              'db' => $useDb,
                              ), true, true, false);
    }

    public function showError() {
        $msg = I('msg');
        $url = I('url');
        $this->error($msg, $url);
    }

    private function movePic($detail, $albumId) {
        $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.JPG]))[\'|\"].*?[\/]?>/";
        preg_match_all($pattern, $detail, $matches);
        $picNum = count($matches[0]);
        for($i=0;$i< $picNum ;$i++) {
            $picUrl = $matches[1][$i];
            $localImagePath = Util::downloadImage($picUrl);
            $localImage = '@'.$localImagePath;
            $resp = $this->checkApiResponse(AlibabaOpenAPI::ibankImageUpload($albumId, uniqid(), $localImage))->result->toReturn[0];
            $newUrl = 'http://img.china.alibaba.com/'.$resp->url;
            $detail = str_replace($picUrl, $newUrl, $detail);
            unlink($localImagePath);
        }
        return $detail;
    }
}