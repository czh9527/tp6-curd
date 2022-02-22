<?php

namespace app<namespace>model;

use app<namespace>common\Output;
use think\model;

class <model> extends Model
{
	use Output;
	/**
     * 封装好的返回数据
     */
    public function returnData($res,$successStr="请求成功~",$errorStr="请求失败~")
    {
        if ($res) {
            return self::Success($res,$successStr,200);
        } else {
            return self::Error($res,$errorStr,400);
        }
    }
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
            return $this->returnData([]);
        }
        return $this->returnData($res);
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
            return $this->returnData([]);
        }
        return $this->returnData($res);
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

            return $this->returnData([]);
        }

        return $this->returnData($res);
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
            return $this->returnData([]);
        }

        return $this->returnData($res,"编辑成功~","当前数据不存在~");
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
            return $this->returnData([]);
        }

        return $this->returnData($res);
    }
}

