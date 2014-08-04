<?php

vendor('QQBUYPHPSDKV2.src.PaiPaiOpenApiOauth');
vendor('taobao-sdk.TopSdk');

class PaipaiOpenAPI {

    private static function getSdk() {
        $sdk = new PaiPaiOpenApiOauth(C('appOAuthId'), C('appOAuthKey'), session('paipai_access_token'), session('uin'));
        $sdk->setDebugOn(false);
        $sdk->setCharset('utf-8');
        $sdk->setMethod('post');
        $sdk->setFormat('json');

        return $sdk;
    }

    private static function invoke($sdk) {
        try {
            $response = $sdk->invoke();
            return json_decode($response);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getNavigationChildList($navigationId) {
        $sdk = self::getSdk();
        $sdk->setApiPath('/attr/getNavigationChildList.xhtml');
        $params = &$sdk->getParams();
        $params['pureData'] = 1;
        $params['navigationId'] = $navigationId;

        return self::invoke($sdk);
    }

    public static function getAttributeList($navigationId) {
        $sdk = self::getSdk();
        $sdk->setApiPath('/attr/getAttributeList.xhtml');
        $params = &$sdk->getParams();
        $params['pureData'] = 1;
        $params['classId'] = $navigationId;
        $params['option'] = 1;

        return self::invoke($sdk);
    }

    public static function addItem($itemAttrs) {
        $sdk = self::getSdk();
        $sdk->setApiPath('/item/addItem.xhtml');
        $params = &$sdk->getParams();
        $params['pureData'] = 1;

        foreach ($itemAttrs as $key => $val) {
            $params[$key] = $val;
        }

        return self::invoke($sdk);
    }

    public static function modifyItemPic($uin, $itemCode, $index, $pic) {
        $sdk = self::getSdk();
        $sdk->setApiPath('/item/modifyItemPic.xhtml');
        $params = &$sdk->getParams();
        $params["appOAuthID"] = $sdk->getAppOAuthID();
        $params["accessToken"] = $sdk->getAccessToken();
        $params["uin"] = $sdk->getUin();
        $params["format"] = $sdk->getFormat();
        $params["charset"] = $sdk->getCharset();
        $params['pureData'] = 1;
        $params['sellerUin'] = $uin;
        $params['itemCode'] = $itemCode;
        $params['index'] = $index;
        $sign = $sdk->makeSign($sdk->getMethod(), $sdk->getAppOAuthkey()."&");
        $params['sign'] = $sign;

        $url = 'http://'.$sdk->getHostName().$sdk->getApiPath().'?charset='.$params['charset'].'&';
        unset($params['charset']);

        $params['pic'] = $pic;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        curl_close($ch);

        return json_decode($data);
    }

    public static function uploadItemStockImage($itemCode, $pic) {
        $sdk = self::getSdk();
        $sdk->setApiPath('/item/uploadItemStockImage.xhtml');
        $params = &$sdk->getParams();
        $params['pureData'] = 1;
        $params['itemCode'] = $itemCode;
        $params['pic'] = $pic;

        return self::invoke($sdk);
    }

    public static function uploadPaiPaiAlbumImage($path, $pic, $fileName) {
        $sdk = self::getSdk();
        $sdk->setApiPath('/album/uploadPaiPaiAlbumImage.xhtml');
        $params = &$sdk->getParams();
        $params["appOAuthID"] = $sdk->getAppOAuthID();
        $params["accessToken"] = $sdk->getAccessToken();
        $params["uin"] = $sdk->getUin();
        $params["format"] = $sdk->getFormat();
        $params["charset"] = $sdk->getCharset();
        $params['pureData'] = 1;
        $params['path'] = $path;
        $params['fileName'] = $fileName;
        $sign = $sdk->makeSign($sdk->getMethod(), $sdk->getAppOAuthkey()."&");
        $params['sign'] = $sign;
        $url = 'http://'.$sdk->getHostName().$sdk->getApiPath().'?charset='.$params['charset'].'&';
        unset($params['charset']);
        $params['pic'] = $pic;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        curl_close($ch);

        return json_decode($data);
    }

    public static function getPaiPaiAlbumList() {
        $sdk = self::getSdk();
        $sdk->setApiPath('/album/getPaiPaiAlbumList.xhtml');
        $params = &$sdk->getParams();
        $params['pureData'] = 1;
        $params['path'] = '/';
        return self::invoke($sdk);
    }

    public static function createPaiPaiAlbumDir() {
        $sdk = self::getSdk();
        $sdk->setApiPath('/album/createPaiPaiAlbumDir.xhtml');
        $params = &$sdk->getParams();
        $params['pureData'] = 1;
        $params['path'] = '/';
        $params['newDirName'] = 'paipai-upload-51zwd';
        return self::invoke($sdk);
    }
}

?>