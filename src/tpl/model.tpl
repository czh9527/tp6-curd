<?php

namespace app<namespace>model;

use app<namespace>common\Output;
use think\model;

class <model> extends Model
{
	use Output;
    /**
    * 获取分页列表
    * @param $where
    * @param $limit
    * @return array
    */
    public function get<model>List($where, $page_size)
    {
        try {
            $res = $this->where($where)->order('id', 'asc')->paginate($page_size);
        } catch(\Exception $e) {
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
    * 添加信息
    * @param $param
    * @return $array
    */
    public function add<model>($param)
    {
        try {
            $res= $this->insert($param,true);
        } catch(\Exception $e) {
            return self::Error([],"新增失败~",400);
        }
        return self::Success($res,"新增成功~",200);
    }

    /**
    * 根据id获取信息
    * @param $id
    * @return array
    */
    public function get<model>ById($id)
    {
        try {
            $res = $this->where('id', $id)->find();
        } catch(\Exception $e) {

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
    * 编辑信息
    * @param $param
    * @return array
    */
    public function edit<model>($param)
    {
        try {
            $res=$this->where('id', $param['id'])->update($param);
        } catch(\Exception $e) {
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
    * 删除信息
    * @param $id
    * @return array
    */
    public function del<model>ById($id)
    {
        try {
            // TODO 不可删除校验
            $res=$this->where('id', $id)->delete();
        } catch(\Exception $e) {
            return self::Error([],"删除失败~",400);
        }
        if($res)
        {
            return self::Success($res,"删除成功~",200);
        }
        else
        {
            return self::Success($res,"该数据不存在~",204);
        }
    }
}

