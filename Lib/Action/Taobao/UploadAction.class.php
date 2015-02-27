<?php
import('@.Util.Util');
import('@.Util.OpenAPI');
import('@.Model.StoreSession');

class UploadAction extends CommonAction {
    public function selectCategory() {
        Util::changeDatabaseAccordingToSession();
        $this->display();
    }

    public function editItem() {
        Util::changeDatabaseAccordingToSession();
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

        if (session('?current_goods_id')) {
            $taobaoItem = OpenAPI::getTaobaoItemFromDatabase(session('current_goods_id'));
        } else {
            $taobaoItem = $this->checkApiResponse(OpenAPI::getTaobaoItem($taobaoItemId));
        }
        $props = $this->checkApiResponse(OpenAPI::getTaobaoItemProps($taobaoItem->cid));
        $cname = $this->checkApiResponse(OpenAPI::getTaobaoItemCat($taobaoItem->cid));
        $deliveryTemplates = $this->checkApiResponse(OpenAPI::getTaobaoDeliveryTemplates());
        $userdata = $this->makeUserdata($nick, $deliveryTemplates);
        $images = $this->makeImages($taobaoItem->item_imgs);
        $propsHtml = $this->makePropsHtml($props, $taobaoItem->props_name, $taobaoItem->cid);
        $sizeType = $this->makeSizeType($props);
        $colorPropHtml = $this->makeColorPropHtml($props);
        $sizePropHtml = $this->makeSizePropHtml($props);
        $salePropsObject = $this->makeSalePropsObject($props);
        $title = Util::makeTitle($taobaoItem->title);
        $storeInfo = $this->getStoreInfo($taobaoItem);
        $price = Util::makePrice($taobaoItem->price, $storeInfo['see_price'], $taobaoItem->title);
        $caculatedPrice = $this->caculatePrice($price, $userdata['profit0'], $userdata['profit']);
        $outerId = $this->makeOuterId($taobaoItem->title, $taobaoItem->price, $storeInfo, $taobaoItem->props_name);
        $isUploadedBefore = $this->makeIsUploadedBefore($outerId);
        $propImgs = urlencode($this->makePropImgs($taobaoItem->prop_imgs->prop_img));
        $deliveryTemplateHtml = $this->makeDeliveryTemplateHtml($deliveryTemplates, $userdata['usePostModu']);
        $sellerCatsHtml = $this->makeSellerCatsHtml($cname);
        $movePic = $this->makeMovePic($taobaoItem->desc);
        $isDelist = $this->makeIsDelist($taobaoItem->delist_time);
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
            'huoHao' => Util::getHuoHao($taobaoItem->title, $taobaoItem->props_name),
            'imgsInDesc' => $this->parseDescImages($taobaoItem->desc),
            'percent' => $userdata['profit0'],
            'profit' => $userdata['profit'],
            'initSkus' => json_encode(Util::parseSkus($taobaoItem->skus)),
            'propImgs' => $propImgs,
            'isUploadedBefore' => $isUploadedBefore,
            'isCurrentTaobaoItemIdInSession' => $isCurrentTaobaoItemIdInSession,
            'isDelist' => $isDelist,
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
            'colorPropHtml' => $colorPropHtml,
            'sizePropHtml' => $sizePropHtml,
            'salePropsObject' => urlencode(json_encode($salePropsObject)),
            'movePic' => $movePic,
            'isSubscribe' => $isSubscribe,
            'otherStoreSessions' => $storeSession->getAllStoreSessionsArray(),
        ));
        $this->display();
    }

    public function uploadItem() {
        Util::changeDatabaseAccordingToSession();
        header("Content-type:text/html;charset=utf-8");
        $imagePath = Util::downloadImage(I('picUrl1'));
        $image = '@'.$imagePath;
        $skuTableData = json_decode($_REQUEST['J_SKUTableData']);
        $salePropsObject = json_decode(urldecode($_REQUEST['salePropsObject']));
        $desc = $this->makeDesc($_REQUEST['_fma_pu__0_d'], session('current_taobao_item_id'));
        $item = array(
            'Num' => I('num'),
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
            'InputStr' => I('band') == '' ? '' : I('band'),
            'InputPids' => I('band') == '' ? '' : '20000',
            'SkuProperties' => $this->makeSkuProperties($skuTableData),
            'SkuQuantities' => $this->makeSkuQuantities($skuTableData),
            'SkuPrices' => $this->makeSkuPrices($skuTableData),
            'SkuOuterIds' => $this->makeSkuOuterIds($skuTableData),
            'OuterId' => I('_fma_pu__0_o'),
            'ItemWeight' => '0.5',
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
        Util::changeDatabaseAccordingToSession();
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
            $skuOuterIds .= ' , ';
        }
        return $skuOuterIds = substr($skuOuterIds, 0, strlen($skuOuterIds) - 2);
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

    public function makePropsHtml($props, $propsName, $cid, $needVerify = true) {
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
            $hasChildProps = false;
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
                    $isParent = isset($value->is_parent) ? ' parent="true" vid="'.$value->vid.'"' : '';
                    if ($isParent != '') {
                        $hasChildProps = true;
                    }
                    $html .= '<option value="'.$optionValue.'" '.$selected.$isParent.'>'.$value->name.'</option>';
                }
            }
            $html .= '</select>';
            if ($hasChildProps) {
                if ($needVerify) {
                    $childProps = $this->checkApiResponse(OpenAPI::getTaobaoItemProps($cid, $prop->pid));
                } else {
                    $childProps = OpenAPI::getTaobaoItemPropsWithoutVerify($cid, $prop->pid);
                }
                $childCount = count($childProps->item_prop);
                for ($j = 0; $j < $childCount; $j++) {
                    $childProp = $childProps->item_prop[$j];
                    $html .= '<select class="child_prop" style="display:none" parent="'.$prop->pid.':'.$childProp->parent_vid.'" name="cp_'.$childProp->pid.'" id="prop_'.$childProp->pid.'">';
                    $childValueCount = count($childProp->prop_values->prop_value);
                    for ($k = 0; $k < $childValueCount; $k++) {
                        $childValue = $childProp->prop_values->prop_value[$k];
                        $childOptionValue = $childProp->pid.':'.$childValue->vid;
                        $html .= '<option value="'.$childOptionValue.'">'.$childValue->name.'</option>';
                    }
                    $html .= '</select>';
                }
            }
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

    private function makeColorPropHtml($props) {
        $colorPropHtml = '';
        $colorArray = array(
            '乳白色' => 'rgb(255, 251, 240)',
            '白色' => 'rgba(0, 0, 0, 0)',
            '米白色' => 'rgb(238, 222, 176)',
            '浅灰色' => 'rgb(228, 228, 228)',
            '深灰色' => 'rgb(102, 102, 102)',
            '灰色' => 'rgb(128, 128, 128)',
            '银色' => 'rgb(192, 192, 192)',
            '黑色' => 'rgb(0, 0, 0)',
            '桔红色' => 'rgb(255, 117, 0)',
            '玫红色' => 'rgb(223, 27, 118)',
            '粉红色' => 'rgb(255, 182, 193)',
            '红色' => 'rgb(255, 0, 0)',
            '藕色' => 'rgb(238, 208, 216)',
            '西瓜红' => 'rgb(240, 86, 84)',
            '酒红色' => 'rgb(153, 0, 0)',
            '卡其色' => 'rgb(195, 176, 145)',
            '姜黄色' => 'rgb(255, 199, 115)',
            '明黄色' => 'rgb(255, 255, 1)',
            '杏色' => 'rgb(247, 238, 214)',
            '柠檬黄' => 'rgb(255, 236, 67)',
            '桔色' => 'rgb(255, 165, 0)',
            '浅黄色' => 'rgb(250, 255, 114)',
            '荧光黄' => 'rgb(234, 255, 86)',
            '金色' => 'rgb(255, 215, 0)',
            '香槟色' => 'rgba(0, 0, 0, 0)',
            '黄色' => 'rgb(255, 255, 0)',
            '军绿色' => 'rgb(93, 118, 42)',
            '墨绿色' => 'rgba(0, 0, 0, 0)',
            '浅绿色' => 'rgb(152, 251, 152)',
            '绿色' => 'rgb(0, 128, 0)',
            '翠绿色' => 'rgb(10, 163, 68)',
            '荧光绿' => 'rgb(35, 250, 7)',
            '青色' => 'rgb(0, 224, 158)',
            '天蓝色' => 'rgb(68, 206, 246)',
            '孔雀蓝' => 'rgb(0, 164, 197)',
            '宝蓝色' => 'rgb(75, 92, 196)',
            '浅蓝色' => 'rgb(210, 240, 244)',
            '深蓝色' => 'rgb(4, 22, 144)',
            '湖蓝色' => 'rgb(48, 223, 243)',
            '蓝色' => 'rgb(0, 0, 254)',
            '藏青色' => 'rgb(46, 78, 126)',
            '浅紫色' => 'rgb(237, 224, 230)',
            '深紫色' => 'rgb(67, 6, 83)',
            '紫红色' => 'rgb(139, 0, 98)',
            '紫罗兰' => 'rgb(183, 172, 228)',
            '紫色' => 'rgb(128, 0, 128)',
            '咖啡色' => 'rgb(96, 57, 18)',
            '巧克力色' => 'rgb(210, 105, 30)',
            '栗色' => 'rgb(96, 40, 30)',
            '浅棕色' => 'rgb(179, 92, 68)',
            '深卡其布色' => 'rgb(189, 183, 107)',
            '深棕色' => 'rgb(124, 75, 0)',
            '褐色' => 'rgb(133, 91, 0)',
            '驼色' => 'rgb(168, 132, 98)',
            '花色' => 'rgba(0, 0, 0, 0)',
            '透明' => 'rgba(0, 0, 0, 0)');
        $count = count($props->item_prop);
        for ($i = 0; $i < $count; $i++) {
            $prop = $props->item_prop[$i];
            if ($this->isSaleProp($prop) && strpos(''.$prop->name, '颜色') !== false) {
                $valueCount = count($prop->prop_values->prop_value);
                for ($j = 0; $j < $valueCount; $j++) {
                    $value = $prop->prop_values->prop_value[$j];
                    $colorPropHtml .= '<li class="sku-item">';
                    $colorPropHtml .= '<input type="checkbox" class="J_Checkbox" name="cp_'.$prop->pid.'" value="'.$prop->pid.':'.$value->vid.'" id="prop_'.$prop->pid.'-'.$value->vid.'" data-color="'.$colorArray[''.$value->name].'" data-path="" data-thumb="">';
                    $colorPropHtml .= '<label class="color-lump" style="background:'.$colorArray[''.$value->name].';" for="prop_'.$prop->pid.'-'.$value->vid.'"></label>';
                    $colorPropHtml .= '<label class="labelname" for="prop_'.$prop->pid.'-'.$value->vid.'" title="'.$value->name.'">'.$value->name.'</label>';
                    $colorPropHtml .= '<input id="J_Alias_'.$prop->pid.'-'.$value->vid.'" class="editbox text" maxlength="15" type="text" value="'.$value->name.'" name="cpva_'.$prop->pid.':'.$value->vid.'">';
                    $colorPropHtml .= '</li>';
                }
            }
        }
        return $colorPropHtml;
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

    public function getStoreInfo($taobaoItem) {
        $store = M('store');
        $isNewVersion = isset($taobaoItem->store_id);
        if ($isNewVersion) { // from ecmall database
            $storeId = $taobaoItem->store_id;
        } else { // old version
            $rs = $store->query("select store_id from ecm_goods where good_http='http://item.taobao.com/item.htm?id=".session('current_taobao_item_id')."'");
            $storeId = $rs[0]['store_id'];
        }
        $storeInfo = $store->where('store_id='.$storeId)->find();
        $storeInfo['address'] = $isNewVersion ? $storeInfo['dangkou_address'] : $storeInfo['address'];
        return $storeInfo;
    }

    public function makeOuterId($title, $rawPrice, $storeInfo, $propsName = null) {
        $seller = $storeInfo['shop_mall'].$storeInfo['address'];
        $price = Util::makePrice($rawPrice, $storeInfo['see_price'], $title);
        $huoHao = Util::getHuoHao($title, $propsName);
        return $outerId = $seller.'_P'.$price.'_'.$huoHao.'#';
    }

    private function caculatePrice($price, $percent, $profit) {
        return floatval($price) * (floatval($percent) / 100.00) + floatval($profit);
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
        $sellerCatsHtml = '<option value="">请选择</option>';
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
        $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.GIF|\.jpg|\.JPG]))[\'|\"].*?[\/]?>/";
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

    private function makeIsDelist($delistTimeStr) {
        $delistTime = strtotime($delistTimeStr);
        $nowTimeStr = date("Y-m-d H:i:s", time());
        $nowTime = strtotime($nowTimeStr);
        if ($delistTime < $nowTime) {
            return true;
        } else {
            return false;
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
}