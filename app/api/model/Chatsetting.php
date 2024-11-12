<?php 
/*
 module:		聊天设置模型
 create_time:	2020-08-19 22:37:03
 author:		
 contact:		
*/

namespace app\api\model;
use think\facade\Db;
use think\Model;

class Chatsetting extends Model {



	protected $pk = 'id';

 	protected $name = 'chat_setting';

    /**
     * 获取信息
     * @param array $where 条件
     * @return array 信息
     */
    public static function getWhereInfo($where,$field='',$orderby='')
    {
        try{
            empty($orderby) && $orderby = 'id desc';
            empty($field) && $field = '*';
            $result = Db::name('chat_setting')->field($field)->where($where)->order($orderby)->find();
        }catch(\Exception $e){
            self::setLog($e->getMessage());
        }

        return $result;
    }
}

