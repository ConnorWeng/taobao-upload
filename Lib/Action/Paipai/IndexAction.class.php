<?php

import('@.Util.Util');
import('@.Util.OpenAPI');
import('@.Util.PaipaiOpenAPI');

class IndexAction extends Action {

    public function index() {
        $this->display();
    }

    public function auth() {
        session('paipai_current_taobao_id', I('taobaoItemId'));
        session('use_db', I('db'));
        if (I('taobaoItemId') == '' && I('goodsId') != '') {
            session('paipai_current_goods_id', I('goodsId'));
        } else {
            session('paipai_current_goods_id', null);
        }
        Util::changeDatabaseAccordingToSession();
        if (!session('?paipai_access_token')) {
            header('location: http://fuwu.paipai.com/my/app/authorizeGetAccessToken.xhtml?responseType=access_token&appOAuthID='.C('appOAuthId'));
        } else {
            U('Index/authBack', array(), true, true, false);
        }
    }

    public function authBack() {
        Util::changeDatabaseAccordingToSession();
        if (!session('?paipai_access_token')) {
            session('paipai_access_token', I('?access_token'));
            session('uin', I('useruin'));
            session('sign', I('sign'));
        }

        $taobaoItemId = session('paipai_current_taobao_id');
        if (session('?paipai_current_goods_id')) {
            $taobaoItem = OpenAPI::getTaobaoItemFromDatabase(session('paipai_current_goods_id'));
        } else {
            $taobaoItem = OpenAPI::getTaobaoItemWithoutVerify($taobaoItemId);
        }
        $taobaoItemCat = OpenAPI::getTaobaoItemCatWithoutVerify($taobaoItem->cid);

        $this->assign(array(
            'basepath' => str_replace('index.php', 'Public', __APP__),
            'memberId' => session('uin'),
            'taobaoItemId' => $taobaoItemId,
            'taobaoItemTitle' => $taobaoItem->title,
            'taobaoItemCat' => $taobaoItemCat
        ));

        $this->display();
    }

    public function searchCategory() {
        $navigationId = I('navigationId');
        $navigationList = PaipaiOpenAPI::getNavigationChildList($navigationId);
        $this->ajaxReturn($navigationList, 'JSON');
    }

    public function editPage() {
        Util::changeDatabaseAccordingToSession();
        $navigationId = I('navigationId');
        if (session('?paipai_current_goods_id')) {
            $taobaoItem = OpenAPI::getTaobaoItemFromDatabase(session('paipai_current_goods_id'));
        } else {
            $taobaoItem = OpenAPI::getTaobaoItemWithoutVerify(I('taobaoItemId'));
        }

        $propsArray = $this->makePropsArray($taobaoItem->props_name.'');
        $imgsInDesc = $this->parseDescImages($taobaoItem->desc);

        $price = floatval($taobaoItem->price);
        $seePrice = '';
        $store = M('store');
        $shopMall = '';
        $address = '';
        $storeInfo = $store->where('im_ww="'.$taobaoItem->nick.'"')->find();
        if ($storeInfo && $store != null) {
            $seePrice = $storeInfo['see_price'];
            $shopMall = $storeInfo['shop_mall'];
            $address = $storeInfo['address'];
        }

        $price = Util::makePrice($price, $seePrice, $taobaoItem->title);

        $profit = '0.00';
        $userdataPp = D('UserdataPp');
        $userdata = $userdataPp->where('nick="'.session('uin').'"')->find();
        if ($userdata && $userdata != null) {
            $profit = $userdata['profit'];
        }

        $title = $taobaoItem->title;
        $khn = $this->getKHN($title);
        $title = str_replace($khn, '', $title);
        $title = str_replace('#', '', $title);
        $title = trim(str_replace('*', '', $title));

        // TODO: 设置类目名称以及添加返回选择类目页面的按钮
        $this->assign(array(
            'memberId' => session('uin'),
            'basepath' => str_replace('index.php', 'Public', __APP__),
            'navigationId' => $navigationId,
            'infoTitle' => $title,
            'offerWeight' => '0.2',
            'picUrl' => $taobaoItem->pic_url,
            'itemImgs' => json_encode(Util::parseItemImgs($taobaoItem->item_imgs)),
            'offerDetail' => $taobaoItem->desc,
            'profit' => $profit,
            'stockPrice' => $price + floatval($profit),
            'seePrice' => $seePrice,
            'rawPrice' => $price,
            'khn' => $khn,
            'catStr' => I('catStr'),
            'initSkus' => json_encode(Util::parseSkus($taobaoItem->skus)),
            'propsAlias' => $taobaoItem->property_alias,
            'imgsInDesc' => $imgsInDesc,
            'businessCode' => $shopMall.$address.'_P'.$price.'_'.$khn.'#',
            'movePic' => $this->isMovePicNeeded($taobaoItem->desc),
            'propsArray' => $propsArray,
        ));

        $this->display();
    }

