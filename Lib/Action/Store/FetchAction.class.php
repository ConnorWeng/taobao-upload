<?php

class FetchAction extends Action {
    public function index() {
        header("Content-type:text/html;charset=utf-8");
        $storeId = I('store_id');
        echo(shell_exec('node /alidata/www/test2/node/51fetch_all/i.js '.$storeId));
    }
}