<?php

class Asserts {
    public static function equal($msg, $actual, $expect) {
        $ret = '<span style="color:red"><b>fail</b></span><br/>';
        if ($actual === $expect) {
            $ret = '<span style="color:green"><b>success</b></span><br/>';
        }
        echo($msg.': '.$ret);
    }
}