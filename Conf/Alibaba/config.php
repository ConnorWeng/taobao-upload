<?php
return array(
    //'配置项'=>'配置值'
    'URL_MODEL' => 0,
    'app_id' => '1008097',
    'secret_id' => 'GLnnB9vC5pKT',
    'grant_type' => 'authorization_code',
    'need_refresh_token' => 'true',
    'host' => 'http://121.196.142.10',
    'redirect_uri' => 'Index/authBack',
    'open_url' => 'http://gw.open.1688.com/openapi',
    'taobao_app_key' => '21336502',
    'taobao_secret_key' => '608b67adaba42db656de726b7cf2549f',
    'title_suffix' => '- 面包西点',
    'max_try_api_times' => 20,
    'max_api_times_per_minute' => 60,

    //数据库配置
    'DB_TYPE' => 'mysql',
    'DB_HOST' => 'rdsqr7ne2m2ifjm.mysql.rds.aliyuncs.com',
    'DB_NAME' => 'wangpi51',
    'DB_USER' => 'wangpi51',
    'DB_PWD' => '51374b78b104',
    'DB_PORT' => '3306',
    'DB_PREFIX' => 'ecm_',
    //开启日志
    'LOG_RECORD' => true,
    'LOG_LEVEL' => 'ERR',

    'gateway_uri' => 'http://gw.api.taobao.com/router/rest',
);
?>
