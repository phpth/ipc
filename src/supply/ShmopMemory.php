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

use Throwable;
use phpth\ipc\exception\IpcException;

/**
 *
 * Class ShmopMemory
 * @package phpth\ipc\supply
 */
class ShmopMemory extends Store
{
    /**
     * 设置的项目标识
     * @var string
     */
    public const CHAR = 'p';

    /**
     * 标识数据占位的字节数
     */
    public const LEN_MARK = 20;

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
     *
     * @param bool $key
     * @return array|bool|mixed|string|null
     * @throws Throwable
     */
    public function get ( $key = true )
    {
        try{
            set_error_handler ( [$this,'errHandle'],E_ALL);
            $data = $this->read();
            if($key !== true)
            {
                $key = explode ( '.' , trim($key,'.'));
                foreach($key as $v)
                {
                    if(isset($data[$v]))
                    {
                        $data  = $data[$v];
                    }
                    else
                    {
                        $data  = null;
                        break ;
                    }
                }
            }
            restore_error_handler ();
            return $data ;
        }
        catch (Throwable $e)
        {
            restore_error_handler ();
            throw $e ;
        }
    }

    /**
     *
     * @param $key
     * @param $value
     * @return bool|int
     * @throws Throwable
     */
    public  function set($key, $value)
    {
        try {
            set_error_handler ( [$this , 'errHandle'],E_ALL);
            $res = false ;
            if(!$this->lock ())
            {
                goto end;
            }
            $data = $this->read();
            $key = explode ( '.' , trim($key,'.'));
            $tmp = &$data  ;
            foreach($key as $v)
            {
                $tmp = &$tmp[$v];
            }
            $tmp = $value;
            $res = $this->write($data);
            unlock:
            $this->unlock ();
            end:
            restore_error_handler ();
            return $res ;
        }catch (Throwable $e)
        {
            $this->unlock ();
            restore_error_handler ();
            throw $e;
        }
    }

    /**
     *
     * @param bool $key
     * @return bool|int
     * @throws \Throwable
     */
    public function unset($key=true)
    {
        try {
            set_error_handler ( [ $this , 'errHandle' ] , E_ALL );
            $res  = false;
            if(!$this->lock ())
            {
                goto end;
            }
            if($key === true)
            {
                $data = '';
            }
            else
            {
                $data = $this -> read ();
                $key      = explode ( '.' , trim ( $key , '.' ) );
                $tmp      = &$data;
                $last_key = array_pop ( $key );
                foreach ( $key as $k ) {
                    if ( isset( $k ) ) {
                        $tmp = &$tmp[ $k ];
                    }
                    else {
                        $res = true;
                        goto unlock;
                    }
                }
                if ( isset( $tmp[ $last_key ] ) ) {
                    unset( $tmp[ $last_key ] );
                }
                else {
                    $res = true;
                    goto unlock;
                }
            }
            $res = $this -> write ( $data );
            unlock:
            $this->unlock ();
            end:
            restore_error_handler ();
            return $res;
        }
        catch ( Throwable $e ) {
            $this->unlock ();
            restore_error_handler ();
            throw $e;
        }
    }

    /**
     *
     * @return bool
     */
    public  function remove()
    {
        $res = shmop_delete($this->options->share_memory);
        $this->unlock();
        unlink($this->options->file_path);
        fclose($this->lock_file_handle);
        return $res;
    }

    /**
     * 加锁
     * @param bool $hang
     * @return bool
     */
    public function lock($hang=true)
    {
        return flock($this->lock_file_handle ,$hang?LOCK_EX:LOCK_EX|LOCK_NB);
    }

    /**
     * 解锁
     * @return bool
     */
    public function unlock()
    {
        return flock($this->lock_file_handle ,LOCK_UN);
    }

    /**
     * @return array|bool|mixed|string
     */
    protected function read()
    {
        $data_len = (int) trim ( shmop_read ( $this ->options->share_memory , 0 , ShmopMemory::LEN_MARK ) );
        $data    = trim ( shmop_read ( $this -> options->share_memory , ShmopMemory::LEN_MARK , $data_len ) );
        $data    = $this->options->serialize_handle->unSerialize ( $data );
        if ( empty( $data ) ) return [];
        return (array) $data;
    }

    /**
     *
     * @param $data
     * @return int
     * @throws IpcException
     */
    protected function write($data)
    {
        $data = $this->options->serialize_handle->serialize($data);
        $data_len = strlen ( $data);
        if($data_len > $this->options->memory_size-ShmopMemory::LEN_MARK)
        {
            throw new IpcException("空间不足，还需要：".($data_len+ShmopMemory::LEN_MARK-$this->options->memory_size). ' Bytes');
        }
        $mark_data = str_pad(strlen($data),ShmopMemory::LEN_MARK,'0',STR_PAD_LEFT);
        $data = "{$mark_data}{$data}";
        return shmop_write($this->options->share_memory, $data, 0);
    }

    /**
     * 关闭处理
     */
    protected function close ()
    {
        if(is_resource ($this->options->share_memory))
        {
            shmop_close($this->options->share_memory);
        }
    }
}
