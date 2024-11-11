<?php 
/*
 module:		客服成员管理验证器
 create_time:	2020-07-25 19:46:54
 author:		
 contact:		
*/

namespace app\api\validate;
use think\validate;

class Customer extends validate {


	protected $rule = [
		'memberid'=>['unique:customer'],
	];

	protected $message = [
		'memberid.unique'=>'会员编号已经存在',
	];

	protected $scene  = [
		'add'=>['memberid'],
		'update'=>['memberid'],
	];



}

