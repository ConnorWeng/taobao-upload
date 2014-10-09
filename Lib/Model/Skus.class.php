<?php

class Skus {
    public $sku;

    public function __construct() {
        $this->sku = array();
    }

    public function addSku($sku) {
        array_push($this->sku, $sku);
    }
}