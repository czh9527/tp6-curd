<?php

namespace czh9527\tp6curd\template\impl;

use czh9527\tp6curd\extend\Utils;
use czh9527\tp6curd\template\IAutoMake;
use Symfony\Component\VarExporter\VarExporter;
use think\console\Output;
use think\facade\App;
use think\facade\Db;

class ValidateAutoMake implements IAutoMake
{
    public function check($table, $path)
    {
        $validateName = Utils::camelize($table) ;
        $validateFilePath = App::getAppPath() . $path . DS . 'validate' . DS . $validateName . '.php';

        if (!is_dir(App::getAppPath() . $path . DS . 'validate')) {
            mkdir(App::getAppPath() . $path . DS . 'validate', 0755, true);
        }

        if (file_exists($validateFilePath)) {
            $output = new Output();
            $output->error("\033[31m"."$validateFilePath 已经存在"."\033[0m");
            exit;
        }
    }

    public function make($table, $path, $relations)
    {
        $validateTpl = dirname(dirname(__DIR__)) . '/tpl/validate.tpl';
        $tplContent = file_get_contents($validateTpl);

        $model = ucfirst(Utils::camelize($table));
        $filePath = empty($path) ? '' : DS . $path;
        $namespace = empty($path) ? '\\' : '\\' . $path . '\\';

        $prefix = config('database.connections.mysql.prefix');
        $column = Db::query('SHOW FULL COLUMNS FROM `' . $prefix . $table . '`');
        $rule = [];
        $attributes = [];
        $pk = 'id';
        $json=[];
        $Comment=[];
        foreach ($column as $vo) {
            if ($vo['Key'] == 'PRI') {
                $pk = $vo['Field'];
                break;
            }
        }
        foreach ($column as $vo) {
            //编辑json
            if(strpos($vo['Type'],'int')!== false)
            {
                $json[$vo['Field']]=1;
            }
            else if(!strpos($vo['Type'],'varchar')!== false)
            {
                $json[$vo['Field']]='test';
            }
            else if(!strpos($vo['Type'],'float')!== false)
            {
                $json[$vo['Field']]=1;
            }
            else
            {
                $json[$vo['Field']]='修改';
            }
            /////
            $rule[$vo['Field'].'|'.$vo['Comment']] = 'require';
            $Comment[$vo['Field']] = $vo['Comment'];
//            $attributes[$vo['Field']] = '需要'.$vo['Comment'].'~';
            $edits[]=$vo['Field'];
            if($vo['Field'] != $pk)
            {
                $adds[]=$vo['Field'];
            }
        }
        /////czh
        file_put_contents("1.log",'111111');
        $addvalidateData= '';
        if(count($relations)!=0)//判断是否有关联关系
        {
            for($i=0;$i<count($relations)/4;$i++) {//循环输出
                
                if ($relations[0+$i*4] == $model)//创建的是主表
                {
                    $tplContent = str_replace('<addValidate>', '', $tplContent);
                } else//创建附表
                {

                    
                    $addvalidate = file_get_contents(dirname(dirname(__DIR__)) . '/tpl/addvalidate.tpl');

                    $addvalidate = str_replace('<table>', $relations[0+$i*4], $addvalidate);
                    $addvalidate = str_replace('<foreignName>', $relations[3+$i*4], $addvalidate);
                    
                    
                    $rule[$relations[3+$i*4].'|'.$Comment[$relations[3+$i*4]]]=$rule[$relations[3+$i*4].'|'.$Comment[$relations[3+$i*4]]].'|'.'check'.$relations[0+$i*4];
                    file_put_contents("1.log",'2222'.$addvalidate);
                    $addvalidateData=$addvalidateData.$addvalidate;
                    
                }

            }
            file_put_contents("1.log",'3333');
            $tplContent = str_replace('<addValidate>', $addvalidateData, $tplContent);
        }

        
        file_put_contents(App::getAppPath() . $filePath . DS . 'controller' . DS .
            $table.".易文档传参.log",json_encode($json));//写外部json

        $ruleArr = VarExporter::export($rule);
        $attributesArr = VarExporter::export($attributes);
        $addsArr = VarExporter::export($adds);
        $editsArr = VarExporter::export($edits);

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
        $tplContent = str_replace('<rule>', '' . $ruleArr, $tplContent);
        $tplContent = str_replace('<attributes>', $attributesArr, $tplContent);
        $tplContent = str_replace('<adds>', $addsArr,$tplContent);
        $tplContent = str_replace('<edits>', $editsArr,$tplContent);

        $file =App::getAppPath() . $filePath . DS . 'validate' . DS . $model . '.php';
        return file_put_contents($file, $tplContent);
    }
}