<?php 
/*
 module:		会员聊天记录
 create_time:	2020-08-10 15:58:51
 author:		
 contact:		
*/

namespace app\api\controller;

use app\api\controller\Jwt;

class GetToken extends Common {


    function getToken(){
        $postField = 'token';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $token = $data['token'];
        if(!$token){
            return json(['status'=>config('my.errorCode'),'msg'=>'token不能为空']);
        }
        if(count(explode('.',$token)) <> 3){
            return json(['status'=>config('my.errorCode'),'msg'=>'token格式错误']);
        }
        $jwt = Jwt::getInstance();
        $jwt->setIss(config('my.jwt_iss'))->setAud(config('my.jwt_aud'))->setSecrect(config('my.jwt_secrect'))->setToken($token);

        if($jwt->decode()->getClaim('exp') < time()){
            return json(['status'=>config('my.jwtExpireCode'),'msg'=>'token过期']);
        }
        if($jwt->validate() && $jwt->verify()){
            $uid = $jwt->decode()->getClaim('uid');
            return json(['status'=>200,'uid'=>$uid]);

        }else{
            return json(['status'=>config('my.jwtErrorCode'),'msg'=>'token失效']);
        }
    }

    function test(){
        $uid = $this->request->get('uid');
        $postField = 'uid';
        $data = $this->request->only(explode(',', $postField), 'post', null);

        if(empty($uid)){
            $uid = $data['uid'];
        }
        $uid = empty($uid)?4:intval($uid);
        $res['token'] =$this->setToken($uid);
        print $res['token'];
    }

}

