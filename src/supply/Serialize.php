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
namespace phpth\ipc\Supply;

use phpth\ipc\exception\ExecutableException;

class Serialize
{
    /**
     * 反序列化
     * @var mixed
     */
    protected $un_serialize;

    /**
     * 序列化
     * @var mixed
     */
    protected $serialize;

    /**
     *
     * Serialize constructor.
     * @param $serialize
     * @param $un_serialize
     * @throws \phpth\ipc\exception\ExecutableException
     */
    public function __construct ($serialize, $un_serialize)
    {
        if(!is_callable ($serialize))
        {
            throw new ExecutableException(var_export ($serialize, true)." 无法执行！");
        }
        if(!is_callable ($un_serialize))
        {
            throw new ExecutableException(var_export ($un_serialize, true)." 无法执行！");
        }
        $this->serialize = $serialize;
        $this->un_serialize = $un_serialize;
    }

    /**
     *
     * @param mixed ...$data
     * @return mixed
     */
    public function serialize(...$data)
    {
        return ($this->serialize)(...$data);
    }

    /**
     *
     * @param mixed ...$data
     * @return mixed
     */
    public function unSerialize(...$data)
    {
        return ($this->un_serialize)(...$data);
    }
}
