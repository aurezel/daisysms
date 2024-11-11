<?php 
/*
 module:		用户管理
 create_time:	2020-07-26 17:45:17
 author:		
 contact:		
*/

namespace app\api\service;
use app\api\model\User;
use think\facade\Log;
use think\exception\ValidateException;
use xhadmin\CommonService;

class UserService extends CommonService {


	/*
 	* @Description  添加账户
 	*/
	public static function add($data){
		try{
			validate(\app\api\validate\User::class)->scene('add')->check($data);
			$data['pwd'] = md5($data['pwd'].config('my.password_secrect'));
			$data['create_time'] = time();
			$data['isguaqi'] = !is_null($data['isguaqi']) ? $data['isguaqi'] : '1';
			$res = User::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res->user_id;
	}


	/*
 	* @Description  修改账户
 	*/
	public static function update($where,$data){
		try{
			validate(\app\api\validate\User::class)->scene('update')->check($data);
			!is_null($data['create_time']) && $data['create_time'] = strtotime($data['create_time']);
			$res = User::where($where)->update($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res;
	}


	/*
 	* @Description  修改密码
 	*/
	public static function updatePassword($where,$data){
		try{
			validate(\app\api\validate\User::class)->scene('updatePassword')->check($data);
			$res = User::where($where)->update(['pwd'=>md5($data['pwd'].config('my.password_secrect'))]);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res;
	}


	/**start
 	* @Description  账号密码登录
 	*/
	public static function loginpwd($data,$returnField){
		$where['user'] = $data['name'];

		$where['pwd'] = md5($data['pwd'].config('my.password_secrect'));

		try{
			$res = User::field($returnField)->where($where)->find();
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		if(!$res){
			throw new ValidateException('请检查用户名或者密码');
		}
		return checkData($res,false);
	}
    /**end
     */

	/*
 	* @Description  手机号登录
 	*/
	public static function phonelogin($mobile,$returnField){
		try{
			$where['mobile'] = $mobile;
			$res = User::field($returnField)->where($where)->find();
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		if(!$res){
			throw new ValidateException('请检查手机号');
		}
		return checkData($res,false);
	}




}

