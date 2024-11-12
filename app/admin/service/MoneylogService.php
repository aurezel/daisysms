<?php 
/*
 module:		资金变动记录
 create_time:	2023-05-03 00:07:01
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\Moneylog;
use think\exception\ValidateException;
use xhadmin\CommonService;

class MoneylogService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = Moneylog::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
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
			$res = Moneylog::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		if(!$res){
			throw new ValidateException ('操作失败');
		}
		return $res->moneylog_id;
	}


	/*
 	* @Description  修改
 	*/
	public static function update($data){
		try{
			$data['dateline'] = strtotime($data['dateline']);
			$res = Moneylog::update($data);
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

