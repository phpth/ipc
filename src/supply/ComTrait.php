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
namespace phpth\ipc\supply;

use Exception;
use phpth\ipc\exception\IpcErrorException;
use phpth\ipc\exception\IpcException;

trait ComTrait
{
    /**
     * 文件句柄
     * @var resource
     */
    protected $lock_file_handle;

    /**
     * pdo 错误信息应该被手动处理
     * @param  int $errno
     * @param  string $errstr
     * @param  string $errfile
     * @param  int $errline
     * @param  array $errcontext
     * @return null
     * @throws \phpth\ipc\exception\IpcErrorException
     */
    public function errHandle($errno, $errstr, $errfile, $errline, $errcontext = null)
    {
        throw new IpcErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * 给定文件路径，创建文件并且返回全路径
     * @param $file_path
     * @param string $root_path
     * @return string $real_path
     * @throws IpcException
     */
    public static function filePathFormat($file_path, $root_path='/dev/shm')
    {
        if(!$file_path)
        {
            throw new Exception("路径不能为空");
        }
        $root_path = rtrim($root_path,'/\\');
        $file_path = rtrim($file_path,'/\\');
        $path_info  = preg_split('#[\\\/]+#m', $file_path);
        foreach($path_info as $k=>$v)
        {
            if($v=='..' || $v == '.')
            {
                unset($path_info[$k]) ;
            }
        }
        $file_path = join('/',$path_info);
        $real_path = "{$root_path}/$file_path";
        $dir = dirname($real_path) ;
        if(!is_dir($dir))
        {
            if(!mkdir($dir,0700,true))
            {
                throw new IpcException("无法创建目录：{$dir}");
            }
        }
        if(!file_exists($real_path))
        {
            if(!touch($real_path))
            {
                throw new IpcException("无法创建文件：{$real_path}");
            }
        }
        return $real_path;
    }

    /**
     * 加锁
     * @param bool $hang
     * @return bool
     */
    public function lock($hang = true )
    {
        return flock($this->lock_file_handle, $hang?LOCK_EX:LOCK_EX|LOCK_NB);
    }

    /**
     * 解锁
     * @return bool
     */
    public function unlock()
    {
        return flock($this->lock_file_handle, LOCK_UN);
    }
}
