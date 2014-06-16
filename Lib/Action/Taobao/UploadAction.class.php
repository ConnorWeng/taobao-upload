<?php
import('@.Util.Util');
import('@.Util.OpenAPI');
import('@.Model.StoreSession');

class UploadAction extends CommonAction {
    public function selectCategory() {
        $this->display();
    }

    public function editItem() {
        header("Content-type:text/html;charset=utf-8");
        $taobaoItemId = session('current_taobao_item_id');
        $nick = session('taobao_user_nick');
        $isCurrentTaobaoItemIdInSession = $this->isCurrentTaobaoItemIdInSession();

        if ($isCurrentTaobaoItemIdInSession && I('continue') == '') {
            $this->assign(array('isCurrentTaobaoItemIdInSession' => $isCurrentTaobaoItemIdInSession));
            return $this->display();
        } else {
            $isCurrentTaobaoItemIdInSession = false;
        }

        $taobaoItem = $this->checkApiResponse(OpenAPI::getTaobaoItem($taobaoItemId));
        $props = $this->checkApiResponse(OpenAPI::getTaobaoItemProps($taobaoItem->cid));
        $cname = $this->checkApiResponse(OpenAPI::getTaobaoItemCat($taobaoItem->cid));
        $deliveryTemplates = $this->checkApiResponse(OpenAPI::getTaobaoDeliveryTemplates());
        $userdata = $this->makeUserdata($nick, $deliveryTemplates);
        $images = $this->makeImages($taobaoItem->item_imgs);
        $propsHtml = $this->makePropsHtml($props, $taobaoItem->props_name);
        $sizeType = $this->makeSizeType($props);
        $sizePropHtml = $this->makeSizePropHtml($props);
        $salePropsObject = $this->makeSalePropsObject($props);
        $title = $this->makeTitle($taobaoItem->title);
        $storeInfo = $this->getStoreInfo($taobaoItem->nick);
        $price = $this->makePrice($taobaoItem->price, $storeInfo['see_price']);
        $caculatedPrice = $this->caculatePrice($price, $userdata['profit0'], $userdata['profit']);
        $outerId = $this->makeOuterId($taobaoItem->title, $taobaoItem->price, $storeInfo);
        $isUploadedBefore = $this->makeIsUploadedBefore($outerId);
        $propImgs = urlencode($this->makePropImgs($taobaoItem->prop_imgs->prop_img));
        $deliveryTemplateHtml = $this->makeDeliveryTemplateHtml($deliveryTemplates, $userdata['usePostModu']);
        $sellerCatsHtml = $this->makeSellerCatsHtml($cname);
        $movePic = $this->makeMovePic($taobaoItem->desc);
        $isSubscribe = $this->isSubscribe();
        $storeSession = new StoreSession(null, null);
        $this->assign(array(
            'taobaoItemTitle' => $title,
            'taobaoItemId' => $taobaoItemId,
            'propsHtml' => $propsHtml,
            'price' => $caculatedPrice,
            'rawPrice' => $price,
            'desc' => $taobaoItem->desc,
            'cid' => $taobaoItem->cid,
            'picUrl' => $taobaoItem->pic_url,
            'image0' => $images[1],
            'image1' => $images[2],
            'image2' => $images[3],
            'image3' => $images[4],
            'sizeType' => $sizeType,
            'outerId' => $outerId,
            'nick' => $nick,
            'huoHao' => $this->getHuoHao($taobaoItem->title),
            'imgsInDesc' => $this->parseDescImages($taobaoItem->desc),
            'percent' => $userdata['profit0'],
            'profit' => $userdata['profit'],
            'initSkus' => json_encode(Util::parseSkus($taobaoItem->skus->sku)),
            'propImgs' => $propImgs,
            'isUploadedBefore' => $isUploadedBefore,
            'isCurrentTaobaoItemIdInSession' => $isCurrentTaobaoItemIdInSession,
            'propAlias' => $taobaoItem->property_alias,
            'postFee' => $userdata['postFee'],
            'expressFee' => $userdata['expressFee'],
            'emsFee' => $userdata['emsFee'],
            'sellerFreight' => $userdata['buyerFreight'] == '0' ? 'checked' : '',
            'buyerFreight' => $userdata['buyerFreight'] == '1' ? 'checked' : '',
            'useModuFreight' => $userdata['usePostModu'] == '999' ? '' : 'checked',
            'postFreight' => $userdata['buyerFreight'] == '1' && $userdata['usePostModu'] == '999' ? 'checked' : '',
            'deliveryTemplateHtml' => $deliveryTemplateHtml,
            'sellerCatsHtml' => $sellerCatsHtml,
            'sizePropHtml' => $sizePropHtml,
            'salePropsObject' => urlencode(json_encode($salePropsObject)),
            'movePic' => $movePic,
            'isSubscribe' => $isSubscribe,
            'otherStoreSessions' => $storeSession->getAllStoreSessionsArray(),
        ));
        $this->display();
    }

