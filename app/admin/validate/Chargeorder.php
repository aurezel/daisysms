<?php 
/*
 module:		充值订单验证器
 create_time:	2023-07-27 23:47:31
 author:		
 contact:		
*/

namespace app\admin\validate;
use think\validate;

class Chargeorder extends validate {


	protected $rule = [
		'tradeno'=>['unique:chargeorder'],
	];

	protected $message = [
		'tradeno.unique'=>'交易流水号已经存在',
	];

	protected $scene  = [
		'update'=>['tradeno'],
	];



}

