<?php
/*
 module:		会员分组
 create_time:	2020-01-26 12:41:51
 author:
 contact:
*/

namespace app\api\controller;

use think\facade\App;

require_once App::getRootPath() . 'extend/QRCode/phpqrcode.php';

use think\facade\Db;
use xhadmin\db\Groupmember;
use xhadmin\db\Member;
use xhadmin\db\MemberGroup;
use xhadmin\service\api\GroupsService;
use xhadmin\db\Groups as GroupsDb;
use think\facade\Cache;
use think\facade\Log;
use xhadmin\service\api\MemberService;
use app\api\service\MemberGroupService;
use xhadmin\service\api\RedisService;

class Groups extends Common
{

    /**
     * @api {get} /Groups/nickname 01、修改自己在群里的昵称
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  群昵称
     * @apiParam (输入参数：) {int}            [id] 群的id
     * @apiParam (输入参数：) {string}            [nickname] 群的昵称
     *
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据
     * @apiParam (成功返回参数：) {string}        array.data.list 返回数据列表
     * @apiParam (成功返回参数：) {string}        array.data.count 返回数据总数
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function nickname()
    {
        $uid = $this->request->uid;    //token解码用户ID
        $qunzhu = \xhadmin\db\Member::getInfo($uid);
        $groupid = $this->request->post('id', 0, 'intval');
        if (empty($groupid)) {
            return error(100);
        }
        $where = array();
        $where[] = ['gid', '=', $groupid];
        $where[] = ['uid', '=', $uid];
        $info = \xhadmin\db\MemberGroup::getWhereInfo($where);
        if (empty($info)) {
            return error(8);
        }
        $nickname = $this->request->post('nickname', '');

        $data = array('remark' => $nickname);
        \xhadmin\db\MemberGroup::editWhere($where, $data);
        return success('操作成功', array('groupid' => $groupid));
    }

    /**
     * @api {get} /Groups/remark 01、修改群公告
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  会员分组
     * @apiParam (输入参数：) {int}              [id] 群的id
     * @apiParam (输入参数：) {string}           [remark] 群的公告
     *
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据
     * @apiParam (成功返回参数：) {string}        array.data.list 返回数据列表
     * @apiParam (成功返回参数：) {string}        array.data.count 返回数据总数
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function remark()
    {
        $uid = $this->request->uid;    //token解码用户ID
        $qunzhu = \xhadmin\db\Member::getInfo($uid);
        $groupid = $this->request->post('id', 0, 'intval');
        if (empty($groupid)) {
            return error(100);
        }
        $where = array();
        $where[] = ['gid', '=', $groupid];
        $where[] = ['uid', '=', $uid];
        $info = \xhadmin\db\Groups::getWhereInfo($where);
        if (empty($info)) {
            return error(10);
        }
        $remark = $this->request->post('remark', '');

        $data = array('remark' => $remark);
        \xhadmin\db\Groups::editWhere($where, $data);
        $where = array();
        $where[] = ['gid', '=', $groupid];
        $groupmembers = \xhadmin\db\MemberGroup::loadList($where, '0,1000', '*');
        //$push    = new  \xhadmin\service\api\Push();
        $content = '[新公告]' . $remark;
        $data = array('ufrom' => $uid, 'uto' => $groupid, 'content' => $content, 'type' => 'group', 'sub_type' => 'system', 'timestamp' => time(), 'chatkey' => 'group' . $groupid, 'message_type' => 1,);
        $mid = \xhadmin\db\Message::createData($data);
        foreach ($groupmembers as $key => $v) {
            $channel1 = "user-" . $v['uid'];
            $event = 'message';
            $data = [
                'id' => $mid,
                'from_id' => $qunzhu['member_id'],
                'from_name' => $qunzhu['nickname'],
                'from_avatar' => $qunzhu['avatar'],
                'to_id' => intval($groupid),
                'to_name' => $info['groupname'],
                'to_avatar' => $info['avatar'],
                'data' => $content,
                'create_time' => time(),
                'chat_type' => 'group',
                'type' => 'system',
                'options' => [],
                'isremove' => 0,
                'sendStatus' => 'success'
            ];


            if (!empty($post['uniqueId'])) {
                $data['uniqueId'] = $post['uniqueId'];
            }
            $arr = array('event' => 'message', 'data' => $data);
            sendchatUid($v['uid'], $arr);
            // $result = $push->emit($channel1, $event, $data);
        }
        return success('操作成功', array('groupid' => $groupid));
    }

    /**
     * @api {get} /Groups/rename 01、修改群聊名称
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  会员分组
     * @apiParam (输入参数：) {int}            [id] 群的id
     * @apiParam (输入参数：) {string}            [name] 群的名称
     *
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据
     * @apiParam (成功返回参数：) {string}        array.data.list 返回数据列表
     * @apiParam (成功返回参数：) {string}        array.data.count 返回数据总数
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function rename()
    {
        $uid = $this->request->uid;    //token解码用户ID
        $qunzhu = \xhadmin\db\Member::getInfo($uid);
        $groupid = $this->request->post('id', 0, 'intval');
        if (empty($groupid)) {
            return error(100);
        }
        $where = array();
        $where[] = ['gid', '=', $groupid];
        $where[] = ['uid', '=', $uid];
        $info = \xhadmin\db\Groups::getWhereInfo($where);
        if (empty($info)) {
            return error(8);
        }
        $name = $this->request->post('name', '');
        if (empty($name)) {
            return error(9);
        }
        $data = array('groupname' => $name);
        \xhadmin\db\Groups::editWhere($where, $data);
        $where = array();
        $where[] = ['gid', '=', $groupid];
        $groupmembers = \xhadmin\db\MemberGroup::loadGroupByid($groupid);


        //$push    = new  \xhadmin\service\api\Push();
        $content = $qunzhu['nickname'] . '修改了群聊名称为' . $name;
        $data = array('ufrom' => $uid, 'uto' => $groupid, 'content' => $content, 'type' => 'group', 'sub_type' => 'system', 'timestamp' => time(), 'chatkey' => 'group' . $groupid, 'message_type' => 1,);
        $mid = \xhadmin\db\Message::createData($data);
        foreach ($groupmembers as $key => $v) {
            $channel1 = "user-" . $v['uid'];
            $event = 'message';
            $data = [
                'id' => $mid,
                'from_id' => $qunzhu['member_id'],
                'from_name' => $qunzhu['nickname'],
                'from_avatar' => $qunzhu['avatar'],
                'to_id' => intval($groupid),
                'to_name' => $name,
                'to_avatar' => $info['avatar'],
                'data' => $content,
                'create_time' => time(),
                'chat_type' => 'group',
                'type' => 'system',
                'options' => [],
                'isremove' => 0,
                'sendStatus' => 'success'
            ];


            if (!empty($post['uniqueId'])) {
                $data['uniqueId'] = $post['uniqueId'];
            }

            sendchatUid($v['uid'], array('event' => 'message', 'data' => $data));
            //$result = $push->emit($channel1, $event, $data);
        }
        return success('操作成功', array('groupid' => $groupid));
    }

    /**
     * @api {get} /Groups/quit 01、解散群
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  会员分组
     * @apiParam (输入参数：) {int}            [groupid] 群的名称
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据
     * @apiParam (成功返回参数：) {string}        array.data.list 返回数据列表
     * @apiParam (成功返回参数：) {string}        array.data.count 返回数据总数
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function quit()
    {

        $uid = $this->request->uid;    //token解码用户ID
        $groupid = $this->request->post('id', 0, 'intval');
        if (empty($groupid)) {
            return error(100);
        }
        $where = array();
        $where[] = ['gid', '=', $groupid];
        $where[] = ['uid', '=', $uid];
        $info = \xhadmin\db\MemberGroup::getWhereInfo($where);
        if (empty($info)) {
            return error(204);
        }
        $where = array();
        $where[] = ['gid', '=', $groupid];
        $groupinfo = \xhadmin\db\Groups::getWhereInfo($where);
        if (empty($info)) {
            return error(203);
        }
        $push = new  \xhadmin\service\api\Push();
        $qunzhuid = $groupinfo['uid'];
        $groupsmember = \xhadmin\db\MemberGroup::loadList($where);
        $isqunzhu = $qunzhuid == $uid;//是否群主
        if ($isqunzhu) {
            \xhadmin\db\MemberGroup::delete($where);
            \xhadmin\db\Groups::delete($where);
        } else {
            $where[] = ['uid', '=', $uid];
            \xhadmin\db\MemberGroup::delete($where);
        }

        $event = 'message';
        $memberinfo = \xhadmin\db\Member::getInfo($uid);
        $data = [
            'id' => uuid(),
            'from_id' => $memberinfo['member_id'],
            'from_name' => $memberinfo['nickname'],
            'from_avatar' => $memberinfo['avatar'],
            'to_id' => intval($groupid),
            'to_name' => $groupinfo['groupname'],
            'to_avatar' => $groupinfo['avatar'],
            'data' => '',
            'create_time' => time(),
            'chat_type' => 'group',
            'type' => 'system',
            'options' => [],
            'isremove' => 0,
            'sendStatus' => 'success'
        ];


        foreach ($groupsmember as $key => $v) {
            if ($isqunzhu) {
                //群主
                $data['data'] = '群主解散了群聊';
            } else {
                //不是群主
                $data['data'] = $memberinfo['nickname'] . '退出了群聊';
            }
            $channel1 = "user-" . $v['uid'];
            $result = $push->emit($channel1, $event, $data);
        }
        return success('操作成功');
    }

    /**
     * @api {get} /Groups/member 01、群成员列表
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  会员分组
     * @apiParam (输入参数：) {int}            [groupid] 群的名称
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据
     * @apiParam (成功返回参数：) {string}        array.data.list 返回数据列表
     * @apiParam (成功返回参数：) {string}        array.data.count 返回数据总数
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function member()
    {
        $uid = $this->request->uid;    //token解码用户ID
        $groupid = $this->request->post('groupid', 0, 'intval');
        if (empty($groupid)) {
            return error(100);
        }
        $where = array();
        $where[] = ['gid', '=', $groupid];
        $where[] = ['uid', '=', $uid];
        $info = \xhadmin\db\MemberGroup::getWhereInfo($where);
        if (empty($info)) {
            return error(6);
        }
        $where = array();
        $where[] = ['gid', '=', $groupid];
        $list = \xhadmin\db\MemberGroup::loadList($where, '0,1000', '*');
        return success('操作成功', $list);
    }

    /**
     * @api {get} /Groups/info 01、群详情
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  会员分组
     * @apiParam (输入参数：) {int}            [groupid] 群的名称
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据
     * @apiParam (成功返回参数：) {string}        array.data.list 返回数据列表
     * @apiParam (成功返回参数：) {string}        array.data.count 返回数据总数
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function info()
    {
        $uid = $this->request->uid;    //token解码用户ID
        $groupid = $this->request->post('groupid', 0, 'intval');
        $pagesize = $this->request->post('pagesize', 44, 'intval');

        if (empty($groupid)) {
            return error(100);
        }
        $where = array();
        $where[] = ['gid', '=', $groupid];
        $where[] = ['uid', '=', $uid];

        $info = \xhadmin\db\MemberGroup::getWhereInfo($where);

        if (empty($info)) {
            return error(6);
        }
        $info = \xhadmin\db\Groups::getInfo($groupid);
        if (empty($info)) {
            return error(203);
        }
        $where = array();
        $where['member_id']= $info['uid'];
        $memberinfo = \app\api\model\Member::getWhereInfo($where,'ypid,nickname,avatar');
        $info['memberinfo'] = $memberinfo;
        $where = array();
        $where[] = ['gid', '=', $groupid];
        $grouplist = \xhadmin\db\MemberGroup::relateQuery('a.remark as nickname,b.membername,b.member_id as id,b.avatar,b.ypid', 'uid', 'member', 'member_id', $where, '0,' . $pagesize, 'a.isowner desc,a.addtime desc');
        return success('操作成功', array('info' => $info, 'group_users' => $grouplist));

    }
    /**start**/
    /**
     * @api {get} /Groups/get 01、获取分组列表
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  会员分组
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [groupname] 分组名称
     * @apiParam (输入参数：) {int}            [state] 状态 开启|1,关闭|0
     * @apiParam (输入参数：) {string}        [timestamp_start] 创建日期开始
     * @apiParam (输入参数：) {string}        [timestamp_end] 创建日期结束
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据
     * @apiParam (成功返回参数：) {string}        array.data.list 返回数据列表
     * @apiParam (成功返回参数：) {string}        array.data.count 返回数据总数
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function get()
    {

        $uid = $this->request->uid;    //token解码用户ID
        $limit = $this->request->get('limit', 20, 'intval');
        $page = $this->request->get('page', 1, 'intval');

        $where = [];
        $where[] = ['uid', '=', $uid];
        $where[] = ['state', '=', 1];
        $limit = ($page - 1) * $limit . ',' . $limit;

        $field = '*';
        $orderby = 'gid desc';

        try {
            $res = GroupsService::pageList(($where), $limit, $field, $orderby);

        } catch (\Exception $e) {
            return json(['status' => $this->errorCode, 'msg' => $e->getMessage()]);
        }

        return json(['status' => $this->successCode, 'data' => htmlOutList($res)]);
    }
    /**end**/


