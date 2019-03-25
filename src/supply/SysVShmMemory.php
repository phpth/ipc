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

use phpth\ipc\exception\IpcException;
use phpth\ipc\Factory;

/**
 * 注意：此类的读写操作不是线程/进程安全，读写期间请使用lock ,unlock方法锁定之后在操作
 * Class sysVShmMemory
 * @package phpth\ipc\supply
 */
class sysVShmMemory extends Store
{
    public const CHAR = 'm';

    /**
     *
     * @return mixed|void
     * @throws IpcException
     */
    protected function init()
    {
        // noop
    }

    /**
     * 设置变量
     * @param $var
     * @param $value
     * @return bool
     */
    public function set($var, $value)
    {
        return shm_put_var($this->options->share_memory, Factory::crcKey($var), $value);
    }

    /**
     * 获取变量
     * @param $var
     * @return mixed
     */
    public function get($var)
    {
        $key = Factory::crcKey($var);
        if (!shm_has_var($this->options->share_memory, $key)) {
            return false;
        }
        return shm_get_var($this->options->share_memory, $key);
    }

    /**
     * 删除一个变量
     * @param $var
     * @return bool
     */
    public function unset($var)
    {
        $key = Factory::crcKey($var);
        if(!shm_has_var($this->options->share_memory, $key)) {
            return true;
        }
        return shm_remove_var($this->options->share_memory,  $key);
    }

    /**
     * 删除共享占用的存储
     * @return bool
     */
    public function remove():bool
    {
        unlink($this->options->file);
        if(is_resource($this->lock_file_handle))
        {
            flock($this->lock_file_handle,LOCK_UN);
            fclose($this->lock_file_handle);
        }
        $res = shm_remove($this->options->share_memory);
        $this->options->share_memory = null;
        return $res ;
    }

    protected function close()
    {
        if(is_resource($this->options->share_memory))
        {
            shm_detach($this->options->share_memory);
        }
    }
}
