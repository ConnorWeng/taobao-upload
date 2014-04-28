<?php
return array(
    'max_try_api_times' => 10,
    'max_api_times_per_minute' => 30,
    'taoapi_count' => 1,

    'DB_NAME' => 'wangpi51',
    'DB_TYPE' => 'mysql',
    'DB_PORT' => 3306,
    'DB_PREFIX' => 'ecm_',

    //* sandbox
    'DB_HOST' => 'localhost',
    'DB_USER' => 'root',
    'DB_PWD' => '57826502',
    'redirect_host' => 'localhost',
    'taobao_app_key' => '1021774925',
    'taobao_secret_key' => 'sandbox30c7d20fc406dd18b5c28430c',
    'oauth_uri' => 'oauth.tbsandbox.com',
    'gateway_uri' => 'http://gw.api.tbsandbox.com/router/rest',
    //*/
    /* production
    'DB_HOST' => 'rdsqr7ne2m2ifjm.mysql.rds.aliyuncs.com',
    'DB_USER' => 'wangpi51',
    'DB_PWD' => '51374b78b104',
    'redirect_host' => '121.196.142.10',
    'taobao_app_key' => '21336502',
    'taobao_secret_key' => '608b67adaba42db656de726b7cf2549f',
    'oauth_uri' => 'oauth.taobao.com',
    'gateway_uri' => 'http://gw.api.taobao.com/router/rest',
    //*/
);