    public function uploadItem() {
        header("Content-type:text/html;charset=utf-8");
        $imagePath = Util::downloadImage(I('picUrl1'));
        $image = '@'.$imagePath;
        $skuTableData = json_decode($_REQUEST['J_SKUTableData']);
        $salePropsObject = json_decode(urldecode($_REQUEST['salePropsObject']));
        $desc = $this->makeDesc($_REQUEST['_fma_pu__0_d'], session('current_taobao_item_id'));
        $item = array(
            'Num' => '30',
            'Price' => I('_fma_pu__0_m'),
            'Type' => 'fixed',
            'StuffStatus' => 'new',
            'Title' => I('_fma_pu__0_ti'),
            'Desc' => $desc,
            'LocationState' => I('_fma_pu__0_po_place'),
            'LocationCity' => I('_fma_pu__0_po_city'),
            'Cid' => I('cid'),
            'ApproveStatus' => I('_now') != '2' ? 'onsale' : 'instock',
            'ListTime' => $this->makeListTime(I('_now'), I('__date'), I('__hour'), I('__minute')),
            'Props' => $this->makeProps($_REQUEST, $skuTableData, I('sizeType')),
            'FreightPayer' => I('postages'),
            'PostageId' => I('postages') == 'buyer' ? I('template_id') : '',
            'ValidThru' => '7',
            'HasInvoice' => 'true',
            'HasWarranty' => 'true',
            'HasShowcase' => 'false',
            'SellerCids' => I('sellerCats'),
            'HasDiscount' => 'false',
            'Image' => $image,
            'PostFee' => I('post_fee'),
            'ExpressFee' => I('express_fee'),
            'EmsFee' => I('ems_fee'),
            'PropertyAlias' => $this->makePropertyAlias($skuTableData, $_REQUEST, $salePropsObject),
            'InputStr' => '',
            'InputPids' => '',
            'SkuProperties' => $this->makeSkuProperties($skuTableData),
            'SkuQuantities' => $this->makeSkuQuantities($skuTableData),
            'SkuPrices' => $this->makeSkuPrices($skuTableData),
            'SkuOuterIds' => $this->makeSkuOuterIds($skuTableData),
            'OuterId' => I('_fma_pu__0_o'),
            'mainpic' => strpos($_REQUEST['picUrl1'], 'http://') === false ? 'http://yjsc.51zwd.com/taobao-upload-multi-store/'.$_REQUEST['picUrl1'] : $_REQUEST['picUrl1'],
        );
        $storeSession = new StoreSession(null, null);
        $otherStoreSessions = $storeSession->getAllStoreSessionsArray();
        $others = array();
        if (I('movePic') == 'on' || $this->makeMovePic($desc) == 'checked') {
            $numIid = $this->checkApiResponse(OpenAPI::addTaobaoItemWithMovePic($item));
            foreach ($otherStoreSessions as $store) {
                $item['FreightPayer'] = 'buyer';
                $item['PostageId'] = '';
                $item['PostFee'] = '15';
                $item['ExpressFee'] = '15';
                $item['EmsFee'] = '15';
                $otherNumIid = $this->checkApiResponse(OpenAPI::addTaobaoItemWithMovePic($item, $store['accessToken']));
                array_push($others, $otherNumIid);
            }
        } else {
            $uploadedItem = $this->checkApiResponse(OpenAPI::addTaobaoItem($item));
            $numIid = $uploadedItem->num_iid;
            if (isset($numIid)) {
                $this->uploadItemImages((float)$numIid, $_REQUEST);
                $this->uploadPropImages((float)$numIid, json_decode(urldecode(I('propImgs'))));
            }
            foreach ($otherStoreSessions as $store) {
                $item['FreightPayer'] = 'buyer';
                $item['PostageId'] = '';
                $item['PostFee'] = '15';
                $item['ExpressFee'] = '15';
                $item['EmsFee'] = '15';
                $otherItem = $this->checkApiResponse(OpenAPI::addTaobaoItem($item, $store['accessToken']));
                $otherNumIid = $otherItem->num_iid;
                if (isset($otherNumIid)) {
                    $this->uploadItemImages((float)$otherNumIid, $_REQUEST, $store['accessToken']);
                    $this->uploadPropImages((float)$otherNumIid, json_decode(urldecode(I('propImgs'))), $store['accessToken']);
                }
                array_push($others, $otherNumIid);
            }
        }
        unlink($imagePath);
        $itemUrls = '';
        $error = false;
        if (isset($numIid)) {
            $result = session('taobao_user_nick').'发布成功！<br/>';
            $itemUrl = 'http://item.taobao.com/item.htm?spm=686.1000925.1000774.13.Odmgnd&id='.$numIid;
            $itemUrls = '<li><a href="'.$itemUrl.'">查看'.session('taobao_user_nick').'中的宝贝</a></li>';
            $this->recordTaobaoItemIdToSession(session('current_taobao_item_id'));
        } else {
            $result = session('taobao_user_nick').'发布失败！<br/>';
            $error = true;
        }
        for ($i = 0; $i < count($otherStoreSessions); $i++) {
            $otherNumIid = $others[$i];
            $store = $otherStoreSessions[$i];
            if (isset($otherNumIid)) {
                $result .= $store['nick'].'发布成功!<br/>';
                $itemUrl = 'http://item.taobao.com/item.htm?spm=686.1000925.1000774.13.Odmgnd&id='.$otherNumIid;
                $itemUrls .= '<li><a href="'.$itemUrl.'">查看'.$store['nick'].'中的宝贝</a></li>';
            } else {
                $result .= $store['nick'].'发布失败!<br/>';
                $error = true;
            }
        }
        $this->assign(array(
            'result' => $result,
            'message' => $error ? '宝贝没有顺利上架，请不要泄气哦！祝生意欣荣，财源广进！' : '宝贝已经顺利上架哦！亲，感谢你对51网的大力支持！',
            'itemUrl' => $itemUrls,
            'error' => $error,
        ));
        $this->display();
    }

