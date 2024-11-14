<?php

namespace app\api\controller;

use app\api\model\Members;
use app\dashboard\model\Payment;
use think\facade\Validate;
use think\facade\Filesystem;
use think\Image;
use think\exception\ValidateException;
use OSS\OssClient;

class Notify extends Common
{


    public function index()
    {
       $order_id = $this->request->param('order_id', '', 'serach_in');
       $status = $this->request->param('status', '', 'serach_in');
       $admin = [];
       $admin['order_id'] = $order_id;
       $admin['status'] = $status;
       $user_id = db("daisysms_payment")->where(['id'=>$order_id])->value('user_id');
       $admin['user_id'] = $user_id;
       file_put_contents("adminer.log",json_encode($admin), FILE_APPEND);
       if($status == 'success'){
           $where = ['member_id'=>$user_id];
           $members = Members::getWhereInfo($where,'umoney');
           if($members){
               Payment::update(['status'=>1,'balance'=>$members['umoney']], ['id'=>$order_id]);
               Members::setInc($where,'umoney',$members['umoney']);
           }

       }
    }


}

