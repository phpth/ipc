<?php
// +----------------------------------------------------------------------
// | program: phpc
// +----------------------------------------------------------------------
// | Copyright (c) 2019
// +----------------------------------------------------------------------
// | Licensed MIT
// +----------------------------------------------------------------------
// | Author: zhangjs
// +----------------------------------------------------------------------
// | Date: 2019/3/22 0022
// +----------------------------------------------------------------------
// | Time: 下午 23:01
// +----------------------------------------------------------------------
namespace phpth\test;

use Exception;
use phpth\ipc\Factory;
use phpth\ipc\supply\Options;


require '../src/autoload.php';

try{
    $option = new Options();
    $factory = new Factory($option);
    $queue = $factory->getQueue ( 'test');
    while (true)
    {
        var_dump($queue->pop ());
    }
}catch (Exception $e)
{
    echo "exception: {$e->getMessage ()}\n";
}

