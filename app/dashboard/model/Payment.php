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

class Payment extends Model {


	protected $pk = 'id';

 	protected $name = 'daisysms_payment';

    public static function getWhereInfo($where,$returnField="*"){
        $where= formatWhere($where);
        $result = Db::name('daisysms_payment')->field($returnField)->where($where)->find();
        //echo Db::getLastSql();
        return $result;
    }
}

