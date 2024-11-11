<?php
/*
 module:		会员管理
 create_time:	2020-08-09 15:44:50
 author:
 contact:
*/

namespace app\api\service;

use app\admin\model\Friend;
use xhadmin\db\MemberGroup;
use app\api\model\Member;
use app\api\model\Room;
use think\facade\Db;
use think\facade\Log;
use think\exception\ValidateException;
use xhadmin\CommonService;
use xhadmin\db\ChatSetting;
use xhadmin\service\api\RedisService;

class MemberService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Member::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page]);
            //echo Db::getLastSql();
        } catch (\Exception $e) {
            echo $e->getMessage();
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['list' => $res->items(), 'count' => $res->total()];
    }

    /*
     * @Description  列表数据
     */
    public static function loadData($where, $field, $orderby, $limit, $page)
    {
        $page = $page-1;
        $page = $page*$limit;

        $result = Db::name('member')->field($field)->alias('a')

            ->where($where)->limit($page,$limit)->order($orderby)->select()->toArray();
        return $result;
    }


    /*
     * @Description  添加
     */
    public static function add($data)
    {
        try {
            $res = Member::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->member_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            $res = Member::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }

    public static function login($data, $returnField)
    {
        try {

            switch ($data['logintype']) {
                case 'oneclick':
                case 'phone':
                    $where['mobile'] = $data['membername'];
                    break;
                case 'mobilepwd':
                    $where['mobile'] = $data['membername'];
                    $where['pwd'] = md5($data['password'] . config('my.password_secrect'));
                    break;
                case 'email':
                    $where['email'] = strtolower($data['membername']);
                    $where['pwd'] = md5($data['password'] . config('my.password_secrect'));
                    break;
                default:
                    $where['membername'] = $data['membername'];
                    $where['pwd'] = md5($data['password'] . config('my.password_secrect'));
                    //$res = Member::getWhereInfo($where, $returnField);
                    break;
            }

            $res = Member::getWhereInfo($where, $returnField);

            $arr = array();
            if (empty($res)) {//为空
                return '';

            } else {
                $res['isreg'] = '1';
    
                $arr['islogin'] = 1;
                $arr['logintime'] = time();

                Member::update($arr,$where );
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return checkData($res);
    }

    public static function chatList1($uid, $page, $pagesize = 1000)
    {

        $unread_friend_apply_count = Db::name('notice')->field('uto')->where([
            'uto' => $uid,
            'operation' => 'not_operated',
        ])->count();
        $where = array();
        $where[] = ['ufrom', '=', $uid];
        $where[] = ['isshow', '=', 1];
        $orderby = 'istop desc,update_time desc';
        $list = ChatService::indexList($where, '*', $orderby, $pagesize, $page);
        $list['unread_friend_apply_count'] = $unread_friend_apply_count;
        return $list;
    }

    /**start**/
    /*
     * $to :接受者
     * $message_type:发送的消息类型
     * $type:friend:好友；group：群聊
     * $content:内容
     * $member_id;发送者
     * $options:后面对应的参数
     *
     */
    public static function send($fromInfo, $toInfo, $message_type, $type, $content, $options = '', $sendme = false)
    {

        if(!in_array($message_type,array('withdraw','busyLine','emoticon','system','text','voiceroom','card','videoroom','map','image','audio','Video','changevoice','leaveroom'))){
            return 1;
        }
        //chatvoice
        if (!in_array($type, array('friend', 'group'))) {
            return 1;
        }

        $t = time();
        $setting = \app\api\model\Chatsetting::find(1);
        // 读取系统配置
        if ($setting['dirty_words']) {
            $dirty_words = explode("\n", $setting['dirty_words']);
            $find = $replace = [];
            foreach ($dirty_words as $key => $value) {
                $value = trim($value);
                if ($value) {
                    $find[] = $value;
                    $replace[] = str_pad('', function_exists('mb_strlen') ? mb_strlen($value) : 2, '*');
                }
            }
            if ($dirty_words) {
                $content = str_replace($dirty_words, $replace, $content);
            }
        }

        // 判断发言用户是否被禁用
        if ($fromInfo['status'] == '0') {
            return 3;
            // return $this->json(3, '该账户已经被禁用');
        }

        // 找出用户信息
        if ($type == 'friend') {
            // 判断系统配置是否开启了允许私聊
            if ($setting && $setting['private_chat'] != '1') {
                return 5;
                //return $this->json(83, '系统设置了禁止私聊');
            }


            //判断是否好友，不是好友就添加
            $frind_remark1 = Db::name('friend')->where(['friend_uid' => $toInfo['member_id'], 'uid' => $fromInfo['member_id']])->field('blocked,state,remark')->find();

            if (empty($frind_remark1)) {
                    return 8;
            }

            if($frind_remark1['blocked']==1){
                return 7;
            }
            $frind_remark=Db::name('friend')->where(['uid' => $toInfo['member_id'], 'friend_uid' => $fromInfo['member_id']])->field('blocked,state,remark')->find();
            if(empty($frind_remark)){
                return 8;
            }
            if($frind_remark['blocked']==1){
                return 7;
            }
            //读取对方是否有备注，如果有备注就读取备注

            if (!empty($frind_remark)) {
                $remark = $frind_remark['remark'];
                $fromInfo['nickname'] = !empty($remark) ? $remark : $fromInfo['nickname'];

            }
            // 如果系统配置不允许陌生人之间聊天，则判断是否是好友

        } else {
            // 判断系统配置是否开启了允许群聊
            if ($setting && $setting['group_chat'] != '1') {
                return 5;
                //return $this->json(83, '系统设置了禁止群聊');
            }
            // 判断是否是群成员
            $member_info = Db::name('group_member')->where(['gid' => $toInfo['gid'], 'uid' => $fromInfo['member_id']])->find();
            if (!$member_info) {
                return 2;
                //return $this->json(2, '不是群组成员无法发言');
            }

            // 判断是否在禁言中
            if ($member_info['forbidden'] > $t) {
                return 4;
                //return $this->json(4, '已被禁言，无法发送消息');
            }

            if ($toInfo['state'] == 'disabled') {
                return 6;
                //return $this->json(85, '该群组已经被禁用');
            }

        }


        if ($message_type == 'music') {
            $songid = $options['song_id'];
            if (empty($songid)) {
                throw new ValidateException('参数错误1');
            }
            $url = 'http://tingapi.ting.baidu.com/v1/restserver/ting?size=1000&type=1&callback=cb_list&_t=1468380543284&format=json&method=baidu.ting.song.play&songid=' . $songid;

            $result = file_get_contents($url);

            $result = str_replace('cb_list(', '', $result);
            $result = str_replace(');', '', $result);

            $result = json_decode($result, TRUE);

            if (empty($result) || !empty($result['errno'])) {
                throw new ValidateException('参数错误2');
            }
            $songinfo = $result['songinfo'];
            $pic_premium = $songinfo['pic_premium'];
            $show_link = $songinfo['bitrate']['show_link'];
            $title = $songinfo['title'];
            $song_id = $songinfo['song_id'];
            $author = $songinfo['author'];
            $options = array('pic' => $pic_premium, 'title' => $title, 'song_id' => $song_id, 'author' => $author);
        } else if ($message_type == 'leaveroom') {

            $roomnum = $options['roomnum'];
            $id = $options['ID'];
            $lose = 10;
            $options = array('roomnum' => $roomnum, 'times' => $lose, 'ID' => $id);
        }
        $sendtype = 1;
        if($message_type=='videoroom'||$message_type=='voiceroom'){
            if (!empty($options['ID'])) {
                if($options['callTime']!=1){
                    $sendtype = 0;
                }

            }
        }
        if($message_type=='busyLine'){
            $sendtype = 0;
        }
        if (empty($options['ID'])) {
            $options['ID'] = '';
        }
        // 执行推送
        $channel1 = $type == 'friend' ? "user-{$toInfo['member_id']}" : "group-{$toInfo['gid']}";

        $mid = uuid() . $channel1;

        $data = [
            'id' => $mid,
            'from_id' => $fromInfo['ypid'],
            'from_name' => $fromInfo['nickname'],
            'from_avatar' => $fromInfo['avatar'],
            'to_id' => $type == 'friend' ? $toInfo['ypid'] : $toInfo['gid'],
            'to_name' => $type == 'friend' ? $toInfo['nickname'] : $toInfo['groupname'],
            'to_avatar' => $type == 'friend' ? $toInfo['avatar'] : $toInfo['avatar'],
            'data' => $content,
            'create_time' => $t,
            'chat_type' => $type,
            'type' => $message_type,
            'options' => $options,
            'isremove' => 0,
            'sendStatus' => 'success'
        ];


        if (!empty($post['uniqueId'])) {
            $data['uniqueId'] = $post['uniqueId'];
        }

        if ($type == 'friend') {
            $data['channel'] = $channel1;

            \app\api\service\RedisService::add($toInfo['member_id'], $data, 'friend');
            $arr = array('event' => 'message', 'data' => $data);
            //echo $toInfo['member_id'];
            sendUid($toInfo['member_id'], $arr,$sendtype);

            //$result = $push->emit($channel1, $event, $data);
        } else {
            $where = array();
            $where[] = ['gid', '=', $toInfo['gid']];

            $memberlists = MemberGroup::loadGroupByid($toInfo['gid']);

            $tempMemberData = array();
            foreach ($memberlists as $key => $v) {

                if ($v['uid'] == $fromInfo['member_id']) continue;
                $arr = array('event' => 'message', 'data' => $data);
                sendUid($v['uid'], $arr,1);
                //RedisService::add($v['uid'],$data,'friend');
                $temp = $data;
                $temp['uid'] = $v['uid'];
                $tempMemberData[] = $temp;
                //Gateway::sendToAll($data);
            }

            \app\api\service\RedisService::addAll($tempMemberData);


        }
        if ($sendme == true) {
            $arr = array('event' => 'own_message', 'data' => $data);
            sendUid($fromInfo['member_id'], $arr);
        }
        return $mid;
    }

    /**end**/

    public static function updatepwd($data)
    {
        try {
            if (is_null($data['repwd'])) throw new \Exception("密码不能为空");
            $where['member_id'] = $data['member_id'];
            $data = array('pwd' => md5($data['repwd'] . config('my.password_secrect')));
            $res = Member::update($data, $where);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return $res;
    }
}

