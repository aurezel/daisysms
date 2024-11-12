<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/10/31
 * Time: 11:27
 */

namespace app\api\controller;

use app\admin\model\Client;
use app\admin\model\Visiter;
use app\Common;
use think\Db;
use think\Model;
use IGtTransmissionTemplate;
use IGtAPNPayload;
use IGtSingleMessage;
use IGtTarget;
use IGeTui;
use IGtNotify;
use NotifyInfo_type;
use DictionaryAlertMsg;
use app\admin\model\Chats;

class UniPush
{
    public function test(){

        $str=  file_get_contents('http://open.sportnanoapi.com/api/sports/stream/list?user=yqb&secret=e46ed0a0aa2a8a0246fb');
       $str = \GuzzleHttp\json_decode($str,TRUE);
       print_r($str);
        echo 'ccc';
    }
    public function push(){

        $igt=self::getIgt();

        $pack_name = config('service_push.pack_name');
        $where = array();

        $list = \xhadmin\db\Member::loadList($where);
        $arr = array();
        foreach ($list as $key=>$v){
            $clients = array();
            $v_name = '佳蜜';
            $content = '您有一条新的消息,请注意查收';
            if(!empty($v['cid']&&!empty($v['platform']))){
                $arr[$v['cid']] = $v['platform'];
            }
        }

        foreach ($arr as $key=>$v){
            $result = self::send($igt,  $key, $v, $pack_name, $v_name, $content);
        }


    }

    public function testid(){
        $uid = 158;
        $v = \xhadmin\db\Member::getInfo($uid,'cid,platform');
        $v_name = '佳蜜';
        $content = '您有一条新的消息,请注意查收';
        print_r($v);
        if(!empty($v['cid']&&!empty($v['platform']))){
            $igt=\app\api\controller\UniPush::getIgt();
            $pack_name = config('service_push.pack_name');
            echo $v['cid'];
            echo $v['platform'];
            $result = self::send($igt,  $v['cid'], $v['platform'], $pack_name, $v_name, $content);
            return ($result);
            echo 'cccc';
        }
        echo 'cccc';
    }
    /**
     * 根据 enable_push,state 判断是否推送
     * @param $sid
     * @param $visiter_id
     * @param $content
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function unread_msg_push($sid,$visiter_id,$content)
    {
       /* $clients = Client::alias('c')
            ->join('wolive_service s','s.service_id=c.service_id')
            ->where('c.update_at', '>', date('Y-m-d H:i:s', time() - 864000))//10天内有登录的app
            ->where('c.service_id', $sid)
            ->where("s.state='offline' or (s.state='online' and c.enable_push=1) ")
            ->field('c.cid,c.platform,c.service_id')
            ->select();

        $v_name='游客'.substr($visiter_id,0,8);*/

        $igt=self::getIgt();
        $pack_name = config('app.service_push.pack_name');
        $clients = array();
        $v_name = 'test';
        $content = '测试下';
        foreach ($clients as $cli){

            self::send($igt, $cli['cid'], $cli['platform'], $pack_name, $v_name, $content);
        }
    }


    /**
     * 游客XXX请求帮忙
     * @param $business_id
     * @param $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function someone_need_help($business_id,$data)
    {
        $clients = Client::alias('c')
            ->join('wolive_service s','s.service_id=c.service_id')
            ->where('c.update_at', '>', date('Y-m-d H:i:s', time() - 864000))//10天内有登录的app
            ->where('c.enable_push', 1)
            ->where('s.groupid', $data['groupid'])
            ->where('s.business_id', $business_id)
            ->field('c.cid,c.platform,c.service_id')
            ->select();

        $igt=self::getIgt();
        $pack_name = config('app.service_push.pack_name');
        foreach ($clients as $cli){
            self::send($igt, $cli['cid'], $cli['platform'], $pack_name, '通知', $data['msg']);
        }
    }

    public static function get_ke_fu_Igt()
    {

        $host = config('app.ke_fu_service_push.host');

        $app_key = config('app.ke_fu_service_push.app_key');
        $master_secret = config('app.ke_fu_service_push.master_secret');

        $igt = new IGeTui($host, $app_key, $master_secret);
        return $igt;
    }


    public static function getIgt()
    {

        $host = config('app.service_push.host');

        $app_key = config('app.service_push.app_key');
        $master_secret = config('app.service_push.master_secret');

        $igt = new IGeTui($host, $app_key, $master_secret);
        return $igt;
    }

    public static function send($igt, $cid, $platform, $package, $title, $content, $payload = '')
    {
        // 生成指定格式的intent支持厂商推送通道
        $intent = "intent:#Intent;action=android.intent.action.oppopush;launchFlags=0x14000000;component={$package}/io.dcloud.PandoraEntry;S.UP-OL-SU=true;S.title={$title};S.content={$content};S.payload={$payload};end";
        self::pushMessageToSingle($igt, $cid, self::createPushMessage($payload, $intent, $title, $content, $platform));
    }

    // 创建支持厂商通道的透传消息
    public static function createPushMessage($p, $i, $t, $c, $platform)
    {
        $app_id = config('app.service_push.app_id');
        $app_key = config('app.service_push.app_key');

        $template = new IGtTransmissionTemplate();
        $template->set_appId($app_id);//应用appid
        $template->set_appkey($app_key);//应用appkey
        $template->set_transmissionType(2);//透传消息类型:1为激活客户端启动

        //为了保证应用切换到后台时接收到个推在线推送消息，转换为{title:'',content:'',payload:''}格式数据，UniPush将在系统通知栏显示
        //如果开发者不希望由UniPush处理，则不需要转换为上述格式数据（将触发receive事件，由应用业务逻辑处理）
        //注意：iOS在线时转换为此格式也触发receive事件
        $payload = array('title' => $t, 'content' => $c);
        $pj = json_decode($p, TRUE);
        $payload['payload'] = is_array($pj) ? $pj : $p;

        $template->set_transmissionContent(json_encode($payload));//透传内容
        if ($platform == 1) {
            //兼容使用厂商通道传输
            $notify = new IGtNotify();
            $notify->set_title($t);
            $notify->set_content($c);
            $notify->set_intent($i);
            $notify->set_type(NotifyInfo_type::_intent);
            $template->set3rdNotifyInfo($notify);
        } else {
            //iOS平台设置APN信息，如果应用离线（不在前台运行）则通过APNS下发推送消息
            $apn = new IGtAPNPayload();
            $apn->alertMsg = new DictionaryAlertMsg();
            $apn->alertMsg->body = $c;
            $apn->add_customMsg('payload', is_array($pj) ? json_encode($pj) : $p);//payload兼容json格式字符串
            $template->set_apnInfo($apn);
        }

        return $template;
    }

    /**
     * 单推接口示例
     * @param $template : 推送的消息模板
     * @param $cid : 推送的客户端标识
     */
    public static function pushMessageToSingle($igt, $cid, $template)
    {
        //个推信息体
        $message = new IGtSingleMessage();
        $message->set_isOffline(true);//是否离线
        $message->set_offlineExpireTime(3600 * 12 * 1000);//离线时间
        $message->set_data($template);//设置推送消息类型
        //	$message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        //接收方
        $target = new IGtTarget();
        $target->set_appId(config('app.service_push.app_id'));
        $target->set_clientId($cid);
        //    $target->set_alias(Alias);

        try {
            $rep = $igt->pushMessageToSingle($message, $target);
            // var_dump($rep);
        } catch (RequestException $e) {
            $requstId = e . getRequestId();
            $rep = $igt->pushMessageToSingle($message, $target, $requstId);
            //var_dump($rep);
        }
        //return $rep;
    }

}