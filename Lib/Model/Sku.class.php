<?php

class Sku {

    public $properties_name = '';
    public $price = '';
    public $quantity = '';

    function __construct($properties_name, $price, $quantity) {
        $this->properties_name = $properties_name;
        $this->price = $price;
        $this->quantity = $quantity;
    }

}

?>