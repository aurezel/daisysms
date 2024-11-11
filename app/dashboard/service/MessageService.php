<?php 
/*
 module:		消息管理
 create_time:	2020-07-25 20:01:31
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\Message;
use think\exception\ValidateException;
use xhadmin\CommonService;

class MessageService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = db('message')->field($field)->alias('a')->join('user b','a.customerid=b.user_id','left')->where($where)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return ['rows'=>$res->items(),'total'=>$res->total()];
	}


	/*
 	* @Description  添加
 	*/
	public static function add($data){
		try{
			$data['timestamp'] = strtotime($data['timestamp']);
			$res = Message::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		if(!$res){
			throw new ValidateException ('操作失败');
		}
		return $res->mid;
	}


	/*
 	* @Description  修改
 	*/
	public static function update($data){
		try{
			$data['timestamp'] = strtotime($data['timestamp']);
			$res = Message::update($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		if(!$res){
			throw new ValidateException ('操作失败');
		}
		return $res;
	}




}

