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
use phpth\ipc\LinuxCode;

/**
 * 队列操作类
 * Class Queue
 */
class Queue
{
    protected $error_msg;

    /**
     *
     * @var resource
     */
    protected $queue;

    /**
     * 此处限定值是因为，从实际业务考虑区分消息类别直接使用不同的key即可。 而且简单明了
     * @var int
     */
    public const IPC_MSG_TYPE = 1;

    /**
     *
     * @var Options
     */
    public $options;

    /**
     *
     * Queue constructor.
     * @param $queue
     * @param Options $options
     */
    public function __construct ($queue, Options $options)
    {
        $this -> queue = $queue;
        $this -> options = $options;
    }

    /**
     * 入列
     * @param $message
     * @return bool
     * @throws IpcException
     */
    public function push ($message):bool
    {
        $msg_size = serialize ($message);
        if($msg_size > $this->options->msg_body_size)
        {
            $this->error_msg = "队列push异常，当前消息大小: $msg_size, 设置所允许的最大大小：{$this->options->msg_body_size}";
            if($this->options->exception_on_error)
            {
                throw new IpcException($this->error_msg );
            }
            else
            {
                $res = false;
                goto end;
            }
        }
        $res = msg_send ( $this -> queue , Queue::IPC_MSG_TYPE, $message , $this->options->auto_serialize, $this->options->block, $error_code );
        if($error_code || !$res)
        {
            $this->error_msg = "队列push异常，push_res：".var_export ($res, true)."，error_code: {$error_code}，error_msg: ".LinuxCode::getMsgByCode ($error_code);
        }
        else
        {
            $this->error_msg = null;
        }
        if($this->error_msg && $this->options->exception_on_error)
        {
            throw new IpcException($this->error_msg);
        }
        end:
        return $res;
    }

    /**
     * 出列
     * @return mixed
     * @throws IpcException
     */
    public function pop ()
    {
        //MSG_IPC_NOWAIT | MSG_EXCEPT |MSG_NOERROR
        if($this->options->block)
        {
            $flags = 0;
        }
        else
        {
            $flags = MSG_IPC_NOWAIT;
        }
        $res = msg_receive ($this -> queue , 0, $msgtype , $this->options->msg_body_size, $message, $this->options->auto_serialize, $flags, $error_code);
        if(!$res || $error_code)
        {
            $this->error_msg = "队列pop异常，pop_res：".var_export ($res, true)."，error_code：{$error_code}，error_msg：".LinuxCode::getMsgByCode ($error_code);
        }
        else
        {
            $this->error_msg = null;
        }
        if($this->error_msg && $this->options->exception_on_error)
        {
            throw new IpcException($this->error_msg);
        }
        return $message;
    }

    /**
     * 获取最近一次操作的错误信息
     * @return mixed
     */
    public function getLastErrorMsg()
    {
        return $this->error_msg;
    }

    /**
     *
     * @return bool
     */
    public function remove ()
    {
        return msg_remove_queue ( $this->queue);
    }

    /**
     *
     * @return array
     */
    public function stat ()
    {
        //[
        //'msg_perm.uid'  => '' ,
        //组id
        //'msg_perm.gid'  => '' ,
        //队列的权限
        //'msg_perm.mode' => 0666 ,
        //队列的最后的发送时间
        //'msg_stime'     => '' ,
        //队列的最后接收时间
        //'msg_rtime'     => '' ,
        //队列的最后修改时间
        //'msg_ctime'     => '' ,
        //队列的数量
        //'msg_qnum'      => '' ,
        //队列的大小
        //'msg_qbytes'    => 1024*1024*500,
        //最后发送的进程pid
        //'msg_lspid'     => '' ,
        //最后接收的进程pid
        //'msg_lrpid'     => '' ,
        //];
        return msg_stat_queue ( $this->queue);
    }
}
