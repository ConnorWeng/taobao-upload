<?php
import('@.Util.OpenAPI');

class ApiAction extends CommonAction {
    public function makePropsHtml() {
        $cid = I('cid');
        $upload = new UploadAction();
        $props = OpenAPI::getTaobaoItemPropsWithoutVerify($cid);
        $html = $upload->makePropsHtml($props, '', $cid, false);
        $this->ajaxReturn($html);
    }
}