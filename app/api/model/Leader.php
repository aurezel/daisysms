<?php 
/*
 module:		大神列表模型
 create_time:	2021-08-31 15:47:31
 author:		
 contact:		
*/

namespace app\api\model;
use think\Model;
use think\facade\Db;

class Leader extends Model {


	protected $pk = 'id';

 	protected $name = 'leader';

    public static function getWhereInfo($where,$returnField="*"){
        $where= formatWhere($where);
        $result = Db::name('leader')->field($returnField)->where($where)->find();
        //echo Db::getLastSql();
        return $result;
    }
}

