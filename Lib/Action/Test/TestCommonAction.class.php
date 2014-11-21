<?php

import('@.Util.Asserts');

class TestCommonAction extends TestAction {
    public $className = 'TestCommonAction';

    public function testReadConfig() {
        Asserts::equal('read DB_PWD from module', C('DB_PWD'), '123456');
        Asserts::equal('read DB_NAME from global', C('DB_NAME'), 'wangpi51');
    }
}