    public function uploadItemFromAndroid() {
        $taobaoItemId = I('taobaoItemId');
        $taobaoItem = $this->checkApiResponse(OpenAPI::getTaobaoItemWithoutVerify($taobaoItemId));
        $imagePath = Util::downloadImage($taobaoItem->pic_url);
        $image = '@'.$imagePath;
        $skuProperties = '';
        $skuQuantities = '';
        $skuPrices = '';
        $skuOuterIds = '';
        $count = count($taobaoItem->skus->sku);
        for ($i = 0; $i < $count; $i++) {
            $sku = $taobaoItem->skus->sku[$i];
            $skuProperties .= $sku->properties.',';
            $skuQuantities .= $sku->quantity.',';
            $skuPrices .= $sku->price.',';
            $skuOuterIds .= ',';
        }
        if (strlen($skuProperties) > 0) {
            $skuProperties = substr($skuProperties, 0, strlen($skuProperties) - 1);
            $skuQuantities = substr($skuQuantities, 0, strlen($skuQuantities) - 1);
            $skuPrices = substr($skuPrices, 0, strlen($skuPrices) - 1);
            $skuOuterIds = substr($skuOuterIds, 0, strlen($skuOuterIds) - 1);
        }
        $item = array(
            'Num' => '30',
            'Price' => $taobaoItem->price,
            'Type' => 'fixed',
            'StuffStatus' => 'new',
            'Title' => $taobaoItem->title,
            'Desc' => $taobaoItem->desc,
            'LocationState' => '广东',
            'LocationCity' => '广州',
            'Cid' => intval($taobaoItem->cid),
            'ApproveStatus' => 'onsale',
            'Props' => $taobaoItem->props,
            'FreightPayer' => 'seller',
            'ValidThru' => '14',
            'HasInvoice' => 'true',
            'HasWarranty' => 'true',
            'HasShowcase' => 'false',
            'HasDiscount' => 'false',
            'Image' => $image,
            'PropertyAlias' => $taobaoItem->property_alias,
            'SkuProperties' => $skuProperties,
            'SkuQuantities' => $skuQuantities,
            'SkuPrices' => $skuPrices,
            'SkuOuterIds' => $skuOuterIds,
        );
        $uploadedItem = OpenAPI::addTaobaoItemWithoutVerify($item, I('access_token'));
        unlink($imagePath);
        if (isset($uploadedItem->num_iid)) {
            $this->ajaxReturn('true');
        } else {
            $this->ajaxReturn('false');
        }
    }

    private function recordTaobaoItemIdToSession($taobaoItemId) {
        if (session('?uploaded_taobao_item_ids')) {
            $uploadedTaobaoItemIds = session('uploaded_taobao_item_ids');
            array_push($uploadedTaobaoItemIds, $taobaoItemId);
        } else {
            session('uploaded_taobao_item_ids', array($taobaoItemId));
        }
    }

    public function saveConfig() {
        $data = array(
            'profit0' => I('percent'),
            'profit' => I('profit'),
            'postFee' => I('postFee'),
            'expressFee' => I('expressFee'),
            'emsFee' => I('emsFee'),
            'buyerFreight' => I('buyerFreight'),
            'usePostModu' => I('usePostModu'),
        );
        $userdataConfig = M('UserdataConfig');
        $this->ajaxReturn($userdataConfig->where("nick='".I('nick')."'")->setField($data));
    }

    private function makePropertyAlias($skuTableData, $request, $salePropsObject) {
        $salePropsArray = get_object_vars($salePropsObject);
        $propertyAlias = '';
        foreach ($skuTableData as $key => $value) {
            $skuProperties = str_replace('_', ';', str_replace('-', ':', $key));
            $skuProps = split(';', $skuProperties);
            foreach ($skuProps as $prop) {
                $originValueName = get_object_vars($salePropsArray[$prop])['0'];
                $alias = $request['cpva_'.$prop];
                if ($originValueName != $alias) {
                    if (strlen($propertyAlias.$prop.':'.$alias.';') < 511) {
                        $propertyAlias .= $prop.':'.$alias.';';
                    }
                }
            }
        }
        return $propertyAlias = substr($propertyAlias, 0, strlen($propertyAlias) - 1);
    }

    private function makeSkuProperties($skuTableData) {
        $skuProperties = '';
        foreach ($skuTableData as $key => $value) {
            $skuProperties .= str_replace('_', ';', str_replace('-', ':', $key)).',';
        }
        return $skuProperties = substr($skuProperties, 0, strlen($skuProperties) - 1);
    }

