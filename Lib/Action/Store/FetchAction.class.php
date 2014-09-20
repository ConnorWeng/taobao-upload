<?php

class FetchAction extends Action {
    public function index() {
        header("Content-type:text/html;charset=utf-8");
        $storeId = I('store_id');
        echo(shell_exec('coffee /alidata/www/test2/node/51fetch_all/single_store.coffee '.$storeId));
    }
}