<?php
/**
 * Author: <user>
 * Date: <date>
 * Time: <time>
 */
 
namespace app<namespace>model;

use app<namespace>common\Output;
use think\model;
use Exception;
class <model> extends Model
{
	use Output;
	protected $pk = '<pk>';
    <frelationModel><relationModel>
    /**
    * Notes: 获取分页列表
	* Author: <user>
    * @param $where
    * @param $page_size
    * @return \think\Response
    */
    public function get<model>List($where, $page_size)
    {
        try {
            $res = $this->where($where)->order('<pk>', 'asc')->paginate($page_size);
        } catch(Exception $e) {
            return self::Error([],"请求数据失败~",400);
        }
        if($res->isEmpty())
        {
            return self::Success([],"请求数据为空~",204);
        }
        else
        {
            return self::Success($res,"请求数据成功~",200);
        }
    }

    /**
    * Notes: 添加信息
	* Author: <user>
    * @param $param
    * @return \think\Response
    */
    public function add<model>($param)
    {
        try {
            $res= $this->insert($param,true);
        } catch(Exception $e) {
            return self::Error([],"新增失败~",400);
        }
        return self::Success($res,"新增成功~",200);
    }

    /**
    * Notes: 根据<pk>获取信息
	* Author: <user>
    * @param $<pk>
    * @return \think\Response
    */
    public function get<model>By<pk>($<pk>)
    {
        try {
            $res = $this->where('<pk>', $<pk>)->find();
        } catch(Exception $e) {

            return self::Error([],"查询数据失败~",400);
        }

        if(!$res)
        {
            return self::Success([],"查询数据为空~",204);
        }
        else
        {
            return self::Success($res,"查询数据成功~",200);
        }
    }

    /**
    * Notes: 编辑信息
	* Author: <user>
    * @param $param
    * @return \think\Response
    */
    public function edit<model>($param)
    {
        try {
            $res=$this->where('<pk>', $param['<pk>'])->update($param);
        } catch(Exception $e) {
            return self::Error([],"编辑失败~",400);
        }
        if($res)
        {
            return self::Success($res,"编辑成功~",200);
        }
        else
        {
            return self::Success($res,"该数据不存在~",204);
        }
    }

    /**
    * Notes: 删除信息
	* Author: <user>
    * @param $<pk>
    * @return \think\Response
    */
    public function del<model>By<pk>($<pk>)
    {
        try {
            <relationDelModel>
            // TODO 不可删除校验
            $res=$this->where('<pk>', $<pk>)->delete();
        } catch(Exception $e) {
            return self::Error([],"删除失败~",400);
        }
        if($res)
        {
            return self::Success($res,"删除成功~",200);
        }
        else
        {
            return self::Success($res,"该数据已被删除~",204);
        }
    }
}

