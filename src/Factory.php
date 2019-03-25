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
namespace phpth\ipc;

use phpth\ipc\exception\IpcException;
use phpth\ipc\supply\Options;
use phpth\ipc\supply\Queue;
use phpth\ipc\supply\ShmopMemory;
use phpth\ipc\supply\Store;
use phpth\ipc\supply\sysVShmMemory;

/**
 *
 * Class Factory
 * @package phpth\ipc
 */
class Factory
{
    /**
     *
     * @var array
     */
    public const SETTING_KEY = [
        'msg_perm.uid' ,
        //组id
        'msg_perm.gid'  ,
        //队列的权限
        'msg_perm.mode',
        //队列的大小
        'msg_qbytes'
    ];

    /**
     * 设置对象
     * @var \phpth\ipc\supply\options
     */
    public $options;

    /**
     *
     * Factory constructor.
     * @param Options|null $options
     * @throws exception\ExecutableException
     */
    public function __construct (?Options $options)
    {
        $this->options = $options??new Options();
    }

    /**
     * 获取sysv队列操作对象
     * @param string $key
     * @return \phpth\ipc\supply\Queue
     * @throws \phpth\ipc\exception\IpcException
     */
    public function getQueue(string $key):Queue
    {
        return new Queue($this->queue ($key), $this->options);
    }

    /**
     * 获取内存映射文件操作类
     * @return FileMap
     * @throws IpcException
     * @throws exception\ExecutableException
     */
    public function getFileMap():FileMap
    {
        if(!is_file ( $this->options->file))
        {
            $this->options->file = Store::filePathFormat ($this->options->file_path, $this->options->file_root_path);
        }
        return new FileMap( $this->options);
    }

    /**
     * 获取shmop共享内存操作对象
     * @return ShmopMemory
     * @throws IpcException
     * @throws exception\ExecutableException
     */
    public function getShmopMemory():ShmopMemory
    {
        if(!is_resource ( $this->options->share_memory))
        {
            if(!is_file ( $this->options->file))
            {
                $this->options->file = Store::filePathFormat ($this->options->file_path, $this->options->file_root_path);
            }
            $this->options->share_memory= shmop_open(
                Factory::pathKey ($this->options->file, ShmopMemory::CHAR),
                $this->options->memory_create_flag,
                $this->options->permission,
                $this->options->memory_size
            );
            if(!is_resource ( $this->options->share_memory))
            {
                throw new IpcException("创建共享内存资源失败！");
            }
        }
        return new ShmopMemory($this->options);
    }

    /**
     * 获取
     * @return SysVShmMemory
     * @throws IpcException
     * @throws exception\ExecutableException
     */
    public function getSysVShmMemory():SysVShmMemory
    {
        if(!is_resource ( $this->options->share_memory))
        {
            if(!is_file ( $this->options->file))
            {
                $this->options->file = Store::filePathFormat ($this->options->file_path, $this->options->file_root_path);
            }
            $this->options->share_memory= shm_attach (
                Factory::pathKey ( $this->options->file , SysVShmMemory::CHAR),
                $this->options->memory_size,
                $this->options->permission
                );
            if(!is_resource ( $this->options->share_memory))
            {
                throw new IpcException("创建共享内存资源失败！");
            }
        }
        return new SysVShmMemory($this->options);
    }

    /**
     * 指定的队列是否存在
     * @param string $key
     * @return bool
     */
    public function existQueue(string $key)
    {
        return msg_queue_exists ( $this->getKey ( $key));
    }

    /**
     * 使用数组参数设置队列
     * @param \phpth\ipc\supply\Options $options
     * @return array
     */
    public static function setByOption(Options $options):array
    {
        $param_setting = [];
        if($options->memory_size)
        {
            $param_setting['msg_qbytes'] = $options->memory_size;
        }
        if($options->gid)
        {
            $param_setting['msg_perm.gid'] = $options->gid;
        }
        if($options->uid)
        {
            $param_setting['msg_perm.uid'] = $options->uid;
        }
        if($options->permission)
        {
            $param_setting['msg_perm.mode'] = $options->permission;
        }
        return $param_setting;
    }

    /**
     * 设置队列大小， 某些情况可能需要root权限才能生效
     * @param int $size
     * @return \phpth\ipc\Factory
     */
    public function setSizeMb(int $size):self
    {
        $this->options->memory_size  = $size*1024*1024;
        return $this;
    }

    /**
     * 设置队列的参数
     * @param $queue
     * @param $options
     * @throws \phpth\ipc\exception\IpcException
     */
    public static function queueOptionSetting($queue, $options):void
    {
        if(!msg_set_queue ($queue, Factory::setByOption ($options)))
        {
            throw new IpcException("无法设置队列参数，请确定设置项是否含有需root权限的设定");
        }
    }

    /**
     * 获取一个Linux queue
     * @param string $key
     * @return resource
     * @throws \phpth\ipc\exception\IpcException
     */
    protected function queue(string $key)
    {
        $queue = msg_get_queue(Factory::crcKey( $key), $this->options->permission);
        if(!is_resource ( $queue))
        {
            throw new IpcException("无法获取ipc队列");
        }
        self::queueoptionSetting ($queue, $this->options);
        return $queue;
    }

    /**
     * 根据路径获取 int key
     * @param $file_path
     * @param $project_id
     * @return int|string
     * @throws IpcException
     */
    public static function pathKey($file_path, $project_id): int
    {
        if(function_exists('ftok'))
        {
            $ipc_key = ftok($file_path, $project_id);
        }
        else
        {
            $fileStats = stat($file_path);
            if (!$fileStats)
            {
                throw  new IpcException('无法stat目录信息');
            }
            $ipc_key = sprintf('%u', ($fileStats['ino'] & 0xffff) | (($fileStats['dev'] & 0xff) << 16) | ((ord($project_id) & 0xff) << 24));
        }
        return (int)$ipc_key;
    }

    /**
     * 根据字符串获取crc key
     * @param string $key
     * @return int
     */
    public static function crcKey(string $key): int
    {
        if(!is_numeric($key) || $key <0)
        {
            $key = crc32 ( $key);
        }
        return (int)$key;
    }
}