    private function makeSkuQuantities($skuTableData) {
        $skuQuantities = '';
        foreach ($skuTableData as $key => $value) {
            if (intval($value->quantity) > 0) {
                $skuQuantities .= $value->quantity.',';
            } else {
                $skuQuantities .= '999,';    /* hard code to avoid empty */
            }
        }
        return $skuQuantities = substr($skuQuantities, 0, strlen($skuQuantities) - 1);
    }

    private function makeSkuPrices($skuTableData) {
        $skuPrices = '';
        foreach ($skuTableData as $key => $value) {
            $skuPrices .= $value->price.',';
        }
        return $skuPrices = substr($skuPrices, 0, strlen($skuPrices) - 1);
    }

    private function makeSkuOuterIds($skuTableData) {
        $skuOuterIds = '';
        foreach ($skuTableData as $key => $value) {
            $skuOuterIds .= ',';
        }
        return $skuOuterIds = substr($skuOuterIds, 0, strlen($skuOuterIds) - 1);
    }

    private function makeProps($request, $skuTableData, $sizeType) {
        $propsArray = array();
        foreach ($request as $key => $value) {
            if (strpos($key, 'cp_') !== false && $value !== ''
                && strpos($key, '1627207') === false
                && strpos($key, '20509') === false
                && strpos($key, '20518') === false) {
                array_push($propsArray, $value);
            }
        }
        $colorArray = array();
        $sizeArray = array();
        foreach ($skuTableData as $key => $value) {
            $color = split('-', split('_', $key)[0])[1];
            array_push($colorArray, $color);
            $size = split('-', split('_', $key)[1])[1];
            array_push($sizeArray, $size);
        }
        $colorProp = '1627207:';
        $colorPropStr = $colorProp.implode(',', array_unique($colorArray));
        $sizeProp = $sizeType == 0 ? '20509:' : '20518:';
        $sizePropStr = $sizeProp.implode(',', array_unique($sizeArray));
        return implode(';', $propsArray).';'.$colorPropStr.';'.$sizePropStr;
    }

    private function makeUserdata($nick, $deliveryTemplates) {
        $userdataConfig = M('UserdataConfig');
        $userdata = $userdataConfig->where("nick='".$nick."'")->find();
        if (count($userdata) == 0) {
            $userdataConfig = M('UserdataConfig');
            $data['profit0'] = '100.00';
            $data['profit'] = '0.00';
            $data['postFee'] = '15.00';
            $data['expressFee'] = '15.00';
            $data['emsFee'] = '15.00';
            $data['nick'] = $nick;
            $data['buyerFreight'] = '1';
            if (count($deliveryTemplates->delivery_template) > 0) {
                $data['usePostModu'] = ''.$deliveryTemplates->delivery_template[0]->template_id;
            } else {
                $data['usePostModu'] = '999';
            }
            $userdataConfig->add($data);
            $userdata = $data;
        }
        return $userdata;
    }

    private function makeImages($itemImgs) {
        $images = array(null, null, null, null, null);
        for ($i = 0; $i < count($itemImgs->item_img); $i++) {
            $images[$i] = $itemImgs->item_img[$i]->url;
        }
        return $images;
    }

    private function makePropsHtml($props, $propsName) {
        $count = count($props->item_prop);
        $html = '';
        for ($i = 0; $i < $count; $i++) {
            $prop = $props->item_prop[$i];
            if ($this->isSaleProp($prop) || ''.$prop->pid == '13021751') continue;
            $html .= '<li class="J_spu-property" id="spu_'.$prop->pid.'">';
            $html .= '<label class="label-title">'.$prop->name.':</label>';
            $html .= '<span><ul class="J_ul-single ul-select"><li>';
            $html .= '<select name="cp_'.$prop->pid.'" id="prop_'.$prop->pid.'">';
            $html .= '<option value=""></option>';
            $hasSelected = false;
            if (isset($prop->prop_values)) {
                $valueCount = count($prop->prop_values->prop_value);
                for ($j = 0; $j < $valueCount; $j++) {
                    $value = $prop->prop_values->prop_value[$j];
                    $optionValue = $prop->pid.':'.$value->vid;
                    if (strpos($propsName, $optionValue) !== false || ($j == $valueCount - 1 && !$hasSelected && ''.$prop->must == 'true')) {
                        $selected = 'selected';
                        $hasSelected = true;
                    } else {
                        $selected = '';
                    }
                    $html .= '<option value="'.$optionValue.'" '.$selected.'>'.$value->name.'</option>';
                }
            }
            $html .= '</select>';
            $html .= '</li></ul></span>';
            $html .= '</li>';
        }
        return $html;
    }

