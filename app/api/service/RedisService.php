<?php


namespace app\api\service;


use think\facade\Db;
use xhadmin\db\OfflineMessage;


class RedisService
{
    public static function existsMember($uid){
        $redis = new \Redis();
        $redis->connect("127.0.0.1","63792");
        $lists = $redis->sMembers('member_online');
        if(!empty($lists)){
            foreach ($lists as $key=>$v){

                if($v['member_id']==$uid){
                    return true;
                }
            }
        }
        return false;
    }

    public static function sAdd($info){
        $redis = new \Redis();
        $redis->connect("127.0.0.1","63792");
        $redis->sAdd('member_online',$info);
    }
    public static function get($uid,$type){
        $redis = new \Redis();
        //连接扩展
        $key = $type.'_'.$uid;
        $redis->connect("127.0.0.1","63792");
        $message = $redis->sMembers($key);
        return $message;
    }

    public static function addOnline($token,$data){
        $redis = new \Redis();
        $redis->connect("127.0.0.1","63792");
        $redis->set($token,$data);
    }

    public static function getGroup($gid){
        $redis = new \Redis();
        $redis->connect("127.0.0.1","63792");
        return json_decode($redis->get($gid),TRUE);
    }

    public static function addGroup($gid,$data){
        $redis = new \Redis();
        $redis->connect("127.0.0.1","63792");
        $redis->set($gid,$data);
    }
    public static function removeGroup($gid){
        $redis = new \Redis();
        $redis->connect("127.0.0.1","63792");
        $redis->del($gid);
    }



    public static function removeOnline($token,$data){
        $redis = new \Redis();
        $redis->connect("127.0.0.1","63792");
        $redis->sRemove($token);
    }

    public static function addAll($data){
        Db::name('offline_message')->insertAll($data);
        //OfflineMessage::createData($data);
        /*  $redis = new \Redis();
          //连接扩展
          $redis->connect("127.0.0.1","63792");
          if(is_array($data))
              $data = json_encode($data);
          $redis->sAdd($type.'_'.$uid,$data);*/
    }
    public static function add($uid,$data,$type='friend'){
        $data['uid'] = $uid;
        if(is_array($data['options']))
        $data['options'] = json_encode($data['options']);
        \app\admin\model\Offlinemessage::create($data);
      /*  $redis = new \Redis();
        //连接扩展
        $redis->connect("127.0.0.1","63792");
        if(is_array($data))
            $data = json_encode($data);
        $redis->sAdd($type.'_'.$uid,$data);*/
    }

    public static function push($channel,$data){
        $redis = new \Redis();
        //连接扩展
        $redis->connect("127.0.0.1","63792");
        $data = \GuzzleHttp\json_encode($data);
        $redis->lPush($channel,$data);
    }

    public static function pop($channel){
        $redis = new \Redis();
        //连接扩展
        $redis->connect("127.0.0.1","63792");
        $result = $redis->rPop($channel);
       if($result==false||$result==null)return;
        $result = json_decode($result,TRUE);
        return $result;
    }

    public function remove($uid,$type,$id,$maxid=0){
        $where = array();
        $where['uid'] = $uid;
        $where['id'] = $id;
        \app\api\model\Offlinemessage::delete1($where);
       // \app\admin\model\Offlinemessage::destroy($where);
        if($maxid>0){
            $where = array();
            $where[] = ['uid','=',$uid];
            $where[] = ['autoid','<=',$maxid];
            \app\admin\model\Offlinemessage::update(array('issend'=>1),$where);
        }


    }
}