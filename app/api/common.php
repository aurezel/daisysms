<?php
// +----------------------------------------------------------------------
// | 应用公共文件
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------

use GatewayWorker\Lib\Gateway;

function send2Group($group_id, $message)
{
    Gateway::$registerAddress = '127.0.0.1:1233';
    Gateway::$persistentConnection = false;
    Gateway::sendToGroup($group_id, json_encode($message, true));
}

function send2Uid($uid, $message)
{
    Gateway::$registerAddress = '127.0.0.1:1233';
    Gateway::$persistentConnection = false;
    Gateway::sendToUid($uid, json_encode($message, true));
}

function join2Group($group_id, $uid)
{
    Gateway::$registerAddress = '127.0.0.1:1233';
    Gateway::$persistentConnection = false;

    $clientlists = Gateway::getClientIdByUid($uid);
    $list = [];
    foreach ($clientlists as $key => $v) {
        $list[] = $v;
        Gateway::joinGroup($v, $group_id);
    }
    return $list;
}

function is_not_json($str){
    return is_null(json_decode($str));
}

error_reporting(0);
function sendMsg($data)
{
    Gateway::$registerAddress = '127.0.0.1:1233';
    Gateway::$persistentConnection = false;
    Gateway::sendToAll(json_encode($data));
}

function sendUid($uid, $data,$type=1)
{
    Gateway::$registerAddress = '127.0.0.1:1233';
    Gateway::$persistentConnection = false;

    if (Gateway::isUidOnline($uid)) {

        $result = Gateway::sendToUid($uid, json_encode($data));
        if ($data['data']['chat_type'] != 'friend') return;

    } else {
        if($type==0)return;
        if ($data['data']['chat_type'] != 'friend') return;
        $pack_name = config('service_push.pack_name');
        $v = \app\api\model\Member::find($uid);//::getInfo($uid,'cid,platform');
        $v_name = 'Amy Chat';
        $data['data']['from_name'] = urldecode( $data['data']['from_name']);
        if($data['data']['type'] =='Video'){
            $msg =  '给您发来一条视频消息';
        }else if($data['data']['type'] =='audio'){
            $msg =  '给您发来一条语音消息';
        }else if($data['data']['type'] =='image'){
            $msg =  '给您发来一张图片消息';
        }else{
            $msg =  '给您发来一条文字消息';
        }
        $content = '您的好友' . $data['data']['from_name'] .$msg;

        if (!empty($v['cid'] && !empty($v['platform']))) {
            $igt = \app\api\controller\UniPush::getIgt();
            $type = json_encode(array('type' => 'message', 'data' => $data['data']));
            $result = \app\api\controller\UniPush::send($igt, $v['cid'], $v['platform'], $pack_name, $v_name, ($content), $type);
        }

    }

}