    private function makeSizePropHtml($props) {
        $sizePropHtml = '';
        $count = count($props->item_prop);
        for ($i = 0; $i < $count; $i++) {
            $prop = $props->item_prop[$i];
            if ($this->isSaleProp($prop) && (''.$prop->name == '尺码' || ''.$prop->name == '尺寸')) {
                $valueCount = count($prop->prop_values->prop_value);
                for ($j = 0; $j < $valueCount; $j++) {
                    $value = $prop->prop_values->prop_value[$j];
                    $sizePropHtml .= '<li class="sku-item">';
                    $sizePropHtml .= '<input type="checkbox" class="J_Checkbox" name="cp_'.$prop->pid.'" value="'.$prop->pid.':'.$value->vid.'" id="prop_'.$prop->pid.'-'.$value->vid.'">';
                    $sizePropHtml .= '<label class="labelname" for="prop_'.$prop->pid.'-'.$value->vid.'" title="'.$value->name.'">'.$value->name.'</label>';
                    $sizePropHtml .= '<input id="J_Alias_'.$prop->pid.'-'.$value->vid.'" class="editbox text" maxlength="15" type="text" value="'.$value->name.'" name="cpva_'.$prop->pid.':'.$value->vid.'">';
                    $sizePropHtml .= '</li>';
                }
            }
        }
        return $sizePropHtml;
    }

    private function makeSalePropsObject($props) {
        $count = count($props->item_prop);
        for ($i = 0; $i < $count; $i++) {
            $prop = $props->item_prop[$i];
            if ($this->isSaleProp($prop)) {
                $valueCount = count($prop->prop_values->prop_value);
                for ($j = 0; $j < $valueCount; $j++) {
                    $value = $prop->prop_values->prop_value[$j];
                    $salePropsObject[$prop->pid.':'.$value->vid] = $value->name;
                }
            }
        }
        return $salePropsObject;
    }

    /* 0:20509 尺码, 1:20518 尺寸 */
    private function makeSizeType($props) {
        $count = count($props->item_prop);
        for ($i = 0; $i < $count; $i++) {
            $prop = $props->item_prop[$i];
            if ($this->isSaleProp($prop)) {
                if (''.$prop->pid == '20509') {
                    return 0;
                } else if (''.$prop->pid == '20518') {
                    return 1;
                }
            }
        }
        return 0;
    }

    private function isSaleProp($prop) {
        if (''.$prop->is_sale_prop === 'true') return true;
        return false;
    }

    private function getStoreInfo($im_ww) {
        $store = M('store');
        return $storeInfo = $store->where('im_ww="'.$im_ww.'"')->find();
    }

    private function makeOuterId($title, $rawPrice, $storeInfo) {
        $seller = $storeInfo['shop_mall'].$storeInfo['address'];
        $price = $this->makePrice($rawPrice, $storeInfo['see_price']);
        $huoHao = $this->getHuoHao($title);
        return $outerId = $seller.'_P'.$price.'_'.$huoHao.'#';
    }

    private function makeTitle($title) {
        $huoHao = $this->getHuoHao($title);
        $newTitle = str_replace('款号', '',
                                str_replace('*', '',
                                            str_replace('#', '',
                                                        str_replace($huoHao, '', $title))));
        return trim($newTitle);
    }

    private function makePrice($rawPrice, $seePrice) {
        $localSeePrice = str_replace("减","",$seePrice);
        $price = $rawPrice;
        if($localSeePrice == "半") {
            $price = $rawPrice >> 1;
        } else if ($localSeePrice == "P") {
            //get price from title
            $pprice='/P(\d+)/';
            preg_match($pprice,$respitem->item->title,$pric);
            $price  = $pric[1];
        } else {
            $price = $price - $localSeePrice;
        }
        return $price;
    }

    private function caculatePrice($price, $percent, $profit) {
        return floatval($price) * (floatval($percent) / 100.00) + floatval($profit);
    }

    private function getHuoHao($title) {
        $kuanHaoRegex='/[A-Z]?\d+/';
        preg_match_all($kuanHaoRegex,$title,$kuanHao);
        $pKhnum=count($kuanHao[0]);
        if($pKhnum>0) {
            for($i=0;$i < $pKhnum;$i++) {
                if(strlen($kuanHao[0][$i])==3 || (strlen($kuanHao[0][$i])==4 && substr($kuanHao[0][$i], 0,3)!= "201")) {
                    $huoHao = $kuanHao[0][$i];
                    break;
                }
            }
        }
        return $huoHao;
    }

    private function parseDescImages($desc) {
        $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
        preg_match_all($pattern, $desc, $matches);//带引号
        return json_encode($matches[1]);
    }

    private function makeDesc($desc, $taobaoItemId) {
        $newDesc = $desc;
        $newDesc .= '<font color="white">welcome to my store!</font>';
        return $newDesc;
    }

    private function uploadItemImages($numIid, $request, $sessionKey = null) {
        $jumpImgCount = 0;
        for ($i = 2; $i <= 5; $i++) {
            $picUrl = $request['picUrl'.$i];
            if ($picUrl != '') {
                $picPath = Util::downloadImage($picUrl);
                $filesize = filesize($picPath);
                if ($filesize !== false && $filesize > 10240) {
                    $itemImg = $this->checkApiResponse(OpenAPI::uploadTaobaoItemImg($numIid, $picPath, $i - 1 - $jumpImgCount, $sessionKey));
                } else {
                    $jumpImgCount += 1;
                }
                unlink($picPath);
            }
        }
    }

    private function uploadPropImages($numIid, $propImgs, $sessionKey = null) {
        foreach ($propImgs as $propImg) {
            $imagePath = Util::downloadImage($propImg->url);
            $image = '@'.$imagePath;
            $this->checkApiResponse(OpenAPI::uploadTaobaoItemPropImg($numIid, $propImg->properties, $image, $propImg->position, $sessionKey));
            unlink($imagePath);
        }
    }

