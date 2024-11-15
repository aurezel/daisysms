<?php

namespace app\api\middleware;
use app\api\controller\Jwt;


class JwtAuth
{
	
    public function handle($request, \Closure $next)
    {	
		$token = $request->header('Authorization');
        $debug = $request->param('debug');
		if(!$token){
			return reDecodejson(['status'=>config('my.errorCode'),'msg'=>'token不能为空1'],$debug);
		}
		if(count(explode('.',$token)) <> 3){
			return reDecodejson(['status'=>config('my.errorCode'),'msg'=>'token格式错误'],$debug);
		}
		$jwt = Jwt::getInstance();
		$jwt->setIss(config('my.jwt_iss'))->setAud(config('my.jwt_aud'))->setSecrect(config('my.jwt_secrect'))->setToken($token);

		if($jwt->decode()->getClaim('exp') < time()){
			return reDecodejson(['status'=>config('my.jwtExpireCode'),'msg'=>'token过期'],$debug);
		}
		if($jwt->validate() && $jwt->verify()){
			$request->uid = $jwt->decode()->getClaim('uid');
			return $next($request);
		}else{
			return reDecodejson(['status'=>config('my.jwtErrorCode'),'msg'=>'token失效'],$debug);
		}
    }
} 