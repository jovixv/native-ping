<?php
include '../vendor/autoload.php';

use jovixv\Ping\Ping;

$test = new Ping();

$pingEntity = $test->ping('dataforseo.com', 500, 4, 32);

var_dump($pingEntity);
