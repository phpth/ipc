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

use Exception;
use phpth\ipc\exception\IpcException;
use phpth\ipc\supply\Options;
use phpth\ipc\supply\Store;

class FileMap extends Store
{
    /**
     * 新增值
     * @param $value
     * @return array|bool|int|mixed|string
     * @throws Exception
     */
    public function add($value)
    {
        try{
            set_error_handler([$this,'errHandle'],E_ALL);
            $res = false ;
            if(!$value)
            {
                goto end ;
            }
            if( !$this->lock(true))
            {
                goto end ;
            }
            $data = $this->read() ;
            if($data === false)
            {
                goto unlock ;
            }
            $data[] = $value;
            $data = $this->write($data);
            if($data )
            {
                throw new IpcException($data);
            }
            unlock:
            $this->unlock();
            end:
            restore_error_handler();
            return $res;
        }
        catch (Exception $e)
        {
            $this->unlock();
            restore_error_handler();
            throw $e;
        }
    }

    /**
     * 设置值
     * @param $key string 使用"." 来指定写入维度 如：set('a.b.c','c') 即 data['a']['b']['c'] = 'c'
     * @param $value
     * @return bool|int|mixed|string
     * @throws Exception
     */
    public function set ($key, $value)
    {
        try{
            set_error_handler([$this,'errHandle'],E_ALL);
            $key = explode ( '.' , trim ( $key , '.' ) );
            $res = false ;
            if ( !$key ) {
                goto end;
            }
            if ( !$this -> lock () ) {
                goto end;
            }
            $data = $this->read() ;
            if($data === false)
            {
                throw new Exception($data);
            }
            $tmp = &$data ;
            foreach($key  as $k)
            {
                if(isset($tmp[$k]))
                {
                    $tmp = &$tmp[$k] ;
                }
                else
                {
                    $tmp[$k] = [] ;
                    $tmp = &$tmp[$k];
                }
            }
            $tmp = $value;
            $data = $this->write($data);
            if($data )
            {
                throw new Exception($data);
            }
            $res = true ;
            unlock:
            $this->unlock();
            end :
            restore_error_handler();
            return $res ;
        }
        catch(Exception $e)
        {
            $this->unlock();
            restore_error_handler();
            throw $e;
        }
    }

    /**
     * 获取值
     * @param $key mixed  为 true 则获取所有
     * @return bool|mixed|null|string
     * @throws Exception
     */
    public function get ($key)
    {
        try{
            set_error_handler([$this,'errHandle'],E_ALL);
            $data = $this->read() ;
            if($key ===true )
            {
                goto end ;
            }
            $key = explode ( '.' , trim ( $key , '.' ) );
            foreach($key as $k)
            {
                if(isset($data[$k]))
                {
                    $data = $data[$k];
                }
                else
                {
                    $data = null ;
                    break;
                }
            }
            end:
            restore_error_handler ();
            return $data ;
        }catch (Exception $e)
        {
            restore_error_handler ();
            throw $e;
        }
    }

    /**
     * 删除数据
     * @param $key
     * @return bool|mixed|string
     * @throws Exception
     */
    public function unset ($key = true)
    {
        try {
            set_error_handler ( [ $this , 'errHandle' ] , E_ALL );
            $key = explode ( '.' , trim ( $key , '.' ) );
            $res = false;

            if ( !$key ) {
                goto end;
            }
            if ( !$this -> lock ( true ) ) {
                goto end;
            }
            if($key === true)
            {
                $data = '' ;
            }
            else
            {
                $data = $this -> read ();
                $tmp      = &$data;
                $last_key = array_pop ( $key );
                foreach ( $key as $k ) {
                    if ( isset( $tmp[ $k ] ) ) {
                        $tmp = &$tmp[ $k ];
                    }
                    else {
                        $res = true;
                        goto unlock;
                    }
                }
                $res = true;
                if ( isset( $tmp[ $last_key ] ) ) {
                    unset( $tmp[ $last_key ] );
                }
                else {
                    goto unlock;
                }
            }
            $res = $this -> write ( $data );
            if ( $res ) {
                throw new Exception($res);
            }
            unlock:
            $this -> unlock ();
            end:
            restore_error_handler ();
            return $res;
        }
        catch ( Exception $e ) {
            $this -> unlock ();
            restore_error_handler ();
            throw $e;
        }
    }

    /**
     * 写入数据
     * @param $data
     * @return bool|int|string
     */
    protected function write($data)
    {
        ftruncate($this->lock_file_handle, 0);
        rewind($this->lock_file_handle);
        $data = $this->options->serialize_handle->serialize($data);
        $len = strlen($data);
        //检测空间容量
        $need_space = $len - disk_free_space($this->options->after_format_path);
        if($need_space > 0)
        {
            $data = "磁盘空间不足，还需：{$need_space} Bytes";
            goto end ;
        }
        $data =  fwrite($this->lock_file_handle, $data) ;
        if($data===false)
        {
            $data = '写入数据时发生错误' ;
            goto end ;
        }
        fflush($this->lock_file_handle);
        $data = false;
        end:
        return $data ;
    }

    /**
     * 读取数据
     * @return array
     * @throws Exception
     * @throws IpcException
     */
    protected function read()
    {
        if(!rewind($this->lock_file_handle)) throw new IpcException('读取数据异常');
        $data = stream_get_contents ( $this->lock_file_handle);
        $data = trim($data);
        $data = $this->options->serialize_handle->unSerialize($data);
        if(empty($data)) $data = [] ;
        return (array)$data ;
    }

    /**
     * 删除存储
     * @return bool
     */
    public function remove()
    {
        if($this->lock(true))
        {
            $res = unlink ( $this->options->file_path);
            $this->unlock();
            fclose ( $this->lock_file_handle);
            return $res ;
        }
        else
        {
            return false ;
        }
    }

    /**
     *
     * @throws \phpth\ipc\exception\IpcException
     */
    protected function init()
    {
        // noop
    }

    /**
     *
     */
    protected function close ()
    {
        // noop
    }
}
