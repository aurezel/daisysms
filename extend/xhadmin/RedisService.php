<?php


namespace xhadmin;

use think\facade\Env;

class RedisService
{

    public static function existsMember($uid)
    {
        $redis = new \Redis();
        $redis->connect(Env::get('database.redis_host', '127.0.0.1'), "63792");
        $lists = $redis->sMembers('member_online');
        if (!empty($lists)) {
            foreach ($lists as $key => $v) {

                if ($v['member_id'] == $uid) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function getGroup($gid)
    {
        $redis = new \Redis();
        $redis->connect(Env::get('database.redis_host', '127.0.0.1'), "63792");
        $redis->auth("123456Qwe");
        return json_decode($redis->get($gid), TRUE);
    }

    public static function addGroup($gid, $data)
    {
        $redis = new \Redis();
        $redis->connect(Env::get('database.redis_host', '127.0.0.1'), "63792");
        $redis->auth("123456Qwe");
        $redis->set($gid, $data);
    }

    public static function removeGroup($gid)
    {
        $redis = new \Redis();
        $redis->connect(Env::get('database.redis_host', '127.0.0.1'), "63792");
        $redis->auth("123456Qwe");
        $redis->del($gid);
    }

    public static function _get($key)
    {
        ini_set('default_socket_timeout', -1);
        $redis = new \Redis();
        $redis->connect(Env::get('database.redis_host', '127.0.0.1'), "63792");
        $redis->auth("123456Qwe");
        $message = $redis->get($key);
        return $message;
    }

    /**
     * 删除key值
     * @param $key
     */
    public static function _del($key)
    {
        $redis = new \Redis();
        $redis->connect(Env::get('database.redis_host', '127.0.0.1'), "63792");
        $redis->auth("123456Qwe");
        $redis->del($key);
    }

    public static function _set($key, $value, $timeout = 0)
    {
        ini_set('default_socket_timeout', -1);
        $redis = new \Redis();
        $redis->connect(Env::get('database.redis_host', '127.0.0.1'), "63792");
        $redis->auth("123456Qwe");
        if ($timeout) {
            $message = $redis->set($key, $value, $timeout);
        } else {
            $message = $redis->set($key, $value);
        }
        return $message;
    }

    /**
     * 队列插入 先进先出（队列）
     * @param $key
     * @param $value
     * @return int
     */
    public static function _rPush($key, $value)
    {
        ini_set('default_socket_timeout', -1);
        $redis = new \Redis();
        $redis->connect(Env::get('database.redis_host', '127.0.0.1'), "63792");
        $redis->auth("123456Qwe");
        $message = $redis->rPush($key, $value);
        return $message;
    }

    /**
     * 队列插入 先进后出（栈）
     * @param $key
     * @param $value
     * @return int
     */
    public static function _lPush($key, $value)
    {
        ini_set('default_socket_timeout', -1);
        $redis = new \Redis();
        $redis->connect(Env::get('database.redis_host', '127.0.0.1'), "63792");
        $redis->auth("123456Qwe");
        $message = $redis->lPush($key, $value);
        return $message;
    }

    /**
     * 获取队列长度
     * @param $key
     * @param $value
     * @return int
     */
    public static function _lLen($key)
    {
        ini_set('default_socket_timeout', -1);
        $redis = new \Redis();
        $redis->connect(Env::get('database.redis_host', '127.0.0.1'), "63792");
        $redis->auth("123456Qwe");
        $message = $redis->lLen($key);
        return $message;
    }

    /**
     * 取出队列(默认倒序取出全部)
     * @param $key
     * @param int $start
     * @param int $stop
     * @return array
     */
    public static function _lRange($key, $start = 0, $stop = -1)
    {
        ini_set('default_socket_timeout', -1);
        $redis = new \Redis();
        $redis->connect(Env::get('database.redis_host', '127.0.0.1'), "63792");
        $redis->auth("123456Qwe");
        if ($stop === 0) {
            $stop = $redis->lLen($key);
        }
        $redis->lRem($key);
        $message = $redis->lRange($key, $start, $stop);
        return $message;
    }

    /**
     * sadd
     * @param $key
     * @param $info
     */
    public static function _sAdd($key, $info)
    {
        ini_set('default_socket_timeout', -1);
        $redis = new \Redis();
        $redis->connect(Env::get('database.redis_host', '127.0.0.1'), "63792");
        $redis->auth("123456Qwe");
        $redis->sAdd($key, $info);
    }


    /**
     * 返回key的类型
     * @param $key
     * @return int
    const REDIS_NOT_FOUND       = 0;
     * const REDIS_STRING          = 1;
     * const REDIS_SET             = 2;
     * const REDIS_LIST            = 3;
     * const REDIS_ZSET            = 4;
     * const REDIS_HASH            = 5;
     */
    public static function key_exist($key)
    {
        \Redis::REDIS_STRING;
        ini_set('default_socket_timeout', -1);
        $redis = new \Redis();
        $redis->connect(Env::get('database.redis_host', '127.0.0.1'), "63792");
        $redis->auth("123456Qwe");
        return $redis->type($key);
    }

    public static function _sMember($key)
    {
        ini_set('default_socket_timeout', -1);
        $redis = new \Redis();
        $redis->connect(Env::get('database.redis_host', '127.0.0.1'), "63792");
        $redis->auth("123456Qwe");
        return $redis->sMembers($key);
    }

    public static function _sRem($key)
    {
        ini_set('default_socket_timeout', -1);
        $redis = new \Redis();
        $redis->connect(Env::get('database.redis_host', '127.0.0.1'), "63792");
        $redis->auth("123456Qwe");
        $redis->del($key);
    }

    public static function sAdd($info)
    {
        $redis = new \Redis();
        $redis->connect("127.0.0.1", "63792");
        $redis->sAdd('member_online', $info);
    }

    public static function get($uid, $type)
    {
        $redis = new \Redis();
        //连接扩展
        $key = $type . '_' . $uid;
        $redis->connect("127.0.0.1", "63792");
        $message = $redis->sMembers($key);
        return $message;
    }

    public static function addOnline($token, $data)
    {
        $redis = new \Redis();
        $redis->connect("127.0.0.1", "63792");
        $redis->set($token, $data);
    }

    public static function removeOnline($token, $data)
    {
        $redis = new \Redis();
        $redis->connect("127.0.0.1", "63792");
        $redis->sRemove($token);
    }


    public static function add($uid, $data, $type = 'friend')
    {
        $data['uid'] = $uid;

        OfflineMessage::createData($data);
        /*  $redis = new \Redis();
          //连接扩展
          $redis->connect("127.0.0.1","63792");
          if(is_array($data))
              $data = json_encode($data);
          $redis->sAdd($type.'_'.$uid,$data);*/
    }

    public static function push($channel, $data)
    {
        $redis = new \Redis();
        //连接扩展
        $redis->connect("127.0.0.1", "63792");
        $data = \GuzzleHttp\json_encode($data);
        $redis->lPush($channel, $data);
    }

    public static function pop($channel)
    {
        $redis = new \Redis();
        //连接扩展
        $redis->connect("127.0.0.1", "63792");
        $result = $redis->rPop($channel);
        if ($result == false || $result == null) return;
        $result = \GuzzleHttp\json_decode($result, TRUE);
        return $result;
    }

    public function remove($uid, $type, $id, $maxid = 0)
    {
        $where = array();
        $where[] = ['uid', '=', $uid];
        $where[] = ['id', '=', $id];
        OfflineMessage::delete($where);
        if ($maxid > 0) {
            $where = array();
            $where[] = ['uid', '=', $uid];
            $where[] = ['autoid', '<=', $maxid];
            OfflineMessage::editWhere($where, array('issend' => 1));
        }


    }
}