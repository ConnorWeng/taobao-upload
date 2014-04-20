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
        dump($taobaoItem);
        $taobaoItemProps = $this->checkApiResponse(OpenAPI::getTaobaoItemProps($taobaoItem->cid));
        dump($taobaoItemProps);
        $this->display();
    }
}