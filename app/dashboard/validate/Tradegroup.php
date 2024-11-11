<?php 
/*
 module:		交易会员组验证器
 create_time:	2023-07-28 14:45:18
 author:		
 contact:		
*/

namespace app\admin\validate;
use think\validate;

class Tradegroup extends validate {


	protected $rule = [
		'gname'=>['require'],
	];

	protected $message = [
		'gname.require'=>'会员组名不能为空',
	];

	protected $scene  = [
		'add'=>['gname'],
		'update'=>['gname'],
	];



}

