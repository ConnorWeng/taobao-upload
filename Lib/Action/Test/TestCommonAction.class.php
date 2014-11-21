<?php

import('@.Util.Asserts');

class TestCommonAction extends TestAction {
    public $className = 'TestCommonAction';

    public function testReadConfig() {
        Asserts::equal('read TEST_CONFIG from module', C('TEST_CONFIG'), '123456');
        Asserts::equal('read DB_NAME from global', C('DB_NAME'), 'wangpi51');
    }
}