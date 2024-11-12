<?php 
/*
 module:		活动领取记录
 create_time:	2023-04-23 10:56:26
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\Activerecord;
use think\exception\ValidateException;
use xhadmin\CommonService;

class ActiverecordService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = Activerecord::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		return ['rows'=>$res->items(),'total'=>$res->total()];
	}




}

