<?php 
/*
 module:		每日收益记录
 create_time:	2023-05-03 23:13:14
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\Incomeday;
use think\exception\ValidateException;
use xhadmin\CommonService;

class IncomedayService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = Incomeday::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		return ['rows'=>$res->items(),'total'=>$res->total()];
	}




}

