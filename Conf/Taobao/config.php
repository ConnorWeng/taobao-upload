<?php
return array(
    'max_try_api_times' => 10,
    'max_api_times_per_minute' => 50,
    'taoapi_count' => 200,

    'DB_NAME' => 'wangpi51',
    'DB_TYPE' => 'mysql',
    'DB_PORT' => 3306,
    'DB_PREFIX' => 'ecm_',

    'servletUri' => 'http://121.196.142.10:30001/duapp/yjscServlet',

    'article_code' => 'FW_GOODS-1862529',

    'stable_taobao_app_key' => '21486955',
    'stable_taobao_secret_key' => 'f6d12223ff4859f639bc54478a834870',

    /* sandbox
    'DB_HOST' => 'localhost',
    'DB_USER' => 'root',
    'DB_PWD' => '57826502',
    'redirect_host' => 'localhost',
    'redirect_path' => U('Taobao/Index/authBack'),
    'taobao_app_key' => '1021774925',
    'taobao_secret_key' => 'sandbox30c7d20fc406dd18b5c28430c',
    'oauth_uri' => 'oauth.tbsandbox.com',
    'gateway_uri' => 'http://gw.api.tbsandbox.com/router/rest',
    //*/
    //* production
    'DB_HOST' => 'rdsqr7ne2m2ifjm.mysql.rds.aliyuncs.com',
    'DB_USER' => 'wangpi51',
    'DB_PWD' => '51374b78b104',
    'redirect_host' => 'www.daifabao.com',
    'redirect_path' => '/auth.jsp',
    'taobao_app_key' => '21486955',
    'taobao_secret_key' => 'f6d12223ff4859f639bc54478a834870',
    'oauth_uri' => 'oauth.taobao.com',
    'gateway_uri' => 'http://gw.api.taobao.com/router/rest',
    //*/

    '51zwd_redirect_host' => '121.196.142.10',
    '51zwd_redirect_path' => '/index.php',
);
