<?php
return array(
    'max_try_api_times' => 10,
    'max_api_times_per_minute' => 50,
    'taoapi_count' => 200,
    'servletUri' => 'http://120.26.196.147:30001/duapp/yjscServlet',
    'article_code' => 'FW_GOODS-1856100',

    //* sandbox
    'DB_HOST' => 'localhost',
    'DB_USER' => 'root',
    'DB_PWD' => '57826502',
    'redirect_host' => 'localhost',
    'redirect_path' => U('Taobao/Index/authBack'),
    'stable_taobao_app_key' => '1021774925',
    'stable_taobao_secret_key' => 'sandbox30c7d20fc406dd18b5c28430c',
    'taobao_app_key' => '1021774925',
    'taobao_secret_key' => 'sandbox30c7d20fc406dd18b5c28430c',
    'oauth_uri' => 'oauth.tbsandbox.com',
    'gateway_uri' => 'http://gw.api.tbsandbox.com/router/rest',
    //*/
    /* production
    'redirect_host' => '120.26.196.147',
    'redirect_path' => '/index.php',
    'oauth_uri' => 'oauth.taobao.com',
    'stable_taobao_app_key' => '21357243',
    'stable_taobao_secret_key' => '244fd3a0b1f554046a282dd9b673b386',
    //*/
);
