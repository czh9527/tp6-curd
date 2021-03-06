<?php
/**
 * Created by PhpStorm.
 * Date: 2021/7/8
 * Time: 10:49 PM
 */
namespace czh9527\tp6curd\template\impl;

use czh9527\tp6curd\extend\Utils;
use czh9527\tp6curd\template\IAutoMake;
use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\VarExporter\VarExporter;
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
            return true;
        }
        return false;
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
        \$request_data=\$request->param();
        return  \$this->model->del<model>By<pk>(\$request_data['<pk>']);
    }";
        $deleteHavePid="    
    public function del(Request \$request)
    {
        \$request_data=\$request->param();

        \$this->model->childList=[];//初始化id列表
        \$this->model->childList[]=\$request_data['<pk>'];//放入自己

        \$this->model->getAllChildByPid(\$request_data['<pk>']);//获取所有孩子
        return \$this->model->del<model>Byid(\$this->model->childList);

   }";
        $getChils='    
   /**
     * @Apidoc\Author("<user>")
     * @Apidoc\Title("获取自己+所有子")
     * @Apidoc\Desc("根据id递归获取所有子（包括孩子的孩子）")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("开发中")
     * @Apidoc\Param("id", type="int",require=true,default="1", desc="当前节点id")
     * @Apidoc\Returned(ref="app\admin\model\<model>\getReturn")
     */
    public function getchilds(Request $request)
    {
        $request_data=$request->param();
        $this->model->childList=[];
        $this->model->childList[]=$request_data[\'id\'];//加入自己
        //递归所有子，再输出
        $this->model->getAllChildByPid($request_data[\'id\']);
        return $this->model->get<model>Byid($this->model->childList);
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
        //替换allowField允许输入的字段
        $zd=[];
        foreach ($column as $vo) {
            if($vo['Key']!='PRI')
            {
                $zd[]=$vo['Field'];
            }

        }
        //组合数组
        $allowField="[";
        foreach ($zd as $key => $value)
        {
            ($key+1)%5==0? $allowField.="'".$value."'".','."\r\n"."\t\t\t\t\t" :$allowField.="'".$value."'".',';         
            
        }
        $allowField.="]";

        $tplContent = str_replace('<allowField>', $allowField, $tplContent);
        //apidoc

        $zdvalue=[];
        $zdtype=[];
        $zddefault=[];
        $zdms=[];
        foreach ($column as $vo) {
            if($vo['Key']!='PRI' && $vo['Field']!='create_time'&& $vo['Field']!='update_time' && $vo['Field']!='create_user' && $vo['Field']!='compy_id' )
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

        //替换apidoc
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
        $paramData="";
        $excel_head_data='';
        $excel_key_data='';
        for($i=0;$i<count($zdvalue);$i++)
        {
            //处理
            $param='
                $param[\''.$zdvalue[$i].'\']=$excel_array[$i]['.$i.'];';
            $paramData=$paramData.$param;
        }
        $excel_head_data=$this->createStr($zdms);
        $excel_key_data=$this->createStr($zdvalue);
        
        $tplContent = str_replace('<param>', $paramData, $tplContent);
        $tplContent = str_replace('<excel_head>', $excel_head_data, $tplContent);
        $tplContent = str_replace('<excel_key>', $excel_key_data, $tplContent);

        $file =App::getAppPath() . $filePath . DS . 'controller' . DS . $controller . '.php';
        return $this->makeFile($file, $tplContent);
        
    }
    public function makeFile($file,$tplContent)
    {
        $output = new Output();
        return file_put_contents($file, $tplContent)
            ?$output->info("\033[32m".$file."创建成功"."\033[0m")
            :$output->info($file."创建失败");
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
    public function createStr($zd)
    {
        //组合数组字符串
        $str="[";
        foreach ($zd as $key => $value)
        {
            ($key+1)%5==0? $str.="'".$value."'".','."\r\n"."\t\t\t\t" :$str.="'".$value."'".',';

        }
        $str.="]";
        return $str;
    }
}