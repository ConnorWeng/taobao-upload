<?php

import('@.Model.Sku');

class Util {

    public static function parseSkus($skus) {
        $parsedSkus = array();
        $count = count($skus);
        for ($i = 0; $i < $count; $i += 1) {
            array_push($parsedSkus, new Sku(self::extractValue($skus[$i]->properties_name->asXML()),
                self::extractValue($skus[$i]->price->asXML()),
                self::extractValue($skus[$i]->quantity->asXML())));
        }
        return $parsedSkus;
    }

    public static function extractValue($xml) {
        $s1 = substr($xml, stripos($xml, '>') + 1);
        $s2 = substr($s1, 0, stripos($s1, '<'));
        return $s2;
    }

    public static function check($v) {
        if ($v == 'on') {
            return 'true';
        } else {
            return 'false';
        }
    }

    public static function changeTaoAppkey($taobaoItemId, $oldAppKey = null) {
        $taoapi = D('Taoapi');
        $taoappkey = $taoapi->getAppKey($taobaoItemId, $oldAppKey);
        session('taobao_app_key', $taoappkey['appkey']);
        session('taobao_secret_key', $taoappkey['appsecret']);
        session('current_taobao_app_key_id', $taoappkey['id']);
    }

    public static function downloadImage($picUrl) {
        $tmpFile = APP_PATH.'Upload/'.uniqid().'.jpg';
        $content = file_get_contents($picUrl);
        file_put_contents($tmpFile, $content);
        $filesize = filesize($tmpFile);
        if ($filesize > 512000) {
            import('ORG.Util.Image');
            $newTmpFile = APP_PATH.'Upload/'.uniqid().'.jpg';
            Image::thumb($tmpFile, $newTmpFile, 'jpg', 250, 250);
            unlink($tmpFile);
            return $newTmpFile;
        }
        return $tmpFile;
    }
}

?>