    private function makePropImgs($propImgs) {
        $result = '[';
        $count = count($propImgs);
        for ($i = 0; $i < $count; $i++) {
            $result .= json_encode($propImgs[$i]).',';
        }
        if (strlen($result) > 1) {
            $result = substr($result, 0, strlen($result) - 1);
        }
        $result .= ']';
        return $result;
    }

    private function makeDeliveryTemplateHtml($deliveryTemplates, $usePostModu) {
        $count = count($deliveryTemplates->delivery_template);
        $deliveryTemplateHtml = '';
        for ($i = 0; $i < $count; $i++) {
            $template = $deliveryTemplates->delivery_template[$i];
            if ($usePostModu != 999 && $usePostModu == $template->template_id) {
                $deliveryTemplateHtml .= '<option value="'.$template->template_id.'" selected>'.$template->name.'</option>';
            } else {
                $deliveryTemplateHtml .= '<option value="'.$template->template_id.'">'.$template->name.'</option>';
            }
        }
        return $deliveryTemplateHtml;
    }

    private function makeSellerCatsHtml($cname) {
        $sellerCatsHtml = '';
        $sellerCats = OpenAPI::getTaobaoSellercatsList(session('taobao_user_nick'));
        $parentCids = $this->getAllParentCids($sellerCats);
        $count = count($sellerCats->seller_cat);
        for ($i = 0; $i < $count; $i++) {
            $sellerCat = $sellerCats->seller_cat[$i];
            if (!in_array(''.$sellerCat->cid, $parentCids)) {
                if ($this->matchCat($cname, $sellerCat->name)) {
                    $sellerCatsHtml .= '<option value="'.$sellerCat->cid.'" selected>'.$sellerCat->name.'</option>';
                } else {
                    $sellerCatsHtml .= '<option value="'.$sellerCat->cid.'">'.$sellerCat->name.'</option>';
                }
            }
        }
        return $sellerCatsHtml;
    }

    private function makeMovePic($desc) {
        $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
        preg_match_all($pattern, $desc, $matches);
        $picNum = count($matches[0]);
        $check = '';
        for($i=0;$i< $picNum ;$i++) {
            $picUrl = $matches[1][$i];
            if (strpos($picUrl, "!!") !== false || strpos($picUrl, "taobaocdn") == false) {
                $check = 'checked';
                break;
            }
        }
        return $check;
    }

    private function getAllParentCids($sellerCats) {
        $parentCids = array();
        $count = count($sellerCats->seller_cat);
        for ($i = 0; $i < $count; $i++) {
            $sellerCat = $sellerCats->seller_cat[$i];
            if (''.$sellerCat->parent_cid !== '0') {
                array_push($parentCids, ''.$sellerCat->parent_cid);
            }
        }
        return $parentCids;
    }

    /* 类目处理：如果有/则先拆分，去掉其中的"小" "毛针织衫变成针织衫"  "休闲套装变成套装" "短外套与毛呢外套变成外套"  ,然后逐一匹配,碰到一个则停止 */
    /* T恤  连衣裙 衬衫   牛仔裤  半身裙   小背心/小吊带  马夹   蕾丝衫/雪纺衫    毛针织衫  短外套 西装 卫衣/绒衫   毛衣  风衣 毛呢外套   棉衣/棉服 羽绒服 皮衣 皮草 中老年服装 大码女装  */
    /* 短裤/热裤  九分裤/七分裤  中裤/五分裤  棉裤/羽绒裤  打底裤 休闲裤  西装裤/正装裤 */
    /* 休闲套装  婚纱 旗袍 礼服/晚装 名族服装/舞台装 唐装/中式服装 裙子 裤子 上衣 */
    private function matchCat($cname, $sname) {
        $isMatch = false;
        if(strpos($cname,"外套") !== false) {
            $cname = "外套";
        }
        $cname = str_replace("小",'',$cname);
        if($cname == "毛针织衫") {
            $cname = "针织衫";
        }
        if(strpos($cname,"/") !== false) {
            $carray = explode("/",$cname);
            $catcnt = 2;
        } else {
            $carray[0] = $cname;
            $catcnt = 1;
        }
        for($index = 0; $index < $catcnt; $index++) {
            $pcat = '/'.$carray[$index].'/';
            preg_match($pcat, $sname, $pcatInfo);
            $pInfonum = count($pcatInfo[0]);
            if($pInfonum > 0) {
                $isMatch = true;
                break;
            }
        }
        return $isMatch;
    }

    private function makeIsUploadedBefore($outerId) {
        $items = $this->checkApiResponse(OpenAPI::getTaobaoCustomItems($outerId));
        if (count($items->item) > 0 || $this->isCurrentTaobaoItemIdInSession()) {
            return true;
        } else {
            return false;
        }
    }

    private function makeListTime($now, $date, $hour, $minute) {
        if ($now == '1') {
            return $date.' '.$hour.':'.$minute.':00';
        } else {
            return '';
        }
    }

