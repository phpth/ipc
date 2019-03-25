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

use phpth\ipc\exception\ExecutableException;
use phpth\ipc\exception\IpcException;

abstract class Store
{
    use ComTrait;

    /**
     *
     * @var \phpth\ipc\supply\Options
     */
    public $options;

    /**
     *
     */
    public const DEFAULT_FILE_PATH = 'data.file';

    /**
     *
     * Store constructor.
     * @param \phpth\ipc\supply\Options $options
     * @throws \phpth\ipc\exception\ExecutableException
     * @throws \phpth\ipc\exception\IpcException
     */
    public function __construct(Options $options)
    {
        $this->options = $options;
        $this->initFiles ();
        $this->init ();
    }

    /**
     * 初始化文件和句柄
     * @throws ExecutableException
     * @throws \phpth\ipc\exception\IpcException
     */
    private function initFiles()
    {
        if(!$this->options->serialize_handle instanceof Serialize)
        {
            throw new ExecutableException("请设置序列化对象");
        }
        if(!is_resource ($this->options->share_memory))
        {
            throw new IpcException("请设置合法的共享内存资源");
        }
        if(empty($this->options->file_path))
        {
            $this->options->file_path = Store::DEFAULT_FILE_PATH;
        }
        if(!is_file($this->options->file))
        {
            $this->options->file = Store::filePathFormat($this->options->file_path, $this->options->file_root_path);
        }
        $this->lock_file_handle = fopen($this->options->file, $this->options->file_open_flag) ;
    }

    /**
     * @param $data
     * @return int
     */
    public static function crc16($data): int
    {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++)
        {
            $crc ^=ord($data[$i]);
            for ($j = 8; $j !=0; $j--)
            {
                if (($crc & 0x0001) !=0)
                {
                    $crc >>= 1;
                    $crc ^= 0xA001;
                }
                else
                    $crc >>= 1;
            }
        }
        return $crc;
    }

    /**
     *
     * @return mixed
     * @throws \phpth\ipc\exception\IpcException
     */
    protected abstract function init();

    protected abstract function close();

    /**
     * 释放资源
     */
    public final function __destruct()
    {
        if(is_resource($this->lock_file_handle))
        {
            $this->unlock();
            fclose($this->lock_file_handle);
        }
        $this->close ();
        $this->options->share_memory = null;
    }
}
