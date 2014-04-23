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
            'Props' => $this->makeProps($_REQUEST),
            'FreightPayer' => 'seller',
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
            'PostageId' => null,
            'PropertyAlias' => null,
            'InputStr' => null,
            'InputPids' => null,
            'SkuProperties' => $this->makeSkuProperties($skuTableData),
            'SkuQuantities' => $this->makeSkuQuantities($skuTableData),
            'SkuPrices' => $this->makeSkuPrices($skuTableData),
            'OuterId' => null,
        );
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

    private function makeProps($request) {
        $propsArray = array();
        foreach ($request as $key => $value) {
            if (strpos($key, 'cp_') !== false && $value !== '') {
                array_push($propsArray, $value);
            }
        }
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

    private function isSaleProp($prop) {
        if (''.$prop->is_sale_prop === 'true') return true;
        return false;
    }
}