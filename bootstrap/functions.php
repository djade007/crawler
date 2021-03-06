<?php
/**
 * Created by PhpStorm.
 * User: olajide
 * Date: 3/1/16
 * Time: 1:28 PM
 */


function stripNl($text) {
    $text = preg_replace('/^\n+/', '', strip_tags(preg_replace('/\<br ?\/?>/', "\n", $text)));
    $text = html_entity_decode($text);
    return $text;
}

function carbon() {
    static $carbon;
    if(!$carbon) $carbon = \Carbon\Carbon::now(config('app.timezone'))->copy();
    return $carbon;
}

function a_link($where) {
    switch($where) {
        case 'nairaland':
            return 'http://nairaland.com';
        case 'stackoverflow':
            return 'http://stackoverflow.com';
    }
    return false;
}


// function to pick the range in which the search matched
// the limit
function limitTo($content, $q) {
    if($q) {
        $q = strtolower($q);
        $l_content = strtolower($content);
        $pos = strpos($l_content, $q);
        $a = strlen($q);
        $total = $pos + $a;
        $rem = abs($total - 160);

        if($pos < 140) {
            $str = $content;
        } else {
            $str = substr($content, $rem+1);
        }
    } else {
        $str = $content;
    }
    return str_limit($str, 160, '...');
}
