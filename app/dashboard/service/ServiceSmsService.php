<?php 
/*
 module:		会员管理
 create_time:	2023-05-04 06:43:27
 author:		
 contact:		
*/

namespace app\dashboard\service;
//use app\dashboard\model\ServiceSms;
use app\dashboard\model\ServiceSms;
use think\exception\ValidateException;
use xhadmin\CommonService;

class ServiceSmsService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = ServiceSms::where($where)->whereOr("code",'=', 'unlisted')->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		return ['rows'=>$res->items(),'total'=>$res->total()];
	}


}

