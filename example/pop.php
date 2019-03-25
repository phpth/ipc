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


require '../src/exception/ExecutableException.php';
require '../src/exception/IpcErrorException.php';
require '../src/exception/IpcException.php';
require '../src/supply/ComTrait.php';
require '../src/supply/Store.php';
require '../src/supply/FileMap.php';
require '../src/supply/Serialize.php';
require '../src/supply/ShmopMemory.php';
require '../src/supply/SysVShmMemory.php';
require '../src/supply/Options.php';
require '../src/supply/Queue.php';
require '../src/Factory.php';

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

