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

require '../src/autoload.php';


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