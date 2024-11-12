<?php 
/*
 module:		交易会员组
 create_time:	2023-07-28 14:45:18
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\Tradegroup;
use think\exception\ValidateException;
use xhadmin\CommonService;

class TradegroupService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = Tradegroup::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
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
			validate(\app\admin\validate\Tradegroup::class)->scene('add')->check($data);
			$res = Tradegroup::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		if(!$res){
			throw new ValidateException ('操作失败');
		}
		return $res->id;
	}


	/*
 	* @Description  修改
 	*/
	public static function update($data){
		try{
			validate(\app\admin\validate\Tradegroup::class)->scene('update')->check($data);
			$res = Tradegroup::update($data);
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

