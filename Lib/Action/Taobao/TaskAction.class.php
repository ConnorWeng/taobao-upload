<?php
import('@.Util.OpenAPI');

class TaskAction extends CommonAction {
    public function updateRealPicToday() {
        $goodsModel = M('Goods');
        $todayTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $goods = $goodsModel->where('add_time > '.$todayTime.' and realpic = 0')->select();
        foreach ($goods as $good) {
            $goodId = $good['goods_id'];
            $numIid = $this->fetchNumIidFromUrl($good['good_http']);
            $taobaoItem = OpenAPI::getTaobaoItemWithoutVerify($numIid);
            $realpicProp = strpos($taobaoItem->props, '157305307');
            $realpicName = strpos($taobaoItem->title, '实拍');
            $realpic = $realpicProp | $realpicName ? 1 : 0;
            $effect = $goodsModel->where('goods_id='.$goodId)->setField('realpic', $realpic);
            if ($effect > 0) {
                dump($goodId.': is realpic!');
            }
        }
    }

    private function fetchNumIidFromUrl($url) {
        $regex = '/id=(\d+)/';
        preg_match($regex, $url, $matches);
        if ($matches) {
            return $matches[1];
        } else {
            return -1;
        }
    }
}