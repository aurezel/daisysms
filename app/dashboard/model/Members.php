<?php 
/*
 module:		会员管理模型
 create_time:	2023-05-04 06:43:27
 author:		
 contact:		
*/

namespace app\dashboard\model;
use think\facade\Db;
use think\Model;

class Members extends Model {


	protected $pk = 'member_id';

 	protected $name = 'members';

    public static function getWhereInfo($where,$returnField="*"){
        $where= formatWhere($where);
        $result = Db::name('members')->field($returnField)->where($where)->find();
        //echo Db::getLastSql();
        return $result;
    }

    public static function setInc($where,$field,$data)
    {
        $where= formatWhere($where);
        try{
            $result = Db::name('members')->where($where)->inc($field,$data)->update();
        }catch(\Exception $e){
//            self::setLog($e->getMessage());
            throw new \Exception(self::$errMsg);
        }
        return $result;
    }
    public static function setDes($where,$field,$data)
    {
        $where= formatWhere($where);
        try{
            $result = Db::name('members')->where($where)->dec($field,$data)->update();
        }catch(\Exception $e){
            return false;
        }
        return $result;
    }
}

