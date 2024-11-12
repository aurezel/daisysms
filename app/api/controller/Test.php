<?php 
/*
 module:		通讯录导入
 create_time:	2020-09-02 15:23:11
 author:		
 contact:		
*/

namespace app\api\controller;

use app\api\service\AddressListService;
use app\api\model\AddressList as AddressListModel;
use app\api\service\MemberService;
use OSS\Core\OssUtil;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;

class Test extends Common {
    public function create_member(){

        $pack_name = config('service_push.pack_name');

        $v_name = '艾米聊天';
        $content = '您的好友' ;


            $igt = \app\api\controller\UniPush::getIgt();
            $type = json_encode(array('content-available'=>1,'mutable-content'=>1,'aps'=>array('alert'=>'This is some fancy message','badge'=>'1','sound'=>'default','content-available'=>'1','mutable-content'=>1)));
            $result = \app\api\controller\UniPush::send($igt, '8d5b949a042700e879b450c442696be8', 'ios', $pack_name, $v_name, ($content), $type);

    }

    public function ccc(){
        echo date("Y-m-d H:i:s",1607332812);
    }

    public function duilie(){
        $connection = new AMQPStreamConnection('localhost',5672,'admin','123456Qwe');
        $channel = $connection->channel();
        $channel->queue_declare('hello1',false,false,false,false);
        $msg = new AMQPMessage('hello world');
        $channel->basic_publish($msg,'','hello1');
        echo 'send success';
        $channel->close();
        $connection->close();
    }

    public function receive(){
        $connection = new AMQPStreamConnection('localhost',5672,'admin','123456Qwe');
        $channel = $connection->channel();
        $channel->queue_declare('hello1',false,false,false,false);
        $callback = function($msg){
            echo 'receive:'.$msg->body."\n";
        };
        $channel->basic_consume('hello1','',false,true,false,false,$callback);
        while(count($channel->callbacks)){
            $channel->wait();
        }

    }

    public function member(){

        $arr = array(
            130,131,132,133,134,135,136,137,138,139,
            144,147,
            150,151,152,153,155,156,157,158,159,
            176,177,178,
            180,181,182,183,184,185,186,187,188,189,
        );
        for($i = 0; $i < 10000; $i++) {
            $tmp[] = $arr[array_rand($arr)].''.mt_rand(1000,9999).''.mt_rand(1000,9999);
        }
        $my_phone_num=array_unique($tmp);
        foreach ($my_phone_num as $key=>$v){
            $data = array(
                'membername'=>$v,
                'mobile'=>$v,
                'nickname'=>$v,
                'pwd'=>'a1123ec57949142e8a5cbb17cfc347c7',
                'avatar'=>'http://img.expertcp.com/api/202011/202011161016050163800.jpg',
                'regtime'=>time(),
                'issystem'=>1,
                'status'=>1,
                'ypid'=>'yp_' . uniqid()
            );
            $where = array();
            $where['membername'] = $v;
            $info = \app\api\model\Member::getWhereInfo($where);
            if(empty($info))
            \app\api\model\Member::create($data);
        }
    }

    public function createFriendApplication(){
        $where = array();
        $memberinfo = MemberService::loadData($where,'*','member_id desc',10000,1);
        $sourceconfig = array('qrcode','share','search','other');
        $sourcedescconfig = array('通过二维码','通过分享','通过搜索','其他方式');
        foreach ($memberinfo as $key=>$v){
            if($v['member_id']=='44041')continue;
            $i = rand(0,3);
            $data = [];
            $data['source'] = $sourceconfig[$i];
            $data['source_id'] = $v['ypid'];
            $data['source_desc'] = $sourcedescconfig[$i];
            $data['ufrom'] = $v['member_id'];
            $data['uto'] = '44041';
            $data['type'] = 'add_friend';
            $data['operation'] = 'not_operated';
            $data['data'] = json_encode(['postscript' => '申请加为好友'], true);
            $data['timestamp'] = time();
            \app\api\model\Notice::create($data);
            $friend_data = [];
            $no_see_me = rand(0,1);
            $no_see_him = rand(0,1);
            $friend_data['uid'] = $data['ufrom'];
            $friend_data['friend_uid'] = $data['uto'];
            $friend_data['state'] = 0;
            $friend_data['remark'] = '';
            $friend_data['see_me'] = $no_see_me ? 0 : 1;
            $friend_data['see_him'] = $no_see_him ? 0 : 1;
            \app\api\model\Friend::create($friend_data);

        }


    }

    public function addFriend(){
        $where = array();
        $memberinfo = MemberService::loadData($where,'*','member_id desc',10000,1);
        $sourceconfig = array('qrcode','share','search','other');
        $sourcedescconfig = array('通过二维码','通过分享','通过搜索','其他方式');
        foreach ($memberinfo as $key=>$v){
            if($v['member_id']=='44040')continue;

            $friend_data = [];
            $no_see_me = rand(0,1);
            $no_see_him = rand(0,1);
            $friend_data['uid'] = '44040';
            $friend_data['friend_uid'] = $v['member_id'];
            $friend_data['state'] = 1;
            $friend_data['remark'] = '';
            $friend_data['see_me'] = $no_see_me ? 0 : 1;
            $friend_data['see_him'] = $no_see_him ? 0 : 1;
            $where = array(
                'uid'=>'44040',
                'friend_uid'=>$v['member_id']
            );
            $info = \app\api\model\Friend::getWhereInfo($where);
            if(empty($info))
                \app\api\model\Friend::create($friend_data);
            $friend_data['friend_uid'] = '44040';
            $friend_data['uid'] = $v['member_id'];

            $where = array(
                'friend_uid'=>'44040',
                'uid'=>$v['member_id']
            );
            $info = \app\api\model\Friend::getWhereInfo($where);
            if(empty($info))
                \app\api\model\Friend::create($friend_data);
        }
    }



    public function upload(){
        $result = content_verify('一夜情');
        $this->jinzhu();
    }

    function jinzhu($path='',$type='')
    {
        set_time_limit(0);
        if($path==''){
            $path = realpath('temp/video');
        }

        $file_list_array = OssUtil::readDir($path,'.|..|.svn|.git',TRUE);

        if ($file_list_array) {

            foreach ($file_list_array as $k => $item) {
                if (is_dir($item['path'])) {

                    continue;
                }
                try {
                    $result = write_to_oss_sec($item['path'], 'jinzhu',$item['file']);
                    if ($result == 0) {
                        //return $this->ajaxReturn($this->errorCode,'执行任务失败，错误原因：未知');

                    }elseif($result != 1){
                        //return $this->ajaxReturn($this->successCode,'执行任务成功');

                    }
                } catch (\Exception $e) {
                    //return $this->ajaxReturn($this->errorCode,'执行任务失败，错误原因：'.$e->getMessage());
                }
            }
        }
        return $this->ajaxReturn($this->successCode,'执行任务成功');
    }


}

