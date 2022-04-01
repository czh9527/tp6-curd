<?php
/**
 * Author: <user>
 * Date: <date>
 * Time: <time>
 */
 
namespace app<namespace>model;

//<tableIntroduce>
use app\common\Output;
use think\model;
use Exception;
use think\facade\Db;
use hg\apidoc\annotation\Field;
use hg\apidoc\annotation\WithoutField;
use hg\apidoc\annotation\AddField;
use hg\apidoc\annotation\Param;
class <model> extends Model
{
	use Output;
	protected $pk = '<pk>';
    /**
    * apidoc注释获取
    */
    public function getReturn(){ }
    //@Field("<field>")//如果只需要部分注释，请用这个单独写

    /**
    * Notes: 新增数据前
    * Author: <user>
    */
    public static function onBeforeInsert($data)// TODO 是否需要下面数据
    {
        $data['compy_id']=getCurrentUserInfo('compy_id')??null;
        $data['create_user']=getCurrentUserInfo('phone')??null;
    }
    <frelationModel><relationModel><getAllListByPid>
    /**
    * Notes: 获取分页列表
	* Author: <user>
    * @param $where
    * @param $page_size
    * @param $is_all
    * @return \think\Response
    */
    public function get<model>List($where, $page_size,$is_all)
    {
        try {
                if($is_all)//是否全部输出
                {
                    $res = $this->where($where)->order('<pk>', 'asc')->select();
                }
                else
                {
                    $res = $this->where($where)->order('<pk>', 'asc')->paginate($page_size);
                }
        } catch(Exception $e) {
            return self::Error($e->getError(),"请求数据失败~",400);
        }
        if($res->isEmpty())
        {
            return self::Success($res,"请求数据为空~",204);
        }
        else
        {
            return self::Success($res,"请求数据成功~",200);
        }
    }
	
    /**
    * Notes: 根据<pk>获取信息-自动转换为数组
	* Author: <user>
    * @param $<pk>
    * @return \think\Response
    */
    public function get<model>By<pk>($<pk>)
    {
        try {
            $<pk>s=is_array($<pk>)?$<pk>:Array($<pk>);
            $res = $this->select($<pk>s);
        } catch(Exception $e) {

            return self::Error($e->getError(),"查询数据失败~",400);
        }

        if($res->isEmpty())
        {
            return self::Success($res,"查询数据为空~",204);
        }
        else
        {
            return self::Success($res,"查询数据成功~",200);
        }
    }

    /**
    * Notes: 添加信息
	* Author: <user>
    * @param $param
    * @return \think\Response
    */
    public function add<model>($param,$allowField)
    {
        Db::startTrans();
        try {
            $res= $this->allowField($allowField)->save($param);
            Db::commit();
        } catch(Exception $e) {
            Db::rollback();
            return self::Error($e->getError(),"新增失败~",400);
        }
        return self::Success($this-><pk>,"新增成功~",200);
    }

    /**
    * Notes: 编辑信息
	* Author: <user>
    * @param $param
    * @return \think\Response
    */
    public function edit<model>($param,$allowField)
    {
        Db::startTrans();
        try {
            $data=$this->where('<pk>', $param['<pk>'])->find();
            if($data)
            {
                $res=$data->allowField($allowField)->update($param);
            }
            else
            {
                return self::Error([],"不存在该数据~",204);
            }
            Db::commit();
        } catch(Exception $e) {
            Db::rollback();
            return self::Error($e->getError(),"编辑失败~",400);
        }
        return self::Success($res,"编辑成功~",200);
    }

    /**
    * Notes: 删除信息-自动转换为数组
	* Author: <user>
    * @param $<pk>
    * @return \think\Response
    */
    public function del<model>By<pk>($<pk>)
    {
        Db::startTrans();
        $<pk>s=is_array($<pk>)?$<pk>:Array($<pk>);
        try { <relationDelModel>
            // TODO 不可删除校验
            $res=$this->where('<pk>','in',$<pk>s)->delete();
            Db::commit();
        } catch(Exception $e) {
            Db::rollback();
            return self::Error($e->getError(),"删除失败~",400);
        }
        if($res)
        {
            return self::Success($res,"删除成功~",200);

        }
        else
        {
            return self::Success([],"该数据已被删除~",204);
        }


    }

}

