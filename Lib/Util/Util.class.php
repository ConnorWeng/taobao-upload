<?php

import('@.Util.Config');
import('@.Model.Sku');

class Util {

    public static function sign($appKey, $appSecret, $url, $apiInfo, $params) {
        ksort($params);
        foreach ($params as $key=>$val) {
            $signStr .= $key . $val;
        }
        $signStr = $apiInfo . $signStr;
        $codeSign = strtoupper(bin2hex(hash_hmac("sha1", $signStr, $appSecret, true)));

        return $codeSign;
    }

    public static function signDefault($url, $api, $params) {
        return self::sign(session('alibaba_app_key'), session('alibaba_secret_key'), $url, $api . '/' . session('alibaba_app_key'), $params);
    }

    public static function getAlibabaAuthUrl($state) {
        $appKey = session('alibaba_app_key');
        $appSecret = session('alibaba_secret_key');
        $redirectUrl = urlencode(C('host').U(C('redirect_uri')));
        $stateEncoded = urlencode($state);

        $code_arr = array(
            'client_id' => $appKey,
            'site' => 'china',
            'redirect_uri' => C('host').U(C('redirect_uri')),
            'state' => $state);
        ksort($code_arr);
        foreach ($code_arr as $key=>$val)
                $sign_str .= $key . $val;
        $code_sign = strtoupper(bin2hex(hash_hmac("sha1", $sign_str, $appSecret, true)));

        $get_code_url = "http://gw.open.1688.com/auth/authorize.htm?client_id={$appKey}&site=china&state={$stateEncoded}&redirect_uri={$redirectUrl}&_aop_signature={$code_sign}";

        return $get_code_url;
    }

    public static function getTokens($code) {
        $url = 'https://gw.open.1688.com/openapi/http/1/system.oauth2/getToken/'.session('alibaba_app_key').
            '?grant_type=authorization_code&need_refresh_token=true&client_id='.session('alibaba_app_key').
            '&client_secret='.session('alibaba_secret_key').'&redirect_uri='.urlencode(C('host').U(C('redirect_uri'))).
            '&code=' . $code;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        curl_close($ch);

        return json_decode($data);
    }

    public static function parseSkus($skus) {
        $parsedSkus = array();
        $count = count($skus->sku);
        for ($i = 0; $i < $count; $i += 1) {
            $sku = $skus->sku[$i];
            array_push($parsedSkus, new Sku(''.$sku->properties_name,
                ''.$sku->price,
                ''.$sku->quantity));
        }
        return $parsedSkus;
    }

    public static function parseItemImgs($itemImgs) {
        $parsedItemImgs = array();
        $count = count($itemImgs->item_img);
        for ($i = 0; $i < $count; $i += 1) {
            array_push($parsedItemImgs, ''.$itemImgs->item_img[$i]->url);
        }
        return $parsedItemImgs;
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
        if (session('use_db') == 'catshome'
            || session('use_db') == 'catshomedemo'
            || session('use_db') == 'ecmall'
            || session('use_db') == 'dg'
            || session('use_db') == 'cs'
            || session('use_db') == 'hz'
            || session('use_db') == 'sz') {
            self::changeDatabaseBackToWangpi51();
        }
        $taoapi = D('Taoapi');
        $taoappkey = $taoapi->getAppKey($taobaoItemId, $oldAppKey);
        session('taobao_app_key', $taoappkey['appkey']);
        session('taobao_secret_key', $taoappkey['appsecret']);
        session('current_taobao_app_key_id', $taoappkey['id']);
        self::changeDatabaseAccordingToSession();
    }

