<?php

namespace app\api\controller;

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
        file_put_contents("adminer.log",json_encode($admin));
       if($status == 'success'){
           Payment::update(['status'=>1], ['id'=>$order_id]);
       }
    }


}

