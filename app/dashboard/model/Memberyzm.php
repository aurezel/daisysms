<?php 
/*
 module:		member_yzm模型
 create_time:	2020-11-13 15:41:22
 author:		
 contact:		
*/

namespace app\admin\model;
use think\facade\Db;
use think\Model;

class Memberyzm extends Model {


	protected $pk = 'id';

 	protected $name = 'member_yzm';

    public static function getWhereInfo($where,$returnField="*"){
        $where= formatWhere($where);
        $result = Db::name('member_yzm')->field($returnField)->where($where)->find();
        //echo Db::getLastSql();
        return $result;
    }

}

