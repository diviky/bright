<?php

if (!function_exists('printr')) {
    function printr($data)
    {
        echo '<pre>';
        print_r($data);
    }
}
