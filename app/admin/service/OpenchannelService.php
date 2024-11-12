<?php 
/*
 module:		开通渠道
 create_time:	2023-05-03 00:23:03
 author:		
 contact:		
*/

namespace app\admin\service;
use think\exception\ValidateException;
use xhadmin\CommonService;

class OpenchannelService extends CommonService {


	/*
 	* @Description  添加
 	*/
	public static function add($data){
		try{
			$data['tradetype'] = implode(',',$data['tradetype']);
			$data['dorule'] = implode(',',$data['dorule']);
			$res = Openchannel::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		if(!$res){
			throw new ValidateException ('操作失败');
		}
//		return $res->;
	}




}

