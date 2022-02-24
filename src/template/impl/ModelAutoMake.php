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
        $relationContent = file_get_contents(dirname(dirname(__DIR__)) . '/tpl/relation.tpl');

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

        $tplContent = str_replace('<namespace>', $namespace, $tplContent);
        $tplContent = str_replace('<model>', $model, $tplContent);
        $tplContent = str_replace('<pk>', $pk, $tplContent);

        if(count($relations)!=0)
        {
            $relationContent = str_replace('<table>', $relations[0], $relationContent);
            $relationContent = str_replace('<tableName>', $relations[1], $relationContent);
            $relationContent = str_replace('<foreignTable>', $relations[2], $relationContent);
            $relationContent = str_replace('<foreignName>', $relations[3], $relationContent);
            $tplContent = str_replace('<relations>', $relationContent, $tplContent);
        }else
        {
            $tplContent = str_replace('<relations>', '', $tplContent);
        }



     
        $file =App::getAppPath() . $filePath . DS . 'model' . DS . $model . '.php';
        return file_put_contents($file, $tplContent);
    }
}