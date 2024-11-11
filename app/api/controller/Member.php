<?php
/*
 module:		会员管理
 create_time:	2020-08-09 18:28:55
 author:		
 contact:		
*/

namespace app\api\controller;

use app\api\service\MemberFindMethodService;
use think\db\Where;
use think\facade\App;

require_once App::getRootPath() . 'extend/QRCode/phpqrcode.php';
use app\admin\model\MemberAds;
use app\admin\model\MemberLike;
use app\admin\model\MemberUnLike;
use app\admin\service\MemberAdsService;
use app\api\service\MemberService;
use app\api\model\Member as MemberModel;
use app\api\service\MemberTagService;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;
use xhadmin\db\Member as MemberDb;

class Member extends Common
{


    /**
     * @api {get} /Member/index 01、首页数据列表
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [membername] 会员名称
     * @apiParam (输入参数：) {int}            [sex] 性别 男|1|success,女|2|warning
     * @apiParam (输入参数：) {string}        [mobile] 手机号
     * @apiParam (输入参数：) {string}        [customerid] 客服id
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
    function index()
    {

    }

    /**
     * @api {post} /Member/add 02、添加
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            membername 会员名称
     * @apiParam (输入参数：) {string}            pwd 密码
     * @apiParam (输入参数：) {int}                sex 性别 男|1|success,女|2|warning
     * @apiParam (输入参数：) {string}            avatar 头像
     * @apiParam (输入参数：) {string}            cardid 身份证号
     * @apiParam (输入参数：) {string}            mobile 手机号
     * @apiParam (输入参数：) {int}                status 状态 开启|1,关闭|0
     * @apiParam (输入参数：) {string}            nickname 昵称
     * @apiParam (输入参数：) {string}            customerid 客服id
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
        $postField = 'membername,pwd,sex,avatar,cardid,mobile,status,nickname,customerid';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $membername = $data['membername'];
        if (empty($membername)) {
            return $this->ajaxReturn($this->errorCode, '用户名不能为空');
        }
        $where['membername'] = $membername;
        $info = \app\api\model\Member::getWhereInfo($where);
        if (!empty($info)) {
            return $this->ajaxReturn($this->errorCode, '重复插入');
        }
        $res = MemberService::add($data);
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Member/update 03、修改
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            avatar 头像
     * @apiParam (输入参数：) {string}            email 邮箱
     * @apiParam (输入参数：) {string}            nickname 昵称
     * @apiParam (输入参数：) {string}            birthday 生日
     * @apiParam (输入参数：) {string}            ads 地址
     * @apiParam (输入参数：) {string}            sex 性别
     * @apiParam (输入参数：) {string}            province 省
     * @apiParam (输入参数：) {string}            city 市
     * @apiParam (输入参数：) {string}            district 区
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
        $uid = $this->request->uid;

        $postField = 'nickname,exp,avatar,email,birthday,ads,sex,province,city,district';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($uid)) return json(['status' => $this->errorCode, 'msg' => '参数错误']);
        try {

            $where['member_id'] = $uid;
            $data['level'] = 1;
            $res = \app\api\model\Member::update($data, $where);

        } catch (\Exception $e) {
            return json(['status' => $this->errorCode, 'msg' => $e->getMessage()]);
        }
        return json(['status' => $this->successCode, 'msg' => '操作成功']);
    }

    /**
     * @api {post} /Member/updatePhone 03、修改手机号
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            mobile 手机号
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
    function updatePhone()
    {
        $uid = $this->request->uid;
        $postField = 'mobile';
        $data = $this->request->only(explode(',', $postField), 'post', null);

        if (empty($uid)) return json(['status' => $this->errorCode, 'msg' => '参数错误']);
        try {
            $where = array();
            $where['mobile'] = $data['mobile'];
            $info = \app\api\model\Member::getWhereInfo($where);
            if (empty($info)) {
                $where['member_id'] = $uid;
                $data['level'] = 1;
                $data['membername'] = $data['mobile'];
                $res = \app\api\model\Member::update($data, $where);
            } else {
                return json(['status' => $this->errorCode, 'msg' => '您的手机号已经被注册，请更换手机']);
            }


        } catch (\Exception $e) {
            return json(['status' => $this->errorCode, 'msg' => $e->getMessage()]);
        }

        return json(['status' => $this->successCode, 'msg' => '操作成功', 'avatar' => $data['avatar']]);
    }

    public function createavatar($string, $sex = 1, $path = '')
    {

        $avatar = createtext($string, $sex, 40, 100, $path);
        $url = \utils\oss\OssService::OssUpload(['tmp_name' => realpath($avatar), 'extension' => 'png']);

        return $url;


    }

    /**
     * @api {post} /Member/delete 04、删除
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            mobile 短信验证手机号
     * @apiParam (输入参数：) {string}            verify_id 短信验证ID
     * @apiParam (输入参数：) {string}            verify 短信验证码
     * @apiParam (输入参数：) {string}            member_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('member_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['member_id'] = explode(',', $idx);
        try {
            MemberModel::destroy($data);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {get} /Member/view 05、查看详情
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  查看详情 exp:标签（用逗号隔开）fanscount：粉丝数；followcount：关注数；article_count：文章数；day:注册天数;see_count:看过我
     * @apiParam (输入参数：) {string}            member_id 主键ID
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
        $uid = $this->request->uid;
        $ypid = $this->request->post('ypid');
        $field = 'cmoney,point,capacity,bg_img,email,birthday,see_count,level,exp,member_id as id,ypid,avatar,mobile,membername,sex,nickname,status,fanscount,followcount,article_count,regtime,notice_count,province,city,district';
        if (empty($ypid)) {
            $where = array();
            $where['member_id'] = $uid;
            $res = \app\api\model\Member::getWhereInfo($where, $field);
        } else {
            $where = array();
            $where['ypid'] = $ypid;
            $res = \app\api\model\Member::getWhereInfo($where, $field);
        }
        if (empty($res)) {
            return $this->ajaxReturn($this->errorCode, '参数错误');
        }
        $res['isowner'] = 1;
        if (!empty($ypid) && $uid != $res['id']) {

            $where = array();
            $where['member_id'] = $uid;
            $where['friend_id'] = $res['id'];

            $followInfo = \app\api\model\Follow::getWhereInfo($where);
            $where = array();
            $where['member_id'] = $res['id'];
            $where['friend_id'] = $uid;;
            $otherInfo = \app\api\model\Follow::getWhereInfo($where);
            $where = array();
            $where['uid'] = $uid;
            $where['friend_uid'] = $res['id'];
            $where['state'] = 1;
            $friendInfo = \app\api\model\Friend::getWhereInfo($where);

            if (!empty($friendInfo)) {
                //$res['nickname'] = empty($friendInfo['remark']) ? $res['nickname'] : $friendInfo['remark'];
                $res['remark'] = empty($friendInfo['remark'])?'':$friendInfo['remark'];
                $res['is_friend'] = 1;
                $res['no_see_him'] = $friendInfo['no_see_him'];
                $res['no_see_me'] = $friendInfo['no_see_me'];
                $res['blocked'] = $friendInfo['blocked'];
                $res['star'] = $friendInfo['star'];
            } else {
                $res['is_friend'] = 0;
                $res['no_see_him'] = 0;
                $res['no_see_me'] = 0;
                $res['blocked'] = 0;
                $res['star'] = 0;
            }
            if (empty($followInfo)) {
                $res['isgz'] = 0;
            } else {
                $res['isgz'] = 1;
            }
            if (empty($otherInfo)) {
                $res['isothergz'] = 0;
            } else {
                $res['isothergz'] = 1;
            }
            $res['isowner'] = 0;

        }

        if( $res['isowner'] ==1){

            $where = array();
            $where['member_id'] = $uid;
            $setting = \app\api\model\MemberFindMethod::getWhereInfo($where);
            if(empty($setting)){
                $data = array(
                    'member_id'=>$uid,
                    'ypid'=>$res['ypid']
                );

                \app\api\model\MemberFindMethod::create($data);
                $setting = \app\api\model\MemberFindMethod::getWhereInfo($where);
            }

            $res['setting'] = $setting;
        }

        $res['day'] = ceil((time() - $res['regtime']) / 86400);
        $where = array();
        $where['member_id'] = $res['isowner'] == 1 ? $uid : $res['id'];
        $tag = MemberTagService::indexList($where, '*', 'id desc', '20', 1);
        $tag = $tag['list'];
        $res['tag'] = $tag;
        $res = checkData($res);
        return $this->ajaxReturn($this->successCode, '返回成功', $res);
    }

    /**
     * @api {post} /Member/sendSms 07、发送手机验证码
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  发送短信验证码
     * @apiParam (输入参数：) {string}            mobile 手机号
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiParam (成功返回参数：) {string}        array.key 返回短信验证ID
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"操作失败"}
     */
    function sendSms()
    {
        $mobile = $this->request->post('mobile');
        if (empty($mobile)) throw new ValidateException ('手机号不能为空');
        try {
            $data['mobile'] = $mobile;    //发送手机号
            $data['code'] = '';
            while(strlen($data['code'])<4){
                $data['code'] = sprintf('%04d', rand(0, 9999));        //验证码
            }
            $res = \utils\sms\AliSmsService::sendSms($data);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        $key = md5(time() . $data['mobile']);
        cache($key, ['mobile' => $data['mobile'], 'code' => $data['code']], 0);
        return $this->reDecodejson(['status' => $this->successCode, 'msg' => '发送成功', 'key' => $key]);
    }

    function chatlist()
    {
        $data['member_id'] = $this->_data['uid'];    //token解码用户ID
        $page = $this->request->get('page', 1, 'intval');
        $list = MemberService::chatList1($data['member_id'], $page);
        return $this->reDecodejson(['status' => $this->successCode, 'data' => $list]);

    }

    function send()
    {

        $ufrom = $uid = $this->request->uid;

        $toid = $this->request->post('toid', '');
        $options = $this->request->post('options', '');

        if (empty($toid)) {
            return $this->reDecodejson(['status' => $this->errorCode, 'data' => '', 'msg' => '您传入的参数错误1']);
        }
        $content = trim($this->request->post('content', ''));
        if (is_null($content) || strlen($content) < 1) {
            return $this->reDecodejson(['status' => $this->errorCode, 'data' => '', 'msg' => '您传入的参数错误2']);
        }
        $type = $this->request->post('type', 'friend');
        if (!in_array($type, array('friend', 'group'))) {
            return $this->reDecodejson(['status' => $this->errorCode, 'data' => '', 'msg' => '您传入的参数错误3']);
        }
        $message_type = $this->request->post('message_type');
        $fromInfo = \app\api\model\Member::find($ufrom);
        if ($type == 'friend') {
            #群发&单发
            $field = 'member_id,nickname,avatar,state,ypid';
            $toid_arr = array_unique(array_filter(explode(',', $toid)));
            foreach ($toid_arr as $k => $v) {
                $where = array();
                $where['ypid'] = $v;
                $toInfo = checkData(\app\api\model\Member::getWhereInfo($where, $field));
                if (empty($toInfo)) {
                    return $this->reDecodejson(['status' => $this->errorCode, 'data' => '', 'msg' => '您传入的参数错误4']);
                }
                if ($fromInfo['member_id'] == $toInfo['member_id']) {
                    return $this->reDecodejson(['status' => $this->errorCode, 'data' => '', 'msg' => '自己不能和自己聊天']);
                }

                $result = MemberService::send($fromInfo, $toInfo, $message_type, $type, $content, $options);
                if (is_int($result)) {
                    $msg = '';
                    $errorConfig = array(
                        '1' => '参数错误',
                        '2' => '不是群组成员无法发言',
                        '3' => '用户已经被禁言',
                        '4' => '用户已经被禁言',
                        '5' => '系统设置了禁止群聊或者私聊',
                        '6' => '群被禁言',
                        '7' => '黑名单',
                        '8' => '只有互为好友才可以聊天',
                    );
                    if ($result > 0) {
                        $msg = $errorConfig[$result];
                    }

                }
            }
        } else {
            $where = array();
            $where['gid'] = $toid;
            $where['uid'] = $ufrom;
            $toInfo = \xhadmin\db\MemberGroup::getWhereInfo($where);

            if (empty($toInfo)) {
                return $this->reDecodejson(['status' => $this->successCode, 'data' => '2', 'msg' => '2']);
            }
            $nickname = $toInfo['remark'];
            $fromInfo['nickname'] = $nickname;
            $toInfo = \xhadmin\db\Groups::getInfo($toid);
            $result = MemberService::send($fromInfo, $toInfo, $message_type, $type, $content, $options);

            if (is_int($result)) {
                $msg = '';
                $errorConfig = array(
                    '1' => '参数错误',
                    '2' => '不是群组成员无法发言',
                    '3' => '用户已经被禁言',
                    '4' => '用户已经被禁言',
                    '5' => '系统设置了禁止群聊或者私聊',
                    '6' => '群被禁言',
                    '7' => '黑名单',
                    '8'=>'不是好友',
                    '9'=>'对方已把你删除，请重新认证好友'
                );
                if ($result > 0) {
                    $msg = $errorConfig[$result];
                }
                if ($result == '7') {
                    $result = uuid();
                }
            }
        }
        return $this->reDecodejson(['status' => $this->successCode, 'data' => $result, 'msg' => $result]);
    }

    /**
     * @api {post} /Member/updatepwd 08、修改密码
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  发送短信验证码
     * @apiParam (输入参数：) {string}            verify_id 短信验证ID
     * @apiParam (输入参数：) {string}            verify 短信验证码
     * @apiParam (输入参数：) {string}            repwd 新密码
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiParam (成功返回参数：) {string}        array.key 返回短信验证ID
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"操作失败"}
     */
    function updatePwd()
    {
        $repwd = $this->request->post('repwd', '');
        $opwd = $this->request->post('opwd', '');
        $data['member_id'] = $uid = $this->request->uid;
        if (empty($data['member_id'])) return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '参数错误']);

