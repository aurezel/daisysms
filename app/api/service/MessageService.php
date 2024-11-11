<?php 
/*
 module:		消息管理
 create_time:	2020-07-25 19:46:57
 author:		
 contact:		
*/

namespace app\api\service;
use app\api\model\Message;
use think\facade\Log;
use think\exception\ValidateException;
use xhadmin\CommonService;

class MessageService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$orderby,$limit,$page){
		try{
			$res = db('message')->field($field)->alias('a')->join('user b','a.customerid=b.user_id','left')->where($where)->order($orderby)->paginate(['list_rows'=>$limit,'page'=>$page]);
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
			$data['timestamp'] = strtotime($data['timestamp']);
			$data['isread'] = !is_null($data['isread']) ? $data['isread'] : '0';
			$res = Message::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res->mid;
	}


	/*
 	* @Description  修改
 	*/
	public static function update($where,$data){
		try{
			!is_null($data['timestamp']) && $data['timestamp'] = strtotime($data['timestamp']);
			$res = Message::where($where)->update($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res;
	}




}