    /**
     * @api {post} /Groups/add 02、添加
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            groupname 分组名称
     * @apiParam (输入参数：) {int}                uid 拥有者id
     * @apiParam (输入参数：) {string}            avatar 群头像
     * @apiParam (输入参数：) {int}                state 状态 开启|1,关闭|0
     * @apiParam (输入参数：) {string}            timestamp 创建日期
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码  201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"操作失败"}
     */
    function add()
    {
        $uid = $this->request->uid;    //token解码用户ID
        $where = array();
        $where[] = ['member_id', '=', $uid];
        $memberinfo = \xhadmin\db\Member::getWhereInfo($where, 'avatar,nickname,ypid,member_id');
        $postField = 'groupname,ypid,type,gid';
        $data = $this->request->only(explode(',', $postField), 'post', null);

        if (empty($data['ypid'])) {
            return json(['status' => $this->errorCode, 'msg' => '您好，成员至少2个才能组群']);
        }
        if (empty($data['type'])) {
            $data['type'] = 'friend';
        }


        $where = array();
        $where[] = ['ypid', 'in', $data['ypid']];
        $memberlist = \xhadmin\db\Member::loadList($where, '0,10000', 'avatar,membername,member_id,ypid,nickname');
        $data['ypid'] = array_unique($data['ypid']);
        if (count($memberlist) != count($data['ypid'])) {
            return json(['status' => $this->errorCode, 'msg' => '您好，您传入的参数有误1']);
        }

        // $push    = new  \xhadmin\service\api\Push();
        if ($data['type'] == 'group') {
            //添加群成员
            $gid = intval($data['gid']);
            $groupinfo = \xhadmin\db\Groups::getInfo($gid);

            if (empty($groupinfo)) {
                return json(['status' => $this->errorCode, 'msg' => '您好，您传入的参数有误1']);
            }
            $arr = array();
            foreach ($memberlist as $key => $v) {

                $where = array();
                $where[] = ['uid', '=', $v['member_id']];
                $where[] = ['gid', '=', $gid];

                $info = \xhadmin\db\MemberGroup::getWhereInfo($where);

                if (empty($info)) {
                    $data = array('gid' => $gid, 'uid' => $v['member_id'], 'remark' => $v['nickname'], 'state' => 1, 'ypid' => $v['ypid']);
                    \xhadmin\db\MemberGroup::createData($data);
                    $arr[] = $v;
                }
            }
            $nickname = array();
            foreach ($arr as $key1 => $v1) {
                $nickname[] = $v1['nickname'];
            }
            if (empty($nickname)) {
                return json(['status' => $this->successCode, 'msg' => '操作成功']);
            } else {

                //RedisService::removeGroup($gid);

            }
            $where = array();
            $where['gid'] = $gid;
            $memberlist = \xhadmin\db\Member::loadList($where, '0,10000', 'avatar,membername,member_id,ypid,nickname');
            $memberlist = MemberGroup::relateQuery('b.avatar,b.membername,b.member_id,b.ypid,b.nickname','uid','member','member_id',$where,'0,5000','isowner desc,addtime asc');
            //$memberlist[] = $memberinfo;
            foreach ($memberlist as $key => $v) {

                // 执行推送

                $channel1 = "user-" . $v['member_id'];
                $event = 'message';
                $data = [
                    'id' => uuid(),
                    'from_id' => $memberinfo['ypid'],
                    'from_name' => $memberinfo['nickname'],
                    'from_avatar' => $memberinfo['avatar'],
                    'to_id' => intval($gid),
                    'to_name' => $groupinfo['groupname'],
                    'to_avatar' => $groupinfo['avatar'],
                    'data' => (($v['member_id']==$uid)?'您':$memberinfo['nickname']) . '邀请' . implode(',', $nickname) . '加入群聊',
                    'create_time' => time(),
                    'chat_type' => 'group',
                    'type' => 'system',
                    'options' => [],
                    'isremove' => 0,
                    'sendStatus' => 'success'
                ];

                if (!empty($post['uniqueId'])) {
                    $data['uniqueId'] = $post['uniqueId'];
                }
                $data['channel'] = $channel1;
                RedisService::add($v['member_id'], $data);
                $arr = array('event' => 'message', 'data' => $data);
                sendchatUid($v['member_id'], $arr);

                //$result = $push->emit($channel1, $event, $data);

            }
            return json(['status' => $this->successCode, 'msg' => '操作成功']);
        }


        $img = array();
        $img[] = $memberinfo['avatar'];
        $uids = array();
        $nicklist = array();
        $nicklist[] = $memberinfo['nickname'];
        foreach ($memberlist as $key => $v) {
            if (!empty($v['avatar']))
                $img[] = $v['avatar'] . '?x-oss-process=image/resize,w_90,h_90';
            $uids[] = $v['member_id'];
            $nicklist[] = $v['nickname'];
        }
        $where = array();
        $where[] = ['friend_uid', 'in', $uids];
        $where[] = ['uid', '=', $uid];

        $friendlist = \xhadmin\db\Friend::loadList($where, '0,1000');

        if (count($friendlist) != count($memberlist)) {
            return json(['status' => $this->errorCode, 'msg' => '您的参数不正确']);
        }
        $state = 1;

        if (!empty($data['groupname'])) {
            $result = content_verify($data['groupname']);
            if ($result == -1) {
                return json(['status' => $this->errorCode, 'msg' => '您好，您的群名称包含敏感词汇不能发布']);
            }
        } else {
            $tempname = array_slice($nicklist, 0, 3);
            $data['groupname'] = implode(',', $tempname);
        }
        $pngName = time() . uniqid() . ".jpg";//生成图片名称

       $result = getGroupAvatar($img, true, 'uploads/group/' . $pngName);
        if ($result) {
            $url = $result;
        } else {
            $url = config('my.amy_customer.member_logo');
        }

        $data['avatar'] = $url;
        $data['timestamp'] = time();
        $data['uid'] = $uid;
        $data['state'] = 1;
        $data['ypid'] = $memberinfo['ypid'];
        $groupname = $data['groupname'];
        try {

            unset($data['type']);
            unset($data['gid']);
            $res = GroupsService::add($data);

            if ($res > 0) {

                $memberlist[] = $memberinfo;
                $content = $memberinfo['nickname'] . '创建了群聊';
                $data = array('ufrom' => $uid, 'uto' => $res, 'content' => $content, 'type' => 'group', 'sub_type' => 'notice', 'timestamp' => time(), 'chatkey' => 'group' . $res, 'message_type' => 1,);
                $mid = \xhadmin\db\Message::createData($data);
                $t = time();
                $isowner = 0;
                foreach ($memberlist as $key => $v) {
                    if($memberinfo['ypid']==$v['ypid']){
                        $isowner = 2;
                    }else{
                        $isowner =0;
                    }
                    $data = array(
                        'gid' => $res,
                        'uid' => $v['member_id'],
                        'state' => 1,
                        'remark' => $v['nickname'],
                        'ypid' => $v['ypid'],
                        'addtime' => $t,
                        'isowner'=>$isowner
                    );
                    \xhadmin\db\MemberGroup::createData($data);
                    // 执行推送

                    $channel1 = "user-" . $v['member_id'];
                    $event = 'addgroup';
                    $data = [
                        'id' => uuid(),
                        'from_id' => $memberinfo['ypid'],
                        'from_name' => $memberinfo['nickname'],
                        'from_avatar' => $memberinfo['avatar'],
                        'to_id' => intval($res),
                        'to_name' => $groupname,
                        'to_avatar' => $url,
                        'data' => $content,
                        'create_time' => time(),
                        'chat_type' => 'group',
                        'type' => 'system',
                        'options' => [],
                        'isremove' => 0,
                        'sendStatus' => 'success'
                    ];


                    if (!empty($post['uniqueId'])) {
                        $data['uniqueId'] = $post['uniqueId'];
                    }
                    $data['channel'] = $channel1;
                    RedisService::add($v['member_id'], $data);
                    $arr = array('event' => 'message', 'data' => $data);
                    sendchatUid($v['member_id'], $arr);
                }
            }

        } catch (\Exception $e) {
            return json(['status' => $this->errorCode, 'msg' => $e->getMessage()]);
        }
        if ($state == 0) {
            return json(['status' => $this->successCode, 'msg' => '您的群名称存在敏感词汇在审核中']);
        }
        return json(['status' => $this->successCode, 'msg' => '操作成功']);
    }

