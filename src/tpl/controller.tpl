<?php
/**
 * Author: <user>
 * Date: <date>
 * Time: <time>
 */
 
namespace app<namespace>controller;

use app<namespace>exception\BaseController;
use app<namespace>model\<model> as <model>Model;
use app<namespace>validate\<model> as <model>Validate;
use think\exception\ValidateException;
use think\Request;

class <controller> extends BaseController
{
    /**
     * Notes:初始化
     * Author: <user>
     * @return bool
     */
    protected function initialize()
    {
        $this->model=new <model>Model();
        return parent::initialize(); 
    }
    /**
    * Notes: 获取列表
	* Author: <user>
    */
    public function index(Request $request)
    {
		$request_data=$request->param();
		
        if(isset($request_data['in_search'])&&$request_data['in_search']!=''){
            $where = [
                //['part_name','like',"%".$request_data['in_search']."%"],//TODO 需要更改
                ];
            $res = $this->model->get<model>List($where, $this->pageSize);
        }
        else
        {
            $where = [];
            $res = $this->model->get<model>List($where, $this->pageSize);
        }
		
        return $res;
    }

    /**
    * Notes: 添加
	* Author: <user>
    */
    public function add(Request $request)
    {
        $request_data=$request->param();

        // 检验完整性
        try {
            validate(<model>Validate::class)->scene('add')->check($request_data);
        } catch (ValidateException $e) {
            return self::Error([],$e->getError(),400);
        }

        $res = $this->model->add<model>($request_data);
        return $res;

    }

    /**
    * Notes: 根据<pk>查询信息
	* Author: <user>
    */
    public function read(Request $request)
    {
        $request_data=$request->param();
		
        $<pk>=$request_data['<pk>'];
        $res = $this->model->get<model>By<pk>($<pk>);
        return $res;
    }

    /**
    * Notes: 编辑
	* Author: <user>
    */
    public function edit(Request $request)
    {
        $request_data=$request->param();

        // 检验完整性
        try {
            validate(<model>Validate::class)->scene('edit')->check($request_data);
        } catch (ValidateException $e) {
            return self::Error([],$e->getError(),400);
        }

        $res = $this->model->edit<model>($request_data);
        return $res;
    }

    /**
    * Notes: 删除
	* Author: <user>
    */
    public function del(Request $request)
    {
        $request_data=$request->param();
		
        $<pk> = $request_data['<pk>'];

        $res = $this->model->del<model>By<pk>($<pk>);
        return $res;
   }
}
