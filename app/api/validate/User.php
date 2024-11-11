<?php 
/*
 module:		用户管理验证器
 create_time:	2020-07-26 17:45:17
 author:		
 contact:		
*/

namespace app\api\validate;
use think\validate;

class User extends validate {


	protected $rule = [
		'name'=>['require'],
		'user'=>['require'],
		'pwd'=>['require','regex'=>'/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,20}$/'],
		'mobile'=>['unique:user','regex'=>'/^1[3456789]\d{9}$/'],
	];

	protected $message = [
		'name.require'=>'真实姓名不能为空',
		'user.require'=>'用户名不能为空',
		'pwd.require'=>'密码不能为空',
		'pwd.regex'=>'6-21位数字字母组合',
		'mobile.unique'=>'手机号已经存在',
		'mobile.regex'=>'手机号格式错误',
	];

	protected $scene  = [
		'add'=>['name','user','pwd'],
		'update'=>['name','user'],
		'updatePassword'=>['pwd'],
	];



}