    private function isCurrentTaobaoItemIdInSession() {
        if (session('?uploaded_taobao_item_ids')) {
            $uploadedTaobaoItemIds = session('uploaded_taobao_item_ids');
            if (in_array(session('current_taobao_item_id'), $uploadedTaobaoItemIds)) {
                return true;
            }
        }
        return false;
    }

    public function editItem2() {
        $this->assign(array(
            'taobaoItemTitle' => 'title',
            'taobaoItemId' => '12345',
            'propsHtml' => $this->makePropsHtml2(),
            'price' => '99.99',
            'rawPrice' => '80.99',
            'desc' => 'xxxxxxxxxx',
            'cid' => '11111111',
            'picUrl' => '#',
            'image0' => '#',
            'image1' => '#',
            'image2' => '#',
            'image3' => '#',
            /*'sizeType' => 0,*/
            'outerId' => '#111#sss',
            'nick' => 'liuhaicc',
            'huoHao' => 'huohao',
            /*'imgsInDesc' => $this->parseDescImages($taobaoItem->desc),*/
            'percent' => '99',
            'profit' => '2',
            'initSkus' => '',
            'propImgs' => '',
            'isUploadedBefore' => true,
            'isCurrentTaobaoItemIdInSession' => false,
            'propAlias' => '',
            'postFee' => '15.00',
            'expressFee' => '15.00',
            'emsFee' => '15.00',
            'sellerFreight' => '',
            'buyerFreight' => 'checked',
            'useModuFreight' => '',
            'postFreight' => 'checked',
            'deliveryTemplateHtml' => '<option value="1221952600" selected="">00</option><option value="1257147530">淘宝</option><option value="1212780690">xj</option>',
            'sellerCatsHtml' => '<option value="">请选择</option><option value="915062563" selected="">女装上上上</option><option value="915062564" selected="">女装下下下</option>',
            'sizePropHtml' => '<li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28415" id="prop_20518-28415"><label class="labelname" for="prop_20518-28415" title="50厘米（1尺5)">50厘米（1尺5)</label><input id="J_Alias_20518-28415" class="editbox text" maxlength="15" type="text" value="50厘米（1尺5)" name="cpva_20518:28415"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28416" id="prop_20518-28416"><label class="labelname" for="prop_20518-28416" title="52厘米( 1尺56)">52厘米( 1尺56)</label><input id="J_Alias_20518-28416" class="editbox text" maxlength="15" type="text" value="52厘米( 1尺56)" name="cpva_20518:28416"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28417" id="prop_20518-28417"><label class="labelname" for="prop_20518-28417" title="54厘米 (1尺6)">54厘米 (1尺6)</label><input id="J_Alias_20518-28417" class="editbox text" maxlength="15" type="text" value="54厘米 (1尺6)" name="cpva_20518:28417"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28418" id="prop_20518-28418"><label class="labelname" for="prop_20518-28418" title="56厘米（1尺68）">56厘米（1尺68）</label><input id="J_Alias_20518-28418" class="editbox text" maxlength="15" type="text" value="56厘米（1尺68）" name="cpva_20518:28418"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28419" id="prop_20518-28419"><label class="labelname" for="prop_20518-28419" title="58厘米 （1尺75）">58厘米 （1尺75）</label><input id="J_Alias_20518-28419" class="editbox text" maxlength="15" type="text" value="58厘米 （1尺75）" name="cpva_20518:28419"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28420" id="prop_20518-28420"><label class="labelname" for="prop_20518-28420" title="60厘米 ( 1尺8）">60厘米 ( 1尺8）</label><input id="J_Alias_20518-28420" class="editbox text" maxlength="15" type="text" value="60厘米 ( 1尺8）" name="cpva_20518:28420"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28421" id="prop_20518-28421"><label class="labelname" for="prop_20518-28421" title="62厘米（1尺85）">62厘米（1尺85）</label><input id="J_Alias_20518-28421" class="editbox text" maxlength="15" type="text" value="62厘米（1尺85）" name="cpva_20518:28421"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28422" id="prop_20518-28422"><label class="labelname" for="prop_20518-28422" title="64厘米（1尺9）">64厘米（1尺9）</label><input id="J_Alias_20518-28422" class="editbox text" maxlength="15" type="text" value="64厘米（1尺9）" name="cpva_20518:28422"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28357" id="prop_20518-28357"><label class="labelname" for="prop_20518-28357" title="66厘米（2尺）">66厘米（2尺）</label><input id="J_Alias_20518-28357" class="editbox text" maxlength="15" type="text" value="66厘米（2尺）" name="cpva_20518:28357"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28358" id="prop_20518-28358"><label class="labelname" for="prop_20518-28358" title="68厘米（2尺05）">68厘米（2尺05）</label><input id="J_Alias_20518-28358" class="editbox text" maxlength="15" type="text" value="68厘米（2尺05）" name="cpva_20518:28358"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28359" id="prop_20518-28359"><label class="labelname" for="prop_20518-28359" title="70厘米（2尺1）">70厘米（2尺1）</label><input id="J_Alias_20518-28359" class="editbox text" maxlength="15" type="text" value="70厘米（2尺1）" name="cpva_20518:28359"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28360" id="prop_20518-28360"><label class="labelname" for="prop_20518-28360" title="72厘米（2尺16）">72厘米（2尺16）</label><input id="J_Alias_20518-28360" class="editbox text" maxlength="15" type="text" value="72厘米（2尺16）" name="cpva_20518:28360"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28361" id="prop_20518-28361"><label class="labelname" for="prop_20518-28361" title="74厘米（2尺2）">74厘米（2尺2）</label><input id="J_Alias_20518-28361" class="editbox text" maxlength="15" type="text" value="74厘米（2尺2）" name="cpva_20518:28361"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28362" id="prop_20518-28362"><label class="labelname" for="prop_20518-28362" title="76厘米（2尺3）">76厘米（2尺3）</label><input id="J_Alias_20518-28362" class="editbox text" maxlength="15" type="text" value="76厘米（2尺3）" name="cpva_20518:28362"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28363" id="prop_20518-28363"><label class="labelname" for="prop_20518-28363" title="78厘米（2尺35）">78厘米（2尺35）</label><input id="J_Alias_20518-28363" class="editbox text" maxlength="15" type="text" value="78厘米（2尺35）" name="cpva_20518:28363"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28364" id="prop_20518-28364"><label class="labelname" for="prop_20518-28364" title="80厘米（2尺4）">80厘米（2尺4）</label><input id="J_Alias_20518-28364" class="editbox text" maxlength="15" type="text" value="80厘米（2尺4）" name="cpva_20518:28364"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28365" id="prop_20518-28365"><label class="labelname" for="prop_20518-28365" title="82厘米（2尺45）">82厘米（2尺45）</label><input id="J_Alias_20518-28365" class="editbox text" maxlength="15" type="text" value="82厘米（2尺45）" name="cpva_20518:28365"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28366" id="prop_20518-28366"><label class="labelname" for="prop_20518-28366" title="84厘米（2尺5）">84厘米（2尺5）</label><input id="J_Alias_20518-28366" class="editbox text" maxlength="15" type="text" value="84厘米（2尺5）" name="cpva_20518:28366"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28367" id="prop_20518-28367"><label class="labelname" for="prop_20518-28367" title="86厘米（2尺6）">86厘米（2尺6）</label><input id="J_Alias_20518-28367" class="editbox text" maxlength="15" type="text" value="86厘米（2尺6）" name="cpva_20518:28367"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28368" id="prop_20518-28368"><label class="labelname" for="prop_20518-28368" title="88厘米（2尺65）">88厘米（2尺65）</label><input id="J_Alias_20518-28368" class="editbox text" maxlength="15" type="text" value="88厘米（2尺65）" name="cpva_20518:28368"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28369" id="prop_20518-28369"><label class="labelname" for="prop_20518-28369" title="90厘米（2尺7）">90厘米（2尺7）</label><input id="J_Alias_20518-28369" class="editbox text" maxlength="15" type="text" value="90厘米（2尺7）" name="cpva_20518:28369"></li><li class="sku-item"><input type="checkbox" class="J_Checkbox" name="cp_20518" value="20518:28370" id="prop_20518-28370"><label class="labelname" for="prop_20518-28370" title="92厘米（2尺75）">92厘米（2尺75）</label><input id="J_Alias_20518-28370" class="editbox text" maxlength="15" type="text" value="92厘米（2尺75）" name="cpva_20518:28370"></li>',
            'salePropsObject' => '',
            'movePic' => 'checked',
            'isSubscribe' => true,
            'otherStoreSessions' => array(
                array(
                    'nick' => '老副',
                ),
                array(
                    'nick' => '副老',
                ),
            ),
        ));
        $this->display();
    }

