<?php
/**
 * Author: <user>
 * Date: <date>
 * Time: <time>
 */
 
namespace app<namespace>controller;

use think\Request;
use hg\apidoc\annotation as Apidoc;

/**
 * <tableIntroduce>导出excel
 * @Apidoc\Group("<model>")
 *
 */
class <controller>
{
    /**
     * excel 读取导入
     */
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
        $objPHPExcel = new \PHPExcel();

        if($file_name =='xlsx' ){
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        }else{
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        }

        $obj_PHPExcel = $objReader->load($file_name, $encode = 'utf-8');  //加载文件内容,编码utf-8
        $excel_array = $obj_PHPExcel->getsheet(0)->toArray();   //转换为数组格式

        return $excel_array;
    }

	/**
     * @Apidoc\Author("<user>")
     * @Apidoc\Title("excel导出")
     * @Apidoc\Desc("导出为excel表")
     * @Apidoc\Method("POST")
     * @Apidoc\Tag("开发中")
     * @Apidoc\Param("in_search", type="string",default="", desc="模糊查询数据")
     * @Apidoc\Param("page", type="string",default="1", desc="页码")
     * @Apidoc\Param("page_size", type="int",default="1",desc="一页的大小" )
     */
    public function output(Request $request)
    {
        $<model>Model = new \app\admin\model\<model>();
        $request_data=$request->param();

        if(isset($request_data['in_search'])&&$request_data['in_search']!=''){
            $where = [
//                ['part_name','like',"%".$request_data['in_search']."%"],//TODO 根据需要修改
            ];
            $res = $<model>Model->getPartsLibList($where, $request_data['page_size']);
        }
        else
        {
            $where = [];
            $res = $<model>Model->get<model>List($where, $request_data['page_size']);
        }

        // 1.选取表中要输出数据
        $con = $res->getData()['data'];

        //3.实例化PHPExcel类
        $objPHPExcel = new \PHPExcel();
        //4.激活当前的sheet表
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $objPHPExcel->setActiveSheetIndex(0)<btdata>;

        // 设置表格头水平居中<btCenter>

        //设置列水平居中<btColomnCenter>

        //设置单元格宽度<dygWidth>

        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
        for ($i = 0; $i < count($con); $i++) { <addTableData>
        }
        //7.设置保存的Excel表格名称
        $filename = '<controller>' . date('ymd', time()) . '.xls';
        //8.设置当前激活的sheet表格名称
        $objPHPExcel->getActiveSheet()->setTitle('<tableIntroduce>');
        //9.设置浏览器窗口下载表格
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $filename . '"');
        //生成excel文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //下载文件在浏览器窗口
        $objWriter->save('php://output');
        exit;

    }

}