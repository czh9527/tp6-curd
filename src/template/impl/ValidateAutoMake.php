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
    
    public function make($table, $path, $other)
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
        foreach ($column as $vo) {
            if ($vo['Key'] == 'PRI') {
                $pk = $vo['Field'];
                break;
            }
        }
        foreach ($column as $vo) {
            $rule[$vo['Field']] = 'require';
            $attributes[$vo['Field']] = '需要'.$vo['Comment'].'~';
            $edits[]=$vo['Field'];
            if($vo['Field'] != $pk)
            {
                $adds[]=$vo['Field'];
            }
        }

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