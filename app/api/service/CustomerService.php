<?php 
/*
 module:		客服成员管理
 create_time:	2020-07-25 19:46:54
 author:		
 contact:		
*/

namespace app\api\service;
use app\api\model\Customer;
use think\facade\Log;
use think\exception\ValidateException;
use xhadmin\CommonService;

class CustomerService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$orderby,$limit,$page){
		try{
			$res = db('customer')->field($field)->alias('a')->join('user b','a.customerid=b.user_id','left')->where($where)->order($orderby)->paginate(['list_rows'=>$limit,'page'=>$page]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return ['list'=>$res->items(),'count'=>$res->total()];
	}


	/*
 	* @Description  添加
 	*/
	public static function add($data){
		try{
			$data['addtime'] = time();
			$res = Customer::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res->id;
	}


	/*
 	* @Description  修改
 	*/
	public static function update($where,$data){
		try{
			!is_null($data['addtime']) && $data['addtime'] = strtotime($data['addtime']);
			$res = Customer::where($where)->update($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res;
	}




}

