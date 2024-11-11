<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/25
 * Time: 15:45
 *
 * TODO 跑定时任务接口
 */

namespace app\api\controller;


use app\api\model\Member;
use xhadmin\RedisService;

class Async extends Base
{

    /**
     * 自动删除oss文件脚本
     */
    public function oss_delete_file()
    {
        $oss_delete_object_list = RedisService::_sMember('oss_delete_object'); #排名更新时间列表[时间戳]
        $num = 0;
        $update_num = 0;
        if ($oss_delete_object_list) {
            foreach ($oss_delete_object_list as $key => $value) {
                $num++;
                $a = $this->delete($value);
                if ($a) {
                    $update_num++;
                }
            }
            #取出数据后，清空
            RedisService::_sRem('oss_delete_object');
        }
        echo "执行完成 ，本次执行$num" . "成功$update_num";

    }


}