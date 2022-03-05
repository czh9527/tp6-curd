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

class ExcelAutoMake implements IAutoMake
{
    public function check($controller, $path)
    {
        !defined('DS') && define('DS', DIRECTORY_SEPARATOR);

        $controller = ucfirst($controller)."Excel";
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
        $controllerTpl = dirname(dirname(__DIR__)) . '/tpl/excelcontroller.tpl';
        $tplContent = file_get_contents($controllerTpl);

        $controller = ucfirst($controller)."Excel";
        
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

        $zdvalue=[];
        $zdtype=[];
        $zdms=[];
        foreach ($column as $vo) {
                $zdvalue[]=$vo['Field'];
                $zdtype[]=$vo['Type'];

                $zdms[]=$vo['Comment'];

        }

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

        $tplContent = str_replace('<tableIntroduce>', $tableIntroduce[0]['table_comment'], $tplContent);

        //字母对应循环写入文件
        $btdataData="";
        $btCenterData="";
        $btColomnCenterData="";
        $dygWidthData="";
        $addTableDataData="";
        for($i=0;$i<count($zdvalue);$i++)
        {
            $zm = $this->IntToChr($i);
            //处理
            $btdata="
        ->setCellValue('".$zm."1', '".$zdms[$i]."')";

            $btCenter='
        $objPHPExcel->setActiveSheetIndex(0)->getStyle(\''.$zm.'1'.'\')->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);';

            $btColomnCenter='
        $objPHPExcel->setActiveSheetIndex(0)->getStyle(\''.$zm.'\')->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);';

            $dygWidth='
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension(\''.$zm.'\')->setWidth(10);';

            $addTableData='
        $objPHPExcel->getActiveSheet()->setCellValue(\''.$zm.'\' . ($i + 2), $con[$i][\''.$zdvalue[$i].'\']);';

            $btdataData=$btdataData.$btdata;
            $btCenterData=$btCenterData.$btCenter;
            $btColomnCenterData=$btColomnCenterData.$btColomnCenter;
            $dygWidthData=$dygWidthData.$dygWidth;
            $addTableDataData=$addTableDataData.$addTableData;
        }
        $tplContent = str_replace('<btdata>', $btdataData, $tplContent);
        $tplContent = str_replace('<btCenter>', $btCenterData, $tplContent);
        $tplContent = str_replace('<btColomnCenter>', $btColomnCenterData, $tplContent);
        $tplContent = str_replace('<dygWidth>', $dygWidthData, $tplContent);
        $tplContent = str_replace('<addTableData>', $addTableDataData, $tplContent);


        $file =App::getAppPath() . $filePath . DS . 'controller' . DS . $controller . '.php';
        return file_put_contents($file, $tplContent);

    }
    public function IntToChr($index, $start = 65) {
        $str = '';
        if (floor($index / 26) > 0) {
            $str .= IntToChr(floor($index / 26)-1);
        }
        return $str . chr($index % 26 + $start);
    }
}