<?php

import('@.Util.Asserts');

class TestCommonAction extends Action {
    public function index() {
        $methods = get_class_methods(__CLASS__);
        foreach ($methods as $method) {
            if (strpos($method, 'test') !== false) {
                call_user_func(array($this, $method));
            }
        }
    }

    public function testReadConfig() {
        Asserts::equal('read DB_PWD from module', C('DB_PWD'), '123456');
        Asserts::equal('read DB_NAME from global', C('DB_NAME'), 'wangpi51');
    }
}