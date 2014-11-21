<?php

class TestAction extends Action {
    public $className = 'TestAction';

    public function index() {
        $methods = get_class_methods($this->className);
        foreach ($methods as $method) {
            if (strpos($method, 'test') !== false) {
                call_user_func(array($this, $method));
            }
        }
    }
}