    /**
     * @api {post} /Groups/update 03、修改
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            gid 主键ID (必填)
     * @apiParam (输入参数：) {string}            groupname 分组名称
     * @apiParam (输入参数：) {int}                uid 拥有者id
     * @apiParam (输入参数：) {string}            avatar 群头像
     * @apiParam (输入参数：) {int}                state 状态 开启|1,关闭|0
     * @apiParam (输入参数：) {string}            timestamp 创建日期
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码  201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"操作失败"}
     */
    function update()
    {
        $postField = 'gid,groupname,uid,avatar,state,timestamp';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['gid'])) return json(['status' => $this->errorCode, 'msg' => '参数错误']);
        try {
            $where['gid'] = $data['gid'];
            $res = GroupsService::update($where, $data);
        } catch (\Exception $e) {
            return json(['status' => $this->errorCode, 'msg' => $e->getMessage()]);
        }
        return json(['status' => $this->successCode, 'msg' => '操作成功']);
    }

    /**
     * @api {post} /Groups/delete 04、删除
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            gids 主键id 注意后面跟了s 多数据删除
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"操作失败"}
     */
    function delete()
    {
        $idx = $this->request->post('gids', '', 'serach_in');
        if (empty($idx)) return json(['status' => $this->errorCode, 'msg' => '参数错误']);
        try {
            $data['gid'] = explode(',', $idx);
            GroupsService::delete($data);
        } catch (\Exception $e) {
            return json(['status' => $this->errorCode, 'msg' => '操作失败']);
        }
        return json(['status' => $this->successCode, 'msg' => '操作成功']);
    }

    /**
     * @api {get} /Groups/view 05、查看数据
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  查看数据
     * @apiParam (输入参数：) {string}            gid 主键ID
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据详情
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"没有数据"}
     */
    function view()
    {
        $data['gid'] = $this->request->get('gid', '', 'intval');
        try {
            $field = 'gid,groupname,uid,avatar,state,timestamp';
            $res = checkData(GroupsDb::getWhereInfo($data, $field));
        } catch (\Exception $e) {
            return json(['status' => $this->errorCode, 'msg' => $e->getMessage()]);
        }
        Log::info('接口输出：' . print_r($res, true));
        return json(['status' => $this->successCode, 'data' => $res]);
    }


    /**
     * @api {get} /Groups/create_qrcode 06、生成群二维码
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  查看数据
     * @apiParam (输入参数：) {string}            gid 群id
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据详情
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"返回成功","data":{"img":"https:\/\/api.aimichat.com\/uploads\/116_13219.png"}}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"没有数据"}
     */
    function create_qrcode()
    {
        $uid = $this->request->uid;    //token解码用户ID
        $gid = $this->request->post('gid');
        $group = \xhadmin\db\Groups::getWhereInfo(['gid' => $gid]);
        if (!$group) {
            return json(['status' => $this->errorCode, 'msg' => '该群不存在']);
        }

        $where = [];
        $where[] = ['gid', '=', $gid];
        $where[] = ['uid', '=', $uid];
        $a = MemberGroup::getWhereInfo($where);
        if (!$a) {
            return json(['status' => $this->errorCode, 'msg' => '您不是该群成员']);
        }
        $base_domain = config('my.home_domain');

        $base_url = 'uploads/';
        $file = $base_url . $gid . "_" . $uid . '.png';
        unlink($file);

        $data = [
            'event' => 'join_group',
            'gid' => $gid,
            'uid' => $uid,
            'expiration_time' => (time() + 7 * 86400),
        ];
        $value = json_encode($data, true); //二维码内容
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 6;//生成图片大小
        //生成二维码图片
        \QRcode::png($value, $file, $errorCorrectionLevel, $matrixPointSize, 2);
        $logo = FALSE;//准备好的logo图片
        $QR = $base_domain . $file;//已经生成的原始二维码图
        if ($logo !== FALSE) {
            $QR = imagecreatefromstring(file_get_contents($QR));
            $logo = imagecreatefromstring(file_get_contents($logo));
            $QR_width = imagesx($QR);//二维码图片宽度
            $QR_height = imagesy($QR);//二维码图片高度
            $logo_width = imagesx($logo);//logo图片宽度
            $logo_height = imagesy($logo);//logo图片高度
            $logo_qr_width = $QR_width / 5;
            $scale = $logo_width / $logo_qr_width;
            $logo_qr_height = $logo_height / $scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            //重新组合图片并调整大小
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
                $logo_qr_height, $logo_width, $logo_height);
        }
        //输出图片
        imagepng($QR, $file);
        $data = [
            'img' => $base_domain . $file,
        ];
        return $this->ajaxReturn($this->successCode, '返回成功', $data);
    }

    /**
     * @api {get} /Groups/group_add_by_qrcode 07、通过群二维码加入群聊
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  查看数据
     * @apiParam (输入参数：) {string}            gid 群id
     * @apiParam (输入参数：) {string}            expiration_time 有效期
     * @apiParam (输入参数：) {string}            uid 邀请者uid
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据详情
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"返回成功","data":{"img":"https:\/\/api.aimichat.com\/uploads\/116_13219.png"}}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"没有数据"}
     */
    public function group_add_by_qrcode()
    {
        $gid = $this->request->post('gid');
        $expiration_time = $this->request->post('expiration_time');
        $member_id = $this->request->post('uid'); #邀请者id
        $uid = $this->request->uid; #加入者

        if ($expiration_time < time()) {
            return json(['status' => $this->errorCode, 'msg' => '该二维码已经失效，请重新生成！']);
        }

        $group = \xhadmin\db\Groups::getInfo($gid);
        if (!$group) {
            return json(['status' => $this->errorCode, 'msg' => '该群不存在']);
        }
        $v = Member::getInfo($uid);
        if (!$v) {
            return json(['status' => $this->errorCode, 'msg' => '请登录!']);
        }

        $memberinfo = Member::getInfo($member_id);
        if (!$memberinfo) {
            return json(['status' => $this->errorCode, 'msg' => '请登录!']);
        }


        $where = [];
        $where[] = ['gid', '=', $gid];
        $where[] = ['uid', '=', $uid];
        $a = MemberGroup::getWhereInfo($where);
        if ($a) {
            return json(['status' => $this->errorCode, 'msg' => '您已经是该群成员，无需重复添加']);
        } else {
            $data = array('gid' => $gid, 'uid' => $v['member_id'], 'remark' => $v['nickname'], 'state' => 1, 'ypid' => $v['ypid']);
            \xhadmin\db\MemberGroup::createData($data);
        }


        $channel1 = "user-" . $v['member_id'];
        $event = 'message';
        $data = [
            'id' => uuid(),
            'from_id' => $memberinfo['ypid'],
            'from_name' => $memberinfo['nickname'],
            'from_avatar' => $memberinfo['avatar'],
            'to_id' => intval($gid),
            'to_name' => $group['groupname'],
            'to_avatar' => $group['avatar'],
            'data' => $v['nickname'] . '通过扫描' . $memberinfo['nickname'] . '的二维码加入群聊',
            'create_time' => time(),
            'chat_type' => 'group',
            'type' => 'system',
            'options' => [],
            'isremove' => 0,
            'sendStatus' => 'success'
        ];

        if (!empty($post['uniqueId'])) {
            $data['uniqueId'] = $post['uniqueId'];
        }
        $data['channel'] = $channel1;
        RedisService::add($v['member_id'], $data);
        $arr = array('event' => 'message', 'data' => $data);
        sendchatUid($v['member_id'], $arr);

    }

    /**
     * @api {post} /Groups/set_state 07、是否把群加到通讯录
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  查看数据
     * @apiParam (输入参数：) {string}            gid 群id
     * @apiParam (输入参数：) {string}            state 1移出，2加到通讯
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据详情
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"返回成功","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"没有数据"}
     */
    function set_state()
    {
        $uid = $this->request->uid;    //token解码用户ID
        $gid = $this->request->post('gid');
        $state = $this->request->post('state');
        $group = \xhadmin\db\Groups::getWhereInfo(['gid' => $gid]);
        if (!$group) {
            return json(['status' => $this->errorCode, 'msg' => '该群不存在']);
        }
        if (!in_array($state, [1, 2])) {
            return json(['status' => $this->errorCode, 'msg' => 'status值错误']);
        }
        $where = [];
        $where[] = ['gid', '=', $gid];
        $where[] = ['uid', '=', $uid];
        $a = MemberGroup::getWhereInfo($where);
        if (!$a) {
            return json(['status' => $this->errorCode, 'msg' => '您不是该群成员']);
        }
        $result = MemberGroup::editWhere($where, ['state' => $state]);
        return $this->ajaxReturn($this->successCode, '返回成功', $result);
    }

    /**
     * @api {post} /Groups/stated_group 07、获取加入通讯录的群
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  查看数据
     * @apiParam (输入参数：) {int}           [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}           [page] 当前页码
     * @apiParam (输入参数：) {int}           [all] 1为获取所有群(不论是否加入通讯录)

     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据详情
     * @apiSuccessExample {json} 01 成功示例
     * {
    "status": "200",
    "msg": "返回成功",
    "data": [
        {
            "gid": 114, //群id
            "groupname": "13322222222,艾米", //群名称
            "avatar": "http://img.expertcp.com/amy/ke_fu.png", //群图标
            "group_remark": null //群备注
        }
    ]
}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"没有数据"}
     */
    function stated_group()
    {
        $uid = $this->request->uid;    //token解码用户ID
        $limit  = $this->request->post('limit', 20, 'intval');
        $page   = $this->request->post('page', 1, 'intval');
        $all   = $this->request->post('all', 0, 'intval');

        $where = [];
        if ($all == 0) {
            $where['b.state'] = 2;
        }
        $where['b.uid'] = $uid;

        $field = 'a.gid,a.groupname,a.avatar,b.group_remark';
        $orderby = 'b.gid desc';

        $res = MemberGroupService::loadData(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn($this->successCode,'返回成功',htmlOutList($res));
    }

    /**
     * @api {get} /Groups/set_group_remark 08、群备注
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  查看数据
     * @apiParam (输入参数：) {string}            gid 群id
     * @apiParam (输入参数：) {string}            remark 备注
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据详情
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"返回成功","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"没有数据"}
     */
    public function set_group_remark()
    {

        $uid = $this->request->uid;
        $remark = $this->request->post('remark');
        $gid = $this->request->post('gid');
        $where = [];
        $where['gid'] = $gid;
        $where['uid'] = $uid;
        if (!MemberGroup::getWhereInfo($where)) {
            return json(['status' => $this->errorCode, 'msg' => '您不是该群聊成员，不能修改备注！']);
        }
        $result = MemberGroup::editWhere($where, ['group_remark' => $remark]);
        return $this->ajaxReturn($this->successCode, '返回成功', $result);
    }


    /**
     * @api {get} /Groups/leave_group 09、主动退群
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  查看数据
     * @apiParam (输入参数：) {string}            gid 群id
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据详情
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"返回成功","data":{"img":"https:\/\/api.aimichat.com\/uploads\/116_13219.png"}}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"没有数据"}
     */
    public function leave_group()
    {
        $uid = $this->request->uid;
        $where = array();
        $where['member_id'] = $uid;
        $memberinfo = \app\api\model\Member::getWhereInfo($where);
        $gid = $this->request->post('gid');
        $where = [];
        $where['gid'] = $gid;
        $where['uid'] = $uid;
        $group = MemberGroup::getWhereInfo($where);

        if (empty($group)) {
            return json(['status' => $this->errorCode, 'msg' => '您不是该群聊成员，无需退群！']);
        }
        $groupInfo = \xhadmin\db\Groups::getInfo($group['gid']);
        $result = MemberGroup::delete($where);
        $event = 'message';
        $data = [
            'id' => uuid(),
            'from_id' => $memberinfo['ypid'],
            'from_name' => $memberinfo['nickname'],
            'from_avatar' => $memberinfo['avatar'],
            'to_id' => intval($gid),
            'to_name' => $groupInfo['groupname'],
            'to_avatar' => $groupInfo['avatar'],
            'data' =>$memberinfo['nickname'].'退出了群聊',
            'create_time' => time(),
            'chat_type' => 'group',
            'type' => 'system',
            'options' => [],
            'isremove' => 0,
            'sendStatus' => 'success'
        ];
        $where = array();
        $where[] = ['gid', '=', $group['gid']];

        $memberlists = MemberGroup::loadGroupByid($group['gid']);
        foreach ($memberlists as $key=>$v){
            $arr = array('event' => 'message', 'data' => $data);
            sendUid($v['member_id'],$data);
        }
        return $this->ajaxReturn($this->successCode, '返回成功', $result);
    }

    /**
     * @api {get} /Groups/delete_group_member 10、删除群成员
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  查看数据
     * @apiParam (输入参数：) {string}            gid 群id
     * @apiParam (输入参数：) {string}            ypid
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据详情
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"返回成功","data":{"img":"https:\/\/api.aimichat.com\/uploads\/116_13219.png"}}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"没有数据"}
     */
    public function delete_group_member(){
        $uid = $this->request->uid;
        $where = array();
        $where['member_id'] = $uid;
        $info = \app\api\model\Member::getWhereInfo($where);
        $gid = $this->request->post('gid');
        $ypids = $this->request->post('ypids');
        $ypids = explode(',',$ypids);
        $groupInfo = \xhadmin\db\Groups::getInfo($gid);
        if(empty($groupInfo)){
            return json(['status' => $this->errorCode, 'msg' => '该群聊不存在']);
        }
        $temp = [];
        foreach ($ypids as $key=>$v){

            $where = array();
            $where['ypid'] = $v;
            $where['gid'] = $gid;

            if($v['isowner']<2){
                    $res = MemberGroup::delete($where);
                    $where = array();
                    $where['ypid'] = $v;
                    $memberinfo = \app\api\model\Member::getWhereInfo($where,'member_id,ypid,nickname,avatar');
                $temp[] = $memberinfo;

            }
        }
        $where = array();
        $where['gid'] =$gid;
        $lists = MemberGroup::relateQuery('b.avatar','uid','member','member_id',$where,'0,9','isowner desc,addtime asc');
        $img = array();
        foreach ($lists as $key=>$v){
            $img[] = $v['avatar'];
        }
        $pngName = time() . uniqid() . ".jpg";//生成图片名称
        $result = getGroupAvatar($img, true, 'uploads/group/' . $pngName);

        $tempdata = array(
            'avatar'=>$result
        );
        \xhadmin\db\Groups::editWhere($where,$tempdata);
        foreach ($temp as $key=>$memberinfo){
            $data = [
                'id' => uuid(),
                'from_id' => $memberinfo['ypid'],
                'from_name' => $memberinfo['nickname'],
                'from_avatar' => $memberinfo['avatar'],
                'to_id' => intval($gid),
                'to_name' => $groupInfo['groupname'],
                'to_avatar' => $result,
                'data' =>'您被'.$info['nickname'].'踢出了群聊',
                'create_time' => time(),
                'chat_type' => 'group',
                'type' => 'system',
                'options' => [],
                'isremove' => 0,
                'sendStatus' => 'success'
            ];
            $arr = array('event' => 'message', 'data' => $data);
            sendUid($memberinfo['member_id'],$arr);
            $data = [
                'id' => uuid(),
                'from_id' => $info['ypid'],
                'from_name' => $info['nickname'],
                'from_avatar' => $info['avatar'],
                'to_id' => intval($gid),
                'to_name' => $groupInfo['groupname'],
                'to_avatar' => $result,
                'data' =>'您把'.$memberinfo['nickname'].'移出了群聊',
                'create_time' => time(),
                'chat_type' => 'group',
                'type' => 'system',
                'options' => [],
                'isremove' => 0,
                'sendStatus' => 'success'
            ];
            $arr = array('event' => 'message', 'data' => $data);
            sendUid($info['member_id'],$arr);
        }

        return $this->ajaxReturn($this->successCode, '返回成功');

    }

    /**
     * @api {get} /Groups/transferGroup 11、群转让
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  群转让
     * @apiParam (输入参数：) {string}            gid 群id
     * @apiParam (输入参数：) {string}            ypid
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据详情
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"返回成功","data":{"img":"https:\/\/api.aimichat.com\/uploads\/116_13219.png"}}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"没有数据"}
     */
    public function transferGroup(){
        $uid = $this->request->uid;
        $gid = $this->request->post('gid');
        $ypid = $this->request->post('ypid');
        $groupInfo = \xhadmin\db\Groups::getInfo($gid);
        if(empty($groupInfo)){
            return json(['status' => $this->errorCode, 'msg' => '该群聊不存在']);
        }

        if($groupInfo['uid']!=$uid){
            return json(['status' => $this->errorCode, 'msg' => '只有群主才可以转让']);
        }
        $where = array();
        $where['ypid'] = $ypid;
        $info = \app\api\model\Member::getWhereInfo($where);
        if(empty($info)){
            return json(['status' => $this->errorCode, 'msg' => '参数错误']);
        }
        if($info['member_id']==$uid)
        {
            return json(['status' => $this->errorCode, 'msg' => '自己无需转让自己的群']);
        }
        $where = array();
        $where['gid'] = $gid;
        $where['ypid'] = $ypid;
        $memberInfo = Groupmember::getWhereInfo($where);
        if(empty($memberInfo)){
            return json(['status' => $this->errorCode, 'msg' => '不是群成员不能转']);
        }
        $where = array();
        $where['gid'] = $gid;
        $data = array(
            'uid'=>$info['member_id'],
            'ypid'=>$info['ypid']
        );
        \xhadmin\db\Groups::editWhere($where,$data);
        $data = array(
            'isowner'=>0
        );
        $where['uid'] = $uid;

        Groupmember::editWhere($where,$data);
        $where['uid'] = $info['member_id'];
        $data = array(
            'isowner'=>2
        );
        Groupmember::editWhere($where,$data);
        return json(['status' => $this->successCode, 'msg' => '群转让成功']);
    }
    /**
     * @api {get} /Groups/addManager 12、添加管理员
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  群转让
     * @apiParam (输入参数：) {string}            gid 群id
     * @apiParam (输入参数：) {string}            ypids
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据详情
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"返回成功","data":{"img":"https:\/\/api.aimichat.com\/uploads\/116_13219.png"}}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"没有数据"}
     */
    public function addManager(){
        $uid = $this->request->uid;
        $gid = $this->request->post('gid');
        $ypids = $this->request->post('ypids');
        $groupInfo = \xhadmin\db\Groups::getInfo($gid);
        if(empty($groupInfo)){
            return json(['status' => $this->errorCode, 'msg' => '该群聊不存在']);
        }
        $ypids = explode(',',$ypids);
        if(count($ypids)>3){
            return json(['status' => $this->errorCode, 'msg' => '最多只能设置3个管理员哦']);
        }
        $where = array();
        $where['gid'] = $gid;
        $where['isowner'] = 1;
        $memberList = Groupmember::loadList($where,'0,3','ypid');
        $arr = array();
        foreach ($memberList as $key=>$v){
            $arr[] = $v['ypid'];
        }

        $arr = array_merge($ypids,$arr);
        $arr = array_unique($arr);
        if((count($arr))>3){
            return json(['status' => $this->errorCode, 'msg' => '最多只能设置3个管理员哦']);
        }
        foreach ($ypids as $key=>$v){
            $data = array(
                'isowner'=>1
            );
            $where = array();
            $where['ypid'] = $v;
            $where['gid'] = $gid;
            Groupmember::editWhere($where,$data);
        }
        return json(['status' => $this->successCode, 'msg' => '设置成功']);
    }

    /**
     * @api {get} /Groups/deleteManager 13、删除管理员
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  群转让
     * @apiParam (输入参数：) {string}            gid 群id
     * @apiParam (输入参数：) {string}            ypids
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据详情
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"返回成功","data":{"img":"https:\/\/api.aimichat.com\/uploads\/116_13219.png"}}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"没有数据"}
     */
    public function deleteManager(){
        $uid = $this->request->uid;
        $gid = $this->request->post('gid');
        $ypids = $this->request->post('ypids');
        $groupInfo = \xhadmin\db\Groups::getInfo($gid);
        if(empty($groupInfo)){
            return json(['status' => $this->errorCode, 'msg' => '该群聊不存在']);
        }
        $ypids = explode(',',$ypids);

       /* $where = array();
        $where['gid'] = $gid;
        $where['isowner'] = 1;
        $memberList = Groupmember::loadList($where,'0,3','ypid');*/

        foreach ($ypids as $key=>$v){
            $data = array(
                'isowner'=>0
            );
            $where = array();
            $where['ypid'] = $v;
            $where['gid'] = $gid;
            $where['isowner'] = 1;
            Groupmember::editWhere($where,$data);
        }
        return json(['status' => $this->successCode, 'msg' => '删除成功']);
    }

    /**
     * @api {get} /Groups/group_member_list 14、列出全部成员
     * @apiGroup Groups
     * @apiVersion 1.0.0
     * @apiDescription  列出全部成员
     * @apiParam (输入参数：) {string}            gid 群id
     * @apiParam (输入参数：) {string}            status 状态；1是管理员；0是全部成员
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据详情
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"返回成功","data":{"img":"https:\/\/api.aimichat.com\/uploads\/116_13219.png"}}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"没有数据"}
     */
    public function group_member_list(){
        $uid = $this->request->uid;
        $gid = $this->request->post('gid','0','intval');
        $status = $this->request->post('status','0','intval');
        if(empty($gid)){
            return json(['status' => $this->errorCode, 'msg' => '参数错误']);
        }
        $where = array();
        $where['a.gid'] = $gid;
        if($status==1||$status==2){
            $where['a.isowner'] = $status;
        }

        $lists = MemberGroup::relateQuery('a.*,b.nickname,b.avatar','uid','member','member_id',$where,'0,5000','isowner desc,addtime asc');
        return json(['status' => $this->successCode, 'msg' => '获取成功','data'=>$lists]);
    }





}

