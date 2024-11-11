<?php 
/*
 module:		会员管理模型
 create_time:	2020-08-09 15:44:50
 author:
 contact:
*/

namespace app\api\model;
use think\facade\Db;
use think\Model;

class Member extends Model {


	protected $pk = 'member_id';

 	protected $name = 'member';
    public static function getWhereInfo($where,$returnField="*"){
        $where= formatWhere($where);
        $result = Db::name('member')->field($returnField)->where($where)->find();
        //echo Db::getLastSql();
        return $result;
    }

    public static function setInc($where,$field,$data)
    {
        $where= formatWhere($where);
        try{
            $result = Db::name('member')->where($where)->inc($field,$data)->update();
        }catch(\Exception $e){
            self::setLog($e->getMessage());
            throw new \Exception(self::$errMsg);
        }
        return $result;
    }
    public static function setDes($where,$field,$data)
    {
        $where= formatWhere($where);
        try{
            $result = Db::name('member')->where($where)->dec($field,$data)->update();
        }catch(\Exception $e){
            return false;
        }
        return $result;
    }

}

