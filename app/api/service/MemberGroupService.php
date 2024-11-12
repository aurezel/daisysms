<?php
/*
 module:		朋友圈点赞
 create_time:	2020-10-29 11:32:31
 author:		
 contact:		
*/

namespace app\api\service;

use think\facade\Db;
use xhadmin\CommonService;

class MemberGroupService extends CommonService
{


    public static function loadData($where, $field, $orderby, $limit, $page)
    {
        $page = $page - 1;
        $page = $page * $limit;
        // $result = Db::name('groups')->field($field)->alias('a')->join('group_member'.' b','a.'.'gid'.'=b.'.'gid',"LEFT")
        //     ->where($where)->limit($page, $limit)->order($orderby)->fetchSql(true)->select();
        //     var_dump($result);
        //     die;
        $result = Db::name('groups')->field($field)->alias('a')->join('group_member'.' b','a.'.'gid'.'=b.'.'gid',"LEFT")
            ->where($where)->limit($page, $limit)->order($orderby)->select()->toArray();
        return $result;
    }

}

