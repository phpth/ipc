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

    $factory = new Factory( new Options());
    $share = $factory->getShmopMemory ();
    $share->set ( 'sdfasdfsdaf' , ['asdfasdf']);
    var_dump ( $share->get('sdfasdfsdaf'));
    $file = $factory->getFileMap ();
    $file->set('a55sdfasdf', ['sdfasdf',144]);
    var_dump ( $file->get('a55sdfasdf'));
    $factory->options->share_memory = null;
    $ss = $factory->getSysVShmMemory ();
    $ss->set('asdfsdf', [4111,'asdasdf']);
    var_dump ( $ss->get('asdfsdf'));

}catch (\Exception $e)
{
    echo $e->getMessage (), PHP_EOL;
}