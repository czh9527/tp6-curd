<?php
/**
 * Created by PhpStorm.
 * Date: 2021/7/8
 * Time: 11:23 PM
 */
namespace czh9527\tp6curd\template\impl;

use czh9527\tp6curd\extend\Utils;
use czh9527\tp6curd\template\IAutoMake;
use think\facade\App;
use think\facade\Db;
use think\console\Output;

class ModelAutoMake implements IAutoMake
{
    public function check($table, $path)
    {
        !defined('DS') && define('DS', DIRECTORY_SEPARATOR);

        $modelName = Utils::camelize($table);
        $modelFilePath = App::getAppPath() . $path . DS . 'model' . DS . $modelName . '.php';

        if (!is_dir(App::getAppPath() . $path . DS . 'model')) {
            mkdir(App::getAppPath() . $path . DS . 'model', 0755, true);
        }

        if (file_exists($modelFilePath)) {
            $output = new Output();
            $output->error("\033[31m"."$modelFilePath 已经存在"."\033[0m");
            exit;
        }
    }

    public function make($table, $path, $relations)
    {

 
        $controllerTpl = dirname(dirname(__DIR__)) . '/tpl/model.tpl';

        $tplContent = file_get_contents($controllerTpl);


        $model = ucfirst(Utils::camelize($table));
        $filePath = empty($path) ? '' : DS . $path;
        $namespace = empty($path) ? '\\' : '\\' . $path . '\\';

        $prefix = config('database.connections.mysql.prefix');
        $column = Db::query('SHOW FULL COLUMNS FROM `' . $prefix . $table . '`');
        $pk = 'id';
        foreach ($column as $vo) {
            if ($vo['Key'] == 'PRI') {
                $pk = $vo['Field'];
                break;
            }
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
        $tplContent = str_replace('<model>', $model, $tplContent);
        $tplContent = str_replace('<pk>', $pk, $tplContent);
    
        //匹配关联表信息

        
        
        
        $relationModelData='';
        $frelationModelData='';
        $relationDelModelData='';
        

        
        if(count($relations)!=0)//判断是否有关联关系
        {
            

            for($i=0;$i<count($relations)/4;$i++) {//循环输出
                
                if ($relations[0+$i*4] == $model)//创建的是主表
                {
                    $relationModel = file_get_contents(dirname(dirname(__DIR__)) . '/tpl/relationModel.tpl');
                    $relationDelModel = file_get_contents(dirname(dirname(__DIR__)) . '/tpl/relationDelModel.tpl');
                    
                    $wayName = lcfirst($relations[2+$i*4]);
    

                    $relationModel = str_replace('<table>', $relations[0+$i*4], $relationModel);
                    $relationModel = str_replace('<tableName>', $relations[1+$i*4], $relationModel);
                    $relationModel = str_replace('<foreignTable>', $relations[2+$i*4], $relationModel);
                    $relationModel = str_replace('<foreignName>', $relations[3+$i*4], $relationModel);
                    
                    $relationModel = str_replace('<wayName>', $wayName, $relationModel);
                    

                    $relationDelModel = str_replace('<tableName>', $relations[1+$i*4], $relationDelModel);
                    $relationDelModel = str_replace('<foreignTable>', $relations[2+$i*4], $relationDelModel);
                    $relationDelModel = str_replace('<foreignName>', $relations[3+$i*4], $relationDelModel);
                  
         
   
                    $relationModelData=$relationModelData.$relationModel;
                    $relationDelModelData=$relationDelModelData.$relationDelModel;

                    


                } else//创建附表
                {
                    $frelationModel = file_get_contents(dirname(dirname(__DIR__)) . '/tpl/frelationModel.tpl');
                    $wayName = lcfirst($relations[0+$i*4]);
                    


                    $frelationModel = str_replace('<table>', $relations[0+$i*4], $frelationModel);
                    $frelationModel = str_replace('<tableName>', $relations[1+$i*4], $frelationModel);
                    $frelationModel = str_replace('<foreignTable>', $relations[2+$i*4], $frelationModel);
                    $frelationModel = str_replace('<foreignName>', $relations[3+$i*4], $frelationModel);
                    
                    $frelationModel = str_replace('<wayName>', $wayName, $frelationModel);
                    $frelationModelData=$frelationModelData.$frelationModel;
                }
                
            }
            
            $tplContent = str_replace('<frelationModel>', $frelationModelData, $tplContent);
            $tplContent = str_replace('<relationModel>', $relationModelData, $tplContent);
            $tplContent = str_replace('<relationDelModel>', $relationDelModelData, $tplContent);

            
        }
        else//没有关联关系，全部替换为空
        {
            $tplContent = str_replace('<frelationModel>', '', $tplContent);
            $tplContent = str_replace('<relationModel>', '', $tplContent);
            $tplContent = str_replace('<relationDelModel>', '', $tplContent);
        }



        $file =App::getAppPath() . $filePath . DS . 'model' . DS . $model . '.php';
        return file_put_contents($file, $tplContent);
    }
}