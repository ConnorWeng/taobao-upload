<?php

import('@.Util.Asserts');
import('@.Util.OpenAPI');

class TestOpenAPIAction extends TestAction {
    public $className = 'TestOpenAPIAction';

    public function testGetTradesSold() {
        dump(OpenAPI::getTradesSold(session('taobao_app_key'), session('taobao_secret_key'), session('taobao_access_token')));
    }
}