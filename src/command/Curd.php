<?php
/**
 * Created by PhpStorm.
 * Date: 2021/7/8
 * Time: 8:23 PM
 */

namespace czh9527\tp6curd\command;

use czh9527\tp6curd\extend\Utils;
use czh9527\tp6curd\strategy\AutoMakeStrategy;
use czh9527\tp6curd\template\impl\ControllerAutoMake;
use czh9527\tp6curd\template\impl\ExcelAutoMake;
use czh9527\tp6curd\template\impl\ModelAutoMake;
use czh9527\tp6curd\template\impl\ValidateAutoMake;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\facade\App;
use think\facade\Db;

class Curd extends Command
{
    protected function configure()
    {
        $this->setName('auto curd')
            ->addOption('table', 't', Option::VALUE_OPTIONAL, '表名', null)
            ->addOption('path', 'p', Option::VALUE_OPTIONAL, '路径', null)
            ->addOption('delete', 'd', Option::VALUE_OPTIONAL, '删除curd文件', null)
            ->addOption('all', 'a', Option::VALUE_OPTIONAL, '库中所有表curd', null)
            ->setDescription('自动创建curd代码');
    }

    protected function execute(Input $input, Output $output)
    {
        $is_all=$input->getOption('all');
        if($is_all==1)
        {
            //获取路径
            $path = $input->getOption('path');
            if (!$path) {
                $output->error("请输入 -p 路径名");
                exit;
            }
            
            $database=config('database.connections.mysql.database');
//            $column=Db::query('select TABLE_NAME from INFORMATION_SCHEMA.KEY_COLUMN_USAGE t where t.TABLE_SCHEMA ='.'\''.$database.'\'');//查找所有表
            $column=Db::query('show tables from '.$database);
            foreach ($column as $vo) {
                if($vo['Tables_in_'.$database]!='')//查询值不为空才输出
                {
                    $this->makeFile($input,$output,$vo['Tables_in_'.$database],$path);
                }
            }
        }
        else
        {
            //获取表名
            $table = $input->getOption('table');
            if (!$table) {
                $output->error("请输入 -t 表名");
                exit;
            }
            //获取路径
            $path = $input->getOption('path');
            if (!$path) {
                $output->error("请输入 -p 路径名");
                exit;
            }
            $this->makeFile($input,$output,$table,$path);
        }

        
    }
    public function makeFile(Input $input, Output $output,$table,$path)
    {
        !defined('DS') && define('DS', DIRECTORY_SEPARATOR);
        //通过表名得到控制器名
        $controller = ucfirst(Utils::camelize($table));
        
        $conrollerFile=App::getAppPath() . $path . DS . 'controller' . DS . $controller . '.php';
        $modelFile=App::getAppPath() . $path . DS . 'model' . DS . $controller . '.php';
        $validateFile=App::getAppPath() .  $path . DS . 'validate' . DS . $controller . '.php';
        
        //删除文件
        $delete = $input->getOption('delete');
        if ($delete) {
            $output->info("文件列表:");
            $readyFiles = [$conrollerFile, $modelFile, $validateFile];
            foreach ($readyFiles as $k => $v) {
                $output->warning($v);
            }
            $output->info("你确定要删除这些文件?  输入 'y' 继续: ");
            $line = fgets(defined('STDIN') ? STDIN : fopen('php://stdin', 'r'));
            if (trim($line) != 'y') {
                $output->info("删除被取消~");
                exit;
            }
            foreach ($readyFiles as $k => $v) {
                if (file_exists($v)) {
                    unlink($v);
                    $output->info("\033[32m".$v."被删除~"."\033[0m");
                }
            }

            exit;
        }

        //检查依赖表
        $relations=[];

        $prefix = config('database.connections.mysql.prefix');
        $database=config('database.connections.mysql.database');

        $sql='select * from INFORMATION_SCHEMA.KEY_COLUMN_USAGE t where t.TABLE_SCHEMA ='.'\''.$database.'\''.
            'and TABLE_NAME='.'\''. $prefix.$table.'\'';//查找此表有无依赖表


        $column = Db::query($sql);
        $relations = [];

        foreach ($column as $vo) {
            if($vo['REFERENCED_TABLE_NAME']!='')//查询值不为空才输出
            {
                $relations[]= ucfirst(Utils::camelize($vo['REFERENCED_TABLE_NAME']));
                $relations[]=$vo['REFERENCED_COLUMN_NAME'];
                $relations[]= ucfirst(Utils::camelize($vo['TABLE_NAME']));
                $relations[]=$vo['COLUMN_NAME'];
            }
        }


        //检查外键表

        $prefix = config('database.connections.mysql.prefix');
        $database=config('database.connections.mysql.database');

        $sql='select * from INFORMATION_SCHEMA.KEY_COLUMN_USAGE t where t.TABLE_SCHEMA ='.'\''.$database.'\''.
            ' and REFERENCED_TABLE_NAME='.'\''.$prefix.$table.'\'';


        $column = Db::query($sql);

        foreach ($column as $vo) {
            if($vo['REFERENCED_TABLE_NAME']!='')//查询值不为空才输出
            {
                $relations[] = ucfirst(Utils::camelize($vo['REFERENCED_TABLE_NAME']));
                $relations[] = $vo['REFERENCED_COLUMN_NAME'];
                $relations[] = ucfirst(Utils::camelize($vo['TABLE_NAME']));
                $relations[] = $vo['COLUMN_NAME'];
            }
        }

        $context = new AutoMakeStrategy();

        // 执行生成controller策略
        $context->Context(new ControllerAutoMake());
        $context->executeStrategy($controller, $path, $table);

        // 执行生成model策略
        $context->Context(new ModelAutoMake());
        $context->executeStrategy($table, $path, $relations);


        // 执行生成validate策略
        $context->Context(new ValidateAutoMake());
        $context->executeStrategy($table, $path, $relations);
    }
}