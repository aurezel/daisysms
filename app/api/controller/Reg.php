<?php
/*
 module:		会员管理
 create_time:	2020-08-09 11:44:15
 author:		
 contact:		
*/

namespace app\api\controller;

use app\api\service\CavenService;
use app\api\service\MemberService;
use app\api\model\Member as MemberModel;
use AppleSignIn\ASDecoder;
use think\exception\ValidateException;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Log;
use app\api\service\ActiveService;

class Reg extends Common
{
    protected $returnField = 'level,email,member_id as id,avatar,mobile,membername,sex,nickname,status';

    /**
     * @api {post} /Reg/index 01、用户注册接口
     * @apiGroup Reg
     * @apiVersion 1.0.0
     * @apiDescription  用户注册
     * @apiParam (输入参数：) {string}            mobile 手机号
     * @apiParam (输入参数：) {string}            nickname 昵称
     * @apiParam (输入参数：) {string}            avatar 头像
     * @apiParam (输入参数：) {string}            sex 性别
     * @apiParam (输入参数：) {string}            pwd 密码
     * @apiParam (输入参数：) {string}            birthday 年龄
     * @apiParam (输入参数：) {string}            regtype 注册类型 phone:手机注册，weixin:微信注册，iphone：苹果注册，
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {
     * "status": "200",
     * "msg": "恭喜您注册成功"
     * }
     * @apiErrorExample {json} 02 失败示例
     * {
     * "status": "201",
     * "msg": "您要注册的手机号已经被注册"
     * }
     */
    function index()
    {
        $postField = 'mobile,pwd,reuid,channel,email';
        $data = $this->request->only(explode(',', $postField), 'post', null);

        $retype = 1;
        if(!preg_match("/^1[3456789]\d{9}$/", $data['mobile'])){
            if (filter_var($data['mobile'], FILTER_VALIDATE_EMAIL))
            {
                $retype = 2;
                $data['email'] = strtolower($data['mobile']);
                $data['nickname'] = strtolower($data['mobile']);
                unset($data['mobile']);
            }else{
                return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '您好，请输入正确的手机号或EMAIL地址']);
            }
        }

        if (empty($data['pwd'])) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '密码必须填写']);
        }

        if (_checkPwLevel($data['pwd']) == 3) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '您好，您设置的密码过于简单']);
        }
        if(is_numeric($data['channel'])){

            $data['reuid'] = $data['channel'];
            unset($data['channel']);
        }

        if((empty($data['reuid'])||intval($data['reuid'])<0)&&empty($data['channel'])){
            return $this->reDecodejson(['status' => 103, 'msg' => '推荐人ID或渠道号填写错误']);

        }else{
            $data['channel'] = empty($data['channel'])?'':$data['channel'];
            $data['groupid'] = 1;

            if(!empty($data['reuid'])) {
                $whereb=[];
                $whereb['member_id'] = intval($data['reuid']);
                $info = \app\api\model\Member::getWhereInfo($whereb);
                if (!empty($info)) {
                    $data['parentid'] = intval($data['reuid']);
                    $data['channel'] = $info['channel'];
                    //$data['channel'] = empty($data['channel']) ? 'sysdl' : $data['channel'];
                    //取得上级ID
                    $parentidinfo = CavenService::getTopuser($data['reuid'], 0);
                    if (!empty($parentidinfo) && isset($parentidinfo['member_id'])) {
                        $data['parentid'] = $parentidinfo['member_id'];
                        $data['twoid'] = intval($parentidinfo['parentid']);
                        $data['threeid'] = intval($parentidinfo['twoid']);
                    }

                } else {
                    return $this->reDecodejson(['status' => 103, 'msg' => '推荐人ID填写错误2']);
                }
            }
            unset($data['reuid']);
        }
        $data['channel'] = strtolower($data['channel']);
        if(empty($data['channel'])){
            return $this->reDecodejson(['status' => 103, 'msg' => '推荐人ID或渠道号填写错误']);
        }
        //查找推荐人的代理信息
        $aginfo = CavenService::getAgentinfo($data['channel']);

        if($aginfo){
            $data['groupid'] = $aginfo['groupid'];
        }else{
            return $this->reDecodejson(['status' => 103, 'msg' => '推荐人ID或渠道号填写错误']);
            /*
            $aginfo = CavenService::getAgentinfo('sysdl');
            if($aginfo){
                $data['groupid'] = $aginfo['groupid'];
            }
            $data['channel'] = 'sysdl';
            */
        }
        $data['pwd'] = md5($data['pwd'] . config('my.password_secrect'));
        $data['regtime'] = time();
        $data['regtype'] = 'phone';
        //$data['nickname'] = $data['mobile'];
        try {
            $data['level'] = 1;
            $where = array();
            if($retype==1){
                $where['mobile'] = $data['mobile'];
                $data['nickname'] = $data['mobile'];
            }else{
                $data['email'] = strtolower($data['email']);
                $where['email'] = $data['email'];
                $data['nickname'] = $data['email'];
                $data['regtype'] = 'email';
            }

            $info = \app\api\model\Member::getWhereInfo($where);
            if (!empty($info)) {
                $msg = $retype==1?'您要注册的手机号已经被注册':'您要注册的Email已经被注册';
                return $this->reDecodejson(['status' => $this->errorCode, 'msg' => $msg]);
            }

            $data['membername'] = $data['mobile'];
            $data['status'] = 1;
            $data['utype'] = 1;


            $res = \app\api\model\Member::create($data);
            $res['id'] = $res['member_id'];
            $res['token'] = $this->setToken($res['member_id']);

            //加入活动代码
            $orderinfo=[];
            $orderinfo['member_id']=$res['member_id'];
            $orderinfo['domember_id']=$res['member_id'];
            $orderinfo['channel']=$data['channel'];
            ActiveService::doActive($orderinfo,1);
            if(isset($data['parentid'])&&!empty($data['parentid'])){
                $orderinfo=[];
                $orderinfo['member_id']=$data['parentid'];
                $orderinfo['domember_id']=$res['member_id'];
                $orderinfo['channel']=$data['channel'];
                ActiveService::doActive($orderinfo,2);
            }

            if ($res) {
                $wapurl =  getSetConfig('appdownurl');
                return $this->reDecodejson(['status' => $this->successCode, 'msg' => '恭喜您注册成功','downurl'=>$wapurl, 'data' => $res]);
            }
        } catch (\Exception $e) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => $e->getMessage()]);
        }

    }


    function email()
    {
        $postField = 'mobile,pwd,reuid,channel,email';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $retype = 1;
        if(!preg_match("/^1[34578]\d{9}$/", $data['mobile'])){
            if (filter_var($data['mobile'], FILTER_VALIDATE_EMAIL))
            {
                $retype = 2;
                $data['email'] = strtolower($data['mobile']);
                unset($data['mobile']);
            }else{
                return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '您好，请输入正确的手机号或EMAIL地址']);
            }
        }

        if (empty($data['pwd'])) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '密码必须填写']);
        }

        if (_checkPwLevel($data['pwd']) == 3) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '您好，您设置的密码过于简单']);
        }
        if(is_numeric($data['channel'])){

            $data['reuid'] = $data['channel'];
            unset($data['channel']);
        }
        if((empty($data['reuid'])||intval($data['reuid'])&&empty($data['channel']))<0){
            return $this->reDecodejson(['status' => 103, 'msg' => '推荐人ID填写错误']);

        }else{
            $data['channel'] = empty($data['channel'])?'sysdl':$data['channel'];

            $data['groupid'] = 1;
            if(!empty($data['reuid'])) {
                $whereb=[];
                $whereb['member_id'] = intval($data['reuid']);
                $info = \app\api\model\Member::getWhereInfo($whereb);
                if (!empty($info)) {
                    $data['parentid'] = intval($data['reuid']);
                    $data['channel'] = $info['channel'];
                    $data['channel'] = empty($data['channel']) ? 'sysdl' : $data['channel'];
                    //取得上级ID
                    $parentidinfo = CavenService::getTopuser($data['reuid'], 0);
                    if (!empty($parentidinfo) && isset($parentidinfo['member_id'])) {
                        $data['parentid'] = $parentidinfo['member_id'];
                        $data['twoid'] = intval($parentidinfo['parentid']);
                        $data['threeid'] = intval($parentidinfo['twoid']);
                    }

                } else {
                    return $this->reDecodejson(['status' => 103, 'msg' => '推荐人ID填写错误']);
                }
            }
            unset($data['reuid']);
        }
        $data['channel'] = strtolower($data['channel']);
        //查找推荐人的代理信息
        $aginfo = CavenService::getAgentinfo($data['channel']);
        if($aginfo){
            $data['groupid'] = $aginfo['groupid'];
        }else{
            return $this->reDecodejson(['status' => 103, 'msg' => '推荐人ID或渠道号填写错误']);
            $aginfo = CavenService::getAgentinfo('sysdl');
            if($aginfo){
                $data['groupid'] = $aginfo['groupid'];
            }
            $data['channel'] = 'sysdl';
        }

        $data['pwd'] = md5($data['pwd'] . config('my.password_secrect'));
        $data['regtime'] = time();
        $data['regtype'] = 'phone';
        $data['nickname'] = $data['mobile'];
        try {
            $data['level'] = 1;
            $where = array();
            if($retype==1){
                $where['mobile'] = $data['mobile'];
            }else{
                $where['email'] = $data['email'];
                $data['regtype'] = 'email';
            }

            $info = \app\api\model\Member::getWhereInfo($where);
            if (!empty($info)) {
                $msg = $retype==1?'您要注册的手机号已经被注册':'您要注册的Email已经被注册';
                return $this->reDecodejson(['status' => $this->errorCode, 'msg' => $msg]);
            }
            $data['nickname'] = $data['mobile'];
            $data['membername'] = $data['mobile'];
            $data['status'] = 1;
            $data['utype'] = 1;


            $res = \app\api\model\Member::create($data);
            $res['id'] = $res['member_id'];
            $res['token'] = $this->setToken($res['member_id']);

            //加入活动代码
            $orderinfo=[];
            $orderinfo['member_id']=$res['member_id'];
            $orderinfo['domember_id']=$res['member_id'];
            $orderinfo['channel']=$data['channel'];
            ActiveService::doActive($orderinfo,1);
            if(isset($data['parentid'])&&!empty($data['parentid'])){
                $orderinfo=[];
                $orderinfo['member_id']=$data['parentid'];
                $orderinfo['domember_id']=$res['member_id'];
                $orderinfo['channel']=$data['channel'];
                ActiveService::doActive($orderinfo,2);
            }
            if ($res) {
                $wapurl =  getSetConfig('appdownurl');
                return $this->reDecodejson(['status' => $this->successCode, 'msg' => '恭喜您注册成功','downurl'=>$wapurl, 'data' => $res]);
            }
        } catch (\Exception $e) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => $e->getMessage()]);
        }

    }

    public function createavatar($sex)
    {
        if ($sex == 1) {
            //男；
            return '';
        } else {
            return '';
        }
    }

    public function getdownurl(){
        $postField = 'reuid';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $reuid =  $this->request->post('reuid');
        $whereb=[];
        if(empty($reuid)){
            $channel = 'sysdl';
        }else{
            $whereb['member_id'] = intval($data['reuid']);
            $info = \app\api\model\Member::getWhereInfo($whereb);
            if (!empty($info)) {
                $channel = $info['channel'];
            }
        }

        $channel = empty($channel)?'sysdl':$channel;
        $field = '*';
        $where['channel']=$channel;
        $res = \app\api\model\Chatsetting::getWhereInfo($where, $field);
        if(empty($res)){

        }
        //ios
        $appurl = $res['ioslink'];
        $androurl = $res['androidlink'];
        return $this->reDecodejson(['status' => '200', 'msg' => '恭喜您注册成功','iosurl'=>$appurl,'androurl'=>$androurl]);
    }
    
    function RegApi()
    {
        $postField = 'mobile,cuid,sign,time,channel';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        
        
        
        //$data['channel'] = 'okx';
        $retype = 1;
        if (empty($data['cuid'])||empty($data['mobile'])||empty($data['time'])) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '接口用户ID，用户名，或时间不能为空'],1);
        }
        if(!preg_match("/^1[3456789]\d{9}$/", $data['mobile'])){
            if (filter_var($data['mobile'], FILTER_VALIDATE_EMAIL))
            {
                $retype = 2;
                $data['email'] = strtolower($data['mobile']);
                $data['nickname'] = strtolower($data['mobile']);
                unset($data['mobile']);
            }else{
                return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '您好，请输入正确的手机号或EMAIL地址'],1);
            }
        }
        
        $key='lxf20220324';
        $sign =  md5($key.$data['time'].$data['mobile'].$data['cuid'].$data['channel']);
        if($sign!=$data['sign']){
          // return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '签名错误'],1); 
        }
        $data['pwd'] = 'a123456';
        if (empty($data['pwd'])) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '密码必须填写'],1);
        }

        if (_checkPwLevel($data['pwd']) == 3) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '您好，您设置的密码过于简单'],1);
        }
        $data['reuid'] = 0;

        if(empty($data['channel'])){
            return $this->reDecodejson(['status' => 103, 'msg' => '推荐人ID或渠道号填写错误'],1);

        }else{
            $data['channel'] = empty($data['channel'])?'':$data['channel'];
            $data['groupid'] = 1;

            if(!empty($data['reuid'])) {
                $whereb=[];
                $whereb['member_id'] = intval($data['reuid']);
                $info = \app\api\model\Member::getWhereInfo($whereb);
                if (!empty($info)) {
                    $data['parentid'] = intval($data['reuid']);
                    $data['channel'] = $info['channel'];
                    //$data['channel'] = empty($data['channel']) ? 'sysdl' : $data['channel'];
                    //取得上级ID
                    $parentidinfo = CavenService::getTopuser($data['reuid'], 0);
                    if (!empty($parentidinfo) && isset($parentidinfo['member_id'])) {
                        $data['parentid'] = $parentidinfo['member_id'];
                        $data['twoid'] = intval($parentidinfo['parentid']);
                        $data['threeid'] = intval($parentidinfo['twoid']);
                    }

                } else {
                    return $this->reDecodejson(['status' => 103, 'msg' => '推荐人ID填写错误2'],1);
                }
            }
            unset($data['reuid']);
        }
        $data['channel'] = strtolower($data['channel']);
        if(empty($data['channel'])){
            return $this->reDecodejson(['status' => 103, 'msg' => '推荐人ID或渠道号填写错误'],1);
        }
        //查找推荐人的代理信息
        $aginfo = CavenService::getAgentinfo($data['channel']);

        if($aginfo){
            $data['groupid'] = $aginfo['groupid'];
        }else{
            return $this->reDecodejson(['status' => 103, 'msg' => '推荐人ID或渠道号填写错误'],1);
            /*
            $aginfo = CavenService::getAgentinfo('sysdl');
            if($aginfo){
                $data['groupid'] = $aginfo['groupid'];
            }
            $data['channel'] = 'sysdl';
            */
        }
        $data['pwd'] = md5($data['pwd'] . config('my.password_secrect'));
        $data['regtime'] = time();
        $data['regtype'] = 'phone';
        //$data['nickname'] = $data['mobile'];
        try {
            $data['level'] = 1;
            
        //先查询返回的用户ID是否同步过
            $where =array();
            $data['cuid'] = intval($data['cuid']);
            $where['cuid'] = $data['cuid'];
            $info = \app\api\model\Member::getWhereInfo($where);
            if(!empty($info)){
                $msg = '注册成功!';
                return $this->reDecodejson(['status' => $this->successCode, 'msg' => $msg,'data'=>$info['member_id']],1);
            }
            
            $where = array();
            if($retype==1){
                $where['mobile'] = $data['mobile'];
                $data['nickname'] = $data['mobile'];
            }else{
                $data['email'] = strtolower($data['email']);
                $where['email'] = $data['email'];
                $data['nickname'] = $data['email'];
                $data['regtype'] = 'email';
            }

            $info = \app\api\model\Member::getWhereInfo($where);
            if (!empty($info)) {
                $msg = '账号已存在，同步成功!';
                if(empty($info['cuid'])){
                    $sql = "UPDATE `cd_member` SET cuid=".$data['cuid']." WHERE member_id=".$info['member_id'];
                    Db::query($sql);
                }
                return $this->reDecodejson(['status' => $this->successCode, 'msg' => $msg,'data'=>$info['member_id']],1);
            }

            $data['membername'] = $data['mobile'];
            $data['status'] = 1;
            $data['utype'] = 1;
            

            $res = \app\api\model\Member::create($data);
            $res['id'] = $res['member_id'];
            $res['token'] = $this->setToken($res['member_id']);

            //加入活动代码
            $orderinfo=[];
            $orderinfo['member_id']=$res['member_id'];
            $orderinfo['domember_id']=$res['member_id'];
            $orderinfo['channel']=$data['channel'];
            ActiveService::doActive($orderinfo,1);
            if(isset($data['parentid'])&&!empty($data['parentid'])){
                $orderinfo=[];
                $orderinfo['member_id']=$data['parentid'];
                $orderinfo['domember_id']=$res['member_id'];
                $orderinfo['channel']=$data['channel'];
                ActiveService::doActive($orderinfo,2);
            }

            if ($res) {
                $wapurl =  getSetConfig('appdownurl');
                return $this->reDecodejson(['status' => $this->successCode, 'msg' => '恭喜您注册成功', 'data' => $res['member_id']],1);
            }
        } catch (\Exception $e) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => $e->getMessage()],1);
        }

    }

}

