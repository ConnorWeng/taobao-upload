<?php

import('@.Model.Sku');

class TaobaoItem {
    public $cid;
    public $item_imgs;
    public $props_name;
    public $title;
    public $pic_url;
    public $nick;
    public $price;
    public $num;
    public $prop_imgs;
    public $desc;
    public $delist_time;
    public $skus;

    public function __construct() {
        $this->cid = '50000671';
        $this->item_imgs = new ItemImgs;
        $this->props_name = '';
        $this->title = 'null';
        $this->pic_url = '';
        $this->nick = 'null';
        $this->price = '99.99';
        $this->num = '99';
        $this->prop_imgs = new PropImgs;
        $this->desc = 'null goods';
        $this->delist_time = '2009-12-10 00:00:00';
        $this->skus = new Skus;
    }

    public function setCid($value) {
        $this->cid = $value;
    }

    public function setItemImgs($value) {
        $this->item_imgs->setItemImg($value);
    }

    public function setPropsName($value) {
        $this->props_name = $value;
    }

    public function setTitle($value) {
        $this->title = $value;
    }

    public function setPicUrl($value) {
        $this->pic_url = $value;
    }

    public function setNick($value) {
        $this->nick = $value;
    }

    public function setPrice($value) {
        $this->price = $value;
    }

    public function setNum($value) {
        $this->num = $value;
    }

    public function setPropImgs($value) {
        $this->prop_imgs->setPropImg($value);
    }

    public function setDesc($value) {
        $this->desc = $value;
    }

    public function setDelistTime($value) {
        $this->delist_time = $value;
    }

    public function addSku($sku) {
        $this->skus->addSku($sku);
    }
}

class ItemImgs {
    public $item_img;

    public function __construct() {
        $this->item_img = array();
    }

    public function setItemImg($value) {
        $this->item_img = $value;
    }
}

class ItemImg {
    public $url;

    public function __construct($url) {
        $this->url = $url;
    }
}

class PropImgs {
    public $prop_img;

    public function __construct() {
        $this->prop_img = array();
    }

    public function setPropImg($value) {
        $this->prop_img = $value;
    }
}

class Skus {
    public $sku;

    public function __construct() {
        $this->sku = array();
    }

    public function addSku($sku) {
        array_push($this->sku, $sku);
    }
}