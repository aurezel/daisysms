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

class DaisySmsHistory extends Model {


	protected $pk = 'id';

 	protected $name = 'daisysms_history';
    public static function getWhereInfo($where,$returnField="*"){
        $where= formatWhere($where);
        $result = Db::name('daisysms_history')->field($returnField)->where($where)->find();
        //echo Db::getLastSql();
        return $result;
    }


}



