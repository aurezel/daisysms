<?php 
/*
 module:		渠道管理模型
 create_time:	2023-05-04 07:57:16
 author:		
 contact:		
*/

namespace app\api\model;
use think\facade\Db;
use think\Model;

class Agent extends Model {


	protected $pk = 'id';

 	protected $name = 'agent';
 
     public static function setInc($where,$field,$data)
    {
        $where= formatWhere($where);
        try{
            $result = Db::name('agent')->where($where)->inc($field,$data)->update();
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
            $result = Db::name('agent')->where($where)->dec($field,$data)->update();
        }catch(\Exception $e){
            return false;
        }
        return $result;
    }
}

