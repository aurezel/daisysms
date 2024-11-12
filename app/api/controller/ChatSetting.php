<?php
/*
 module:		聊天设置
 create_time:	2020-05-13 15:52:54
 author:		
 contact:		
*/

namespace app\api\controller;

use think\facade\Db;
use think\facade\Log;

class ChatSetting extends Common
{


    /**
     * @api {post} /ChatSetting/view 01、查看数据
     *
     */
    function view()
    {


        try {
            $type =  $this->request->post('type');
            $channel =  $this->request->post('channel');
            $channel = empty($channel)?'sysdl':$channel;
            $field = '*';
            $channel = strtolower($channel);
            $where['channel']=$channel;
            $res = \app\api\model\Chatsetting::getWhereInfo($where, $field);
            if(empty($res) && $channel!='sysdl'){
                $where['channel']='sysdl';
                $res = \app\api\model\Chatsetting::getWhereInfo($where, $field);
            }
            if($type=='1101'){
                //android
            }else{
                //ios
                $res['androidlink'] = $res['ioslink'];
                $res['androidversion'] = $res['iosversion'];
                $res['androidmethod'] = $res['iosmethod'];
            }
        } catch (\Exception $e) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => $e->getMessage()]);
        }
        $ts =  getSetConfig('description');
        $res['wxts'] = $ts;
//        Log::info('接口输出：' . print_r($res, true));
        return $this->reDecodejson(['status' => $this->successCode, 'data' => $res]);
    }

    function getNotice(){
        $typearr = array(
            '1'=>'一般',
            '2'=>'紧急',
        );
        $time = time();
        $sql = "SELECT * FROM `cd_webnotice` WHERE status=1 AND starttime<".$time." AND overtime>".$time." limit 1";
        $result = Db::query($sql);
        $res = [];
        if(!empty($result)){
            $info = $result[0];
            $info['levelt'] = $typearr[$info['level']];
            $info['starttime'] = date('Y-m-d H:i:s',$info['starttime']);
            $res = $info;
        }
        if(!empty($res)){
            return $this->reDecodejson(['status' => $this->successCode, 'data' => $res]);
        }
        return $this->reDecodejson(['status' => 201, 'data' => '']);


    }


}

