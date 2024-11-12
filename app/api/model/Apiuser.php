<?php 
/*
 module:		接口用户列表模型
 create_time:	2021-08-03 16:39:05
 author:		
 contact:		
*/

namespace app\api\model;
use think\facade\Db;
use think\Model;

class Apiuser extends Model {


	protected $pk = 'id';

 	protected $name = 'apiuser';

    public static function getWhereInfo($where,$returnField="*"){
        $where= formatWhere($where);
        $result = Db::name('apiuser')->field($returnField)->where($where)->find();
        //echo Db::getLastSql();
        return $result;
    }
 

}