    public static function changeAliAppkey($taobaoItemId, $oldAppKey = null) {
        if (session('use_db') == 'catshome'
            || session('use_db') == 'catshomedemo'
            || session('use_db') == 'ecmall'
            || session('use_db') == 'dg'
            || session('use_db') == 'cs'
            || session('use_db') == 'hz'
            || session('use_db') == 'sz') {
            self::changeDatabaseBackToWangpi51();
        }
        $aliapi = D('Aliapi');
        $aliappkey = $aliapi->getAppKey($taobaoItemId, $oldAppKey);
        session('alibaba_app_key', $aliappkey['appkey']);
        session('alibaba_secret_key', $aliappkey['appsecret']);
        session('current_alibaba_app_key_id', $aliappkey['id']);
        session('access_token', null);
        self::changeDatabaseAccordingToSession();
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

    public static function changeDatabaseAccordingToSession() {
        self::changeDatabase(session('use_db'));
    }

    public static function changeDatabase($db) {
        switch($db) {
            case 'ecmall':
                C('DB_NAME', 'ecmall51_2');
                break;
            case 'catshome':
                C('DB_NAME', 'test');
                C('DB_HOST', '114.215.149.19');
                C('DB_USER', 'root');
                C('DB_PWD', 'suowei');
                break;
            case 'catshomedemo':
                C('DB_NAME', '315pangxie');
                C('DB_HOST', '114.215.149.19');
                C('DB_USER', '315pangxie');
                C('DB_PWD', 'q4r5c8C4');
                break;
            case 'dg':
                C('DB_NAME', 'wangpi51_dg');
                C('DB_HOST', 'rdsqr7ne2m2ifjm.mysql.rds.aliyuncs.com');
                C('DB_USER', 'wangpicn');
                C('DB_PWD', 'wangpicn123456');
                break;
            case 'cs':
                C('DB_NAME', 'wangpi51_cs');
                C('DB_HOST', 'rdsqr7ne2m2ifjm.mysql.rds.aliyuncs.com');
                C('DB_USER', 'wangpicn');
                C('DB_PWD', 'wangpicn123456');
                break;
            case 'hz':
                C('DB_NAME', 'wangpi51_hz');
                break;
            case 'sz':
                C('DB_NAME', 'wangpi51_sz');
                C('DB_HOST', 'rdsqr7ne2m2ifjm.mysql.rds.aliyuncs.com');
                C('DB_USER', 'wangpicn');
                C('DB_PWD', 'wangpicn123456');
                break;
            case 'local':
                C('DB_NAME', 'wangpi51');
                C('DB_HOST', 'localhost');
                C('DB_USER', 'root');
                C('DB_PWD', '57826502');
                break;
            default:
                self::changeDatabaseBackToWangpi51();
                break;
        }
    }

    public static function changeDatabaseBackToWangpi51() {
        C('DB_NAME', 'wangpi51');
        C('DB_HOST', 'rdsqr7ne2m2ifjm.mysql.rds.aliyuncs.com');
        C('DB_USER', 'wangpi51');
        C('DB_PWD', '51374b78b104');
    }

    public static function checkIP() {
        $ip = self::getIP();
        if ($ip == '124.172.221.107' || $ip == '124.172.221.110' || $ip == '180.168.191.194' || $ip == '27.115.4.22') {
            //echo 'ok,the ip is ' . $ip;
        } else {
            echo 'no,the ip is ' . $ip;
            redirect('http://www.51zwd.com');
        }
    }

    public static function getIP() {
        if (getenv("http_client_ip") && strcasecmp(getenv("http_client_ip"), "unknown"))
            $ip = getenv("http_client_ip");
        else if (getenv("http_x_forwarded_for") && strcasecmp(getenv("http_x_forwarded_for"), "unknown"))
            $ip = getenv("http_x_forwarded_for");
        else if (getenv("remote_addr") && strcasecmp(getenv("remote_addr"), "unknown"))
            $ip = getenv("remote_addr");
        else if (isset($_server[remote_addr]) && $_server[remote_addr] && strcasecmp($_server[remote_addr], "unknown"))
            $ip = $_server[remote_addr];
        else
            $ip = "unknown";
        return $ip;
    }

    public static function makePrice($price, $seePrice, $title = null) {
        $rawPrice = floatval($price);
        $finalPrice = $rawPrice;
        if (strpos($seePrice, '减半') !== false) {
            $finalPrice = $rawPrice / 2;
        } else if (strpos($seePrice, 'P') !== false || $seePrice == '减P' || $seePrice == '减p') {
            $regexP = '/[Pp](\d+)/';
            $regexF = '/[Ff](\d+)/';
            if (preg_match($regexP, $title, $matches) == 1) {
                $finalPrice = floatval($matches[1]);
            } else if (preg_match($regexF, $title, $matches) == 1) {
                $finalPrice = floatval($matches[1]);
            }
        } else if (strpos($seePrice, '减') === 0) {
            $finalPrice = $rawPrice - floatval(mb_substr($seePrice, 1, mb_strlen($seePrice, 'utf-8') - 1, 'utf-8'));
        } else if (strpos($seePrice, '实价') !== false) {
            $finalPrice = $rawPrice;
        } else if (strpos($seePrice, '*') === 0) {
            $finalPrice = $rawPrice * floatval(mb_substr($seePrice, 1, mb_strlen($seePrice, 'utf-8') - 1, 'utf-8'));
        } else if (strpos($seePrice, '打') === 0) {
            $finalPrice = $rawPrice * (floatval(mb_substr($seePrice, 1, mb_strlen($seePrice, 'utf-8') - 1, 'utf-8')) / 10);
        } else if (strpos($seePrice, '折') === mb_strlen($seePrice, 'utf-8') - 1) {
            $finalPrice = $rawPrice * (floatval(mb_substr($seePrice, 0, mb_strlen($seePrice, 'utf-8') - 1, 'utf-8')) / 10);
        }
        if (is_numeric($finalPrice)) {
            return $finalPrice;
        } else {
            return $price;
        }
    }

    public static function makeTitle($title) {
        $huoHao = self::getHuoHao($title);
        $newTitle = str_replace('款号', '',
                                str_replace('*', '',
                                            str_replace('#', '',
                                                        str_replace($huoHao, '', $title))));
        $regex ='/[PpFf](\d+)/';
        preg_match($regex, $newTitle, $matches);
        if ($matches) {
            $newTitle = str_replace($matches[0], '', $newTitle);
        }
        return trim($newTitle);
    }

    public static function getHuoHao($title, $propsName = null) {
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
        if (!$huoHao && $propsName != null) {
            if (strpos(''.$propsName, '13021751') !== false) {
                $parts = explode(';', ''.$propsName);
                $count = count($parts);
                for ($i = 0; $i < $count; $i++) {
                    if (strpos($parts[$i], '13021751') !== false) {
                        $values = explode(':', $parts[$i]);
                        $huoHao = $values[3];
                    }
                }
            }
        }
        return $huoHao;
    }

    public static function getNumIidFromUrl($url) {
        $regex = '/id=(\d+)/';
        preg_match($regex, $url, $matches);
        if ($matches) {
            return $matches[1];
        } else {
            return -1;
        }
    }
}

?>