<?php 
/*
 module:		接口告警
 create_time:	2020-09-23 11:20:36
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\InterfaceError;
use think\exception\ValidateException;
use xhadmin\CommonService;

class InterfaceErrorService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = InterfaceError::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		return ['rows'=>$res->items(),'total'=>$res->total()];
	}




}

