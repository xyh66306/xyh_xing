<?php

function _sImage($image_url = '')
{
    if (!$image_url) {
        $image_url = "https://bingocn.wobeis.com/uploads/20250407/29eb470cc61eecbc54417e985db53d8b.png";//系统默认图片
    }

    if (stripos($image_url, 'http') !== false || stripos($image_url, 'https') !== false) {
        return $image_url;
    }

    if (stripos($image_url, 'http') !== false || stripos($image_url, 'https') !== false) {
        return str_replace("\\", "/", $image_url);
    } else {
        return request()->domain() . str_replace("\\", "/", $image_url);
    }
}