        if(empty($repwd)||strlen($repwd)<6){
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '密码不能小于6位']);
        }
        $data['repwd'] = $repwd;

        $where = array();
        $where['member_id'] = $uid;
        $res = \app\api\model\Member::getWhereInfo($where, 'level,member_id as id,avatar,mobile,membername,sex,nickname,status,pwd');
        if(empty($res)){
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '未找到用户信息']);
        }
        /*/
        if(!empty($res['pwd'])){
            $pwdd= md5($opwd . config('my.password_secrect'));
            if($pwdd!=$res['pwd']){
                return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '旧密码输入错误']);
            }
        }
        */
        try {
            $resb = MemberService::updatepwd($data);
        } catch (\Exception $e) {
            Log::error('错误：' . print_r($e->getMessage(), true));
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => $e->getMessage()]);
        }
        if ($resb) {
            $ret = ['status' => $this->successCode, 'msg' => '操作成功'];
        } else {
            $ret = ['status' => $this->errorCode, 'msg' => '操作失败'];
        }
        $res['token'] = $this->setToken($res['id']);
        $res['pwd'] = $pwdd= md5($repwd . config('my.password_secrect'));
        Log::info('接口返回：' . print_r($ret, true));
        return $this->reDecodejson(['status' => $this->successCode, 'data' => $res, 'token' => $res['token']]);
    }

    /**
     * @api {post} /Member/ads 09、获取地址
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  获取地址
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiParam (成功返回参数：) {string}        array.key 返回短信验证ID
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"操作失败"}
     */
    public function ads()
    {
        $data['member_id'] = $uid = $this->request->uid;
        $where = array();
        $where['member_id'] = $uid;
        $info = MemberAds::getWhereInfo($where);
        return $this->reDecodejson(['status' => $this->successCode, 'data' => $info, 'msg' => '获取成功']);
    }

    /**
     * @api {post} /Member/modifyads 10、更新地址
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  更新地址
     * @apiParam (输入参数：) {string}            realname 真实名称
     * @apiParam (输入参数：) {string}            mobile 手机号
     * @apiParam (输入参数：) {string}            province 省
     * @apiParam (输入参数：) {string}            city 市
     * @apiParam (输入参数：) {string}            district 区
     * @apiParam (输入参数：) {string}            ads 详细地址
     * @apiParam (输入参数：) {string}            exp 备注
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiParam (成功返回参数：) {string}        array.key 返回短信验证ID
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"操作失败"}
     */
    public function modifyads()
    {
        $uid = $this->request->uid;
        $postField = 'realname,mobile,city,province,district,ads,exp';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $where = array();
        $where['member_id'] = $uid;
        $info = MemberAds::getWhereInfo($where);
        $where = array();
        $where['member_id'] = $uid;
        if (empty($info)) {
            $data['member_id'] = $uid;
            $info = MemberAds::create($data);
        } else {
            $info = MemberAds::update($data, $where);
        }

        return $this->reDecodejson(['status' => $this->successCode, 'data' => $info, 'msg' => '修改成功']);
    }

    public function ringoff()
    {

    }




    public function star()
    {
        $uid = $this->request->uid;

        $info = \app\api\model\Member::find($uid);

        $where = array();
        $where[] = ['member_id', '<>', $uid];
        $res = MemberService::indexList($where, 'ypid,avatar,nickname,member_id,RAND() as r', 'r ', 50, 1);
        return success('操作成功', $res);
    }

    public function lists()
    {
        $ufrom = $uid = $this->request->uid;

        $info = \app\api\model\Member::find($uid);
        $sex = $info['sex'];

        $where = array();
        $where[] = ['sex', '=', ($info['sex'] == 1 ? 2 : 1)];
        $un_like_list = Db::name('member_unlike')->where("member_id", $uid)->select()->toArray();
        $like_list = Db::name('member_like')->where("member_id", $uid)->select()->toArray();
        if ($un_like_list || $like_list) {
            $un_like_ids = array_column($un_like_list, 'friend_id');
            $like_ids = array_column($like_list, 'friend_id');
            $ids = array_merge($un_like_ids, $like_ids);
            $where[] = ['member_id', 'not in', $ids];
        }
        $res = MemberService::indexList($where, 'ypid,avatar,nickname,member_id', 'regtime desc', 50, 1);
        return success('操作成功', $res);
    }

    /**
     * @api {post} /Member/set_member_like 101、喜欢
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  更新地址
     * @apiParam (输入参数：) {string}            member_id "lists"接口的member_id
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiParam (成功返回参数：) {string}        array.key 返回短信验证ID
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"操作失败"}
     */
    public function set_member_like()
    {
        $uid = $this->request->uid;
        $member_id = $this->request->post('member_id');

        $data = [
            'member_id' => $uid,
            'friend_id' => $member_id,
        ];

        $result = true;
        if (!Db::name('member_like')->where($data)->find()) {
            $data['addtime'] = time();
            $result = MemberLike::create($data);
        }
        return $this->ajaxReturn($this->successCode, '返回成功', $result);
    }

    /**
     * @api {post} /Member/set_member_un_like 102、不喜欢
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  更新地址
     * @apiParam (输入参数：) {string}            member_id "lists"接口的member_id
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiParam (成功返回参数：) {string}        array.key 返回短信验证ID
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"操作失败"}
     */
    public function set_member_un_like()
    {
        $uid = $this->request->uid;
        $member_id = $this->request->post('member_id');
        $data = [
            'member_id' => $uid,
            'friend_id' => $member_id,
        ];

        $result = true;
        if (!Db::name('member_unlike')->where($data)->find()) {
            $data['addtime'] = time();
            $result = MemberUnLike::create($data);
        }
        return $this->ajaxReturn($this->successCode, '返回成功', $result);
    }

    /**
     * @api {post} /Member/save_bg_img 100、更新背景墙
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  更新地址
     * @apiParam (输入参数：) {string}            img 背景图片
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiParam (成功返回参数：) {string}        array.key 返回短信验证ID
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"操作失败"}
     */
    public function save_bg_img()
    {
        $uid = $this->request->uid;
        $info = \app\api\model\Member::find($uid);
        if (!$info) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '不存在该会员']);
        }
        $img = $this->request->post('img');
        $result = \app\api\model\Member::update(['bg_img' => $img], [['member_id', '=', $uid]]);
        if ($result !== false) {
            return $this->ajaxReturn($this->successCode, '操作成功');
        } else {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '操作失败']);
        }
    }


    public function save_avatar_img()
    {
        $uid = $this->request->uid;
        $info = \app\api\model\Member::find($uid);
        if (!$info) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '不存在该会员']);
        }
        $img = $this->request->post('img');
        $result = \app\api\model\Member::update(['avatar' => $img], [['member_id', '=', $uid]]);
        if ($result !== false) {
            return $this->ajaxReturn($this->successCode, '操作成功');
        } else {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '操作失败']);
        }
    }

    /**
     * @api {get} /Member/create_qrcode 100、个人二维码
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  查看数据
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
        $a = \xhadmin\db\Member::getInfo($uid);
        if (!$a) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '不存在该成员']);
        }
        $base_domain = config('my.home_domain');

        $base_url = 'uploads/';
        $file = $base_url . "member_" . $uid . '.png';
        unlink($file);
        $data = [
            'event' => 'friend',
            'uid' => $uid,
            'ypid'=>$a['ypid']
        ];
        $value = json_encode($data, true); //二维码内容
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 6;//生成图片大小
        //生成二维码图片
        \QRcode::png($value, $file);
        $logo = $a['avatar'] ? $a['avatar'] : FALSE;//准备好的logo图片
        $QR = $base_domain . $file;//已经生成的原始二维码图
        header("Content-type: image/jpg");

        if ($logo !== FALSE) {
            $QR = imagecreatefromstring(file_get_contents($QR));
            $logo = imagecreatefromstring(file_get_contents($logo));

            $QR_width = imagesx($QR);//二维码图片宽度
            $QR_height = imagesy($QR);//二维码图片高度
            $logo_width = imagesx($logo);//logo图片宽度
            $logo_height = imagesy($logo);//logo图片高度
            #logo修改成正方形
            $l_w = $logo_width > $logo_height ? $logo_height : $logo_width; #取最短
            $logo_qr_width = $QR_width / 5;
            $scale = $logo_width / $logo_qr_width;
            $logo_qr_height = $logo_height / $scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            //重新组合图片并调整大小
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
                $logo_qr_height, $l_w, $l_w);
        }

        //输出图片
        imagepng($QR, $file);
        $data = [
            'img' => $base_domain . $file.'?t='.time(),
        ];
        return $this->ajaxReturn($this->successCode, '返回成功', $data);
    }

    public function exchange(){
        $uid = $this->request->uid;    //token解码用户ID
        $sql = 'update cd_member set cmoney=cmoney+point,point=0 where member_id='.$uid;
        $res= Db::execute($sql);
        return $this->ajaxReturn($this->successCode, '返回成功', $res);
    }


}