    private function parseDescImages($desc) {
        $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
        preg_match_all($pattern, $desc, $matches);//带引号
        return json_encode($matches[1]);
    }

    private function getKHN($title) {
        $pKh='/[A-Z]?\d+/';
        preg_match_all($pKh,$title,$pKuanhao);
        $pKhnum=count($pKuanhao[0]);
        if($pKhnum>0)
        {
            for($i=0;$i < $pKhnum;$i++)
            {
                if(strlen($pKuanhao[0][$i])==3 || (strlen($pKuanhao[0][$i])==4 && substr($pKuanhao[0][$i], 0,3)!= "201"))
                {
                    $khn = $pKuanhao[0][$i];
                    break;
                }
            }
        }

        return $khn;
    }

    public function getAttributeList() {
        $navigationId = I('navigationId');
        $attributeList = PaipaiOpenAPI::getAttributeList($navigationId);

        $this->ajaxReturn($attributeList, 'JSON');
    }

    public function addItem() {
        Util::changeDatabaseAccordingToSession();
        $itemAttrs = array();

        $itemAttrs['sellerUin'] = session('uin');
        $itemAttrs['itemName'] = I('sTitle');
        $itemAttrs['attr'] = I('attr'); // '31:80020504|30:800|2ef4:3|2ef9:2|516:2|7a4:2|2ee2:3|7a0:2|2b0:2|93b5:3|2ed6:2|93bf:3|79d:2|37:20b|38:f';
        $itemAttrs['classId'] = I('navigationId');
        $itemAttrs['validDuration'] = 1209600;
        $itemAttrs['itemState'] = 'IS_FOR_SALE';
        $itemAttrs['detailInfo'] = $this->movePic($_REQUEST['sDesc']);
        $itemAttrs['sellerPayFreight'] = 1;
        $itemAttrs['freightId'] = 0;
        $itemAttrs['stockPrice'] = floatval(I('dwPrice_bin')) * 100;
        $itemAttrs['stockCount'] = 1000;

        if (I('stockSwitch') == 'on') {
            unset($itemAttrs['stockPrice']);
            unset($itemAttrs['stockCount']);
            $itemAttrs['stockJsonList'] = $_REQUEST['stockList'];
        }

        /* auto off */
        $autoOff = I('autoOff');
        if ($autoOff == 'on') {
            $encNumIid = '51chk'.base64_encode(session('paipai_current_taobao_id'));
            $autoOffJpg = 'http://51wangpi.com/'.$encNumIid.'.jpg';
            $autoOffWarnHtml = '<img align="middle" src="'.$autoOffJpg.'"/><br/>';
            $itemAttrs['detailInfo'] = $autoOffWarnHtml.$itemAttrs['detailInfo'];
        }
        /* end */

        $response = PaipaiOpenAPI::addItem($itemAttrs);
        if ($response->errorCode == 0) {
            /* upload image */
            $uploadImgErrorCode = 0;
            $uploadImgErrorMsg = '';
            for ($i = 1; $i <= 5; $i += 1) {
                $picUrl = $_REQUEST['uploadPicInfo'.$i];
                if ($picUrl != '') {
                    $localImageFile = '@'.Util::downloadImage($picUrl);
                    $uploadResult = PaipaiOpenAPI::modifyItemPic(session('uin'), $response->itemCode, $i - 1, $localImageFile);
                    unlink(substr($localImageFile,1));
                    if ($uploadResult->errorCode != 0) {
                        $uploadImgErrorCode = $uploadResult->errorCode;
                        $uploadImgErrorMsg = $uploadResult->errorMessage;
                    }
                }
            }
            /* end */

            if ($uploadImgErrorCode == 0) {
                $itemUrl = 'http://auction1.paipai.com/'.$response->itemCode;
                $this->assign(array(
                    'result' => '发布成功啦！',
                    'message' => '宝贝已顺利上架哦！祝生意欣荣，财源广进！',
                    'itemUrl' => '<li><a href="'.$itemUrl.'">来看看刚上架的宝贝吧！</a></li>'
                ));
            } else {
                $this->assign(array(
                    'result' => '商品发布成功，但图片上传失败！ errorCode:'.$uploadImgErrorCode.', errorMessage:'.$uploadImgErrorMsg,
                    'message' => '宝贝没有顺利上架，请不要泄气哦，换个宝贝试试吧！祝生意欣荣，财源广进！',
                    'itemUrl' => ''
                ));
            }
        } else {
            $this->assign(array(
                'result' => '发布失败！ errorCode:'.$response->errorCode.', errorMessage:'.$response->errorMessage,
                'message' => '宝贝没有顺利上架，请不要泄气哦，换个宝贝试试吧！祝生意欣荣，财源广进！',
                'itemUrl' => ''
            ));
        }

        $this->display();
    }

