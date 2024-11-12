<?php 
/*
 module:		渠道管理验证器
 create_time:	2024-07-05 16:19:50
 author:		
 contact:		
*/

namespace app\admin\validate;
use think\validate;

class Agent extends validate {


	protected $rule = [
		'channel'=>['unique:agent'],
		'passwd'=>['require'],
	];

	protected $message = [
		'channel.unique'=>'渠道标识已经存在',
		'passwd.require'=>'登陆密码不能为空',
	];

	protected $scene  = [
		'add'=>['channel','passwd'],
		'update'=>['channel'],
	];



}

