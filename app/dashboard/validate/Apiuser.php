<?php 
/*
 module:		接口用户列表验证器
 create_time:	2023-03-15 16:16:42
 author:		
 contact:		
*/

namespace app\admin\validate;
use think\validate;

class Apiuser extends validate {


	protected $rule = [
		'userid'=>['require'],
	];

	protected $message = [
		'userid.require'=>'会员ID不能为空',
	];

	protected $scene  = [
		'add'=>['userid'],
		'update'=>['userid'],
	];



}

