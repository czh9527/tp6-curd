<?php
/**
 * Author: <user>
 * Date: <date>
 * Time: <time>
 */
 
namespace app<namespace>controller;

use app<namespace>exception\BaseController;
use think\exception\ValidateException;
use think\Request;
use think\facade\Db;
use hg\apidoc\annotation as Apidoc;

/**
 * <tableIntroduce>
 * @Apidoc\Group("Base")
 *
 */
class <controller> extends BaseController
{
    /**
     * <model>模型对象
     * @var \app<namespace>model\<model>
     */
    protected $model = null;
    public function initialize()
    {
        parent::initialize();
        $this->model = new \app<namespace>model\<model>;
    }<getChilds>

    /**
     * @Apidoc\Author("<user>")
     * @Apidoc\Title("获取列表")
     * @Apidoc\Desc("获取最基础数据")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("开发中")
     * @Apidoc\Param("in_search", type="string",default="", desc="模糊查询数据")
     * @Apidoc\Param(ref="page")
     * @Apidoc\Param("is_all", type="int",default="0",desc="是否输出全部数据，默认使用分页输出")
     * @Apidoc\Returned(ref="page")
     * @Apidoc\Returned("data",type="array",childrenType="object",desc="业务数据",
     *      @Apidoc\Returned (ref="app<namespace>model\<model>\getReturn"),
     * )
     */
    public function index(Request $request)
    {
        $request_data=$request->param();

        $is_all= $request_data['is_all'] ?? null;
        $where = [
                    [],//TODO 继续添加查询条件
                ];
        if(isset($request_data['in_search'])&&$request_data['in_search']!=''){
            //$where[] = ['part_name','like',"%".$request_data['in_search']."%"];//TODO 需要更改
        }

        return $this->model->get<model>List($where, $this->pageSize,$is_all);
    }

	/**
     * @Apidoc\Author("<user>")
     * @Apidoc\Title("查询-详细")
     * @Apidoc\Desc("根据<pk>查询数据，支持单条，也支持多条(传数组)")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("开发中")
     * @Apidoc\Param("<pk>", type="int",require=true,default="1", desc="主键")
     * @Apidoc\Returned(ref="app<namespace>model\<model>\getReturn")
     */
    public function read(Request $request)
    {
        $request_data=$request->param();
        return $this->model->get<model>By<pk>($request_data['<pk>']);
    }

    /**
     * @Apidoc\Author("<user>")
     * @Apidoc\Title("新增")
     * @Apidoc\Desc("新增数据")
     * @Apidoc\Method("POST")
     * @Apidoc\Tag("开发中")
<addApidoc>     * @Apidoc\Returned("data", type="int", desc="主键<pk>",replaceGlobal=true)
     */
    public function add(Request $request)
    {
        $request_data=$request->param();

        // 检验完整性
        try {
            validate(\app<namespace>validate\<model>::class)->scene('add')->check($request_data);
        } catch (ValidateException $e) {
            return self::Error([],$e->getError(),400);
        }
        $allowField=<allowField>;
        return $this->model->add<model>($request_data,$allowField);
    }

    /**
     * @Apidoc\Author("<user>")
     * @Apidoc\Title("编辑")
     * @Apidoc\Desc("编辑数据")
     * @Apidoc\Method("POST")
     * @Apidoc\Tag("开发中")
<editApidoc>     * @Apidoc\Returned(ref="app<namespace>model\<model>\getReturn")
     */
    public function edit(Request $request)
    {
        $request_data=$request->param();

        // 检验完整性
        try {
            validate(\app<namespace>validate\<model>::class)->scene('edit')->check($request_data);
        } catch (ValidateException $e) {
            return self::Error([],$e->getError(),400);
        }
        $allowField=<allowField>;
        return $this->model->edit<model>($request_data,$allowField);
    }

    /**
     * @Apidoc\Author("<user>")
     * @Apidoc\Title("删除")
     * @Apidoc\Desc("根据<pk>删除数据，支持单条，也支持多条(传数组)")
     * @Apidoc\Method("POST")
     * @Apidoc\Tag("开发中")
     * @Apidoc\Param("<pk>", type="int",require=true,default="1", desc="主键")
     * @Apidoc\Returned("data", type="bool", desc="是否删除成功",replaceGlobal=true)
     */<delete>

   	/**
     * @Apidoc\Author("<user>")
     * @Apidoc\Title("excel导入")
     * @Apidoc\Desc("读取excel来获取数据")
     * @Apidoc\Method("POST")
     * @Apidoc\Tag("开发中")
     * @Apidoc\Param("path", type="string",default="", desc="excel文件路径")
     * @Apidoc\Returned("data", type="array", desc="数据列表",replaceGlobal=true)
     */
    public function input(Request $request)
    {
        $request_data=$request->param();
        //读取excel文件
        $file_name = $request_data['path'];

        $obj_PHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_name);  //加载文件内容,编码utf-8
        $excel_array = $obj_PHPExcel->getsheet(0)->toArray();   //转换为数组格式

//        插入数据库
        $res=[];

        for($i=1;$i<count($excel_array);$i++)
        {
            $param=[];<param>
            Db::startTrans();
            try {
                $res[]=$this->model->insert($param,true);
                Db::commit();
            } catch(\Exception $e) {
                Db::rollback();
                $res[]="第".$i."条数据导入失败~";
            }
        }
        $flagArray=[];
        foreach ($res as $value)
        {
            $flag = 1;
            if(strpos($value,"导入失败"))
            {
                $flag=0;
            }
            $flagArray[]=$flag;
        }
        $sum=0;
        foreach ($flagArray as $value)
        {
            $sum=$sum+$value;
        }
        if($sum==count($flagArray))
        {
            return self::Success($res,"全部导入成功~",200);
        }
        else if($sum==0)
        {
            return self::Error($res,"全部导入失败~",400);
        }
        else
        {
            return self::Error($res,"部分导入成功~",204);
        }
    }

	/**
     * @Apidoc\Author("<user>")
     * @Apidoc\Title("excel导出")
     * @Apidoc\Desc("导出为excel表")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("开发中")
     * @Apidoc\Param("in_search", type="string",default="", desc="模糊查询数据")
     * @Apidoc\Param("page", type="string",default="1", desc="页码")
     * @Apidoc\Param("page_size", type="int",default="1",desc="一页的大小" )
     * @Apidoc\Param("is_all", type="int",default="0",desc="是否输出全部数据，默认使用分页输出")
     */
    public function output(Request $request)
    {
        $res=$this->index($request);
        // 1.选取表中要输出数据
        $con = $res->getData()['data'];

        //3.实例化PHPExcel类
        $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        //4.激活当前的sheet表
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $objPHPExcel->setActiveSheetIndex(0)<btdata>;

        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];

        // 设置表格头水平居中<btCenter>

        //设置列水平居中<btColomnCenter>

        //设置单元格宽度<dygWidth>

        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
        for ($i = 0; $i < count($con); $i++) { <addTableData>
        }
        //7.设置保存的Excel表格名称
        $filename = '<model>-' . date('Y-m-d G:i:s', time()) . '.xls';
        //8.设置当前激活的sheet表格名称
        $objPHPExcel->getActiveSheet()->setTitle('<tableIntroduce>');
        //9.设置浏览器窗口下载表格
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $filename . '"');
        //生成excel文件
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xls');
        //下载文件在浏览器窗口
        $objWriter->save('php://output');
        exit;
    }
}
