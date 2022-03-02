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
use hg\apidoc\annotation as Apidoc;

/**
 * <tableIntroduce>
 * @Apidoc\Group("<model>")
 *
 */
class <controller> extends BaseController
{
    /**
     * @Apidoc\Author("<user>")
     * @Apidoc\Title("获取列表")
     * @Apidoc\Desc("获取最基础数据")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("开发中")
     * @Apidoc\Param("in_search", type="string",default="", desc="模糊查询数据")
     * @Apidoc\Param("page", type="string",default="1", desc="页码")
     * @Apidoc\Param("page_size", type="int",default="1",desc="一页的大小" )
     * @Apidoc\Returned("data", type="array", desc="数据列表",replaceGlobal=true)
     */
    public function index(Request $request)
    {
        $<model>Model = new <model>Model();
		$request_data=$request->param();
		
        if(isset($request_data['in_search'])&&$request_data['in_search']!=''){
            $where = [
                //['part_name','like',"%".$request_data['in_search']."%"],//TODO 需要更改
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
     * @Apidoc\Author("<user>")
     * @Apidoc\Title("新增")
     * @Apidoc\Desc("新增数据")
     * @Apidoc\Method("POST")
     * @Apidoc\Tag("开发中")
<addApidoc>
     * @Apidoc\Returned("data", type="array", desc="数据列表",replaceGlobal=true)
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
     * @Apidoc\Author("<user>")
     * @Apidoc\Title("查询单条数据")
     * @Apidoc\Desc("根据<pk>获取数据")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("开发中")
     * @Apidoc\Param("<pk>", type="int",require=true,default="1", desc="主键")
     * @Apidoc\Returned("data", type="array", desc="数据列表",replaceGlobal=true)
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
     * @Apidoc\Author("<user>")
     * @Apidoc\Title("编辑")
     * @Apidoc\Desc("编辑数据")
     * @Apidoc\Method("PUT")
     * @Apidoc\Tag("开发中")
<editApidoc>
     * @Apidoc\Returned("data", type="array", desc="数据列表",replaceGlobal=true)
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
     * @Apidoc\Author("<user>")
     * @Apidoc\Title("删除")
     * @Apidoc\Desc("根据<pk>删除数据")
     * @Apidoc\Method("DELETE")
     * @Apidoc\Tag("开发中")
     * @Apidoc\Param("<pk>", type="int",require=true,default="1", desc="主键")
     * @Apidoc\Returned("data", type="int", desc="是否删除成功")
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
