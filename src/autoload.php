<?php
// +----------------------------------------------------------------------
// | phpc
// +----------------------------------------------------------------------
// | Copyright (c) 2019
// +----------------------------------------------------------------------
// | Licensed MIT
// +----------------------------------------------------------------------
// | Author: zhangjs
// +----------------------------------------------------------------------
// | Date: 2019-4-30
// +----------------------------------------------------------------------
// | Time: 上午 09:39
// +----------------------------------------------------------------------
namespace phpth\mexec;

spl_autoload_register (function( $class_name){
    if(stripos ($class_name, 'phpth\ipc')===false)
    {
       return false ;
    }
    $class_name = str_ireplace ('phpth\ipc', '', $class_name);
    $class_name = str_replace ('\\', '/', ltrim($class_name, '\\'));
    $class_name = str_replace ('\\', '/', $class_name);
    $path = realpath (__DIR__);
    $file = $path."/{$class_name}.php";
    if(file_exists ($file))
    {
        return require $file;
    }
    else{
        return false;
    }
});
