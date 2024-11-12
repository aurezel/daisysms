<?php 
/*
 module:		接口访问日志
 create_time:	2023-03-28 10:16:14
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\Apilog;
use think\exception\ValidateException;
use xhadmin\CommonService;

class ApilogService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = Apilog::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		return ['rows'=>$res->items(),'total'=>$res->total()];
	}




}

