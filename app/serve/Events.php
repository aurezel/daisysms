<?php

namespace app\serve;

use app\api\controller\Common;
use app\api\controller\Jwt;
use \GatewayWorker\Lib\Gateway;


use think\facade\Db;
use think\facade\Cache;
use think\facade\Env;

class Events
{
    public static function getUid($token)
    {

        if (!empty($token)) {
            $token = trim($token);
            if (count(explode('.', $token)) == 3) {
                $jwt = Jwt::getInstance();
                $jwt->setIss(config('my.jwt_iss'))->setAud(config('my.jwt_aud'))->setSecrect(config('my.jwt_secrect'))->setToken($token);
                if ($jwt->decode()->getClaim('exp') > time()) {

                    if ($jwt->validate() && $jwt->verify()) {
                        $uid = $jwt->decode()->getClaim('uid');
                        return $uid;
                    }
                }
            }
        }
        return 0;
    }

    /**
     * @param $client_id
     * @param $data
     * 当客户端连接上gateway完成websocket握手时触发的回调函数。
     *
     * 注意：此回调只有gateway为websocket协议并且gateway没有设置onWebSocketConnect时才有效。
     */
    public static function onWebSocketConnect($client_id, $data)
    {

        if (empty($data['get']['token'])) {
            Gateway::closeClient($client_id, self::_echo('ami:error', '您好，参数传入不正确'));
            return;
        }
        $token = $data['get']['token'];//获取用户token

        $uid = Events::getUid($token);

        if (empty($uid)) {
            Gateway::closeClient($client_id, self::_echo('ami:error', '您好，您的token不正确，或者已经过期'));
            return;
        }
        $cliendList = Gateway::getClientIdByUid($uid);
        echo '列表：';
        print_r($cliendList);
        if(!empty($cliendList)){
            foreach ($cliendList as $key=>$v){
                Gateway::closeClient($v,self::_echo('ami:otherlogin', '您好，您的账户在其他设备上登入您被迫下线'));
            }
        }

        Gateway::bindUid($client_id, $uid);

        Gateway::sendToClient($client_id, self::_echo('ami:success', '恭喜您，成功建立链接'));
        echo $client_id . '登入\n';
    }

    public static function success($data = [], $event = 'match_status:success', $msg = '成功')
    {
        return json_encode(array('event' => $event, 'msg' => $msg, 'data' => $data));
    }

    protected static function _echo($event = 'ami:success', $msg = '', $data = array())
    {
        return json_encode(array('event' => $event, 'msg' => $msg, 'data' => $data));
    }

    /**
     * 当客户端连接上gateway进程时(TCP三次握手完毕时)触发的回调函数。
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        // 向当前client_id发送数据
        //Gateway::sendToClient($client_id,self::success('success','恭喜您聊天链接已经建立'));
        // 向所有人发送
        //Gateway::sendToAll("$client_id login");
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param string $message 具体消息
     */
    public static function onMessage($connection, $data)
    {
        /*
         *Gateway::sendToAll("$client_id said $message");
         * 向所有客户端或者client_id_array指定的客户端发送$send_data数据。如果指定的$client_id_array中的client_id不存在则自动丢弃
         * void Gateway::sendToClient(string $client_id, string $send_data);
         * void Gateway::closeClient(string $client_id);
         * int Gateway::isOnline(string $client_id);//是否在线
         * 默认uid与client_id是一对多的关系，如果当前uid下绑定了多个client_id，则多个client_id对应的客户端都会收到消息
         */
        echo '接受数据'.$data;
        $data = json_decode($data, true);

        if (!$data) {
            return;
        }
        if (isset($data['event'])) {
            $event = $data['event'];
            switch ($event) {
                case 'ami:ping':
                    Gateway::sendToClient($connection, '{"event":"ami:pong","data":"{}"}');
                    return;
            }
        }

        // 向所有人发送
        //Gateway::sendToAll("$client_id said $message");
    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id)
    {
        echo $client_id . '关闭\n';
        // 向所有人发送
        //GateWay::sendToAll("$client_id logout");
        //Gateway::unbindUid($client_id);
    }
}