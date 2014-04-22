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
        $propsHtml = $this->makePropsHtml($taobaoItem->cid, $taobaoItem->props_name);
        $this->assign(array(
            'taobaoItemTitle' => $taobaoItem->title,
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
        ));
        $this->display();
    }

    public function uploadItem() {
        header("Content-type:text/html;charset=utf-8");
        dump($_REQUEST);
        $item = array(
            'num' => '30',
            'price' => I('_fma_pu__0_m'),
            'type' => 'fixed',
            'stuffStatus' => 'new',
            'title' => I('_fma_pu__0_ti'),
            'desc' => I('_fma_pu__0_d'),
            'locationState' => I('_fma_pu__0_po_place'),
            'locationCity' => I('_fma_pu__0_po_city'),
            'cid' => I('cid'),
            'approveStatus' => 'onsale',
            'props' => $this->makeProps($_REQUEST),
            'freightPayer' => 'seller',
            'validThru' => '14',
            'hasInvoice' => 'true',
            'hasWarranty' => 'true',
            'hasShowcase' => 'false',
            'sellerCids' => null,
            'hasDiscount' => 'false',
            'postFee' => '5.07',
            'expressFee' => '15.07',
            'emsFee' => '25.07',
            'listTime' => null,
            'image' => null,
            'postageId' => null,
            'propertyAlias' => null,
            'inputStr' => null,
            'inputPids' => null,
            'skuProperties' => null,
            'skuQuantities' => null,
            'skuPrices' => null,
            'skuOuterIds' => null,
            'outerId' => null,
        );
        $uploadedItem = $this->checkApiResponse(OpenAPI::addTaobaoItem($item));
        dump($uploadedItem);
    }

    private function makeProps($request) {
        $propsArray = array();
        foreach ($request as $key => $value) {
            if (strpos($key, 'cp_') !== false && $value !== '') {
                array_push($propsArray, $value);
            }
        }
        dump($propsArray);
        return implode(';', $propsArray);
    }

    private function makeImages($itemImgs) {
        $images = array(null, null, null, null, null);
        for ($i = 0; $i < count($itemImgs->item_img); $i++) {
            $images[$i] = $itemImgs->item_img[$i]->url;
        }
        return $images;
    }

    private function makePropsHtml($cid, $propsName) {
        $props = $this->checkApiResponse(OpenAPI::getTaobaoItemProps($cid));
        $count = count($props->item_prop);
        $html = '';
        for ($i = 0; $i < $count; $i++) {
            $prop = $props->item_prop[$i];
            if ($this->isSpecProp($prop)) continue;
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

    private function isSpecProp($prop) {
        if ($prop->pid == '1627207') return true;
        if ($prop->pid == '20509') return true;
        return false;
    }
}