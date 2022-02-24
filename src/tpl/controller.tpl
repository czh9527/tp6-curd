<?php

namespace app<namespace>controller;

use app<namespace>exception\BaseController;
use app<namespace>model\<model> as <model>Model;
use app<namespace>validate\<model> as <model>Validate;
use think\exception\ValidateException;
use think\Request;

class <controller> extends BaseController
{
    /**
    * 获取列表
    */
    public function index(Request $request)
    {
        $<model>Model = new <model>Model();
		$request_data=$request->param();
		
        if(isset($request_data['in_search'])&&$request_data['in_search']!=''){
            $where = [
                //'title'=>['like',$request_data['in_search']],//需要修改
                ];
            $res = $<model>Model->get<model>List($where, $this->pageSize);
        }
        else
        {
            $where = [];
            $res = $<model>Model->get<model>List($where, $this->pageSize);
        }
		
        return $res;
    }

    /**
    * 添加
    */
    public function add(Request $request)
    {
		$<model>Model = new <model>Model();
        $request_data=$request->param();

        // 检验完整性
        try {
            validate(<model>Validate::class)->scene('add')->check($request_data);
        } catch (ValidateException $e) {
            return self::Error([],$e->getError(),400);
        }

        $res = $<model>Model->add<model>($request_data);
        return $res;

    }

    /**
    * 根据<pk>查询信息
    */
    public function read(Request $request)
    {
		$<model>Model = new <model>Model();
        $request_data=$request->param();
        $<pk>=$request_data['<pk>'];
		
        $res = $<model>Model->get<model>By<pk>($<pk>);
        return $res;
    }

    /**
    * 编辑
    */
    public function edit(Request $request)
    {
        $<model>Model = new <model>Model();
        $request_data=$request->param();

        // 检验完整性
        try {
            validate(<model>Validate::class)->scene('edit')->check($request_data);
        } catch (ValidateException $e) {
            return self::Error([],$e->getError(),400);
        }

        $res = $<model>Model->edit<model>($request_data);
        return $res;
    }

    /**
    * 删除
    */
    public function del(Request $request)
    {
        $<model>Model = new <model>Model();
        $request_data=$request->param();
        $<pk> = $request_data['<pk>'];

        $res = $<model>Model->del<model>By<pk>($<pk>);
        return $res;
   }
}