    public function updateProfit() {
        Util::changeDatabaseAccordingToSession();
        $memberId = session('uin');
        $profit = I('profit');
        $userdataPp = D('UserdataPp');
        $this->ajaxReturn($userdataPp->updateProfit($memberId, $profit), 'JSON');
    }

    // 登出
    public function signOut() {
        $taobaoItemId = session('paipai_current_taobao_id');
        $currentGoodsId = session('paipai_current_goods_id');
        $useDb = session('use_db');
        session(null);
        cookie(null);
        U('Index/auth', array('taobaoItemId' => $taobaoItemId,
                              'goodsId' => $currentGoodsId,
                              'db' => $useDb,
                              ), true, true, false);
    }

    private function movePic($detailInfo) {
        $pathId = $this->getAlbumPathID();
        $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.JPG]))[\'|\"].*?[\/]?>/";
        preg_match_all($pattern, $detailInfo, $matches);
        $picNum = count($matches[0]);
        for($i=0;$i< $picNum ;$i++) {
            $picUrl = $matches[1][$i];
            $localImagePath = Util::downloadImage($picUrl);
            $localImage = '@'.$localImagePath;
            $newUrl = PaipaiOpenAPI::uploadPaiPaiAlbumImage($pathId, $localImage, uniqid().'.jpg')->Pictures[0]->URL;
            $detailInfo = str_replace($picUrl, $newUrl, $detailInfo);
            unlink($localImagePath);
        }
        return $detailInfo;
    }

    private function getAlbumPathID() {
        $album = PaipaiOpenAPI::getPaiPaiAlbumList();
        $directories = $album->directories;
        $dirCount = count($directories);
        for ($i = 0; $i < $dirCount; $i++) {
            $dir = $directories[$i];
            if (strpos($dir->Name, 'paipai-upload-51zwd') !== false) {
                return '/'.$dir->ID.'/';
            }
        }
        $createDirResult = PaipaiOpenAPI::createPaiPaiAlbumDir()->AlbumCreateDirResult;
        return '/'.$createDirResult->pathId.'/';
    }

    private function isMovePicNeeded($detailInfo) {
        $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.JPG]))[\'|\"].*?[\/]?>/";
        preg_match_all($pattern, $detailInfo, $matches);
        $picNum = count($matches[0]);
        for($i=0;$i< $picNum ;$i++) {
            $picUrl = $matches[1][$i];
            if (strpos($picUrl, 'taobaocdn') !== false) {
                return true;
            }
        }
        return false;
    }

    private function makePropsArray($propsName) {
        $result = array();
        $propsNameArray = split(';', $propsName);
        foreach ($propsNameArray as $propStr) {
            $prop = split(':', $propStr);
            $found = false;
            for ($i = 0; $i < count($result); $i++) {
                $propArray = $result[$i];
                if ($propArray['name'] == $prop[2]) {
                    $result[$i] = array('name' => $prop[2], 'value' => $propArray['value'].','.$prop[3]);
                    $found = true;
                }
            }
            if (!$found) {
                array_push($result, array('name' => $prop[2], 'value' => $prop[3]));
            }
        }
        return $result;
    }
}