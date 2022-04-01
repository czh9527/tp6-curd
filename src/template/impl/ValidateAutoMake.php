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
            return true;
        }
        return false;
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
        $database = config('database.connections.mysql.database');
        $tableIntroduce = Db::query('SELECT table_comment FROM information_schema.TABLES WHERE table_schema ='.'\'' . $database .'\''.' and table_name =' .'\''.$table. '\'');

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
        $adds=[];
        $edits=[];
        foreach ($column as $vo) {
            if($vo['Field']!='create_time'&&$vo['Field']!='update_time' && $vo['Field']!='create_user' && $vo['Field']!='compy_id') {
                //编辑json
                if (strpos($vo['Type'], 'int') !== false) {
                    $json[$vo['Field']] = 1;
                } else if (!strpos($vo['Type'], 'varchar') !== false) {
                    $json[$vo['Field']] = 'test';
                } else if (!strpos($vo['Type'], 'float') !== false) {
                    $json[$vo['Field']] = 1;
                } else {
                    $json[$vo['Field']] = '修改';
                }
                $edits[]=$vo['Field'];
                if($vo['Field'] != $pk)
                {
                    $adds[]=$vo['Field'];
                }
            }
            /////
            $rule[$vo['Field'].'|'.$vo['Comment']] = 'require';
            $Comment[$vo['Field']] = $vo['Comment'];
//            $attributes[$vo['Field']] = '需要'.$vo['Comment'].'~';

        }
        /////czh
        file_put_contents("1.log",'111111');
        $addvalidateData= '';
        if(count($relations)!=0)//判断是否有关联关系
        {
            for($i=0;$i<count($relations)/4;$i++) {//循环输出
                
                if ($relations[0+$i*4] == $model)//创建的是主表
                {

                } else//创建附表
                {

                    
                    $addvalidate = file_get_contents(dirname(dirname(__DIR__)) . '/tpl/addvalidate.tpl');

                    $addvalidate = str_replace('<table>', $relations[0+$i*4], $addvalidate);
                    $addvalidate = str_replace('<foreignName>', $relations[3+$i*4], $addvalidate);
                    
                    
                    $rule[$relations[3+$i*4].'|'.$Comment[$relations[3+$i*4]]]=$rule[$relations[3+$i*4].'|'.$Comment[$relations[3+$i*4]]].'|'.'check'.$relations[0+$i*4];

                    $addvalidateData=$addvalidateData.$addvalidate;
                    
                }

            }

            $tplContent = str_replace('<addValidate>', $addvalidateData, $tplContent);
        }
        else
        {
            $tplContent = str_replace('<addValidate>', '', $tplContent);

        }

        


        $ruleArr = VarExporter::export($rule);
        $attributesArr = VarExporter::export($attributes);
        $addsArr = $this->createStr($adds);
        $editsArr = $this->createStr($edits);

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
        $tplContent = str_replace('<tableIntroduce>', $tableIntroduce[0]['table_comment'], $tplContent);


        $file =App::getAppPath() . $filePath . DS . 'validate' . DS . $model . '.php';
        return $this->makeFile($file, $tplContent);
    }
    public function createStr($zd)
    {
        //组合数组字符串
        $str="[";
        foreach ($zd as $key => $value)
        {
            ($key+1)%5==0? $str.="'".$value."'".','."\r\n"."\t\t\t\t\t" :$str.="'".$value."'".',';

        }
        $str.="]";
        return $str;
    }
    public function makeFile($file,$tplContent)
    {
        $output = new Output();
        return file_put_contents($file, $tplContent)
            ?$output->info("\033[32m".$file."创建成功"."\033[0m")
            :$output->info($file."创建失败");
    }
}