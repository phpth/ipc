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

class Serialize
{
    /**
     * 反序列化
     * @var mixed
     */
    protected $decode;

    /**
     * 序列化
     * @var mixed
     */
    protected $encode;

    /**
     *
     * Serialize constructor.
     * @param $encode
     * @param $decode
     */
    public function __construct (callable $encode, callable $decode)
    {
        $this->encode = $encode;
        $this->decode = $decode;
    }

    /**
     *
     * @param mixed ...$data
     * @return mixed
     */
    public function encode(...$data)
    {
        return ($this->encode)(...$data);
    }

    /**
     *
     * @param mixed ...$data
     * @return mixed
     */
    public function decode(...$data)
    {
        return ($this->decode)(...$data);
    }
}
