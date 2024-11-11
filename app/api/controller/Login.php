<?php
/*
 module:		会员管理
 create_time:	2020-08-09 11:44:15
 author:		
 contact:		
*/

namespace app\api\controller;

use app\admin\model\Tradegroup;
use app\api\service\MemberService;
use app\api\model\Member as MemberModel;
use AppleSignIn\ASDecoder;
use phpmailer\PHPMailer;
use think\exception\ValidateException;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Log;

class Login extends Common
{
    protected $returnField = 'email,level,member_id as id,avatar,mobile,membername,sex,nickname,status,pwd';

    /**
     * @api {post} /Login/sendSms 01、发送手机验证码
     * @apiGroup Login
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
     * {
     * "status": "200",
     * "msg": "发送成功",
     * "key": "f267bc2f3cbccb6a22c9b6171229b548"
     * }
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"操作失败"}
     */
    function sendSms()
    {

        $mobile = $this->request->post('mobile');
        if (empty($mobile)) throw new ValidateException ('手机号不能为空');
        $key = md5(time() . $data['mobile']);
        $data['code'] = sprintf('%04d', rand(0, 9999));        //验证码
        $dataadd = [];
        $dataadd['mobile'] = $mobile;
        $dataadd['verify'] = $data['code'];
        $dataadd['verify_id'] = $key;
        $dataadd['type'] = 'reg';
        $dataadd['dateline'] = time();
        Db::name('member_yzm')->insertGetId($dataadd);
        try {
            $data['mobile'] = $mobile;    //发送手机号
            $type = isMobileOrEmail($mobile);
            if($type==1){
                $res = \utils\sms\AliSmsService::sendSms($data);
                if(!$res){
                    return $this->reDecodejson(['status' => 201, 'msg' => '发送失败，请检查您的手机号是否正确']);
                }
            }
            if($type==2){
                $content='【币易交易辅助】您的验证码为:'.$data['code']."，您正在进行身份验证，打死不要告诉别人哦！";
                $res = \phpmailer\Email::send($data['mobile'],'验证码信息',$content);
                if(!$res){
                    return $this->reDecodejson(['status' => 201, 'msg' => '发送失败，请检查您的邮箱是否正确']);
                }

            }
            
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        
        cache($key, ['mobile' => $data['mobile'], 'code' => $data['code']], 60);
        return $this->reDecodejson(['status' => $this->successCode, 'msg' => '发送成功', 'key' => $key]);
    }


    /**
     * @api {post} /Login/pwd 03、账号密码登入

     */
    function pwd()
    {
        $postField = 'membername,password';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['membername']) || empty($data['password'])) return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '账号或者密码不能为空']);
        $data['logintype'] = 'mobilepwd';
        if(!preg_match("/^1[3456789]\d{9}$/", $data['membername'])){
            if (filter_var($data['membername'], FILTER_VALIDATE_EMAIL))
            {
                $data['membername'] = strtolower($data['membername']);
                $data['logintype'] = 'email';
            }else{
                return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '您好，请输入正确的手机号或EMAIL地址']);
            }
        }

        try {
            $res = MemberService::login($data, $this->returnField);
            if (empty($res)) {
                return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '账号或者密码不正确']);
            }else{
                if($res['status']!=1){
                    return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'账号已被禁用，请联系管理员']);
                }
            }
            if(empty($res['mobile'])){
                $res['mobile'] = $res['email'];
            }
            $res['token'] = $this->setToken($res['id']);
        } catch (\Exception $e) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => $e->getMessage()]);
        }
        return $this->reDecodejson(['status' => $this->successCode, 'data' => $res, 'token' => $res['token']]);
    }



    /**
     * @api {post} /Login/phone 05、短信验证码登入

     */
    function phone()
    {
        $mobile = $this->request->post('mobile', '', null);

        if (empty($mobile)) return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '手机号不能为空']);
        try {
            $data = array('membername' => $mobile, 'mobile' => $mobile, 'logintype' => 'phone');

            $res = MemberService::login($data, $this->returnField);
            if ($res['isreg'] == 0) {
                return $this->reDecodejson(['status' => $this->noReg, 'msg' => '您还未注册，请先注册后再使用本系统']);
            }
            if ($res['status'] != 1) {
                return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'账号已被禁用，请联系管理员']);
            }
            if(empty($res['mobile'])){
                $res['mobile'] = $res['email'];
            }
        } catch (\Exception $e) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => $e->getMessage()]);
        }


        Log::info('接口返回：' . print_r($res, true));
        $token = $this->setToken($res['id']);

        $res['token'] = $token;
        return $this->reDecodejson(['status' => $this->successCode, 'data' => $res]);
    }

}

