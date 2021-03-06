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

class Options
{
    ####################################################/* Common options  */
    /**
     *
     * @var null
     */
    public $gid = null;

    /**
     *
     * @var null
     */
    public $uid = null;

    /**
     * 权限
     * @var int
     */
    public $permission = 0666;

    /**
     * 最大使用内存
     * @var float|int
     */
    public $memory_size = 2000*100*100;

    /**
     * 发生错误是否抛出异常
     * @var bool
     */
    public $exception_on_error = true ;


    ####################################################/* store options */
    /**
     * 文件地址
     * @var string
     */
    public $file;

    /**
     * 格式话之后的文件的全路径目录
     * @var string
     */
    public $after_format_path = '';

    /**
     * 文件相对路径
     * @var string
     */
    public $file_path = 'share';

    /**
     * 文件跟路径
     * @var string
     */
    public $file_root_path = 'c:/shm/';

    /**
     * 读取文件每次读取大小
     * @var int
     */
    public $file_len_every_read = 8192;

    /**
     *
     * @var Serialize
     */
    public $serialize;

    /**
     * 共享内存资源标量
     * @var resource
     */
    public $share_memory;

    /**
     *
     * @var string
     */
    public $memory_create_flag = 'c';

    /**
     *
     * @var string
     */
    public $file_open_flag = 'c+';

    /**
     *
     */
    public const DEFAULT_FILE_PATH = 'data.file';

    ####################################################/* queue options */
    /**
     * 入列和出列在队列满或者为空是否阻塞
     * @var bool
     */
    public $block = false;

    /**
     * 是否自动序列化
     * @var bool
     */
    public $auto_serialize = true;

    /**
     * 每个消息的最大大小
     * @var float|int
     */
    public $msg_body_size = 100*1024*1024;

    /**
     *
     * Options constructor.
     * @param mixed $encode
     * @param mixed $decode
     */
    public function __construct ($encode = 'json_encode', $decode = 'json_decode')
    {
        $this->serialize = new Serialize( $encode, $decode);
    }
}
