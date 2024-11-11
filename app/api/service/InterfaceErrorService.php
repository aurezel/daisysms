<?php 
/*
 module:		接口告警
 create_time:	2020-09-23 11:38:42
 author:		
 contact:		
*/

namespace app\api\service;
use app\api\model\InterfaceError;
use think\facade\Log;
use think\exception\ValidateException;
use xhadmin\CommonService;

class InterfaceErrorService extends CommonService {


	/*
 	* @Description  创建数据
 	*/
	public static function add($data){
		try{
			$res = InterfaceError::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		return $res->id;
	}




}

