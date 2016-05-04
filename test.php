<?php
/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 21.03.2016
 * Time: 08:54
 */



$data = 'Port 11';
$data = 'Port05';
$search = '/(\d+)/';
$val = $match[1];

preg_match($search, $data, $match);
echo "<pre><hr>";
print_r($match);
echo "<hr></pre><br>";