    private function makePropsHtml2($props, $propsName) {
        $count = count($props->item_prop);
        $html = '';
        for ($i = 0; $i < $count; $i++) {
            $prop = $props->item_prop[$i];
            if ($this->isSaleProp($prop) || ''.$prop->pid == '13021751') continue;
            $html .= '<li>';
            $html .= '<div><label>'.$prop->name.':</label></div>';
            $html .= '<select name="cp_'.$prop->pid.'" id="prop_'.$prop->pid.'">';
            $html .= '<option value=""></option>';
            $hasSelected = false;
            if (isset($prop->prop_values)) {
                $valueCount = count($prop->prop_values->prop_value);
                for ($j = 0; $j < $valueCount; $j++) {
                    $value = $prop->prop_values->prop_value[$j];
                    $optionValue = $prop->pid.':'.$value->vid;
                    if (strpos($propsName, $optionValue) !== false || ($j == $valueCount - 1 && !$hasSelected && ''.$prop->must == 'true')) {
                        $selected = 'selected';
                        $hasSelected = true;
                    } else {
                        $selected = '';
                    }
                    $html .= '<option value="'.$optionValue.'" '.$selected.'>'.$value->name.'</option>';
                }
            }
            $html .= '</select>';
            $html .= '</li>';
        }
        //return $html;
        return '<li><div><label>主图来源:</label></div><select name="cp_123123"><option value="222222:2222"></option></select></li>
                <li><div><label>风格:</label></div><select name="cp_321321"><option value="111:1111"></select></li>';
    }

    public function uploadItem2() {
        header("Content-type:text/html;charset=utf-8");
        dump($_REQUEST);
    }
}