<?php
import('@.Util.Util');
import('@.Util.OpenAPI');

class UploadAction extends CommonAction {
    public function selectCategory() {
        $this->display();
    }

    public function editItem() {
        header("Content-type:text/html;charset=utf-8");
        $taobaoItemId = I('taobaoItemId');
        Util::changeTaoAppkey($taobaoItemId);
        $taobaoItem = $this->checkApiResponse(OpenAPI::getTaobaoItem($taobaoItemId));
        $images = $this->makeImages($taobaoItem->item_imgs);
        $props = $this->checkApiResponse(OpenAPI::getTaobaoItemProps($taobaoItem->cid));
        $propsHtml = $this->makePropsHtml($props, $taobaoItem->props_name);
        $sizeType = $this->makeSizeType($props);
        $title = $this->makeTitle($taobaoItem->title);
        /* $storeInfo = $this->getStoreInfo($taobaoItem->nick); */
        /* $outerId = $this->makeOuterId($taobaoItem->title, $storeInfo['see_price']); */
        $this->assign(array(
            'taobaoItemTitle' => $title,
            'propsHtml' => $propsHtml,
            'price' => $taobaoItem->price,
            'desc' => $taobaoItem->desc,
            'cid' => $taobaoItem->cid,
            'picUrl' => $taobaoItem->pic_url,
            'image0' => $images[0],
            'image1' => $images[1],
            'image2' => $images[2],
            'image3' => $images[3],
            'image4' => $images[4],
            'sizeType' => $sizeType,
        ));
        $this->display();
    }

    public function uploadItem() {
        header("Content-type:text/html;charset=utf-8");
        dump($_REQUEST);
        dump(session('taobao_access_token'));
        $image = '@'.Util::downloadImage(I('picUrl1'));
        $skuTableData = json_decode($_REQUEST['J_SKUTableData']);
        $item = array(
            'Num' => '30',
            'Price' => I('_fma_pu__0_m'),
            'Type' => 'fixed',
            'StuffStatus' => 'new',
            'Title' => I('_fma_pu__0_ti'),
            'Desc' => $_REQUEST['_fma_pu__0_d'],
            'LocationState' => I('_fma_pu__0_po_place'),
            'LocationCity' => I('_fma_pu__0_po_city'),
            'Cid' => I('cid'),
            'ApproveStatus' => 'onsale',
            'Props' => $this->makeProps($_REQUEST, $skuTableData, I('sizeType')),
            'FreightPayer' => I('postages'),
            'ValidThru' => '14',
            'HasInvoice' => 'true',
            'HasWarranty' => 'true',
            'HasShowcase' => 'false',
            'SellerCids' => null,
            'HasDiscount' => 'false',
            'PostFee' => null,
            'ExpressFee' => null,
            'EmsFee' => null,
            'ListTime' => null,
            'Image' => $image,
            'PostFee' => I('post_fee'),
            'ExpressFee' => I('express_fee'),
            'EmsFee' => I('ems_fee'),
            'PropertyAlias' => null,
            'InputStr' => null,
            'InputPids' => null,
            'SkuProperties' => $this->makeSkuProperties($skuTableData),
            'SkuQuantities' => $this->makeSkuQuantities($skuTableData),
            'SkuPrices' => $this->makeSkuPrices($skuTableData),
            'SkuOuterIds' => $this->makeSkuOuterIds($skuTableData),
            'OuterId' => null,
        );
        dump($item);
        $uploadedItem = $this->checkApiResponse(OpenAPI::addTaobaoItem($item));
        dump($uploadedItem);
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

    /* 0:20509 ����, 1:20518 �ߴ� */
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

    private function makeOuterId($title, $seePrice) {

    }

    private function makeTitle($title) {
        $huoHao = $this->getHuoHao($title);
        $newTitle = str_replace('���', '',
                                str_replace('*', '',
                                            str_replace('#', '',
                                                        str_replace($huoHao, '', $title))));
        return trim($newTitle);
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
}