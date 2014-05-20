<?php
import('@.Util.Util');
import('@.Util.OpenAPI');

class UploadAction extends CommonAction {
    public function selectCategory() {
        $this->display();
    }

    public function editItem() {
        header("Content-type:text/html;charset=utf-8");
        $taobaoItemId = session('current_taobao_item_id');
        $nick = session('taobao_user_nick');
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
            'autoOffWarn' => $userdata['autoOffWarn'] == 1 ? 'checked' : '',
            'initSkus' => json_encode(Util::parseSkus($taobaoItem->skus->sku)),
            'propImgs' => $propImgs,
            'isUploadedBefore' => $isUploadedBefore,
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
        ));
        $this->display();
    }

    public function uploadItem() {
        header("Content-type:text/html;charset=utf-8");
        $imagePath = Util::downloadImage(I('picUrl1'));
        $image = '@'.$imagePath;
        $skuTableData = json_decode($_REQUEST['J_SKUTableData']);
        $salePropsObject = json_decode(urldecode($_REQUEST['salePropsObject']));
        $autoOffWarn = I('autoOffWarn') == 'on' ? true : false;
        $desc = $this->makeDesc($_REQUEST['_fma_pu__0_d'], session('current_taobao_item_id'), $autoOffWarn);
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
            'ApproveStatus' => 'onsale',
            'Props' => $this->makeProps($_REQUEST, $skuTableData, I('sizeType')),
            'FreightPayer' => I('postages'),
            'PostageId' => I('template_id'),
            'ValidThru' => '14',
            'HasInvoice' => 'true',
            'HasWarranty' => 'true',
            'HasShowcase' => 'false',
            'SellerCids' => I('sellerCats'),
            'HasDiscount' => 'false',
            'ListTime' => '',
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
            'OuterId' => '',
            'mainpic' => 'http://yjsc.51zwd.com/taobao-upload/'.$_REQUEST['picUrl1'],
        );
        if (I('movePic') == 'on') {
            $numIid = $this->checkApiResponse(OpenAPI::addTaobaoItemWithMovePic($item));
        } else {
            $uploadedItem = $this->checkApiResponse(OpenAPI::addTaobaoItem($item));
            $numIid = $uploadedItem->num_iid;
            if (isset($numIid)) {
                $this->uploadItemImages((float)$numIid, $_REQUEST);
                $this->uploadPropImages((float)$numIid, json_decode(urldecode(I('propImgs'))));
            }
        }
        unlink($imagePath);
        if (isset($numIid)) {
            $itemUrl = 'http://item.taobao.com/item.htm?spm=686.1000925.1000774.13.Odmgnd&id='.$numIid;
            $this->assign(array(
                'result' => '发布成功啦！',
                'message' => '宝贝已经顺利上架哦！亲，感谢你对51网的大力支持！',
                'itemUrl' => '<li><a href="'.$itemUrl.'">来看看刚上架的宝贝吧！</a></li>',
                'error' => 'false',
            ));
        } else {
            $this->assign(array(
                'result' => '发布失败！',
                'message' => '宝贝没有顺利上架，请不要泄气哦，换个宝贝试试吧！祝生意欣荣，财源广进！',
                'itemUrl' => '',
                'error' => 'true',
            ));
        }
        $this->display();
    }

    public function saveConfig() {
        $data = array(
            'profit0' => I('percent'),
            'profit' => I('profit'),
            'autoOffWarn' => I('autoOffWarn') == 'checked' ? 1 : 0,
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
            $skuQuantities .= $value->quantity.',';
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
            $data['autoOffWarn'] = '1';
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
            if ($this->isSaleProp($prop)) continue;
            $html .= '<li class="J_spu-property" id="spu_'.$prop->pid.'">';
            $html .= '<label class="label-title">'.$prop->name.':</label>';
            $html .= '<span><ul class="J_ul-single ul-select"><li>';
            $html .= '<select name="cp_'.$prop->pid.'" id="prop_'.$prop->pid.'">';
            $html .= '<option value=""></option>';
            if (isset($prop->prop_values)) {
                $valueCount = count($prop->prop_values->prop_value);
                for ($j = 0; $j < $valueCount; $j++) {
                    $value = $prop->prop_values->prop_value[$j];
                    $optionValue = $prop->pid.':'.$value->vid;
                    if (strpos($propsName, $optionValue) !== false) {
                        $selected = 'selected';
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

    private function makeDesc($desc, $taobaoItemId, $autoOffWarn) {
        $newDesc = $desc;
        if ($autoOffWarn) {
            $encNumIid = '51chk'.base64_encode($taobaoItemId);
            $autoOffJpg = 'http://51wangpi.com/'.$encNumIid.'.jpg';
            $autoOffWarnHtml = '<img align="middle" src="'.$autoOffJpg.'"/><br/>';
            if (get_magic_quotes_gpc() == 0) {
                $autoOffWarnHtml = addslashes(addslashes($autoOffWarnHtml));
            } else {
                $autoOffWarnHtml = addslashes($autoOffWarnHtml);
            }
            $newDesc = $autoOffWarnHtml.$newDesc;
        }
        return $newDesc;
    }

    private function uploadItemImages($numIid, $request) {
        for ($i = 2; $i <= 5; $i++) {
            $picUrl = $request['picUrl'.$i];
            if ($picUrl != '') {
                $picPath = Util::downloadImage($picUrl);
                $pic = '@'.$picPath;
                $itemImg = $this->checkApiResponse(OpenAPI::uploadTaobaoItemImg($numIid, $pic, $i - 1));
                unlink($picPath);
            }
        }
    }

    private function uploadPropImages($numIid, $propImgs) {
        foreach ($propImgs as $propImg) {
            $imagePath = Util::downloadImage($propImg->url);
            $image = '@'.$imagePath;
            $this->checkApiResponse(OpenAPI::uploadTaobaoItemPropImg($numIid, $propImg->properties, $image, $propImg->position));
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
        if (strpos($desc, "taobaocdn.com") !== false) {
            return 'checked';
        } else {
            return '';
        }
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
        if (count($items->item) > 0) {
            return true;
        } else {
            return false;
        }
    }
}