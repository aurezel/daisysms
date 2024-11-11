<?php 
/*
 module:		用户管理验证器
 create_time:	2020-07-26 17:44:02
 author:		
 contact:		
*/

namespace app\admin\validate;
use think\validate;

class User extends validate {


	protected $rule = [
		'name'=>['require'],
		'user'=>['require'],
		'mobile'=>['unique:user','regex'=>'/^1[3456789]\d{9}$/'],
		'pwd'=>['require','regex'=>'/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,20}$/'],
	];

	protected $message = [
		'name.require'=>'真实姓名不能为空',
		'user.require'=>'用户名不能为空',
		'mobile.unique'=>'手机号已经存在',
		'mobile.regex'=>'手机号格式错误',
		'pwd.require'=>'密码不能为空',
		'pwd.regex'=>'6-21位数字字母组合',
	];

	protected $scene  = [
		'add'=>['name','user','pwd','mobile'],
		'update'=>['name','user','mobile'],
		'updatePassword'=>['pwd'],
	];



}

