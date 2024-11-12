<?php 
/*
 module:		会员管理
 create_time:	2023-05-04 06:43:27
 author:		
 contact:		
*/

namespace app\dashboard\service;
use app\dashboard\model\Payment;
use think\exception\ValidateException;
use xhadmin\CommonService;

class PaymentService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
            $where['user_id'] = session('user.user_id');
			$res = Payment::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		return ['rows'=>$res->items(),'total'=>$res->total()];
	}


}

