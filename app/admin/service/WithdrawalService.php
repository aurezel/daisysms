<?php 
/*
 module:		提现管理
 create_time:	2023-04-06 18:24:54
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\Withdrawal;
use think\exception\ValidateException;
use xhadmin\CommonService;

class WithdrawalService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = Withdrawal::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		return ['rows'=>$res->items(),'total'=>$res->total()];
	}


	/*
 	* @Description  修改
 	*/
	public static function update($data){
		try{
			$data['updateline'] = time();
			$res = Withdrawal::update($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		if(!$res){
			throw new ValidateException ('操作失败');
		}
		return $res;
	}




}

