<?php 
/*
 module:		充值订单
 create_time:	2023-07-27 23:47:31
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\Chargeorder;
use think\exception\ValidateException;
use xhadmin\CommonService;

class ChargeorderService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = Chargeorder::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		return ['rows'=>$res->items(),'total'=>$res->total()];
	}


	/*
 	* @Description  添加
 	*/
	public static function add($data){
		try{
			$data['dateline'] = time();
			$res = Chargeorder::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		if(!$res){
			throw new ValidateException ('操作失败');
		}
		return $res->chargeorder_id;
	}


	/*
 	* @Description  修改
 	*/
	public static function update($data){
		try{
			validate(\app\admin\validate\Chargeorder::class)->scene('update')->check($data);
			$data['dateline'] = strtotime($data['dateline']);
			$data['updateline'] = time();
			$res = Chargeorder::update($data);
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

