<?php
/**
 * Created by PhpStorm.
 * Date: 2021/7/8
 * Time: 10:49 PM
 */
namespace czh9527\tp6curd\template\impl;

use czh9527\tp6curd\extend\Utils;
use czh9527\tp6curd\template\IAutoMake;
use think\facade\App;
use think\facade\Db;
use think\console\Output;

class ControllerAutoMake implements IAutoMake
{
    public function check($controller, $path)
    {
        !defined('DS') && define('DS', DIRECTORY_SEPARATOR);

        $controller = ucfirst($controller);
        $controllerFilePath = App::getAppPath() . $path . DS . 'controller' . DS . $controller . '.php';

        if (!is_dir(App::getAppPath() . $path . DS . 'controller')) {
            mkdir(App::getAppPath() . $path . DS . 'controller', 0755, true);
        }
        
        if (file_exists($controllerFilePath)) {
            $output = new Output();
            $output->error("\033[31m"."$controllerFilePath 已经存在"."\033[0m");
            exit;
        }
    }

    public function make($controller, $path, $table)
    {
        $controllerTpl = dirname(dirname(__DIR__)) . '/tpl/controller.tpl';
        $tplContent = file_get_contents($controllerTpl);

        $controller = ucfirst($controller);
        $model = ucfirst(Utils::camelize($table));
        $filePath = empty($path) ? '' : DS . $path;
        $namespace = empty($path) ? '\\' : '\\' . $path . '\\';

        $prefix = config('database.connections.mysql.prefix');
        $column = Db::query('SHOW FULL COLUMNS FROM `' . $prefix . $table . '`');

        $database = config('database.connections.mysql.database');
        $tableIntroduce = Db::query('SELECT table_comment FROM information_schema.TABLES WHERE table_schema ='.'\'' . $database .'\''.' and table_name =' .'\''.$table. '\'');

        $pk = 'id';
        $pkList=[];
        foreach ($column as $vo) {
            if ($vo['Key'] == 'PRI') {
                $pk = $vo['Field'];
                $pkList[]=$vo['Field'];
                $pkList[]=$vo['Type'];
                $pkList[]=1;
                $pkList[]=$vo['Comment'];
                break;
            }
        }
        ///处理有pid树的情况
        $have_pid=false;
        foreach ($column as $vo) {
            if ($vo['Field'] == 'pid') {
                $have_pid=true;
                break;
            }
        }

        $delete="    
    public function del(Request \$request)
    {
        $<model>Model = new <model>Model();
        \$request_data=\$request->param();
        $<pk> = \$request_data['<pk>'];

        \$res = $<model>Model->del<model>By<pk>($<pk>);
        return \$res;
   }";
        $deleteHavePid="    
    public function del(Request \$request)
    {
        \$<model>Model = new <model>Model();
        \$request_data=\$request->param();

        \$<model>Model->idList=[];//初始化id列表
        \$<model>Model->idList[]=\$request_data['<pk>'];//放入自己

        \$<model>Model->getAllListByPid(\$request_data['<pk>']);//获取所有孩子
        return \$<model>Model->del<model>Byid(\$<model>Model->idList);

   }";
        $getChils='    
   /**
     * @Apidoc\Author("<user>")
     * @Apidoc\Title("获取自己+所有子")
     * @Apidoc\Desc("根据id递归获取所有子（包括孩子的孩子）")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("开发中")
     * @Apidoc\Param("id", type="int",require=true,default="1", desc="当前节点id")
     * @Apidoc\Returned("data", type="array", desc="数据列表",replaceGlobal=true)
     */
    public function getchilds(Request $request)
    {
        $<model>Model = new <model>Model();
        $request_data=$request->param();
        $<model>Model->idList=[];
        $<model>Model->idList[]=$request_data[\'id\'];//加入自己
        //递归所有子，再输出
        $<model>Model->getAllListByPid($request_data[\'id\']);
        return $<model>Model->get<model>Byid($<model>Model->idList);
    }';

        if($have_pid)
        {
            $tplContent = str_replace('<delete>', $deleteHavePid, $tplContent);
            $tplContent = str_replace('<getChilds>', $getChils, $tplContent);
        }
        else{
            $tplContent = str_replace('<delete>', $delete, $tplContent);
            $tplContent = str_replace('<getChilds>', '', $tplContent);
        }
        //apidoc
//        * @Apidoc\Param("<zdvalue>", type="<zdtype>",require=true,default="<zddefault>", desc="<zdms>")

        $zdvalue=[];
        $zdtype=[];
        $zddefault=[];
        $zdms=[];
        foreach ($column as $vo) {
            if($vo['Key']!='PRI' && $vo['Field']!='create_time' && $vo['Field']!='create_user' &&$vo['Field']!='compy_id' )
            {
                $zdvalue[]=$vo['Field'];
                $zdtype[]=$vo['Type'];
                //编辑json
                if(strpos($vo['Type'],'int')!== false)
                {
                    $zddefault[]=1;
                }
                else if(!strpos($vo['Type'],'varchar')!== false)
                {
                    $zddefault[]='测试';
                }
                else if(!strpos($vo['Type'],'float')!== false)
                {
                    $zddefault[]=1;
                }
                else
                {
                    $zddefault[]='修改';
                }
                $zdms[]=$vo['Comment'];
            }

        }
        
        $apidocdata='';
      
        for($j=0;$j<count($zdvalue);$j++)
        {
            $apidoc = file_get_contents(dirname(dirname(__DIR__)) . '/tpl/apidoc.tpl');
            $apidoc = str_replace('<zdvalue>', $zdvalue[$j], $apidoc);
            $apidoc = str_replace('<zdtype>', $zdtype[$j], $apidoc);
            $apidoc = str_replace('<zddefault>', $zddefault[$j], $apidoc);
            $apidoc = str_replace('<zdms>', $zdms[$j], $apidoc);
            $apidocdata=$apidocdata.$apidoc;
            
        }
        
        $tplContent = str_replace('<addApidoc>', $apidocdata, $tplContent);
        //编辑需要主键
        $apidoc = file_get_contents(dirname(dirname(__DIR__)) . '/tpl/apidoc.tpl');
        //有主键的时候才执行
        if(count($pkList)==4)
        {
            $apidoc = str_replace('<zdvalue>', $pkList[0], $apidoc);
            $apidoc = str_replace('<zdtype>', $pkList[1], $apidoc);
            $apidoc = str_replace('<zddefault>', $pkList[2], $apidoc);
            $apidoc = str_replace('<zdms>', $pkList[3], $apidoc);
            $apidocdata=$apidocdata.$apidoc;
        }
        $tplContent = str_replace('<editApidoc>', $apidocdata, $tplContent);

        //确定注册的创建时间，创建人等
        $user=get_current_user();
        $date=date('Y-m-d');
        $time=date('G:i');
        $tplContent = str_replace('<user>', $user, $tplContent);
        $tplContent = str_replace('<date>', $date, $tplContent);
        $tplContent = str_replace('<time>', $time, $tplContent);
        //
        
        $tplContent = str_replace('<namespace>', $namespace, $tplContent);
        $tplContent = str_replace('<controller>', $controller, $tplContent);
        $tplContent = str_replace('<model>', $model, $tplContent);
        $tplContent = str_replace('<pk>', $pk, $tplContent);

        $tplContent = str_replace('<tableIntroduce>', $tableIntroduce[0]['table_comment'], $tplContent);
        
        //处理excel
        //字母对应循环写入文件
        $zdvalue=[];
        $zdtype=[];
        $zdms=[];
        foreach ($column as $vo) {
            $zdvalue[]=$vo['Field'];
            $zdtype[]=$vo['Type'];

            $zdms[]=$vo['Comment'];

        }
        $btdataData="";
        $btCenterData="";
        $btColomnCenterData="";
        $dygWidthData="";
        $addTableDataData="";
        $paramData="";
        for($i=0;$i<count($zdvalue);$i++)
        {
            $zm = $this->IntToChr($i);
            //处理
            $param='
            $param[\''.$zdvalue[$i].'\']=$excel_array[$i]['.$i.'];';

            $btdata="
        ->setCellValue('".$zm."1', '".$zdms[$i]."')";

            $btCenter='
        $objPHPExcel->setActiveSheetIndex(0)->getStyle(\''.$zm.'1'.'\')->applyFromArray($styleArray);';

            $btColomnCenter='
        $objPHPExcel->setActiveSheetIndex(0)->getStyle(\''.$zm.'\')->applyFromArray($styleArray);';

            $dygWidth='
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension(\''.$zm.'\')->setWidth(10);';

            $addTableData='
        $objPHPExcel->getActiveSheet()->setCellValue(\''.$zm.'\' . ($i + 2), $con[$i][\''.$zdvalue[$i].'\']);';

            $paramData=$paramData.$param;
            $btdataData=$btdataData.$btdata;
            $btCenterData=$btCenterData.$btCenter;
            $btColomnCenterData=$btColomnCenterData.$btColomnCenter;
            $dygWidthData=$dygWidthData.$dygWidth;
            $addTableDataData=$addTableDataData.$addTableData;
        }
        $tplContent = str_replace('<param>', $paramData, $tplContent);
        $tplContent = str_replace('<btdata>', $btdataData, $tplContent);
        $tplContent = str_replace('<btCenter>', $btCenterData, $tplContent);
        $tplContent = str_replace('<btColomnCenter>', $btColomnCenterData, $tplContent);
        $tplContent = str_replace('<dygWidth>', $dygWidthData, $tplContent);
        $tplContent = str_replace('<addTableData>', $addTableDataData, $tplContent);
        



        $file =App::getAppPath() . $filePath . DS . 'controller' . DS . $controller . '.php';
        return file_put_contents($file, $tplContent);

        // 检测base是否存在--现在不创建babse
//        if (!file_exists(App::getAppPath() . $filePath . DS . 'controller' . DS . 'Base.php')) {
//
//            $controllerTpl = dirname(dirname(__DIR__)) . '/tpl/baseController.tpl';
//            $tplContent = file_get_contents($controllerTpl);
//
//            $tplContent = str_replace('<namespace>', $namespace, $tplContent);
//
//            file_put_contents(App::getAppPath() . $filePath . DS . 'controller' . DS . 'Base.php', $tplContent);
//        }
    }

    public static function IntToChr($pColumnIndex = 0)
    {
        static $_indexCache = array();

        if (!isset($_indexCache[$pColumnIndex])) {
            // Determine column string
            if ($pColumnIndex < 26) {
                $_indexCache[$pColumnIndex] = chr(65 + $pColumnIndex);
            } elseif ($pColumnIndex < 702) {
                $_indexCache[$pColumnIndex] = chr(64 + ($pColumnIndex / 26)) .
                    chr(65 + $pColumnIndex % 26);
            } else {
                $_indexCache[$pColumnIndex] = chr(64 + (($pColumnIndex - 26) / 676)) .
                    chr(65 + ((($pColumnIndex - 26) % 676) / 26)) .
                    chr(65 + $pColumnIndex % 26);
            }
        }
        return $_indexCache[$pColumnIndex];
    }
}